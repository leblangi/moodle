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
 * Unit tests for the manip question definition class.
 *
 * @package    qtype
 * @subpackage manip
 * @copyright  2008 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/manip/question.php');


/**
 * Unit tests for the manip question definition class.
 *
 * @copyright  2008 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_manip_question_test extends advanced_testcase {
    public function test_is_complete_response() {
        $question = test_question_maker::make_question('manip');
        $this->assertFalse($question->is_complete_response(array()));
    }

    public function test_is_gradable_response() {
        $question = test_question_maker::make_question('manip');
        $this->assertFalse($question->is_gradable_response(array()));
    }

    public function test_grading() {
        $question = test_question_maker::make_question('manip');
        $this->assertEquals(array(0, question_state::$invalid),
                $question->grade_response(array('attachment' => '')));
    }

    public function test_get_correct_response() {
        $question = test_question_maker::make_question('manip');

        $this->assertEquals(null, 
                $question->get_correct_response(array('attachment' => '')));
    }

    public function test_get_question_summary() {
        $sa = test_question_maker::make_question('manip');
        $qsummary = $sa->get_question_summary();
        $this->assertEquals('Apply a bold style to all paragraphs in the document provided.', $qsummary);
    }

    public function test_summarise_response() {
        $sa = test_question_maker::make_question('manip');
        $summary = $sa->summarise_response(array('attachment' => ''));
        $this->assertEquals(get_string('filenotsubmitted', 'qtype_manip'), $summary);
    }

    public function test_classify_response() {
        $sa = test_question_maker::make_question('manip');
        $sa->start_attempt(new question_attempt_step(), 1);
        $this->assertEquals(array(
                question_classified_response::no_response()),
                $sa->classify_response(array('attachment' => '')));
    }
}
