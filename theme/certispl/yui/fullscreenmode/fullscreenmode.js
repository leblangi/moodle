YUI.add('moodle-theme_certispl-fullscreenmode', function(Y) {

/**
 * Splash UdeM fullscreen mode class.
 * Initialise this class by calling M.theme_certispl.init
 */
var FullscreenMode = function() {
    FullscreenMode.superclass.constructor.apply(this, arguments);
};
FullscreenMode.prototype = {
    /**
     * Constructor for this class
     * @param {object} config
     */
    initializer : function(config) {
        // Attach events to the link to change fullscreenmode state so we can do it with
        // JavaScript without refreshing the page
		if (btn = Y.one(config.toggleBtn)) {
			btn.on('click', this.setFullscreenModeState, this);
		}
    },
    /**
     * Sets the state being used for the splash theme
     * @param {Y.Event} e The event that fired
     * @param {string} state The new state
     */
    setFullscreenModeState : function(e) {
        // Prevent the event from refreshing the page
        e.preventDefault();
		var body = Y.one(document.body);
        // Switch over the CSS classes on the body
		body.toggleClass('certispl-collapsed');
		var state = body.hasClass('certispl-collapsed');
		var btn = Y.one('#fullscreenmode a');
		if (state){
			btn.setAttribute('title',M.str.theme_certispl.disablefullscreenmode);
		}else{
			btn.setAttribute('title',M.str.theme_certispl.enablefullscreenmode);
		}
		
		// Dispatch the resize event for resize the page ressource container.
		// Ugly hack because of a IE8 YUI bug.
		if (Y.UA.ie==0 || Y.UA.ie>8) {
			Y.one('window').simulate('resize');
		}
		
        // Store the users selection (Uses AJAX to save to the database)
        M.util.set_user_preference('theme_certispl_fullscreenmode_state', state);

    }
};
// Make the fullscreen mode a fully fledged YUI module
Y.extend(FullscreenMode, Y.Base, FullscreenMode.prototype, {
    NAME : 'Splash UdeM theme fullscreen mode',
    ATTRS : {
        state : {
            value : 0
        }
    }
});
// Our Splash UdeM theme namespace
M.theme_certispl = M.theme_certispl || {};
// Initialisation function for the fullscreen mode
M.theme_certispl.initFullscreenMode = function(cfg) {
    return new FullscreenMode(cfg);
}

}, '@VERSION@', {requires:['base','node','node-event-simulate']});