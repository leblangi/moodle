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
 * Multiple entries 'question' renderer class.
 *
 * @package    qtype
 * @subpackage multientries
 * @copyright 2012 Cégep@distance
 * @author contact@gpleblanc.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Generates the output for multientries 'question's.
 *
 * @copyright 2012 Cégep@distance
 * @author contact@gpleblanc.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_multientries_renderer extends qtype_renderer {
    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {
        
        $result = html_writer::tag('div', $qa->get_question()->format_questiontext($qa), array('class' => 'qtext'));
                
        if (get_class($options) == 'question_preview_options') {
			$additionnalinfos = html_writer::tag('p', get_string('previewmode', 'qtype_multientries'));
		}else if (get_class($options) == 'mod_quiz_display_options') {
			
			$count = $this->get_shortanswer_count_in_page();

			if ($count > 1) {
                $table = new html_table();
                $table->attributes = array('class' => 'line-table');
                $lines = html_writer::start_tag('pre', array('class' => 'line_numbers'));
                for ($i=1;$i<=$count; $i++) {
                    $lines .= html_writer::tag('span', $i, array('id' => 'L' . $i, 'rel' => '#L' . $i));
                }
                $lines .= html_writer::end_tag('pre');
                $table->data[] = array($lines, html_writer::tag('textarea', "", array('class' => 'tokenbox', 'rows' => $count)));
                $textarea = html_writer::table($table);
                $wizardlink = html_writer::link('#', get_string('usewizard', 'qtype_multientries'), array('class' => 'showwizard', 'title' => get_string('popuptitle','qtype_multientries' )));
                $instructions = get_string('instructions', 'qtype_multientries', $wizardlink);
                $additionnalinfos = html_writer::tag('p', $instructions);
				$config = new stdClass();
                $config->title = get_string('popuptitle', 'qtype_multientries');
                $config->question = html_writer::tag('p', get_string('pastetokens', 'qtype_multientries'), array('class' => 'question')) . $textarea;
                $config->noLabel =  get_string('cancel');
                $config->yesLabel = get_string('confirmbutton', 'qtype_multientries');
                $config->errormsg = get_string('errormsg', 'qtype_multientries');
                $config->warningmsg = get_string('warningmsg', 'qtype_multientries');
                $config->successmsg = get_string('successmsg', 'qtype_multientries');
                $config->width = "552px";
                $config->linescount = $count;
                $this->page->requires->js_init_call('M.qtype_multientries.init',
                    array($config), false, array(
                        'name'     => 'qtype_multientries',
                        'fullpath' => '/question/type/multientries/module.js',
                        'requires' => array('base', 'node', 'event','node-event-simulate','overlay','moodle-enrol-notification', 'selector-css3'),
                    ));
			}else{
				$additionnalinfos = html_writer::tag('p', get_string('notenoughshortanwerdetected', 'qtype_multientries'));
			}
			
		}
        
        $result .= html_writer::tag('div', $additionnalinfos , array('class' => 'additionnalinfos'));
        
        return $result;
    }

    public function get_shortanswer_count_in_page() {
        $attemptid = required_param('attempt', PARAM_INT);
		$page = optional_param('page', 0, PARAM_INT);
		$attemptobj = quiz_attempt::create($attemptid);

        $count = 0;
		// Get the list of questions needed by this page.
		$slots = $attemptobj->get_slots($page);
        foreach ($slots as $slot) {
			$sqa = $attemptobj->get_question_attempt($slot);
			$subquestion = $sqa->get_question();
        
			if ($subquestion->get_type_name() == 'shortanswer') {
				$count++;
			}
		}
        return $count;
    }

    public function formulation_heading() {
        return get_string('informationtext', 'qtype_multientries');
    }
}
