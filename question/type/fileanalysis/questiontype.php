<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Question type class for the file analysis question type.
 *
 * @package    qtype
 * @subpackage fileanalysis
 * @copyright &copy; 2012 Université de Montréal
 * @author gilles-philippe.leblanc@umontreal.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 
defined('MOODLE_INTERNAL') || die();


/**
 * The simulation answer question type.
 *
 * @copyright &copy; 2012 Université de Montréal
 * @author gilles-philippe.leblanc@umontreal.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
 require_once($CFG->dirroot . '/question/type/shortanswer/questiontype.php');
 
class qtype_fileanalysis extends qtype_shortanswer {
	public function requires_qtypes() {
        return array('shortanswer', 'filedepot');
    }
	
	public function extra_question_fields() {
        return array('question_fileanalysis', 'usecase');
    }
	
}