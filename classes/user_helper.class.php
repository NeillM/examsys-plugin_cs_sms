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
* User import helper file
*
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * User import helper class.
 */
class user_helper
{
    /**
     * Parse the user node of the membership xml and create/update users as required
     * @param \DOMNodeList $usernode xml for users
     * @param string $moduleextid external system module id
     * @param integer $userid user to log action to
     * @param string $logfile log file location
     * @param \mysqli $db db connection
     * @param array $userupdated list of users already updated so we can skip
     * @return array list of enrolled users
     */
    public static function get_users($usernode, $moduleextid, $userid, $logfile, $db, &$userupdated)
    {
        $currentenrols = array();
        foreach ($usernode as $users) {
            if ($users->hasChildNodes()) {
                $xpath = new \DOMXPath($users->ownerDocument);
                // Student IDs in ExamSys are User IDs in Campus Solutions.
                try {
                    $externalid = $xpath->query('./UserId', $users)->item(0)->nodeValue;
                } catch (\exception $e) {
                    // If externalid not provided skip to next user.
                    continue;
                }
                $node = 1;
                if (!is_null($externalid)) {
                    $um = new \api\usermanagement($db);
                    // Only update a user once per enrolment import.
                    if (!in_array($externalid, $userupdated)) {
                        $id = \UserUtils::studentid_exists($externalid, $db);
                        $params = array();
                        try {
                            // Status affects Role.
                            if ($xpath->query('./Role', $users)->item(0)->nodeValue == 'Student') {
                                $params['role'] = self::map_student_status($xpath->query('./Status', $users)->item(0)->nodeValue, $id, $db);
                            } else {
                                //Non Students not supported.
                                continue;
                            }
                            $params['studentid'] = $externalid;
                            $params['username'] = $xpath->query('./Username', $users)->item(0)->nodeValue;
                            $params['forename'] = $xpath->query('./ForeName', $users)->item(0)->nodeValue;
                            $params['surname'] = $xpath->query('./Surname', $users)->item(0)->nodeValue;
                            $params['title'] = self::map_title($xpath->query('./Title', $users)->item(0)->nodeValue);
                            $params['email'] = $xpath->query('./Email', $users)->item(0)->nodeValue;
                            $params['gender'] = self::map_gender($xpath->query('./Gender', $users)->item(0)->nodeValue, $params['title']);
                            $params['course'] = $xpath->query('./PlanID', $users)->item(0)->nodeValue;
                            $params['year'] = self::map_yearofstudy($xpath->query('./YearOfStudy', $users)->item(0)->nodeValue);
                        } catch (\exception $e) {
                            // If user data not provided skip to next user.
                            continue;
                        }
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
     * Function to map gender supplied by CS to gender in ExamSys
     * @param string $csgender gender in CS
     * @param string $title title in ExamSys
     * @return string|null ExamSys gender or null if not mapped
     */
    public static function map_gender($csgender, $title)
    {
         // Possible Genders from CS
         //  F - Female
         //  M - Male
         //  O - Other
         //  U - Unknown
         //  X - Intersex
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
                // Use title in ExamSys to assume gender.
                $gender = self::title_to_gender($title);
                break;
        }
        return $gender;
    }

    /**
     * Function to map title supplied by CS to title in ExamSys
     * @param string $cstitle title in CS
     * @return string|null ExamSys title or null if not mapped
     */
    public static function map_title($cstitle)
    {
        // Valid ExamSys titles Mx|Mr|Mrs|Miss|Ms|Dr|Professor
        if (preg_match('/^(Mx|Mr|Mrs|Miss|Ms|Dr|Professor)$/', $cstitle)) {
            $title = $cstitle;
        } else {
            $title = null;
        }
        return $title;
    }

    /**
     * Function to map title to gender
     * @param string $csgender gender in CS
     * @param string $title title in ExamSys
     * @return string|null ExamSys gender or null if not mapped
     */
    public static function title_to_gender($title)
    {
        // Valid ExamSys titles Mx|Mr|Mrs|Miss|Ms|Dr|Professor
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
     * Function to map status supplied by CS to role in ExamSys
     * @param string $csstatus status in CS
     * @param integer $userid users ExamSys internal id
     * @param \mysqli $db database connection
     * @return string|null ExamSys role or null if not mapped
     */
    public static function map_student_status($csstatus, $userid, $db)
    {
        // Users locked internally in ExamSys can only be unlocked manually within ExamSys.
        if (\UserUtils::has_user_role($userid, 'Locked', $db)) {
            return 'Locked';
        }

        // Possible Statuses from CS
        // AC  Active in Program
        // AD  Admitted - should not be sent to ExamSys so deafults to suspended
        // AP  Applicant - should not be sent to ExamSys so deafults to suspended
        // CM  Completed Program
        // CN  Cancelled
        // DC  Discontinued
        // DE  Deceased
        // DM  Dismissed
        // LA  Leave of Absence - leave as Student role
        // PM  Prematriculant - should not be sent to ExamSys so deafults to suspended
        // SP  Suspended
        // WT  Waitlisted - should not be sent to ExamSys so deafults to suspended
        switch ($csstatus) {
            case 'CN':
            case 'DC':
            case 'DE':
            case 'DM':
                $role = 'left';
                break;
            case 'CM':
                $role = 'graduate';
                break;
            case 'AD':
            case 'AP':
            case 'PM':
            case 'SP':
            case 'WT':
                $role = 'Suspended';
                break;
            default:
                $role = 'Student';
                break;
        }
        return $role;
    }

    /**
     * Function to map year of study supplied by CS to year of study in ExamSys
     * @param string $csyear year in CS
     * @return string|null ExamSys year or null if not mapped
     */
    public static function map_yearofstudy($csyear)
    {
        // Possible Statuses from CS
        // 00-06 - undergraduate year as zero padded integer, maps to single digit integer
        // PGT - not releveant to ExamSys so map to null
        // PGR - not releveant to ExamSys so map to null
        // FND - maps to 0
        if (preg_match('/^0[0-6]$/', $csyear)) {
            $year = substr($csyear, 1);
        } elseif ($csyear == 'FND') {
            $year = 0;
        } else {
            $year = null;
        }
        return $year;
    }
}
