var cell_height;
var panzoom_scale = 1.0;
var select_shown = false;

/**
 * Initialize people table
 * @param people
 * @param groups
 * @param artworks
 * @param table_id
 * @param people_per_page
 * @param att_list
 */
function initPeopleTable(people, groups, artworks, table_id, people_per_page, att_list){
		
		//if it's already a dataTable, destroy it.
		if($(table_id).parents('.dataTables_wrapper').length>=1)
			$(table_id).dataTable().fnDestroy();
		destroyBoundingBox();

		to_append="";
		t_id=table_id;
		for(var i in people){
			to_append +='<tr data-id="'+people[i]["id"]+'" id="tr-'+people[i]["id"]+'" '+(people[i]["prev_frame"]?'class="success"':'')+'>';	
			for(var att in att_list){
				switch(att_list[att]){
					case "id":
						to_append += '<td><a href="#" class="popover-img" data-container="body" href="#" data-toggle="popover" data-placement="right" data-content=\'';
						$.ajax({
							url: '../img/avatars/'+people[i][att_list[att]]+'.jpg',
							async: false,
							success: function(){
								to_append += '<img style="max-height: 100px;" src="../img/avatars/'+people[i][att_list[att]]+'.jpg">';
							},
							error: function(){
								to_append += 'Image not found';
							}
						});
						to_append +='\'>'+people[i][att_list[att]]+'</a></td>';
						break;

					case "color":
					  to_append += '<td><a href="#" id="color-'+people[i]["id"]+'" class="thumbnail pickthb" style="background-color:'+people[i][att_list[att]]+'"></a></td>';				
						break;
					case "group":
						to_append += '<td><a href="#" id="group-'+people[i]["id"]+'" class="group-picker" data-type="select2" data-pk="'+people[i]["id"]+'" data-value="'+people[i][att_list[att]]+'" data-title="Select Group">'+jQuery.grep(groups,function(group){return group["id"]==people[i]["group"]})[0]["text"]+'</a></td>';
						break;	

					case "artwork":
						to_append += '<td><a href="#" id="art-'+people[i]["id"]+'" class="artwork-picker" data-type="select2" data-pk="'+people[i]["id"]+'" data-value="'+people[i][att_list[att]]+'" data-title="Select poi">'+jQuery.grep(artworks,function(artwork){return artwork["id"]==people[i]["artwork"]})[0]["text"]+'</a></td>';
						break;	

					case "angle_face":
						to_append += "<td>("+people[i]["angle_face"]+","+people[i]["angle_face_z"]+")</td>";
						break;		

					case "angle_body":
						to_append += "<td>("+people[i]["angle_body"]+","+people[i]["angle_body_z"]+")</td>";
						break;		

					default:
						to_append += "<td>"+people[i][att_list[att]]+"</td>";
						break;
				}
			}		
			to_append +='<td><a class="remove-person" href="javascript:void(0);"><span class="glyphicon glyphicon-remove"></span></a></td></tr>';
			
		}
		$(table_id+"-tbody").html(to_append);

		updateTypeAhead(false);
		updateRemovePerson(table_id, false);
		updatePopover(false);
		pagination(table_id, people_per_page);
		addBoundingBox(people);		
		
		for (var i in people){
			updateColor('#color-'+people[i]['id'], people[i]['color']);		
		}
}

/**
 * Add people to the table
 * @param person
 * @param groups
 * @param artworks
 * @param table_id
 * @param people_per_page
 * @param att_list
 */
