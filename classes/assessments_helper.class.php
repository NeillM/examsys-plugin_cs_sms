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
     * Process assessment WS response
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
        if (xml_helper::check_for_error($data, $userid, $db)) {
            return false;
        }
        if ($validation) {
            if (!xml_helper::validate($data, 'AssessmentList', $userid, $strings, $db)) {
                return false;
            }
        }
        $assessments = $data->getElementsByTagName('Assessment');
        $currentassessments = array();
        // Schedule assessments.
        $am = new \api\assessmentmanagement($db);
        $node = 1;
        foreach ($assessments as $assessment) {
            $xpath = new \DOMXPath($assessment->ownerDocument);
            // The AssessmentID in Campus Solutions is the Properties External ID in Rogo.
            try {
                $externalid = $xpath->query('./AssessmentID', $assessment)->item(0)->nodeValue;
                // TODO what assessment types do we care about.
                $assessmenttype = $xpath->query('./AssessmentType', $assessment)->item(0)->nodeValue;
            } catch (\exception $e) {
                // If externalid not provided skip to next assessment.
                continue;
            }
            if (!is_null($externalid) and $assessmenttype == 'TBC') {
                // Schedule assessment.
                $currentassessments[] = $externalid;
                $params = array();
                $params['externalid'] = $externalid;
                $params['externalsys'] = plugin_cs_sms::SMS;
                try {
                    $params['title'] = $xpath->query('./AssessmentDescr', $assessment)->item(0)->nodeValue;
                    $hours = $xpath->query('./DurationHours', $assessment)->item(0)->nodeValue;
                    $mintues = $xpath->query('./DurationMinutes', $assessment)->item(0)->nodeValue;
                    $params['duration'] = ($hours * 60) + $minutes;
                    $params['session'] = $xpath->query('./AcademicSession', $assessment)->item(0)->nodeValue;
                    $params['sittings'] = $xpath->query('./Sittings', $assessment)->item(0)->nodeValue;
                    $username = $xpath->query('./Owner/UserName', $assessment)->item(0)->nodeValue;
                    $param['owner'] = \UserUtils::username_exists($username, $db);
                    $modules = $xpath->query('./Modules', $assessment)->item(0)->childNodes;
                    $params['modules'] = $this->process_module($modules);
                } catch (\exception $e) {
                    // If session not provided no enrolments can take place.
                    break;
                }
                try {
                    $params['month'] = $xpath->query('./Month', $assessment)->item(0)->nodeValue;
                    $params['cohort_size'] = $xpath->query('./CohortSize', $assessment)->item(0)->nodeValue;
                    $params['barriers'] = $xpath->query('./Barriers', $assessment)->item(0)->nodeValue;
                    $params['campus'] = $xpath->query('./Campus', $assessment)->item(0)->nodeValue;
                    $params['notes'] = $xpath->query('./Notes', $assessment)->item(0)->nodeValue;
                } catch (\exception $e) {
                    // Optional so dont care.
                } 
                $params['nodeid'] = $node;
                $node++;
                $response = $am->schedule($params, $userid);
                log_helper::log('Schedule', $params, $response, $logfile);
            }
        }
        // Diff and delete assessments not longer required.
        $delete = \Paper_utils::diff_external_assessments_to_internal_assessments($currentassessments, plugin_cs_sms::SMS, $db);
        // Try to delete assessment via assessmentmanagement delete api.
        foreach ($delete as $deleteid) {
            $params = array();
            $params['externalid'] = $deleteid;
            $params['nodeid'] = $node;
            $node++;
            $response = $am->delete($params, $userid);
            log_helper::log('Schedule Delete', $params, $response, $logfile);
        }
        return true;
    }

    /**
     * Process modules node
     * @param DOMNodeList $modulenode xml for modules
     * @return array list of module external ids.
     */
    private function process_module($modulenode) {
        $modulesarray = array();
        foreach ($modulenode as $module) {
            if ($module->hasChildNodes()) {
                $xpath = new \DOMXPath($module->ownerDocument);
                try {
                    $modulesarray[] = $xpath->query('./ModuleID', $module)->item(0)->nodeValue;
                } catch (\exception $e) {
                    // If ModuleID not provided skip to next module.
                    continue;
                }
            }
        }
        return $modulesarray;
    }
}