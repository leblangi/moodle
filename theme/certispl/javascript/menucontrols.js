YUI.add('moodle-theme_certispl-menucontrols', function(Y) {

/**
 * Theme Splash UdeM classe MenuControls.
 * Initialiser cette classe en appelant M.theme_certispl.init
 */
var MenuControls = function() {
    MenuControls.superclass.constructor.apply(this, arguments);
};
MenuControls.prototype = {
    /**
     * Constructeur pour cette classe
     * @param {object} config
     */
    initializer : function(config) {
	
		Y.all(config.div+' a.yui3-menu-label').each(function(node) {
			
			var submenu = node.get('parentNode').one('.custom_menu_submenu');
			submenu.setStyle('overflow','visible');
			//IE7 Overflow Bug use fixed width
			if (Y.UA.ie == 7) {
				var p1 = parseFloat(submenu.getComputedStyle('paddingLeft').replace(/[A-Za-z$-]/g, ""));
				var p2 = parseFloat(submenu.getComputedStyle('paddingRight').replace(/[A-Za-z$-]/g, ""));
				var width = (submenu.get('scrollWidth') - p1 - p2) + 'px';
			}
			var anim = new Y.Anim({
        		node: submenu,
				easing: Y.Easing.easeIn,
        		duration: 0.15,
        		to: {
					opacity:1,
					height: function(node) {
								var p1 = parseFloat(node.getComputedStyle('paddingTop').replace(/[A-Za-z$-]/g, ""));
								var p2 = parseFloat(node.getComputedStyle('paddingBottom').replace(/[A-Za-z$-]/g, ""));
								return (node.get('scrollHeight') - p1 - p2);
					}
				},
        		from: {opacity:0,height:0}
   			});
			anim.on('end',function(e,submenu) {
				submenu.setStyle('overflow','visible');
				if (Y.UA.ie == 7) submenu.setStyle('width',width);
			},anim,submenu);
			node.on('mouseenter', function(e){
				if (submenu.getStyle('visibility') == 'hidden') {
					submenu.setStyle('overflow','hidden');
					submenu.setStyle('opacity',0);
					Y.later(25, e.target, function(){
						if (Y.UA.ie == 7) submenu.setStyle('width',width);
						anim.run();
					});
				}
			}, this);
		});
	}
};
// Faire de cette classe un module YUI valide
Y.extend(MenuControls, Y.Base, MenuControls.prototype, {
    NAME : 'Splash UdeM menu controls',
    ATTRS : {}
});

// Namespace du theme
M.theme_certispl = M.theme_certispl || {};
// Initialisation de la fonction pour l'objet e cette classe
M.theme_certispl.initMenuControls = function(cfg) {
    return new MenuControls(cfg);
}

}, '@VERSION@', {requires:['base','node','anim']});