function addPeople(person, groups, artworks, table_id, people_per_page, att_list){
		to_append="";
		to_append +='<tr data-id="'+person["id"]+'" id="tr-'+person["id"]+'" '+(person["prev_frame"]?'class="success"':'')+'>';
		for(var att in att_list){
			switch(att_list[att]){
				case "id":
					to_append += '<td><a href="#" class="popover-img" data-container="body" href="#" data-toggle="popover" data-placement="right" data-content="';
					$.ajax({
						url: '../img/avatars/'+person[att_list[att]]+'.jpg',
						async: false,
						success: function(){
							to_append += '<img src=\'../img/avatars/'+person[att_list[att]]+'.jpg\'>';
						},
						error: function(){
							to_append += 'Image not found.';
						}
					});
					to_append +='">'+person[att_list[att]]+'</a></td>';
					break;

				case "color":
				  to_append += '<td><a href="#" id="color-'+person["id"]+'" class="thumbnail not-update pickthb" style="background-color:'+person[att_list[att]]+'"></a></td>';				
					break;
				case "group":
					to_append += '<td><a href="#" id="group-'+person["id"]+'"  class="group-picker" data-type="select2" data-pk="'+person["id"]+'" data-value="'+person[att_list[att]]+'" data-title="Select Group">'+jQuery.grep(groups,function(group){return group["id"]==person["group"]})[0]["text"]+'</a></td>';
					break;	

				case "artwork":
					to_append += '<td><a href="#" id="art-'+person["id"]+'"  class="artwork-picker" data-type="select2" data-pk="'+person["id"]+'" data-value="'+person[att_list[att]]+'" data-title="Select poi">'+jQuery.grep(artworks,function(artwork){return artwork["id"]==person["artwork"]})[0]["text"]+'</a></td>';
					break;	

				case "angle_face":
					to_append += "<td>("+person["angle_face"]+","+person["angle_face_z"]+")</td>";
					break;		

				case "angle_body":
					to_append += "<td>("+person["angle_body"]+","+person["angle_body_z"]+")</td>";
					break;					

				default:
					to_append += "<td>"+person[att_list[att]]+"</td>";
					break;
			}
		}		
		to_append +='<td><a class="remove-person" href="javascript:void(0);"><span class="glyphicon glyphicon-remove"></span></a></td></tr>';
			
		// add a new row at the bottom
		$(table_id+' > tbody:last').append(to_append);

		updateTypeAhead(true);
		updateRemovePerson(table_id, true);
		pagination(table_id, people_per_page);
		updatePopover(true);
		$(table_id).dataTable().fnPageChange('last');
		addBoundingBox([person]);
		updateColor('#color-'+person['id'], person['color']);		
		
		
}

function updateRemovePerson(table_id, last){
	var last_str = last?":last":"";
	$(".remove-person"+last_str).click(function(){
		var t = $(this).parent().parent();
		console.log("[remove-person] call id:"+$(this).parent().parent().data("id"));
		$.ajax({
			type: "POST",
			url: "../php/api.php",
			data: {action:"remove-person",
						id: $(this).parent().parent().data("id")},
			success: function(response){
				console.log("[remove-person] returned "+response);
				if(response){				
					$(table_id).dataTable().fnDeleteRow($(table_id).DataTable().fnPagingInfo()["iStart"]+t.index());
					$("#box-"+t.data("id")+"-bbV").remove();
					$("#box-"+t.data("id")+"-face").remove();
					$("#box-"+t.data("id")).remove();
					updateDeletable("#groups-table");
					updateNPeople("#groups-table");
					$('.timeline').timeline('removePerson', t.data("id"));
				}else{
					alert("Error: could not delete");
				}
			},
			error: function(error){
				console.log('[ERROR] remove-person: ' + error.responseText);
			},
		});	
	});
}

function updatePopover(last){
	var last_str = last?":last":"";
	$(".popover-img"+last_str).popover({
		trigger: 'hover',
		html: true
	});
}

/**
 * Initialize groups table
 * @param groups
 * @param table_id
 * @param groups_per_page
 * @param att_list
 */
function initGroupsTable(groups, table_id, groups_per_page, att_list){
		if($(table_id).parents('.dataTables_wrapper').length>=1)
			$(table_id).dataTable().fnDestroy();
		to_append="";
		for(var i in groups){
			if(groups[i]["id"]!='0'){
				to_append +='<tr id="tr-'+groups[i]["id"]+'-grp" class="grouptah" data-id="'+groups[i]["id"]+'">';	
				for(var att in att_list){
					to_append += "<td>"+groups[i][att_list[att]]+"</td>";
				}		
				to_append +='<td></td></tr>';
			}
		}
		$(table_id+"-tbody").html(to_append);
		pagination(table_id, groups_per_page);
		updateDeletable(table_id);
}

/**
 * Add groups table
 * @param group
 * @param table_id
 * @param groups_per_page
 * @param att_list
 */
function addGroupsTable(group, table_id, groups_per_page, att_list){
		to_append="";
		to_append +='<tr id="tr-'+group["id"]+'-grp" class="grouptah" data-id="'+group["id"]+'">';	
		for(var att in att_list){
			to_append += "<td>"+group[att_list[att]]+"</td>";
		}		
		to_append +='<td><a class="remove-group" href="javascript:void(0);"><span class="glyphicon glyphicon-remove"></span></a></td></tr>';
			
		$(table_id+' > tbody:last').append(to_append);
		pagination(table_id, groups_per_page);
		updateRemoveGroup(table_id, true);
		$(table_id).dataTable().fnPageChange('last');
}

/**
 * Update groups deletable
 * @param table_id
 */
