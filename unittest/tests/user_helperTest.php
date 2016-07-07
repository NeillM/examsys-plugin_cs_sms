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

use testing\unittest\unittest,
    plugins\SMS\plugin_cs_sms\user_helper as user_helper;
/**
 * Test user helper functions
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class user_helpertest extends UnitTest {
    /**
     * Test map gender
     * @group sms
     */
    public function test_map_gender() {
        // Other gender.
        $this->assertEquals('Other', user_helper::map_gender('X', 'Mx'));
        // Male gender.
        $this->assertEquals('Male', user_helper::map_gender('M', 'Mr'));
        // Female gender.
        $this->assertEquals('Female', user_helper::map_gender('F', 'Miss'));
        // Male gender assumed from title
        $this->assertEquals('Male', user_helper::map_gender('', 'Mr'));
        // Unkown gender
        $this->assertEquals(null, user_helper::map_gender('', 'Prof'));
    }
    /**
     * Test map title
     * @group sms
     */
    public function test_map_title() {
        // Known title.
        $this->assertEquals('Professor', user_helper::map_title('Professor'));
        // Unknown title.
        $this->assertEquals(null, user_helper::map_title('Prof'));
    }
    /**
     * Test map title to gender
     * @group sms
     */
    public function test_title_to_gender() {
        // Other gender.
        $this->assertEquals('Other', user_helper::title_to_gender('Mx'));
        // Male gender.
        $this->assertEquals('Male', user_helper::title_to_gender('Mr'));
        // Female gender.
        $this->assertEquals('Female', user_helper::title_to_gender('Miss'));
        // Null gender
        $this->assertEquals(null, user_helper::title_to_gender('Dr'));
    }
    /**
     * Test map student status
     * @group sms
     */
    public function test_map_student_status() {
        // User Cancelled.
        $this->assertEquals('Left', user_helper::map_student_status('CN'));
        // User Discontinued.
        $this->assertEquals('Left', user_helper::map_student_status('DC'));
        // User Deceased.
        $this->assertEquals('Left', user_helper::map_student_status('DE'));
        // User Dismissed.
        $this->assertEquals('Left', user_helper::map_student_status('DM'));
        // Completed Program.
        $this->assertEquals('Graduate', user_helper::map_student_status('CM'));
        // User Admitted.
        $this->assertEquals('Suspended', user_helper::map_student_status('AD'));
        // User Applicant.
        $this->assertEquals('Suspended', user_helper::map_student_status('AP'));
        // User Leave of absence.
        $this->assertEquals('Suspended', user_helper::map_student_status('LA'));
        // User Prematriculant.
        $this->assertEquals('Suspended', user_helper::map_student_status('PM'));
        // User Suspended.
        $this->assertEquals('Suspended', user_helper::map_student_status('SP'));
        // User Waitlisted.
        $this->assertEquals('Suspended', user_helper::map_student_status('WT'));
        // User Active.
        $this->assertEquals('Student', user_helper::map_student_status('AC'));
    }
    /**
     * Test map year of study
     * @group sms
     */
    public function test_map_yearofstudy() {
        // Valid year.
        $this->assertEquals(6, user_helper::map_yearofstudy(6));
        // Invalid year.
        $this->assertEquals(null, user_helper::map_yearofstudy(7));
    }
}
