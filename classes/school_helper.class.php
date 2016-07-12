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
* School import helper file
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * School import helper class.
 */
class school_helper {
    /**
     * Parse the school members node of the facultylist xml and create/update schools where required
     * @param DOMNodeList $schoolnode xml for schools
     * @param string $facultyextid external system id of faculty
     * @param mysqli $db db connection
     * @param integer $userid user to log action to
     * @param string $logfile log file location
     * @return array list of schools in faculty
     */
    static public function get_schools($schoolnode, $facultyextid, $db, $userid, $logfile) {
        // Create / Update schools.
        $sm = new \api\schoolmanagement($db);
        $node = 1;
        $currentschools = array();
        foreach ($schoolnode as $school) {
            $xpath = new \DOMXPath($school->ownerDocument);
            if ($school->hasChildNodes()) {
                // The SchoolID in Campus Solutions is the School External ID in Rogo.
                try {
                    $externalid = $xpath->query('./SchoolID', $school)->item(0)->nodeValue;
                } catch (\exception $e) {
                    // If externalid not provided skip to next school.
                    continue;
                }
                if (!is_null($externalid)) {
                    $currentschools[] = $externalid;
                    $params = array();
                    $schoolid = \SchoolUtils::get_schoolid_from_externalid($externalid, $db);
                    try {
                        $params['code'] = $xpath->query('./SchoolCode', $school)->item(0)->nodeValue;
                        $params['name'] = $xpath->query('./SchoolDescr', $school)->item(0)->nodeValue;
                    } catch (\exception $e) {
                        // If missing data nodes skip to next school.
                        continue;
                    }   
                    $params['externalid'] = $externalid;
                    $params['externalsys'] = plugin_cs_sms::SMS;
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
        return $currentschools;
    }
}