function updateDeletable(table_id){
	console.log("[get-deletable] call");
	$.ajax({
		type: "POST",
		url: "../php/api.php",
		data: {action:"get-deletable"},
		success: function(response){
			console.log("[get-deletable] returned");
			for(var i in response){
				if(response[i]["id"] != 0){
					var table = $(table_id).DataTable(); 
					var id = "tr-"+response[i]["id"]+"-grp"; 
					for(var j in table.fnGetNodes()){ 
						if(table.fnGetNodes()[j].id==id){ 
							table.fnUpdate(response[i]["deletable"]?'<a class="remove-group" href="javascript:void(0);"><span class="glyphicon glyphicon-remove"></span></a>':'',table.fnGetNodes()[j],3);
						}
					} 
				}
			}
			updateRemoveGroup(table_id, false);
		}
	});
}

/**
 * Update number of people per groups
 * @param table_id
 */
function updateNPeople(table_id){ 
	console.log("[get-groups] call"); 
	$.ajax({ 
		type: "POST", 
		url: "../php/api.php", 
		data: {action:"get-groups"}, 
			success: function(response){ 
				console.log("[get-groups] returned"); 
				for(var i in response){ 
						if(response[i]["id"] != 0){ 
							var table = $(table_id).DataTable(); 
							var id = "tr-"+response[i]["id"]+"-grp"; 
							for(var j in table.fnGetNodes()){ 
								if(table.fnGetNodes()[j].id==id){ 
									table.fnUpdate(response[i]["people"],table.fnGetNodes()[j],2);
								}
							} 
						} 
				} 
			},
			error: function(error){
				console.log('[ERROR] get-groups: ' + error.responseText);
			}
	}); 
}

/**
 * Update remove group
 * @param table_id
 * @param last
 */
function updateRemoveGroup(table_id, last){
	var last_str = last?":last":"";
	$(".remove-group"+last_str).click(function(){
		var t = $(this).parent().parent();
		console.log("[remove-group] call id:"+$(this).parent().parent().data("id"));
		$.ajax({
			type: "POST",
			url: "../php/api.php",
			data: {action:"remove-group",
						id: $(this).parent().parent().data("id")},
			success: function(response){
				console.log("[remove-group] returned "+response);
				if(response){				
					$(table_id).dataTable().fnDeleteRow($(table_id).DataTable().fnPagingInfo()["iStart"]+t.index());
				}else{
					alert("Error: could not delete. The group may be not empty");
				}
			},
			error: function(error){
				console.log('[ERROR] remove-group: ' + error.responseText);
			}
		});	
	});
}

/**
 * Update person color
 * @param elem
 * @param col
 */
function updateColor(elem, col){
	var table = $("#people-table").DataTable();
	table.$(elem).colorpicker();
	table.$(elem).colorpicker('setValue', col);
	table.$(elem).click(function(e){
		$("#box-"+$(this).parent().parent().data("id")).click();
	});
	table.$(elem).on('changeColor',function(event){
		var col = event.color.toHex();
		var el = $(this);
		
		console.log("[update-person-attribute] call id:"+el.parent().parent().data("id")+" color:"+col);
		$.ajax({
			type: "POST",
			url: "../php/api.php",
			data: {action:"update-person-attribute",
						id: el.parent().parent().data("id"),
						color: col},
			success: function(response){
				console.log("[update-person-attribute] returned "+response);
				if(response){
					el.css("background-color", col);
					$("#box-"+el.parent().parent().data('id')).css("border-color", col);
					$("#box-"+el.parent().parent().data('id')+"-bbV").css("border-color", col);
					$("#box-"+el.parent().parent().data('id')+"-face").css("border-color", col);
					//Updating timeline
					$('.timeline-annotation-container').remove();
					$('.timeline').timeline('updatePersonColor', {
						id: el.parent().parent().data('id'),
						color: col
					})
				}
			},
			error: function(error){
				console.log('[ERROR] update-person-attribute: ' + error.responseText);
			}
		});	
	});
}


