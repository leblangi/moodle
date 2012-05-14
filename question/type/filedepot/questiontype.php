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
 * Question type class for the filedepot question type.
 *
 * @package    qtype
 * @subpackage filedepot
 * @copyright &copy; 2012 Université de Montréal
 * @author gilles-philippe.leblanc@umontreal.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * The filedepot question type.
 *
 * @copyright &copy; 2012 Université de Montréal
 * @author gilles-philippe.leblanc@umontreal.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_filedepot extends question_type {
	public function requires_qtypes() {
        return array('essay', 'fileanalysis');
    }
	
    public function is_manual_graded() {
        return true;
    }
	
	public function actual_number_of_questions($question) {
        // By default, each question is given one number
        return 0;
    }
	
    public function response_file_areas() {
        return array('attachments', 'answer');
    }

    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record('qtype_filedepot_options',
                array('questionid' => $question->id), '*', MUST_EXIST);
        parent::get_question_options($question);
    }

    public function save_question_options($formdata) {
        global $DB;
        $context = $formdata->context;

        $options = $DB->get_record('qtype_filedepot_options', array('questionid' => $formdata->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $formdata->id;
            $options->id = $DB->insert_record('qtype_filedepot_options', $options);
        }

        $options->responseformat = $formdata->responseformat;
        $options->responsefieldlines = $formdata->responsefieldlines;
        $options->attachments = $formdata->attachments;
        $options->graderinfo = $this->import_or_save_files($formdata->graderinfo,
                $context, 'qtype_filedepot', 'graderinfo', $formdata->id);
        $options->graderinfoformat = $formdata->graderinfo['format'];
        $DB->update_record('qtype_filedepot_options', $options);
		file_save_draft_area_files($formdata->basefile, $context->id,
        	'qtype_filedepot', 'basefile', $formdata->id,
        	array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1));
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->responseformat = $questiondata->options->responseformat;
        $question->responsefieldlines = $questiondata->options->responsefieldlines;
        $question->attachments = $questiondata->options->attachments;
        $question->graderinfo = $questiondata->options->graderinfo;
        $question->graderinfoformat = $questiondata->options->graderinfoformat;
    }

    /**
     * @return array the different response formats that the question type supports.
     * internal name => human-readable name.
     */
    public function response_formats() {
        return array(
            'editorfilepicker' => get_string('formateditorfilepicker', 'qtype_filedepot'),
        );
    }

    /**
     * @return array the choices that should be offered for the input box size.
     */
    public function response_sizes() {
        $choices = array();
        $choices[5] = get_string('nlines', 'qtype_filedepot', 5);
        return $choices;
    }

    /**
     * @return array the choices that should be offered for the number of attachments.
     */
    public function attachment_options() {
        return array(1 => '1');
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $fs = get_file_storage();
        $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'qtype_filedepot', 'graderinfo', $questionid);
		$fs->move_area_files_to_new_context($oldcontextid,
				$newcontextid, 'qtype_filedepot', 'basefile', $questionid);

    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $fs = get_file_storage();
        $fs->delete_area_files($contextid, 'qtype_filedepot', 'graderinfo', $questionid);
		$fs->delete_area_files($contextid, 'qtype_filedepot', 'basefile', $questionid);
    }
	
	public function import_from_xml($data, $question, $format, $extra=null) {
        // get common parts
		$question_type = $data['@']['type'];
		
        if ($question_type != $this->name()) {
            return false;
        }
		
        $qo = $format->import_headers($data);
        $qo->qtype = $question_type;
		
		// parts common with essay question type
        $qo->responseformat = $format->getpath($data,
                array('#', 'responseformat', 0, '#'), 'editor');
        $qo->responsefieldlines = $format->getpath($data,
                array('#', 'responsefieldlines', 0, '#'), 15);
        $qo->attachments = $format->getpath($data,
                array('#', 'attachments', 0, '#'), 0);
        $qo->graderinfo['text'] = $format->getpath($data,
                array('#', 'graderinfo', 0, '#', 'text', 0, '#'), '', true);
        $qo->graderinfo['format'] = FORMAT_HTML;
        $qo->graderinfo['files'] = $format->import_files($format->getpath($data,
                array('#', 'graderinfo', '0', '#', 'file'), array()));
		//$qo->basefile = $format->import_files($format->getpath($data,
                //array('#', 'basefile', '0', '#', 'file'), array()));
		// parts particular to filedepot
		$filexml = $format->getpath($data, array('#', 'file'), array());
        $qo->basefile = $this->import_files_to_draft_file_area($format, $filexml);
		return $qo;
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
        
        $expout='';
		$expout .= "    <responseformat>" . $question->options->responseformat .
                "</responseformat>\n";
        $expout .= "    <responsefieldlines>" . $question->options->responsefieldlines .
                "</responsefieldlines>\n";
        $expout .= "    <attachments>" . $question->options->attachments .
                "</attachments>\n";
        $expout .= "    <graderinfo format=\"html\">\n";
        $expout .= $format->writetext($question->options->graderinfo, 3);
        $expout .= $this->writefiles($fs->get_area_files($contextid, 'qtype_filedepot',
                'graderinfo', $question->id));
        $expout .= "    </graderinfo>\n";

		$basefile = 'basefile';
		$files = $fs->get_area_files($contextid, 'qtype_filedepot', $basefile, $question->id);
        $expout .= "\t" . $this->writefiles($files) . "\n";
		
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
     * This method is copied from qformat_default as a quick fix, as the method there is
     * protected.
     * convert files into text output in the given format.
     * @param array
     * @param string encoding method
     * @return string $string
     */
    protected function writefiles($files, $encoding='base64') {
        if (empty($files)) {
            return '';
        }
        $string = '';
        foreach ($files as $file) {
            if ($file->is_directory()) {
                continue;
            }
            $string .= '<file name="' . $file->get_filename() . '" encoding="' . $encoding . '">';
            $string .= base64_encode($file->get_content());
            $string .= '</file>';
        }
        return $string;
    }

}
