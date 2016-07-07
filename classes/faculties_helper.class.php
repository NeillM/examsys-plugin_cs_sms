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
        $node = 1;
        // Create / Update faculties.
        $fm = new \api\facultymanagement($db);
        foreach ($faculties as $faculty) {
            $xpath = new \DOMXPath($faculty->ownerDocument);
            // The FacultyID in Campus Solutions is the Faculty External ID in Rogo.
            $externalid = $xpath->query('./FacultyID', $faculty)->item(0)->nodeValue;
            if (!is_null($externalid)) {
                $currentfaculties[] = $externalid;
                $params = array();
                $facultyid= \FacultyUtils::get_facultyid_from_externalid($externalid, $db);
                $params['code'] = $xpath->query('./FacultyCode', $faculty)->item(0)->nodeValue;
                $params['name'] = $xpath->query('./FacultyDescr', $faculty)->item(0)->nodeValue;
                $params['externalid'] = $externalid;
                $params['externalsys'] = plugin_cs_sms::SMS;
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
                $memberschools = $xpath->query('./MemberSchools', $faculty)->item(0)->childNodes;
                $currentschools = array_merge($currentschools, school_helper::get_schools($memberschools, $externalid, $db, $userid, $logfile));
            }
        }
        // Delete schools that have been removed from CS.
        $sm = new \api\schoolmanagement($db);
        $delete = \SchoolUtils::diff_external_schools_to_internal_schools($currentschools, plugin_cs_sms::SMS, $db);
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
        $delete = \FacultyUtils::diff_external_faculties_to_internal_faculties($currentfaculties, plugin_cs_sms::SMS, $db);
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