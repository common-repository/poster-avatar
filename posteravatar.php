<?php
/*
Plugin Name: Poster Avatar
Plugin URI: http://www.yriase.fr/plugins
Author: Hugo Giraud
Author URI: http://www.yriase.fr/
Description: Display the poster's avatar.
Version: 0.2
Text Domain: posteravatar
*/

if (!class_exists("PosterAvatar")) {
	class PosterAvatar {

		public function __construct() {
                    if ( is_admin() ) {
                            add_action( 'admin_menu',  array('PosterAvatar', 'addAdminPage') );
                            register_activation_hook( __FILE__, array('PosterAvatar', 'setup') );
                    }
                        add_filter('the_author', array('PosterAvatar','theAuthor'));
                        add_filter('get_comment_author', array('PosterAvatar','theCommentAuthor'));
		}

                public function setup() {
                    $default = array(
                        'position'=>'left',
                        'border'=>'on',
                        'border-color'=>'000000',
                        'size'=>'12',
                        'use-default'=>'off',
                        'default'=>get_bloginfo( 'home' ) . '/' . PLUGINDIR . '/poster-avatar/style/default.gif',
                        'css'=>''
                    );

                    update_option( 'posteravatar', $default );
                }

                public function addAdminPage() {
                    $page = add_options_page(__( 'Poster Avatar', 'posteravatar' ), __( 'Poster Avatar', 'posteravatar' ), 10, 'posteravatar',
                             array('PosterAvatar', 'adminPage'));

                    add_action( 'admin_head-' . $page, array('PosterAvatar', 'addAdminHead') );

                    add_filter( 'ozh_adminmenu_icon_posteravatar', array('PosterAvatar', 'addOzhIcon') );
                }

                public function addAdminHead() { ?>
                    <link rel="stylesheet" href="<?php echo get_bloginfo( 'home' ) . '/' . PLUGINDIR . '/poster-avatar/style/admin.css' ?>" type="text/css" media="all" />
                    <script src="<?php echo get_bloginfo( 'home' ) . '/' . PLUGINDIR . '/poster-avatar/jscolor/jscolor.js' ?>" type="text/javascript" />
                    <?php
                }

                public function addOzhIcon() {
                   return get_bloginfo( 'home' ) . '/' . PLUGINDIR . '/poster-avatar/style/ozh_icon.gif';
                }

                public function adminPage() {
                    if(isset($_POST['save'])) {
                        update_option( 'posteravatar', $_POST['config'] );
                    } elseif(isset($_POST['reset'])) {
                        PosterAvatar::setup();
                    }
                    PosterAvatar::loadTranslation();

                   $options = get_option( 'posteravatar' );

                   ?> <div id="posteravatar" class="wrap" >

                       <div id="posteravatar-credits">
                           <h3><?php _e('Credits', 'posteravatar') ?> : </h3>
                           <?php _e('Developped by', 'posteravatar') ?> <a href="http://www.yriase.fr/" target="_blank">Yriase</a> <br />
                           <?php _e('ColorPicker by', 'posteravatar') ?> <a href="http://jscolor.com/" target="_blank">Jan Odvárko</a>
                       </div>

                       <div id="posteravatar-icon" class="icon32"><br/></div>
			<h2><?php _e( 'Poster Avatar Configuration', 'posteravatar' ) ?></h2>

                        <p>
                            <?php _e( 'You can configure the avatar position, its border, the border\'s color etc...', 'posteravatar' ) ?>
                        </p>

                        <form action="" method="post">
                            <?php _e( 'Avatar position', 'posteravatar' ) ?><br />
                            <?php _e( 'Left', 'posteravatar' ) ?> <input type="radio" name="config[position]" value="left" <?php if($options['position'] == 'left') echo 'checked'; ?> /><br />
                            <?php _e( 'Right', 'posteravatar' ) ?> <input type="radio" name="config[position]" value="right" <?php if($options['position'] == 'right') echo 'checked'; ?> />

                            <br /><br />

                            <?php _e( 'Size', 'posteravatar' ) ?>
                            <select name="config[size]">
                                <?php
                                    for($i = 8; $i <= 48; $i+=2) {
                                        echo '<option value="'.$i.'"';
                                        if($options['size'] == $i) echo 'selected="selected"';
                                        echo ">".$i."</option>";
                                    }
                                ?>
                            </select>

                            <br /><br />

                            <?php _e( 'Use a default avatar', 'posteravatar' ) ?> <input type="checkbox" name="config[use-default]" <?php if($options['use-default'] == 'on') echo 'checked'; ?> />
                            <br />
                            <?php _e( 'Default avatar', 'posteravatar' ) ?> <input type="text" name="config[default]" value="<?php if($options['default']) echo $options['default']; ?>" />

                            <br /><br />

                            <?php _e( 'Border', 'posteravatar' ) ?> <input type="checkbox" name="config[border]" <?php if($options['border'] == 'on') echo 'checked'; ?> />

                            <br /><br />

                            <?php _e( 'Border color', 'posteravatar' ) ?> <input type="text" name="config[border-color]" class="color" value="<?php if($options['border-color']) echo $options['border-color']; ?>" />

                            <br /><br />

                            <?php _e( 'Additional CSS', 'posteravatar' ) ?> <br />
                            <textarea name="config[css]" cols="70" rows="6"><?php if($options['css']) echo $options['css']; ?></textarea>

                            <br /><br />

                            <input type="submit" class="button-primary" name="save" value="<?php _e('Save', 'posteravatar') ?>" /><br />
                            <input type="submit" class="button-secondary" name="reset" value="<?php _e('Reset', 'posteravatar') ?>" />
                          </form>
                        </div>
                  <?php
                }

                public function theAuthor($text = null) {
                    $options = get_option('posteravatar');
                    $class = ''; $style = '';

                    $email = get_the_author_meta('user_email');
                    $avatar = ($options['use-default'] == "on") ? get_avatar($email, $options['size'], $options['default']) : get_avatar($email, $options['size']);

                    if($options['border']) $style .= " border: 1px solid;";
                    if($options['border-color'] && $options['border']) $style .= " border-color: #".$options['border-color'].";";

                    if($options['css']) $style .= $options['css'];

                    $avatar = new SimpleXMLElement($avatar);
                    $avatar->addAttribute('style', $style);

                    if($options['position'] == 'left') $content = $avatar->asXML() . $text;
                    else if ($options['position'] == 'right')  $content = $text . $avatar->asXML();

                    return str_replace('<?xml version="1.0"?>', '', $content);
                }

                public function theCommentAuthor($text = null) {
                    if(!in_the_loop() && !is_admin()) {
                        $options = get_option('posteravatar');
                        $class = ''; $style = '';

                        $email = get_comment_author_email();
                    $avatar = ($options['use-default'] == "on") ? get_avatar($email, $options['size'], $options['default']) : get_avatar($email, $options['size']);

                        if($options['border']) $style .= " border: 1px solid;";
                        if($options['border-color'] && $options['border']) $style .= "border-color: #".$options['border-color'].";";

                        if($options['css']) $style .= $options['css'];

                        $avatar = new SimpleXMLElement($avatar);
                        $avatar->addAttribute('style', $style);

                        if($options['position'] == 'left') $content = $avatar->asXML() . $text;
                        else if ($options['position'] == 'right')  $content = $text . $avatar->asXML();

                        return str_replace('<?xml version="1.0"?>', '', $content);
                    } else {
                        return $text;
                    }
                }

                public function loadTranslation() {
                        $plugin_path = plugin_basename( dirname( __FILE__ ) .'/translations' );
                        load_plugin_textdomain( 'posteravatar', '', $plugin_path );
                }
	}

}

if (class_exists("PosterAvatar")) {
	$pa = new PosterAvatar();
}