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
     * Mock assessment xml
     * @var string
     */
    private $assessmentxml = '<?xml version="1.0" encoding="utf-8"?>
        <AssessmentList>
          <Assessment>
            <AssessmentID>C-00000000032</AssessmentID>
            <AssessmentType>SUMMATIVE</AssessmentType>
            <DurationMinutes>30</DurationMinutes>
            <Modules>
              <Module>
                <ModuleID>00001111</ModuleID>
                <ModuleCode>TESTMOD</ModuleCode>
              </Module>
            </Modules>
            <Owner>
              <UserID>91234567</UserID>
              <UserName>staff</UserName>
            </Owner>
            <AcademicSession>2016</AcademicSession>
            <Sittings>1</Sittings>
          </Assessment>
          <Assessment>
            <AssessmentID>C-00000000033</AssessmentID>
            <AssessmentType>SUMMATIVE</AssessmentType>
            <AssessmentDescr>Test Exam</AssessmentDescr>
            <DurationMinutes>30</DurationMinutes>
            <Modules>
              <Module>
                <ModuleID>00001111</ModuleID>
                <ModuleCode>TESTMOD</ModuleCode>
              </Module>
            </Modules>
            <Owner>
              <UserID>91234567</UserID>
              <UserName>staff</UserName>
            </Owner>
            <AcademicSession>2016</AcademicSession>
            <Sittings>1</Sittings>
          </Assessment>
          <Assessment>
            <AssessmentID>C-00000000034</AssessmentID>
            <AssessmentType>CRSE</AssessmentType>
            <AssessmentDescr>Test Coursework</AssessmentDescr>
            <DurationMinutes>0</DurationMinutes>
            <Modules>
              <Module>
                <ModuleID>00001111</ModuleID>
                <ModuleCode>TESTMOD</ModuleCode>
              </Module>
            </Modules>
            <Owner>
              <UserID>91234567</UserID>
              <UserName>staff</UserName>
            </Owner>
            <AcademicSession>2016</AcademicSession>
            <Sittings>1</Sittings>
          </Assessment>
          <Assessment>
            <AssessmentID>C-00000000035</AssessmentID>
            <AssessmentType>SUMMATIVE</AssessmentType>
            <AssessmentDescr>Test Exam 2</AssessmentDescr>
            <DurationMinutes>0</DurationMinutes>
            <Modules>
              <Module>
                <ModuleID>00001111</ModuleID>
                <ModuleCode>TESTMOD</ModuleCode>
              </Module>
            </Modules>
            <Owner>
              <UserID>91234567</UserID>
              <UserName>staff</UserName>
            </Owner>
            <AcademicSession>2016</AcademicSession>
            <Sittings>1</Sittings>
            <Notes>meh</Notes>
          </Assessment>
        </AssessmentList>';
    /**
     * Mock faculty xml
     * @var string
     */
    private $facultyxml = '<?xml version="1.0"?>
        <FacultyList>
            <Faculty>
                <FacultyCode>CFY-AE</FacultyCode>
                <FacultyDescr>Faculty of Arts and Education</FacultyDescr>
                <MemberSchools>
                    <School>
                        <SchoolID>CSC-CELE</SchoolID>
                        <SchoolCode>CSC-CELE</SchoolCode>
                        <SchoolDescr>Centre for English Language Education</SchoolDescr>
                    </School>
                    <School>
                        <SchoolCode>CSC-EDU</SchoolCode>
                        <SchoolDescr>School of Education</SchoolDescr>
                    </School>
                    <School>
                        <SchoolID>CSC-CELD</SchoolID>
                        <SchoolDescr>Centre for English Language Education D</SchoolDescr>
                    </School>
                    <School>
                        <SchoolID>CSC-EDU</SchoolID>
                        <SchoolCode>CSC-EDU</SchoolCode>
                        <SchoolDescr>School of Education</SchoolDescr>
                    </School>
                </MemberSchools>
            </Faculty>
            <Faculty>
                <FacultyID>CFY-AE</FacultyID>
                <MemberSchools>
                    <School>
                        <SchoolID>CSC-CELE</SchoolID>
                        <SchoolCode>CSC-CELE</SchoolCode>
                        <SchoolDescr>Centre for English Language Education</SchoolDescr>
                    </School>
                    <School>
                        <SchoolCode>CSC-EDU</SchoolCode>
                        <SchoolDescr>School of Education</SchoolDescr>
                    </School>
                    <School>
                        <SchoolID>CSC-CELD</SchoolID>
                        <SchoolDescr>Centre for English Language Education D</SchoolDescr>
                    </School>
                    <School>
                        <SchoolID>CSC-EDU</SchoolID>
                        <SchoolCode>CSC-EDU</SchoolCode>
                        <SchoolDescr>School of Education</SchoolDescr>
                    </School>
                </MemberSchools>
            </Faculty>
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
                        <SchoolCode>CSC-EDU</SchoolCode>
                        <SchoolDescr>School of Education</SchoolDescr>
                    </School>
                    <School>
                        <SchoolID>CSC-CELD</SchoolID>
                        <SchoolDescr>Centre for English Language Education D</SchoolDescr>
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
     * Mock faculty xml
     * @var string
     */
    private $facultyxml2 = '<?xml version="1.0"?>
        <FacultyList>
            <Faculty>
                <FacultyID>CFY-AE</FacultyID>
                <FacultyCode>CFY-AE</FacultyCode>
                <FacultyDescr>Faculty of Arts and Education</FacultyDescr>
            </Faculty>
        </FacultyList>';
    /**
     * Mock course xml
     * @var string
     */   
    private $coursexml = '<?xml version="1.0"?>
        <PlanList>
            <Plan>
                <PlanCode>U8PBRSGY</PlanCode>
                <PlanDescr>Breast Surgery</PlanDescr>
                <FacultyID>UFY-MHS</FacultyID>
                <SchoolID>USC-MED</SchoolID>
                <ProgramID>UON|U1509</ProgramID>
                <ProgramCode>U1509</ProgramCode>
                <ProgramDescr>Breast Surgery</ProgramDescr>
            </Plan>
            <Plan>
                <PlanID>UON|U8PBRSGY</PlanID>
                <PlanDescr>Breast Surgery</PlanDescr>
                <FacultyID>UFY-MHS</FacultyID>
                <SchoolID>USC-MED</SchoolID>
                <ProgramID>UON|U1509</ProgramID>
                <ProgramCode>U1509</ProgramCode>
                <ProgramDescr>Breast Surgery</ProgramDescr>
            </Plan>
            <Plan>
                <PlanID>UON|U8PBRSGY</PlanID>
                <PlanCode>U8PBRSGY</PlanCode>
                <PlanDescr>Breast Surgery</PlanDescr>
                <FacultyID>UFY-MHS</FacultyID>
                <ProgramID>UON|U1509</ProgramID>
                <ProgramCode>U1509</ProgramCode>
                <ProgramDescr>Breast Surgery</ProgramDescr>
            </Plan>
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
                <ModuleCode>NAAAXXXX</ModuleCode>
                <Description>Self-marketing skills</Description>
                <FacultyID>TESTECT</FacultyID>
                <SchoolID>USC-MED</SchoolID>
            </Module>
            <Module>
                <ModuleID>030003</ModuleID>
                <ModuleCode>NAAAXXXX</ModuleCode>
                <FacultyID>TESTECT</FacultyID>
                <SchoolID>USC-MED</SchoolID>
            </Module>
            <Module>
                <ModuleID>030003</ModuleID>
                <ModuleCode>NAAAXXXX</ModuleCode>
                <Description>Self-marketing skills</Description>
                <FacultyID>TESTECT</FacultyID>
            </Module>
            <Module>
                <ModuleID>030003</ModuleID>
                <ModuleCode>NAAAXXXX</ModuleCode>
                <Description>Self-marketing skills</Description>
                <FacultyID>TESTECT</FacultyID>
                <SchoolID>USC-MED</SchoolID>
            </Module>
        </ModuleList>';
    /**
     * Mock enrolment xml
     * @var string
     */    
    private $enrolxml = '<?xml version="1.0"?>
        <ModuleEnrolments>
            <Module>
                <ModuleCode>MISSING</ModuleCode>
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
                        <YearOfStudy>01</YearOfStudy>
                        <Status>Enrolled</Status>
                        <Role>Student</Role>
                    </User>
                </Membership>
            </Module>
            <Module>
                <ModuleID>00001111</ModuleID>
                <ModuleCode>TESTMOD</ModuleCode>
                <Year>2016</Year>
                <Membership>
                    <User>
                        <Title/>
                        <ForeName>Lewis</ForeName>
                        <Surname>John</Surname>
                        <Username>brzhs5</Username>
                        <Email/>
                        <Gender/>
                        <PlanID>M6UNUTRN</PlanID>
                        <YearOfStudy>01</YearOfStudy>
                        <Status>Enrolled</Status>
                        <Role>Student</Role>
                    </User>
                    <User>
                        <UserId>10000667</UserId>
                        <Title/>
                        <ForeName>Lewis</ForeName>
                        <Surname>John</Surname>
                        <Username>brzhs5</Username>
                        <Email/>
                        <Gender/>
                        <PlanID>M6UNUTRN</PlanID>
                        <YearOfStudy>01</YearOfStudy>
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
                        <YearOfStudy>01</YearOfStudy>
                        <Status>Enrolled</Status>
                    </User>
                    <User>
                        <UserId>10000670</UserId>
                        <ForeName>Daniel</ForeName>
                        <Surname>Watson</Surname>
                        <Username>brzamh</Username>
                        <PlanID>M6UCVENG</PlanID>
                        <YearOfStudy>01</YearOfStudy>
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
                        <YearOfStudy>01</YearOfStudy>
                        <Status>Enrolled</Status>
                        <Role>Student</Role>
                    </User>
                </Membership>
            </Module>
        </ModuleEnrolments>';
    /**
     * Mock enrolment xml
     * @var string
     */  
    private $enrolxml2 = '<?xml version="1.0"?>
        <ModuleEnrolments>
            <Module>
                <ModuleID>00001111</ModuleID>
                <ModuleCode>TESTMOD</ModuleCode>
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
                        <YearOfStudy>01</YearOfStudy>
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
                        <YearOfStudy>01</YearOfStudy>
                        <Status>Enrolled</Status>
                        <Role>Student</Role>
                    </User>
                </Membership>
            </Module>
        </ModuleEnrolments>';
    /**
     * Mock enrolment xml
     * @var string
     */  
    private $enrolxml3 = '<?xml version="1.0"?>
        <ModuleEnrolments>
            <Module>
                <ModuleID>00001111</ModuleID>
                <ModuleCode>TESTMOD</ModuleCode>
                <Year>2016</Year>
            </Module>
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
                        <YearOfStudy>01</YearOfStudy>
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
                        <YearOfStudy>01</YearOfStudy>
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
     * Test get assessments
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_assessments() {
        $sms = $this->getMockBuilder('plugins\SMS\plugin_cs_sms\plugin_cs_sms')
            ->setMethods(array('callws'))
            ->setConstructorArgs(array($this->db, 0))
            ->getMock();
        $sms->expects($this->once())
            ->method('callws')
            ->will($this->returnValue($this->assessmentxml));
        $sms->get_assessments(2016);
        $queryTable = $this->getConnection()->createQueryTable('scheduling', 'SELECT id, paperID, notes, sittings FROM scheduling');
        $expectedTable = $this->get_expected_data_set('scheduling')->getTable("scheduling");
        $this->assertTablesEqual($expectedTable, $queryTable);
        $queryTable = $this->getConnection()->createQueryTable('properties', 'SELECT property_id, paper_title, paper_type, exam_duration, paper_ownerID, calendar_year, externalid, externalsys FROM properties');
        $expectedTable = $this->get_expected_data_set('scheduling')->getTable("properties");
        $this->assertTablesEqual($expectedTable, $queryTable);
        $queryTable = $this->getConnection()->createQueryTable('properties_modules', 'SELECT property_id, idMod FROM properties_modules');
        $expectedTable = $this->get_expected_data_set('scheduling')->getTable("properties_modules");
        $this->assertTablesEqual($expectedTable, $queryTable);
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
     * Test get faculties - missing schools
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_faculties_missing_schools() {
        $sms = $this->getMockBuilder('plugins\SMS\plugin_cs_sms\plugin_cs_sms')
            ->setMethods(array('callws'))
            ->setConstructorArgs(array($this->db, 0))
            ->getMock();
        $sms->expects($this->once())
            ->method('callws')
            ->will($this->returnValue($this->facultyxml2));
        $sms->get_faculties();
        // Faculties provided so created.
        $queryTable = $this->getConnection()->createQueryTable('faculty', 'SELECT id, code, name, externalid, externalsys FROM faculty');
        $expectedTable = $this->get_expected_data_set('faculty')->getTable("faculty");
        $this->assertTablesEqual($expectedTable, $queryTable);
        // Missing schools so no schools created.
        $this->assertEquals(1, $this->getConnection()->getRowCount('schools'));
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
     * Test get enrolments with missing year node
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_enrolments_all_missing_nodes() {
        $this->config->set_setting('campuslist', 'U', 'plugin_cs_sms');
        $sms = $this->getMockBuilder('plugins\SMS\plugin_cs_sms\plugin_cs_sms')
            ->setMethods(array('callws'))
            ->setConstructorArgs(array($this->db, 0))
            ->getMock();
        $sms->expects($this->once())
            ->method('callws')
            ->will($this->returnValue($this->enrolxml2));
        $sms->get_enrolments(2016);
        // Users should still be created.
        $queryTable = $this->getConnection()->createQueryTable('users', 'SELECT id, grade, surname, username, title, email, gender, roles, first_names, yearofstudy FROM users');
        $expectedTable = $this->get_expected_data_set('faculty')->getTable("users");
        $this->assertTablesEqual($expectedTable, $queryTable);
        $queryTable = $this->getConnection()->createQueryTable('sid', 'SELECT student_id, userID FROM sid');
        $expectedTable = $this->get_expected_data_set('faculty')->getTable("sid");
        $this->assertTablesEqual($expectedTable, $queryTable);
        // Enrolments should not be created as missing session.
        $this->assertEquals(0, $this->getConnection()->getRowCount('modules_student'));
    }
    /**
     * Test get enrolments with session only (all enrolments) - skip module with missing members nodes
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_enrolments_all_skip_missing_members() {
        $this->config->set_setting('campuslist', 'U', 'plugin_cs_sms');
        $sms = $this->getMockBuilder('plugins\SMS\plugin_cs_sms\plugin_cs_sms')
            ->setMethods(array('callws'))
            ->setConstructorArgs(array($this->db, 0))
            ->getMock();
        $sms->expects($this->once())
            ->method('callws')
            ->will($this->returnValue($this->enrolxml3));
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
        $queryTable = $this->getConnection()->createQueryTable('config', 'SELECT component, setting, value, type FROM config order by 1, 2');
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
        $queryTable = $this->getConnection()->createQueryTable('config', 'SELECT component, setting, value, type FROM config  order by 1, 2');
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
    /**
     * Test supports_module_import
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_supports_module_import() {
        $lang = new \langpack();
        $component = 'plugins/SMS/plugin_cs_sms/plugin_cs_sms';
        $strings = $lang->get_all_strings($component);
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms($this->db);
        $array = array('url' => $this->config->get('cfg_root_path') . '/plugins/SMS/plugin_cs_sms/admin/import_modules.php',
         'blurb' => $strings['importmodules'],
         'tooltip' => $strings['importmodulestooltip']);
        $this->assertEquals($array, $sms->supports_module_import());
        
    }
    /**
     * Test supports_module_import - disabled in config
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_supports_module_import_disabled() {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms($this->db);
        $this->config->set_setting('enable_module', 0, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->config->set_setting('enable_enrolment', 0, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->assertFalse($sms->supports_module_import());
        
    }
    /**
     * Test supports_faculty_import
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_supports_faculty_import() {
        $lang = new \langpack();
        $component = 'plugins/SMS/plugin_cs_sms/plugin_cs_sms';
        $strings = $lang->get_all_strings($component);
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms($this->db);
        $array = array('url' => $this->config->get('cfg_root_path') . '/plugins/SMS/plugin_cs_sms/admin/import_faculties.php',
         'blurb' => $strings['importfaculties'],
         'tooltip' => $strings['importfacultiestooltip']);
        $this->assertEquals($array, $sms->supports_faculty_import());
    }
    /**
     * Test supports_faculty_import - disabled in config
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_supports_faculty_import_disabled() {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms($this->db);
        $this->config->set_setting('enable_faculty', 0, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->assertFalse($sms->supports_faculty_import());
    }
    /**
     * Test supports_course_import
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_supports_course_import() {
        $lang = new \langpack();
        $component = 'plugins/SMS/plugin_cs_sms/plugin_cs_sms';
        $strings = $lang->get_all_strings($component);
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms($this->db);
        $array = array('url' => $this->config->get('cfg_root_path') . '/plugins/SMS/plugin_cs_sms/admin/import_courses.php',
         'blurb' => $strings['importcourses'],
         'tooltip' => $strings['importcoursestooltip']);
        $this->assertEquals($array, $sms->supports_course_import());
    }
    /**
     * Test supports_course_import - disabled in config
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_supports_course_import_disabled() {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms($this->db);
        $this->config->set_setting('enable_course', 0, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->assertFalse($sms->supports_course_import());
    }
    /**
     * Test supports_enrol_import
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_supports_enrol_import() {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms($this->db);
        $this->assertTrue($sms->supports_enrol_import());
    }
    /**
     * Test supports_enrol_import - disabled in config
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_supports_enrol_import_disabled() {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms($this->db);
        $this->config->set_setting('enable_enrolment', 0, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->assertFalse($sms->supports_enrol_import());
    }
    /**
     * Test supports_assessment_import
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_supports_assessment_import() {
        $lang = new \langpack();
        $component = 'plugins/SMS/plugin_cs_sms/plugin_cs_sms';
        $strings = $lang->get_all_strings($component);
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms($this->db);
        $array = array('url' => $this->config->get('cfg_root_path') . '/plugins/SMS/plugin_cs_sms/admin/import_assessments.php',
         'blurb' => $strings['importassessments'],
         'tooltip' => $strings['importassessmentstooltip']);
        $this->assertEquals($array, $sms->supports_assessment_import());
    }
    /**
     * Test supports_assessment_import - disabled in config
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_supports_assessment_import_disabled() {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms($this->db);
        $this->config->set_setting('enable_assessment', 0, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->assertFalse($sms->supports_assessment_import());
    }
    /**
     * Test get_name
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_name() {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms($this->db);
        $this->assertEquals('Campus Solutions', $sms->get_name());
    }
    /**
     * Test enable_plugin
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_enable_plugin() {
        $config = $this->config->get_setting('plugin_sms', 'enabled_plugin');
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms($this->db);
        // Check already enabled.
        $sms->enable_plugin();
        $this->assertEquals(array('plugin_cs_sms'), $config);
        // Disable so we can test enabling.
        $sms->disable_plugin();
        $sms->enable_plugin();
        $this->assertEquals(array('plugin_cs_sms'), $config);
    }
    /**
     * Test disable_plugin
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_disable_plugin() {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms($this->db);
        $sms->disable_plugin();
        $config = $this->config->get_setting('plugin_sms', 'enabled_plugin');
        $this->assertEquals(array(), $config);
    }
}
