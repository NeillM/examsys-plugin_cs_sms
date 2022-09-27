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
* SMS plugin helper file
*
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * SMS import plugin.
 */
class plugin_cs_sms extends \plugins\plugins_sms
{
    /**
     * Name of the plugin;
     * @var string
     */
    protected $plugin = 'plugin_cs_sms';

    /**
     * Language pack component.
     * @var string
     */
    protected $langcomponent = 'plugins/SMS/plugin_cs_sms/plugin_cs_sms';

    /**
     * Land pack strings.
     * @var string
     */
    private $strings;

    /**
     * User running import.
     * @var integer
     */
    private $userid;

    /**
     * Schema validation status.
     * @var boolean
     */
    private $validation;

    /**
     * List of campuses.
     * @var array
     */
    private $campuslist;

    /**
     * Name of external student management system.
     * @var string
     */
    public const SMS = 'Campus Solutions';

    /**
     * Campus Solutions web service version.
     * @var string
     */
    public const CSVERSIONONE = 'v1';

    /**
     * Set the availbe land pack strings for the plugin
     */
    private function set_lang_strings()
    {
        $langpack = new \langpack();
        $this->strings = $langpack->get_all_strings($this->langcomponent);
    }

    /**
     * Is the plugin function configured
     * @param string $function name of function
     * @return boolean true if configured
     */
    private function is_configured($function)
    {
        $configured = $this->config->get_setting($this->plugin, 'enable_' . $function);
        if (!is_null($configured) and $configured == true) {
            return true;
        }
        return false;
    }

    /**
     * Is this plugin enabled
     * @return boolean true if enabled
     */
    private function is_enabled()
    {
        $enabledplugins = \plugin_manager::get_plugin_type_enabled('plugin_' . $this->plugin_type);
        if (in_array($this->plugin, $enabledplugins)) {
            return true;
        }
        return false;
    }

    /**
     * Constructor
     * @param \mysqli $mysqli db connection
     * @param integer $userid ExamSys id of user running import
     */
    public function __construct($userid = 0)
    {
        parent::__construct();
        $this->set_lang_strings();
        $this->logdir = $this->config->get_setting($this->plugin, 'loglocation');
        $this->userid = $userid;
        $this->campuslist = explode(',', ($this->config->get_setting($this->plugin, 'campuslist')));
        $this->validation = $this->config->get_setting($this->plugin, 'validate_schema');
        $this->gradebookdir = $this->config->get_setting($this->plugin, 'gradebooklocation');
    }

    /**
     * Call web service to retrieve information.
     * @param string $type type of web service to call i.e. RogoProgPlan for courses
     * @param string $version version of web service.
     * @param array $args any arguments to call the web service with
     * arguments should be in the following order if given - academic_session, campus, externalid
     * @return string xml data from web service
     */
    public function callws($type, $version, $args = array())
    {
        $url = $this->config->get_setting($this->plugin, 'url');
        $url .= '/' . $type . '.' . $version . '/';
        foreach ($args as $param => $value) {
            $url .=  $value . '/';
        }
        // Strip last &.
        $url = rtrim($url, '/');
        $username = $this->config->get_setting($this->plugin, 'username');
        $password = $this->config->get_setting($this->plugin, 'password');
        $timeout = $this->config->get_setting($this->plugin, 'timeout');
        $options = array(CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => $this->config->get_setting($this->plugin, 'ssl_verify')
        );
        // Auth options.
        if ($username != '') {
            $authoptions = array(CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => $username . ':' . $password);
            $options += $authoptions;
        }
        $restful = new \restful($this->db);
        $response = $restful->get($url, $options);
        return $response;
    }

