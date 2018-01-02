<?php 
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

// format urls
if(!function_exists('trickbd_psb_format_url')) : 
function trickbd_psb_format_url($url){

    $set_sub_dir=trickbd_psb_get_option('set_sub_dir');

    $url=trim(strtolower(($url)));
    $url=str_replace("http://","",$url);
    $url=str_replace("https://","",$url);
    if($set_sub_dir){
        $url=explode("/",$url)[0];
    }
    if($url != "" && $url != "http" && $url != "https" && $url != "http://" && $url != "https://" && strlen($url) > 4 ) {
        return $url;
    }
}
endif;

/**
 * format text to url list
 * @param string 
 * @return string formated list text
 */
if(!function_exists('trickbd_psb_format_url_list')) : 
function trickbd_psb_format_url_list($plaintext){
    $spams = explode("\n", $plaintext);
    $new_list=array();
    foreach ($spams as $spam) {
        $new_list[]=trickbd_psb_format_url($spam);
    }
    return join("\n",$new_list);
}
endif;

// make array from text
if(!function_exists('psb_get_array_from_txt')) : 
function psb_get_array_from_txt($txt){
    $txt=trickbd_psb_format_url_list($txt);
    return explode("\n", $txt);
}
endif;

// get option function
if(!function_exists('trickbd_psb_get_option')) : 
function trickbd_psb_get_option($name){
    return isset(get_option('trickbd_psb_option')[$name]) ? get_option('trickbd_psb_option')[$name] : false;
}
endif;

// update status
if(!function_exists('psb_update_post_status')) : 
function psb_update_post_status($post_id,$new_status){
     wp_update_post(array(
            'ID' => $post_id,
            'post_status' => $new_status
        ));
     
     //make identy 
    update_post_meta($post_id,"caught_spam",true);

    add_filter('redirect_post_location',function( $location, $post_id){
         $location= remove_query_arg('message', $location);
         $location= add_query_arg('message', 8 ,$location);
         return $location;

    }, 8, 2);
}
endif;

// show suggestion of spam links
if(!function_exists('load_spam_comments_url')) : 
function load_spam_comments_url(){
    $q= new WP_Comment_Query;
    $comments = $q->query(array(
        'status' => 'spam'
        ));

    $posts=new WP_Query(array(
            'post_type' => 'post',
            'post_status' => array('publish', 'pending', 'draft' , 'auto-draft', 'future', 'private', 'inherit', 'trash')

        ));

    $old_spams=trickbd_psb_get_option('spam_keywords');
    $old_spams=explode("\n", $old_spams);
    $old=array();
    foreach ($old_spams as $spam) {
        $old[]=trickbd_psb_format_url($spam);
    }

    $list=array();

    $url_pattern = '/((http|https)\:\/\/)?[a-zA-Z0-9\.\/\?\:@\-_=#]+\.([a-zA-Z0-9\.\/\?\:@\-_=#])*/';

    // seach oll comments
    foreach ($comments as $comment) {
        $content=$comment->comment_content;
        preg_match_all($url_pattern, $content, $matches);
        foreach ($matches as $key => $match) {
            foreach ($match as $k => $url) {
                $formated_url = trickbd_psb_format_url($url);
                if($formated_url) $list[]=$formated_url;
            }
        }
    }

    // search all posts
    foreach ($posts->posts as $key => $post) {
        $content=$post->post_content;
        preg_match_all($url_pattern, $content, $matches);
        foreach ($matches as $key => $match) {
            foreach ($match as $k => $url) {
                $formated_url = trickbd_psb_format_url($url);
                if($formated_url) $list[]=$formated_url;
            }
        }
    }
    $list=array_unique($list);
    $show_list=array_diff($list, $old);
    
    // output
    foreach ($show_list as $url) {
        echo "<a class='trickbd_psb_spam_link' href='{$url}'> <span class='dashicons dashicons-warning'></span> {$url}</a>"; 
    }
}
endif;