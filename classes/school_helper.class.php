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
* School import helper file
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * School import helper class.
 */
class school_helper {
    /**
     * Intergrate the school members node of the facultylist xml
     * @param array $schoools schools array to populate
     * @param simpleXMLObject $schoolnode xml for schools
     * @param simpleXMLObject $parentnode xml for schools faculty
     */
    static public function get_schools(&$schools, $schoolnode, $parentnode) {
        $xpath = new \DOMXPath($parentnode->ownerDocument);
        $results = $xpath->query('./FacultyID', $parentnode);
        if ($results->length > 0) {
            $facultyid = $results->item(0)->nodeValue;
        }
        $i = 0;
        foreach ($schoolnode as $school) {
            if ($school->hasChildNodes()) {
                foreach ($school->childNodes as $childnode) {
                    $schools[$facultyid][$i][$childnode->nodeName] = $childnode->nodeValue;
                }
                $i++;
            }
        }
    }
}