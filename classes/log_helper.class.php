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
* Logging helper file
*
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * Log class.
 */
class log_helper
{
    /**
     * Log to application error
     * @param integer $userid user to log error to
     * @param array $string error string
     * @param integer $errline line web service was called
     * @param array $args arguments used to call web service
     * @param mysqli $db db connection
     */
    public static function log_app_warning($userid, $string, $errorline, $db, $args)
    {
        $log = new \Logger($db);
        $username = 'plugin_cs_sms';
        $errorfile = $_SERVER['PHP_SELF'];
        $log->record_application_warning($userid, $username, $string, $errorfile, $errorline, $args);
    }

    /**
     * Set the log file for the was service call
     * @param string logdir log directory location
     * @param string $type type of ws call
     * @return string log file path
     */
    public static function set_logfile($logdir, $type)
    {
        if ($logdir != '') {
            if (PHP_SAPI == 'cli') {
                $type .= '-cli';
            }
            $logfile = $logdir . DIRECTORY_SEPARATOR . $type . '.log.' . date('Ymd');
        } else {
            $logfile = '';
        }
        return $logfile;
    }

    /**
     * Log response to file
     * @param string $type action type
     * @param array $request request parameters to log
     * @param array $response response data to log
     * @param string $logfile file to log to
     */
    public static function log($type, $request, $response, $logfile)
    {
        if ($logfile != '') {
            $updatelog = "\n\n" . '--' . date('YmdHis') . '--' . $type . "\n\nREQUEST  " . implode(';', $request);
            if ($response['statuscode'] !== 100) {
                // Log failure.
                $state = 'FAILURE';
            } else {
                // Log success.
                $state = 'SUCCESS';
            }
            if ($logfile != '') {
                $updatelog .= "\n\n" . 'RESPONSE  ' . $state . ' ' . $response['id'] . ' ' . $response['externalid'] . ' ' . $response['statuscode']
                    . ' ' . $response['status'];
                file_put_contents($logfile, $updatelog, FILE_APPEND);
            }
        }
    }
}
