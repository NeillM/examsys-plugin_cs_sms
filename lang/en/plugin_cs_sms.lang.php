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

$string['restnodata'] = 'No data returned';
$string['restnotvalid'] = 'Schema validation failed';
$string['importfaculties'] = 'Import Faculties/Schools from SMS';
$string['importfacultiestooltip'] = 'Sync Faculties and Schools with the connected Student Management System';
$string['importmodules'] = 'Import modules from SMS';
$string['importmodulestooltip']  = 'Sync Modules and Module Enrolments with the connected Student Management System';
$string['importcourses'] = 'Import courses from SMS';
$string['importcoursestooltip'] = 'Sync Courses with the connected Student Management System';
$string['importassessments'] = 'Import assessments from SMS';
$string['importassessmentstooltip'] = 'Sync Assessments with the connected Student Management System';
$string['campuslist'] = 'List of campuses (by code) that we use as parameters to the sms web service. This can be used to limit syncing to a specific campus.';
$string['enable_assessment'] = 'Enable/Disable assessment syncing.';
$string['enable_course'] = 'Enable/Disable assessment syncing.';
$string['enable_enrolment'] = 'Enable/Disable enrolment syncing.';
$string['enable_faculty'] = 'Enable/Disable faculty/school syncing.';
$string['enable_module'] = 'Enable/Disable module syncing.';
$string['loglocation'] = 'Location to log actions of the syncing process. Leave blank to disable logging.';
$string['password'] = 'The password required by the sms web service.';
$string['ssl_verify'] = 'Enable/Disable verifying the ssl connection.';
$string['timeout'] = 'Time limit in seconds before ExamSys gives up calling the web service if no response.';
$string['url'] = 'The url of the web service.';
$string['username'] = 'The username required by the web service.';
$string['validate_schema'] = 'Enable/Disable schema validation on the response from the web service.';
$string['enable_gradebook'] = 'Enable/Disable gradebook publishing.';
$string['gradebooklocation'] = 'Location to write greadebook files to be picked up by external system. Note: Leaving blank will effectively disable gradebook publishing.';
$string['gradebook_md5'] = 'Write md5 of file into file name of gradebook export (Useful for the endpoint system to verify file changes)';
$string['lockfile_lifespan'] = 'Lifespan of process lock files (in hours)';
$string['active_modules_only'] = 'Only sync enrolments to modules active in ExamSys. If enabeld the enrolment feed processing time will be reduced.';
$string['enable_delete_modules'] = 'Enabled/Disable deletion of modules during module sync.';
$string['target_module_enrolments'] = 'Enable targeted enrolment sync. At the cost of additional API calls to the SMS.';
