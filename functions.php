<?php
/**
 * Plugin Name: WordPress Post Spam Filter
 * Description: This plugin will check spam keywords when a post is going to publish by author. If it mathes any spam listed link post status will change to draft and give warning to that author.
 * Plugin URI: http://trickbd.co/plugins/wp-content/plugins/wp-post-spam-filter/documentation/index.html
 * Author: Nasir Uddin Nobin
 * Author URI: http://trickbd.com/author/nasir
 * Version: 1.1
 * License: license purchased
 * License URI: http://codecanyon.net/licenses/standard
 *
 * @category Blocker
 * @package WordPress Post Spam Filter
 * @link http://trickbd.com
 * @since File available since Rekease 1.0.0
 */


if ( ! defined( 'ABSPATH' ) ) { 
   exit; // Exit if accessed directly
}

// default setting when installing plugin
if(!function_exists('trickbd_psb_set_default_settings')) : 
function trickbd_psb_set_default_settings(){
    if(!get_option('trickbd_psb_option')){
        $new_options=array(
            'set_for_post' => 1,
            'max_link_setting' => 1,
            'max_link_count' =>2,
            'spam_action' => 'draft',
            'while_keywords' => "google.com \n youtube.com",
            'spam_keywords' => "spam_link.com \n spamlink2.com",
            'set_sub_dir' => 1,
            'show_suggetion' => 1
          );
        update_option('trickbd_psb_option',$new_options);
    }
}
endif;

register_activation_hook(__FILE__, 'trickbd_psb_set_default_settings');
 /**
 * Include plugin files..
 */
 require_once 'inc/trickbd_psb_functions.php';
 require_once 'inc/trickbd_psb_actions.php';
 require_once 'inc/trickbd_psb_options.php';


 /**
  * Enqueue scripts and js on options page.
  * @param current page
  * @return mixed
  */

if(!function_exists('trickbd_psb_admin_script')) : 
 function trickbd_psb_admin_script($hook){
    if($hook=="settings_page_trickbd_psb_post_spam_blocker"){
        wp_enqueue_script( 'trickbd_psb-admin-js', plugin_dir_url(__FILE__)."js/trickbd_psb.js", array( 'jquery' ), "1.0.0", true);
        wp_enqueue_style( 'trickbd_psb-admin-css',plugin_dir_url(__FILE__)."css/trickbd_psb.css", null , "1.0");
    }
}
add_action('admin_enqueue_scripts','trickbd_psb_admin_script');
endif;