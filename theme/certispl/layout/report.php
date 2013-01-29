<?php

certispl_check_colourswitch();
certispl_check_fullscreenmode();

$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$isinfullscreenmode = certispl_get_fullscreenmode_state();
$hassideprefakeblock = $PAGE->blocks->region_has_fakeblock('side-pre');
$maintitle = $COURSE->id == 1 ? $COURSE->fullname : '';

$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));

certispl_initialise_colourswitcher($PAGE);
certispl_initialise_menucontrols($PAGE);
certispl_initialise_fullscreenmode($PAGE);

$bodyclasses = array();
$bodyclasses[] = 'certispl-'.certispl_get_colour();

// Put the fullscreenmode unless we have fakeblock (read: important, unhideable block);
if ($isinfullscreenmode) {
	$bodyclasses[] = 'certispl-collapsed';
}

if ($hassideprefakeblock) {
	$bodyclasses[] = 'side-pre-fakeblock';
}

if (!$hassidepre) {
    $bodyclasses[] = 'content-only';
}

$haslogo = (!empty($PAGE->theme->settings->logo));
$hasfootnote = (!empty($PAGE->theme->settings->footnote));
$hidetagline = (!empty($PAGE->theme->settings->hide_tagline) && $PAGE->theme->settings->hide_tagline == 1);