/* UPDATE GROUPS TYPEAHEAD */
function updateTypeAhead(last){
	var last_str = last?":last":"";
	$('.group-picker'+last_str).editable({
		type: 'text',
	    url: '../php/api.php',
		params: function(params) {
			
			//original params contain pk, name and value
			console.log("[update-person-attribute] call id:"+params.pk+" group_id:"+params.value);
			params.action="update-person-attribute";
			params.id=params.pk;
			params.group_id=params.value;
			return params;
		},
		select2: {
			multiple: false,
			placeholder: 'Select Group',
			ajax : {
				url : "../php/api.php",
		        type : "POST",
		        dataType : 'json',
		        data : function ( term,page ) {
					console.log("[get-groups] call query:"+term);
		            return {
						action: 'get-groups',
		                query : term,
		            };
		        },
		        results : function ( data, page ) {
		            return {results : data};
		        }
        	},
		},
		success: function(response, newValue) {		
				select_shown = false;
        if(!response) {
            return "Unknown error!";
        }else
			if(response.success === false) {
				return response.msg;
        	}else{
				updateDeletable("#groups-table");
				updateNPeople("#groups-table");
			}
		}
	});

	$('.artwork-picker'+last_str).editable({
		type: 'text',
	    url: '../php/api.php',
		params: function(params) {
			//original params contain pk, name and value
			console.log("[update-person-attribute] call id:"+params.pk+" opera_id:"+params.value);
			params.action="update-person-attribute";
			params.id=params.pk;
			params.opera_id=params.value;
			return params;
		},
		select2: {
			multiple: false,
			placeholder: 'Select poi',
			ajax : {
				url : "../php/api.php",
		        type : "POST",
		        dataType : 'json',
		        data : function ( term,page ) {
					console.log("[get-artworks] call query:"+term);
		            return {
						action: 'get-artworks',
		                query : term,
		            };
		        },
		        results : function ( data, page ) {
		            return {results : data};
		        }
        	},
		},
		success: function(response, newValue) {
			select_shown = false;
			
        if(!response) {
            return "Unknown error!";
        }else
			if(response.success === false) {
				return response.msg;
        	}
		}
	});
	
	$('.artwork-picker'+last_str).addClass("not-update");
}


/* TABLE pagination */
function pagination(table_id, people_per_page){
	$(table_id).dataTable({
      "bSort": false,       // Disable sorting
			"iDisplayLength": people_per_page,   //records per page
			"sDom": "t<'row'<'col-md-5'i><'col-md-7 not-update'p>>",
			"sPaginationType": "bootstrap",
			"bDestroy": true,
			"bAutoWidth":false,
			"sWrapper":"not-update"
	});
}

/**
 * Add bounding box into the frame, based on people list
 * @param people
 */
