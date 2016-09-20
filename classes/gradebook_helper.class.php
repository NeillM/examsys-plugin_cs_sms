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
* Gradebook publishing file
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * Gradebook helper class.
 */
class gradebook_helper {
    
    /**
     * Campus Solutions Result status type 'imported'
     * @var string
     */
    const RESULTSTATUS_IMPORTED = '07-Imported';
    /**
     * Campus Solutions Result type 'AM'
     * @var string
     */
    const RESULTTYPE_AM = 'AM Result';
    /**
     * Publish whole gradebook for an academic session to a file
     * @param mysqli $db database connection
     * @param integer $session academic year to publish gradebook for
     * @param string $gradebookdir path to directory to write file
     * @param object $configObject config object
     * @param string $path path to plugin
     */
    static public function publish($db, $session, $gradebookdir, $configObject, $path) {
        $render = new \render($configObject, $path . DIRECTORY_SEPARATOR . 'templates');
        // Only interested in summative papers.
        $papers = \Paper_utils::get_papers_by_session($session, '2', $db);
        $gradebookarray = array();
        foreach ($papers as $paper_id) {
            $g = self::get_paper_gradebook($paper_id, $db);
            if ($g !== false) {
                 $gradebookarray = array_merge($gradebookarray, $g);
            }
        }
        if (count($gradebookarray) !== 0) {
            $response_xml = $render->render_xml('gradebook.xml', 'UON_AssessmentResults', $gradebookarray);
            if ($configObject->get_setting('plugin_cs_sms', 'gradebook_md5')) {
                $suffix = md5($response_xml);
            } else {
                $suffix = date("YmdHis");
            }
            $logfile = $gradebookdir . DIRECTORY_SEPARATOR . 'ROGO-' . $session . '-' . $suffix . '.xml';
            // If md5 enabled we only write a file if a change has occured i.e. a grade has been added
            // If md5 is disabled we only write a file if the datetime has changed which is essentially always
            if(!file_exists($logfile)) {
                file_put_contents($logfile, $response_xml);
            }
        }
    }

    /**
     * Get gradebook gradebook for paper
     * @param integer $paper_id paper identifier
     * @param mysqli $db db connection
     * @return array|bool gradebook or false if non
     */
    static private function get_paper_gradebook($paper_id, $db) {
        $response = array();
        $gradebook = new \gradebook($db);
        $paperdetails = \Paper_utils::get_paper_properties($paper_id, $db);
        $activityid = $paperdetails['externalid'];
        $activitysys = $paperdetails['externalsys'];
        // Only interested in campus solutions assessments.
        if (is_null($activityid) or $activitysys != plugin_cs_sms::SMS) {
            return false;
        }
        $activitydesc = $paperdetails['title'];
        $resultstatus = self::RESULTSTATUS_IMPORTED;
        $resulttype = self::RESULTTYPE_AM;
        $submissiondate = $paperdetails['enddatetime'];
        $grades = $gradebook->get_paper_gradebook(\gradebook::PAPER, $paper_id);
        if ($grades !== false) {
            foreach ($grades as $paperidx => $paper) {
                foreach ($paper as $useridx => $user) {
                    $userdetails = \UserUtils::get_user_details($useridx, $db);
                    $studentid = $userdetails['student_id'];
                    $lastname = $userdetails['surname'];
                    $firstname = $userdetails['first_names'];
                    $mark = $user['adjusted_grade'];
                    $modules = \module_utils::get_modules_for_paper($paper_id, $useridx, $db);
                    // Module level info.
                    foreach ($modules as $module) {
                        $moduledetails = \module_utils::get_full_details_by_ID($module, $db);
                        $courseid = $moduledetails['externalid'];
                        $coursedesc = $moduledetails['fullname'];
                        $coursesubject = $moduledetails['moduleid'];
                        $response[] = array(
                            'courseid' => $courseid,
                            'coursedesc' => $coursedesc,
                            'coursesubject' => $coursesubject,
                            'activityid' => $activityid,
                            'activitydesc' => $activitydesc,
                            'studentid' => $studentid,
                            'lastname' => $lastname,
                            'firstname' => $firstname,
                            'mark' => $mark,
                            'resultstatus' => $resultstatus,
                            'resulttype' => $resulttype,
                            'submissiondate' => $submissiondate,
                            'duedate' => $submissiondate);
                    }
                }
            }
        }
        return $response;
    }
}