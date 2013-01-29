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
        // error_log('get_expected_data');
        return array('attachment' => question_attempt::PARAM_FILES);
    }

    public function get_correct_response() {
        // error_log('get_correct_response');
        return null;
    }


    // Si on veut forcer le type de question à n'être évalué qu'en deferredfeedback.
    /*
    public function make_behaviour(question_attempt $qa, $preferredbehaviour) {
        return question_engine::make_archetypal_behaviour('deferredfeedback', $qa);
    }
    */

    public function summarise_response(array $response) {
        //error_log('summarise_response');
        if ($this->is_complete_response($response)) {
            return get_string('filesubmitted', 'qtype_manip');
        } else {
            return get_string('filenotsubmitted', 'qtype_manip');
        }
    }

    public function classify_response(array $response) {
        // error_log('classify_response');
        if (!$this->is_complete_response($response)) {
           return array($this->id => question_classified_response::no_response());
        }

        list($fraction) = $this->grade_response($response);
        if ($this->result) {
            return array($this->id => new question_classified_response(0,
                    get_string('correctanswer', 'qtype_manip'), $fraction));
        } else {
            return array($this->id => new question_classified_response(1,
                    get_string('incorrectanswer', 'qtype_manip'), $fraction));
        }
    }

    public function is_complete_response(array $response) {
        error_log('is_complete_response');
        // TODO: mettre les messages d'erreur dans le fichier de langue
        if (!array_key_exists('attachment', $response) || !is_object($response['attachment'])) {
            $this->error = 'noanswer';
            return false;
        }
        $stored_file = $response['attachment']->get_files();
        if (!$stored_file) {
            $this->error = 'filenotsubmitted';
            return false;
        }

        // error_log ('is_complete_response ($stored_file) :: '. print_r($stored_file, true));
        $file = array_shift($stored_file);
        // error_log ('is_complete_response ($file) :: '. print_r($file, true));
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
        error_log('is_same_response');
        // error_log('is_same_response (prev) :: '. var_export($prevresponse, true) .' (new) ::'. var_export($newresponse, true));
        // error_log('is_same_response (new->attachement) ::'. var_export($newresponse['attachment']->__toString(), true));

        return array_key_exists('attachment', $prevresponse) &&
            array_key_exists('attachment', $newresponse) &&
            is_object($prevresponse['attachment']) && is_object($newresponse['attachment']) &&
            ($prevresponse['attachment']->__toString() == $newresponse['attachment']->__toString());
    }

    public function grade_response(array $response) {
        error_log('grade_response');
        // error_log('grade_response ($response) :: '. print_r($response, true));
        $stored_file = $response['attachment']->get_files();
        // error_log('grade_response ($stored_files) :: '. print_r($stored_file, true));
        $file = array_shift($stored_file);

        // ZipArchive seem to only be able to open files and stored_file does
        // not let us read the file directly - so we have to copy_content_to
        // somewhere else.
        $zipfilename = tempnam(sys_get_temp_dir(), 'm');
        if (!$file->copy_content_to($zipfilename)) {
            // TODO: Log this error which, really, should not happen.
            return array(0, question_state::$invalid); // TODO: test this out
        }

        $zip = new ZipArchive;
        if ($zip->open($zipfilename) === TRUE) {
            $content =  $zip->getFromName('word/document.xml');
            $zip->close();
        } else {
            // TODO LOG TO COURSE (if it's possible to find course id - otherwise
            // log to system log)
            debugging('zip file could not be opened');
            return array(0, question_state::$invalid); // TODO: test this out
        }

        // GRADING WITH STRPOS
        // Si le système demeure "tout ou rien", strpos est plus rapide que preg_match_all
        // Ça ne fonctionne pas, par contre, si on veut évaluer en fonction du
        // nombre d'occurence trouvées ou que les "patterns" deviennent plus complexes, il
        // pourrait être nécessaire d'utiliser preg_match_all.
        /*$pos = strpos($content, $this->regex);
        if ($pos === FALSE) {
            //add_to_log()
            $fraction = 0.0;
        } else {
            $fraction = 1.0;
        }*/

        //// GRADING WITH PREG_MATCH_ALL
        // Unless the patterns are real regex, strpos is faster and simpler.
        $regex = "/" . $this->regex . "/";
        $result = preg_match_all($regex, $content, $out);
        error_log('grade_response (result) :: '. $result);

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
            // TODO: trouver comment retourner une question clairement invalide,
            // pour éviter que le résultat ne compte (et permettre à l'étudiant
            // d'envoyer un autre fichier?)
            return array(0, question_state::$invalid); // TODO: test this out
        // If the minimum occurence is reached and if the maximum is not exceeded or unlimited, the answer is correct.
        } elseif ($result >= $this->minocc && ((!empty($this->minocc) && $result <= $this->maxocc) || empty($this->maxocc))) {
            $fraction = 1.0;
        } else {
            $fraction = 0.0;
        }

        // Delete temporary file
        unlink($zipfilename);

        return array($fraction, question_state::graded_state_for_fraction($fraction));
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
