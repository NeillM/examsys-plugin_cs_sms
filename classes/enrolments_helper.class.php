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
     * @return boolean true on success, false on error
     */
    static public function process($response, $userid, $strings, $db, $logfile, $session, $validation) {
        // Parse returned XML.
        $data = new \DOMDocument();
        $data->loadXML($response);
        $errornode = $data->getElementsByTagName('Error')->item(0);
        if (!is_null($errornode)) {
            foreach ($errornode->childNodes as $childnode) {
                if ($childnode->nodeName == 'Header') {
                    $errorline = __LINE__ - 1;
                    log_helper::log_app_warning($userid, $childnode->nodeValue, $errorline, $db);
                    return false;
                }
            }
        }
        if ($validation) {
            // Enable user error handling.
            libxml_use_internal_errors(true);
            $schema = '..' . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'ModuleEnrolments.xsd';
            if (!$data->schemaValidate($schema)) {
                $errorline = __LINE__ - 1;
                log_helper::log_app_warning($userid, $strings['restnotvalid'], $errorline, $db);
                return false;
            }
            // Disable user error handling.
            libxml_use_internal_errors(false);
        }
        $enrolments = $data->getElementsByTagName('Module');
        $current_enrols = array();
        $enrols = array();
        $users = array();
        foreach ($enrolments as $enrolment) {
            $enrol = array();
            foreach ($enrolment->childNodes as $childnode) {
                if ($childnode->nodeName == 'Membership') {
                    user_helper::get_users($users, $childnode->childNodes, $enrolment);
                } else {
                    $enrol[$childnode->nodeName] = $childnode->nodeValue;
                }
            }
            $enrols[] = $enrol;
        }
        $node = 1;
        // Create/update users.
        $um = new \api\usermanagement($db);
        $userupdated = array();
        foreach ($users as $moduleextid => $moduleenroldata) {
            foreach ($moduleenroldata as $useridx => $userdata) {
                if (!empty($userdata['UserId'])) {
                    // Only update a user once per enrolment import.
                    if (!in_array($userdata['UserId'], $userupdated)) {
                        $params = array();
                        // Status affects Role.
                        if ($userdata['Role'] == 'Student') {
                            $params['role'] = user_helper::map_student_status($userdata['Status']);
                        } else {
                            //Non Students not supported.
                            continue;
                        }
                        $currentenrols[$moduleextid][] = $userdata['UserId'];
                        $id = \UserUtils::studentid_exists($userdata['UserId'], $db);
                        // Student IDs in Rogo are User IDs in Campus Solutions.
                        $params['studentid'] = $userdata['UserId'];
                        $params['username'] = $userdata['Username'];
                        $params['forename'] = $userdata['ForeName'];
                        $params['surname'] = $userdata['Surname'];
                        $params['title'] = user_helper::map_title($userdata['Title']);
                        $params['email'] = $userdata['Email'];
                        $params['gender'] = user_helper::map_gender($userdata['Gender'], $params['title']);
                        $params['course'] = $userdata['PlanID']; 
                        $params['year'] = user_helper::map_yearofstudy($userdata['YearOfStudy']);
                        $params['nodeid'] = $node;
                        $node++;
                        if ($id) {
                            // Update User.
                            $params['id'] = $id;
                            $response = $um->update($params, $userid);
                            if ($response['status'] === 100) {
                                $userupdated[] = $userdata['UserId'];
                            }
                            $type = 'User Update';
                        } else {
                            //Create User.
                            $response = $um->create($params, $userid);
                            if ($response['status'] === 100) {
                                $userupdated[] = $userdata['UserId'];
                            }
                            $type = 'User Create';
                        }
                        log_helper::log($type, $params, $response, $logfile);
                    }
                }
            }
        }
        // Enrol/UnEnrol users on to modules.
        $mm = new \api\modulemanagement($db);
        $smsimports = array();
        foreach ($enrols as $enroldata) {
            if (!empty($enroldata['ModuleID'])) {
                $moduleid = \module_utils::get_id_from_externalid($enroldata['ModuleID'], $db);
                // We only enrol/unenrol if the module exists in rogo.
                if ($moduleid) {
                    $smsimports[$moduleid]['enrolcount'] = 0;
                    $smsimports[$moduleid]['enrolusers'] = '';
                    $smsimports[$moduleid]['unenrolcount'] = 0;
                    $smsimports[$moduleid]['unenrolusers'] = '';
                    $params = array();
                    $params['moduleextid'] = $enroldata['ModuleID'];
                    $params['session'] = $session;
                    // Enrol.
                    foreach ($users[$enroldata['ModuleID']] as $useridx => $userdata) {
                        $params['studentid'] = $userdata['UserId'];
                        $params['session'] = $enroldata['Year'];
                        $params['attempt'] = 1;
                        $params['nodeid'] = $node;
                        $node++;
                        $response = $mm->enrol($params, $userid);
                        log_helper::log('Enrol', $params, $response, $logfile);
                        if ($response['statuscode'] === 100) {
                            $smsimports[$moduleid]['enrolcount']++;
                            $smsimports[$moduleid]['enrolusers'] .= $userdata['Username'] . ',';
                        }
                    }
                    // Unenrol.
                    $params = array();
                    $params['moduleextid'] = $enroldata['ModuleID'];
                    $params['session'] = $session;
                    $membership = \module_utils::get_student_members($session, $moduleid, $db);
                    foreach ($membership as $idx => $member) {
                        $details = \UserUtils::get_full_details_by_ID($member['userID'], $db);
                        if (!in_array($details['studentid'], $currentenrols[$enroldata['ModuleID']])) {
                            $params['studentid'] = $details['studentid'];
                            $params['nodeid'] = $node;
                            $response = $mm->unenrol($params, $userid);
                            $node++;
                            log_helper::log('UnEnrol', $params, $response, $logfile);
                            if ($response['statuscode'] === 100) {
                                $smsimports[$moduleid]['unenrolcount']++;
                                $smsimports[$moduleid]['unenrolusers'] .= $details['username'] . ',';
                            }
                        }
                    }
                    // Log SMS import info.
                    $smsimports[$moduleid]['enrolusers'] = rtrim($smsimports[$moduleid]['enrolusers'], ',');
                    $smsimports[$moduleid]['unenrolusers'] = rtrim($smsimports[$moduleid]['unenrolusers'], ',');
                }
            }
        }
        foreach ($smsimports as $idMod => $details) {
            if (!isset($details['enrolcount'])) {
                $details['enrolcount'] = 0;
                $details['enrolusers'] = '';
            }
            if (!isset($details['unenrolcount'])) {
                $details['unenrolcount'] = 0;
                $details['unenrolusers'] = '';
            }
            if ($details['unenrolcount'] > 0 or $details['enrolcount'] > 0) {
                \module_utils::log_sms_imports($idMod, $details['enrolcount'], $details['enrolusers'], 
                    $details['unenrolcount'], $details['unenrolusers'], 'Campus Solutions', $session, $db);
            }
        }
        return true;
    }
}