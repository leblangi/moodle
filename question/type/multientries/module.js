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
 * JavaScript required by the multientries question type.
 *
 * @package    qtype
 * @subpackage multientries
 * @copyright 2012 Cégep@distance
 * @author contact@gpleblanc.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.qtype_multientries = M.qtype_multientries || {};
M.qtype_multientries.questionlist = new Array();


/**
 * Initialise the paste events and the wizard popup
 * 
 * @param {object} Y the global YUI object
 * @param {object} config the configurations parameters for the wizard popup
 */
M.qtype_multientries.init = function (Y, config) {
	// Paste Events
    Y.all("div.que.shortanswer .answer > input").each(function(answerfield) {
        answerfield = answerfield.get("id");
        M.qtype_multientries.questionlist.push(answerfield);
        answerfield = document.getElementById(answerfield);
        
        // Normal handler for the paste Event
        var handlePaste = function(e) {
            var target = (typeof e.target != 'undefined' ? e.target : e.srcElement);
            target.value = "";
            setTimeout(function() {M.qtype_multientries.handleLatePaste(target.value)}, 1);
        }
        // Add the Event the old way because the paste Event do not work with YUI...
        if (answerfield.addEventListener) {
            answerfield.addEventListener("paste",handlePaste,false);
        }else if (answerfield.attachEvent) { // IE events
            answerfield.attachEvent("onpaste",handlePaste);
        }
    });
	// Wizard popup
    Y.all('.que.multientries .showwizard').each(function(node){
        // Prevent the any previously added event (submit) from firing.
        if (this._confirmationListener) {
             this._confirmationListener.detach();
        }
        this._confirmationListener = this.on('click', function(e){
            // Prevent the default event (submit) from firing
            e.preventDefault();
            if (this._closeLinkListener) {
                Y.one(".que.multientries .msg.success").remove();
                this._closeLinkListener.detach();
            }
            M.qtype_multientries.createDialogBox(Y, config);
        }, this);
    });
}


/**
 * Handle the analysis of the answer and format it the correct way
 * 
 * @param {string} value the global YUI object
 */
M.qtype_multientries.handleLatePaste = function(value) {
    var answers = value;
    answers = answers.split(" ");
    if (answers.length == 1) {
        answers = answers[0].split("\t");
    }
    
    if (answers.length == 1) {
        answers = window.clipboardData.getData("text");
        answers = answers.split("\n");
    }
    M.qtype_multientries.insertAnswers(answers);
}

/**
 * Insert the answers to the shortanswers input fields
 * 
 * @param {array} answers all the answers to insert in shortanswers inputs fields
 */
M.qtype_multientries.insertAnswers = function(answers) {
    for (var i=0;i<M.qtype_multientries.questionlist.length;i++) {
        document.getElementById(M.qtype_multientries.questionlist[i]).value = (answers[i] != undefined? answers[i] : "");
    }
}

/**
 * Handle the analyse of the answer and format it the correct way
 * 
 * @param {object} target the textera who receive the pasted content in DOM element format
 * @param {object} confirm the wizard confirm popup
 * @param {string} msg the warning message to show if the number of answers exceed the number of shortanswer questions
 * @param {boolean} isPasted if the event occurs during a real paste event
 */
M.qtype_multientries.handleLatePasteWizard = function(target, confirm, msg, isPasted) {
    var container = confirm.bodyNode.one('.line-table');
    var answers = target.value;

    answers = answers.split("\n");
    
    
    // Trim the last element if its a linebreak, especially when copied/pasted from Excel
    if (answers[answers.length -1].length == 0 && isPasted) {
        answers.pop();
        target.value = answers.join("\n");

        // If here, there's still a line break at the end, we are on IE8 so, we remove it.
        if (target.value.slice(-1) == "\n" ) {
            target.value = target.value.slice(0, -2);
        }
    }
    
    // Clear the status
    confirm.bodyNode.all('.line_numbers span').each(function(node) {
        node.removeClass("correct");
        node.removeClass("incorrect");
    });
    container.ancestor().all('.msg').remove();
    
    // If there is too many answers, remove the subsequents ones and show a warning about this
    if (answers.length > M.qtype_multientries.questionlist.length) {
        answers = answers.slice(0, M.qtype_multientries.questionlist.length);
        target.value = answers.join("\n");
        M.qtype_multientries.displayMessage(msg, "warning", container);
    }
}

