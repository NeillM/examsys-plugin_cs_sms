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
     * Publish a gradebook for a paper to a file
     * @param mysqli $db database connection
     * @param integer $paper_id identifier of paper to publish gradebook for
     * @param string $gradebookdir path to directory to write file
     * @param object $configObject config object
     * @param string $path path to plugin
     */
    static public function publish($db, $paper_id, $gradebookdir, $configObject, $path) {
        $render = new \render($configObject, $path . DIRECTORY_SEPARATOR . 'templates');
        $gradebook = new \gradebook($db);
        // Only interested in summative papers.
        if (\Paper_utils::get_paper_type($paper_id, $db) == '2') {
            // Paper level info. 
            $paperdetails = \Paper_utils::get_paper_properties($paper_id, $db);
            $activityrootid = "";
            $activityid = $paperdetails['externalid'];
            // Only interested in external system assessments.
            if (!is_null($activityid)) {
                $activitydesc = $paperdetails['title'];
                $resultstatus = "07-Imported";
                $resulttype = "AM Result";
                $submissiondate = $paperdetails['enddatetime'];
                
                // User level info.
                $grades = $gradebook->get_paper_gradebook(\gradebook::PAPER, $paper_id);
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
                            $response[$useridx][$courseid] = array(
                                'coursedesc' => $coursedesc,
                                'coursesubject' => $coursesubject,
                                'activityrootid' => $activityrootid,
                                'activityid' => $activityid,
                                'activitydesc' => $activitydesc,
                                'studentid' => $studentid,
                                'lastname' => $lastname,
                                'firstname' => $firstname,
                                'mark' => $mark,
                                'resultstatus' => $resultstatus,
                                'resulttype' => $resulttype,
                                'submissiondate' => $submissiondate);
                        }
                    }
                }
                $logfile = $gradebookdir . DIRECTORY_SEPARATOR . $activityid . '.xml';
                $response_xml = $render->render_xml('paper_gradebook.xml', 'UON_AssessmentResults', $response);
                file_put_contents($logfile, $response_xml);
            }
        }
    }
    
    /**
     * Publish whole gradebook for an academic session to a file
     * @param mysqli $db database connection
     * @param integer $session academic year to publish gradebook for
     * @param string $gradebookdir path to directory to write file
     * @param object $configObject config object
     * @param string $path path to plugin
     */
    static public function publish_all($db, $session, $gradebookdir, $configObject, $path) {
        $gradebook = new \gradebook($db);
        // Only interested in summative papers.
        $papers = \Paper_utils::get_papers_by_session($session, '2', $db);
        foreach ($papers as $paper_id) {
            $grades = $gradebook->get_paper_gradebook(\gradebook::PAPER, $paper_id);
        }
    }
}