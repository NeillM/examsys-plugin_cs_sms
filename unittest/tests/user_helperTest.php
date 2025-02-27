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

use testing\unittest\unittestdatabase;
use plugins\SMS\plugin_cs_sms\user_helper as user_helper;

/**
 * Test user helper functions
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class user_helpertest extends unittestdatabase
{
    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('users', 'core');
        $user = $datagenerator->create_user(['surname' => 'tester', 'username' => 'testy1', 'roles' => 'Student', 'sid' => 'dgsfg345235b']);
        $this->uid1 = $user['id'];
        $user = $datagenerator->create_user(['surname' => 'tester', 'username' => 'testy2', 'roles' => 'Student', 'sid' => 'dgsfg345235c']);
        $this->uid2 = $user['id'];
        $user = $datagenerator->create_user(['surname' => 'tester', 'username' => 'testy3', 'roles' => 'Student', 'sid' => 'dgsfg345235b']);
        $this->uid3 = $user['id'];
        $user = $datagenerator->create_user(['surname' => 'tester', 'username' => 'testy4', 'roles' => 'Student', 'sid' => 'dgsfg345235c']);
        $this->uid4 = $user['id'];
        $user = $datagenerator->create_user(['surname' => 'tester', 'username' => 'testy5', 'roles' => 'Student', 'sid' => 'dgsfg345235d']);
        $this->uid5 = $user['id'];
        $user = $datagenerator->create_user(['surname' => 'tester', 'username' => 'testy6', 'roles' => 'Student', 'sid' => 'dgsfg345235e']);
        $this->uid6 = $user['id'];
        $user = $datagenerator->create_user(['surname' => 'tester', 'username' => 'testy7', 'roles' => 'Student', 'sid' => 'dgsfg345235f']);
        $this->uid7 = $user['id'];
        $user = $datagenerator->create_user(['surname' => 'tester', 'username' => 'testy8', 'roles' => 'Student', 'sid' => 'dgsfg345235g']);
        $this->uid8 = $user['id'];
        $user = $datagenerator->create_user(['surname' => 'tester', 'username' => 'testy9', 'roles' => 'Student', 'sid' => 'dgsfg345235h']);
        $this->uid9 = $user['id'];
        $user = $datagenerator->create_user(['surname' => 'tester', 'username' => 'testy10', 'roles' => 'Student', 'sid' => 'dgsfg345235i']);
        $this->uid10 = $user['id'];
        $user = $datagenerator->create_user(['surname' => 'tester', 'username' => 'testy11', 'roles' => 'Student', 'sid' => 'dgsfg345235j']);
        $this->uid11 = $user['id'];
        $user = $datagenerator->create_user(['surname' => 'tester', 'username' => 'testy12', 'roles' => 'Suspended', 'sid' => 'dgsfg345235k']);
        $this->uid12 = $user['id'];
        $user = $datagenerator->create_user(['surname' => 'tester', 'username' => 'testy13', 'roles' => 'Locked', 'sid' => 'dgsfg345235l']);
        $this->uid13 = $user['id'];
    }

    /**
     * Test map gender
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_map_gender()
    {
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
     * @group plugin_cs_sms
     */
    public function test_map_title()
    {
        // Known titles.Mx|Mr|Mrs|Miss|Ms|Dr|Professor
        $this->assertEquals('Professor', user_helper::map_title('Professor'));
        $this->assertEquals('Dr', user_helper::map_title('Dr'));
        $this->assertEquals('Miss', user_helper::map_title('Miss'));
        $this->assertEquals('Mrs', user_helper::map_title('Mrs'));
        $this->assertEquals('Ms', user_helper::map_title('Ms'));
        $this->assertEquals('Mr', user_helper::map_title('Mr'));
        $this->assertEquals('Mx', user_helper::map_title('Mx'));
        // Unknown titles.
        $this->assertEquals(null, user_helper::map_title('Prof'));
        $this->assertEquals(null, user_helper::map_title('Mrx'));
        $this->assertEquals(null, user_helper::map_title('xMrs'));
    }

    /**
     * Test map title to gender
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_title_to_gender()
    {
        // Other gender.
        $this->assertEquals('Other', user_helper::title_to_gender('Mx'));
        // Male gender.
        $this->assertEquals('Male', user_helper::title_to_gender('Mr'));
        // Female gender.
        $this->assertEquals('Female', user_helper::title_to_gender('Miss'));
        $this->assertEquals('Female', user_helper::title_to_gender('Mrs'));
        $this->assertEquals('Female', user_helper::title_to_gender('Ms'));
        // Null gender
        $this->assertEquals(null, user_helper::title_to_gender('Dr'));
        $this->assertEquals(null, user_helper::title_to_gender('Professor'));
        // Unknown title, null gender.
        $this->assertEquals(null, user_helper::title_to_gender('Prof'));
    }

    /**
     * Test map student status
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_map_student_status()
    {
        // User Cancelled.
        $this->assertEquals('left', user_helper::map_student_status('CN', $this->uid1, $this->db));
        // User Discontinued.
        $this->assertEquals('left', user_helper::map_student_status('DC', $this->uid2, $this->db));
        // User Deceased.
        $this->assertEquals('left', user_helper::map_student_status('DE', $this->uid3, $this->db));
        // User Dismissed.
        $this->assertEquals('left', user_helper::map_student_status('DM', $this->uid4, $this->db));
        // Completed Program.
        $this->assertEquals('graduate', user_helper::map_student_status('CM', $this->uid5, $this->db));
        // User Admitted.
        $this->assertEquals('Suspended', user_helper::map_student_status('AD', $this->uid6, $this->db));
        // User Applicant.
        $this->assertEquals('Suspended', user_helper::map_student_status('AP', $this->uid7, $this->db));
        // User Leave of absence.
        $this->assertEquals('Student', user_helper::map_student_status('LA', $this->uid8, $this->db));
        // User Prematriculant.
        $this->assertEquals('Suspended', user_helper::map_student_status('PM', $this->uid9, $this->db));
        // User Suspended.
        $this->assertEquals('Suspended', user_helper::map_student_status('SP', $this->uid10, $this->db));
        // User Waitlisted.
        $this->assertEquals('Suspended', user_helper::map_student_status('WT', $this->uid11, $this->db));
        // User Active.
        $this->assertEquals('Student', user_helper::map_student_status('AC', $this->uid12, $this->db));
        // User Locked interanlly in ExamSys
        $this->assertEquals('Locked', user_helper::map_student_status('AC', $this->uid13, $this->db));
    }

    /**
     * Test map year of study
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_map_yearofstudy()
    {
        // Valid year 06.
        $this->assertEquals(6, user_helper::map_yearofstudy('06'));
        // Valid year 05.
        $this->assertEquals(5, user_helper::map_yearofstudy('05'));
        // Valid year 04.
        $this->assertEquals(4, user_helper::map_yearofstudy('04'));
        // Valid year 03.
        $this->assertEquals(3, user_helper::map_yearofstudy('03'));
        // Valid year 02.
        $this->assertEquals(2, user_helper::map_yearofstudy('02'));
        // Valid year 01.
        $this->assertEquals(1, user_helper::map_yearofstudy('01'));
        // Valid year 00.
        $this->assertEquals(0, user_helper::map_yearofstudy('00'));
        // Foundation year.
        $this->assertEquals(0, user_helper::map_yearofstudy('FND'));
        // PGT.
        $this->assertEquals(null, user_helper::map_yearofstudy('PGT'));
        // PGR.
        $this->assertEquals(null, user_helper::map_yearofstudy('PGR'));
        // Invalid year.
        $this->assertEquals(null, user_helper::map_yearofstudy(7));
    }
}
