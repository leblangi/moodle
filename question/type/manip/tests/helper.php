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
 * Test helpers for the manip question type.
 *
 * @package    qtype_manip
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Test helper class for the manip question type.
 *
 * @copyright  2012 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_manip_test_helper extends question_test_helper {
    public function get_test_questions() {
        return array('inbold', 'centrallyaligned');
    }

    /**
     * Makes a manip question with correct answer is
     * a  .docx file with 8 paragraphs in bold.
     * @return qtype_manip_question
     */
    public function make_manip_question_inbold() {
        question_bank::load_question_definition_classes('manip');
        $sa = new qtype_manip_question();
        test_question_maker::initialise_a_question($sa);
        $sa->name = 'Word manipulation question';
        $sa->questiontext = 'Apply a bold style to all paragraphs in the document provided.';
        $sa->generalfeedback = 'The eight paragraphs should be bold to to have it good.';
        $sa->regex = '\<w\:b\/\>';
        $sa->minocc = 8;
        $sa->maxocc = 8;
        $sa->answers = array(
            13 => new question_answer(13, 'Correct', 1.0, '', FORMAT_HTML),
            14 => new question_answer(14, 'Incorrect', 0.0, '', FORMAT_HTML),
        );
        $sa->qtype = question_bank::get_qtype('manip');

        return $sa;
    }

        /**
     * Makes a manip question with correct answer is
     * a  .docx file with 8 paragraphs in bold.
     * @return qtype_manip_question
     */
    public function make_manip_question_centrallyaligned() {
        question_bank::load_question_definition_classes('manip');
        $sa = new qtype_manip_question();
        test_question_maker::initialise_a_question($sa);
        $sa->name = 'Word manipulation question';
        $sa->questiontext = 'Center align at least three paragraphs in the document provided.';
        $sa->generalfeedback = 'At least three paragraphs should be centrally aligned to to have good.';
        $sa->regex = '\<w\:jc w\:val\="center"';
        $sa->minocc = 3;
        $sa->maxocc = 0;
        $sa->answers = array(
            13 => new question_answer(13, 'Correct', 1.0, '', FORMAT_HTML),
            14 => new question_answer(14, 'Incorrect', 0.0, '', FORMAT_HTML),
        );
        $sa->qtype = question_bank::get_qtype('manip');

        return $sa;
    }
}