if (!empty($PAGE->theme->settings->tagline)) {
    $tagline = $PAGE->theme->settings->tagline;
} else {
    $tagline = get_string('defaulttagline', 'theme_certispl');
}

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <meta name="description" content="<?php p(strip_tags(format_text($SITE->summary, FORMAT_HTML))) ?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
</head>
<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">
    <?php echo $OUTPUT->standard_top_of_body_html() ?>
    <div id="page">
        <?php if ($hasheading || $hasnavbar) { ?>
        <div id="page-header">
			<?php echo $OUTPUT->heading($SITE->fullname, 1, 'hiddentitle'); ?>
            <div id="page-header-wrapper" class="wrapper clearfix">
				<div id="logobox">
					<?php 
					if ($haslogo) {
                        echo html_writer::link(new moodle_url('/'), "<img src='".$PAGE->theme->settings->logo."' alt='" . get_string('certitude', 'theme_certispl') . "' />",array('title'=>get_string('certitude', 'theme_certispl')));
                    } else {
						echo html_writer::link(new moodle_url('/'), "<img style='width:300px;height:84px' src='".$OUTPUT->pix_url('logo_certitude', 'theme')."' alt='" . get_string('certitude', 'theme_certispl') . "' />",array('title'=>get_string('certitude', 'theme_certispl')));
					} ?>
                </div>
                <?php if ($hasheading) { ?>
                <div id="headermenu">
                    <?php if (isloggedin()) {
                        echo html_writer::start_tag('div', array('id'=>'userdetails'));
                        echo html_writer::start_tag('p', array('class'=>'welcome'));
						echo get_string('usergreeting', 'theme_certispl', '<span>'.$USER->firstname.'</span>');
						echo html_writer::end_tag('p');
                        echo html_writer::start_tag('p', array('class'=>'prolog'));
                        echo html_writer::link(new moodle_url('/user/profile.php', array('id'=>$USER->id)), get_string('myprofile')).' | ';
                        echo html_writer::link(new moodle_url('/login/logout.php', array('sesskey'=>sesskey())), get_string('logout'));
                        echo html_writer::end_tag('p');
                        echo html_writer::end_tag('div');
                        echo html_writer::tag('div', $OUTPUT->user_picture($USER, array('size'=>62)), array('class'=>'userimg'));
                    } else {
						$mainloginval = get_string('loginhere', 'theme_certispl');
						$mainlogintitle = $mainloginval;
						$loginurl = get_login_url();
                        echo html_writer::start_tag('div', array('id'=>'userdetails_loggedout'));
						echo html_writer::start_tag('form', array('name'=>'login', 'id'=>'login', 'method'=>'POST', 'action'=>$loginurl));
						echo html_writer::start_tag('p', array('class'=>'welcome'));
						$mainloginbutton = html_writer::empty_tag('input', array('type'=>'submit', 'value'=>$mainloginval, 'id'=>'mainlogin', 'title'=>$mainlogintitle));
						echo get_string('welcome', 'theme_certispl', $mainloginbutton);
						echo html_writer::end_tag('p');
						echo html_writer::end_tag('form');
                        echo html_writer::end_tag('div');
                    } ?>
                </div>
            <?php } // End of if ($hasheading)?>
                <!-- DROP DOWN MENU -->
                <div id="dropdownmenu">
                    <?php if ($hascustommenu && isloggedin()) { ?>
                    <div id="custommenu"><?php echo $custommenu; ?></div>
                    <?php }else{ ?>
					 <div id="nocustommenu"></div>
					<?php } ?>
					<div id="usercontrols" >
						<div id="colourswitcher">
							<a 
								title="<?php echo get_string('choosecolorgeneral', 'theme_certispl') ?>" 
								href="#" 
								class="colourswitcher">
							</a>
							<div id="colourselector">
								<ul>
									<li>
									<a 
										title="<?php echo get_string('choosecolor', 'theme_certispl', get_string('blue', 'theme_certispl')) ?>" 
										href="<?php echo new moodle_url($PAGE->url, array('certisplcolour'=>'blue')); ?>" 
										class="styleswitch colour-blue <?php if (certispl_get_colour() == 'blue') echo 'active' ?>">
									</a>
									</li>
									<li>
									<a 
										title="<?php echo get_string('choosecolor', 'theme_certispl', get_string('green', 'theme_certispl')) ?>" 
										href="<?php echo new moodle_url($PAGE->url, array('certisplcolour'=>'green')); ?>" 
										class="styleswitch colour-green <?php if (certispl_get_colour() == 'green') echo 'active' ?>">
									</a>
									</li>
									<li>
									<a 
										title="<?php echo get_string('choosecolor', 'theme_certispl', get_string('red', 'theme_certispl')) ?>" 
										href="<?php echo new moodle_url($PAGE->url, array('certisplcolour'=>'red')); ?>" 
										class="styleswitch colour-red <?php if (certispl_get_colour() == 'red') echo 'active' ?>">
									</a>
									</li>
									<li>
									<a 
										title="<?php echo get_string('choosecolor', 'theme_certispl', get_string('orange', 'theme_certispl')) ?>" 
										href="<?php echo new moodle_url($PAGE->url, array('certisplcolour'=>'orange')); ?>" 
										class="styleswitch colour-orange <?php if (certispl_get_colour() == 'orange') echo 'active' ?>">
									</a>
									</li>
								</ul>
							</div>
                    	</div>
						<?php if (!in_array('content-only', $bodyclasses)) { ?>
						<div class="separator" ></div>
						<div id="fullscreenmode">
							<a 
								title="<?php 
									$string = $isinfullscreenmode ? 'disablefullscreenmode' : 'enablefullscreenmode';
									echo get_string($string, 'theme_certispl');
								?>" 
								href="<?php 
									$state = $isinfullscreenmode ? 'false' : 'true';
									echo new moodle_url($PAGE->url, array('fullscreenmodestate'=>$state));
								?>"> 
							</a>
						</div>
						<?php  }							
                   			$lang = $OUTPUT->lang_menu();
                   			if ($lang != "") {
                   				echo '<div class="separator" ></div>';
                   				echo $lang;
                   			}
                   		?>
                   		<div class="usercontrols-right" ></div>
					</div>
                </div>
                <!-- END DROP DOWN MENU -->
            </div>
        </div>
    <?php } // if ($hasheading || $hasnavbar) ?>
        <!-- END OF HEADER -->
        <!-- START OF CONTENT -->

        <div id="page-content" class="clearfix">
            <div id="report-main-content">
                <div class="region-content">
                		<?php if ($PAGE->url != $CFG->wwwroot . "/"  || isloggedin()) { ?>
											<div class="navbar">
                    			<div class="wrapper clearfix">
                    				<div class="breadcrumb"><?php if ($hasnavbar) echo $OUTPUT->navbar(); ?></div>
                        			<div class="navbutton"> <?php echo $PAGE->button; ?></div>
                   			 </div>
                			</div>
										<?php } ?>
										<?php if ($PAGE->heading != $maintitle) { ?>
											<h1 id="main-title" ><?php echo $PAGE->heading; ?></h1>
										<?php } ?>
                    <?php echo $OUTPUT->main_content() ?>
                </div>
            </div>
            <?php if ($hassidepre) { ?>
            <div id="report-region-wrap">
                <div id="report-region-pre" class="block-region">
                    <div class="region-content">
                        <?php echo $OUTPUT->blocks_for_region('side-pre') ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <!-- END OF CONTENT -->
        <div class="clearfix"></div>
    <!-- END OF #Page -->
    </div>
    <!-- START OF FOOTER -->
    <?php if ($hasfooter) { ?>
    <div id="page-footer">
	<div id="footer-wrapper">
            <?php if ($hasfootnote) { ?>
            <div id="footnote"><?php echo $PAGE->theme->settings->footnote; ?></div>
            <?php } ?>
            <p class="helplink"><?php echo page_doc_link(get_string('moodledocslink')) ?></p>
            <?php
            echo $OUTPUT->login_info();
            echo $OUTPUT->home_link();
            $a = explode('.',php_uname('n'));
            echo html_writer::tag('p','Serveur: ' . $a[0], array('class'=>'serverinfo'));
            echo $OUTPUT->standard_footer_html();
            ?>
        </div>
    </div>
    <?php }  
	echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>