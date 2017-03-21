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
* Lock file helper file
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2017 onwards The University of Nottingham
*/

/**
 * Lock file helper class.
 */
class lockfile_helper {
    /**
     * Timeout lock file after a day - removes lock file.
     * @param string $lockfile filename of lock file
     * @param integer $lifespan lifespan of lock file in hours
     */
    static public function lockfiletimeout($lockfile, $lifespan) {
        if (file_exists($lockfile)) {
            $lastlocked = file_get_contents($lockfile);
            $onedayago = strtotime('-$lifespan hour', time());
            if ($lastlocked < $onedayago) {
                unlink($lockfile);
            }
        }
    }
}