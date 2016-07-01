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
* Faculties processig file
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * Faculties helper class.
 */
class faculties_helper {
    /**
     * Process faculties WS response
     * @param string xml $response xml from faculty WS
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
        if (xml_helper::check_for_error($data, $userid, $db)) {
            return false;
        }
        if ($validation) {
            if (!xml_helper::validate($data, 'FacultyList', $userid, $strings, $db)) {
                return false;
            }
        }
        $faculties = $data->getElementsByTagName('Faculty');
        $currentfaculties = array();
        $currentschools = array();
        $facs = array();
        $schools = array();
        foreach ($faculties as $faculty) {
            $fac = array();
            foreach ($faculty->childNodes as $childnode) {
                if ($childnode->nodeName == 'MemberSchools') {
                    school_helper::get_schools($schools, $childnode->childNodes, $faculty);
                } else {
                    $fac[$childnode->nodeName] = $childnode->nodeValue;
                }
            }
            $facs[] = $fac;
        }
        $node = 1;
        // Create / Update faculties.
        $fm = new \api\facultymanagement($db);
        foreach ($facs as $facultydata) {
            // The FacultyID in Campus Solutions is the Faculty External ID in Rogo.
            if (!empty($facultydata['FacultyID'])) {
                $currentfaculties[] = $facultydata['FacultyID'];
                $params = array();
                $facultyid= \FacultyUtils::get_facultyid_from_externalid($facultydata['FacultyID'], $db);
                $params['code'] = $facultydata['FacultyCode'];
                $params['name'] = $facultydata['FacultyDescr'];
                $params['externalid'] = $facultydata['FacultyID'];
                $params['nodeid'] = $node;
                $node++;
                if ($facultyid) {
                    // If ExternalID exists call facultymanagement update api.
                    $response = $fm->update($params, $userid);
                    $type = 'Faculty Update';
                } else {
                    // If ExternalID new call facultymanagement create api.
                    $response = $fm->create($params, $userid);
                    $type = 'Faculty Create';
                }
                log_helper::log($type, $params, $response, $logfile);
            }
        }
        // Create / Update schools.
        $sm = new \api\schoolmanagement($db);
        foreach ($schools as $facultyextid => $facultydata) {
            foreach ($facultydata as $schoolidx => $schooldata) {
                // The SchoolID in Campus Solutions is the School External ID in Rogo.
                if (!empty($schooldata['SchoolID'])) {
                    $currentschools[] = $schooldata['SchoolID'];
                    $params = array();
                    $schoolid = \SchoolUtils::get_schoolid_from_externalid($schooldata['SchoolID'], $db);
                    $params['code'] = $schooldata['SchoolCode'];
                    $params['name'] = $schooldata['SchoolDescr'];
                    $params['externalid'] = $schooldata['SchoolID'];
                    $params['facultyextid'] = $facultyextid;
                    $params['nodeid'] = $node;
                    $node++;
                    if ($schoolid) {
                        // If ExternalID exists call schoolmanagement update api.
                        $response = $sm->update($params, $userid);
                        $type = 'School Update';
                    } else {
                        // If ExternalID new call schoolmanagement create api.
                        $response = $sm->create($params, $userid);
                        $type = 'School Create';
                    }
                    log_helper::log($type, $params, $response, $logfile);
                }
            }
        }
        // Delete schools that have been removed from CS.
        $delete = \SchoolUtils::diff_external_schools_to_internal_schools($currentschools, $db);
        // Try to delete course via schoolmanagement delete api.
        foreach ($delete as $deleteid) {
            $params = array();
            $params['externalid'] = $deleteid;
            $params['nodeid'] = $node;
            $node++;
            $response = $sm->delete($params, $userid);
            log_helper::log('School Delete', $params, $response, $logfile);
        }
        // Delete faculties that have been removed from CS.
        $delete = \FacultyUtils::diff_external_faculties_to_internal_faculties($currentfaculties, $db);
        // Try to delete course via facultymanagement delete api.
        foreach ($delete as $deleteid) {
            $params = array();
            $params['externalid'] = $deleteid;
            $params['nodeid'] = $node;
            $node++;
            $response = $fm->delete($params, $userid);
            log_helper::log('Faculty Delete', $params, $response, $logfile);
        }
        return true;
    }
}