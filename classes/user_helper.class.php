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
     * Intergrate the user node of the membership xml
     * @param array $users users array to populate
     * @param simpleXMLObject $membership xml for user membership
     * @param simpleXMLObject $parentnode xml for enrolment
     */
    static public function get_users(&$users, $membershipnode, $parentnode) {
        foreach ($parentnode->childNodes as $childnode) {
            if ($childnode->nodeName == 'ModuleID') {
                $moduleid = $childnode->nodeValue;
            }
        }
        $i = 0;
        foreach ($membershipnode as $membership) {
            // Should only be user nodes
            if ($membership->nodeName == 'User') {
                foreach ($membership->childNodes as $userdetails) {
                    $users[$moduleid][$i][$userdetails->nodeName] = $userdetails->nodeValue;
                }
                $i++;
            }
        }
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