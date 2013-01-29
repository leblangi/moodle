/*
 * This file is part of Moodle - http://moodle.org/
 *
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * JavaScript required by the manip question type.
 *
 * @package    qtype
 * @subpackage manip
 * @copyright 2012 Cégep@distance
 * @author mathieu.petitclair@gmail.com
 * @author contact@gpleblanc.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.qtype_manip = M.qtype_manip || {};

/**
 * Initialise the upload button
 * 
 * @param {object} Y the global YUI object
 */
M.qtype_manip.initUpload = function(Y) {

    var questionNodes = Y.all('div.manip');
    var oldQuestionAnswers = {}
    
    // Remove the first question from the list
    var firstQuestionNode = questionNodes.shift();
    // If there is only one question of this type in the page, it is unnecessary to display the button.
    if (questionNodes.size() == 0) {
        return;
    }
    
    /* Show the section containing the button only when the script is loaded.
     Also avoid to show the button if javascript is disabled */
    var copytoallsection = Y.one(".copytoallsection");
    copytoallsection.addClass("enabled");

    var button = copytoallsection.one('#manip-button');
    var buttonOnClick = function(e) {
        e.preventDefault();
        //If a file has already been submitted in the first field
        if (!firstQuestionNode.one('.attachments .filemanager').hasClass('fm-noitems')) {
            // For each question, we update the value of the answer by using the one of the first question
            var allAvailable = true;
            questionNodes.each(function(node, i) {
                var attachments = node.one('.attachments');
                var fm = attachments.one('.filemanager');
                if (fm.hasClass('fm-noitems')) {
                    // Only reset the state if its not already the case
                    if (!fm.hasClass("fm-manip-itemcopied")) {
                        // Hide the fliepicker
                        fm.addClass("fm-manip-itemcopied");
                        // Add and show the file copied message
                        var newMessage = M.str.qtype_manip.filecopiedfromquestion + firstQuestionNode.one(".info .no .qno").get("text");
                        M.qtype_manip.displayMessage(Y, newMessage, 'success', fm);
                        // Put the new information in the filepicker
                        var inputNode = attachments.one("input[type=hidden]");
                        var nodeName = inputNode.get("name");
                        oldQuestionAnswers[nodeName] = inputNode.get("value");
                        inputNode.set('value', firstQuestionNode.one(".attachments input[type=hidden]").get('value'));
                    }
                }else{
                    allAvailable = false;
                }
            });
            if (allAvailable) {
                M.qtype_manip.displayMessage(Y, M.str.qtype_manip.copyfilesuccessmsg, 'success', copytoallsection, true);
            }else{
                M.qtype_manip.displayMessage(Y, M.str.qtype_manip.copyfilewarningmsg, 'warning', copytoallsection, true);
            }
        }else{
            // For each question. if necessary, we reset the message if the question has not been answered manually
            var allOriginal = true;
            questionNodes.each(function(node, i) {
                var attachments = node.one('.attachments');
                var fm = attachments.one('.filemanager');
                if (fm.hasClass('fm-manip-itemcopied')) {
                    // Remove the manip itemcopied class
                    fm.removeClass("fm-manip-itemcopied");
                    // Restore the old state
                     M.qtype_manip.displayMessage(Y, M.str.qtype_manip.emptyfilewarningmsg, 'warning', fm, true);
                    var inputNode = attachments.one("input[type=hidden]");
                    var nodeName = inputNode.get("name");
                    inputNode.set('value', oldQuestionAnswers[nodeName]);
                    allOriginal = false;
                }
            });
            // Only display error if no other questions is restored to their initial state
            if (allOriginal) {
                M.qtype_manip.displayMessage(Y, M.str.qtype_manip.copyfileerrormsg, 'error', copytoallsection);
            }
            
        }
    }
    button.on('click', buttonOnClick);
}

/**
 * Initialise the question form
 * 
 * @param {object} Y the global YUI object
 */
M.qtype_manip.initQuestionForm = function(Y) {
    var select = Y.one('#id_regexselector');
    var regex = Y.one('#id_regex');
    // Set the regular expression based on the one choosen on the select list
    var setRegex = function() {
        var option = select.get('value');
        if (option != 'custom') {
            regex.set('value', option);
        }
    }
    // Update the selection in the select list based on the value in the regex input field
    var setRegexSelector = function() {
        var iscustom = true;
        select.get("options").each( function() {
            var selected = this.get('selected');
            var value  = this.get('value');
             if (selected) {
                this.set('selected', null);
            }
            if (value == regex.get('value')) {
                this.set('selected', 'selected');
                iscustom = false;
            }
        });
        if (iscustom) {
            select.one('option[value=custom]').set('selected', 'selected');
        }
    }
    
    // Do it on change or on keyup
    select.on('change', setRegex);
    select.on('keyup', setRegex);
    regex.on('keyup', setRegexSelector);
};

/**
 * Add a message to display
 * 
 * @param {string} msg the message to display
 * @param {string} type the type of message (warning, error or success)
 * @param {object} refNode the node used as reference to add the message just before it
 * @param {boolean} autoRemove if the message is automatically removed
 */
M.qtype_manip.displayMessage = function(Y, msg, type, refNode, autoRemove) {
    var parent = refNode.ancestor();
    parent.all('.msg').remove(true);
    
    // Display the message
    var msg = parent.insertBefore('<div class="msg '+type+'" ><p>' + msg + '</p></div>', refNode);
    
    // remove automatically the display message
    if (autoRemove){
        Y.later(3000, this, function(){msg.remove(true);});
    }
}
