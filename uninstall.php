<?php
/*
 * WPGear. 
 * Org. Departments
 * uninstall.php
 */

	// if uninstall.php is not called by WordPress, die
	if (!defined('WP_UNINSTALL_PLUGIN')) {
		die;
	}
	
	// Remove Plugin Options
	delete_option('orgdepartments_options');
