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
 * manip question type upgrade code.
 *
 * @package    qtype
 * @subpackage manip
 * @copyright  2012 Université de Montréal
 * @author     gilles-philippe.leblanc@umontreal.ca
 * @author     mathieu.petit-clair@umontreal.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Upgrade code for the manip question type.
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_manip_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013011406) {

        $table = new xmldb_table('question_manip');
        
        // Define field minocc to be added to question_manip
        $field = new xmldb_field('minocc', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                XMLDB_NOTNULL, null, '1', 'regex');

        // Conditionally launch add field minocc
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field maxocc to be added to question_manip
        $field = new xmldb_field('maxocc', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
                null, null, null, 'minocc');

       // Conditionally launch add field maxocc
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_plugin_savepoint(true, 2013011406, 'qtype', 'manip');
        }
    return true;
}
