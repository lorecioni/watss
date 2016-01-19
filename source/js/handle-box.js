
/**
 * Set draggable and resizable bounding box
 * @param bb : bounding box (jQuery obj)
 * @param bbV : bounding box visible (jQuery obj)
 * @param bbF : bounding box face (jQuery obj)
 */

function setDragResize(bb, bbV, bbF) {
	
	//Set bounding box draggable
	bb.draggable({ 
		containment: "parent",
		drag: function( event, ui ) {
			/*if(!$("#tr-"+$(this).data("id")).hasClass('info')){
				deselectAllBox("#people-table");
				changeSelectBox("#people-table",$(this));
			}*/
			bbV.offset({left: ui.offset.left + bbV.position().left - ui.originalPosition.left, 
							  top: ui.offset.top + bbV.position().top - ui.originalPosition.top });
			bbF.offset({left: ui.offset.left + bbF.position().left - ui.originalPosition.left, 
				  top: ui.offset.top + bbF.position().top - ui.originalPosition.top });
			ui.originalPosition.left = ui.position.left;
			ui.originalPosition.top = ui.position.top;
		}
  	});

	//Set global bounding box resizable
	bb.resizable({ 
		containment: "parent",
		resize: function(event, ui){
			var border = bb.outerWidth() - bb.width();
			//Updating visible bounding box
			if(bb.width() < bbV.width()){
				bbV.width(bb.width() - border);
			}
			if(bb.height() < bbV.height()){
				bbV.height(bb.height() - border)
			}
			//Updating face bounding box
			//FIXME to be updated
			bbF.width(bb.width());
			bbF.height(bb.height());
		},
		alsoResize : bbF
	});
	
	//Set bounding box visible draggable
	bbV.draggable({ 
		containment: "parent",
		drag: function( event, ui ) {
			/*if(!$("#tr-"+$(this).data("id")).hasClass('info')){
				deselectAllBox("#people-table");
				changeSelectBox("#people-table",$(this));
			}*/
			bb.offset({left: ui.offset.left + bb.position().left - ui.originalPosition.left, 
							  top: ui.offset.top + bb.position().top - ui.originalPosition.top });
			bbF.offset({left: ui.offset.left + bbF.position().left - ui.originalPosition.left, 
				  top: ui.offset.top + bbF.position().top - ui.originalPosition.top });
			ui.originalPosition.left = ui.position.left;
			ui.originalPosition.top = ui.position.top;
		}
  	});
	
	//Set bounding box visible resizable
	bbV.resizable({
		containment: "parent", 
		resize: function(event, ui){
			//Contaning resize
			if(bb.width() <= bbV.width()){
				bbV.width(bb.width());
			}
			if(bb.height() <= bbV.height()){
				bbV.height(bb.height())
			}
		},
	});
}
