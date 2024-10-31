<?php
/*
 * WPGear. 
 * Org. Departments
 * functions.php
 */

	/* Get Options
	----------------------------------------------------------------- */
	function OrgDepartments_Get_Options () {
		$OrgDepartments_Options = get_option("orgdepartments_options", array(
			'adminonly' => 1,
			'sortby' => 'name',
			'users_column' => 1,
			)
		);

		return $OrgDepartments_Options;
	}
	
	/* Get All Departments.
	----------------------------------------------------------------- */
	function OrgDepartments_Get_Departments () {
		global $OrgDepartments_Options;
		
		$taxonomy = 'departments';

		$args = array (
			'taxonomy' => $taxonomy,
			'page' => 1,
			'number' => 0,
			'search' => '',
			'hide_empty' => 0,
			'offset' => 0,
			'orderby' => $OrgDepartments_Options['sortby'],
		);		
		$terms = get_terms ($args);
		
		$Terms_Hierarchy = OrgDepartments_Get_Departments_Hierarchy_Structure ($taxonomy);
		
		$start = 0;
		$end = wp_count_terms($taxonomy);
		$count = 0;
		$parent = 0;
		$level = 0;
		
		$terms = OrgDepartments_Get_Departments_Tree ($taxonomy, $terms, $Terms_Hierarchy, $start, $end, $count, $parent, $level);	
		
		return $terms;
	}	
		
	/* Get Departments Hierarchy Structure
	----------------------------------------------------------------- */	
	function OrgDepartments_Get_Departments_Hierarchy_Structure ($taxonomy) {
		$children = array();
		
		$args = array (
			'taxonomy'               	=> $taxonomy,
			'get'                    	=> 'all',
			'fields'                 	=> 'id=>parent',
			'orderby'					=> 'none',	
		);

		$terms = get_terms ($args);			
		
		foreach ($terms as $term_id => $parent) {
			if ($parent > 0) {
				$children[$parent][] = $term_id;
			}
		}
		
		return $children;
	}
	
	/* Get Departments Tree with Levels
	----------------------------------------------------------------- */		
	function OrgDepartments_Get_Departments_Tree ($taxonomy, $terms, &$children, $start, $end, &$count, $parent = 0, $level = 0, &$result = array()) {	
		foreach ( $terms as $key => $term ) {
			if ($count >= $end) {
				break;
			}

			if ($term->parent != $parent){
				continue;
			}

			if ($count == $start && $term->parent > 0) {
				$my_parents = $parent_ids = array();
				$p = $term->parent;

				while ($p) {
					$my_parent = get_term ($p, $taxonomy);
					$my_parents[] = $my_parent;
					$p = $my_parent->parent;
					
					if (in_array ($p, $parent_ids))
						break;
					$parent_ids[] = $p;
				}
				unset ($parent_ids);

				$num_parents = count ($my_parents);
				while ($my_parent = array_pop ($my_parents)) {
					$my_parent->level = $level;
					$result[] = $my_parent;
				}
			}

			if ($count >= $start) {
				$term->level = $level;
				$result[] = $term;
			}

			$count = $count + 1;
			
			unset ($terms[$key]);

			if (isset ($children[$term->term_id])) {
				OrgDepartments_Get_Departments_Tree ($taxonomy, $terms, $children, $start, $end, $count, $term->term_id, $level + 1, $result);
			}
		}
		
		return $result;
	}	
	
	/* Get User Departments.
	----------------------------------------------------------------- */
	function OrgDepartments_Get_User_Departments_List ($user_id) {
		$Departments = array();
		
		$taxonomy = 'departments';

		$Terms = wp_get_object_terms ($user_id, $taxonomy);
		
		if ($Terms) {
			foreach ($Terms as $Term) {
				$Term_ID 	= $Term -> term_id;
				$Term_Name 	= $Term -> name;
				// $Term_Slug 	= $Term -> slug;
				
				$Departments[$Term_ID] = $Term_Name;
			}
		}		
		
		return $Departments;
	}