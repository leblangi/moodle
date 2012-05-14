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
 * file analysis question definition class.
 *
 * @package    qtype
 * @subpackage fileanalysis
 * @copyright &copy; 2012 Université de Montréal
 * @author gilles-philippe.leblanc@umontreal.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/shortanswer/question.php');
/**
 * Represents a file analysis question.
 *
 * @copyright &copy; 2012 Université de Montréal
 * @author gilles-philippe.leblanc@umontreal.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_fileanalysis_question extends qtype_shortanswer_question {
    public function __construct() {
        parent::__construct();
    }
	
	public function summarise_response(array $response) {
		$response['answer'] = $this->get_correct_answer()->answer;
        if (isset($response['answer'])) {
            return $response['answer'];
        } else {
            return null;
        }
    }
	
	/**
     * Get an answer that contains the feedback and fraction that should be
     * awarded for this resonse.
     * @param array $response a response.
     * @return question_answer the matching answer.
     */
    public function get_matching_answer(array $response) {
		//$response['answer'] = $this->get_correct_answer()->answer;
        return $this->gradingstrategy->grade($response);
    }

    public function grade_response(array $response) {
		//$response['answer'] = $this->get_correct_answer()->answer;
        $answer = $this->get_matching_answer($response);
        if ($answer) {
            return array($answer->fraction,
                    question_state::graded_state_for_fraction($answer->fraction));
        } else {
            return array(0, question_state::$gradedwrong);
        }
    }

    public function classify_response(array $response) {
		//$response['answer'] = $this->get_correct_answer()->answer;
        if (empty($response['answer'])) {
            return array($this->id => question_classified_response::no_response());
        }

        $ans = $this->get_matching_answer($response);
        if (!$ans) {
            return array($this->id => question_classified_response::no_response());
        }
        return array($this->id => new question_classified_response(
                $ans->id, $response['answer'], $ans->fraction));
    }
	
    public function is_complete_response(array $response) {
		//$response['answer'] = $this->get_correct_answer()->answer;
        return array_key_exists('answer', $response) &&
                ($response['answer'] || $response['answer'] === '0');
    }

    public function get_validation_error(array $response) {
		// $response['answer'] = $this->get_correct_answer()->answer;
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string('pleaseenterananswer', 'qtype_fileanalysis');
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
		//$newresponse['answer'] = $this->get_correct_answer()->answer;
        return question_utils::arrays_same_at_key_missing_is_blank(
                $prevresponse, $newresponse, 'answer');
    }

    public function get_answers() {
        return $this->answers;
    }
	
    public function compare_response_with_answer(array $response, question_answer $answer) {
        return $response['answer'] == $answer->answer;
    }
	
	public function check_file_access($qa, $options, $component, $filearea,
            $args, $forcedownload) {
        if ($component == 'question' && $filearea == 'answerfeedback') {
            $currentanswer = $qa->get_last_qt_var('answer');
            $answer = $qa->get_question()->get_matching_answer(array('answer' => $currentanswer));
            $answerid = reset($args); // itemid is answer id.
            return $options->feedback && $answerid == $answer->id;

        } else if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);

        } else {
            return parent::check_file_access($qa, $options, $component, $filearea,
                    $args, $forcedownload);
        }
    }
}
