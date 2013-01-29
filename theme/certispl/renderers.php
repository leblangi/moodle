<?php

class theme_certispl_core_renderer extends core_renderer {

    protected function render_custom_menu(custom_menu $menu) {
        global $USER, $PAGE;

		// Ajouter le menu «Accueil»
		$menu->add(get_string('home'), new moodle_url('/?redirect=0'), get_string('home'),-5);


		// Ajouter le menu «Mes cours»
		$branchtitle = get_string('frontpagecourselist');
		$branchurl   = new moodle_url('/course');
		$branch = $menu->add($branchtitle, $branchurl, $branchtitle,-3);

        if (isloggedin()) {
            $mycourses = $this->page->navigation->get('mycourses');
            if ($mycourses && $mycourses->has_children()) {
                $maxitems = 10;
                $itemid = 0;
                $childs = array();


                foreach ($mycourses->children as $coursenode) {
                    $childs[] = array($coursenode->get_content(), $coursenode->action, $coursenode->get_title());
                }
                foreach ($childs as $coursenode) {
                    $branch->add($coursenode[0], $coursenode[1], $coursenode[2]);
                    $itemid++;
                    if ($itemid >= $maxitems) {
                        $showall = get_string('navshowallcourses', 'admin');
                        $branch->add($showall, $branchurl, $showall);
                        break;
                    }
                }
            }
			// Ajouter le menu «Ajouter un cours»
            $context = $PAGE->context;
            if (has_capability('moodle/course:request', $context)) {
				if ($this->can_add_courses() || has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM))) {
                $menu->add(get_string('createcoursesite', 'theme_certispl'), new moodle_url('/course/category.php?id=1'), get_string('createcoursesite', 'theme_certispl'), -1);
            }
			}
        }

        return parent::render_custom_menu($menu);
    }

    protected function render_custom_menu_item(custom_menu_item $menunode) {
        global $CFG, $PAGE;
        // Required to ensure we get unique trackable id's
        static $submenucount = 0;

        $current = false;
        $local = false;
        if ($menunode->get_url() !== null) {
            if (!(strpos($menunode->get_url(), $CFG->wwwroot) === false)) {
                $local = true;
            }
            $itemurl = str_replace('?redirect=0', '', $menunode->get_url());
            $current = ($itemurl == trim(str_replace('/index.php', '', $PAGE->url)));
        }

        $text = self::translate_custom_menu_item($menunode->get_text());
        $title = self::translate_custom_menu_item($menunode->get_title());


        if ($menunode->has_children()) {
            // If the child has menus render it as a sub menu
            $submenucount++;
            $content = html_writer::start_tag('li', array('class' => ($current ? 'yui3-menuitem-current' : '')));
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#cm_submenu_' . $submenucount;
            }
            $text .= html_writer::tag('span', '', array('class' => 'yui3-menu-label-arrow'));
            $content .= html_writer::link($url, $text, array('class' => 'yui3-menu-label', 'title' => $title, 'target' => ($local ? '' : '_blank')));
            $content .= html_writer::start_tag('div', array('id' => 'cm_submenu_' . $submenucount, 'class' => 'yui3-menu custom_menu_submenu'));
            $content .= html_writer::start_tag('div', array('class' => 'yui3-menu-content'));
            $content .= html_writer::start_tag('ul');
            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item($menunode);
            }
            $content .= html_writer::end_tag('ul');
            $content .= html_writer::end_tag('div');
            $content .= html_writer::end_tag('div');
            $content .= html_writer::end_tag('li');
        } else {
            // The node doesn't have children so produce a final menuitem

            $content = html_writer::start_tag('li', array('class' => 'yui3-menuitem' . ($current ? ' yui3-menuitem-current' : '')));
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#';
            }
            $content .= html_writer::link($url, $text, array('class' => 'yui3-menuitem-content', 'title' => $title, 'target' => ($local ? '' : '_blank')));
            $content .= html_writer::end_tag('li');
        }

        // Return the sub menu
        return $content;
    }

    private static function translate_custom_menu_item($string) {
        $matches = array();
        if (preg_match('/^\[\[([a-zA-Z0-9\-\_\:]+)\]\]$/', $string, $matches)) {
            $match = $matches[1];
            $sm = get_string_manager();
            if ($sm->string_exists($matches[1], 'theme_certispl')) {
                $string = get_string($match, 'theme_certispl');
            } else if ($sm->string_exists($match, 'core')) {
                $string = get_string($match, 'core');
            }
        }
        return $string;
    }

    private function can_add_courses() {
        global $USER, $DB;
        return $DB->count_records_select('role_assignments', "userid=$USER->id AND roleid IN ( 1, 2, 3, 4, 9, 11, 13 )") > 0;
    }
	
	/**
     * Produces the navigation bar for the certispl theme
     * Note that this navbaris created to generate the class associated with the itemcolor
	 *
     * @return string
     */
	public function navbar() {
        $items = $this->page->navbar->get_items();

        $htmlblocks = array();
        // Iterate the navarray and display each node
        $itemcount = count($items);
        $separator = get_separator();
		$startcolorid = 7;
		
        for ($i=0;$i < $itemcount;$i++) {
            $item = $items[$i];
            $item->hideicon = true;
			
			// Add classes manually for correct color selection
			$colorid = $i;
			if ($itemcount < $startcolorid) {
				$colorid = $i - $itemcount + $startcolorid;
			}
			$classes = 'color'.($colorid + 1);
			
			// Add classes manually for old browsers
			if ($i===0) {
                $classes .= ' first-child';
            }
			if ($i===$itemcount-1) {
                $classes .= ' last-child';
            }
			
            if ($i===0) {
                $content = html_writer::tag('li', $this->render($item), array('class'=>$classes));
            } else {
                $content = html_writer::tag('li', $separator.$this->render($item), array('class'=>$classes));
            }
            $htmlblocks[] = $content;
        }

        //accessibility: heading for navbar list  (MDL-20446)
        $navbarcontent = html_writer::tag('span', get_string('pagepath'), array('class'=>'accesshide'));
        $navbarcontent .= html_writer::tag('ul', join('', $htmlblocks));
        // XHTML
        return $navbarcontent;
    }
}