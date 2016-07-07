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

/**
 * Test cs mapping functions
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class cssmstest extends unittestdatabase {
    /**
     * Mock faculty xml
     * @var string
     */
    private $facultyxml = '<?xml version="1.0"?>
        <FacultyList>
            <Faculty>
                <FacultyID>CFY-AE</FacultyID>
                <FacultyCode>CFY-AE</FacultyCode>
                <FacultyDescr>Faculty of Arts and Education</FacultyDescr>
                <MemberSchools>
                    <School>
                        <SchoolID>CSC-CELE</SchoolID>
                        <SchoolCode>CSC-CELE</SchoolCode>
                        <SchoolDescr>Centre for English Language Education</SchoolDescr>
                    </School>
                    <School>
                        <SchoolID>CSC-EDU</SchoolID>
                        <SchoolCode>CSC-EDU</SchoolCode>
                        <SchoolDescr>School of Education</SchoolDescr>
                    </School>
                </MemberSchools>
            </Faculty>
        </FacultyList>';
    /**
     * Mock course xml
     * @var string
     */   
    private $coursexml = '<?xml version="1.0"?>
        <PlanList>
            <Plan>
                <PlanID>UON|U8PBRSGY</PlanID>
                <PlanCode>U8PBRSGY</PlanCode>
                <PlanDescr>Breast Surgery</PlanDescr>
                <FacultyID>UFY-MHS</FacultyID>
                <SchoolID>USC-MED</SchoolID>
                <ProgramID>UON|U1509</ProgramID>
                <ProgramCode>U1509</ProgramCode>
                <ProgramDescr>Breast Surgery</ProgramDescr>
            </Plan>
        </PlanList>';
    /**
     * Mock module xml
     * @var string
     */
    private $modulexml = '<?xml version="1.0"?>
        <ModuleList>
            <Module>
                <ModuleID>030003</ModuleID>
                <ModuleCode>NAAAXXXX</ModuleCode>
                <Description>Self-marketing skills</Description>
                <FacultyID>TESTECT</FacultyID>
                <SchoolID>USC-MED</SchoolID>
            </Module>
        </ModuleList>';
        
    private $enrolxml = '<?xml version="1.0"?>
        <ModuleEnrolments>
            <Module>
                <ModuleID>00001111</ModuleID>
                <ModuleCode>TESTMOD</ModuleCode>
                <Year>2016</Year>
                <Membership>
                    <User>
                        <UserId>10000667</UserId>
                        <Title/>
                        <ForeName>Lewis</ForeName>
                        <Surname>John</Surname>
                        <Username>brzhs5</Username>
                        <Email/>
                        <Gender/>
                        <PlanID>M6UNUTRN</PlanID>
                        <YearOfStudy>1</YearOfStudy>
                        <Status>Enrolled</Status>
                        <Role>Student</Role>
                    </User>
                    <User>
                        <UserId>10000670</UserId>
                        <Title/>
                        <ForeName>Daniel</ForeName>
                        <Surname>Watson</Surname>
                        <Username>brzamh</Username>
                        <Email/>
                        <Gender/>
                        <PlanID>M6UCVENG</PlanID>
                        <YearOfStudy>1</YearOfStudy>
                        <Status>Enrolled</Status>
                        <Role>Student</Role>
                    </User>
                </Membership>
            </Module>
        </ModuleEnrolments>';
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet(dirname(__DIR__) . DIRECTORY_SEPARATOR  . "fixtures" . DIRECTORY_SEPARATOR . "sms.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet(dirname(__DIR__) . DIRECTORY_SEPARATOR  . "fixtures" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    /**
     * Test get faculties
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_faculties() {
        $sms = $this->getMockBuilder('plugins\SMS\plugin_cs_sms\plugin_cs_sms')
            ->setMethods(array('callws'))
            ->setConstructorArgs(array($this->db, 0))
            ->getMock();
        $sms->expects($this->once())
            ->method('callws')
            ->will($this->returnValue($this->facultyxml));
        $sms->get_faculties();
        $queryTable = $this->getConnection()->createQueryTable('faculty', 'SELECT id, code, name, externalid, externalsys FROM faculty');
        $expectedTable = $this->get_expected_data_set('faculty')->getTable("faculty");
        $this->assertTablesEqual($expectedTable, $queryTable);
        $queryTable = $this->getConnection()->createQueryTable('schools', 'SELECT id, code, school, facultyID, externalid, externalsys FROM schools');
        $expectedTable = $this->get_expected_data_set('faculty')->getTable("schools");
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
    /**
     * Test get courses
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_courses() {
        $sms = $this->getMockBuilder('plugins\SMS\plugin_cs_sms\plugin_cs_sms')
            ->setMethods(array('callws'))
            ->setConstructorArgs(array($this->db, 0))
            ->getMock();
        $sms->expects($this->once())
            ->method('callws')
            ->will($this->returnValue($this->coursexml));
        $sms->get_courses();
        $queryTable = $this->getConnection()->createQueryTable('courses', 'SELECT id, name, description, schoolid, externalid, externalsys FROM courses');
        $expectedTable = $this->get_expected_data_set('faculty')->getTable("courses");
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
    /**
     * Test get modules
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_modules() {
        $sms = $this->getMockBuilder('plugins\SMS\plugin_cs_sms\plugin_cs_sms')
            ->setMethods(array('callws'))
            ->setConstructorArgs(array($this->db, 0))
            ->getMock();
        $sms->expects($this->once())
            ->method('callws')
            ->will($this->returnValue($this->modulexml));
        $sms->get_modules();
        $queryTable = $this->getConnection()->createQueryTable('modules', 'SELECT id, moduleid, fullname, schoolid, externalid, academic_year_start, sms FROM modules');
        $expectedTable = $this->get_expected_data_set('faculty')->getTable("modules");
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
    /**
     * Test get modules with session and module as arguments
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_module() {
        $sms = $this->getMockBuilder('plugins\SMS\plugin_cs_sms\plugin_cs_sms')
            ->setMethods(array('callws'))
            ->setConstructorArgs(array($this->db, 0))
            ->getMock();
        $sms->expects($this->once())
            ->method('callws')
            ->will($this->returnValue($this->modulexml));
        $sms->get_modules('030003', 2016);
        $queryTable = $this->getConnection()->createQueryTable('modules', 'SELECT id, moduleid, fullname, schoolid, externalid, academic_year_start, sms FROM modules');
        $expectedTable = $this->get_expected_data_set('faculty')->getTable("modules");
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
    /**
     * Test get enrolments with session only (all enrolments)
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_enrolments_all() {
        $this->config->set_setting('campuslist', 'U', 'plugin_cs_sms');
        $sms = $this->getMockBuilder('plugins\SMS\plugin_cs_sms\plugin_cs_sms')
            ->setMethods(array('callws'))
            ->setConstructorArgs(array($this->db, 0))
            ->getMock();
        $sms->expects($this->once())
            ->method('callws')
            ->will($this->returnValue($this->enrolxml));
        $sms->get_enrolments(2016);
        $queryTable = $this->getConnection()->createQueryTable('users', 'SELECT id, grade, surname, username, title, email, gender, roles, first_names, yearofstudy FROM users');
        $expectedTable = $this->get_expected_data_set('faculty')->getTable("users");
        $this->assertTablesEqual($expectedTable, $queryTable);
        $queryTable = $this->getConnection()->createQueryTable('sid', 'SELECT student_id, userID FROM sid');
        $expectedTable = $this->get_expected_data_set('faculty')->getTable("sid");
        $this->assertTablesEqual($expectedTable, $queryTable);
        $queryTable = $this->getConnection()->createQueryTable('modules_student', 'SELECT id, userID, idMod, calendar_year FROM modules_student');
        $expectedTable = $this->get_expected_data_set('faculty')->getTable("modules_student");
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
    /**
     * Test get enrolments with session and moudle id
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_enrolments() {
        $this->config->set_setting('campuslist', 'U', 'plugin_cs_sms');
        $sms = $this->getMockBuilder('plugins\SMS\plugin_cs_sms\plugin_cs_sms')
            ->setMethods(array('callws'))
            ->setConstructorArgs(array($this->db, 0))
            ->getMock();
        $sms->expects($this->once())
            ->method('callws')
            ->will($this->returnValue($this->enrolxml));
        $sms->get_enrolments(2016, '00001111');
        $queryTable = $this->getConnection()->createQueryTable('users', 'SELECT id, grade, surname, username, title, email, gender, roles, first_names, yearofstudy FROM users');
        $expectedTable = $this->get_expected_data_set('faculty')->getTable("users");
        $this->assertTablesEqual($expectedTable, $queryTable);
        $queryTable = $this->getConnection()->createQueryTable('sid', 'SELECT student_id, userID FROM sid');
        $expectedTable = $this->get_expected_data_set('faculty')->getTable("sid");
        $this->assertTablesEqual($expectedTable, $queryTable);
        $queryTable = $this->getConnection()->createQueryTable('modules_student', 'SELECT id, userID, idMod, calendar_year FROM modules_student');
        $expectedTable = $this->get_expected_data_set('faculty')->getTable("modules_student");
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
    /**
     * Test install mapping plugin - already installed on setup
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_install() {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms($this->db);
        $this->assertEquals('OK', $sms->install($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password')));
        // Check tables are correct.
        $queryTable = $this->getConnection()->createQueryTable('plugins', 'SELECT component, version, type FROM plugins');
        $expectedTable = $this->get_expected_data_set('pluginconfig')->getTable("plugins");
        $this->assertTablesEqual($expectedTable, $queryTable);
        $queryTable = $this->getConnection()->createQueryTable('config', 'SELECT component, setting, value FROM config order by 1, 2');
        $expectedTable = $this->get_expected_data_set('pluginconfig')->getTable("config");
        $this->assertTablesEqual($expectedTable, $queryTable);
        $sms->uninstall($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
    }
    /**
     * Test uninstall mapping plugin - already installed on setup
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_uninstall() {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms($this->db);
        $sms->install($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
        $this->assertEquals('OK', $sms->uninstall($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password')));
        // Check tables are correct.
        $queryTable = $this->getConnection()->getRowCount('plugins');
        $this->assertEquals(0, $queryTable);
        $queryTable = $this->getConnection()->createQueryTable('config', 'SELECT component, setting, value FROM config  order by 1, 2');
        $expectedTable = $this->get_expected_data_set('nopluginconfig')->getTable("config");
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
    /**
     * Test check plugin version
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_plugin_version() {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms($this->db);
        $sms->install($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
        $this->assertEquals($sms->get_installed_version(), $sms->get_plugin_version('plugin_cs_sms'));
        $sms->uninstall($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
    }
}
