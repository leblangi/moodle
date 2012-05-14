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
 * Filedepot question renderer class.
 *
 * @package    qtype
 * @subpackage filedepot
 * @copyright &copy; 2012 Université de Montréal
 * @author gilles-philippe.leblanc@umontreal.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Generates the output for filedepot questions.
 *
 * @copyright &copy; 2012 Université de Montréal
 * @author gilles-philippe.leblanc@umontreal.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_filedepot_renderer extends qtype_renderer {
	protected $draftitemid = 0;
	
    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {

        $question = $qa->get_question();		
        $responseoutput = $question->get_format_renderer($this->page);
		
		// Answer field.
        $step = $qa->get_last_step_with_qt_var('answer');
        if (empty($options->readonly)) {
            $answer = $responseoutput->response_area_input('answer', $qa,
                    $step, $question->responsefieldlines, $options->context);

        } else {
            $answer = $responseoutput->response_area_read_only('answer', $qa,
                    $step, $question->responsefieldlines, $options->context);
        }

        $files = '';
        if ($question->attachments) {
            if (empty($options->readonly)) {
                $files = $this->files_input($qa, $question->attachments, $options);

            } else {
                $files = $this->files_read_only($qa, $options);
            }
        }
		
        $result = '';
        print_r($_POST);
		print_r($_GET);
		
		// Step 1
		$inputname = $qa->get_qt_field_name('answer');
		$basefile = self::get_url_for_file($qa, 'basefile');
		$basefile .= "?inputid=$inputname";
		echo $qa->get_usage_id();
		$result .= html_writer::tag('h4', get_string('steps', 'qtype_filedepot', 1));
		$result .= html_writer::tag('p', get_string('downloadbasefile', 'qtype_filedepot', $basefile));
		//$result .= html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'submit','value' => 'Telecharger'));
		
		// Step 2
		$result .= html_writer::tag('h4', get_string('steps', 'qtype_filedepot', 2));
		$result .= html_writer::tag('p', get_string('performfollowing', 'qtype_filedepot'));
		
		if (get_class($options) == 'question_preview_options') {
			$result .= html_writer::tag('p', get_string('previewsubquestion', 'qtype_filedepot'));
		}
		if (get_class($options) == 'mod_quiz_display_options') {
			
			$attemptid = required_param('attempt', PARAM_INT);
			$page = optional_param('page', 0, PARAM_INT);
			$attemptobj = quiz_attempt::create($attemptid);
			
			// Get the list of questions needed by this page.
			$slots = $attemptobj->get_slots($page);
			
			$subquestions = array();
			
			// Print all the questions
			foreach ($slots as $slot) {
				$sqa = $attemptobj->get_question_attempt($slot);
				$subquestion = $sqa->get_question();

				if ($subquestion->get_type_name() == 'fileanalysis') {
					$subquestions[] = 
						$this->mark_summary($sqa, $options) . 
						$this->status($sqa, $options) . 
						$this->edit_question_link($sqa, $options) . 
						html_writer::tag('p', $subquestion->questiontext);
				}
			}
			if (count($subquestions)>0) {
				$result .= html_writer::alist($subquestions, array('class'=>'list'), 'ol');
			}else{
				$result .= html_writer::tag('p', get_string('nosubquestionfound', 'qtype_filedepot'));
			}
			
		}
		
		// Step 3
		$result .= html_writer::tag('h4', get_string('steps', 'qtype_filedepot', 3));
		$result .= html_writer::tag('p', get_string('saveandupload', 'qtype_filedepot'));
        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
        $result .= html_writer::tag('div', $answer, array('class' => 'answer'));
        $result .= html_writer::tag('div', $files, array('class' => 'attachments'));
        $result .= html_writer::end_tag('div');

		$result .= html_writer::tag('button', 'Envoyer', array('name' => 'q548:1_-test', 'id' => 'q548:1_-test'));
		
		
		$this->page->requires->js_init_call('M.qtype_filedepot.addform',
                array('frm_test', 'q548:1_-test', $attemptid), false, array(
                    'name'     => 'qtype_filedepot',
                    'fullpath' => '/question/type/filedepot/module.js',
                    'requires' => array('base', 'node', 'event'),
                ));
				
				
        return $result;
    }

    /**
     * Displays any attached files when the question is in read-only mode.
     * @param question_attempt $qa the question attempt to display.
     * @param question_display_options $options controls what should and should
     *      not be displayed. Used to get the context.
     */
    public function files_read_only(question_attempt $qa, question_display_options $options) {
        $files = $qa->get_last_qt_files('attachments', $options->context->id);
        $output = array();

        foreach ($files as $file) {
            $mimetype = $file->get_mimetype();
            $output[] = html_writer::tag('p', html_writer::link($qa->get_response_file_url($file),
                    $this->output->pix_icon(file_mimetype_icon($mimetype), $mimetype,
                    'moodle', array('class' => 'icon')) . ' ' . s($file->get_filename())));
        }
        return implode($output);
    }

    /**
     * Displays the input control for when the student should upload a single file.
     * @param question_attempt $qa the question attempt to display.
     * @param int $numallowed the maximum number of attachments allowed. -1 = unlimited.
     * @param question_display_options $options controls what should and should
     *      not be displayed. Used to get the context.
     */
    public function files_input(question_attempt $qa, $numallowed,
            question_display_options $options) {
        global $CFG;
        require_once($CFG->dirroot . '/lib/form/filemanager.php');

        $pickeroptions = new stdClass();
        $pickeroptions->mainfile = null;
        $pickeroptions->maxfiles = $numallowed;
        $pickeroptions->context = $options->context;
		$this->draftitemid  = $qa->prepare_response_files_draft_itemid(
                'attachments', $options->context->id);
        $pickeroptions->itemid = $this->draftitemid;
        return form_filemanager_render($pickeroptions) . html_writer::empty_tag(
                'input', array('type' => 'hidden', 'name' => $qa->get_qt_field_name('attachments'),
                'value' => $pickeroptions->itemid));
    }

    public function manual_comment(question_attempt $qa, question_display_options $options) {
        if ($options->manualcomment != question_display_options::EDITABLE) {
            return '';
        }

        $question = $qa->get_question();
        return html_writer::nonempty_tag('div', $question->format_text(
                $question->graderinfo, $question->graderinfo, $qa, 'qtype_filedepot',
                'graderinfo', $question->id), array('class' => 'graderinfo'));
    }
	protected static function get_url_for_file(question_attempt $qa, $filearea, $itemid = 0) {
        $question = $qa->get_question();
        $qubaid = $qa->get_usage_id();
        $slot = $qa->get_slot();
        $fs = get_file_storage();
        if ($filearea == 'basefile') {
            $itemid = $question->id;
        }
        $draftfiles = $fs->get_area_files($question->contextid, 'qtype_filedepot',
                                                                    $filearea, $itemid, 'id');
        if ($draftfiles) {
            foreach ($draftfiles as $file) {
                if ($file->is_directory()) {
                    continue;
                }
                $url = moodle_url::make_pluginfile_url($question->contextid, 'qtype_filedepot',
                                            $filearea, "$qubaid/$slot/{$itemid}", '/',
                                            $file->get_filename());
                return $url->out();
            }
        }
        return null;
    }
	
	protected static function get_url_for_attachmentfile(question_attempt $qa, $draftitemid = 0) {
		global $USER;
		$contextid = get_context_instance(CONTEXT_USER, $USER->id)->id;
		
        $fs = get_file_storage();
        $draftfiles = $fs->get_area_files($contextid, 'user', 'draft', $draftitemid, 'id');
        if ($draftfiles) {
            foreach ($draftfiles as $file) {
                if ($file->is_directory()) {
                    continue;
                }
                
				$url = moodle_url::make_draftfile_url($draftitemid, '/', $file->get_filename());
                return $url->out();
            }
        }
        return null;
    }
	/**
     * Generate the display of the status line that gives the current state of
     * the question.
     * @param question_attempt $qa the question attempt to display.
     * @param qbehaviour_renderer $behaviouroutput the renderer to output the behaviour
     *      specific parts.
     * @param question_display_options $options controls what should and should not be displayed.
     * @return HTML fragment.
     */
    protected function status(question_attempt $qa, question_display_options $options) {
        return html_writer::tag('div', $qa->get_state_string($options->correctness),
                array('class' => 'state'));
    }

    /**
     * Generate the display of the marks for this question.
     * @param question_attempt $qa the question attempt to display.
     * @param question_display_options $options controls what should and should not be displayed.
     * @return HTML fragment.
     */
    protected function mark_summary(question_attempt $qa, question_display_options $options) {
        if (!$options->marks) {
            return '';
        }

        if ($qa->get_max_mark() == 0) {
            $summary = get_string('notgraded', 'question');

        } else if ($options->marks == question_display_options::MAX_ONLY ||
                is_null($qa->get_fraction())) {
            $summary = get_string('markedoutofmax', 'question',
                    $qa->format_max_mark($options->markdp));

        } else {
            $a = new stdClass();
            $a->mark = $qa->format_mark($options->markdp);
            $a->max = $qa->format_max_mark($options->markdp);
            $summary = get_string('markoutofmax', 'question', $a);
        }

        return html_writer::tag('div', $summary, array('class' => 'grade'));
    }

    /**
     * Render the question flag, assuming $flagsoption allows it.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param int $flagsoption the option that says whether flags should be displayed.
     */
    protected function question_flag(question_attempt $qa, $flagsoption) {
        global $CFG;
        switch ($flagsoption) {
            case question_display_options::VISIBLE:
                $flagcontent = $this->get_flag_html($qa->is_flagged());
                break;
            case question_display_options::EDITABLE:
                $id = $qa->get_flag_field_name();
                if ($qa->is_flagged()) {
                    $checked = 'checked="checked" ';
                } else {
                    $checked = '';
                }
                $postdata = question_flags::get_postdata($qa);
                // The checkbox id must be different from any element name, because
                // of a stupid IE bug:
                // http://www.456bereastreet.com/archive/200802/beware_of_id_and_name_attribute_mixups_when_using_getelementbyid_in_internet_explorer/
                $flagcontent = '<input type="hidden" name="' . $id . '" value="0" />' .
                        '<input type="checkbox" id="' . $id . 'checkbox" name="' . $id .
                                '" value="1" ' . $checked . ' />' .
                        '<input type="hidden" value="' . s($postdata) .
                                '" class="questionflagpostdata" />' .
                        '<label id="' . $id . 'label" for="' . $id . 'checkbox">' .
                                $this->get_flag_html($qa->is_flagged(), $id . 'img') .
                                '</label>' . "\n";
                break;
            default:
                $flagcontent = '';
        }
        if ($flagcontent) {
            return '<div class="questionflag">' . $flagcontent . "</div>\n";
        }
    }

    /**
     * Work out the actual img tag needed for the flag
     *
     * @param bool $flagged whether the question is currently flagged.
     * @param string $id an id to be added as an attribute to the img (optional).
     * @return string the img tag.
     */
    protected function get_flag_html($flagged, $id = '') {
        if ($id) {
            $id = 'id="' . $id . '" ';
        }
        if ($flagged) {
            $img = 'flagged';
        } else {
            $img = 'unflagged';
        }
        return '<img ' . $id . 'src="' . $this->pix_url('/i/' . $img) .
                '" alt="' . get_string('flagthisquestion', 'question') . '" />';
    }

    protected function edit_question_link(question_attempt $qa,
            question_display_options $options) {
        global $CFG;

        if (empty($options->editquestionparams)) {
            return '';
        }

        $params = $options->editquestionparams;
        if ($params['returnurl'] instanceof moodle_url) {
            $params['returnurl'] = str_replace($CFG->wwwroot, '',
                    $params['returnurl']->out(false));
        }
        $params['id'] = $qa->get_question()->id;
        $editurl = new moodle_url('/question/question.php', $params);

        return html_writer::tag('div', html_writer::link(
                $editurl, $this->pix_icon('i/edit', get_string('edit')) .
                get_string('editquestion', 'question')),
                array('class' => 'editquestion'));
    }
}


