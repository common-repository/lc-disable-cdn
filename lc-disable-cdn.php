<?php
/*
Plugin Name: LC Disable CDN
Plugin URI: https://wordpress.org/plugins/lc-disable-cdn
Version: 1.0.3
Description: Disable CDN rewriting for the Live Composer Editor
Author: James Low
Author URI: http://jameslow.com
*/

class LC_Disable_CDN {
	public static function add_hooks() {
		add_action('init', array('LC_Disable_CDN', 'disable_cdn'));
	}
	public static function disable_cdn($requested_url = null, $do_redirect = true) {
		if (self::is_editing()) {
			/* [Kinsta\CDN_Enabler::handle_rewrite_hook] => Array
			(
				[function] => Array
					(
						[0] => Kinsta\CDN_Enabler
						[1] => handle_rewrite_hook
					)
				[accepted_args] => 1
			)*/
			self::remove_hook_by_name('template_redirect', 'handle_rewrite_hook');
		}
	}
	public static function is_editing() {
		global $dslc_active;
		//isset($_REQUEST['module_id'])
		return ($dslc_active && is_user_logged_in() && current_user_can(DS_LIVE_COMPOSER_CAPABILITY)) || strpos($_SERVER['REQUEST_URI'], 'widgets.php') !== false;
	}
	public static function remove_hook_by_name($hook, $search, $removepriority = 10) {
		global $wp_filter;
		foreach ($wp_filter as $name => $hookobject) {
			if ($name == $hook) {
				$priorities = $hookobject->callbacks;
				foreach ($priorities as $priority => $callbacks) {
					foreach ($callbacks as $id => $functionobject) {
						$function = $functionobject['function'];
						$string = print_r($function, true);
						if(strpos($string, $search) !== false) {
							if (remove_filter($hook, $id, $removepriority)) {
								//Removed
								return true;
							}
						}
					}
				}
			}
		}
	}
}
LC_Disable_CDN::add_hooks();