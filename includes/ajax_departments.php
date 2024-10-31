<?php
/*
 * WPGear. 
 * Org. Departments
 * ajax_departments.php
 */
 	
	$Mode 		= isset($_REQUEST['mode']) ? sanitize_text_field($_REQUEST['mode']) : null;	
	$Term_ID 	= isset($_REQUEST['term_id']) ? sanitize_text_field($_REQUEST['term_id']) : 0;
	$Search 	= isset($_REQUEST['s']) ? sanitize_text_field ($_REQUEST['s']) : '';
	
	$taxonomy = 'departments';
	
	$Result = false;
	
	// Get Members
	$Members_List = '';	
	if ($Mode == 'get_members') {			
		if ($Term_ID > 0) {
			$ID_Members = get_objects_in_term ($Term_ID, $taxonomy);
			
			if (empty ($ID_Members)) {
				$Members_List = 'Members Not found.';
			} else {
				$args = array(
					'include' => $ID_Members,
					'orderby' => 'display_name',
					);
				$Members = get_users($args);
				
				$Members_List = "<ul class='orgdepartments_department_members'>";	

				foreach ($Members as $Member) {
					$Member_ID 			= $Member -> ID;
					$Member_DisplayName = $Member -> data -> display_name;
					$Member_Email		= $Member -> data -> user_email;
					
					$Avatar = get_avatar ($Member_ID, 96);
					
					$Member_Meta = get_user_meta($Member_ID);
					
					$Member_FirstName 	= $Member_Meta['first_name'][0];
					$Member_LastName 	= $Member_Meta['last_name'][0];	
					
					if ($Member_LastName == '' && $Member_FirstName == '') {
						$Members_Name = "$Member_DisplayName</li>";
					} else {
						$Members_Name = "$Member_LastName $Member_FirstName</li>";
					}
					
					$Member_Card = "<div id='orgdepartments_department-" .$Term_ID ."_member_card-" .$Member_ID ."' class='orgdepartments_department_member_card' style='display: none;'>";					
						$Member_Card .= "<div class='orgdepartments_department_member_card_img'><span class='dashicons dashicons-admin-users'></span></div>";
						$Member_Card .= "<div class='orgdepartments_department_member_card_title'>$Members_Name</div>";
						$Member_Card .= "<div class='orgdepartments_department_member_card_emal'><div class ='orgdepartments_department_member_card_label'>email:</div><div class ='orgdepartments_department_member_card_value'>$Member_Email</div></div>";
					$Member_Card .= "</div>";
					
					$Members_List .= "<li title='Click for view.' onclick='orgdepartments_toggle_member_card($Term_ID, $Member_ID)'>";
						$Members_List .= "<span class='orgdepartments_department_member_avatar'>$Avatar</span><div class='orgdepartments_department_member_name'>$Members_Name $Member_Card</div>";
					$Members_List .= "</li>";
				}	

				$Members_List .= "</ul>";				
			}	
		} else {
			$Members_List = 'Members Not found.';
		}			
	
		$Result = true;	
	}
	
	// Search
	$Search_Found = array(
		'count' => 0,
	);
	
	$Members_Found = array();	
	
	if ($Mode == 'search') {
		if ($Search != '') {
			// Поиск по: Users -> 'ID', 'user_login', 'user_email', 'user_url', 'user_nicename', 'display_name'
			
			global $wpdb;	

			$Users_Table 	= $wpdb->prefix .'users';
			$Usermeta_Table = $wpdb->prefix .'usermeta';
			
			$Search_Arg = "%" .$Search ."%";
			
			$Query = "
				SELECT
					users.ID, users.display_name,
					m_last_name.meta_value AS last_name,
					m_first_name.meta_value AS first_name
				FROM $Users_Table users
				LEFT JOIN $Usermeta_Table m_last_name ON (m_last_name.user_id = users.ID AND m_last_name.meta_key = 'last_name')
				LEFT JOIN $Usermeta_Table m_first_name ON (m_first_name.user_id = users.ID AND m_first_name.meta_key = 'first_name')
				LEFT JOIN $Usermeta_Table m_members ON (m_members.user_id = users.ID AND m_members.meta_key = 'orgdepartments_confirm')
				WHERE (
						users.display_name LIKE %s OR
						users.user_email LIKE %s OR 
						m_last_name.meta_value LIKE %s OR 
						m_first_name.meta_value LIKE %s
					) AND (
						m_members.meta_value = 1
					)
				ORDER BY users.display_name ASC
			";	

			$Members = $wpdb->get_results ($wpdb->prepare ($Query, $Search_Arg, $Search_Arg, $Search_Arg, $Search_Arg));				
			
			$Members_Count = count($Members);
			$Output = "<div id='search_result_members'><ul>Found Members: $Members_Count";

			// Определяем вхождение в Подразделения
			$Mode_Show = 1; // Members	
			
			foreach ($Members as &$Member) {			
				$Member_ID 			= $Member -> ID;
				$Member_LastName 	= $Member -> last_name;
				$Member_FirstName 	= $Member -> first_name;
				$Member_DisplayName = $Member -> display_name;
				
				$Departments = array ();
				
				$Terms = wp_get_object_terms($Member_ID, $taxonomy);
				
				foreach ($Terms as $Term) {
					$Term_ID = $Term -> term_id;
					$Departments[] = $Term_ID;
				}
				
				$Member -> departments = $Departments;

				if ($Member_LastName && $Member_FirstName) {
					$Member_DisplayName = $Member_LastName .' ' .$Member_FirstName;
				}
				
				$Departments_List = implode(",", $Departments);
				$Departments_Count = count($Departments);				
				
				$Output .= "<li title='Click for view.' onclick='orgdepartments_show_search_results ($Member_ID, [" .$Departments_List ."], $Mode_Show);'>$Member_DisplayName [" .$Departments_Count ."]</li>";
			}			

			$Output .= "</ul></div>";

			// Поиск по: Departments. В Названиях	wpb_terms
			$args = array (
				'taxonomy' 			=> $taxonomy,
				'hide_empty' 		=> 0,
				'fields' 			=> 'ids',
				'name__like' 		=> $Search,
			);

			$Terms_Like_Name = get_terms ($args);				
			
			// Поиск по: Departments. В Описаниях	wpb_term_taxonomy
			$args = array (
				'taxonomy' 			=> $taxonomy,
				'hide_empty' 		=> 0,
				'fields' 			=> 'ids',
				'description__like' => $Search,
			);

			$Terms_Like_Description = get_terms ($args);		
			
			$Departments = array_merge ($Terms_Like_Name, $Terms_Like_Description);
			$Departments = array_unique ($Departments);	

			if ($Departments) {
				// Получаем ID Подразделений и их Названия.
				$args = array (
					'taxonomy' 		=> $taxonomy,
					'hide_empty' 	=> 0,
					'fields' 		=> 'id=>name',
					'include' 		=> $Departments,
					'orderby'		=> 'name',
				);

				$Departments = get_terms ($args);	
			}			
			
			$Departments_Count = count($Departments);
			$Mode_Show = 2; // Departments

			$Output .= "<div id='search_result_deparments'><ul>Found Departments: $Departments_Count";
			foreach ($Departments as $Term_ID => $Term_Name) {
				$Output .= "<li title='Click for view.' onclick='orgdepartments_show_search_results (0, $Term_ID, $Mode_Show);'>$Term_Name</li>";
			}
			$Output .= "</ul></div>";
	
			$Search_Found['count_members'] = $Members_Count;
			$Search_Found['count_departments'] = $Departments_Count;
			$Search_Found['output'] = $Output;			
		}
		
		$Result = true;
	}	
	
	$Obj_Request = new stdClass();
	$Obj_Request->status 	= 'OK';
	$Obj_Request->answer 	= $Result;
	$Obj_Request->memberss 	= $Members_List;
	$Obj_Request->found		= $Search_Found;

	wp_send_json($Obj_Request);    

	die; // Complete.