/**
 * A base class to abstract out the differences between different type of
 * response format.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class qtype_filedepot_format_renderer_base extends plugin_renderer_base {
    /**
     * Render the students respone when the question is in read-only mode.
     * @param string $name the variable name this input edits.
     * @param question_attempt $qa the question attempt being display.
     * @param question_attempt_step $step the current step.
     * @param int $lines approximate size of input box to display.
     * @param object $context the context teh output belongs to.
     * @return string html to display the response.
     */
    public abstract function response_area_read_only($name, question_attempt $qa,
            question_attempt_step $step, $lines, $context);

    /**
     * Render the students respone when the question is in read-only mode.
     * @param string $name the variable name this input edits.
     * @param question_attempt $qa the question attempt being display.
     * @param question_attempt_step $step the current step.
     * @param int $lines approximate size of input box to display.
     * @param object $context the context teh output belongs to.
     * @return string html to display the response for editing.
     */
    public abstract function response_area_input($name, question_attempt $qa,
            question_attempt_step $step, $lines, $context);

    /**
     * @return string specific class name to add to the input element.
     */
    protected abstract function class_name();
}


/**
 * An filedepot format renderer for filedepots where the student should use the HTML
 * editor without the file picker.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_filedepot_format_editor_renderer extends plugin_renderer_base {
    protected function class_name() {
        return 'qtype_filedepot_editor';
    }

    public function response_area_read_only($name, $qa, $step, $lines, $context) {
        return html_writer::tag('div', $this->prepare_response($name, $qa, $step, $context),
                array('class' => $this->class_name() . ' qtype_filedepot_response readonly'));
    }

    public function response_area_input($name, $qa, $step, $lines, $context) {
        global $CFG;
        require_once($CFG->dirroot . '/repository/lib.php');

        $inputname = $qa->get_qt_field_name($name);
        $responseformat = $step->get_qt_var($name . 'format');
        $id = $inputname . '_id';

        $editor = editors_get_preferred_editor($responseformat);
        $strformats = format_text_menu();
        $formats = $editor->get_supported_formats();
        foreach ($formats as $fid) {
            $formats[$fid] = $strformats[$fid];
        }

        list($draftitemid, $reponse) = $this->prepare_response_for_editing(
                $name, $step, $context);

        $editor->use_editor($id, $this->get_editor_options($context),
                $this->get_filepicker_options($context, $draftitemid));

        $output = '';
        $output .= html_writer::start_tag('div', array('class' =>
                $this->class_name() . ' qtype_filedepot_response'));

        $output .= html_writer::tag('div', html_writer::tag('textarea', s($reponse),
                array('id' => $id, 'name' => $inputname, 'rows' => $lines, 'cols' => 60)));

        $output .= html_writer::start_tag('div');
        if (count($formats == 1)) {
            reset($formats);
            $output .= html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => $inputname . 'format', 'value' => key($formats)));

        } else {
            $output .= html_writer::select($formats, $inputname . 'format', $responseformat, '');
        }
        $output .= html_writer::end_tag('div');

        $output .= $this->filepicker_html($inputname, $draftitemid);

        $output .= html_writer::end_tag('div');
        return $output;
    }

    /**
     * Prepare the response for read-only display.
     * @param string $name the variable name this input edits.
     * @param question_attempt $qa the question attempt being display.
     * @param question_attempt_step $step the current step.
     * @param object $context the context the attempt belongs to.
     * @return string the response prepared for display.
     */
    protected function prepare_response($name, question_attempt $qa,
            question_attempt_step $step, $context) {
        if (!$step->has_qt_var($name)) {
            return '';
        }

        $formatoptions = new stdClass();
        $formatoptions->para = false;
        return format_text($step->get_qt_var($name), $step->get_qt_var($name . 'format'),
                $formatoptions);
    }

    /**
     * Prepare the response for editing.
     * @param string $name the variable name this input edits.
     * @param question_attempt_step $step the current step.
     * @param object $context the context the attempt belongs to.
     * @return string the response prepared for display.
     */
    protected function prepare_response_for_editing($name,
            question_attempt_step $step, $context) {
        return array(0, $step->get_qt_var($name));
    }

    /**
     * @param object $context the context the attempt belongs to.
     * @return array options for the editor.
     */
    protected function get_editor_options($context) {
        return array('context' => $context);
    }

    /**
     * @param object $context the context the attempt belongs to.
     * @param int $draftitemid draft item id.
     * @return array filepicker options for the editor.
     */
    protected function get_filepicker_options($context, $draftitemid) {
        return array();
    }

    /**
     * @param string $inputname input field name.
     * @param int $draftitemid draft file area itemid.
     * @return string HTML for the filepicker, if used.
     */
    protected function filepicker_html($inputname, $draftitemid) {
        return '';
    }
}


