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

    plugins\SMS\plugin_cs_sms\modules_helper as modules_helper;
/**
 * Test modules helper functions
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class modules_helpertest extends UnitTest
{


    /**
     * Test module mapping
     * @group sms
     * @group plugin_cs_sms
     */
    public function test_module_campus_mapping()
    {
        // UK CS module code.
        $this->assertEquals('COMP1001', modules_helper::module_campus_mapping('COMP1001_U'));
// CN CS module code.
        $this->assertEquals('COMP1001_UNNC', modules_helper::module_campus_mapping('COMP1001_C'));
// MY CS module code.
        $this->assertEquals('COMP1001_UNMC', modules_helper::module_campus_mapping('COMP1001_M'));
// Non naming convention module code.
        $this->assertEquals('ABCDEF', modules_helper::module_campus_mapping('ABCDEF'));
    }
}
