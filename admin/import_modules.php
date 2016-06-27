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

require '../../../../include/sysadmin_auth.inc';
require '../../../../include/errors.inc';

set_time_limit(0);

$session = check_var('session', 'GET', true, false, true);
$yearutils = new \yearutils($mysqli);
$supported_sessions = $yearutils->get_supported_years();
if (!array_key_exists($session, $supported_sessions)) {
    exit();
}

$userObj = \UserObject::get_instance();
$mappingplugin_name = plugin_manager::get_plugin_type_enabled('plugin_sms');
if (count($mappingplugin_name) > 0) {
    if ($mappingplugin_name[0] == 'plugin_cs_sms') {
        $sms = new plugin_cs_sms($mysqli, $userObj->get_user_ID());
        // Get modules.
        $sms->get_modules();
        // Get enrolments.
        $sms->get_enrolments($session);
        header("location: " . $configObject->get('cfg_root_path') . "/admin/list_modules.php", true, 303);
    }
}
exit();