/**
 * An filedepot format renderer for filedepots where the student should use the HTML
 * editor with the file picker.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_filedepot_format_editorfilepicker_renderer extends qtype_filedepot_format_editor_renderer {
    protected function class_name() {
        return 'qtype_filedepot_editorfilepicker';
    }

    protected function prepare_response($name, question_attempt $qa,
            question_attempt_step $step, $context) {
        if (!$step->has_qt_var($name)) {
            return '';
        }

        $formatoptions = new stdClass();
        $formatoptions->para = false;
        $text = $qa->rewrite_response_pluginfile_urls($step->get_qt_var($name),
                $context->id, 'answer', $step);
        return format_text($text, $step->get_qt_var($name . 'format'), $formatoptions);
    }

    protected function prepare_response_for_editing($name,
            question_attempt_step $step, $context) {
        return $step->prepare_response_files_draft_itemid_with_text(
                $name, $context->id, $step->get_qt_var($name));
    }

    protected function get_editor_options($context) {
        return array(
            'subdirs' => 0,
            'maxbytes' => 0,
            'maxfiles' => -1,
            'context' => $context,
            'noclean' => 0,
            'trusttext'=>0
        );
    }

    /**
     * Get the options required to configure the filepicker for one of the editor
     * toolbar buttons.
     * @param mixed $acceptedtypes array of types of '*'.
     * @param int $draftitemid the draft area item id.
     * @param object $context the context.
     * @return object the required options.
     */
    protected function specific_filepicker_options($acceptedtypes, $draftitemid, $context) {
        $filepickeroptions = new stdClass();
        $filepickeroptions->accepted_types = $acceptedtypes;
        $filepickeroptions->return_types = FILE_INTERNAL | FILE_EXTERNAL;
        $filepickeroptions->context = $context;
        $filepickeroptions->env = 'filepicker';

        $options = initialise_filepicker($filepickeroptions);
        $options->context = $context;
        $options->client_id = uniqid();
        $options->env = 'editor';
        $options->itemid = $draftitemid;

        return $options;
    }

    protected function get_filepicker_options($context, $draftitemid) {
        global $CFG;

        return array(
            'image' => $this->specific_filepicker_options(array('image'),
                            $draftitemid, $context),
            'media' => $this->specific_filepicker_options(array('video', 'audio'),
                            $draftitemid, $context),
            'link'  => $this->specific_filepicker_options('*',
                            $draftitemid, $context),
        );
    }

    protected function filepicker_html($inputname, $draftitemid) {
        $nonjspickerurl = new moodle_url('/repository/draftfiles_manager.php', array(
            'action' => 'browse',
            'env' => 'editor',
            'itemid' => $draftitemid,
            'subdirs' => false,
            'maxfiles' => -1,
            'sesskey' => sesskey(),
        ));

        return html_writer::empty_tag('input', array('type' => 'hidden',
                'name' => $inputname . ':itemid', 'value' => $draftitemid)) .
                html_writer::tag('noscript', html_writer::tag('div',
                    html_writer::tag('object', '', array('type' => 'text/html',
                        'data' => $nonjspickerurl, 'height' => 160, 'width' => 600,
                        'style' => 'border: 1px solid #000;'))));
    }
}


