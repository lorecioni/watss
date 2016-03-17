var groups;
var artworks;
var people_per_page = 5;
var groups_per_page = 3;
var people_att = ["id","color","angle_face","angle_body","group","artwork"];
var groups_att = ["id","text","people"];
var loaded_keyboard = false;

$(document).ready(function(){
	
	//Checking user login permissions
	checkLogin();

	$('#cameras').select2({
		multiple: false,
		placeholder: 'Select a camera',
		ajax : {
			url : "../php/api.php",
			type : "POST",
			dataType : 'json',
			data : function ( term,page ) {
				console.log("[get-cameras] call query:"+term);
				return {
					action: 'get-cameras',
					query : term
				};
			},
			results : function ( data, page ) {
				console.log("[get-cameras] returned");
				return {results : data};
			}
	  	}
	});

	$('#cameras').on('change', function(){
		console.log("[set-camera] call camera_id:"+$("#cameras").select2("data").id);
		$.ajax({
			type: "POST",
			url: "../php/api.php",
			data: {action:"set-camera",
					camera_id: $("#cameras").select2("data").id},
			success: function(response){
				console.log("[set-camera] returned");
			},
			error: function(error){
				console.log('[ERROR] set-camera: ' + error.responseText);
			}
		});
	});

	$('#frame-number').select2({
		multiple: false,
		placeholder: 'Select frame',
		ajax : {
			url : "../php/api.php",
			type : "POST",
			dataType : 'json',
			width: "copy",
			data : function ( term,page ) {
				console.log("[get-frames] call query:"+term+" limit:10");
				return {
					action: 'get-frames',
					query : term,
					limit: 10
				};
			},
			results : function ( data, page ) {
				console.log("[get-frames] returned");
				return {results : data};
			}
	  	}
	});
	$('#frame-number').on('select2-opening',function(){
		$("#optionsRadios3").prop('checked', true);
	});

	

	//Add person link
	$("#add-person").click(function(){
		if( $("input[name=personRadios]:checked").val()=='add-person'){
			console.log("[add-person] call");
			$.ajax({
				type: "POST",
				url: "../php/api.php",
				data: {action:"add-person"},
				success: function(response){
					console.log("[add-person] returned");
					$("#people-table").dataTable().fnDestroy();
					addPeople(response, groups, artworks, "#people-table", people_per_page, people_att);
					//Updates timeline
					$('.timeline').timeline('addPerson', {id: response.id, color: response.color});
				},
				error: function(err){
					console.log(err);
				}
			});
		} else {
			console.log("[add-person] call id: "+$("#prev-person-picker").val());
			$.ajax({
				type: "POST",
				url: "../php/api.php",
				data: {action:"add-person",
						people_id: $("#prev-person-picker").val()},
				success: function(response){
					console.log("[add-person] returned");
					$("#people-table").dataTable().fnDestroy();
					addPeople(response, groups, artworks, "#people-table", people_per_page, people_att);
					//Updates timeline
					$('.timeline').timeline('addPerson', {id: response.id, color: response.color});
				}
			});
		}	
	});

	//Add group link
	$("#add-group").click(function(){
		console.log("[add-group] call name:"+$("#addGroupName").val());
		$.ajax({
			type: "POST",
			url: "../php/api.php",
			data: {action:"add-group",
					name: $("#addGroupName").val() },
			success: function(response){
				if(response){
					groups.push(response);				
					$("#groups-table").dataTable().fnDestroy();
					addGroupsTable(response, "#groups-table", groups_per_page, groups_att);
				}else{
					console.log("Error");
				}
			}
		});	
		
	});

	//Modal option
	$("#insertGroupModal").on('hidden.bs.modal',function(){
		$("#addGroupName").val("");
	});

	$("#insertGroupModal").on('shown.bs.modal',function(){
		$("#addGroupName").focus();
	});

	$('#addGroupName').keyup(function(event){
	  if(event.keyCode == 13){
	      $("#add-group").click();
	  }
	});

	$("#insertPersonModal").on('shown.bs.modal',function(){
		$("#prev-person-picker").html("");
		console.log("[get-realpeople] call");
		$.ajax({
			type: "POST",
			url: "../php/api.php",
			data: {action:"get-realpeople" },
			success: function(response){
				console.log("[get-realpeople] returned");
				to_append = "";
				for(var i in response){
					to_append += '<option data-img-src="'+response[i]["image"]+'" value="'+response[i]["id"]+'"><span class="label label-warning" style="margin: 2px auto;">ID: '+response[i]["id"]+'</span></option>';
				}
				$("#prev-person-picker").html(to_append);
				$("#prev-person-picker").imagepicker({
					hide_select : true,
					show_label  : true,
					clicked: function(){
						$("#personRadios2").prop('checked', true);
					}
				});
			}
		});	
	});
	
	//Logout button clicked
	$("#logout-button").click(function(){
		console.log("[logout] call");
		$.ajax({
			type: "POST",
			url: "../php/api.php",
			data: {action:"logout"},
			success: function(response){
				console.log("[logout] returned");
				$("#checkInfoModal").modal("show");
			}
		});
	});
}); // end of ready method

