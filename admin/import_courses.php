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

/**
* Admin screen to import courses
*
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

use plugins\SMS\plugin_cs_sms\plugin_cs_sms;

require '../../../../include/sysadmin_auth.inc';
set_time_limit(0);
$userObj = \UserObject::get_instance();
$sms = new plugin_cs_sms($userObj->get_user_ID());
// Get courses.
$sms->get_courses();
header('location: ' . $configObject->get('cfg_root_path') . '/admin/list_courses.php', true, 303);
exit();
