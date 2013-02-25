<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/question/type/manip/lib.php');
    
    $str = html_writer::tag('p', get_string('adminnoconfig', 'qtype_manip'));
    
    // TODO Enable the search pattern list modification for the site administrators
    
    // load patterns from DB
    
    // list them in a table
    /*$t = new html_table();
    $str .= html_writer::table($t);*/
    
    $settings->add(new admin_setting_heading('qtype_manip_settings_header', get_string('adminsearchpattern', 'qtype_manip'), $str));
}