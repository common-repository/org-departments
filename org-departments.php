<?php
/*
Plugin Name: Org. Departments
Plugin URI: wpgear.xyz/org-departments
Description: Hierarchical Structure of Departments. For each User, you can set a binding to several Departments.
Version: 4.9
Author: WPGear
Author URI: http://wpgear.xyz
License: GPLv2
*/

	$OrgDepartments_plugin_url = plugin_dir_url( __FILE__);		// со слэшем на конце
	$OrgDepartments_plugin_dir = plugin_dir_path( __FILE__);	// со слэшем на конце
	
	$OrgDepartments_Post_Type = "departments";

	include_once(__DIR__ .'/includes/functions.php');
	include_once(__DIR__ .'/includes/shortcodes.php');
	
	$OrgDepartments_Options = OrgDepartments_Get_Options();

	/* Регистрируем Taxonomy "departments" для Users.
	----------------------------------------------------------------- */		
	function OrgDepartments_taxonomy(){
		$taxonomy = 'departments';

		$labels = array(
			'name'							=> 'Departments',
			'singular_name'					=> 'Department',
			'search_items'					=> 'Search',
			'popular_items'					=> 'Popular Departments',
			'all_items'						=> 'All Departments',
			'view_item '					=> 'View',
			'parent_item'					=> null,
			'parent_item_colon'				=> null,
			'edit_item'						=> 'Edit',
			'update_item'					=> 'Update',
			'add_new_item'					=> 'Add New',
			'new_item_name'					=> 'New',
			'separate_items_with_commas'	=> 'Separate writers with commas',
			'add_or_remove_items'			=> 'Add or Remove',
			'choose_from_most_used'			=> 'Choose from the most used writers',
			'menu_name'						=> 'Departments',
			'not_found'						=> 'No Departments found.',
			'no_terms'                   	=> 'No departments',
			'items_list'                 	=> 'Departments list',
			'items_list_navigation'      	=> 'Departments list navigation',
		);	

		$args = array(
			'label'  				=> null,
			'labels'            	=> $labels,
			'description'           => '',
			'public'                => true,
			'publicly_queryable'    => false,
			'show_in_nav_menus'     => false,
			'hierarchical'      	=> true,	
			'show_ui'           	=> true,	// равен аргументу public
			// 'show_in_menu'		=> true, 	// равен аргументу show_ui
			// 'show_in_quick_edit'	=> null, 	// равен аргументу show_ui
			'show_tagcloud'         => true,
			'show_admin_column' 	=> true,	// авто-создание колонки таксы в таблице ассоциированного типа записи.
			'query_var'         	=> true,	// название параметра запроса. array ('slug' => 'genre'),
			'meta_box_cb'           => null, 	// html метабокса. callback: 'post_categories_meta_box' или 'post_tags_meta_box'. false — метабокс отключен. null - auto by hierarchical
			'rewrite'               => true,
			'capabilities'          => array(),
			'show_in_rest'          => null, 	// добавить в REST API
			'rest_base'             => null, 	// $taxonomy
			// '_builtin'			=> false,
			//'update_count_callback' => '_update_post_term_count',	
		);
		
		register_taxonomy ($taxonomy, 'user', $args);
	}	
	add_action ('init', 'OrgDepartments_taxonomy');
	
	/* Enqueue Styles for Pages with ShortCode [org.departments]
	----------------------------------------------------------------- */	
	function OrgDepartments_enqueue_scripts() {
		global $OrgDepartments_plugin_url;	
		global $post;

		if (has_shortcode($post->post_content, 'org.departments')) {
			wp_enqueue_style ('orgdepartments', $OrgDepartments_plugin_url .'style.css');
			wp_enqueue_style ('fontawesome_4.7.0', "https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css");		
			
			wp_enqueue_script ('orgdepartments_frontend_script', $OrgDepartments_plugin_url .'includes/departments_frontend.js');
			
			// Формируем Ветви Дочерних Элементов.
			$taxonomy = 'departments';
			$Terms_Hierarchy = OrgDepartments_Get_Departments_Hierarchy_Structure ($taxonomy);

			// Передаем Параметры в Скрипт.
			$Params = array (
				'sub_tree' => $Terms_Hierarchy,	
				'ajaxurl' => admin_url('admin-ajax.php'),
			);
			
			wp_localize_script('orgdepartments_frontend_script', 'orgdepartments_frontend_script_params', $Params);			
		}
	}
	add_action ('wp_enqueue_scripts', 'OrgDepartments_enqueue_scripts');
	
	/* AJAX Processing
	----------------------------------------------------------------- */
    function OrgDepartments_Ajax(){		
		include_once ('includes/ajax_departments.php');
    }	
    add_action( 'wp_ajax_orgdepartments', 'OrgDepartments_Ajax' );
	add_action( 'wp_ajax_nopriv_orgdepartments', 'OrgDepartments_Ajax' );	
	
	/* Действия, после Удаления Пользователя
	----------------------------------------------------------------- */
	add_action ('delete_user', 'OrgDepartments_Action_After_Delete_User');
	function OrgDepartments_Action_After_Delete_User ($User_ID) {
		$taxonomy = 'departments';	
		
		$args = array(
			'taxonomy' => $taxonomy,
			'fields' => 'ids',
			'hide_empty' => false,
		);		

		$terms = get_terms ($args);
		
		$Result_Remove_Object_Terms = wp_remove_object_terms ($User_ID, $terms, $taxonomy);
	}	
	
	include_once(__DIR__ .'/includes/admin/admin.php');