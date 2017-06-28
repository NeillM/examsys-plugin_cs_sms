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
* Modules processig file
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * Modules helper class.
 */
class modules_helper {
    /**
     * Process modules WS response
     * @param string xml $response xml from module WS
     * @param integer $userid user to record actions under
     * @param array $strings lnaguage strings
     * @param mysqli $db db connection
     * @param string $logfile log file location
     * @param boolean $validation validate xml response against schema
     * @param array $args arguments used to call web service
     * @return boolean|array false on error, list of current module ids on success
     */
    static public function process($response, $userid, $strings, $db, $logfile, $validation, $args) {
        // Parse returned XML.
        $data = new \DOMDocument();
        $data->loadXML($response);
        if (xml_helper::check_for_error($data, $userid, $db, 'module feed', $args)) {
            return false;
        }
        if ($validation) {
            if (!xml_helper::validate($data, 'ModuleList', $userid, $strings, $db)) {
                return false;
            }
        }
        $modules = $data->getElementsByTagName('Module');
        $currentmodules = array();
        $node = 1;
        // Create / Update modules.
        $mm = new \api\modulemanagement($db);
        foreach ($modules as $module) {
            $xpath = new \DOMXPath($module->ownerDocument);
            // The ModuleID in Campus Solutions is the Module External ID in Rogo.
            try {
                $externalid = $xpath->query('./ModuleID', $module)->item(0)->nodeValue;
            } catch (\exception $e) {
                // If externalid not provided skip to next module.
                continue;
            }
            if (!is_null($externalid)) {
                $currentmodules[] = $externalid;
                $params = array();
                $params['externalid'] = $externalid;
                try {
                    $params['modulecode'] = self::module_campus_mapping($xpath->query('./ModuleCode', $module)->item(0)->nodeValue);
                    $params['name'] = $xpath->query('./Description', $module)->item(0)->nodeValue;
                    $params['schoolextid'] = $xpath->query('./SchoolID', $module)->item(0)->nodeValue;
                } catch (\exception $e) {
                    // If module data not provided skip to next module.
                    continue;
                }
                $params['nodeid'] = $node;
                $params['sms'] = plugin_cs_sms::SMS;
                $modid = \module_utils::get_id_from_externalid($externalid, plugin_cs_sms::SMS, $db);
                if ($modid) {
                    // If ExternalID exists call modulemanagement update api.
                    $response = $mm->update($params, $userid);
                    $type = 'Module Update';
                } else {
                    // If ExternalID new call modulemanagement create api.
                    $response = $mm->create($params, $userid);
                    $type = 'Module Create';
                }
                log_helper::log($type, $params, $response, $logfile);
            }
        }
        return $currentmodules;
    }

    /**
     * Map campus solutions module code to rogo module code
     * Rogo appends campus name to module code for China and Malaysia
     * @param string $sourcecode module code return by web servuce
     * @return string module code to store in rogo
     */
    static public function module_campus_mapping($sourcecode) {
        // Check if source is campus solutions module code.
        preg_match("/^(?P<module>[A-Z]{4}[F1-5][0-9]{3})(_(?P<country>U|C|M))?$/", $sourcecode, $info);
        if (count($info) > 0) {
            $modulecode = $info['module'];
            if (isset($info['country'])) {
                switch ($info['country']) {
                    case 'C':
                        $modulecode .= '_UNNC';
                        break;
                    case 'M':
                        $modulecode .= '_UNMC';
                        break;
                    default:
                        break;
                }
            }
        } else {
            // Return source module code if naming convention not recognised.
            $modulecode = $sourcecode;
        }
        return $modulecode;
    }

    /**
     * Delete modules that have been removed from CS
     * 
     * @param array $currentmodules list of module ids in CS
     * @param string $logfile log file location
     * @param integer $userid user to record actions under
     * @param mysqli $db db connection
     */
    static public function delete_modules($currentmodules, $logfile, $userid, $db) {
        $node = 1;
        $mm = new \api\modulemanagement($db);
        // Delete modules that have been removed from CS.
        $delete = \module_utils::diff_external_modules_to_internal_modules($currentmodules, plugin_cs_sms::SMS, $db);
        // Try to delete course via modulemanagement delete api.
        foreach ($delete as $deleteid) {
            $params = array();
            $params['externalid'] = $deleteid;
            $params['nodeid'] = $node;
            $node++;
            $response = $mm->delete($params, $userid);
            log_helper::log('Module Delete', $params, $response, $logfile);
        }
    }

    /**
     * Get modules in Rogo that we want to action
     * @param string $campus university campus
     * @param boolean $active filter by active modules
     * @param mysqli $db db connection
     * @return array modules
     */
    static public function get_target_modules($campus, $active, $db) {
        $sms = plugin_cs_sms::SMS;
        $modules = array();
        switch ($campus) {
            case 'C':
                $modcode = 'AND moduleid LIKE \'%_UNNC\'';
                break;
            case 'M':
                $modcode = 'AND moduleid LIKE \'%_UNMC\'';
                break;
            default:
                $modcode = 'AND moduleid NOT LIKE \'%_UNMC\' AND moduleid NOT LIKE \'%_UNNC\'';
                break;
        }
        if ($active) {
            $act = 'active = 1 AND';
        } else {
            $act = '';
        }
        $result = $db->prepare("SELECT externalid FROM modules WHERE sms = ? AND $act mod_deleted IS NULL $modcode");
        $result->bind_param('s', $sms);
        $result->execute();
        $result->store_result();
        $result->bind_result($externalid);
        while ($result->fetch()) {
            $modules[] = $externalid;
        }
        return $modules;
    }
}