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
* Admin screen to import modules
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

use plugins\SMS\plugin_cs_sms\plugin_cs_sms;

define('AJAX_REQUEST', true);

require '../../../../include/sysadmin_auth.inc';
require '../../../../include/errors.php';

set_time_limit(0);

$session = \param::required('session', \param::INT, \param::FETCH_POST);
$id = \param::required('id', \param::ALPHANUM, \param::FETCH_POST);
$yearutils = new \yearutils($mysqli);
$supported_sessions = $yearutils->get_supported_years();
if (!array_key_exists($session, $supported_sessions)) {
    echo json_encode("ERROR");
    exit();
}

$userObj = \UserObject::get_instance();
$sms = new plugin_cs_sms($userObj->get_user_ID());
// Get modules.
$sms->get_modules();
// Get enrolments.
$sms->get_enrolments($session, $id);
echo json_encode("SUCCESS");