    /**
     * Get all assessments for academic session
     * @params integer $session academic session to sync assessments with
     */
    public function get_assessments($session)
    {
        if (!$this->is_enabled() or !$this->is_configured('assessment')) {
            return;
        }
        // Check if sync already running.
        $lockfile = $this->config->get('cfg_tmpdir') . DIRECTORY_SEPARATOR . 'assessment.lock';
        $lifespan = $this->config->get_setting($this->plugin, 'lockfile_lifespan');
        lockfile_helper::lockfiletimeout($lockfile, $lifespan);
        if (!file_exists($lockfile)) {
            file_put_contents($lockfile, time());
            $logfile = log_helper::set_logfile($this->logdir, 'assessment');
            foreach ($this->campuslist as $campus) {
                $args = array('academic_session' => $session, 'campus' => $campus);
                $response = $this->callws('RogoAssessments', self::CSVERSIONONE, $args);
                if ($response != '') {
                    assessments_helper::process($response, $this->userid, $this->strings, $this->db, $logfile, $session, $this->validation, $args);
                }
            }
            unlink($lockfile);
        }
    }

    /**
     * Get enrolments for academic session
     * @params integer $session academic session to sync enrolments with
     * @params integer $externalid external system module id
     */
    public function get_enrolments($session = null, $externalid = null)
    {
        if (!$this->is_enabled() or !$this->is_configured('enrolment')) {
            return;
        }
        // Check if sync already running.
        $lockfile = $this->config->get('cfg_tmpdir') . DIRECTORY_SEPARATOR . 'enrolment.lock';
        $lifespan = $this->config->get_setting($this->plugin, 'lockfile_lifespan');
        lockfile_helper::lockfiletimeout($lockfile, $lifespan);
        if (!file_exists($lockfile)) {
            file_put_contents($lockfile, time());
            $logfile = log_helper::set_logfile($this->logdir, 'enrol');
            $targeted = $this->config->get_setting($this->plugin, 'target_module_enrolments');
            $active = $this->config->get_setting($this->plugin, 'active_modules_only');
            // If external id is provided we can select the specific campus to call.
            if (!is_null($externalid)) {
                try {
                    $campuses[] = modules_helper::get_campus_code($externalid);
                } catch (\Exception $e) {
                    // Module not in system so have to check all campuses.
                    $campuses = $this->campuslist;
                }
            } else {
                $campuses = $this->campuslist;
            }
            $yearutils = new \yearutils($this->config->db);
            foreach ($campuses as $campus) {
                $args = array('academic_session' => $session, 'campus' => $campus);
                // Targeted list of modules.
                if (is_null($externalid) and $targeted) {
                    $targetmodules = modules_helper::get_target_modules($campus, $active);
                    foreach ($targetmodules as $eid) {
                        $args['externalid'] = $eid;
                        // Get current academic session for module it non provided.
                        if (is_null($args['academic_session'])) {
                            $modid = \module_utils::get_id_from_externalid($eid, plugin_cs_sms::SMS, $this->config->db);
                            $args['academic_session'] = $yearutils->get_current_session(
                                \module_utils::getAcademicYearStart($modid)
                            );
                        }
                        $response = $this->callws('RogoEnrolments', self::CSVERSIONONE, $args);
                        if ($response != '') {
                            enrolments_helper::process($response, $this->userid, $this->strings, $this->db, $logfile, $args['academic_session'], $this->validation, $active, $args);
                        }
                    }
                } else {
                    // Specific module.
                    if (!is_null($externalid)) {
                        $args['externalid'] = $externalid;
                    }
                    // Get current academic session for module it non provided.
                    if (is_null($args['academic_session'])) {
                        $modid = \module_utils::get_id_from_externalid($eid, plugin_cs_sms::SMS, $this->config->db);
                        $args['academic_session'] = $yearutils->get_current_session(
                            \module_utils::getAcademicYearStart($modid)
                        );
                    }
                    $response = $this->callws('RogoEnrolments', self::CSVERSIONONE, $args);
                    if ($response != '') {
                        enrolments_helper::process($response, $this->userid, $this->strings, $this->db, $logfile, $args['academic_session'], $this->validation, $active, $args);
                    }
                }
            }
            unlink($lockfile);
        }
    }

    /**
     * Update module in an academic session
     * Updates module details and enrolments
     * @params integer $externalid external system module id
     * @params integer $session academic session to sync enrolments with
     */
    public function update_module_enrolments($externalid, $session)
    {
        if (!$this->is_enabled()) {
            return;
        }
        $this->get_modules($externalid, $session);
        $this->get_enrolments($session, $externalid);
    }

