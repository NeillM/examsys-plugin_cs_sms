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

use testing\unittest\UnitTest;
use org\bovigo\vfs\vfsStream;
use plugins\SMS\plugin_cs_sms\lockfile_helper;

/**
 *Tests that the lock factory works correctly.
 *
 * @covers \plugins\SMS\plugin_cs_sms\lockfile_helper
 *
 * @group sms
 * @group plugin_cs_sms
 */
class lockfile_helperTest extends UnitTest
{
    /** @var string The name of the directory we will use during the tests. */
    protected const DIR_PATH = 'tmp';

    /** @var int The number of hours that locks will be configured to last for. */
    protected const LOCK_TIME = 1;

    /** @var \org\bovigo\vfs\vfsStreamDirectory  */
    protected $directory;

    /**
     * Sets up the directory for us to use in the tests.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->directory = vfsStream::setup(self::DIR_PATH, 0777);
    }

    /**
     * Adds a file to the directory we created.
     *
     * @param string $name The name of the file to be created.
     * @param string $content The content of the file.
     * @return string The full path to the file.
     */
    protected function create_file(string $name, string $content): string
    {
        $path = $this->directory->url() . '/';
        $filepath = $path . $name;
        file_put_contents($filepath, $content);
        return $filepath;
    }

    /**
     * Tests that a file is deleted when a lock has expired.
     *
     * @return void
     */
    public function testLockFiletTimeoutExpiredLock()
    {
        // Create lockfile that should be deleted.
        $time = time() - ((self::LOCK_TIME * 3600) + 1);
        $name = 'enrolment.lock';
        $filepath = $this->create_file($name, $time);
        $this->assertTrue($this->directory->hasChild($name));

        lockfile_helper::lockfiletimeout($filepath, self::LOCK_TIME);

        // Test that the file is no longer present.
        $this->assertFalse($this->directory->hasChild($name));
    }

    /**
     * Tests that a file is not deleted when a lock is valid.
     *
     * @return void
     */
    public function testLockFiletTimeoutLocked()
    {
        // Create lockfile that should not be deleted.
        $time = time();
        $name = 'enrolment.lock';
        $filepath = $this->create_file($name, $time);
        $this->assertTrue($this->directory->hasChild($name));

        lockfile_helper::lockfiletimeout($filepath, self::LOCK_TIME);

        // Test that the file has not been deleted.
        $this->assertTrue($this->directory->hasChild($name));
    }
}
