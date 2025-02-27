<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

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
class courses_helper
{
    /**
     * Process courses WS response
     * @param string xml $response xml from course WS
     * @param integer $userid user to record actions under
     * @param array $strings lnaguage strings
     * @param \mysqli $db db connection
     * @param string $logfile log file location
     * @param boolean $validation validate xml response against schema
     * @param array $args arguments used to call web service
     * @return boolean|array false on error, list of current plan ids on success
     */
    public static function process($response, $userid, $strings, $db, $logfile, $validation, $args)
    {
        // Parse returned XML.
        $data = new \DOMDocument();
        $data->loadXML($response);
        if (xml_helper::check_for_error($data, $userid, $db, 'course feed', $args)) {
            return false;
        }
        if ($validation) {
            if (!xml_helper::validate($data, 'PlanList', $userid, $strings, $db)) {
                return false;
            }
        }
        // Courses in ExamSys are Plans in Campus Solutions.
        $plans = $data->getElementsByTagName('Plan');
        $currentplans = [];
        $node = 1;
        // Create / Update Courses.
        $cm = new \api\coursemanagement($db);
        foreach ($plans as $plan) {
            $xpath = new \DOMXPath($plan->ownerDocument);
            // The PlanID in Campus Solutions is the Course External ID in ExamSys.
            try {
                $externalid = $xpath->query('./PlanID', $plan)->item(0)->nodeValue;
            } catch (\exception $e) {
                // If externalid not provided skip to next course.
                continue;
            }
            if (!is_null($externalid)) {
                $currentplans[] = $externalid;
                $params = [];
                $courseid = \CourseUtils::get_courseid_from_externalid($externalid, plugin_cs_sms::SMS, $db);
                try {
                    $params['name'] = $xpath->query('./PlanCode', $plan)->item(0)->nodeValue;
                    $params['description'] = $xpath->query('./PlanDescr', $plan)->item(0)->nodeValue;
                    $params['schoolextid'] = $xpath->query('./SchoolID', $plan)->item(0)->nodeValue;
                } catch (\exception $e) {
                    // If course data not provided skip to next course.
                    continue;
                }
                $params['externalid'] = $externalid;
                $params['externalsys'] = plugin_cs_sms::SMS;
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
        }
        return $currentplans;
    }

    /**
     * Delete courses that have been removed from CS
     *
     * @param array $currentplans list of course ids in CS
     * @param string $logfile log file location
     * @param integer $userid user to record actions under
     * @param \mysqli $db db connection
     */
    public static function delete_courses($currentplans, $logfile, $userid, $db)
    {
        $cm = new \api\coursemanagement($db);
        $delete = \CourseUtils::diff_external_courses_to_internal_courses($currentplans, plugin_cs_sms::SMS, $db);
        $node = 1;
        // Try to delete course via coursemanagement delete api.
        foreach ($delete as $deleteid) {
            $params = [];
            $params['externalid'] = $deleteid;
            $params['nodeid'] = $node;
            $node++;
            $response = $cm->delete($params, $userid);
            log_helper::log('Course Delete', $params, $response, $logfile);
        }
    }
}