/**
 * An filedepot format renderer for filedepots where the student should use a plain
 * input box, but with a normal, proportional font.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_filedepot_format_plain_renderer extends plugin_renderer_base {
    /**
     * @return string the HTML for the textarea.
     */
    protected function textarea($response, $lines, $attributes) {
        $attributes['class'] = $this->class_name() . ' qtype_filedepot_response';
        $attributes['rows'] = $lines;
        $attributes['cols'] = 60;
        return html_writer::tag('textarea', s($response), $attributes);
    }

    protected function class_name() {
        return 'qtype_filedepot_plain';
    }

    public function response_area_read_only($name, $qa, $step, $lines, $context) {
        return $this->textarea($step->get_qt_var($name), $lines, array('readonly' => 'readonly'));
    }

    public function response_area_input($name, $qa, $step, $lines, $context) {
        $inputname = $qa->get_qt_field_name($name);
        return $this->textarea($step->get_qt_var($name), $lines, array('name' => $inputname)) .
                html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => $inputname . 'format', 'value' => FORMAT_PLAIN));
    }
}


/**
 * An filedepot format renderer for filedepots where the student should use a plain
 * input box with a monospaced font. You might use this, for example, for a
 * question where the students should type computer code.
 *
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_filedepot_format_monospaced_renderer extends qtype_filedepot_format_plain_renderer {
    protected function class_name() {
        return 'qtype_filedepot_monospaced';
    }
}