function addBoundingBox(people){
	var videoBoxWidth = $('#video-box').width();
	var videoBoxHeight = $('#video-box').height();
	var videoWidth = $("#video-box").data("width");
	var videoHeight = $("#video-box").data("height");
	var factorWidth = videoBoxWidth/videoWidth;
	var defactorWidth = 1;
	var factorHeight = videoBoxHeight/videoHeight;
	var defactorHeight = 1;
			
	for(var i in people){
		
		//Updates bounding box coordinates
		people[i]["bb"][0] = people[i]["bb"][0]*factorWidth;
		people[i]["bb"][1] = people[i]["bb"][1]*factorHeight;
		people[i]["bb"][2] = people[i]["bb"][2]*factorWidth;
		people[i]["bb"][3] = people[i]["bb"][3]*factorHeight;

		//Updates bounding box visible coordinates
		people[i]["bbV"][0] = people[i]["bbV"][0]*factorWidth;
		people[i]["bbV"][1] = people[i]["bbV"][1]*factorHeight;
		people[i]["bbV"][2] = people[i]["bbV"][2]*factorWidth;
		people[i]["bbV"][3] = people[i]["bbV"][3]*factorHeight;

		//Bounding box
		var boundingBox = $('<div></div>')
			.addClass('draggable not-update bb')
			.css({'top': people[i]["bb"][1],
				'left' : people[i]["bb"][0],
				'width' : people[i]['bb'][2],
				'height' : people[i]['bb'][3],
				'border' : '1px dotted ' + people[i]["color"],
				'z-index' : 3,
				'position' : 'absolute'})
			.attr('id', 'box-' + people[i]["id"])
			.attr('data-id',  people[i]["id"])
			.attr('data-mode', 'bb')
			.attr('data-top', people[i]["bb"][1]*defactorHeight)
			.attr('data-left', people[i]["bb"][0]*defactorWidth)
			.attr('data-width', people[i]["bb"][2]*defactorWidth)
			.attr('data-height', people[i]["bb"][3]*defactorHeight);
		
		var boundingBoxVisible = $('<div></div>')
			.addClass('not-update bbV')
			.css({'top': people[i]["bbV"][1],
				'left' : people[i]["bbV"][0],
				'width' : people[i]['bbV'][2],
				'height' : people[i]['bbV'][3],
				'border' : '2px dashed ' + people[i]["color"],
				'z-index' : 4,
				'visibility' : 'hidden',
				'position' : 'absolute'})
			.attr('id', 'box-' + people[i]["id"] + '-bbV')
			.attr('data-id',  people[i]["id"])
			.attr('data-top', people[i]["bbV"][1]*defactorHeight)
			.attr('data-left', people[i]["bbV"][0]*defactorWidth)
			.attr('data-width', people[i]["bbV"][2]*defactorWidth)
			.attr('data-height', people[i]["bbV"][3]*defactorHeight);
		
		//Bounding box face
		var boundingBoxFace = $('<div></div>')
			.addClass('not-update face')
			.css({'top': people[i]["bb"][1],
				'left' : people[i]["bb"][0],
				'width' : people[i]['bb'][2],
				'height' : people[i]['bb'][3],
				'border' : '1px dashed ' + people[i]["color"],
				'visibility' : 'hidden',
				'z-index' : 2,
				'position' : 'absolute'})
			.attr('id', 'box-' + people[i]["id"] + '-face')
			.attr('data-id',  people[i]["id"])
			.attr('data-face', people[i]["angle_face"])
			.attr('data-facez', people[i]["angle_face_z"])
			.attr('data-body', people[i]["angle_body"])
			.attr('data-bodyz', people[i]["angle_body_z"])
			.attr('data-top', people[i]["bb"][1]*defactorHeight)
			.attr('data-left', people[i]["bb"][0]*defactorWidth)
			.attr('data-width', people[i]["bb"][2]*defactorWidth)
			.attr('data-height', people[i]["bb"][3]*defactorHeight);
		
		//Detecting new bounding box, append it to mouse
		if(people[i]["bb"][0] < 0 || people[i]["bb"][1] < 0 ){
			boundingBox.addClass('init');
			boundingBoxVisible.addClass('init');
			boundingBoxFace.addClass('init');
			$('.video-overlay').remove();
			$("#video-wrapper").prepend('<div class="video-overlay"></div>')
				.css('cursor', 'crosshair');
		}
		
		$("#video-wrapper").append(boundingBox);
		$("#video-wrapper").append(boundingBoxVisible);
		$("#video-wrapper").append(boundingBoxFace);
		setDragResize(boundingBox, boundingBoxVisible, boundingBoxFace)
		
	}
	
	if(people.length == 1){
		$(".bb:last, .bbV:last, .face:last").click(function(){
			$("#video-box").panzoom("option", "disablePan", true);
			if(!$("#tr-"+$(this).data("id")).hasClass('info')){
				deselectAllBox("#people-table");
				changeSelectBox("#people-table",$(this));
				//Selecting person in timeline
				$('.timeline').timeline('selectPerson', {
					id: $(this).data('id'), 
					color: $('#people-table #color-' + $(this).data('id'))
						.css('background-color')
				});
			}
		});
	} else {
		$('.bb').click(function(){
			if(!$(this).hasClass('selected')){
				$(this).addClass('selected');
			}
		})
		
		$(".bb, .bbV, .face").click(function(){
			if(!$("#tr-"+$(this).data("id")).hasClass('info')){
				deselectAllBox("#people-table");
				changeSelectBox("#people-table",$(this));
				//Selecting person in timeline
				$('.timeline').timeline('selectPerson', {
					id: $(this).data('id'), 
					color: $('#people-table #color-' + $(this).data('id'))
						.css('background-color')
				});
			}
		});
	}
}


function destroyBoundingBox(){
	$("#video-box").panzoom("destroy");
	$("#video-wrapper .bb").remove();
	$("#video-wrapper .bbV").remove();
	$("#video-wrapper .face").remove();
}

/* -- BOX OPTIONS -- */

/* CONFIRM AND ERROR BOX */
function confirmBox(el){
	if(el.hasClass('danger')){
		el.removeClass('danger');
	}
	el.addClass('success');
}
function errorBox(el){
	if(el.hasClass('success')){
		el.removeClass('success');
	}
	el.addClass('danger');
}

/* SELECT BOX */
function selectBox(el){
	if(el.hasClass('danger')){
		el.removeClass('danger');
	}
	if(el.hasClass('success')){
		el.removeClass('success');
	}
	el.addClass('info');
	updateBoxGraphics(el);
	changeBoxMode("#box-"+el.data("id"),"bbV");
	
	$('#video-wrapper #box-' + el.data("id")).addClass('bb-selected');
	$('#video-wrapper #box-' + el.data("id") + '-bbV').addClass('bb-selected');
	$('#video-wrapper #box-' + el.data("id") + '-face').addClass('bb-selected');
}

