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
* Gradebook publishing file
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

/**
 * Gradebook helper class.
 */
class gradebook_helper {
    
    /**
     * Publish a gradebook for a paper to a file
     * @param mysqli $db database connection
     * @param integer $paper_id identifier of paper to publish gradebook for
     * @param string $gradebookdir path to directory to write file
     */
    static public function publish($db, $paper_id, $gradebookdir) {
        $gradebook = new \gradebook($db);
        // Only interested in summative papers.
        if (\Paper_utils::get_paper_type($paper_id, $db) == '2') { 
            $grades = $gradebook->get_paper_gradebook(\gradebook::PAPER, $paper_id);
        }
    }
    
    /**
     * Publish whole gradebook for an academic session to a file
     * @param mysqli $db database connection
     * @param integer $session academic year to publish gradebook for
     * @param string $gradebookdir path to directory to write file
     */
    static public function publish_all($db, $session, $gradebookdir) {
        $gradebook = new \gradebook($db);
        // Only interested in summative papers.
        $papers = \Paper_utils::get_papers_by_session($session, '2', $db);
        foreach ($papers as $paper_id) {
            $grades = $gradebook->get_paper_gradebook(\gradebook::PAPER, $paper_id);
        }
    }
}