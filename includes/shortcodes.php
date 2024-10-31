<?php
/*
 * WPGear. 
 * Org. Departments
 * shortcodes.php
 */

	/* ShortCode [org.departments] Подразделения.
	----------------------------------------------------------------- */	
	add_shortcode ('org.departments', 'ShortCode_OrgDepartments');
	function ShortCode_OrgDepartments ($atts, $shortcode_content = null){
		$Departments = OrgDepartments_Get_Departments ();
		
		echo "<div id='orgdepartments_departments_box' class='orgdepartments_departments_box'>";
		
			if ($atts['search'] == true) {
				// Поле "Поиск"
				echo "<div id='orgdepartments_search_box' class='orgdepartments_search_box'>";
					echo "<div class='orgdepartments_search_input'>";
						echo "<input id='orgdepartments_search' type='text'></input>";
						echo "<input id='orgdepartments_search_btn' onclick='orgdepartments_search ();' type='button' class='orgdepartments_btn_search button button-primary' style='margin-left: 10px;' value='Search'>";
					echo "</div>";	
					echo "<div id='orgdepartments_search_result_info' class='orgdepartments_search_result_info'></div>";
					echo "<div id='orgdepartments_search_processing' class='orgdepartments_search_processing' style='display: none;'><i class='fa fa-refresh fa-spin fa-fw fa-2x' aria-hidden='true' style='vertical-align: baseline;'></i></div>";
				echo "</div>";
			}		
		
			echo "<div id='orgdepartments_departments_tree_box' class='orgdepartments_departments_tree_box'>";
		
			if (empty($Departments)) {
				echo 'No Departments ...';
			} else {
				foreach ($Departments as $term) {
					$Term_ID 			= $term -> term_id;
					$Term_Count 		= $term -> count;
					$Term_Level			= $term -> level;
					$Term_Slug 			= $term -> slug;
					$Term_Name 			= $term -> name;
					$Term_Description 	= $term -> description;
					
					$Department_CSS_Class = get_term_meta ($Term_ID, 'department_css_class', true);
					
					$esc_Term_ID 				= esc_attr($Term_ID);
					$esc_Term_Slug 				= esc_attr($Term_Slug);
					$esc_Term_Name 				= esc_attr($Term_Name);
					$esc_Department_CSS_Class 	= esc_attr($Department_CSS_Class);
					$esc_Term_Level 			= esc_attr(24 * $Term_Level);
					$esc_Term_Count 			= esc_attr($Term_Count);
					$esc_Term_Description		= esc_attr($Term_Description);
					
					// Department Item
					echo "<div id='orgdepartments_department-" .$esc_Term_ID ."' class='orgdepartments_department_item" .$esc_Department_CSS_Class ."' style='margin-left: " .$esc_Term_Level ."px;' title='Click for Show/Hide.' onclick='orgdepartments_toggle_department_box ($esc_Term_ID, $esc_Term_Count, 0)'  onmouseover='orgdepartments_show_departments_subtree ($esc_Term_ID)'>";
						echo $esc_Term_Name ."[$esc_Term_Count]";
					echo "</div>";

					// Department Description
					echo "<div id='orgdepartments_department_description-" .$esc_Term_ID ."' style='display: none; margin-left: " .$esc_Term_Level ."px;' class='orgdepartments_department_description'>";
						echo "<div>";
							if ($Term_Description) {
								// echo $esc_Term_Description; // Hm... WP Security! Description - is HTML Content.
								echo $Term_Description;
								echo "<hr>";
							}
						echo "</div>";
						
						// Department Members
						echo "<div class='orgdepartments_department_members_box'>";
							echo "<h3>Members:</h3>";
							
							echo "<div id='orgdepartments_members_box_processing_department-" .$esc_Term_ID ."' style='display: none; margin-left: 10px;'>";
								echo "<i class='fa fa-refresh fa-spin fa-fw fa-2x' aria-hidden='true' style='vertical-align: baseline;'></i>";
							echo "</div>";
						
							echo "<div id='orgdepartments_members_box_department-" .$esc_Term_ID ."'></div>";
						echo "</div>";					
					echo "</div>";
				}
			}							
		
			echo "</div>";
		echo "</div>";
		
		return do_shortcode($shortcode_content);
	}