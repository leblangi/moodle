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
 * Defines the editing form for the true-false question type.
 *
 * @package    qtype
 * @subpackage manip
 * @copyright  2012 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot.'/question/type/edit_question_form.php');


/**
 * manip question editing form definition.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_manip_edit_form extends question_edit_form {
    /**
     * Add question-type specific form fields.
     *
     * @param object $mform the form being built.
     */
    protected function definition_inner($mform) {
        global $PAGE;

        $PAGE->requires->js_init_call('M.qtype_manip.initQuestionForm', null, true, array(
            'name'     => 'qtype_manip',
            'fullpath' => '/question/type/manip/module.js',
            'requires' => array('base', 'dom', 'node', 'event', 'widget-base'),
        ));

        $qtype = question_bank::get_qtype('manip');
        $mform->addElement('select', 'regexselector',
                get_string('regexselector', 'qtype_manip'), $qtype->get_regex());
        $mform->addHelpButton('regexselector', 'regexselector', 'qtype_manip');

        $mform->addElement('text', 'regex', get_string('regex', 'qtype_manip'), array('size' => '75'));
        $mform->setType('regex', PARAM_RAW);
        $mform->addHelpButton('regex', 'regex', 'qtype_manip');
        $mform->addRule('regex', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'minocc', get_string('minocc', 'qtype_manip'), array('size' => '4'));
        $mform->setType('minocc', PARAM_INT);
        $mform->addHelpButton('minocc', 'minocc', 'qtype_manip');
        $mform->addRule('minocc', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'maxocc', get_string('maxocc', 'qtype_manip'), array('size' => '4'));
        $mform->setType('maxocc', PARAM_INT);
        $mform->addHelpButton('maxocc', 'maxocc', 'qtype_manip');
        $mform->setDefault('maxocc', 0);

        $mform->addElement('editor', 'feedbackcorrect', get_string('feedbackcorrect', 'qtype_manip'), array('rows' => 10), $this->editoroptions);
        $mform->setType('feedbackcorrect', PARAM_RAW);
        $mform->addHelpButton('feedbackcorrect', 'feedbackcorrect', 'qtype_manip');

        $mform->addElement('editor', 'feedbackincorrect', get_string('feedbackincorrect', 'qtype_manip'), array('rows' => 10), $this->editoroptions);
        $mform->setType('feedbackincorrect', PARAM_RAW);
        $mform->addHelpButton('feedbackincorrect', 'feedbackincorrect', 'qtype_manip');
    }

    // If more granular fractions are desired, this would be the place to start adding such a feature.
    /*
    protected function get_per_answer_fields($mform, $label, $gradeoptions, &$repeatedoptions, &$answersoption) {
        $repeated = array();
        $repeated[] = $mform->createElement('header', 'answerhdr', $label);
        $repeated[] = $mform->createElement('text', 'answer', get_string('answer', 'question'), array('size' => 80));
        $repeated[] = $mform->createElement('select', 'fraction', get_string('grade'), $gradeoptions);
        $repeated[] = $mform->createElement('editor', 'feedback', get_string('feedback', 'question'), array('rows' => 5), $this->editoroptions);
        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = 'answers';
        return $repeated;
    }
    */

    public function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);

        if (!empty($question->options->correct)) {
            $correctanswer = $question->options->answers[$question->options->correct];
            $question->correctanswerid = $question->options->correct;

            $draftid = file_get_submitted_draft_itemid('correctanswer');
            $answerid = $question->options->correct;

            $question->feedbackcorrect = array();
            $question->feedbackcorrect['format'] = $correctanswer->feedbackformat;
            $question->feedbackcorrect['text'] = file_prepare_draft_area(
                    $draftid, // draftid
                    $this->context->id, // context
                    'question', // component
                    'answerfeedback', // filarea
                    !empty($answerid) ? (int) $answerid : null, // itemid
                    $this->fileoptions, // options
                    $correctanswer->feedback // text
            );
            $question->feedbackcorrect['itemid'] = $draftid;
        }

        if (!empty($question->options->incorrect)) {
            $incorrectanswer = $question->options->answers[$question->options->incorrect];
            $question->incorrectanswerid = $question->options->incorrect;

            $draftid = file_get_submitted_draft_itemid('incorrectanswer');
            $answerid = $question->options->incorrect;

            $question->feedbackincorrect = array();
            $question->feedbackincorrect['format'] = $incorrectanswer->feedbackformat;
            $question->feedbackincorrect['text'] = file_prepare_draft_area(
                    $draftid, // draftid
                    $this->context->id, // context
                    'question', // component
                    'answerfeedback', // filarea
                    !empty($answerid) ? (int) $answerid : null, // itemid
                    $this->fileoptions, // options
                    $incorrectanswer->feedback // text
            );
            $question->feedbackincorrect['itemid'] = $draftid;
        }
        if (!empty($question->options->regex)) {
            $question->regex = $question->options->regex;
        }

        if (!empty($question->options->minocc)) {
            $question->minocc = $question->options->minocc;
        }

        if (!empty($question->options->maxocc)) {
            $question->maxocc = $question->options->maxocc;
        }

        $qtype = question_bank::get_qtype('manip');
        if (!empty($question->options->regex) && array_key_exists($question->options->regex, $qtype->get_regex())) {
            $question->regexselector = $question->options->regex;
        }else{
            $question->regexselector = 'custom';
        }

        return $question;
    }

    public function qtype() {
        return 'manip';
    }

    /**
     * Custom validation to check if the regex is valid and if the minocc and maxocc are coherent
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files)  {
        $errors = parent::validation($data, $files);
        $regex = $data['regex'];
        $minocc = $data['minocc'];
        $maxocc = $data['maxocc'];

        // If not valid regex
        if (@preg_match("/" . $regex . "/", '') === false) {
            $errors['regex'] = get_string('regexerror', 'qtype_manip');
        }

        // If minocc is not greater or equal to 1
        if ($minocc < 1) {
            $errors['minocc'] = get_string('minoccerror', 'qtype_manip');
        }

        // If maxocc is not greater or equal to minocc
        if ($maxocc < $minocc && $maxocc != 0) {
            $errors['maxocc'] = get_string('maxoccerror', 'qtype_manip');
        }
 
        return $errors;
    }
}
