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
 * Defines the editing form for the simulationanswer question type.
 *
 * @package    qtype
 * @subpackage simulationanswer
 * @copyright  2007 Jamie Pratt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/shortanswer/edit_shortanswer_form.php');

/**
 * Short answer question editing form definition.
 *
 * @copyright  2007 Jamie Pratt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_simulationanswer_edit_form extends qtype_shortanswer_edit_form {

    protected function definition_inner($mform) {
		$mform->addElement('filepicker', 'simulationfile',
				get_string('simulationswffile', 'qtype_simulationanswer'), null, array('subdirs' => 0, 'maxfiles' => 1, 'maxbytes' => 0, 'accepted_types' => array('*.swf')));
		$mform->addRule('simulationfile', get_string('required'), 'required', null, 'client');
		parent::definition_inner($mform);
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
		
		//initialise file picker for simulationfile
        $simulationfile = file_get_submitted_draft_itemid('simulationfile');

        file_prepare_draft_area($simulationfile, $this->context->id, 'qtype_simulationanswer',
                                'simulationfile', !empty($question->id) ? (int) $question->id : null,
                                array('subdirs' => 0, 'maxfiles' => 1, 'maxbytes' => 0, 'accepted_types' => array('*.swf')));
        $question->simulationfile = $simulationfile;
		
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
		if (!self::file_uploaded($data['simulationfile'])) {
            $errors["simulationfile"] = get_string('swfmustbeuploaded', 'qtype_simulationanswer');
        }
        return $errors;
    }

    public function qtype() {
        return 'simulationanswer';
    }
}
