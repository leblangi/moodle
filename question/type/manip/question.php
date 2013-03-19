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
 * manip question definition class.
 *
 * @package    qtype
 * @subpackage manip
 * @copyright  2012 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

/**
 * Represents a docx file manipulation question.
 *
 * @copyright  2012 Université de Montréal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_manip_question extends question_graded_automatically {
    public $correctanswerid;
    public $feedbackcorrect;

    public $incorrectanswerid;
    public $feedbackincorrect;

    public $regex;
    
    public $minocc;
    public $maxocc;

    public $attachment;
    public $result;
    private $error;

    public function get_expected_data() {
        return array('attachment' => question_attempt::PARAM_FILES);
    }

    public function get_correct_response() {
        return null;
    }

    public function summarise_response(array $response) {
        if ($this->is_complete_response($response)) {
            return get_string('filesubmitted', 'qtype_manip');
        } else {
            return get_string('filenotsubmitted', 'qtype_manip');
        }
    }

    public function classify_response(array $response) {
        if (!$this->is_complete_response($response)) {
           return array($this->id => question_classified_response::no_response());
        }

        list($fraction) = $this->grade_response($response);
        if ($this->result) {
            return array($this->id => new question_classified_response(0,
                    qtype_manip::CORRECT_ANSWER, $fraction));
        } else {
            return array($this->id => new question_classified_response(1,
                    qtype_manip::INCORRECT_ANSWER, $fraction));
        }
    }

    public function is_complete_response(array $response) {
        if (!array_key_exists('attachment', $response) || !is_object($response['attachment'])) {
            $this->error = 'noanswer';
            return false;
        }
        $stored_files = $response['attachment']->get_files();
        if (!$stored_files) {
            $this->error = 'filenotsubmitted';
            return false;
        }

        $file = array_shift($stored_files);

        $content = $file->get_content();
        if ($content === FALSE) {
            $this->error = 'filenotreadable';
            return false;
        }
        return true;
    }

    public function is_gradable_response(array $response) {
        return $this->is_complete_response($response);
    }

    public function get_validation_error(array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string($this->error, 'qtype_manip');
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        return array_key_exists('attachment', $prevresponse) &&
            array_key_exists('attachment', $newresponse) &&
            is_object($prevresponse['attachment']) && is_object($newresponse['attachment']) &&
            ($prevresponse['attachment']->__toString() == $newresponse['attachment']->__toString());
    }

    public function grade_response(array $response) {
        $content = $this->get_valid_file_content_from_response($response);
        if (empty($content)) {
            return array(0, question_state::$invalid);
        }

        $regex = "/" . $this->regex . "/";
        $result = preg_match_all($regex, $content, $out);

        if (!$this->is_valid_regex($result)) {
            return array(0, question_state::$invalid);
        // If the minimum occurence is reached and if the maximum is not exceeded or unlimited, the answer is correct.
        } elseif ($result >= $this->minocc && ((!empty($this->minocc) && $result <= $this->maxocc) || empty($this->maxocc))) {
            $fraction = qtype_manip::CORRECT_VALUE;
        } else {
            $fraction = qtype_manip::INCORRECT_VALUE;
        }

        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }

    public function is_valid_regex($result) {
         if (($result === FALSE) && (preg_last_error() != PREG_NO_ERROR)) {
            if (preg_last_error() == PREG_INTERNAL_ERROR) {
                error_log('There is an internal error!');
            }
            else if (preg_last_error() == PREG_BACKTRACK_LIMIT_ERROR) {
                error_log('Backtrack limit was exhausted!');
            }
            else if (preg_last_error() == PREG_RECURSION_LIMIT_ERROR) {
                error_log('Recursion limit was exhausted!');
            }
            else if (preg_last_error() == PREG_BAD_UTF8_ERROR) {
                error_log('Bad UTF8 error!');
            }
            else if (preg_last_error() == PREG_BAD_UTF8_OFFSET_ERROR) {
                error_log('Bad UTF8 offset error!');
            }
            return false;
        } else {
            return true;
        }
    }

    public function get_valid_file_content_from_response(array $response) {
        if (!is_object($response['attachment']) || !$stored_files = $response['attachment']->get_files()) {
            return null;
        }
        $file = array_shift($stored_files);

        if (empty($file)) {
            return null;
        }
        // ZipArchive seem to only be able to open files and stored_files does
        // not let us read the files directly - so we have to copy_content_to
        // somewhere else.
        $zipfilename = tempnam(sys_get_temp_dir(), 'm');
        if (!$file->copy_content_to($zipfilename)) {
            error_log('ERROR: cannot write in temp folder');
            // Delete temporary file
            unlink($zipfilename);
            return null;
        }

        $zip = new ZipArchive;
        $content = null;
        if ($zip->open($zipfilename) === TRUE) {
            $content =  $zip->getFromName('word/document.xml');
            $zip->close();
        }
        // Delete temporary file
        unlink($zipfilename);
        return $content;
    }

    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        if ($component == 'question' && $filearea == 'answerfeedback') {
            $answerid = reset($args); // itemid is answer id.
            $response = $qa->get_last_qt_var('answer', '');

            return $options->feedback &&
                    // TODO: test this condition
                    ($answerid == $this->correctanswerid && $response);

        } elseif ($component == 'question' && $filearea == 'response_attachment') {
            $answerid = reset($args); // itemid is answer id and should match attemptstepid
            $response = $qa->get_last_qt_var('attachment', '');

            $i = $qa->get_reverse_step_iterator();
            while($i->valid()) {
                if ($i->current()->get_id() == $answerid) {
                    return true;
                }
                $i->next();
            }
            return false;

        } else {
            return parent::check_file_access($qa, $options, $component, $filearea,
                    $args, $forcedownload);
        }
    }
}