/* DESELECT BOX */
function deselectBox(el){
	resetPeopleMode(table_id);
	el.removeClass('info');
	updateBoxGraphics(el);
	changeBoxMode("#box-"+row_el.data("id"),"bb")
	$('#video-wrapper .bb').removeClass('bb-selected');
	$('#video-wrapper .bbV').removeClass('bb-selected');
	$('#video-wrapper .face').removeClass('bb-selected');
}

/**
 * Deselect all bounding box
 * @param table_id
 */
function deselectAllBox(table_id){
	var videoBoxWidth = $('#video-box').width();
	var videoBoxHeight = $('#video-box').height();
	var videoWidth = $("#video-box").data("width");
	var videoHeight = $("#video-box").data("height");
	var defactorWidth = videoWidth/videoBoxWidth;
	var defactorHeight = videoHeight/videoBoxHeight;
	
	var table = $(table_id).DataTable();
	if(table.$('tr.info').length){
		
	var boxId = table.$('tr.info').data("id");
	var bb = [parseInt(parseInt($("#box-"+boxId).data('left')) * defactorWidth),
				parseInt(parseInt($("#box-"+boxId).data('top')) * defactorHeight),
				parseInt(parseInt($("#box-"+boxId).data('width')) * defactorWidth),
				parseInt(parseInt($("#box-"+boxId).data('height')) * defactorHeight)]; 
	var bbV = [parseInt(parseInt($("#box-"+boxId+"-bbV").data('left')) * defactorWidth),
				parseInt(parseInt($("#box-"+boxId+"-bbV").data('top')) * defactorHeight),
				parseInt(parseInt($("#box-"+boxId+"-bbV").data('width')) * defactorWidth),
				parseInt(parseInt($("#box-"+boxId+"-bbV").data('height')) * defactorHeight)]; 

		console.log("[update-person-attribute]");
		
		$.ajax({
			type: "POST",
			url: "../php/api.php",
			data: {action:"update-person-attribute",
					id: boxId,
					bb: bb,
					bbV: bbV,
					angle_face: $("#box-"+table.$('tr.info').data("id")+"-face").data("face"),
					angle_face_z: $("#box-"+table.$('tr.info').data("id")+"-face").data("facez"),
					angle_body: $("#box-"+table.$('tr.info').data("id")+"-face").data("body"),
					angle_body_z: $("#box-"+table.$('tr.info').data("id")+"-face").data("bodyz"), 
					color: table.$('#color-'+table.$('tr.info').data('id')).colorpicker().data('colorpicker').color.toHex(),
					group_id: table.$('#group-'+table.$('tr.info').data('id')).editable('getValue')['group-'+table.$('tr.info').data('id')], 
					opera_id: table.$('#art-'+table.$('tr.info').data('id')).editable('getValue')['art-'+table.$('tr.info').data('id')]
				  },
			success: function(response){
				console.log("[update-person-attribute] returned "+response);
				if(response){
					confirmBox(table.$('tr.info'));
					updateNPeople('#groups-table');
				} else {
					errorBox(table.$('tr.info'));
				}
			
			},
			error: function(error){
				console.log('[ERROR] update-person-attribute: ' + error.responseText);
			},		
			async: false
		});
	}	
	resetPeopleMode(table_id);
	updateBoxGraphics(table.$('tr.info'));
	table.$('tr.info').removeClass('info');
	$('#video-wrapper .bb').removeClass('bb-selected');
	$('#video-wrapper .bbV').removeClass('bb-selected');
	$('#video-wrapper .face').removeClass('bb-selected');
}

/* CHANGE AND SELECT BOX */
function changeSelectBox(table_id, el){
	var table = $(table_id).DataTable();
	var id = "tr-"+el.data("id");
	var page;	
	for(var i in table.fnGetNodes()){
		if(table.fnGetNodes()[i].id==id){
			page = Math.floor(table.fnGetNodes()[i]._DT_RowIndex/table.fnPagingInfo()["iLength"]);
		}
	}
	table.fnPageChange(page);
	selectBox($("#tr-"+el.data("id")));
}

/* UPDATE BOX GRAPHICS */
function updateBoxGraphics(row_el){
	if(row_el.hasClass("success")){
		$("#box-"+row_el.data("id")).css("border-style","solid");
		$("#box-"+row_el.data("id")).css("border-width","1px");
	}else if(row_el.hasClass("danger")){
		$("#box-"+row_el.data("id")).css("border-style","dotted");
		$("#box-"+row_el.data("id")).css("border-width","1px");
	}else if(row_el.hasClass("info")){
		$("#box-"+row_el.data("id")).css("border-style","solid");
		$("#box-"+row_el.data("id")).css("border-width","2px");	
	}else{
		$("#box-"+row_el.data("id")).css("border-style","dotted");
		$("#box-"+row_el.data("id")).css("border-width","1px");
	}
}

