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

use plugins\SMS\plugin_cs_sms\plugin_cs_sms;
use testing\unittest\unittestdatabase;

/**
 * Test cs mapping functions
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class cssmstest extends unittestdatabase
{
    /**
     * @var array Storage for module data in tests
     */
    private $mod;

    /**
     * @var array Storage for faculty data in tests
     */
    private $fac;

    /**
     * @var array Storage for school data in tests
     */
    private $school1;

    /**
     * @var array Storage for school data in tests
     */
    private $school2;

    /**
     * @var integer id for user generated in datageneration
     */
    private $uid;

    /**
     * @var integer new version of plugin being installed
     */
    private $newversion;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms();
        $this->newversion = $sms->get_file_version();
        $sms->install($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
        $sms->enable_plugin();
        $this->config->set_setting('active_modules_only', 1, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->config->set_setting('campuslist', 'U', \Config::STRING, 'plugin_cs_sms');
        $this->config->set_setting('enable_assessment', 1, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->config->set_setting('enable_course', 1, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->config->set_setting('enable_delete_modules', 1, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->config->set_setting('enable_enrolment', 1, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->config->set_setting('enable_faculty', 1, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->config->set_setting('enable_gradebook', 1, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->config->set_setting('enable_module', 1, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->config->set_setting('gradebooklocation', '', \Config::STRING, 'plugin_cs_sms');
        $this->config->set_setting('gradebook_md5', 1, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->config->set_setting('lockfile_lifespan', 0, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->config->set_setting('loglocation', '', \Config::STRING, 'plugin_cs_sms');
        $this->config->set_setting('password', 'aninscurepassword', \Config::PASSWORD, 'plugin_cs_sms');
        $this->config->set_setting('target_module_enrolments', 0, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->config->set_setting('timeout', 10, \Config::INTEGER, 'plugin_cs_sms');
        $this->config->set_setting('url', 'https://www.example.com', \Config::URL, 'plugin_cs_sms');
        $this->config->set_setting('username', 'username', \Config::STRING, 'plugin_cs_sms');
        $this->config->set_setting('validate_schema', 0, \Config::BOOLEAN, 'plugin_cs_sms');
        $datagenerator = $this->get_datagenerator('academic_year', 'core');
        $datagenerator->create_academic_year(array('calendar_year' => 2016, 'academic_year' => '2016/17'));
        $datagenerator = $this->get_datagenerator('faculty', 'core');
        $this->fac = $datagenerator->create_faculty(array('name' => 'Faculty of Testing', 'externalid' => 'TESTECT', 'externalsys' => 'Campus Solutions', 'code' => 'TEST'));
        $datagenerator = $this->get_datagenerator('school', 'core');
        $this->school1 = $datagenerator->create_school(array('school' => 'Centre for Testing', 'facultyID' => $this->fac['id'], 'externalid' => 'USC-MED', 'externalsys' => 'Campus Solutions', 'code' => 'TEST'));
        $this->school2 = $datagenerator->create_school(array('school' => 'Centre for Testing 2', 'facultyID' => $this->fac['id'], 'externalid' => 'USC-ME2', 'externalsys' => 'Campus Solutions', 'code' => 'TEST2'));
        $datagenerator = $this->get_datagenerator('course', 'core');
        $datagenerator->create_course(array('name' => 'M6UNUTRN', 'description' => 'Nutrition', 'schoolid' => $this->school1['id'], 'externalid' => 'M6UNUTRN', 'externalsys' => 'Campus Solutions'));
        $datagenerator->create_course(array('name' => 'M6UCVENG', 'description' => 'Civil Engineering', 'schoolid' => $this->school1['id'], 'externalid' => 'M6UCVENG', 'externalsys' => 'Campus Solutions'));
        $datagenerator = $this->get_datagenerator('modules', 'core');
        $this->mod = $datagenerator->create_module(array('fullname' => 'Testing skills', 'moduleid' => 'TESTMOD', 'schoolID' => $this->school1['id'], 'externalID' => '00001111', 'sms_api' => 'Campus Solutions'));
        $datagenerator = $this->get_datagenerator('users', 'core');
        $user = $datagenerator->create_user(array('surname' => 'staff', 'username' => 'staff', 'grade' => 'University Lecturer', 'first_names' => 'staffy', 'yearofstudy' => 1,
            'title' => 'Mr', 'email' => 'staffy@example.com', 'gender' => 'Male', 'roles' => 'Staff'));
        $this->uid = $user['id'];
    }

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
            <Owners>
              <Owner>
                <UserID>1248778</UserID>
                <UserName>unknown</UserName>
              </Owner>
              <Owner>
                <UserID>91234567</UserID>
                <UserName>staff</UserName>
              </Owner>
            </Owners>
            <AcademicSession>2016</AcademicSession>
            <Sittings>1</Sittings>
          </Assessment>
          <Assessment>
            <AssessmentID>C-00000000033</AssessmentID>
            <AssessmentType>SUMMATIVE</AssessmentType>
            <AssessmentDescr>Test Exam</AssessmentDescr>
            <DurationMinutes>90</DurationMinutes>
            <Modules>
              <Module>
                <ModuleID>00001111</ModuleID>
                <ModuleCode>TESTMOD</ModuleCode>
              </Module>
            </Modules>
            <Owners>
              <Owner>
                <UserID>1248778</UserID>
                <UserName>unknown</UserName>
              </Owner>
              <Owner>
                <UserID>91234567</UserID>
                <UserName>staff</UserName>
              </Owner>
            </Owners>
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
            <Owners>
              <Owner>
                <UserID>1248778</UserID>
                <UserName>unknown</UserName>
              </Owner>
              <Owner>
                <UserID>91234567</UserID>
                <UserName>staff</UserName>
              </Owner>
            </Owners>
            <AcademicSession>2016</AcademicSession>
            <Sittings>1</Sittings>
          </Assessment>
          <Assessment>
            <AssessmentID>C-00000000035</AssessmentID>
            <AssessmentType>SUMMATIVE</AssessmentType>
            <AssessmentDescr>Test Exam 2</AssessmentDescr>
            <DurationMinutes>60</DurationMinutes>
            <Modules>
              <Module>
                <ModuleID>00001111</ModuleID>
                <ModuleCode>TESTMOD</ModuleCode>
              </Module>
            </Modules>
            <Owners>
              <Owner>
                <UserID>1248778</UserID>
                <UserName>unknown</UserName>
              </Owner>
              <Owner>
                <UserID>91234567</UserID>
                <UserName>staff</UserName>
              </Owner>
            </Owners>
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
            <Plan>
                <PlanID>UON|U8PBRSGY</PlanID>
                <PlanCode>U8PBRSGY</PlanCode>
                <PlanDescr>Breast Surgery</PlanDescr>
                <FacultyID>UFY-MHS</FacultyID>
                <SchoolID>USC-ME2</SchoolID>
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
                        <Title>Mr</Title>
                        <ForeName>Lewis</ForeName>
                        <Surname>John</Surname>
                        <Username>brzhs5</Username>
                        <Email>brzhs5@example.com</Email>
                        <Gender>Male</Gender>
                        <PlanID>M6UNUTRN</PlanID>
                        <YearOfStudy>01</YearOfStudy>
                        <Status>AC</Status>
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
                        <Email>brzhs5@example.com</Email>
                        <Gender>Male</Gender>
                        <PlanID>M6UNUTRN</PlanID>
                        <YearOfStudy>01</YearOfStudy>
                        <Status>AC</Status>
                        <Role>Student</Role>
                    </User>
                    <User>
                        <UserId>10000667</UserId>
                        <Title>Mr</Title>
                        <ForeName>Lewis</ForeName>
                        <Surname>John</Surname>
                        <Username>brzhs5</Username>
                        <Email>brzhs5@example.com</Email>
                        <Gender>Male</Gender>
                        <PlanID>M6UNUTRN</PlanID>
                        <YearOfStudy>01</YearOfStudy>
                        <Status>AC</Status>
                        <Role>Student</Role>
                    </User>
                    <User>
                        <UserId>10000670</UserId>
                        <Title>Mr</Title>
                        <ForeName>Daniel</ForeName>
                        <Surname>Watson</Surname>
                        <Username>brzamh</Username>
                        <Email>brzamh@example.com</Email>
                        <Gender>Male</Gender>
                        <PlanID>M6UCVENG</PlanID>
                        <YearOfStudy>01</YearOfStudy>
                        <Status>AC</Status>
                    </User>
                    <User>
                        <UserId>10000670</UserId>
                        <ForeName>Daniel</ForeName>
                        <Surname>Watson</Surname>
                        <Username>brzamh</Username>
                        <PlanID>M6UCVENG</PlanID>
                        <YearOfStudy>01</YearOfStudy>
                        <Status>AC</Status>
                        <Role>Student</Role>
                    </User>
                    <User>
                        <UserId>10000670</UserId>
                        <Title>Mr</Title>
                        <ForeName>Daniel</ForeName>
                        <Surname>Watson</Surname>
                        <Username>brzamh</Username>
                        <Email>brzamh@example.com</Email>
                        <Gender>Male</Gender>
                        <PlanID>M6UCVENG</PlanID>
                        <YearOfStudy>01</YearOfStudy>
                        <Status>AC</Status>
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
                        <Title>Mr</Title>
                        <ForeName>Lewis</ForeName>
                        <Surname>John</Surname>
                        <Username>brzhs5</Username>
                        <Email>brzhs5@example.com</Email>
                        <Gender>Male</Gender>
                        <PlanID>M6UNUTRN</PlanID>
                        <YearOfStudy>01</YearOfStudy>
                        <Status>AC</Status>
                        <Role>Student</Role>
                    </User>
                    <User>
                        <UserId>10000670</UserId>
                        <Title>Mr</Title>
                        <ForeName>Daniel</ForeName>
                        <Surname>Watson</Surname>
                        <Username>brzamh</Username>
                        <Email>brzamh@example.com</Email>
                        <Gender>Male</Gender>
                        <PlanID>M6UCVENG</PlanID>
                        <YearOfStudy>01</YearOfStudy>
                        <Status>AC</Status>
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
                        <Title>Mr</Title>
                        <ForeName>Lewis</ForeName>
                        <Surname>John</Surname>
                        <Username>brzhs5</Username>
                        <Email>brzhs5@example.com</Email>
                        <Gender>Male</Gender>
                        <PlanID>M6UNUTRN</PlanID>
                        <YearOfStudy>01</YearOfStudy>
                        <Status>AC</Status>
                        <Role>Student</Role>
                    </User>
                    <User>
                        <UserId>10000670</UserId>
                        <Title>Mr</Title>
                        <ForeName>Daniel</ForeName>
                        <Surname>Watson</Surname>
                        <Username>brzamh</Username>
                        <Email>brzamh@example.com</Email>
                        <Gender>Male</Gender>
                        <PlanID>M6UCVENG</PlanID>
                        <YearOfStudy>01</YearOfStudy>
                        <Status>DM</Status>
                        <Role>Student</Role>
                    </User>
                </Membership>
            </Module>
        </ModuleEnrolments>';

    /**
     * Test get assessments
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_assessments()
    {
        $sms = $this->getMockBuilder(plugin_cs_sms::class)
            ->onlyMethods(array('callws'))
            ->setConstructorArgs(array($this->db, 0))
            ->getMock();
        $sms->expects($this->once())
            ->method('callws')
            ->will($this->returnValue($this->assessmentxml));
        $this->config->set_setting('cfg_summative_mgmt', true, \Config::BOOLEAN);
        $sms->get_assessments(2016);
        $queryTable = $this->query(array('columns' => array('paperID', 'notes', 'sittings'), 'table' => 'scheduling'));
        $expectedTable = array(
            0 => array(
                'paperID' => \Paper_utils::get_id_from_externalid('C-00000000033', 'Campus Solutions', $this->db),
                'notes' => null,
                'sittings' => 1,
            ),
            1 => array(
                'paperID' => \Paper_utils::get_id_from_externalid('C-00000000035', 'Campus Solutions', $this->db),
                'notes' => 'meh',
                'sittings' => 1,
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
        $queryTable = $this->query(array('columns' => array('paper_title', 'paper_type', 'exam_duration', 'paper_ownerID', 'calendar_year', 'externalid', 'externalsys'), 'table' => 'properties'));
        $expectedTable = array(
            0 => array(
                'paper_title' => 'Test Exam',
                'paper_type' => 2,
                'exam_duration' => 90,
                'paper_ownerID' => $this->uid,
                'calendar_year' => 2016,
                'externalid' => 'C-00000000033',
                'externalsys' => 'Campus Solutions'
            ),
            1 => array(
                'paper_title' => 'Test Exam 2',
                'paper_type' => 2,
                'exam_duration' => 60,
                'paper_ownerID' => $this->uid,
                'calendar_year' => 2016,
                'externalid' => 'C-00000000035',
                'externalsys' => 'Campus Solutions'
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
        $queryTable = $this->query(array('columns' => array('property_id', 'idMod'), 'table' => 'properties_modules'));
        $expectedTable = array(
            0 => array(
                'property_id' => \Paper_utils::get_id_from_externalid('C-00000000033', 'Campus Solutions', $this->db),
                'idMod' => $this->mod['id'],
            ),
            1 => array(
                'property_id' => \Paper_utils::get_id_from_externalid('C-00000000035', 'Campus Solutions', $this->db),
                'idMod' => $this->mod['id'],
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
        $this->config->set_setting('cfg_summative_mgmt', false, \Config::BOOLEAN);
    }

    /**
     * Test get faculties
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_faculties()
    {
        $sms = $this->getMockBuilder(plugin_cs_sms::class)
            ->onlyMethods(array('callws'))
            ->setConstructorArgs(array($this->db, 0))
            ->getMock();
        $sms->expects($this->once())
            ->method('callws')
            ->will($this->returnValue($this->facultyxml));
        $sms->get_faculties();
        $queryTable = $this->query(array('columns' => array('code', 'name', 'externalid', 'externalsys'), 'table' => 'faculty',
            'where' => array(array('column' => 'name', 'operator' => 'NOT IN', 'value' => array('UNKNOWN Faculty', 'Administrative and Support Units')))));
        $expectedTable = array(
            0 => array (
                'code' => 'TEST',
                'name' => 'Faculty of Testing',
                'externalid' => 'TESTECT',
                'externalsys' => 'Campus Solutions'
            ),
            1 => array (
                'code' => 'CFY-AE',
                'name' => 'Faculty of Arts and Education',
                'externalid' => 'CFY-AE',
                'externalsys' => 'Campus Solutions'
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
        $queryTable = $this->query(array('columns' => array('code', 'school', 'facultyID', 'externalid', 'externalsys'), 'table' => 'schools',
            'where' => array(array('column' => 'school', 'operator' => 'NOT IN', 'value' => array('UNKNOWN School', 'Training')))));
        $expectedTable = array(
            0 => array (
                'code' => 'TEST',
                'school' => 'Centre for Testing',
                'facultyID' => $this->fac['id'],
                'externalid' => 'USC-MED',
                'externalsys' => 'Campus Solutions'
            ),
            1 => array (
                'code' => 'TEST2',
                'school' => 'Centre for Testing 2',
                'facultyID' => $this->fac['id'],
                'externalid' => 'USC-ME2',
                'externalsys' => 'Campus Solutions'
            ),
            2 => array (
                'code' => 'CSC-CELE',
                'school' => 'Centre for English Language Education',
                'facultyID' => \facultyutils::get_facultyid_by_code('CFY-AE', $this->db),
                'externalid' => 'CSC-CELE',
                'externalsys' => 'Campus Solutions'
            ),
            3 => array (
                'code' => 'CSC-EDU',
                'school' => 'School of Education',
                'facultyID' => \facultyutils::get_facultyid_by_code('CFY-AE', $this->db),
                'externalid' => 'CSC-EDU',
                'externalsys' => 'Campus Solutions'
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test get faculties - missing schools
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_faculties_missing_schools()
    {
        $numschools = $this->rowcount('schools');
        $sms = $this->getMockBuilder(plugin_cs_sms::class)
            ->onlyMethods(array('callws'))
            ->setConstructorArgs(array($this->db, 0))
            ->getMock();
        $sms->expects($this->once())
            ->method('callws')
            ->will($this->returnValue($this->facultyxml2));
        $sms->get_faculties();
        // Faculties provided so created.
        $queryTable = $this->query(array('columns' => array('code', 'name', 'externalid', 'externalsys'), 'table' => 'faculty'
        , 'where' => array(array('column' => 'name', 'operator' => 'NOT IN', 'value' => array('UNKNOWN Faculty', 'Administrative and Support Units')))));
        $expectedTable = array(
            0 => array (
                'code' => 'TEST',
                'name' => 'Faculty of Testing',
                'externalid' => 'TESTECT',
                'externalsys' => 'Campus Solutions'
            ),
            1 => array (
                'code' => 'CFY-AE',
                'name' => 'Faculty of Arts and Education',
                'externalid' => 'CFY-AE',
                'externalsys' => 'Campus Solutions'
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
        // Missing schools so no schools created.
        $this->assertEquals($numschools, $this->rowcount('schools'));
    }

    /**
     * Test get courses
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_courses()
    {
        $sms = $this->getMockBuilder(plugin_cs_sms::class)
            ->onlyMethods(array('callws'))
            ->setConstructorArgs(array($this->db, 0))
            ->getMock();
        $sms->expects($this->once())
            ->method('callws')
            ->will($this->returnValue($this->coursexml));
        $sms->get_courses();
        $queryTable = $this->query(array('columns' => array('name', 'description', 'schoolid', 'externalid', 'externalsys'), 'table' => 'courses'));
        $expectedTable = array(
            0 => array (
                'name' => 'M6UNUTRN',
                'description' => 'Nutrition',
                'schoolid' => $this->school1['id'],
                'externalid' => 'M6UNUTRN',
                'externalsys' => 'Campus Solutions'
            ),
            1 => array (
                'name' => 'M6UCVENG',
                'description' => 'Civil Engineering',
                'schoolid' => $this->school1['id'],
                'externalid' => 'M6UCVENG',
                'externalsys' => 'Campus Solutions'
            ),
            2 => array (
                'name' => 'U8PBRSGY',
                'description' => 'Breast Surgery',
                'schoolid' => $this->school2['id'],
                'externalid' => 'UON|U8PBRSGY',
                'externalsys' => 'Campus Solutions'
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test get modules
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_modules()
    {
        $sms = $this->getMockBuilder(plugin_cs_sms::class)
            ->onlyMethods(array('callws'))
            ->setConstructorArgs(array($this->db, 0))
            ->getMock();
        $sms->expects($this->once())
            ->method('callws')
            ->will($this->returnValue($this->modulexml));
        $sms->get_modules();
        $queryTable = $this->query(
            array(
                'columns' => array(
                    'moduleid',
                    'fullname',
                    'schoolid',
                    'externalid',
                    'academic_year_start',
                    'sms',
                    'active'
                    ),
                'table' => 'modules',
                'where' => array(
                    array(
                        'column' => 'moduleid',
                        'operator' => 'NOT IN',
                        'value' => array('TRAIN', 'SYSTEM')
                    ),
                ),
                'orderby' => array('moduleid DESC'),
            )
        );
        $expectedTable = array(
            0 => array (
                'moduleid' => 'TESTMOD',
                'fullname' => 'Testing skills',
                'schoolid' => $this->school1['id'],
                'externalid' => '00001111',
                'academic_year_start' => '07/01',
                'sms' => 'Campus Solutions',
                'active' => 1
            ),
            1 => array (
                'moduleid' => 'NAAAXXXX',
                'fullname' => 'Self-marketing skills',
                'schoolid' => $this->school1['id'],
                'externalid' => '030003',
                'academic_year_start' => '07/01',
                'sms' => 'Campus Solutions',
                'active' => 1
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test get modules with session and module as arguments
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_module()
    {
        $sms = $this->getMockBuilder(plugin_cs_sms::class)
            ->onlyMethods(array('callws'))
            ->setConstructorArgs(array($this->db, 0))
            ->getMock();
        $sms->expects($this->once())
            ->method('callws')
            ->will($this->returnValue($this->modulexml));
        $sms->get_modules('030003', 2016);
        $queryTable = $this->query(
            array(
                'columns' => array(
                    'moduleid',
                    'fullname',
                    'schoolid',
                    'externalid',
                    'academic_year_start',
                    'sms',
                    'active'
                ),
                'table' => 'modules',
                'where' => array(
                    array(
                        'column' => 'moduleid',
                        'operator' => 'NOT IN',
                        'value' => array('TRAIN', 'SYSTEM')
                    ),
                ),
                'orderby' => array('moduleid DESC'),
            )
        );
        $expectedTable = array(
            0 => array (
                'moduleid' => 'TESTMOD',
                'fullname' => 'Testing skills',
                'schoolid' => $this->school1['id'],
                'externalid' => '00001111',
                'academic_year_start' => '07/01',
                'sms' => 'Campus Solutions',
                'active' => 1
            ),
            1 => array (
                'moduleid' => 'NAAAXXXX',
                'fullname' => 'Self-marketing skills',
                'schoolid' => $this->school1['id'],
                'externalid' => '030003',
                'academic_year_start' => '07/01',
                'sms' => 'Campus Solutions',
                'active' => 1
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test get enrolments with session only (all enrolments)
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_enrolments_all()
    {
        $sms = $this->getMockBuilder(plugin_cs_sms::class)
            ->onlyMethods(array('callws'))
            ->setConstructorArgs(array($this->db, 0))
            ->getMock();
        $sms->expects($this->once())
            ->method('callws')
            ->will($this->returnValue($this->enrolxml));
        $sms->get_enrolments(2016);
        $queryTable = $this->query(
            array(
                'columns' => array(
                    'grade',
                    'surname',
                    'username',
                    'title',
                    'email',
                    'gender',
                    'first_names',
                    'yearofstudy'
                ),
                'table' => 'users',
                'where' => array(
                    array(
                        'column' => 'username',
                        'operator' => 'NOT IN',
                        'value' => array('admin', 'cron', 'test1', 'test2')
                    )
                )
            )
        );
        $expectedTable = array(
            0 => array (
                'grade' => 'University Lecturer',
                'surname' => 'staff',
                'username' => 'staff',
                'title' => 'Mr',
                'email' => 'staffy@example.com',
                'gender' => 'Male',
                'first_names' => 'staffy',
                'yearofstudy' => 1
            ),
            1 => array (
                'grade' => 'M6UNUTRN',
                'surname' => 'John',
                'username' => 'brzhs5',
                'title' => 'Mr',
                'email' => 'brzhs5@example.com',
                'gender' => 'Male',
                'first_names' => 'Lewis',
                'yearofstudy' => 1
            ),
            2 => array (
                'grade' => 'M6UCVENG',
                'surname' => 'Watson',
                'username' => 'brzamh',
                'title' => 'Mr',
                'email' => 'brzamh@example.com',
                'gender' => 'Male',
                'first_names' => 'Daniel',
                'yearofstudy' => 1
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
        $student1 = \userutils::username_exists('brzhs5', $this->db);
        $student2 = \userutils::username_exists('brzamh', $this->db);
        $this->assertEquals('Staff', implode(Role::getUsersRoles(UserUtils::username_exists('staff', $this->db))));
        $this->assertEquals('Student', implode(Role::getUsersRoles($student1)));
        $this->assertEquals('Student', implode(Role::getUsersRoles($student2)));
        $queryTable = $this->query(array('table' => 'sid',
            'where' => array(array('column' => 'userID', 'operator' => 'IN', 'value' => array($student1, $student2)))));
        $expectedTable = array(
            0 => array(
                'student_id' => '10000667',
                'userID' => $student1
            ),
            1 => array(
                'student_id' => '10000670',
                'userID' => $student2
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
        $queryTable = $this->query(array('columns' => array('userID', 'idMod', 'calendar_year'), 'table' => 'modules_student',
            'where' => array(array('column' => 'userID', 'operator' => 'IN', 'value' => array($student1, $student2)))));
        $expectedTable = array(
            0 => array (
                'userID' => $student1,
                'idMod' => $this->mod['id'],
                'calendar_year' => 2016
            ),
            1 => array (
                'userID' => $student2,
                'idMod' => $this->mod['id'],
                'calendar_year' => 2016
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test get enrolments with missing year node
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_enrolments_all_missing_nodes()
    {
        $numenrolments = $this->rowcount('modules_student');
        $sms = $this->getMockBuilder(plugin_cs_sms::class)
            ->onlyMethods(array('callws'))
            ->setConstructorArgs(array($this->db, 0))
            ->getMock();
        $sms->expects($this->once())
            ->method('callws')
            ->will($this->returnValue($this->enrolxml2));
        $sms->get_enrolments(2016);
        // Users should still be created.
        $queryTable = $this->query(
            array(
                'columns' => array(
                    'grade',
                    'surname',
                    'username',
                    'title',
                    'email',
                    'gender',
                    'first_names',
                    'yearofstudy'
                ),
                'table' => 'users',
                'where' => array(
                    array(
                        'column' => 'username',
                        'operator' => 'NOT IN',
                        'value' => array('admin', 'cron', 'test1', 'test2')
                    )
                )
            )
        );
        $expectedTable = array(
            0 => array (
                'grade' => 'University Lecturer',
                'surname' => 'staff',
                'username' => 'staff',
                'title' => 'Mr',
                'email' => 'staffy@example.com',
                'gender' => 'Male',
                'first_names' => 'staffy',
                'yearofstudy' => 1
            ),
            1 => array (
                'grade' => 'M6UNUTRN',
                'surname' => 'John',
                'username' => 'brzhs5',
                'title' => 'Mr',
                'email' => 'brzhs5@example.com',
                'gender' => 'Male',
                'first_names' => 'Lewis',
                'yearofstudy' => 1
            ),
            2 => array (
                'grade' => 'M6UCVENG',
                'surname' => 'Watson',
                'username' => 'brzamh',
                'title' => 'Mr',
                'email' => 'brzamh@example.com',
                'gender' => 'Male',
                'first_names' => 'Daniel',
                'yearofstudy' => 1
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
        $student1 = \userutils::username_exists('brzhs5', $this->db);
        $student2 = \userutils::username_exists('brzamh', $this->db);
        $this->assertEquals('Staff', implode(Role::getUsersRoles(UserUtils::username_exists('staff', $this->db))));
        $this->assertEquals('Student', implode(Role::getUsersRoles($student1)));
        $this->assertEquals('Student', implode(Role::getUsersRoles($student2)));
        $queryTable = $this->query(array('table' => 'sid',
            'where' => array(array('column' => 'userID', 'operator' => 'IN', 'value' => array($student1, $student2)))));
        $expectedTable = array(
            0 => array(
                'student_id' => '10000667',
                'userID' => $student1
            ),
            1 => array(
                'student_id' => '10000670',
                'userID' => $student2
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
        // Enrolments should not be created as missing session.
        $this->assertEquals($numenrolments, $this->rowcount('modules_student'));
    }

    /**
     * Test get enrolments with session only (all enrolments)
     * - skip module with missing members nodes
     * - do not enrol left student
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_enrolments_all_skip_missing_members()
    {
        $sms = $this->getMockBuilder(plugin_cs_sms::class)
            ->onlyMethods(array('callws'))
            ->setConstructorArgs(array($this->db, 0))
            ->getMock();
        $sms->expects($this->once())
            ->method('callws')
            ->will($this->returnValue($this->enrolxml3));
        $sms->get_enrolments(2016);
        $queryTable = $this->query(
            array(
                'columns' => array(
                    'grade',
                    'surname',
                    'username',
                    'title',
                    'email',
                    'gender',
                    'first_names',
                    'yearofstudy'
                ),
                'table' => 'users',
                'where' => array(
                    array(
                        'column' => 'username',
                        'operator' => 'NOT IN',
                        'value' => array('admin', 'cron', 'test1', 'test2')
                    )
                )
            )
        );
        $expectedTable = array(
            0 => array (
                'grade' => 'University Lecturer',
                'surname' => 'staff',
                'username' => 'staff',
                'title' => 'Mr',
                'email' => 'staffy@example.com',
                'gender' => 'Male',
                'first_names' => 'staffy',
                'yearofstudy' => 1
            ),
            1 => array (
                'grade' => 'M6UNUTRN',
                'surname' => 'John',
                'username' => 'brzhs5',
                'title' => 'Mr',
                'email' => 'brzhs5@example.com',
                'gender' => 'Male',
                'first_names' => 'Lewis',
                'yearofstudy' => 1
            ),
            2 => array (
                'grade' => 'M6UCVENG',
                'surname' => 'Watson',
                'username' => 'brzamh',
                'title' => 'Mr',
                'email' => 'brzamh@example.com',
                'gender' => 'Male',
                'first_names' => 'Daniel',
                'yearofstudy' => 1
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
        $student1 = \userutils::username_exists('brzhs5', $this->db);
        $student2 = \userutils::username_exists('brzamh', $this->db);
        $this->assertEquals('Staff', implode(Role::getUsersRoles(UserUtils::username_exists('staff', $this->db))));
        $this->assertEquals('Student', implode(Role::getUsersRoles($student1)));
        $this->assertEquals('left', implode(Role::getUsersRoles($student2)));
        $queryTable = $this->query(array('table' => 'sid',
            'where' => array(array('column' => 'userID', 'operator' => 'IN', 'value' => array($student1)))));
        $expectedTable = array(
            0 => array(
                'student_id' => '10000667',
                'userID' => $student1
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
        $queryTable = $this->query(array('columns' => array('userID', 'idMod', 'calendar_year'), 'table' => 'modules_student',
            'where' => array(array('column' => 'userID', 'operator' => 'IN', 'value' => array($student1)))));
        $expectedTable = array(
            0 => array (
                'userID' => $student1,
                'idMod' => $this->mod['id'],
                'calendar_year' => 2016
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test get enrolments with session and moudle id
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_enrolments()
    {
        $sms = $this->getMockBuilder(plugin_cs_sms::class)
            ->onlyMethods(array('callws'))
            ->setConstructorArgs(array($this->db, 0))
            ->getMock();
        $sms->expects($this->once())
            ->method('callws')
            ->will($this->returnValue($this->enrolxml));
        $sms->get_enrolments(2016, '00001111');
        $queryTable = $this->query(array('columns' => array('grade', 'surname', 'username', 'title', 'email', 'gender', 'first_names', 'yearofstudy'),
            'table' => 'users', 'where' => array(array('column' => 'username', 'operator' => 'NOT IN', 'value' => array('admin', 'cron', 'test1', 'test2')))));
        $expectedTable = array(
            0 => array (
                'grade' => 'University Lecturer',
                'surname' => 'staff',
                'username' => 'staff',
                'title' => 'Mr',
                'email' => 'staffy@example.com',
                'gender' => 'Male',
                'first_names' => 'staffy',
                'yearofstudy' => 1
            ),
            1 => array (
                'grade' => 'M6UNUTRN',
                'surname' => 'John',
                'username' => 'brzhs5',
                'title' => 'Mr',
                'email' => 'brzhs5@example.com',
                'gender' => 'Male',
                'first_names' => 'Lewis',
                'yearofstudy' => 1
            ),
            2 => array (
                'grade' => 'M6UCVENG',
                'surname' => 'Watson',
                'username' => 'brzamh',
                'title' => 'Mr',
                'email' => 'brzamh@example.com',
                'gender' => 'Male',
                'first_names' => 'Daniel',
                'yearofstudy' => 1
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
        $student1 = \userutils::username_exists('brzhs5', $this->db);
        $student2 = \userutils::username_exists('brzamh', $this->db);
        $this->assertEquals('Staff', implode(Role::getUsersRoles(UserUtils::username_exists('staff', $this->db))));
        $this->assertEquals('Student', implode(Role::getUsersRoles($student1)));
        $this->assertEquals('Student', implode(Role::getUsersRoles($student2)));
        $queryTable = $this->query(array('table' => 'sid',
            'where' => array(array('column' => 'userID', 'operator' => 'IN', 'value' => array($student1, $student2)))));
        $expectedTable = array(
            0 => array(
                'student_id' => '10000667',
                'userID' => $student1
            ),
            1 => array(
                'student_id' => '10000670',
                'userID' => $student2
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
        $queryTable = $this->query(array('columns' => array('userID', 'idMod', 'calendar_year'), 'table' => 'modules_student'));
        $expectedTable = array(
            0 => array (
                'userID' => \userutils::username_exists('brzhs5', $this->db),
                'idMod' => $this->mod['id'],
                'calendar_year' => 2016
            ),
            1 => array (
                'userID' => \userutils::username_exists('brzamh', $this->db),
                'idMod' => $this->mod['id'],
                'calendar_year' => 2016
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test install mapping plugin - already installed on setup
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_install()
    {
        // Already installed by data generator so just check data tables.
        // Check tables are correct.
        $queryTable = $this->query(array('columns' => array('component', 'type', 'version'), 'table' => 'plugins'));
        $expectedTable = array(
            0 => array(
                'component' => 'plugin_cs_sms',
                'type' => 'SMS',
                'version' => $this->newversion
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
        $queryTable = $this->query(array('columns' => array('component', 'setting', 'value', 'type'), 'table' => 'config',
            'where' => array(array('column' => 'component', 'value' => 'plugin_cs_sms'), array('column' => 'setting', 'value' => 'installed'))));
        $expectedTable = array(
            0 => array(
                'component' => 'plugin_cs_sms',
                'setting' => 'installed',
                'value' => 1,
                'type' => 'boolean'
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test uninstall mapping plugin - already installed on setup
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_uninstall()
    {
        // Already installed by data generator.
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms();
        $this->assertEquals('OK', $sms->uninstall($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password')));
        // Check tables are correct.
        $queryTable = $this->rowcount('plugins');
        $this->assertEquals(0, $queryTable);
        $queryTable = $this->query(array('columns' => array('component', 'setting', 'value', 'type'), 'table' => 'config',
            'where' => array(array('column' => 'component', 'value' => 'plugin_cs_sms'), array('column' => 'setting', 'value' => 'installed'))));
        $expectedTable = array(
            0 => array(
                'component' => 'plugin_cs_sms',
                'setting' => 'installed',
                'value' => 0,
                'type' => 'boolean'
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test check plugin version
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_plugin_version()
    {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms();
        $sms->install($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
        $this->assertEquals($sms->get_installed_version(), $sms->get_plugin_version());
        $sms->uninstall($this->config->get('cfg_phpunit_db_user'), $this->config->get('cfg_phpunit_db_password'));
    }

    /**
     * Test supports_module_import
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_supports_module_import()
    {
        $lang = new \langpack();
        $component = 'plugins/SMS/plugin_cs_sms/plugin_cs_sms';
        $strings = $lang->get_all_strings($component);
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms();
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
    public function test_supports_module_import_disabled()
    {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms();
        $this->config->set_setting('enable_module', 0, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->config->set_setting('enable_enrolment', 0, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->assertFalse($sms->supports_module_import());
    }

    /**
     * Test supports_faculty_import
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_supports_faculty_import()
    {
        $lang = new \langpack();
        $component = 'plugins/SMS/plugin_cs_sms/plugin_cs_sms';
        $strings = $lang->get_all_strings($component);
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms();
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
    public function test_supports_faculty_import_disabled()
    {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms();
        $this->config->set_setting('enable_faculty', 0, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->assertFalse($sms->supports_faculty_import());
    }

    /**
     * Test supports_course_import
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_supports_course_import()
    {
        $lang = new \langpack();
        $component = 'plugins/SMS/plugin_cs_sms/plugin_cs_sms';
        $strings = $lang->get_all_strings($component);
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms();
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
    public function test_supports_course_import_disabled()
    {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms();
        $this->config->set_setting('enable_course', 0, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->assertFalse($sms->supports_course_import());
    }

    /**
     * Test supports_enrol_import
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_supports_enrol_import()
    {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms();
        $this->assertTrue($sms->supports_enrol_import());
    }

    /**
     * Test supports_enrol_import - disabled in config
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_supports_enrol_import_disabled()
    {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms();
        $this->config->set_setting('enable_enrolment', 0, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->assertFalse($sms->supports_enrol_import());
    }

    /**
     * Test supports_assessment_import
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_supports_assessment_import()
    {
        $lang = new \langpack();
        $component = 'plugins/SMS/plugin_cs_sms/plugin_cs_sms';
        $strings = $lang->get_all_strings($component);
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms();
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
    public function test_supports_assessment_import_disabled()
    {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms();
        $this->config->set_setting('enable_assessment', 0, \Config::BOOLEAN, 'plugin_cs_sms');
        $this->assertFalse($sms->supports_assessment_import());
    }

    /**
     * Test get_name
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_get_name()
    {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms();
        $this->assertEquals('Campus Solutions', $sms->get_name());
    }

    /**
     * Test enable_plugin
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_enable_plugin()
    {
        $config = $this->config->get_setting('plugin_sms', 'enabled_plugin');
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms();
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
    public function test_disable_plugin()
    {
        $sms = new plugins\SMS\plugin_cs_sms\plugin_cs_sms();
        $sms->disable_plugin();
        $config = $this->config->get_setting('plugin_sms', 'enabled_plugin');
        $this->assertEquals(array(), $config);
    }
}
