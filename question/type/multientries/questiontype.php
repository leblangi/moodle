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
 * Question type class for the multientries 'question' type.
 *
 * @package    qtype
 * @subpackage multientries
 * @copyright 2012 Cégep@distance
 * @author contact@gpleblanc.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/description/questiontype.php');


/**
 * The multientries 'question' type.
 *
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_multientries extends qtype_description {
    public function requires_qtypes() {
        return array('description');
    }

    public function export_to_xml($question, qformat_xml $format, $extra=null) {
        return ' ';
    }

    public function import_from_xml($data, $question, qformat_xml $format, $extra=null) {
        $question_type = $data['@']['type'];
        if ($question_type != $this->name()) {
            return false;
        }
        $qo = $format->import_headers($data);
        $qo->qtype = $question_type;
        $qo->defaultmark = 0;
        $qo->length = 0;
        return $qo;
    }
}
