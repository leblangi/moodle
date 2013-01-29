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
 * JavaScript required by the simulationanswer question type.
 *
 * @package    qtype
 * @subpackage simulationanswer
 * @copyright &copy; 2011 Université de Montréal
 * @author gilles-philippe.leblanc@umontreal.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


M.qtype_simulationanswer = M.qtype_simulationanswer || {};

M.qtype_simulationanswer.init = function(Y, questiondiv) {
	var questiondiv = Y.one(questiondiv);
	var swfcontainer = questiondiv.one(".swfcontainer");
	var embed = swfcontainer.one("embed");
	//answerbox = document.getElementById(answerbox);
	//answerbox = Y.one(answerbox);
	//Y.on('windowresize', this.handleResize, Y.one(window), swfcontainer,embed);
	//swfcontainer.setStyle("height",Math.round(swfcontainer.get('scrollWidth')*0.5625));
	//embed.setAttribute("height",Math.round(swfcontainer.get('scrollWidth')*0.5625));
	//embed.setAttribute("height",400);
	//questiondiv.on("resize",this.handleResize);
	
}

M.qtype_simulationanswer.insereReponse = function(inputid, value) {
    YUI().use("node", function(Y) {
        var input = document.getElementById(inputid);
        input.value = value;
    });
}

M.qtype_simulationanswer.handleResize = function(e,swfcontainer,embed) {
	//swfcontainer.setStyle("height",Math.round(swfcontainer.get('scrollWidth')*0.5625));
	//embed.setAttribute("height",Math.round(swfcontainer.get('scrollWidth')*0.5625));
}