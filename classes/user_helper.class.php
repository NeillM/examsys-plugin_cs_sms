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
* User import helper file
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * User import helper class.
 */
class user_helper {
    /**
     * Parse the user node of the membership xml and create/update users as required
     * @param DOMNodeList $usernode xml for users
     * @param string $moduleextid external system module id
     * @param integer $userid user to log action to
     * @param string $logfile log file location
     * @param mysqli $db db connection
     * @param array $userupdated list of users already updated so we can skip
     * @return array list of enrolled users
     */
    static public function get_users($usernode, $moduleextid, $userid, $logfile, $db, &$userupdated) {
        $currentenrols = array();
        foreach ($usernode as $users) {
            if ($users->hasChildNodes()) {
                $xpath = new \DOMXPath($users->ownerDocument);
                // Student IDs in Rogo are User IDs in Campus Solutions.
                $externalid = $xpath->query('./UserId', $users)->item(0)->nodeValue;
                $node = 1;
                if (!is_null($externalid)) {
                    $um = new \api\usermanagement($db);
                    // Only update a user once per enrolment import.
                    if (!in_array($externalid, $userupdated)) {
                        $params = array();
                        // Status affects Role.
                        if ($xpath->query('./Role', $users)->item(0)->nodeValue == 'Student') {
                            $params['role'] = self::map_student_status($xpath->query('./Status', $users)->item(0)->nodeValue);
                        } else {
                            //Non Students not supported.
                            continue;
                        }
                        $id = \UserUtils::studentid_exists($externalid, $db);
                        $params['studentid'] = $externalid;
                        $params['username'] = $xpath->query('./Username', $users)->item(0)->nodeValue;
                        $params['forename'] = $xpath->query('./ForeName', $users)->item(0)->nodeValue;
                        $params['surname'] = $xpath->query('./Surname', $users)->item(0)->nodeValue;
                        $params['title'] = self::map_title($xpath->query('./Title', $users)->item(0)->nodeValue);
                        $params['email'] = $xpath->query('./Email', $users)->item(0)->nodeValue;
                        $params['gender'] = self::map_gender($xpath->query('./Gender', $users)->item(0)->nodeValue, $params['title']);
                        $params['course'] = $xpath->query('./PlanID', $users)->item(0)->nodeValue;
                        $params['year'] = self::map_yearofstudy($xpath->query('./YearOfStudy', $users)->item(0)->nodeValue);
                        $params['nodeid'] = $node;
                        $currentenrols[$moduleextid][$externalid] = $params['username'];
                        $node++;
                        if ($id) {
                            // Update User.
                            $params['id'] = $id;
                            $response = $um->update($params, $userid);
                            if ($response['status'] === 100) {
                                $userupdated[] = $externalid;
                            }
                            $type = 'User Update';
                        } else {
                            //Create User.
                            $response = $um->create($params, $userid);
                            if ($response['status'] === 100) {
                                $userupdated[] = $externalid;
                            }
                            $type = 'User Create';
                        }
                        log_helper::log($type, $params, $response, $logfile);
                    }
                }
            }
        }
        return $currentenrols;
    }

    /**
     * Function to map gender supplied by CS to gender in Rogo
     * @param string $csgender gender in CS
     * @param string $title title in rogo
     * @return string|null rogo gender or null if not mapped
     */
    static public function map_gender($csgender, $title) {
        switch ($csgender) {
            case 'F':
                $gender = 'Female';
                break;
            case 'M':
                $gender = 'Male';
                break;
            case 'O':
            case 'X':
                $gender = 'Other';
                break;
            default:
                // Use title in rogo to assume gender.
                $gender = self::title_to_gender($title);
                break;
        }
        return $gender;
    }
    
    /**
     * Function to map title supplied by CS to title in Rogo
     * @param string $cstitle title in CS
     * @return string|null rogo title or null if not mapped
     */
    static public function map_title($cstitle) {
        // Valid Rogo titles Mx|Mr|Mrs|Miss|Ms|Dr|Professor
        if (preg_match("/^Mx|Mr|Mrs|Miss|Ms|Dr|Professor$/", $cstitle)) {
            $title = $cstitle;
        } else {
            $title = null;
        }
        return $title;
    }
    
    /**
     * Function to map title to gender
     * @param string $csgender gender in CS
     * @param string $title title in rogo
     * @return string|null rogo gender or null if not mapped
     */
    static public function title_to_gender($title) {
        // Valid Rogo titles Mx|Mr|Mrs|Miss|Ms|Dr|Professor
        switch ($title) {
            case 'Mx':
                $gender = 'Other';
                break;
            case 'Mr':
                $gender = 'Male';
                break;
            case 'Mrs':
            case 'Miss':
            case 'Ms':
                $gender = 'Female';
                break;
            default:
                $gender = null;
                break;
        }
        return $gender;
    }
    
    /**
     * Function to map status supplied by CS to role in Rogo
     * @param string $csstatus status in CS
     * @return string|null rogo role or null if not mapped
     */
    static public function map_student_status($csstatus) {
        /*  
        Possible Statuses from CS
        AC  Active in Program
        AD  Admitted - should not be sent to rogo so deafults to student
        AP  Applicant - should not be sent to rogo so deafults to student
        CM  Completed Program
        CN  Cancelled
        DC  Discontinued
        DE  Deceased
        DM  Dismissed
        LA  Leave of Absence
        PM  Prematriculant - should not be sent to rogo so deafults to student
        SP  Suspended
        WT  Waitlisted - should not be sent to rogo so deafults to student
        */
        switch ($csstatus) {
            case 'CN':
            case 'DC':
            case 'DE':
            case 'DM':
                $role = 'Left';
                break;
            case 'CM':
                $role = 'Graduate';
                break;
            case 'LA':
            case 'SP':
                $role = 'Suspended';
                break;
            default:
                $role = 'Student';
                break;
        }
        return $role;
    }

    /**
     * Function to map year of study supplied by CS to year of study in Rogo
     * @param string $csyear year in CS
     * @return string|null rogo year or null if not mapped
     */
    static public function map_yearofstudy($csyear) {
        // Valid Rogo years 0-6
        if (preg_match("/^[0-6]$/", $csyear)) {
            $year = $csyear;
        } else {
            $year = null;
        }
        return $year;
    }
}