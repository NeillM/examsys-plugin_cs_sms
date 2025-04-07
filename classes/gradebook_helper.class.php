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
* Gradebook publishing file
*
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * Gradebook helper class.
 */
class gradebook_helper
{
    /**
     * Campus Solutions Result status type 'imported'
     * @var string
     */
    public const RESULTSTATUS_IMPORTED = '07-Imported';

    /**
     * Campus Solutions Result type 'AM'
     * @var string
     */
    public const RESULTTYPE_AM = 'AM Result';

    /**
     * Gradebook object
     * @var \gradebook
     */
    private static $gradebook;

    /**
     * Publish gradebook for year to multiple files per paper
     * @param integer $session year to publish gradebook for
     * @param string $gradebookdir path to directory to write file
     * @param string $path path to plugin
     */
    public static function publish($session, $gradebookdir, $path)
    {
        $configObject = \Config::get_instance();
        $db = $configObject->db;
        $render = new \render($configObject, $path . DIRECTORY_SEPARATOR . 'templates');
        // Only interested in summative papers.
        $papers = \Paper_utils::get_finalised_papers($session, '2', $db);
        self::$gradebook = new \gradebook($db);
        foreach ($papers as $paper_id) {
            $g = self::get_paper_gradebook($paper_id, $db);
            if ($g !== false) {
                $activtyid = $g['activityid'];
                $grades = $g['grades'];
                $response_xml = $render->render_xml('gradebook.xml', 'UON_AssessmentResults', $grades);
                if ($configObject->get_setting('plugin_cs_sms', 'gradebook_md5')) {
                    $suffix = md5((string) $response_xml);
                } else {
                    $suffix = date('YmdHis');
                }
                $logfile = $gradebookdir . DIRECTORY_SEPARATOR . 'ROGO-' . $session . '-' . $activtyid . '-' . $suffix . '.xml';
                // If md5 enabled we only write a file if a change has occured i.e. a grade has been added
                // If md5 is disabled we only write a file if the datetime has changed which is essentially always
                if (!file_exists($logfile)) {
                    file_put_contents($logfile, $response_xml);
                }
            }
        }
    }

    /**
     * Get gradebook gradebook for paper
     * @param integer $paper_id paper identifier
     * @param \mysqli $db db connection
     * @return array|bool actvity id and gradebook or false if non
     */
    private static function get_paper_gradebook($paper_id, $db)
    {
        $response = [];
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
        $grades = self::$gradebook->get_user_detailed_paper_gradebook($paper_id);
        if ($grades !== false) {
            foreach ($grades as $paperidx => $paper) {
                foreach ($paper as $useridx => $userdetails) {
                    $studentid = $userdetails['student_id'];
                    $lastname = $userdetails['surname'];
                    $firstname = $userdetails['first_names'];
                    $mark = $userdetails['adjusted_grade'];
                    $modules = \module_utils::get_modules_for_paper($paper_id, $useridx, $db);
                    // Module level info.
                    foreach ($modules as $moduledetails) {
                        $courseid = $moduledetails['externalid'];
                        $coursedesc = $moduledetails['fullname'];
                        $coursesubject = $moduledetails['moduleid'];
                        $response[] = [
                            'courseid' => $courseid,
                            'coursedesc' => $coursedesc,
                            'coursesubject' => $coursesubject,
                            'activityid' => $activityid,
                            'activitydesc' => $activitydesc,
                            'studentid' => $studentid,
                            'lastname' => $lastname,
                            'firstname' => $firstname,
                            'mark' => self::round_up_negative($mark),
                            'resultstatus' => $resultstatus,
                            'resulttype' => $resulttype,
                            'submissiondate' => $submissiondate,
                            'duedate' => $submissiondate];
                    }
                }
            }
        }
        return ['activityid' => $activityid, 'grades' => $response];
    }

    /**
     * Round up neagtive marks.
     * CS cannot handle negative marks so we round up to zero
     * @param integer $mark mark to round up
     */
    private static function round_up_negative($mark)
    {
        if ($mark < 0) {
            return 0;
        }
        return $mark;
    }
}
