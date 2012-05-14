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
 * Defines the editing form for the filedepot question type.
 *
 * @package    qtype
 * @subpackage filedepot
 * @copyright &copy; 2012 Université de Montréal
 * @author gilles-philippe.leblanc@umontreal.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Filedepot question type editing form.
 *
 * @copyright &copy; 2012 Université de Montréal
 * @author gilles-philippe.leblanc@umontreal.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_filedepot_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        $qtype = question_bank::get_qtype('filedepot');

		$mform->addElement('filepicker', 'basefile',
				get_string('basefile', 'qtype_filedepot'), null, array('subdirs' => 0, 'maxfiles' => 1, 'maxbytes' => 0, 'accepted_types' => array('*.docx','*.xlsx','*.pptx', '*.psd')));
		$mform->addRule('basefile', get_string('required'), 'required', null, 'client');

        $mform->addElement('select', 'responseformat',
                get_string('responseformat', 'qtype_filedepot'), $qtype->response_formats());
        $mform->setDefault('responseformat', 'editor');

        $mform->addElement('select', 'responsefieldlines',
                get_string('responsefieldlines', 'qtype_filedepot'), $qtype->response_sizes());
        $mform->setDefault('responsefieldlines', 15);

        $mform->addElement('select', 'attachments',
                get_string('allowattachments', 'qtype_filedepot'), $qtype->attachment_options());
        $mform->setDefault('attachments', 0);

        $mform->addElement('editor', 'graderinfo', get_string('graderinfo', 'qtype_filedepot'),
                array('rows' => 10), $this->editoroptions);
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);

        if (empty($question->options)) {
            return $question;
        }
		
		//initialise file picker for the basefile
        $basefile = file_get_submitted_draft_itemid('basefile');
		
		file_prepare_draft_area($basefile, $this->context->id, 'qtype_filedepot',
                                'basefile', !empty($question->id) ? (int) $question->id : null,
                                array('subdirs' => 0, 'maxfiles' => 1, 'maxbytes' => 0, 'accepted_types' => array('*.docx')));
        $question->basefile = $basefile;
        $question->responseformat = $question->options->responseformat;
        $question->responsefieldlines = $question->options->responsefieldlines;
        $question->attachments = $question->options->attachments;

        $draftid = file_get_submitted_draft_itemid('graderinfo');
        $question->graderinfo = array();
        $question->graderinfo['text'] = file_prepare_draft_area(
            $draftid,           // draftid
            $this->context->id, // context
            'qtype_filedepot',      // component
            'graderinfo',       // filarea
            !empty($question->id) ? (int) $question->id : null, // itemid
            $this->fileoptions, // options
            $question->options->graderinfo // text
        );
        $question->graderinfo['format'] = $question->options->graderinfoformat;
        $question->graderinfo['itemid'] = $draftid;

        return $question;
    }

	public static function file_uploaded($draftitemid) {
        $draftareafiles = file_get_drafarea_files($draftitemid);
        do {
            $draftareafile = array_shift($draftareafiles->list);
        } while ($draftareafile !== null && $draftareafile->filename == '.');
        if ($draftareafile === null) {
            return false;
        }
        return true;
    }
	
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
		if (!self::file_uploaded($data['basefile'])) {
            $errors["basefile"] = get_string('incorrectfiletype', 'qtype_filedepot');
        }
        return $errors;
    }

    public function qtype() {
        return 'filedepot';
    }
}