    /**
     * Get faculties/schools.
     */
    public function get_faculties()
    {
        if (!$this->is_enabled() or !$this->is_configured('faculty')) {
            return;
        }
        // Check if sync already running.
        $lockfile = $this->config->get('cfg_tmpdir') . DIRECTORY_SEPARATOR . 'faculty.lock';
        $lifespan = $this->config->get_setting($this->plugin, 'lockfile_lifespan');
        lockfile_helper::lockfiletimeout($lockfile, $lifespan);
        if (!file_exists($lockfile)) {
            file_put_contents($lockfile, time());
            $logfile = log_helper::set_logfile($this->logdir, 'faculty');
            $currentfaculties = array();
            $currentschools = array();
            foreach ($this->campuslist as $campus) {
                $args = array('faculty' => '', 'campus' => $campus);
                $response = $this->callws('RogoSchools', self::CSVERSIONONE, $args);
                if ($response != '') {
                    $faculties = faculties_helper::process($response, $this->userid, $this->strings, $this->db, $logfile, $this->validation, $args);
                    if ($faculties !== false) {
                        $currentfaculties = array_merge($currentfaculties, $faculties[0]);
                        $currentschools = array_merge($currentschools, $faculties[1]);
                    }
                }
            }
            // Delete faculties and schools no longer in CS.
            faculties_helper::delete_faculties_schools($currentschools, $currentfaculties, $logfile, $this->userid, $this->db);
            unlink($lockfile);
        }
    }

    /**
     * Get courses
     */
    public function get_courses()
    {
        if (!$this->is_enabled() or !$this->is_configured('course')) {
            return;
        }
        // Check if sync already running.
        $lockfile = $this->config->get('cfg_tmpdir') . DIRECTORY_SEPARATOR . 'course.lock';
        $lifespan = $this->config->get_setting($this->plugin, 'lockfile_lifespan');
        lockfile_helper::lockfiletimeout($lockfile, $lifespan);
        if (!file_exists($lockfile)) {
            file_put_contents($lockfile, time());
            $logfile = log_helper::set_logfile($this->logdir, 'course');
            $currentplans = array();
            foreach ($this->campuslist as $campus) {
                $args = array('session' => '', 'campus' => $campus);
                $response = $this->callws('RogoProgPlan', self::CSVERSIONONE, $args);
                if ($response != '') {
                    $plans = courses_helper::process($response, $this->userid, $this->strings, $this->db, $logfile, $this->validation, $args);
                    if ($plans !== false) {
                        $currentplans = array_merge($currentplans, $plans);
                    }
                }
            }
            // Delete courses no longer in CS.
            courses_helper::delete_courses($currentplans, $logfile, $this->userid, $this->db);
            unlink($lockfile);
        }
    }

    /**
     * Get modules
     * @params integer $externalid external system module id
     * @params integer $session academic session for the module
     */
    public function get_modules($externalid = null, $session = null)
    {
        if (!$this->is_enabled() or !$this->is_configured('module')) {
            return;
        }
        // Check if sync already running.
        $lockfile = $this->config->get('cfg_tmpdir') . DIRECTORY_SEPARATOR . 'module.lock';
        $lifespan = $this->config->get_setting($this->plugin, 'lockfile_lifespan');
        lockfile_helper::lockfiletimeout($lockfile, $lifespan);
        if (!file_exists($lockfile)) {
            file_put_contents($lockfile, time());
            $args = array();
            $logfile = log_helper::set_logfile($this->logdir, 'module');
            $singleexternal = false;
            $currentmodules = array();
            // If external id is provided we can select the specific campus to call.
            if (!is_null($externalid)) {
                try {
                    $campuses[] = modules_helper::get_campus_code($externalid);
                } catch (\Exception $e) {
                    // Module not in system so have to check all campuses.
                    $campuses = $this->campuslist;
                }
            } else {
                $campuses = $this->campuslist;
            }
            foreach ($campuses as $campus) {
                if (!is_null($externalid) and !is_null($session)) {
                    $args = array('academic_session' => $session, 'externalid' => $externalid, 'campus' => $campus);
                    $singleexternal = true;
                } else {
                    $args = array('academic_session' => '', 'externalid' => '', 'campus' => $campus);
                }
                $response = $this->callws('RogoClasses', self::CSVERSIONONE, $args);
                if ($response != '') {
                    $modules = modules_helper::process($response, $this->userid, $this->strings, $logfile, $this->validation, $args);
                    if ($modules !== false) {
                        $currentmodules = array_merge($currentmodules, $modules);
                    }
                }
            }
            // Check if delete modules is enabled.
            $delete = $this->config->get_setting($this->plugin, 'enable_delete_modules');
            // Do not diff modules on single module update.
            if (!$singleexternal and $delete) {
                // Delete modules no longer in CS
                modules_helper::delete_modules($currentmodules, $logfile, $this->userid);
            }
            unlink($lockfile);
        }
    }

