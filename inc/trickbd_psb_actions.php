<?php 
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}
/**
 * On publish_post hook check if there is any spam word and take action.
 */
if(!function_exists('nobin_post_spam_check')) : 
function nobin_post_spam_check($id,$post){
    if(current_user_can('manage_options') && trickbd_psb_get_option('no_admin_filter')){
        return;
    }

    // get options
    $get_spams       = trickbd_psb_get_option('spam_keywords');
    $keywords        = psb_get_array_from_txt($get_spams);
    $while_keywords  = trickbd_psb_get_option('while_keywords');
    $while_keywords  = psb_get_array_from_txt($while_keywords);
    $content         = $post->post_content;
    $title           = $post->title;

    // find all genaral links
    $genaral_links=array();

    // url finder regex
    $url_pattern = '/((http|https)\:\/\/)?[a-zA-Z0-9\.\/\?\:@\-_=#]+\.([a-zA-Z0-9\.\/\?\:@\-_=#])*/';
    preg_match_all($url_pattern, $content, $matches);
    foreach ($matches as $key => $match) {
        foreach ($match as $k => $url) {
            $formated_url = trickbd_psb_format_url($url);
            if($formated_url) $genaral_links[]=$formated_url;
        }
    }

    // we will store spam links here
    $spam_found=array();

    // spam listed links
        foreach ($keywords as $spam) {
            $re="/($spam)/i";
            preg_match($re,$content,$matchs);
            if($matchs){
                $spam_found[]=$matchs[0];
            }
        }

    // exclude white lised links from spam
    foreach ($spam_found as $key => $spam_link) {
       if(in_array($spam_link, $while_keywords)){
            unset($spam_found[$key]);
       }
    }

    // exclude white lised links from general link
    foreach ($genaral_links as $key => $spam_link) {
       if(in_array($spam_link, $while_keywords)){
            unset($genaral_links[$key]);
       }
    }

    //load options 
    $spam_action    = trickbd_psb_get_option('spam_action');

    switch ($spam_action) {
        case 'draft':    $new_status='draft';    break;
        case 'pending':  $new_status='pending';  break;
        default:         $new_status='draft';    break;
    }

    // get options
    $no_spam_notify = trickbd_psb_get_option('no_spam_notify');
    $max_link_setting = trickbd_psb_get_option('max_link_setting');
    $max_link_count = trickbd_psb_get_option('max_link_count');

    if(!count($spam_found) && $max_link_setting && count($genaral_links) >= $max_link_count){
        // no spam word && max link seting yes && count of general link is greater than max_link_count
        // do action. make this post pending.
        // give user a notification why post is pending.
        
        if(!$no_spam_notify){
            update_post_meta($id,"spam_post_detected","general_case");
        }
        psb_update_post_status($id,$new_status);
    }

    if(count($spam_found)){
        // if no_spam_notify chacked dont show notification..
        if(!$no_spam_notify){
            update_post_meta($id,"spam_post_detected",$spam_found);
        }
        psb_update_post_status($id,$new_status);
    }
}
endif;

// make sure user turned it on!
if(trickbd_psb_get_option('set_for_post')){

    /**
     * @param hook name
     * @param function
     */
    add_action('publish_post', 'nobin_post_spam_check',0,2);
}

/**
 * Show error message to user
 * @hook admin_notices
 */
if(!function_exists('nobin_show_spam_error')) : 
function nobin_show_spam_error(){

    if(!isset($_GET['post'])){
        return;
    }
    $post_id= $_GET['post'];

    $spams=get_post_meta($post_id, 'spam_post_detected', true);
    if(!empty($spams)){

        $output ="";
        if($spams != "general_case"){
            $output.="Spam detected. Remove spam marked links and try again. <br><ul>";
            foreach ($spams as $spam) {
                $output.="<li>{$spam}</li>";
            }
            $output.="</ul>";
        }else{
             $output.="Post contains too many links. This post need admin's review to be published.";
        }
        echo '<div class="error"><p>'.$output.'</p></div>';
        delete_post_meta( $post_id, 'spam_post_detected');
    }
}
endif;
add_action('admin_notices','nobin_show_spam_error');

