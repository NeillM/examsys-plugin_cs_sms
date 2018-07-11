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

/**
 *
 * Script for processing Campus Solutions Web Services to create/update faculties/schools
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use plugins\SMS\plugin_cs_sms\plugin_cs_sms;

// Only run from the command line!
if (PHP_SAPI != 'cli') {
  die("Please run this script from the CLI!\n");
}

set_time_limit(0);

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/include/load_config.php';
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/include/custom_error_handler.inc';

// Start class autoloading.
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/include/autoload.inc.php';
autoloader::init();

$configObject = \Config::get_instance();
$notice = UserNotices::get_instance();

$mysqli = \DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $configObject->get('cfg_db_sysadmin_user'),
    $configObject->get('cfg_db_sysadmin_passwd'), $configObject->get('cfg_db_database'), $configObject->get('cfg_db_charset'),
    $notice, $configObject->get('dbclass'));

$configObject->set_db_object($mysqli);
// Run sms if enabled.
$sms = new plugin_cs_sms(0);
$sms->get_faculties();
$mysqli->close();
