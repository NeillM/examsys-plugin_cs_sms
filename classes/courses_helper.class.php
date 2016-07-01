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
* Courses processig file
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * Courses helper class.
 */
class courses_helper {
    /**
     * Process courses WS response
     * @param string xml $response xml from course WS
     * @param integer $userid user to record actions under
     * @param array $strings lnaguage strings
     * @param mysqli $db db connection
     * @param string $logfile log file location
     * @param boolean $validation validate xml response against schema
     * @return boolean true on success, false on error
     */
    static public function process($response, $userid, $strings, $db, $logfile, $validation) {
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
            $schema = '..' . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . 'PlanList.xsd';
            if (!$data->schemaValidate($schema)) {
                $errorline = __LINE__ - 1;
                log_helper::log_app_warning($userid, $strings['restnotvalid'], $errorline, $db);
                return false;
            }
            // Disable user error handling.
            libxml_use_internal_errors(false);
        }
        // Courses in Rogo are Plans in Campus Solutions.
        $plans = $data->getElementsByTagName('Plan');
        $currentplans = array();
        $courses = array();
        foreach ($plans as $plan) {
            $course = array();
            foreach ($plan->childNodes as $childnode) {
                $course[$childnode->nodeName] = $childnode->nodeValue;
            }
            $courses[] = $course;
        }
        $node = 1;
        // Create / Update Courses.
        $cm = new \api\coursemanagement($db);
        foreach ($courses as $coursedata) {
            // The PlanID in Campus Solutions is the Course External ID in Rogo.
            $currentplans[] = $coursedata['PlanID'];
            $params = array();
            $courseid = \CourseUtils::get_courseid_from_externalid($coursedata['PlanID'], $db);
            $params['name'] = $coursedata['PlanCode'];
            $params['description'] = $coursedata['PlanDescr'];
            $params['schoolextid'] = $coursedata['SchoolID'];
            $params['externalid'] = $coursedata['PlanID'];
            $params['nodeid'] = $node;
            $node++;
            if ($courseid) {
                // If ExternalID exists call coursemanagement update api.
                $response = $cm->update($params, $userid);
                $type = 'Course Update';
            } else {
                // If ExternalID new call coursemanagement create api.
                $response = $cm->create($params, $userid);
                $type = 'Course Create';
            }
            log_helper::log($type, $params, $response, $logfile);
        }
        // Delete courses that have been removed from CS.
        $delete = \CourseUtils::diff_external_courses_to_internal_courses($currentplans, $db);
        // Try to delete course via coursemanagement delete api.
        foreach ($delete as $deleteid) {
            $params = array();
            $params['externalid'] = $deleteid;
            $params['nodeid'] = $node;
            $node++;
            $response = $cm->delete($params, $userid);
            log_helper::log('Course Delete', $params, $response, $logfile);
        }
        return true;
    }
}