    /**
     * Write a gradebook for an academic session to a file to be processed by campus solutions.
     * @param integer $session academic session to publish gradebook for
     */
    public function publish_gradebook($session)
    {
        if (!$this->is_enabled() or !$this->is_configured('gradebook') or $this->gradebookdir == '') {
            return;
        }
        // Check if export is already running.
        $lockfile = $this->config->get('cfg_tmpdir') . DIRECTORY_SEPARATOR . 'gradebook.lock';
        $lifespan = $this->config->get_setting($this->plugin, 'lockfile_lifespan');
        lockfile_helper::lockfiletimeout($lockfile, $lifespan);
        if (!file_exists($lockfile)) {
            file_put_contents($lockfile, time());
            gradebook_helper::publish($session, $this->gradebookdir, $this->get_path());
            unlink($lockfile);
        }
    }

    /**
     * Check if module import is supported by the plugin
     * @return array|bool import url and translation strings, false  if module import not supported
     */
    public function supports_module_import()
    {
        if ($this->is_configured('module') or $this->is_configured('enrolment')) {
            return array('url' => $this->config->get('cfg_root_path') . '/plugins/SMS/' . $this->plugin . '/admin/import_modules.php', 'blurb' => $this->strings['importmodules'], 'tooltip' => $this->strings['importmodulestooltip']);
        } else {
            return false;
        }
    }

    /**
     * Check if faculty/school import is supported by the plugin
     * @return array|bool import url and translation strings, false if faculty/school import not supported
     */
    public function supports_faculty_import()
    {
        if ($this->is_configured('faculty')) {
            return array('url' => $this->config->get('cfg_root_path') . '/plugins/SMS/' . $this->plugin . '/admin/import_faculties.php', 'blurb' => $this->strings['importfaculties'], 'tooltip' => $this->strings['importfacultiestooltip']);
        } else {
            return false;
        }
    }

    /**
     * Check if course import is supported by the plugin
     * @return array|bool import url and translation strings, false  if course import not supported
     */
    public function supports_course_import()
    {
        if ($this->is_configured('course')) {
            return array('url' => $this->config->get('cfg_root_path') . '/plugins/SMS/' . $this->plugin . '/admin/import_courses.php', 'blurb' => $this->strings['importcourses'], 'tooltip' => $this->strings['importcoursestooltip']);
        } else {
            return false;
        }
    }

    /**
     * Check if enrolment import is supported by the plugin
     * @return array|bool false if enrolment import not supported
     */
    public function supports_enrol_import()
    {
        if ($this->is_configured('enrolment')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if assessment import is supported by the plugin
     * @return array|bool import url and translation strings, false  if assessment import not supported
     */
    public function supports_assessment_import()
    {
        if ($this->is_configured('assessment')) {
            return array('url' => $this->config->get('cfg_root_path') . '/plugins/SMS/' . $this->plugin . '/admin/import_assessments.php', 'blurb' => $this->strings['importassessments'], 'tooltip' => $this->strings['importassessmentstooltip']);
        } else {
            return false;
        }
    }

    /**
     * Get name of sms
     * @return string name of sms
     */
    public function get_name()
    {
        return self::SMS;
    }
}
