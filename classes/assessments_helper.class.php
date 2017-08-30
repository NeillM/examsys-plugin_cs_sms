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
* Assessments processig file
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * Assessments helper class.
 */
class assessments_helper {
    
    /**
     * List of valid assessment types.
     * SUMMATIVE - CS only currently mapping one exam type to Rogo (summative)
     * @var array $validtypes
     */
    private static $validtypes = array('SUMMATIVE');
    
    /**
     * Process assessment WS response
     * @param string xml $response xml from enrolment WS
     * @param integer $userid user to record actions under
     * @param array $strings lnaguage strings
     * @param mysqli $db db connection
     * @param string $logfile log file location
     * @param integer $session academic session for enrolments
     * @param boolean $validation validate xml response against schema
     * @param array $args arguments used to call web service
     * @return boolean true on success, false on error
     */
    static public function process($response, $userid, $strings, $db, $logfile, $session, $validation, $args) {
        // Parse returned XML.
        $data = new \DOMDocument();
        $data->loadXML($response);
        if (xml_helper::check_for_error($data, $userid, $db, 'assessment', $args)) {
            return false;
        }
        if ($validation) {
            if (!xml_helper::validate($data, 'AssessmentList', $userid, $strings, $db)) {
                return false;
            }
        }
        $assessments = $data->getElementsByTagName('Assessment');
        // Schedule assessments.
        $am = new \api\assessmentmanagement($db);
        $node = 1;
        foreach ($assessments as $assessment) {
            $xpath = new \DOMXPath($assessment->ownerDocument);
            // The AssessmentID in Campus Solutions is the Properties External ID in Rogo.
            try {
                $externalid = $xpath->query('./AssessmentID', $assessment)->item(0)->nodeValue;
                // Skip invalid assessment types.
                $assessmenttype = $xpath->query('./AssessmentType', $assessment)->item(0)->nodeValue;
                if (!self::validate_type($assessmenttype)) {
                    continue;
                }
            } catch (\exception $e) {
                // If externalid not provided skip to next assessment.
                continue;
            }
            if (!is_null($externalid)) {
                // Schedule assessment.
                $params = array();
                $params['externalid'] = $externalid;
                $params['externalsys'] = plugin_cs_sms::SMS;
                try {
                    $params['title'] = $xpath->query('./AssessmentDescr', $assessment)->item(0)->nodeValue;
                    $params['duration'] = $xpath->query('./DurationMinutes', $assessment)->item(0)->nodeValue;
                    $params['session'] = $xpath->query('./AcademicSession', $assessment)->item(0)->nodeValue;
                    $params['sittings'] = $xpath->query('./Sittings', $assessment)->item(0)->nodeValue;
                    $owners = $xpath->query('./Owners', $assessment)->item(0)->childNodes;
                    $params['owner'] = self::process_owner($owners, $db);
                    $modules = $xpath->query('./Modules', $assessment)->item(0)->childNodes;
                    $params['extmodules'] = self::process_module($modules);
                } catch (\exception $e) {
                    // If the above are not provided we cannot create the assessment.
                    continue;
                }
                // Default optionals to null.
                $params['month'] = null;
                $params['cohort_size'] = null;
                $params['barriers'] = null;
                $params['campus'] = null;
                $params['notes'] = null;
                try {
                    $month = $xpath->query('./Month', $assessment)->item(0);
                    if (!empty($month)) {
                        $params['month'] = $month->nodeValue;
                    }
                } catch (\exception $e) {
                    // Optional so dont care.
                }
                try {
                    $cohort = $xpath->query('./CohortSize', $assessment)->item(0);
                    if (!empty($cohort)) {
                        $params['cohort_size'] = $cohort->nodeValue;
                    }
                } catch (\exception $e) {
                    // Optional so dont care.
                }
                try {
                    $barrier = $xpath->query('./Barriers', $assessment)->item(0);
                    if (!empty($barrier)) {
                        $params['barriers'] = $barrier->nodeValue;
                    }
                } catch (\exception $e) {
                    // Optional so dont care.
                }
                try {
                    $campus = $xpath->query('./Campus', $assessment)->item(0);
                    if (!empty($campus)) {
                        $params['campus'] = $campus->nodeValue;
                    }
                } catch (\exception $e) {
                    // Optional so dont care.
                }
                try {
                    $notes = $xpath->query('./Notes', $assessment)->item(0);
                    if (!empty($notes)) {
                        $params['notes'] = $notes->nodeValue;
                    }
                } catch (\exception $e) {
                    // Optional so dont care.
                }
                $params['nodeid'] = $node;
                $node++;
                if ($assessmenttype == 'SUMMATIVE') {
                    $response = $am->schedule($params, $userid);
                    // Convert array to string for logging
                    $loggingmodules = $params['extmodules'];
                    $params['extmodules'] = '';
                    foreach ($loggingmodules as $extmod) {
                        $params['extmodules'] .= $extmod['value'] . ',';
                    }
                    log_helper::log('Schedule', $params, $response, $logfile);
                }
            }
        }
        return true;
    }

    /**
     * Process owners node
     * @param DOMNodeList $ownernode xml for owners
     * @param mysqli $db db connection
     * @return mixed user rogo id or false if not found, null if missing.
     */
    static private function process_owner($ownernode, $db) {
        if (is_null($ownernode)) {
          throw new \Exception('owners tag missing');
          exit();
        }
        $userid = null;
        foreach ($ownernode as $owner) {
            if ($owner->hasChildNodes()) {
                $userid = self::get_owner($owner, $db);
                if ($userid) {
                    // Found an owner that exits in rogo.
                    break;
                }
            }
        }
        return $userid;
    }
    
    /**
     * Get owner from node
     * @param DOMNode $usernode xml for user
     * @param mysqli $db db connection
     * @return mixed user rogo id or false if not found, null if missing.
     */
    static private function get_owner($usernode, $db) {
        $xpath = new \DOMXPath($usernode->ownerDocument);
        try {
            $username = $xpath->query('./UserName', $usernode)->item(0)->nodeValue;
        } catch (\exception $e) {
            // Should not get here but fail gracefully later on.
            $username = null;
        }
        return \UserUtils::username_exists($username, $db);
    }
     
    /**
     * Process modules node
     * @param DOMNodeList $modulenode xml for modules
     * @return array list of module external ids.
     */
    static private function process_module($modulenode) {
        if (is_null($modulenode)) {
          throw new \Exception('modules tag missing');
          exit();
        }
        $modulesarray = array();
        $i = 0;
        foreach ($modulenode as $module) {
            if ($module->hasChildNodes()) {
                $xpath = new \DOMXPath($module->ownerDocument);
                try {
                    $modulesarray[] = array('id' => $i,'value' => $xpath->query('./ModuleID', $module)->item(0)->nodeValue);
                } catch (\exception $e) {
                    // If ModuleID not provided skip to next module.
                    continue;
                }
            }
        }
        return $modulesarray;
    }

    /**
     * Check if valid assessment type
     * @param string $type assessment type.
     * @return boolean true if valid, false otherwise
     */
    static private function validate_type($type) {
        return in_array($type, self::$validtypes);
    }
}