$("#checkInfoModal").on("hidden.bs.modal", function(){
	checkLogin();
});

$("#checkInfoModal").on('shown.bs.modal',function(){
	$("#username").focus();
});

$('#checkInfoModal #close-login').on('click', function(){
	window.location = '../';
})


/**
 * Person in table selected, selects its bounding box in frame
 */
$('#people-table tbody').on( 'click', 'tr', function (e) {
	var table = $('#people-table').DataTable();
	if (! $(this).hasClass('info') ) {			
		deselectAllBox("#people-table");
		selectBox($(this));
		//Select person in timeline
		var target = $(e.target);
		if(!target.is('.remove-person *')) {
			$('.timeline').timeline('selectPerson', {
				id: $(this).data('id'), 
				color: $(this).find('.pickthb').css('background-color')
			});
		}
    }
});

// frame form
$('#goto-frame').select2({
	multiple: false,
	placeholder: 'Select frame',
	ajax : {
		url : "../php/api.php",
		type : "POST",
		dataType : 'json',
		width: "copy",
		data : function ( term,page ) {
			console.log("[get-frames] call query:"+term+" limit:10");
			return {
				action: 'get-frames',
				query : term,
				limit: 10
			};
		},
		results : function ( data, page ) {
			console.log("[get-frames] returned");
			return {results : data};
		}
  	}
});

$('#goto-frame').on('change', function(){
	console.log("[get-frame] call frame_id:"+$("#goto-frame").select2("data").id);
	$.ajax({
		type: "POST",
		url: "../php/api.php",
		data: {action:"get-frame",
				frame_id: $("#goto-frame").select2("data").id},
		success: function(response){
			console.log("[get-frame] returned");
			setFrame(response);
			$('.timeline').timeline('gotoFrame', response.frame_id);
		}
	});
});

$('#next-frame').click(function(e){
	e.preventDefault();
	$('.timeline').timeline('nextFrame');
});

$('#prev-frame').click(function(e){
	e.preventDefault();
	$('.timeline').timeline('previousFrame');
});


/**
 * Called first for checking user login validity, permission and correct camera
 */
