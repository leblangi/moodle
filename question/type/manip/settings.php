<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/question/type/manip/lib.php');
    
    $str = 'Aucun élément de configuration n\'est disponible pour le moment.';

    // load patterns from DB
    
    // list them in a table
    
    $str .= '<table>';
    // foreach ... {
        $str .= '<tr><td></td><td></td></tr>';
    // }
    $str .= '</table>';
    
    $settings->add(new admin_setting_heading('qtype_manip_settings_header', get_string('admindocxsnippets', 'qtype_manip'), $str));
}