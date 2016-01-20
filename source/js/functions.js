var cell_height;
var panzoom_scale = 1.0;
var select_shown = false;

/* INITIALIZE PEOPLE TABLE */
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
							url: '../img/real_people/'+people[i][att_list[att]]+'_100.jpg',
							async: false,
							success: function(){
								to_append += '<img src="../img/real_people/'+people[i][att_list[att]]+'_100.jpg">';
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

/* UPDATE PEOPLE TABLE - ADD PERSON IN BOTTOM POSITION*/
function addPeople(person, groups, artworks, table_id, people_per_page, att_list){
		to_append="";
		to_append +='<tr data-id="'+person["id"]+'" id="tr-'+person["id"]+'" '+(person["prev_frame"]?'class="success"':'')+'>';
		for(var att in att_list){
			switch(att_list[att]){
				case "id":
					to_append += '<td><a href="#" class="popover-img" data-container="body" href="#" data-toggle="popover" data-placement="right" data-content="';
					$.ajax({
						url: '../img/real_people/'+person[att_list[att]]+'_100.jpg',
						async: false,
						success: function(){
							to_append += '<img src=\'../img/real_people/'+person[att_list[att]]+'_100.jpg\'>';
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
				}else{
					alert("Error: could not delete");
				}
			}
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

/* INIT GROUPS TABLE */
function initGroupsTable(groups, table_id, groups_per_page, att_list){
		if($(table_id).parents('.dataTables_wrapper').length>=1)
			$(table_id).dataTable().fnDestroy();
		to_append="";
		for(var i in groups){
			if(groups[i]["id"]!='G0'){
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

/* ADD GROUP TABLE */
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

/* UPDATE DELETABLE */
function updateDeletable(table_id){
	console.log("[get-deletable] call");
	$.ajax({
		type: "POST",
		url: "../php/api.php",
		data: {action:"get-deletable"},
		success: function(response){
			console.log("[get-deletable] returned");
			for(var i in response){
				if(response[i]["id"] != "G0"){
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

/* UPDATE NPEOPLE */
function updateNPeople(table_id){ 
	console.log("[get-groups] call"); 
	$.ajax({ 
		type: "POST", 
		url: "../php/api.php", 
		data: {action:"get-groups"}, 
			success: function(response){ 
				console.log("[get-groups] returned"); 
				for(var i in response){ 
						if(response[i]["id"] != "G0"){ 
							var table = $(table_id).DataTable(); 
							var id = "tr-"+response[i]["id"]+"-grp"; 
							for(var j in table.fnGetNodes()){ 
								if(table.fnGetNodes()[j].id==id){ 
									table.fnUpdate(response[i]["people"],table.fnGetNodes()[j],2);
								}
							} 
						} 
				} 
			} 
	}); 
}

/* UPDATE REMOVE GROUP */
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
			}
		});	
	});
}

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
				}
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
	var videoBoxWidth = jQuery('#video-box').width();
	var videoBoxHeight = jQuery('#video-box').height();
	var videoWidth = $("#video-box").data("width");
	var videoHeight = $("#video-box").data("height");
	
	for(var i in people){
		//Updates bounding box coordinates
		people[i]["bb"][0] = people[i]["bb"][0]*videoBoxWidth/videoWidth;
		people[i]["bb"][1] = people[i]["bb"][1]*videoBoxHeight/videoHeight;
		people[i]["bb"][2] = people[i]["bb"][2]*videoBoxWidth/videoWidth;
		people[i]["bb"][3] = people[i]["bb"][3]*videoBoxHeight/videoHeight;

		//Updates bounding box visible coordinates
		people[i]["bbV"][0] = people[i]["bbV"][0]*videoBoxWidth/videoWidth;
		people[i]["bbV"][1] = people[i]["bbV"][1]*videoBoxHeight/videoHeight;
		people[i]["bbV"][2] = people[i]["bbV"][2]*videoBoxWidth/videoWidth;
		people[i]["bbV"][3] = people[i]["bbV"][3]*videoBoxHeight/videoHeight;

		//Bounding box
		var boundingBox = jQuery('<div></div>')
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
			.attr('data-mode', 'bb');
		
		var boundingBoxVisible = jQuery('<div></div>')
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
			.attr('data-id',  people[i]["id"]);
		
		//Bounding box face
		var boundingBoxFace = jQuery('<div></div>')
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
			.attr('data-bodyz', people[i]["angle_body_z"]);
		
		$("#video-box").append(boundingBox);
		$("#video-box").append(boundingBoxVisible);
		$("#video-box").append(boundingBoxFace);
		setDragResize(boundingBox, boundingBoxVisible, boundingBoxFace)
		
	}

//	for(var i in people){
//		setDragResize("#box-"+people[i]["id"],"#box-"+people[i]["id"]+"-bbV");
//	}

	if(people.length == 1){
		$(".bb:last, .bbV:last, .face:last").click(function(){
			$("#video-box").panzoom("option", "disablePan", true);
			if(!$("#tr-"+$(this).data("id")).hasClass('info')){
				deselectAllBox("#people-table");
				changeSelectBox("#people-table",$(this));
			}
		});
	}else{
		$(".bb, .bbV, .face").click(function(){
			$("#video-box").panzoom("option", "disablePan", true);
			if(!$("#tr-"+$(this).data("id")).hasClass('info')){
				deselectAllBox("#people-table");
				changeSelectBox("#people-table",$(this));
			}
		});
	}
}

function destroyBoundingBox(){
	$("#video-box").panzoom("destroy");
	$("#video-box").html("");

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

	//ZOOM
	if(panzoom_scale> 1.0){
		var table = $("#people-table").DataTable();
		var $box = $("#box-"+table.$('tr.info').data("id"));
		console.log("#box-"+table.$('tr.info').data("id"));
		$("#video-box").panzoom("option", "disablePan", false);
		$("#video-box").panzoom("resetPan");
		$("#video-box").panzoom("pan", $("#video-box").width()*panzoom_scale/2 - $box.position().left, $("#video-box").height()*panzoom_scale/2 - $box.position().top, {relative: true});
		$("#video-box").panzoom("option", "disablePan", true);
	}
}

/* DESELECT BOX */
function deselectBox(el){
	resetPeopleMode(table_id);
	el.removeClass('info');
	updateBoxGraphics(el);
	changeBoxMode("#box-"+row_el.data("id"),"bb")
}

/* DESELECT ALL BOX */
function deselectAllBox(table_id){
	var table = $(table_id).DataTable();
	if(table.$('tr.info').length){
	console.log("LOG: ", $("#box-"+table.$('tr.info').data("id")).offset().left, $("#video-box").offset().left, $("#video-box").data("width"), (panzoom_scale));
		var bb = [parseInt(($("#box-"+table.$('tr.info').data("id")).offset().left - $("#video-box").offset().left)*parseFloat($("#video-box").data("width"))/(680.0*panzoom_scale)),
				parseInt(($("#box-"+table.$('tr.info').data("id")).offset().top - $("#video-box").offset().top)*parseFloat($("#video-box").data("height"))/(425.0*panzoom_scale)),
				parseInt($("#box-"+table.$('tr.info').data("id")).outerWidth()*parseFloat($("#video-box").data("width"))/680.0),
				parseInt($("#box-"+table.$('tr.info').data("id")).outerHeight()*parseFloat($("#video-box").data("height"))/425.0)]; 
		var bbV = [parseInt(($("#box-"+table.$('tr.info').data("id")+"-bbV").offset().left - $("#video-box").offset().left)*parseFloat($("#video-box").data("width"))/(680.0*panzoom_scale)),
				parseInt(($("#box-"+table.$('tr.info').data("id")+"-bbV").offset().top - $("#video-box").offset().top)*parseFloat($("#video-box").data("height"))/(425.0*panzoom_scale)),
				parseInt($("#box-"+table.$('tr.info').data("id")+"-bbV").outerWidth()*parseFloat($("#video-box").data("width"))/680.0),
				parseInt($("#box-"+table.$('tr.info').data("id")+"-bbV").outerHeight()*parseFloat($("#video-box").data("height"))/425.0)]; 
		
		console.log("[update-person-attribute] call bb:["+bb[0]+","+bb[1]+","+bb[2]+","+bb[3]+"] bbV:["+bbV[0]+","+bbV[1]+","+bbV[2]+","+bbV[3]+"] angle_face:"+ $("#box-"+table.$('tr.info').data("id")+"-face").data("face") +" angle_face_z:"+$("#box-"+table.$('tr.info').data("id")+"-face").data("facez")+" angle_body:"+ $("#box-"+table.$('tr.info').data("id")+"-face").data("body")+ " angle_body_z:"+$("#box-"+table.$('tr.info').data("id")+"-face").data("bodyz")+" color: ",table.$('#color-'+table.$('tr.info').data('id')).colorpicker().data('colorpicker').color.toHex()," group: ",table.$('#group-'+table.$('tr.info').data('id')).editable('getValue'), "	art: "+table.$('#art-'+table.$('tr.info').data('id')).editable().data('select2'));

		$.ajax({
			type: "POST",
			url: "../php/api.php",
			data: {action:"update-person-attribute",
					id: table.$('tr.info').data("id"),
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
				}else{
					errorBox(table.$('tr.info'));
				}
			
			},
			async: false
		});
	}	
	resetPeopleMode(table_id);
	updateBoxGraphics(table.$('tr.info'));
	table.$('tr.info').removeClass('info');
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
            startTransform: 'scale(1.0)',
            increment: 1,
            minScale: 1,
            contain: 'invert'
          });
		

	    panzoom.on('mousewheel.focal', function( e ) {
	    	e.preventDefault();			
	        var delta = e.delta || e.originalEvent.wheelDelta;
	        var zoomOut = delta ? delta < 0 : e.originalEvent.deltaY > 0;
	        panzoom.panzoom('zoom', zoomOut, {
	        	increment: 1,
	            focal: e
	        });
	    });
	    
	}
	img.src = frame["background"];

	$("#video-box").on("panzoomzoom", function(e, panzoom, scale, opts){
		panzoom_scale = scale;
	});
	
	panzoom_scale = 1.0;
}