/* CHANGE PEOPLE MODE */
function changePeopleMode(table_id){
	var table = $(table_id).DataTable();
	if(typeof table.$('tr.info').data("id") != 'undefined'){
		changeBoxMode("#box-"+table.$('tr.info').data("id"), "next");
	}
	
}

function resetPeopleMode(table_id){
	var table = $(table_id).DataTable();
	if(typeof table.$('tr.info').data("id") != 'undefined'){
		changeBoxMode("#box-"+table.$('tr.info').data("id"),"bb");
	}
}

function changeBoxMode(box_id, val){
	switch(val){
		case "bb":
			$(box_id).css('visibility','visible');	
			$(box_id+"-bbV").css('visibility','hidden');
			$(box_id+"-face").css('visibility','hidden');
			$(box_id).data("mode","bb");	
			destroyCone(box_id+"-face");		
			break;
		case "bbV":
	
			$(box_id+"-bbV").css('visibility','visible');	
			$(box_id).css('visibility','visible');
			$(box_id+"-face").css('visibility','hidden');
			$(box_id).data("mode","bbV");
			destroyCone(box_id+"-face");
			break;
		case "face":
			$(box_id+"-face").css('top', $(box_id).css('top'));
			$(box_id+"-face").css('left', $(box_id).css('left'));
			$(box_id+"-face").css('width', $(box_id).css('width'));
			$(box_id+"-face").css('height', $(box_id).css('height'));
			$(box_id+"-face").css('visibility','visible');
			$(box_id).css('visibility','hidden');	
			$(box_id+"-bbV").css('visibility','hidden');
			$(box_id).data("mode","face");
			addCone(box_id+"-face", $(box_id).data("id"), $(box_id+"-face").data("face"), $(box_id+"-face").data("facez"));
			break;
		case "body":
			$(box_id+"-face").css('top', $(box_id).css('top'));
			$(box_id+"-face").css('left', $(box_id).css('left'));
			$(box_id+"-face").css('width', $(box_id).css('width'));
			$(box_id+"-face").css('height', $(box_id).css('height'));
			$(box_id).data("mode","body");
			setConeVal($(box_id+"-face").data("body"), $(box_id+"-face").data("bodyz"));
			break;
		case "next":
			switch($(box_id).data('mode')){
				case "bbV":
					changeBoxMode(box_id, "face");
					break;
				case "face":
					changeBoxMode(box_id, "body");
					break;
				case "body":
					changeBoxMode(box_id, "bbV");
					break;
			}
	}	
}

