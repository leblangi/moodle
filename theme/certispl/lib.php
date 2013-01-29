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
 * Library functions for the Certitude Splash theme
 *
 * @package   theme_certispl
 * @copyright 2010 Caroline Kennedy of Synergy Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Certitude Splash theme post process function for CSS
 * @param string $css Incoming CSS to process
 * @param stdClass $theme The theme object
 * @return string The processed CSS
 */
function certispl_process_css($css, $theme) {
 
    if (!empty($theme->settings->regionwidth)) {
        $regionwidth = $theme->settings->regionwidth;
    } else {
        $regionwidth = null;
    }
    $css = certispl_set_regionwidth($css, $regionwidth);
 
    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    $css = certispl_set_customcss($css, $customcss);
 
    return $css;
}

/**
 * Sets the region width variable in CSS
 *
 * @param string $css
 * @param mixed $regionwidth
 * @return string
 */
function certispl_set_regionwidth($css, $regionwidth) {
    $tag = '[[setting:regionwidth]]';
    $doubletag = '[[setting:regionwidthdouble]]';
    $leftmargintag = '[[setting:leftregionwidthmargin]]';
    $rightmargintag = '[[setting:rightregionwidthmargin]]';
    $replacement = $regionwidth;
    if (is_null($replacement)) {
        $replacement = 240;
    }
    $css = str_replace($tag, $replacement.'px', $css);
    $css = str_replace($doubletag, ($replacement*2).'px', $css);
    $css = str_replace($rightmargintag, ($replacement*3-5).'px', $css);
    $css = str_replace($leftmargintag, ($replacement+5).'px', $css);
    return $css;
}

/**
 * Sets the custom css variable in CSS
 *
 * @param string $css
 * @param mixed $customcss
 * @return string
 */
function certispl_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

/**
 * Adds the JavaScript for the colour switcher to the page.
 *
 * The colour switcher is a YUI moodle module that is located in
 *     theme/certispl/yui/certispl/certispl.js
 *
 * @param moodle_page $page 
 */
function certispl_initialise_colourswitcher(moodle_page $page) {
    user_preference_allow_ajax_update('theme_certispl_chosen_colour', PARAM_ALPHA);
    $page->requires->yui_module('moodle-theme_certispl-colourswitcher', 'M.theme_certispl.initColourSwitcher', array(array('div'=>'#colourswitcher')));
}

/**
 * Gets the colour the user has selected, or the default if they have never changed
 *
 * @param string $default The default colour to use, normally red
 * @return string The colour the user has selected
 */
function certispl_get_colour($default='blue') {
    return get_user_preferences('theme_certispl_chosen_colour', $default);
}

/**
 * Checks if the user is switching colours with a refresh (JS disabled)
 *
 * If they are this updates the users preference in the database
 *
 * @return bool
 */
function certispl_check_colourswitch() {
    $changecolour = optional_param('certisplcolour', null, PARAM_ALPHA);
    if (in_array($changecolour, array('blue','green','red','orange'))) {
        return set_user_preference('theme_certispl_chosen_colour', $changecolour);
    }
    return false;
}

/**
 * Adds the JavaScript for the effects of the main meu.
 *
 *
 * @param moodle_page $page 
 */
function certispl_initialise_menucontrols(moodle_page $page) {
    $page->requires->yui_module('moodle-theme_certispl-menucontrols', 'M.theme_certispl.initMenuControls', array(array('div'=>'#custom_menu_1')));
}

/**
 * Adds the JavaScript for the fullscreen mode to the page.
 *
 * The fullscreen mode is a YUI moodle module that is located in
 *     theme/certispl/yui/certispl/certispl.js
 *
 * @param moodle_page $page 
 */
function certispl_initialise_fullscreenmode(moodle_page $page) {
    user_preference_allow_ajax_update('theme_certispl_fullscreenmode_state', PARAM_ALPHA);
    $page->requires->yui_module('moodle-theme_certispl-fullscreenmode', 'M.theme_certispl.initFullscreenMode', array(array('toggleBtn'=>'#fullscreenmode a')));
	$strings = Array('enablefullscreenmode','disablefullscreenmode');
	$page->requires->strings_for_js($strings, 'theme_certispl');
}

/**
 * Gets fullscreen mode state the user has selected, or the default if they have never changed
 *
 * @param string $default The default colour to use, normally red
 * @return string The fullscreen mode state the user has selected
 */
function certispl_get_fullscreenmode_state($default='false') {
	return toStrictBoolean(get_user_preferences('theme_certispl_fullscreenmode_state', $default));
}

/**
 * Checks if the user is switching colours with a refresh (JS disabled)
 *
 * If they are this updates the users preference in the database
 *
 * @return bool if the optionnal param is setted
 */
function certispl_check_fullscreenmode() {
    $fullscreenmodestate = optional_param('fullscreenmodestate', null, PARAM_ALPHA);
     if (in_array($fullscreenmodestate, array('true','false'))) {
        return set_user_preference('theme_certispl_fullscreenmode_state', $fullscreenmodestate);
    }
    return false;
}

/**
 * Return the title of the current site when not in production
 *
 * @return string $fullname the fullname of the site
 */
function certispl_show_sitefullname() {
	global $CFG, $SITE;
	$default = 'studium';
	$fullname = '';
	if (!empty($CFG->udemlevel) && $CFG->udemlevel == UdeMLevel::Prod) {
		return $fullname;
	}
	$separator =  ' ';
	$fullnameparts = explode($separator, $SITE->fullname);
	if (count($fullnameparts)>1 && strtolower($fullnameparts[0]) == $default) {
		$fullnameparts[0] = html_writer::tag('span',$fullnameparts[0], array('class'=>'prefixe'));
		$fullname = implode($separator, $fullnameparts);
	}else{
		$fullname = $SITE->fullname;
	}
	return html_writer::tag('span', $fullname, array('id'=>'site_fullname'));
}

/**
 * Check if the value entered is onsidered to be a true value
 *
 * @return boolean If the value is true
 */
function toStrictBoolean ($val, $trueValues = array('true'), $forceLowercase = true) {
    if (is_string($val)) {
        return (in_array(($forceLowercase ? strtolower($val):$val), $trueValues));
    } else {
        return (boolean) $val;
    }
}