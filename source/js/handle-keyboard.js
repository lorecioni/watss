var keymap = {
	bb_left: 'left',
	bb_up: 'up', 
	bb_right: 'right', 
	bb_down: 'down', 
	bbV_left: 'Alt+left', 
	bbV_up: 'Alt+up', 
	bbV_right: 'Alt+right', 
	bbV_down: 'Alt+down',
	bb_more_width: 'd',
	bb_less_width: 'a', 
	bb_more_height: 's', 
	bb_less_height: 'w', 
	bbV_more_width: 'Alt+d',
	bbV_less_width: 'Alt+a', 
	bbV_more_height: 'Alt+s', 
	bbV_less_height: 'Alt+w', 
	rotate_cone_y: 'f',
	rotate_cone_y_less: 'Alt+f',
	rotate_cone_z: 'g',
	rotate_cone_z_less: 'Alt+g',
	change_mode: 'm',
	add_person: '+',
	change_selected_person: 'u',
	next_frame: 'return'
};

var counter = 0;
var move = 1;

function addKeyboardEvents(){
	//key-event handler
    jQuery('#platform-details').html('<code>' + navigator.userAgent + '</code>');


/**************** LEFT ****************/
    jQuery(document).bind('keydown', keymap["bb_left"],function (evt){
		counter++;
		if (counter >= 20){
			move = 3;
		}
		var table = $("#people-table").DataTable(); 
		if(table.$('tr.info').length!=0){
			var boxId = table.$('tr.info').data("id");
			var bb = $("#box-"+ boxId);
			var bbV = $("#box-"+ boxId + "-bbV");
			var face = $("#box-"+ boxId + "-face");

			if(bb.position().left > 0){
				bb.offset({
					left: Math.max(bb.offset().left - move, 0),
					top: bb.offset().top
				});
				bbV.offset({
					left: Math.max(bbV.offset().left - move, 0),
					top: bbV.offset().top
				});
			} 
			updateBoundingBoxesData(bb, bbV, face);
		}
		return false;
	});

	jQuery(document).bind('keyup', keymap["bb_left"],function (evt){counter=0; move=1;return false;});

/**************** RIGHT ****************/
    jQuery(document).bind('keydown', keymap["bb_right"],function (evt){
		counter++;
		if (counter >= 20){
			move = 3;
		}
		var table = $("#people-table").DataTable(); 
		if(table.$('tr.info').length!=0){ 
			var boxId = table.$('tr.info').data("id");
			var bb = $("#box-"+ boxId);
			var bbV = $("#box-"+ boxId + "-bbV");
			var face = $("#box-"+ boxId + "-face");
			
			if(bb.position().left + bb.outerWidth() < $("#video-box").width()){	
				bb.offset({
							left: Math.min(bb.offset().left + move, 
									$("#video-box").offset().left + $("#video-box").width() - 
									bb.outerWidth()),
							top: bb.offset().top
				});
				bbV.offset({
							left: Math.min(bbV.offset().left+move, 
									$("#video-box").offset().left + $("#video-box").width() - 
									bbV.outerWidth()),
							top: bbV.offset().top
				});
			}
			updateBoundingBoxesData(bb, bbV, face);
		}
		return false;
	});

	jQuery(document).bind('keyup', keymap["bb_right"],function (evt){counter=0; move=1;return false;});

/**************** DOWN ****************/
    jQuery(document).bind('keydown', keymap["bb_down"],function (evt){
		counter++;
		if (counter >= 20){
			move = 3;
		}
		var table = $("#people-table").DataTable(); 
		if(table.$('tr.info').length!=0){ 
			var boxId = table.$('tr.info').data("id");
			var bb = $("#box-"+ boxId);
			var bbV = $("#box-"+ boxId + "-bbV");
			var face = $("#box-"+ boxId + "-face");
			
			if(bb.position().top+bb.outerHeight() < $("#video-box").height()){
				bb.offset({
							left: bb.offset().left,
							top: Math.min(bb.offset().top+move, 
									$("#video-box").offset().top + $("#video-box").height() - 
									bb.outerHeight())
				});
				bbV.offset({
							left: bbV.offset().left,
							top: Math.min(bbV.offset().top+move, 
									$("#video-box").offset().top + 	$("#video-box").height() - 
									bbV.outerHeight())
				});
			}
			updateBoundingBoxesData(bb, bbV, face);
		}
		return false;
	});

	jQuery(document).bind('keyup', keymap["bb_down"],function (evt){counter=0; move=1;return false;});

/**************** UP ****************/
    jQuery(document).bind('keydown', keymap["bb_up"],function (evt){
		counter++;
		if (counter >= 20){
			move = 3;
		}
		var table = $("#people-table").DataTable(); 
		if(table.$('tr.info').length!=0){ 
			var boxId = table.$('tr.info').data("id");
			var bb = $("#box-"+ boxId);
			var bbV = $("#box-"+ boxId + "-bbV");
			var face = $("#box-"+ boxId + "-face");
			
			if(bb.position().top>0){
				bb.offset({
							left: bb.offset().left,
							top: Math.max(bb.offset().top-move, 0)
				});
				bbV.offset({
							left: bbV.offset().left,
							top: Math.max(bbV.offset().top-move, 0)
				});
			} 
			updateBoundingBoxesData(bb, bbV, face);
		}
		return false;
	});

	jQuery(document).bind('keyup', keymap["bb_up"],function (evt){counter=0; move=1;return false;});

/**************** CTRL + LEFT ****************/
    jQuery(document).bind('keydown', keymap["bbV_left"],function (evt){
		counter++;
		if (counter >= 20){
			move = 3;
		}
		var table = $("#people-table").DataTable(); 
		if(table.$('tr.info').length!=0){
			if($("#box-"+table.$('tr.info').data("id")+"-bbV").position().left > $("#box-"+table.$('tr.info').data("id")).position().left){
				$("#box-"+table.$('tr.info').data("id")+"-bbV").offset({
							left: Math.max($("#box-"+table.$('tr.info').data("id")+"-bbV").offset().left-move,
										$("#box-"+table.$('tr.info').data("id")).offset().left),
							top: $("#box-"+table.$('tr.info').data("id")+"-bbV").offset().top
				});
			} 
		}
		return false;
		
	});
	jQuery(document).bind('keyup', keymap["bbV_left"],function (evt){counter=0; move=1;});
	
/**************** CTRL + UP ****************/	
    jQuery(document).bind('keydown', keymap["bbV_up"],function (evt){ 
		counter++;
		if (counter >= 20){
			move = 3;
		}
		var table = $("#people-table").DataTable(); 
		if(table.$('tr.info').length!=0){ 
			if($("#box-"+table.$('tr.info').data("id")+"-bbV").position().top > $("#box-"+table.$('tr.info').data("id")).position().top){
				$("#box-"+table.$('tr.info').data("id")+"-bbV").offset({
							left: $("#box-"+table.$('tr.info').data("id")+"-bbV").offset().left,
							top: Math.max($("#box-"+table.$('tr.info').data("id")+"-bbV").offset().top-move,
										$("#box-"+table.$('tr.info').data("id")).offset().top)
				});
			} 
		}
		return false;
		
	});
	jQuery(document).bind('keyup', keymap["bbV_up"],function (evt){counter=0; move=1;});
	
/**************** CTRL + RIGHT ****************/
    jQuery(document).bind('keydown', keymap["bbV_right"],function (evt){ 
		counter++;
		if (counter >= 20){
			move = 3;
		}
		var table = $("#people-table").DataTable(); 
		if(table.$('tr.info').length!=0){ 
			if($("#box-"+table.$('tr.info').data("id")+"-bbV").position().left+$("#box-"+table.$('tr.info').data("id")+"-bbV").outerWidth()*panzoom_scale < $("#box-"+table.$('tr.info').data("id")).position().left+$("#box-"+table.$('tr.info').data("id")).outerWidth()*panzoom_scale){	
				$("#box-"+table.$('tr.info').data("id")+"-bbV").offset({
							left: Math.min($("#box-"+table.$('tr.info').data("id")+"-bbV").offset().left+move,
									$("#box-"+table.$('tr.info').data("id")).offset().left + 
									$("#box-"+table.$('tr.info').data("id")).outerWidth()*panzoom_scale - 
									$("#box-"+table.$('tr.info').data("id")+"-bbV").outerWidth()*panzoom_scale),
							top: $("#box-"+table.$('tr.info').data("id")+"-bbV").offset().top
				});
			} 
		}
		return false;
	});
	jQuery(document).bind('keyup', keymap["bbV_right"],function (evt){counter=0; move=1; });
	
/**************** CTRL + DOWN ****************/
   jQuery(document).bind('keydown', keymap["bbV_down"],function (evt){ 
		counter++;
		if (counter >= 20){
			move = 3;
		}
		var table = $("#people-table").DataTable(); 
		if(table.$('tr.info').length!=0){ 
			if($("#box-"+table.$('tr.info').data("id")+"-bbV").position().top+$("#box-"+table.$('tr.info').data("id")+"-bbV").outerHeight()*panzoom_scale < $("#box-"+table.$('tr.info').data("id")).position().top+$("#box-"+table.$('tr.info').data("id")).outerHeight()*panzoom_scale){
				$("#box-"+table.$('tr.info').data("id")+"-bbV").offset({
							left: $("#box-"+table.$('tr.info').data("id")+"-bbV").offset().left,
							top: Math.min($("#box-"+table.$('tr.info').data("id")+"-bbV").offset().top+move,
									$("#box-"+table.$('tr.info').data("id")).offset().top + 
									$("#box-"+table.$('tr.info').data("id")).outerHeight()*panzoom_scale - 
									$("#box-"+table.$('tr.info').data("id")+"-bbV").outerHeight()*panzoom_scale)
				});
			} 
		}
		return false;
		
   });
   jQuery(document).bind('keyup', keymap["bbV_down"],function (evt){counter=0; move=1;});

/**************** D ****************/
   jQuery(document).bind('keydown', keymap["bb_more_width"],function (evt){ 
		counter++;
		if (counter >= 20){
			move = 3;
		}
		var table = $("#people-table").DataTable(); 
		if(table.$('tr.info').length!=0){ 
			if($("#box-"+table.$('tr.info').data("id")).position().left+$("#box-"+table.$('tr.info').data("id")).outerWidth()*panzoom_scale < $("#video-box").width()*panzoom_scale){
				$("#box-"+table.$('tr.info').data("id")).width( 
					Math.min($("#box-"+table.$('tr.info').data("id")).width()+move,
					$("#video-box").width()*panzoom_scale - $("#box-"+table.$('tr.info').data("id")).position().left - 4));
			}
		}
		return false;
		
   });
   jQuery(document).bind('keyup', keymap["bb_more_width"],function (evt){counter=0; move=1;});

/**************** S ****************/
   jQuery(document).bind('keydown', keymap["bb_more_height"],function (evt){ 
		counter++;
		if (counter >= 20){
			move = 3;
		}
		var table = $("#people-table").DataTable(); 
		if(table.$('tr.info').length!=0){ 
			if($("#box-"+table.$('tr.info').data("id")).position().top+$("#box-"+table.$('tr.info').data("id")).outerHeight()*panzoom_scale < $("#video-box").height()*panzoom_scale){
				$("#box-"+table.$('tr.info').data("id")).height( 
					Math.min($("#box-"+table.$('tr.info').data("id")).height()+move,
					$("#video-box").height()*panzoom_scale - $("#box-"+table.$('tr.info').data("id")).position().top - 4));
			}
		}
		return false;
		
   });
   jQuery(document).bind('keyup', keymap["bb_more_height"],function (evt){counter=0; move=1;});

/**************** A ****************/
   jQuery(document).bind('keydown', keymap["bb_less_width"],function (evt){ 
		counter++;
		if (counter >= 20){
			move = 3;
		}
		var table = $("#people-table").DataTable(); 
		if(table.$('tr.info').length!=0){ 
			if($("#box-"+table.$('tr.info').data("id")).width()*panzoom_scale>1){
				$("#box-"+table.$('tr.info').data("id")).width(
					Math.max($("#box-"+table.$('tr.info').data("id")).width()-move, 1));	

				if ($("#box-"+table.$('tr.info').data("id")+"-bbV").outerWidth()*panzoom_scale + $("#box-"+table.$('tr.info').data("id")+"-bbV").offset().left > $("#box-"+table.$('tr.info').data("id")).outerWidth()*panzoom_scale + $("#box-"+table.$('tr.info').data("id")).offset().left ) {
					$("#box-"+table.$('tr.info').data("id")+"-bbV").offset({
						top: $("#box-"+table.$('tr.info').data("id")+"-bbV").offset().top, 
						left: $("#box-"+table.$('tr.info').data("id")).outerWidth()*panzoom_scale + 
							$("#box-"+table.$('tr.info').data("id")).offset().left -
							$("#box-"+table.$('tr.info').data("id")+"-bbV").outerWidth()*panzoom_scale+panzoom_scale
					});
				}
				if ($("#box-"+table.$('tr.info').data("id")+"-bbV").outerWidth()*panzoom_scale > $("#box-"+table.$('tr.info').data("id")).outerWidth()*panzoom_scale) {
					$("#box-"+table.$('tr.info').data("id")+"-bbV").width($("#box-"+table.$('tr.info').data("id")).width()*panzoom_scale);
					
				}
			}
		}
		return false;
		
   });
   jQuery(document).bind('keyup', keymap["bb_less_width"],function (evt){counter=0; move=1;});

/**************** W ****************/
   jQuery(document).bind('keydown', keymap["bb_less_height"],function (evt){ 
		counter++;
		if (counter >= 20){
			move = 3;
		}
		var table = $("#people-table").DataTable(); 
		if(table.$('tr.info').length!=0){ 
			if($("#box-"+table.$('tr.info').data("id")).height()*panzoom_scale>1){
				$("#box-"+table.$('tr.info').data("id")).height(
					Math.max($("#box-"+table.$('tr.info').data("id")).height() - move, 1));	

				if ($("#box-"+table.$('tr.info').data("id")+"-bbV").outerHeight()*panzoom_scale + $("#box-"+table.$('tr.info').data("id")+"-bbV").offset().top > $("#box-"+table.$('tr.info').data("id")).outerHeight()*panzoom_scale + $("#box-"+table.$('tr.info').data("id")).offset().top ) {
					console.log($("#box-"+table.$('tr.info').data("id")).offset().top,$("#box-"+table.$('tr.info').data("id")+"-bbV").offset().top, panzoom_scale);
					$("#box-"+table.$('tr.info').data("id")+"-bbV").offset({
						left: $("#box-"+table.$('tr.info').data("id")+"-bbV").offset().left, 
						top: $("#box-"+table.$('tr.info').data("id")).outerHeight()*panzoom_scale + 
							$("#box-"+table.$('tr.info').data("id")).offset().top -
							$("#box-"+table.$('tr.info').data("id")+"-bbV").outerHeight()*panzoom_scale+panzoom_scale,
					});
				}
				if ($("#box-"+table.$('tr.info').data("id")+"-bbV").outerHeight()*panzoom_scale > $("#box-"+table.$('tr.info').data("id")).outerHeight()*panzoom_scale) {
						$("#box-"+table.$('tr.info').data("id")+"-bbV").height($("#box-"+table.$('tr.info').data("id")).innerHeight());
						
					}
			}
		}
		return false;
		
   });
   jQuery(document).bind('keyup', keymap["bb_less_height"],function (evt){counter=0; move=1;});


/**************** CTRL+D ****************/
   jQuery(document).bind('keydown', keymap["bbV_more_width"],function (evt){ 
		counter++;
		if (counter >= 20){
			move = 3;
		}
		var table = $("#people-table").DataTable(); 
		if(table.$('tr.info').length!=0){ 
			if($("#box-"+table.$('tr.info').data("id")+"-bbV").position().left+$("#box-"+table.$('tr.info').data("id")+"-bbV").outerWidth()*panzoom_scale < $("#box-"+table.$('tr.info').data("id")).position().left + $("#box-"+table.$('tr.info').data("id")).outerWidth()*panzoom_scale){

				$("#box-"+table.$('tr.info').data("id")+"-bbV").width( 
					Math.min($("#box-"+table.$('tr.info').data("id")+"-bbV").width()+move,
					$("#box-"+table.$('tr.info').data("id")).width()*panzoom_scale + $("#box-"+table.$('tr.info').data("id")).position().left -
					$("#box-"+table.$('tr.info').data("id")+"-bbV").position().left));
			}
		}
		return false;
		
   });
   jQuery(document).bind('keyup', keymap["bbV_more_width"],function (evt){counter=0; move=1;});

/**************** CTRL+S ****************/
   jQuery(document).bind('keydown', keymap["bbV_more_height"],function (evt){ 
		counter++;
		if (counter >= 20){
			move = 3;
		}
		var table = $("#people-table").DataTable(); 
		if(table.$('tr.info').length!=0){ 
			if($("#box-"+table.$('tr.info').data("id")+"-bbV").position().top+$("#box-"+table.$('tr.info').data("id")+"-bbV").outerHeight()*panzoom_scale < $("#box-"+table.$('tr.info').data("id")).position().top + $("#box-"+table.$('tr.info').data("id")).outerHeight()*panzoom_scale){

				$("#box-"+table.$('tr.info').data("id")+"-bbV").height( 
					Math.min($("#box-"+table.$('tr.info').data("id")+"-bbV").height()+move,
					$("#box-"+table.$('tr.info').data("id")).height()*panzoom_scale + $("#box-"+table.$('tr.info').data("id")).position().top -
					$("#box-"+table.$('tr.info').data("id")+"-bbV").position().top));
			}
		}
		return false;
		
   });
   jQuery(document).bind('keyup', keymap["bbV_more_height"],function (evt){counter=0; move=1;});

/**************** CTRL+A ****************/
   jQuery(document).bind('keydown', keymap["bbV_less_width"],function (evt){ 
		counter++;
		if (counter >= 20){
			move = 3;
		}
		var table = $("#people-table").DataTable(); 
		if(table.$('tr.info').length!=0){ 
			if($("#box-"+table.$('tr.info').data("id")+"-bbV").width()*panzoom_scale > 1){
				$("#box-"+table.$('tr.info').data("id")+"-bbV").width(
					Math.max($("#box-"+table.$('tr.info').data("id")+"-bbV").width()-move, 1));	
			}
		}
		return false;
   });
   jQuery(document).bind('keyup', keymap["bbV_less_width"],function (evt){counter=0; move=1;});

/**************** CTRL+W ****************/
   jQuery(document).bind('keydown', keymap["bbV_less_height"],function (evt){ 
		counter++;
		if (counter >= 20){
			move = 3;
		}
		var table = $("#people-table").DataTable(); 
		if(table.$('tr.info').length!=0){ 
			if($("#box-"+table.$('tr.info').data("id")+"-bbV").height()*panzoom_scale>1){
				$("#box-"+table.$('tr.info').data("id")+"-bbV").height(
					Math.max($("#box-"+table.$('tr.info').data("id")+"-bbV").height()-move, 1));	
			}
		}
		return false;
		
   });
   jQuery(document).bind('keyup', keymap["bbV_less_height"],function (evt){counter=0; move=1;});



	// Rotate the cone
    jQuery(document).bind('keydown', keymap["rotate_cone_y"],function (evt){ 
		var table = $("#people-table").DataTable(); 
		if(table.$('tr.info').length!=0){
			rotateConeY(table.$('tr.info').data("id"));
			if($("#box-"+table.$('tr.info').data("id")).data("mode")=='face'){
				$("#box-"+table.$('tr.info').data("id")+"-face").data("face", (parseInt($("#box-"+table.$('tr.info').data("id")+"-face").data("face"))+5)%360);
				var z_val = $("#tr-"+table.$('tr.info').data("id")+" td:nth-child(3)").html().split(",")[1].slice(0,-1);
				$("#tr-"+table.$('tr.info').data("id")+" td:nth-child(3)").html("("+$("#box-"+table.$('tr.info').data("id")+"-face").data("face")+","+z_val+")");
			}else{
				$("#box-"+table.$('tr.info').data("id")+"-face").data("body", (parseInt($("#box-"+table.$('tr.info').data("id")+"-face").data("body"))+5)%360);
				var z_val = $("#tr-"+table.$('tr.info').data("id")+" td:nth-child(4)").html().split(",")[1].slice(0,-1);
				$("#tr-"+table.$('tr.info').data("id")+" td:nth-child(4)").html("("+$("#box-"+table.$('tr.info').data("id")+"-face").data("body")+","+z_val+")");
			}
		}
		return false;
	});
	
	
    jQuery(document).bind('keydown', keymap["rotate_cone_y_less"],function (evt){ 
		var table = $("#people-table").DataTable(); 
		if(table.$('tr.info').length!=0){
			rotateConeY_less(table.$('tr.info').data("id"));
			if($("#box-"+table.$('tr.info').data("id")).data("mode")=='face'){
				$("#box-"+table.$('tr.info').data("id")+"-face").data("face", (parseInt($("#box-"+table.$('tr.info').data("id")+"-face").data("face"))-5)%360);
				var z_val = $("#tr-"+table.$('tr.info').data("id")+" td:nth-child(3)").html().split(",")[1].slice(0,-1);
				$("#tr-"+table.$('tr.info').data("id")+" td:nth-child(3)").html("("+$("#box-"+table.$('tr.info').data("id")+"-face").data("face")+","+z_val+")");
			}else{
				$("#box-"+table.$('tr.info').data("id")+"-face").data("body", (parseInt($("#box-"+table.$('tr.info').data("id")+"-face").data("body"))-5)%360);
				var z_val = $("#tr-"+table.$('tr.info').data("id")+" td:nth-child(4)").html().split(",")[1].slice(0,-1);
				$("#tr-"+table.$('tr.info').data("id")+" td:nth-child(4)").html("("+$("#box-"+table.$('tr.info').data("id")+"-face").data("body")+","+z_val+")");
			}
		}
		return false;
	});


	jQuery(document).bind('keydown', keymap["rotate_cone_z"],function (evt){ 
		var table = $("#people-table").DataTable(); 
		if(table.$('tr.info').length!=0){
			rotateConeZ(table.$('tr.info').data("id"));
			if($("#box-"+table.$('tr.info').data("id")).data("mode")=='face'){
				$("#box-"+table.$('tr.info').data("id")+"-face").data("facez", (parseInt($("#box-"+table.$('tr.info').data("id")+"-face").data("facez"))+5)%360);
				var y_val = $("#tr-"+table.$('tr.info').data("id")+" td:nth-child(3)").html().split(",")[0].substring(1);
				$("#tr-"+table.$('tr.info').data("id")+" td:nth-child(3)").html("("+y_val+","+$("#box-"+table.$('tr.info').data("id")+"-face").data("facez")+")");
			}else{
				$("#box-"+table.$('tr.info').data("id")+"-face").data("bodyz", (parseInt($("#box-"+table.$('tr.info').data("id")+"-face").data("bodyz"))+5)%360);
				var y_val = $("#tr-"+table.$('tr.info').data("id")+" td:nth-child(4)").html().split(",")[0].substring(1);
				$("#tr-"+table.$('tr.info').data("id")+" td:nth-child(4)").html("("+y_val+","+$("#box-"+table.$('tr.info').data("id")+"-face").data("bodyz")+")");
			}
		}
		return false;
	});
    
	jQuery(document).bind('keydown', keymap["rotate_cone_z_less"],function (evt){ 
		var table = $("#people-table").DataTable(); 
		if(table.$('tr.info').length!=0){
			rotateConeZ_less(table.$('tr.info').data("id"));
			if($("#box-"+table.$('tr.info').data("id")).data("mode")=='face'){
				$("#box-"+table.$('tr.info').data("id")+"-face").data("facez", (parseInt($("#box-"+table.$('tr.info').data("id")+"-face").data("facez"))-5)%360);
				var y_val = $("#tr-"+table.$('tr.info').data("id")+" td:nth-child(3)").html().split(",")[0].substring(1);
				$("#tr-"+table.$('tr.info').data("id")+" td:nth-child(3)").html("("+y_val+","+$("#box-"+table.$('tr.info').data("id")+"-face").data("facez")+")");
			}else{
				$("#box-"+table.$('tr.info').data("id")+"-face").data("bodyz", (parseInt($("#box-"+table.$('tr.info').data("id")+"-face").data("bodyz"))-5)%360);
				var y_val = $("#tr-"+table.$('tr.info').data("id")+" td:nth-child(4)").html().split(",")[0].substring(1);
				$("#tr-"+table.$('tr.info').data("id")+" td:nth-child(4)").html("("+y_val+","+$("#box-"+table.$('tr.info').data("id")+"-face").data("bodyz")+")");
			}
		}
		return false;
	});


	// Zoomin and Zoomout
    jQuery(document).bind('keydown', 'Ctrl+a',function (evt){ $(".zoom-out").click(); return false;});
    jQuery(document).bind('keydown', 'Ctrl+z',function (evt){ $(".zoom-in").click(); return false;});
    
    
	jQuery(document).bind('keydown', keymap["add_person"],function (evt){ $("#open-modal-add-person").click(); return false;});
	
    // Show cone
	jQuery(document).bind('keydown', keymap["change_mode"],function (evt){ changePeopleMode("#people-table"); return false;});

	jQuery(document).bind('keydown', keymap["next_frame"],function (evt){ 
		deselectAllBox("#people-table");
		$("#next-frame").click();
		return false;
	});
	
	jQuery(document).bind('keydown', keymap["change_selected_person"],function (evt){ 
		var table = $("#people-table").DataTable();
		if(table.$('tr').length != 0){ 
			if(table.$('tr.info').length == 0){
				table.fnPageChange(0);
				selectBox(table.$('tr:first'));
			}else{
				$old_box = $("#box-"+table.$('tr.info').data("id"));
				if(table.$('tr.info')[0].id == table.$('tr:last')[0].id){
					console.log("found");

					table.fnPageChange(0);
					deselectAllBox("#people-table");
					selectBox(table.$('tr:first'));
				}else if(table.$('tr.info')[0].rowIndex == people_per_page){

					table.fnPageChange(table.fnPagingInfo()["iPage"]+1);
					var tr = table.$('tr:nth-child(1)');
					deselectAllBox("#people-table");
					changeSelectBox("#people-table", tr);
				}else{
					var tr_id = '#people-table tr:nth-child('+(table.$('tr.info')[0].rowIndex+1)+')';
					deselectAllBox("#people-table");
					changeSelectBox("#people-table", $(tr_id));
				}		
			}	
		}

		return false;
	});
	

}
