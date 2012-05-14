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
 * JavaScript required by the filedepot question type.
 *
 * @package    qtype
 * @subpackage filedepot
 * @copyright &copy; 2012 Université de Montréal
 * @author gilles-philippe.leblanc@umontreal.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.qtype_filedepot = M.qtype_filedepot || {};
M.qtype_filedepot.questionlist = new Array();

M.qtype_filedepot.init = function (Y, answerfield) {
	
}

M.qtype_filedepot.addform = function (Y, formid, submitbtnid, attemptid) {
	var form = Y.Node.create('<form id="'+formid+ '" name="'+formid+ '" accept-charset="utf-8" method="GET"></form>');
	var attempt = Y.Node.create('<input type="hidden" value="' + attemptid + '" name="attempt">');
	var hidden = Y.Node.create('<input type="hidden" value="2222" name="tst_test">');
	
	form.append(attempt);
	form.append(hidden);
	var btn = Y.one(document.getElementById(submitbtnid));
	btn.on("click", M.qtype_filedepot.submitform, M.qtype_filedepot, form);
	Y.one("body").append(form);
	
}
M.qtype_filedepot.submitform = function (e, form) {
	e.preventDefault();
	form.submit();
}