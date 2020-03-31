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
use plugins\SMS\plugin_cs_sms\xml_helper as xml_helper;

/**
 * Test xml helper functions
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class xml_helpertest extends unittestdatabase
{



    /**
     * Generate data for test.
     */
    public function datageneration(): void
    {
        // Currently only base data required.
    }

    /**
     * Test map gender
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_check_for_error()
    {
        $userid = 0;
// Error.
        $data = '<?xml version="1.0"?>
            <Error><Header>Header Info</Header><Detail>Some Details</Detail></Error>';
        $doc = new DOMDocument();
        $doc->loadXML($data);
        $this->assertTrue(xml_helper::check_for_error($doc, $userid, $this->db, 'assessment', array('academic_session' => 2017, 'campus' => 'M')));
        $queryTable = $this->query(array('columns' => array('auth_user', 'errtype', 'errstr'), 'table' => 'sys_errors'));
        $expectedTable = array(
            0 => array (
                'auth_user' => 'plugin_cs_sms',
                'errtype' => 'Application Warning',
                'errstr' => 'assessment - Header Info'
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
// No Error.
        $data = '<?xml version="1.0"?>
            <FacultyList></FacultyList>';
        $doc = new DOMDocument();
        $doc->loadXML($data);
        $this->assertFalse(xml_helper::check_for_error($doc, $userid, $this->db, 'assessment', array('academic_session' => 2017, 'campus' => 'M')));
    }
}
