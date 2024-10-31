<?php
/*
 * WPGear. 
 * Org. Departments
 * admin.php
 */
	/* Menu.
	----------------------------------------------------------------- */	
	function OrgDepartments_create_menu() {	
		// Users. Create Sub "Departments"
		$tax = get_taxonomy ('departments');

		add_users_page(
			esc_attr( $tax->labels->menu_name ),
			esc_attr( $tax->labels->menu_name ),
			$tax->cap->manage_terms,
			'edit-tags.php?taxonomy=' .$tax->name
		);	
		
		// Settings. Create Sub "Departments"
		add_options_page(
			'Org. Departments',
			'Org. Departments',
			'publish_posts',
			'org-departments/includes/admin/options.php',
			''
		);		
	}
	add_action ('admin_menu', 'OrgDepartments_create_menu');	

	/* Styles for Admin Console.
	----------------------------------------------------------------- */	
	function OrgDepartments_admin_style ($hook) {
		global $OrgDepartments_plugin_url;
		global $OrgDepartments_Post_Type;
		
		$Screen = get_current_screen();
		
		$Screen_Base 		= $Screen->base;
		$Screen_ID 			= $Screen->id;
		$Screen_PostType 	= $Screen->post_type;	

		if ($Screen_PostType == $OrgDepartments_Post_Type || $Screen_ID == 'edit-departments' || $Screen_ID == 'org-departments/includes/admin/options') {
			wp_enqueue_style ('orgdepartments_admin', $OrgDepartments_plugin_url .'includes/admin/style.css');
		}
	}
	add_action ('admin_enqueue_scripts', 'OrgDepartments_admin_style');
	
	/* Menu. Users. Делаем Активным для Таксономии "Departments"
	----------------------------------------------------------------- */	
	function OrgDepartments_change_parent_file ($parent_file) {
		global $submenu_file;
		
		if (isset($_GET['taxonomy']) && $_GET['taxonomy'] == 'departments' && $submenu_file == 'edit-tags.php?taxonomy=departments') {
			$parent_file = 'users.php';
		}
		
		return $parent_file;
	}
	add_filter('parent_file', 'OrgDepartments_change_parent_file');	

	/* Таксономия "Departments". Удаляем колонку "Count", Добавляем колонку "Users"
	----------------------------------------------------------------- */	
	function OrgDepartments_manage_departments_user_column ($columns) {
		unset ($columns['posts']);
		
		$columns['users'] = 'Users';
		
		return $columns;
	}
	add_filter ('manage_edit-departments_columns', 'OrgDepartments_manage_departments_user_column');	
	
	/* Таксономия "Departments". Users Column Count.
	----------------------------------------------------------------- */		
	function OrgDepartments_manage_departments_column ($display, $column, $term_id) {
		if ($column == 'users') {
			$term = get_term ($term_id, 'departments');
			
			echo $term->count;
		}
	}
	add_filter ('manage_departments_custom_column', 'OrgDepartments_manage_departments_column', 10, 3 );
	
	/* Таксономия "Departments". Add New. Добавляем МетаПоле "CSS Class"	
	----------------------------------------------------------------- */
	function OrgDepartments_add_term_fields ($taxonomy) {		
		echo '<div class="form-field">
		<label for="department_css_class">CSS Class</label>
		<input type="text" name="department_css_class" id="department_css_class" />
		<p>Extended for Special MarkUp.</p>
		</div>';
	}
	add_action ('departments_add_form_fields', 'OrgDepartments_add_term_fields');	
	
	/* Таксономия "Departments". Edit. Добавляем МетаПоле "CSS Class"
	----------------------------------------------------------------- */	
	function OrgDepartments_edit_term_fields ($term, $taxonomy) {
		$value = get_term_meta ($term->term_id, 'department_css_class', true );
		
		echo '<tr class="form-field">
		<th>
			<label for="department_css_class">CSS Class</label>
		</th>
		<td>
			<input name="department_css_class" id="department_css_class" type="text" value="' . esc_attr($value) .'" />
			<p class="description">Extended for Special MarkUp.</p>
		</td>
		</tr>';
	}
	add_action ('departments_edit_form_fields', 'OrgDepartments_edit_term_fields', 10, 2);	
	
	/* Таксономия "Departments". Save МетаПоле "CSS Class"
	----------------------------------------------------------------- */		
	function OrgDepartments_save_term_fields ($term_id) {
		if (isset($_POST['department_css_class'])) {		
			update_term_meta(
				$term_id,
				'department_css_class',
				sanitize_text_field ($_POST['department_css_class'])
			);
		}
	}
	add_action ('created_departments', 'OrgDepartments_save_term_fields');
	add_action ('edited_departments', 'OrgDepartments_save_term_fields');	
	
	/* Users. Добавляем новые колонки.
	----------------------------------------------------------------- */			
	function OrgDepartments_columns ($column) {
		global $OrgDepartments_Options;
		
		if ($OrgDepartments_Options['users_column']) {
			$column['department'] = 'Department';
		}
		
		return $column;
	}
	add_filter ('manage_users_columns', 'OrgDepartments_columns');	
	
	/* Users. Users. Формируем новые колонки "Department".
	----------------------------------------------------------------- */	
	function OrgDepartments_user_column ($output, $column_name, $user_id) {
		global $OrgDepartments_Options;
				
		if ($OrgDepartments_Options['users_column']) {
			
			if ($column_name == 'department') {
				$Departments_List = '';
				
				$Departments = OrgDepartments_Get_User_Departments_List ($user_id);
				
				if ($Departments) {
					foreach ($Departments as $Department_ID => $Department_Name) {
						$Departments_List .= "<div>$Department_Name</div>";
					}
				} else {
					$Departments_List = "N/A";
				}						
				
				$output = $Departments_List;
			}
		}
		
		return $output;
	}	
	add_filter ('manage_users_custom_column', 'OrgDepartments_user_column', 10, 3);
	
	/* Users. Делаем колонки "Department" сортируемыми.	
	----------------------------------------------------------------- */		
	function OrgDepartments_user_column_sortable ($sortable_columns) {
		// $sortable_columns['department'] = 'department';
		
		return $sortable_columns;
	}	
	add_filter ('manage_users_sortable_columns', 'OrgDepartments_user_column_sortable');

	/* Users. Делаем сортировку колонок "Department".
	----------------------------------------------------------------- */	
	add_filter ('pre_user_query', 'OrgDepartments_user_column_orderby');
	function OrgDepartments_user_column_orderby ($user_query) {
		// global $current_screen;
		
		// if ($current_screen -> id == 'users') {
			// $vars = $user_query -> query_vars;
			
			// if ($vars['orderby'] == 'department') {
				
			// }			
		// }
		
		return $user_query;
	}
	
	/* Profile. Добавляем новые поля.
	----------------------------------------------------------------- */		
	add_action ('edit_user_profile', 'OrgDepartments_show_extra_profile_fields', 999);
	add_action ('user_new_form', 'OrgDepartments_show_extra_profile_fields');
	// add_action('show_user_profile', 'OrgDepartments_show_extra_profile_fields');	// Пользователь не может сам Изменять Служебные Поля.
	function OrgDepartments_show_extra_profile_fields ($user) {
		$User_ID = $user->ID;

		$meta_key = 'orgdepartments_confirm';
		$OrgDepartments_Confirm = get_user_meta ($User_ID, $meta_key, true);
		
		$Departments = OrgDepartments_Get_Departments ();
		
		$taxonomy = 'departments';		
		?>
		<hr>
		<h3>Org. Info</h3>
		<table class="form-table">
			<tbody>
				<tr id="box_orgdepartments_confirm">
					<th><label for="orgdepartments_confirm">Crew Member</label></th>
					<td>
						<input name="orgdepartments_confirm" type="checkbox" id="orgdepartments_confirm" <?php if($OrgDepartments_Confirm) {echo "checked";} ?>></label>
					</td>
				</tr>
				
				<tr>
					<th>Deparments</th>
					<td>
						<?php 
						if (empty($Departments)) {
							echo 'No Departments ...';
						} else {
							foreach ($Departments as $term) {				
								$Term_ID 	= $term -> term_id;
								$Term_Count = $term -> count;
								$Term_Level	= $term -> level;
								$Term_Slug 	= $term -> slug;
								$Term_Name 	= $term -> name;
								
								$Department_CSS_Class = get_term_meta ($Term_ID, 'department_css_class', true);
								
								$esc_Term_Slug 				= esc_attr($Term_Slug);
								$esc_Department_CSS_Class 	= esc_attr($Department_CSS_Class);	
								$esc_Term_Level 			= esc_attr(24*$Term_Level);								
								
								// Выводим контент в виде Дерева.
								if ($User_ID) {							
									// Редактирование Пользователя				
									?>
									<label for="departments-<?php echo $esc_Term_Slug; ?>" class="<?php echo $esc_Department_CSS_Class; ?>" style="margin-left: <?php echo $esc_Term_Level; ?>px;">
										<input type="checkbox" name="departments[]" id="departments-<?php echo $esc_Term_Slug; ?>" value="<?php echo $esc_Term_Slug; ?>" <?php checked(true, is_object_in_term($User_ID, $taxonomy, $Term_Slug)); ?>><?php echo esc_attr($Term_Name ." [$Term_Count]");?>
									</label>
									<br/>
									<?php							
								} else {
									// Регистрация Нового Пользователя
									?>
									<label for="departments-<?php echo $esc_Term_Slug; ?>" class="<?php echo $esc_Department_CSS_Class; ?>" style="margin-left: <?php echo $esc_Term_Level; ?>px;">
										<input type="checkbox" name="departments[]" id="departments-<?php echo $esc_Term_Slug; ?>" value="<?php echo $esc_Term_Slug;?>"><?php echo esc_attr($Term_Name ." [$Term_Count]");?>
									</label>
									<br/>
									<?php					
								}
							}
						}
						?>
					</td>
				</tr>	
			</tbody>			
		</table>
		<hr>
		<?php				
	}	
	
	// Профиль. Сохранение новых полей.
	add_action ('edit_user_profile_update', 'OrgDepartments_save_extra_profile_fields');
	// add_action ('personal_options_update', 'OrgDepartments_save_extra_profile_fields');
	function OrgDepartments_save_extra_profile_fields($User_ID) {
		if (!current_user_can('edit_user', $User_ID)) {
			return false;
		}		
		
		// Crew Member
		$meta_key = 'orgdepartments_confirm';
		$OrgDepartments_Confirm = isset($_POST[$meta_key]) ? '1' : '0';
		$OrgDepartments_Confirm_Last = get_user_meta ($User_ID, $meta_key, true);
		
		if ($OrgDepartments_Confirm != $OrgDepartments_Confirm_Last) {
			update_user_meta ($User_ID, $meta_key, $OrgDepartments_Confirm);			
		}		

		$taxonomy = 'departments';

		$tax = get_taxonomy ($taxonomy);		
		
		$terms = isset ($_POST['departments']) ? array_map('esc_attr', (array) $_POST['departments']) : (int) $term;		
		
		wp_set_object_terms ($User_ID, $terms, $taxonomy, false);
		clean_object_term_cache ($User_ID, $taxonomy);
	}		
?>