// FRAME FUNCTIONS
/* FRAME SIZE 680 x 425px */
function setFrame(frame){

	$("#video-box").css("background-image",'url(' + frame["background"] + ')');
	$("#video-box").data("width",frame["width"]);
	$("#video-box").data("height",frame["height"]);
	$("#video-box").css("width", 680);
	$("#video-box").css("height", 425);
	$("#video-box").css("background-size", "680px 425px");
	$("#goto-frame").select2('data', {'id':frame["frame_id"],'text':frame["frame_id"]});	
	
	loadInfo();

	// Create an image element
	var img = document.createElement('IMG');
	
	img.onload = function () {
	    
		var panzoom = $('#video-box').panzoom({
            $zoomIn: $(".zoom-in"),
            $zoomOut: $(".zoom-out"),
            minScale: 1,
            contain: 'invert',
            onZoom: function(){
            	var transformMatrix = $('#video-box').panzoom("getMatrix");
            	if(transformMatrix.input == 'matrix(1, 0, 0, 1, -15, 0)'){
            		$('#video-box').panzoom("setTransform", 'matrix(1, 0, 0, 1, 0, 0)')
            		transformMatrix = $('#video-box').panzoom("getMatrix");
            	}
            	
            	var scale = parseInt(transformMatrix[0]);
            	var videoInitialWidth = parseFloat($("#video-box").width());
            	var videoInitialHeight = parseFloat($("#video-box").height());
            	var videoWidth = videoInitialWidth * scale;
            	var videoHeight = videoInitialHeight * scale;
            	
            	var left = (videoWidth - videoInitialWidth) / 2;
            	var top = (videoHeight - videoInitialHeight) / 2;

            	var deltaX = parseFloat(transformMatrix[4]);
            	var deltaY = parseFloat(transformMatrix[5]);
            	
            	left -= deltaX;
            	top -= deltaY;            	
     
            	$('.bb').each(function(){
            		var self = $(this);
            		self.addClass('transition-ease-in-out');
            		self.css({
            			'left': parseFloat($(this).data('left')) * scale - left,
            			'top': parseFloat($(this).data('top')) * scale - top,
            			'width': parseFloat($(this).data('width')) * scale,
            			'height': parseFloat($(this).data('height')) * scale            			
            		});
            		setTimeout(function(){self.removeClass('transition-ease-in-out');}, 200);
            		
            	});
            	$('.bbV').each(function(){
            		var self = $(this);
            		self.addClass('transition-ease-in-out');
            		$(this).css({
            			'left': parseFloat($(this).data('left')) * scale - left,
            			'top': parseFloat($(this).data('top')) * scale - top,
            			'width': parseFloat($(this).data('width')) * scale,
            			'height': parseFloat($(this).data('height')) * scale            			
            		});
            		setTimeout(function(){self.removeClass('transition-ease-in-out');}, 200);
            	}); 	
            	$('.face').each(function(){
            		$(this).css({
            			'left': parseFloat($(this).data('left')) * scale - left,
            			'top': parseFloat($(this).data('top')) * scale - top,
            			'width': parseFloat($(this).data('width')) * scale,
            			'height': parseFloat($(this).data('height')) * scale            			
            		});
            	});
            	
            	if(scale > 1){
            		$('.bb').draggable("option", "containment", false);
            		$('.bbV').draggable("option", "containment", false);
            		$('.bb').resizable("option", "containment", false);
            		$('.bbV').resizable("option", "containment", false);
            	} else {
            		$('.bb').draggable("option", "containment", "parent");
            		$('.bbV').draggable("option", "containment", "parent");
            		$('.bb').resizable("option", "containment", "parent");
            		$('.bbV').resizable("option", "containment", "parent");
            	}
            },
            onPan: function(){
            	var transformMatrix = $('#video-box').panzoom("getMatrix");
            	if(transformMatrix.input == 'matrix(1, 0, 0, 1, -15, 0)'){
            		$('#video-box').panzoom("setTransform", 'matrix(1, 0, 0, 1, 0, 0)')
            		transformMatrix = $('#video-box').panzoom("getMatrix");
            	}
            	
            	var scale = parseInt(transformMatrix[0]);
            	var videoInitialWidth = parseFloat($("#video-box").width());
            	var videoInitialHeight = parseFloat($("#video-box").height());
            	var videoWidth = videoInitialWidth * scale;
            	var videoHeight = videoInitialHeight * scale;
            	
            	var left = (videoWidth - videoInitialWidth) / 2;
            	var top = (videoHeight - videoInitialHeight) / 2;

            	var deltaX = parseFloat(transformMatrix[4]);
            	var deltaY = parseFloat(transformMatrix[5]);
            	
            	left -= deltaX;
            	top -= deltaY;            	
     
            	$('.bb').each(function(){
            		var self = $(this);
            		self.css({
            			'left': parseFloat($(this).data('left')) * scale - left,
            			'top': parseFloat($(this).data('top')) * scale - top,
            			'width': parseFloat($(this).data('width')) * scale,
            			'height': parseFloat($(this).data('height')) * scale            			
            		});            		
            	});
            	$('.bbV').each(function(){
            		var self = $(this);
            		$(this).css({
            			'left': parseFloat($(this).data('left')) * scale - left,
            			'top': parseFloat($(this).data('top')) * scale - top,
            			'width': parseFloat($(this).data('width')) * scale,
            			'height': parseFloat($(this).data('height')) * scale            			
            		});
            	}); 	
            	$('.face').each(function(){
            		$(this).css({
            			'left': parseFloat($(this).data('left')) * scale - left,
            			'top': parseFloat($(this).data('top')) * scale - top,
            			'width': parseFloat($(this).data('width')) * scale,
            			'height': parseFloat($(this).data('height')) * scale            			
            		});
            	});
            }
          });
		

	    panzoom.on('mousewheel.focal', function( e ) {
	    	e.preventDefault();	
	    	var sel = $(e.toElement);
	    	if(!sel.hasClass('bb-selected')){
		        var delta = e.delta || e.originalEvent.wheelDelta;
		        var zoomOut = delta ? delta < 0 : e.originalEvent.deltaY > 0;
		        panzoom.panzoom('zoom', zoomOut, {
		        	increment: 1,
		            focal: e
		        });
	    	}
	    
	    });
	    
	}
	img.src = frame["background"];

	$("#video-box").on("panzoomzoom", function(e, panzoom, scale, opts){
		panzoom_scale = scale;
	});
	
	panzoom_scale = 1.0;
}