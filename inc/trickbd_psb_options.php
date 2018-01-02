<?php 
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

// make our option page
if(!function_exists('trickbd_psb_options_page')) : 
function trickbd_psb_options_page(){
	$trickbd_psb=add_options_page('Spam filter for post', 'Spam Filter','manage_options','trickbd_psb_post_spam_blocker',function(){
		echo '<div class="warp"><form action="options.php" method="POST">';
		do_settings_sections('trickbd_psb_op_section');
		settings_fields('trickbd_psb_op_section');
		submit_button();
		echo "</form></div>";
	});
}
endif;
add_action('admin_menu','trickbd_psb_options_page');

// option page setup
if(!function_exists('trickbd_psb_options_init')) : 
function trickbd_psb_options_init(){
	/**
	 * Register setting for options.
	 */
	register_setting('trickbd_psb_op_section','trickbd_psb_option',function($input){
		return $input;
	});

	/**
	 * Binding all fileds to a section.
	 */
	
	add_settings_section('trickbd_psb_op_section_id','WP Post Spam Filter Options', function(){
		$help_link="http://trickbd.co/plugins/wp-content/plugins/wp-post-spam-filter/documentation/index.html";
		echo "<p>Customize spam filter! Visit <a href='{$help_link}'>documentation</a> to learn more.</p>";
	},'trickbd_psb_op_section');

	/**
	 * Input Section 1
	 */
	add_settings_field('trickbd_psb_status', 'Filter Setting', function(){
		$set_for_post=isset(get_option('trickbd_psb_option')['set_for_post']) ? get_option('trickbd_psb_option')['set_for_post'] : "";
		$set_for_comment=isset(get_option('trickbd_psb_option')['set_for_comment']) ? get_option('trickbd_psb_option')['set_for_comment'] : "";
		$spam_action=isset(get_option('trickbd_psb_option')['spam_action']) ? get_option('trickbd_psb_option')['spam_action'] : "draft";
		$max_link_setting=isset(get_option('trickbd_psb_option')['max_link_setting']) ? get_option('trickbd_psb_option')['max_link_setting'] : "";
		$max_link_count=isset(get_option('trickbd_psb_option')['max_link_count']) ? get_option('trickbd_psb_option')['max_link_count'] : "2";
		?>

		<fieldset>
		<label for="trickbd_psb_set_for_post">
			<input type="checkbox" id="trickbd_psb_set_for_post" name="trickbd_psb_option[set_for_post]" value="1" <?php checked(1,$set_for_post) ?>>Filter Posts
		</label>
		<br>
		<label for="trickbd_psb_no_admin_filter">
			<input type="checkbox" id="trickbd_psb_no_admin_filter" name="trickbd_psb_option[no_admin_filter]" value="1" <?php checked(1,trickbd_psb_get_option("no_admin_filter")); ?>>No spam filter for administrator
		</label>
		<br>
		<h4>When spam found on post, what to do?</h4>
		<label for="trickbd_psb_set_action_after_spam_1">
			<input type="radio" id="trickbd_psb_set_action_after_spam_1" name="trickbd_psb_option[spam_action]" value="draft" <?php checked('draft',$spam_action) ?>> Change post status to <b>Draft</b>. Give them warrning and tell to fix.
		</label>
		<br>
		<label for="trickbd_psb_set_action_after_spam_2">
			<input type="radio" id="trickbd_psb_set_action_after_spam_2" name="trickbd_psb_option[spam_action]" value="pending" <?php checked('pending',$spam_action) ?>> Change post status to <b>Pending</b>. Simply prevent to publish.
		</label>
		<br>
		<label for="trickbd_psb_set_notify">
			<input type="checkbox" id="trickbd_psb_set_notify" name="trickbd_psb_option[no_spam_notify]" value="1" <?php checked(1,trickbd_psb_get_option('no_spam_notify')); ?>> Don't show notification about spam.
		</label>

		<h4>Filter by number of general links</h4>
		<label for="trickbd_psb_max_links">
		<input type="checkbox" name="trickbd_psb_option[max_link_setting]" id="trickbd_psb_max_links" value="1" <?php checked(1,$max_link_setting) ?>/>
		Make a post <b>Pending</b> if it contains <input type="number" class="small-text" value="<?php echo $max_link_count; ?>"  min="0" step="1" name="trickbd_psb_option[max_link_count]"> or more links. (A common characteristic of post spam is a large number of hyperlinks.)
		</label>
		<p class="description">(Count of links it will exclude white list links. And directly take action if found <b>spam keyword.</b>)</p>

		</fieldset>
		<?php
	},'trickbd_psb_op_section','trickbd_psb_op_section_id');

	/**
	 * White list links
	 */
	add_settings_field(
		'trickbd_psb_white_keywords', 
		'White Keywords', 
		function(){
			$while_keywords=isset(get_option('trickbd_psb_option')['while_keywords']) ? get_option('trickbd_psb_option')['while_keywords'] : "";
			?>
				<fieldset>
				<p><label for="trickbd_psb_white_keywords_list">Enter white links here. One word per line. It will match inside words, so “press” will match “WordPress”. </label></p>
				<p class="description"> (White listed links will never caught as spam.) </p>
				<p>
				<textarea class="large-text code" id="trickbd_psb_white_keywords_list" cols="50" rows="10" name="trickbd_psb_option[while_keywords]"><?php echo trickbd_psb_format_url_list($while_keywords); ?></textarea>
				</p> 
				</fieldset> 
			<?php
		},
		'trickbd_psb_op_section',
		'trickbd_psb_op_section_id');


	/**
	 * Spam_keywords
	 */
	add_settings_field('trickbd_psb_keywords', 'Spam keywords', function(){
		$spam_keywords=isset(get_option('trickbd_psb_option')['spam_keywords']) ? get_option('trickbd_psb_option')['spam_keywords'] : "";
		$set_sub_dir=isset(get_option('trickbd_psb_option')['set_sub_dir']) ? get_option('trickbd_psb_option')['set_sub_dir'] : "";	
		$show_suggetion=trickbd_psb_get_option("show_suggetion");
		?>
			<fieldset>
			<p><label for="trickbd_psb_keywords_list">Enter spam keywords here. One word per line. It will match inside words, so “press” will match “WordPress”. </label></p>
			<p class="description">(If any author enters any text or link that is listed on spam keywords, User will get warrning about spam detection and it will prevent user to publish post.)</p>
			<p>
			<textarea class="large-text code" id="trickbd_psb_keywords_list" cols="50" rows="10" name="trickbd_psb_option[spam_keywords]"><?php echo trickbd_psb_format_url_list($spam_keywords); ?></textarea>
			</p> 
			<label for="trickbd_psb_set_sub_dir">
			<input type="checkbox" id="trickbd_psb_set_sub_dir" name="trickbd_psb_option[set_sub_dir]" value="1" <?php checked(1,$set_sub_dir) ?>>I don't want to see sub directory of url.
			</label>			
			<br>
			<label for="psb_show_suggetion">
			<input type="checkbox" id="psb_show_suggetion" name="trickbd_psb_option[show_suggetion]" value="1" <?php checked(1,$show_suggetion) ?>>Show Suggetion.
			</label>
			</fieldset> 
			<div id="psb_suggestion">
			Suggested: 
		<?php
		load_spam_comments_url();
			echo "</div>";
	},'trickbd_psb_op_section','trickbd_psb_op_section_id');
}
endif;
// hook to admin_init
add_action('admin_init','trickbd_psb_options_init');