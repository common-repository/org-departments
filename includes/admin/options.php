<?php
/*
 * WPGear. 
 * Org. Departments
 * options.php
 */
	
	$OrgDepartments_Action 			= isset($_REQUEST['action']) ? sanitize_text_field ($_REQUEST['action']) : null;
	$OrgDepartments_Setup_AdminOnly = isset($_REQUEST['orgdepartments_option_adminonly']) ? 1 : 0;	
	$OrgDepartments_SortBy 			= isset($_REQUEST['orgdepartments_option_sortby']) ? sanitize_text_field ($_REQUEST['orgdepartments_option_sortby']) : null;
	$OrgDepartments_Users_Column 	= isset($_REQUEST['orgdepartments_option_users_column']) ? 1 : 0;	
		
	if ($OrgDepartments_Action == 'Update') {
		$options = array (
			'adminonly' 	=> $OrgDepartments_Setup_AdminOnly,
			'sortby' 		=> $OrgDepartments_SortBy,
			'users_column' 	=> $OrgDepartments_Users_Column,
		);
		
		update_option('orgdepartments_options', $options);
	} 
	
	$OrgDepartments_Options = OrgDepartments_Get_Options();
	
	if ($OrgDepartments_Options['adminonly']) {
		if (!current_user_can('edit_dashboard')) {
			?>
			<div class="orgdepartments_warning" style="margin: 40px;">
				Sorry, you are not allowed to view this page.
			</div>
			<?php
			
			return;
		}		
	}
	
	?>
	<div class="wrap">
		<h2>Org. Departments</h2>
		<hr>
		
		<div class="orgdepartments_options_box">
			<form name="form_OrgDepartments_Options" method="post" style="margin-top: 20px;">
				<div style="margin-top: 10px;">
					<label for="orgdepartments_option_adminonly" title="On/Off">
						Enable this Page for Admin only
					</label>
					<input id="orgdepartments_option_adminonly" name="orgdepartments_option_adminonly" type="checkbox" <?php if($OrgDepartments_Options['adminonly']) {echo 'checked';} ?>>
				</div>	

				<div style="margin-top: 10px; margin-left: 24px;">
					<label for="orgdepartments_option_users_column" title="On/Off">
						Show "Department" Column
					</label>
					<input id="orgdepartments_option_users_column" name="orgdepartments_option_users_column" type="checkbox" <?php if($OrgDepartments_Options['users_column']) {echo 'checked';} ?>>
					<span style="color: grey;">(Admin -> Users List)</span>
				</div>					

				<div style="margin-top: 10px; margin-left: 38px;">
					Sort Departments Tree by:
					<div style="margin-left: 152px;">
						<div>
							<input id="orgdepartments_option_sortby_name" name="orgdepartments_option_sortby" type="radio" value="name" <?php if($OrgDepartments_Options['sortby'] == 'name') {echo 'checked';} ?>>
							<label for="orgdepartments_option_sortby_name">Name</label>
						</div>
						<div style="margin-top: 5px;">
							<input id="orgdepartments_option_sortby_id" name="orgdepartments_option_sortby" type="radio" value="term_id" <?php if($OrgDepartments_Options['sortby'] == 'id') {echo 'checked';} ?>>
							<label for="orgdepartments_option_sortby_id">ID (term_id)</label>
						</div>					
					</div>
				</div>				

				<hr>
				<div style="margin-top: 10px; margin-bottom: 5px; text-align: right;">
					<input id="orgdepartments_btn_options_save" type="submit" class="button button-primary" style="margin-right: 5px;" value="Save">
				</div>
				<input id="action" name="action" type="hidden" value="Update">
			</form>
		</div>	
	</div>
