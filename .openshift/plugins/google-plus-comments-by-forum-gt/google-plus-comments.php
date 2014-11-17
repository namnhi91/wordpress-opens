<?php 
/*
	Plugin Name: Wordpress Google Plus Comments
	Plugin URI: http://andreapernici.com/wordpress/google-plus-comments/
	Description: Add Google Plus Comment System to Wordpress Posts.
	Version: 1.0.1
	Author: Andrea Pernici
	Author URI: http://www.andreapernici.com/
	
	Copyright 2013 Andrea Pernici (andreapernici@gmail.com)
	
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

	*/

define( 'GPCOMMENTS_VERSION', '1.0.1' );

$pluginurl = plugin_dir_url(__FILE__);
if ( preg_match( '/^https/', $pluginurl ) && !preg_match( '/^https/', get_bloginfo('url') ) )
	$pluginurl = preg_replace( '/^https/', 'http', $pluginurl );
define( 'GPCOMMENTS_FRONT_URL', $pluginurl );

define( 'GPCOMMENTS_URL', plugin_dir_url(__FILE__) );
define( 'GPCOMMENTS_PATH', plugin_dir_path(__FILE__) );
define( 'GPCOMMENTS_BASENAME', plugin_basename( __FILE__ ) );

if (!class_exists("AndreaGooglePlusComments")) {

	class AndreaGooglePlusComments {
		/**
		 * Class Constructor
		 */
		function AndreaGooglePlusComments(){
		
		}
		
		/**
		 * Enabled the AndreaGooglePlusComments plugin with registering all required hooks
		 */
		function Enable() {

			add_action('admin_menu', array("AndreaGooglePlusComments",'GooglePlusCommentsMenu'));
			//add_action("wp_insert_post",array("AndreaFacebookSend","SetFacebookSendCode"));
			add_action('wp_head', array("AndreaGooglePlusComments",'GooglePlusCommentsInit'));
			$options_after = get_option( 'google_plus_comments_after_content' );
			$options_replace_wp = get_option( 'google_plus_comments_replace_wp' );
			$options_size = get_option( 'google_plus_comments_size' );
			if(!$options_size)
				update_option( $google_plus_comments_size, '600' );
			if(!$options_replace_wp)
		        update_option( $google_plus_comments_replace_wp, '1' );
			if ($options_after) {
				add_filter("the_content", array("AndreaGooglePlusComments","SetGooglePlusOneCodeFilter"));
			}
			if ($options_replace_wp) {
				//add_action("comments_template",array("AndreaGooglePlusComments","SetGooglePlusCommentsCode"));
				add_filter("comments_template",array("AndreaGooglePlusComments","SetGooglePlusCommentsCode"));
			}	
			
		}
		
		/**
		 * Set the Admin editor to set options
		 */
		 
		function SetAdminConfiguration() {
			add_action('admin_menu', array("AndreaGooglePlusComments",'GooglePlusCommentsMenu'));
			return true;
		}
		
		function GooglePlusCommentsInit() {
			$google_lang = get_option( 'google_plus_comments_lang' );
			if (is_single()){
				/*wp_deregister_script('registra_google_plus_comments_js');
				wp_register_script('registra_google_plus_comments_js','http://apis.google.com/js/plusone.js',false,null,true);
				add_action('init',array(&$this,"registra_google_plus_comments_js"));*/
				echo '<script type="text/javascript" src="http://apis.google.com/js/plusone.js">';
				if ($google_lang!='') {echo "{lang: '".$google_lang."'}";}
				echo '</script>';
echo "<style>div[id^='___comments_'], div[id^='___comments_'] iframe {width: 100% !important;}</style>";
			}
		}
		
		/*function registra_google_plus_comments_js() {
			wp_enqueue_script('registra_google_plus_comments_js');
		}*/
		
		function GooglePlusCommentsMenu() {
			add_options_page('Google Plus Comments Options', 'Google Plus Comments', 'manage_options', 'google-plus-comments-options', array("AndreaGooglePlusComments",'GooglePlusCommentsOptions'));
		}
		
		function GooglePlusCommentsOptions() {
			if (!current_user_can('manage_options'))  {
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}
			
		    // variables for the field and option names 
		    $google_plus_comments_replace_wp = 'google_plus_comments_replace_wp';
		    $google_plus_comments_after_content = 'google_plus_comments_after_content';
		    $google_plus_comments_size = 'google_plus_comments_size';
		    $google_plus_comments_lang = 'google_plus_comments_lang';
		    
		    $hidden_field_name = 'mt_submit_hidden';
		    $data_field_name_replace_wp = 'google_plus_comments_replace_wp';
		    $data_field_name_after = 'google_plus_comments_after_content';
		    $data_field_comments_size = 'google_plus_comments_size';
			$data_field_comments_lang = 'google_plus_comments_lang';
		
		    // Read in existing option value from database
		    $opt_val_replace_wp = get_option( $google_plus_comments_replace_wp );
		    $opt_val_after = get_option( $google_plus_comments_after_content );
		    $opt_val_comments_size = get_option( $google_plus_comments_size );
		    $opt_val_comments_lang = get_option( $google_plus_comments_lang );
		    
		    // See if the user has posted us some information
		    // If they did, this hidden field will be set to 'Y'
		    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
		        // Read their posted value
		        $opt_val_replace_wp = $_POST[ $data_field_name_replace_wp ];
		    	$opt_val_after = $_POST[ $data_field_name_after ];
		    	$opt_val_comments_size = $_POST[ $data_field_comments_size ];
		    	$opt_val_comments_lang = $_POST[ $data_field_comments_lang ];
		
		        // Save the posted value in the database
		        update_option( $google_plus_comments_replace_wp, $opt_val_replace_wp );
		        update_option( $google_plus_comments_after_content, $opt_val_after );
		        update_option( $google_plus_comments_size, $opt_val_comments_size );
		        update_option( $google_plus_comments_lang, $opt_val_comments_lang );
		
		        // Put an settings updated message on the screen
		
		?>
		<div class="updated"><p><strong><?php _e('settings saved.', 'menu-google-plus-comments' ); ?></strong></p></div>
		<?php
		
		    }
		    // Now display the settings editing screen
		    echo '<div class="wrap">';
		    // header
		    echo "<h2>" . __( 'Google Plus Comments Options', 'menu-google-plus-comments' ) . "</h2>";
		    // settings form
		    
		    ?>
		
		<form name="form1" method="post" action="">
		<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
		
		<?php $options_replace_wp = get_option( 'google_plus_comments_replace_wp' ); ?>
		<p><?php _e("Show before Wordpress Comments:", 'menu-google-plus-comments' ); ?> 
		<input type="checkbox" name="google_plus_comments_replace_wp" value="1"<?php checked( 1 == $options_replace_wp ); ?> />
		
		<?php $options_before = get_option( 'google_plus_comments_after_content' ); ?>
		<p><?php _e("Show After Content:", 'menu-google-plus-comments' ); ?> 
		<input type="checkbox" name="google_plus_comments_after_content" value="1"<?php checked( 1 == $options_before ); ?> /> <b><span style="color:#ff0000">(use only if replacing comments not working</span></b></p>
		
		<?php $options_size = get_option( 'google_plus_comments_size' ); ?>
		<p><?php _e("Button Size:", 'menu-google-plus-comments' ); ?> 
		<input type="text" name="google_plus_comments_size" value="<?php echo $options_size; ?>" /> (put the width in pixel - IE: 600)</p>
		
		<?php $options_lang = get_option( 'google_plus_comments_lang' ); ?>
		<p><?php _e("Language:", 'menu-google-plus-comments' ); ?> 
		<input type="text" name="google_plus_comments_lang" value="<?php echo $options_lang; ?>" /> (default blank is en, you can put it for italian.)</p>

		<p class="submit">
		<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
		</p>
		
		</form>
		<?php echo "<h2>" . __( 'Put Function in Your Theme', 'menu-google-plus-comments' ) . "</h2>"; ?>
		<p>If you want to put the box anywhere in your theme or you have problem showing the box simply use this function:</p>
		<p>if (function_exists('andrea_google_plus_comments')) { andrea_google_plus_comments(); }</p>
		</div>
		
		<?php

		}
		
		/**
		 * Setup Iframe Buttons for actions
		 */
		
		function SetGooglePlusCommentsCode() {
			
			$google_size = get_option( 'google_plus_comments_size' );
			$sizer = $google_size;
			
			
			$button = '<div class="g-comments" ';
    		$button .= 'data-href="'. get_permalink().'" ';
        	$button .= 'data-width="'.$sizer.'" ';
        	$button .= 'data-first_party_property="BLOGGER" ';
        	$button .= 'data-view_type="FILTERED_POSTMOD">';
        	$button .= '</div>';
			
			echo $button;
		}		
		
		/**
		 * Setup Iframe Buttons for Filter
		 */
		
		function SetGooglePlusCommentsCodeFilter($content) {
			
			$google_size = get_option( 'google_plus_comments_size' );
			$sizer = $google_size;
			
			$button = '<div class="g-comments" ';
    		$button .= 'data-href="'. get_permalink().'" ';
        	$button .= 'data-width="'.$sizer.'"';
        	$button .= 'data-first_party_property="BLOGGER" ';
        	$button .= 'data-view_type="FILTERED_POSTMOD">';
        	$button .= '</div>';
			
			return $content.$button;
		}	
		
		/**
		 * Returns the plugin version
		 *
		 * Uses the WP API to get the meta data from the top of this file (comment)
		 *
		 * @return string The version like 1.0.0
		 */
		function GetVersion() {
			if(!function_exists('get_plugin_data')) {
				if(file_exists(ABSPATH . 'wp-admin/includes/plugin.php')) require_once(ABSPATH . 'wp-admin/includes/plugin.php'); //2.3+
				else if(file_exists(ABSPATH . 'wp-admin/admin-functions.php')) require_once(ABSPATH . 'wp-admin/admin-functions.php'); //2.1
				else return "0.ERROR";
			}
			$data = get_plugin_data(__FILE__);
			return $data['Version'];
		}
	
	}
}

/*
 * Plugin activation
 */
 
if (class_exists("AndreaGooglePlusComments")) {
	$afs = new AndreaGooglePlusComments();
}


if (isset($afs)) {
	add_action("init",array("AndreaGooglePlusComments","Enable"),1000,0);
	//add_action("wp_insert_post",array("AndreaFacebookSend","SetFacebookSendCode"));
}

if (!function_exists('andrea_google_plus_comments')) {
	function andrea_google_plus_comments() {
		$google_plus_comments = new AndreaGooglePlusComments();
		return $google_plus_comments->SetGooglePlusCommentsCode();
	}	
}