/**
 * Add a message to display
 * 
 * @param {string} msg the message to display
 * @param {string} type the type of message (warning, error or success)
 * @param {object} refNode the node used as reference to add the message just before it
 * @param {boolean} autoRemove if the message is automatically removed
 */
M.qtype_multientries.displayMessage = function(msg, type, refNode, autoRemove) {
    var parent = refNode.ancestor();
    parent.all('.msg').remove(true);
    
    // Display the message
    var msg = parent.insertBefore('<div class="msg '+type+'" ><p>' + msg + '</p></div>', refNode);
    
    // remove automatically the display message
    if (autoRemove){
        Y.later(3000, this, function(){msg.remove(true);});
    }
}

/**
 * Add a dialog box to show the paste wizard
 * 
 * @param {object} Y the global YUI object
 * @param {object} config the configurations parameters for the wizard popup
 */
M.qtype_multientries.createDialogBox = function(Y, config) {
    var confirm = new M.core.confirm(config);
    confirm.centerDialogue();
    confirm._enterKeypress.detach();
    
    // Add specific css class to the confirm box
    confirm.bodyNode.ancestor().addClass('moodle-dialogue-multientries');
    
    var textarea = confirm.bodyNode.one('.tokenbox');
    textarea.generateID();
    var lineHeight = parseFloat(textarea.getStyle("lineHeight"));
    textarea.setStyle("height", config.linescount*lineHeight);
    
    // Destroy the confirm dialog on close to avoid multi instance of it in background
    if (this._closeBtnListener) {
         this._closeBtnListener.detach();
    }
    this._closeBtnListener = confirm.headerNode.one(".closebutton").on("click",function(e, confirm){confirm.destroy();},this, confirm);
    var yes = confirm.bodyNode.one(".confirmation-buttons input:first-child");
    
    // Remove the standard click event to avoid the normal behavior who close the dialog without our validations
    yes.detach("click");
    
    // Before pasting the answers, we check if the number of answer correspond to the number of questions
    yes.on('click', function(e, config) {
        var textarea = confirm.bodyNode.one('.tokenbox');
        var answers = textarea.get("value").split('\n');
        var nbPastedAnswer = answers.length;
        
        // If each answers contains something and if the number of answers correspond to the number of questions
		var isCorrectAnswer = function(answers) {
			for (var i in answers) {
				if (answers[i] == '') {
					return false;
				}
			}
			return answers.length == M.qtype_multientries.questionlist.length;
		}
        
        // If the answers are valid
        if (isCorrectAnswer(answers)) {
            M.qtype_multientries.insertAnswers(answers);
			M.qtype_multientries.displayMessage(config.successmsg, "success", Y.one(".additionnalinfos"), true);
            confirm.submit(e,true);
        }else{
            var container = confirm.bodyNode.one('.line-table');
            var numbers = confirm.bodyNode.all('.line_numbers span');
            numbers.each(function(node, index) {
                // Clear the status
                node.removeClass("correct");
                node.removeClass("incorrect");
                // Show the line number as correct or not
                if (answers[index] != '' && index < answers.length) {
                    node.addClass("correct");
                }else{
                    node.addClass("incorrect");
                }
            });
            // Display a error message
            M.qtype_multientries.displayMessage(config.errormsg, "error", container);
        }
    }, this, config);
    
    // Normal handler for the paste Event
    var handlePaste = function(e) {
        var target = (typeof e.target != 'undefined' ? e.target : e.srcElement);
        setTimeout(function() {M.qtype_multientries.handleLatePasteWizard(target, confirm, config.warningmsg, true)}, 1);
    }
    
    var field = document.getElementById(textarea.get("id"));
    textarea.on("keyup", function(e, target){M.qtype_multientries.handleLatePasteWizard(target, confirm, config.warningmsg);}, this, field);
    
    // Add the Event the old way because the paste Event do not work with YUI...
    if (field.addEventListener) {
        field.addEventListener("paste", handlePaste, false);
    }else if (field.attachEvent) { // IE events
        field.attachEvent("onpaste",handlePaste);
    }
    // Show the confirm box
    confirm.show();
}