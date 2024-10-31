// WPGear. Org. Departments
// departments_frontend.js

	window.addEventListener ('load', function() {
		console.log('org_departments.js - is Loaded.');
	});

	var OrgDepartments_Sub_Trees 	= orgdepartments_frontend_script_params['sub_tree'];
	var OrgDepartments_Ajax_URL 	= orgdepartments_frontend_script_params['ajaxurl'];	
	
	var Sub_Tree_Node_Selected = 0;
	
	// Выделяем Ветвь
	function orgdepartments_show_departments_subtree (Term_ID) {
		if (Sub_Tree_Node_Selected > 0) {
			orgdepartments_hide_departments_subtree (Sub_Tree_Node_Selected);			
		}
		
		Sub_Tree_Node_Selected = Term_ID;		
		
		var Sub_Tree = OrgDepartments_Sub_Trees[Term_ID];
		var Sub_Tree_Node = 0;
		
		if (typeof Sub_Tree != 'undefined') {
			document.getElementById('orgdepartments_department-' + Term_ID).classList.add('orgdepartments_departments_item_selected');
			
			for (var Node = 0; Node < Sub_Tree.length; Node ++){
				Sub_Tree_Node = Sub_Tree[Node];
				
				document.getElementById('orgdepartments_department-' + Sub_Tree_Node).classList.add('orgdepartments_departments_subtree_selected');
			}
		}			
	}
	
	// Скрываем выделенную Ветвь
	function orgdepartments_hide_departments_subtree (Term_ID) {
		var Sub_Tree = OrgDepartments_Sub_Trees[Term_ID];
		var Sub_Tree_Node = 0;
		
		if (typeof Sub_Tree != 'undefined') {
			document.getElementById('orgdepartments_department-' + Term_ID).classList.remove('orgdepartments_departments_item_selected');
			
			for (var Node = 0; Node < Sub_Tree.length; Node ++){
				Sub_Tree_Node = Sub_Tree[Node];
				
				document.getElementById('orgdepartments_department-' + Sub_Tree_Node).classList.remove('orgdepartments_departments_subtree_selected');
			}
		}

		Sub_Tree_Node_Selected = 0;
	}	
	
	// Показываем / Скрываем Подразделение
	function orgdepartments_toggle_department_box (Term_ID, Count_Users, Member_ID) {
		document.getElementById('orgdepartments_department-' + Term_ID).classList.toggle('orgdepartments_departments_item_opened');
		
		var Department_Box = document.getElementById('orgdepartments_department_description-' + Term_ID);
		
		if (Department_Box.style.display === 'none') {
			Department_Box.style.display = 'block';	
			
			if (Count_Users != 0) {
				// Получаем Список Сотрудников.
				
				document.getElementById('orgdepartments_members_box_processing_department-' + Term_ID).style.display = "inline-block";
				document.getElementById('orgdepartments_members_box_department-' + Term_ID).innerHTML = '';
				
				var OrgDepartments_Ajax_Data 	= 'action=orgdepartments&mode=get_members&term_id=' + Term_ID;
			
				jQuery.ajax({
					type:"POST",
					url: OrgDepartments_Ajax_URL,
					dataType: 'json',
					data: OrgDepartments_Ajax_Data,
					cache: false,
					success: function(jsondata) {
						var Obj_Request = jsondata;	
						
						var Status	= Obj_Request.status;
						var Answer 	= Obj_Request.answer;					
						var Members	= Obj_Request.memberss;	
						
						if (Answer) {							
							document.getElementById('orgdepartments_members_box_processing_department-' + Term_ID).style.display = "none";
							document.getElementById('orgdepartments_members_box_department-' + Term_ID).innerHTML = Members;

							if (Count_Users == -1 && Member_ID > 0) {
								// Открываем Карточку с Найденным Сотрудником
								orgdepartments_toggle_member_card (Term_ID, Member_ID);

								// Позиционируем на Member
								var Member_Card = document.getElementById('orgdepartments_department-' + Term_ID + '_member_card-' + Member_ID);
								Member_Card.scrollIntoView (true);
								
								var Scrolled_Y = window.scrollY;
								if (Scrolled_Y) {
									window.scroll(0, Scrolled_Y - 400);
								}								
							}
							
							if (Count_Users == -1 && Member_ID == 0) {
								// Позиционируем на Department
								var Department = document.getElementById('orgdepartments_department-' + Term_ID);
								Department.scrollIntoView (true);

								var Scrolled_Y = window.scrollY;
								if (Scrolled_Y) {
									window.scroll(0, Scrolled_Y - 400);
								}								
							}							
						}
					},
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
						console.log(XMLHttpRequest);
						console.log(textStatus);
						console.log(errorThrown);
                    }				
				});
				
			} else {
				// Сотрудников Нет.
				document.getElementById('orgdepartments_members_box_department-' + Term_ID).innerHTML = "<ul class='orgdepartments_department_members'><li>Members Not found.</li></ul>";				
			}				
		} else {
			Department_Box.style.display = 'none';
		}				
	}
	
	// Показываем / Скрываем Карточку Сотрудника
	function orgdepartments_toggle_member_card (Term_ID, Member_ID) {
		var Member_Card = document.getElementById('orgdepartments_department-' + Term_ID + '_member_card-' + Member_ID);
		
		if (Member_Card.style.display === 'none') {
			Member_Card.style.display = 'inline-block';
		} else {
			Member_Card.style.display = 'none';
		}			
	}
	
	// Поиск по Сотрудникам
	function orgdepartments_search () {		
		var Search = document.getElementById('orgdepartments_search').value;
		
		if (Search != '') {
			document.getElementById('orgdepartments_search_processing').style.display = "block";			
			document.getElementById('orgdepartments_search_result_info').innerHTML = '';
			
			var OrgDepartments_Ajax_Data = 'action=orgdepartments&mode=search&s=' + Search;
			
			jQuery.ajax({
				type:"POST",
				url: OrgDepartments_Ajax_URL,
				dataType: 'json',
				data: OrgDepartments_Ajax_Data,
				cache: false,
				success: function(jsondata) {
					var Obj_Request = jsondata;	
							
					var Status 		= Obj_Request.status;
					var Answer 		= Obj_Request.answer;
					var Found		= Obj_Request.found;	
					
					console.log(Obj_Request);
				
					if (Answer) {							
						document.getElementById('orgdepartments_search_processing').style.display = "none";			
						document.getElementById('orgdepartments_search_result_info').innerHTML = Found.output;
					}
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					console.log(XMLHttpRequest);
					console.log(textStatus);
					console.log(errorThrown);
				}
			});		
		} else {
			document.getElementById('orgdepartments_search_processing').style.display = "none";			
			document.getElementById('orgdepartments_search_result_info').innerHTML = 'Search criteria not specified.';			
		}		
	}

	// Результаты Поиска в Структуре Дерева.
	function orgdepartments_show_search_results (Member_ID, Terms_ID, Mode_Show) {
		var Length = 0;
	
		// Закрываем все открытые Ветви
		var OpenedDepartments = document.getElementsByClassName ("orgdepartments_departments_item_opened");
		Length = OpenedDepartments.length;
		
		for (i = 0; i < Length; i++) {
			OpenedDepartments[0].classList.remove('orgdepartments_departments_item_opened');
		}
		
		// Закрываем все Выделенные Ветви
		var SelectedDepartments = document.getElementsByClassName ("orgdepartments_departments_item_selected");
		Length = SelectedDepartments.length;
		
		for (i = 0; i < Length; i++) {
			SelectedDepartments[0].classList.remove('orgdepartments_departments_item_selected');
		}
		
		// Закрываем все Дочерние Ветви
		var SubSelectedDepartments = document.getElementsByClassName ("orgdepartments_departments_subtree_selected");
		Length = SubSelectedDepartments.length;
		
		for (i = 0; i < Length; i++) {
			SubSelectedDepartments[0].classList.remove('orgdepartments_departments_subtree_selected');
		}

		// Зыкрываем все открытые Подразделения. И Карточки Сотрудников
		var MembersCard = document.getElementsByClassName("orgdepartments_department_description");
		Length = MembersCard.length;

		for (i = 0; i < Length; i++) {
			MembersCard[i].style.display = "none";
		}

		if (Mode_Show == 1) {
			// Members. Открываем Ветви с Найденным Сотрудником и Открываем его карточку
			for (i = 0; i < Terms_ID.length; i++) {
				orgdepartments_toggle_department_box (Terms_ID[i], -1, Member_ID);			
			}
		}
		
		if (Mode_Show == 2) {
			// Departments. Открываем Ветвь с Найденным Подразделением
			orgdepartments_toggle_department_box (Terms_ID, -1, 0);			
		}
	}