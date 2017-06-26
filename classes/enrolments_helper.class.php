<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

namespace plugins\SMS\plugin_cs_sms;
/**
* Enrolments processig file
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * Enrolments helper class.
 */
class enrolments_helper {
    /**
     * Process enrolment WS response
     * @param string xml $response xml from enrolment WS
     * @param integer $userid user to record actions under
     * @param array $strings lnaguage strings
     * @param mysqli $db db connection
     * @param string $logfile log file location
     * @param integer $session academic session for enrolments
     * @param boolean $validation validate xml response against schema
     * @param boolean $active only process enrolments for active modules
     * @return boolean true on success, false on error
     */
    static public function process($response, $userid, $strings, $db, $logfile, $session, $validation, $active) {
        // Parse returned XML.
        $data = new \DOMDocument();
        $data->loadXML($response);
        if (xml_helper::check_for_error($data, $userid, $db)) {
            return false;
        }
        if ($validation) {
            if (!xml_helper::validate($data, 'ModuleEnrolments', $userid, $strings, $db)) {
                return false;
            }
        }
        $enrolments = $data->getElementsByTagName('Module');
        $current_enrols = array();
        // Enrol/UnEnrol users on to modules.
        $mm = new \api\modulemanagement($db);
        $smsimports = array();
        $node = 1;
        $userupdated = array();
        foreach ($enrolments as $enrolment) {
            $currentenrols = array();
            $xpath = new \DOMXPath($enrolment->ownerDocument);
            // The ModuleID in Campus Solutions is the Module External ID in Rogo.
            try {
                $externalid = $xpath->query('./ModuleID', $enrolment)->item(0)->nodeValue;
            } catch (\exception $e) {
                // If externalid not provided skip to next module.
                continue;
            }
            if (!is_null($externalid)) {
                // Create/update users.
                try {
                    $usermembership = $xpath->query('./Membership', $enrolment)->item(0)->childNodes;
                } catch (\exception $e) {
                    // If membership node not provided cannot proceed with current module enrolments.
                    continue;
                }
                // Enrol / Unerol users.
                $details = \module_utils::get_full_details('external', $externalid, $db);
                $moduleid = $details['moduleid'];
                $activemodule = true;
                // Check if only syncing active modules.
                if ($active) {
                    $activemodule = $details['active'];
                }
                // We only enrol/unenrol if the module exists in rogo.
                if ($moduleid && $activemodule) {
                    $currentenrols = user_helper::get_users($usermembership, $externalid, $userid, $logfile, $db, $userupdated);
                    $smsimports[$moduleid]['enrolcount'] = 0;
                    $smsimports[$moduleid]['enrolusers'] = '';
                    $smsimports[$moduleid]['unenrolcount'] = 0;
                    $smsimports[$moduleid]['unenrolusers'] = '';
                    $params = array();
                    $params['moduleextid'] = $externalid;
                    $params['session'] = $session;
                    // Enrol.
                    foreach ($currentenrols[$externalid] as $userexternalid => $username) {
                        // Student IDs in Rogo are User IDs in Campus Solutions.
                        $params['studentid'] = $userexternalid;
                        try {
                            $params['session'] = $xpath->query('./Year', $enrolment)->item(0)->nodeValue;
                        } catch (\exception $e) {
                            // If session not provided no enrolments can take place.
                            break;
                        }
                        $params['attempt'] = 1;
                        $params['nodeid'] = $node;
                        $node++;
                        $response = $mm->enrol($params, $userid);
                        log_helper::log('Enrol', $params, $response, $logfile);
                        if ($response['statuscode'] === 100) {
                            $smsimports[$moduleid]['enrolcount']++;
                            $smsimports[$moduleid]['enrolusers'] .= $username . ',';
                        }
                    }
                    // Unenrol.
                    $params = array();
                    $params['moduleextid'] = $externalid;
                    $params['session'] = $session;
                    $membership = \module_utils::get_student_members($session, $moduleid, $db);
                    foreach ($membership as $idx => $member) {
                        if (!key_exists($member['studentid'], $currentenrols[$externalid])) {
                            $params['studentid'] = $member['studentid'];
                            $params['nodeid'] = $node;
                            $response = $mm->unenrol($params, $userid);
                            $node++;
                            log_helper::log('UnEnrol', $params, $response, $logfile);
                            if ($response['statuscode'] === 100) {
                                $smsimports[$moduleid]['unenrolcount']++;
                                $smsimports[$moduleid]['unenrolusers'] .= $member['username'] . ',';
                            }
                        }
                    }
                    // Update SMS import log table.
                    $smsimports[$moduleid]['enrolusers'] = rtrim($smsimports[$moduleid]['enrolusers'], ',');
                    $smsimports[$moduleid]['unenrolusers'] = rtrim($smsimports[$moduleid]['unenrolusers'], ',');
                    if ($smsimports[$moduleid]['unenrolcount'] > 0 or $smsimports[$moduleid]['enrolcount'] > 0) {
                        \module_utils::log_sms_imports($moduleid, $smsimports[$moduleid]['enrolcount'], $smsimports[$moduleid]['enrolusers'], 
                            $smsimports[$moduleid]['unenrolcount'], $smsimports[$moduleid]['unenrolusers'], 'Campus Solutions', $session, $db);
                    }
                }
            }
        }
        return true;
    }
}