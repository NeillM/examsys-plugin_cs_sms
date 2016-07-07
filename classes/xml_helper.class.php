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
* Xml helper file
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * Xml helper class.
 */
class xml_helper {
    /**
     * Check if xml response is an error.
     * @param DOMDocument $data xml response
     * @param integer $userid user used to log error to
     * @param mysqli $db db connection
     * @return boolean true on error
     */
    static public function check_for_error($data, $userid, $db) {
        $errornode = $data->getElementsByTagName('Error');
        foreach ($errornode as $error) {
            $xpath = new \DOMXPath($error->ownerDocument);
            $header = $xpath->query('./Header', $error)->item(0)->nodeValue;
            if (!is_null($header)) {
                $errorline = __LINE__ - 1;
                log_helper::log_app_warning($userid, $header, $errorline, $db);
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if xml validates against schema
     * @param DOMDocument $data xml response
     * @param string $schemaname name of schema file
     * @param integer $userid user used to log error to
     * @param mysqli $db db connection
     * @return boolean true if valid xml
     */
    static public function validate($data, $schemaname, $userid, $strings, $db) {
        // Enable user error handling.
        libxml_use_internal_errors(true);
        $schema = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . $schemaname . '.xsd';
        if (!$data->schemaValidate($schema)) {
            $errorline = __LINE__ - 1;
            log_helper::log_app_warning($userid, $strings['restnotvalid'], $errorline, $db);
            return false;
        }
        // Disable user error handling.
        libxml_use_internal_errors(false);
    }
}