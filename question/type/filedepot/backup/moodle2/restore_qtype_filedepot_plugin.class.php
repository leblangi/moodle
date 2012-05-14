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
 * @package    moodlecore
 * @subpackage backup-moodle2
 * @copyright &copy; 2012 Université de Montréal
 * @author gilles-philippe.leblanc@umontreal.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * restore plugin class that provides the necessary information
 * needed to restore one filedepot qtype plugin
 *
 * @copyright &copy; 2012 Université de Montréal
 * @author gilles-philippe.leblanc@umontreal.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_qtype_filedepot_plugin extends restore_qtype_plugin {

    /**
     * Returns the paths to be handled by the plugin at question level
     */
    protected function define_question_plugin_structure() {
        return array(
            new restore_path_element('filedepot', $this->get_pathfor('/filedepot'))
        );
    }

    /**
     * Process the qtype/filedepot element
     */
    public function process_filedepot($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // Detect if the question is created or mapped
        $questioncreated = $this->get_mappingid('question_created',
                $this->get_old_parentid('question')) ? true : false;

        // If the question has been created by restore, we need to create its
        // qtype_filedepot too
        if ($questioncreated) {
            $data->questionid = $this->get_new_parentid('question');
            $newitemid = $DB->insert_record('qtype_filedepot_options', $data);
            $this->set_mapping('qtype_filedepot', $oldid, $newitemid);
        }
    }

    /**
     * Return the contents of this qtype to be processed by the links decoder
     */
    public static function define_decode_contents() {
        return array(
            new restore_decode_content('qtype_filedepot_options', 'graderinfo', 'qtype_filedepot'),
        );
    }

    /**
     * When restoring old data, that does not have the filedepot options information
     * in the XML, supply defaults.
     */
    protected function after_execute_question() {
        global $DB;

        $filedepotswithoutoptions = $DB->get_records_sql("
                    SELECT *
                      FROM {question} q
                     WHERE q.qtype = ?
                       AND NOT EXISTS (
                        SELECT 1
                          FROM {qtype_filedepot_options}
                         WHERE questionid = q.id
                     )
                ", array('filedepot'));

        foreach ($filedepotswithoutoptions as $q) {
            $defaultoptions = new stdClass();
            $defaultoptions->questionid = $q->id;
            $defaultoptions->responseformat = 'editor';
            $defaultoptions->responsefieldlines = 15;
            $defaultoptions->attachments = 0;
            $defaultoptions->graderinfo = '';
            $defaultoptions->graderinfoformat = FORMAT_HTML;
            $DB->insert_record('qtype_filedepot_options', $defaultoptions);
        }
    }
}