function checkLogin(){
	console.log('[check-gt-login]');
	$.ajax({
		type: "POST",
		url: "../php/api.php",
		data: {action:"check-gt-login",
				user: $("#username").val(),
				camera_id: $("#cameras").select2("data")==null?"":$("#cameras").select2("data").id,
				frame_id: $("input[name=frameRadios]:checked").val(),
				frame_number: $("#frame-number").select2("data")==null?"":$("#frame-number").select2("data").id},
		success: function(response){
			console.log("[check-gt-login] returned: response "+ response);
			if(response){
				if (!loaded_keyboard) {
					addKeyboardEvents();
					loaded_keyboard = true;
				}
				
				//get username
				console.log("[get-user] call");
				$.ajax({
					type: "POST",
					url: "../php/api.php",
					data: {action:"get-user"},
					success: function(response){
						console.log("[get-user] returned "+response);
						$("#welcome-person").html("Welcome, "+response);
					}
				});

				//setFrame
				console.log("[get-frame] call");
				$.ajax({
					type: "POST",
					url: "../php/api.php",
					data: {action:"get-frame"},
					success: function(response){
						console.log("[get-frame] returned");
						setFrame(response);
					},
					async: false
				});
				
				//Loading timeline
				$('#timeline-container').timeline({
					getFrames: function(){
						var self = $(this);
						$.ajax({
							type: "POST",
							url: "../php/api.php",
							data: {action:"get-timeline-frames"},
							success: function(data){
								var current = data.current;
								self.timeline('showFrames', {frames: data.frames, current: current});				
							},
							error: function(error){
								console.log('[ERROR] get-timeline-frames: ' + error.responseText);
							}
						});	
					},
					onFrameSelected: function(id){
						$.ajax({
							type: "POST",
							url: "../php/api.php",
							data: {action:"get-frame",
									frame_id: id},
							success: function(response){
								setFrame(response);
							},
							error: function(error){
								console.log('[ERROR] get-frames: ' + error.responseText);
							}
						});
					},
				});
				
				//Camera calibration
				loadCameraCalibration();

			} else {
				$("#checkInfoModal").modal("show");
			}
		},
		error: function(error){
			console.log('[ERROR] check-login: ' + error.responseText);
		},
		async: false
	});
}

/**
 * Loading gt info, getting groups, artworks, people
 * 
 */
function loadInfo(){	
	
	console.log("[get-groups] call");
	$.ajax({
		type: "POST",
		url: "../php/api.php",
		data: {action:"get-groups"},
		success: function(response){
			console.log("[get-groups] returned");
			groups = response
			initGroupsTable(groups, "#groups-table", groups_per_page, groups_att);
		},
		error: function(error){
			console.log('[ERROR] get-groups: ' + error.responseText);
		},
		async: false
	});

	console.log("[get-artworks] call");
	$.ajax({
		type: "POST",
		url: "../php/api.php",
		data: {action:"get-artworks"},
		success: function(response){
			console.log("[get-artworks] returned");
			artworks = response;
		},
		error: function(error){
			console.log('[ERROR] get-artworks: ' + error.responseText);
		},
		async: false
	});

	console.log("[get-people] call");
	$.ajax({
		type: "POST",
		url: "../php/api.php",
		data: {action:"get-people"},
		success: function(response){
			console.log("[get-people] returned");
			people = response;
			initPeopleTable(response, groups, artworks, "#people-table", people_per_page, people_att);
		},
		error: function(error){
			console.log('[ERROR] get-people: ' + error.responseText);
		},
		async: false
	});
	
	// Click on the body (remove focus)
	$('body').click(function(e){
		var target = $(e.target);
		if(!target.is('.not-update *') && !target.is('.colorpicker *') && !target.is('.editable-submit *') && !target.is('.timeline *')) {
			if(!target.hasClass('not-update')){
				if(!target.is('a') && !target.is('button')  && !target.is('submit')) {		
					if (!select_shown){
						deselectAllBox("#people-table");
						$("#video-box").panzoom("option", "disablePan", false);
						$('.timeline').timeline('deselectAll');
					}
				}
			}
		}
	});

	var reloadTable = function(table_id){
		console.log("[get-groups] call");
		$.ajax({
			type: "POST",
			url: "../php/api.php",
			data: {action:"get-groups"},
			success: function(response){
				console.log("[get-groups] returned");
				groups = response
				initGroupsTable(groups, "#groups-table", groups_per_page, groups_att);
			},
			error: function(error){
				console.log('[ERROR] get-groups: ' + error.responseText);
			},
			async: false
		});
	}
}
