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

use testing\unittest\unittestdatabase;
use PHPUnit\DbUnit\DataSet\YamlDataSet;
use plugins\SMS\plugin_cs_sms\xml_helper as xml_helper;
/**
 * Test xml helper functions
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class xml_helpertest extends unittestdatabase {
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new YamlDataSet(dirname(__DIR__) . DIRECTORY_SEPARATOR  . "fixtures" . DIRECTORY_SEPARATOR . "sms.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new YamlDataSet(dirname(__DIR__) . DIRECTORY_SEPARATOR  . "fixtures" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    /**
     * Test map gender
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_check_for_error() {
        $userid = 0;
        // Error.
        $data = '<?xml version="1.0"?>
            <Error><Header>Header Info</Header><Detail>Some Details</Detail></Error>';
        $doc = new DOMDocument();
        $doc->loadXML($data);
        $this->assertTrue(xml_helper::check_for_error($doc, $userid, $this->db, 'assessment', array('academic_session' => 2017, 'campus' => 'M')));
        $queryTable = $this->getConnection()->createQueryTable('sys_errors', 'SELECT auth_user, errtype, errstr FROM sys_errors');
        $expectedTable = $this->get_expected_data_set('xmlhelper')->getTable("sys_errors");
        $this->assertTablesEqual($expectedTable, $queryTable);
        // No Error.
        $data = '<?xml version="1.0"?>
            <FacultyList></FacultyList>';
        $doc = new DOMDocument();
        $doc->loadXML($data);
        $this->assertFalse(xml_helper::check_for_error($doc, $userid, $this->db, 'assessment', array('academic_session' => 2017, 'campus' => 'M')));
    }
}
