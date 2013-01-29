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
 * Question type class for the short answer question type.
 *
 * @package    qtype
 * @subpackage simulationanswer
 * @copyright  2011 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The simulation answer question type.
 *
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/simulationanswer/question.php');
require_once($CFG->dirroot . '/question/type/shortanswer/question.php');
require_once($CFG->dirroot . '/question/type/shortanswer/questiontype.php');

/**
 * The simulation answer question type.
 *
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_simulationanswer extends qtype_shortanswer {
	
    public function extra_question_fields() {
        return array('question_simulationanswer', 'usecase');
    }
	
	public function move_files($questionid, $oldcontextid, $newcontextid) {
		parent::move_files($questionid, $oldcontextid, $newcontextid);
		$fs = get_file_storage();
		$fs->move_area_files_to_new_context($oldcontextid, $newcontextid, 'qtype_simulationanswer', 'simulationfile', $questionid);
    }
	
    public function save_question_options($question) {
        parent::save_question_options($question);
		file_save_draft_area_files($question->simulationfile, $question->context->id,
        	'qtype_simulationanswer', 'simulationfile', $question->id,
        	array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1));
    }
	
	public function import_from_xml($data, $question, $format, $extra=null) {
        $question = parent::import_from_xml($data, $question, $format, $extra);
		$filexml = $format->getpath($data, array('#', 'file'), array());
        $question->simulationfile = $this->import_files_to_draft_file_area($format, $filexml);
		return $question;
    }

    /*
     * Export question to the Moodle XML format
     *
     * Export question using information from extra_question_fields function
     * If some of you fields contains id's you'll need to reimplement this
     */
    public function export_to_xml($question, $format, $extra=null) {
		$fs = get_file_storage();
		$contextid = $question->contextid;
        $extraquestionfields = $this->extra_question_fields();
        if (!is_array($extraquestionfields)) {
            return false;
        }

        //omit table name
        array_shift($extraquestionfields);
        $expout='';
		$simulationfile = 'simulationfile';
		$files = $fs->get_area_files($contextid, 'qtype_simulationanswer', $simulationfile, $question->id);
        $expout .= $this->write_files($files, 2);
		
        foreach ($extraquestionfields as $field) {
            $exportedvalue = $format->xml_escape($question->options->$field);
            $expout .= "    <$field>{$exportedvalue}</$field>\n";
        }
		
        $extraanswersfields = $this->extra_answer_fields();
        if (is_array($extraanswersfields)) {
            array_shift($extraanswersfields);
        }
        foreach ($question->options->answers as $answer) {
            $percent = 100 * $answer->fraction;
            $expout .= "    <answer fraction=\"$percent\">\n";
            $expout .= $format->writetext($answer->answer, 3, false);
            $expout .= "      <feedback>\n";
            $expout .= $format->writetext($answer->feedback, 4, false);
            $expout .= "      </feedback>\n";
            if (is_array($extraanswersfields)) {
                foreach ($extraanswersfields as $field) {
                    $exportedvalue = $format->xml_escape($answer->$field);
                    $expout .= "      <{$field}>{$exportedvalue}</{$field}>\n";
                }
            }

            $expout .= "    </answer>\n";
        }
        return $expout;
    }
	
	/**
     * Create a draft files area, import files into it and return the draft item id.
     * @param qformat_xml $format
     * @param array $xml an array of <file> nodes from the the parsed XML.
     * @return integer draftitemid
     */
    public function import_files_to_draft_file_area($format, $xml) {
        global $USER;
        $fs = get_file_storage();
        $files = $format->import_files($xml);
        $usercontext = get_context_instance(CONTEXT_USER, $USER->id);
        $draftitemid = file_get_unused_draft_itemid();
        foreach ($files as $file) {
            $record = new stdClass();
            $record->contextid = $usercontext->id;
            $record->component = 'user';
            $record->filearea  = 'draft';
            $record->itemid    = $draftitemid;
            $record->filename  = $file->name;
            $record->filepath  = '/';
            $fs->create_file_from_string($record, $this->decode_file($file));
        }
        return $draftitemid;
    }
	
	/**
     * Convert files into text output in the given format.
     * This method is copied from qformat_default as a quick fix, as the method there is
     * protected.
     * @param array
     * @param string encoding method
     * @return string $string
     */
    public function write_files($files, $indent) {
        if (empty($files)) {
            return '';
        }
        $string = '';
        foreach ($files as $file) {
            if ($file->is_directory()) {
                continue;
            }
            $string .= str_repeat('  ', $indent);
            $string .= '<file name="' . $file->get_filename() . '" encoding="base64">';
            $string .= base64_encode($file->get_content());
            $string .= "</file>\n";
        }
        return $string;
    }
}
