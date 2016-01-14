function setDragNResize(first, second) {
	
	/*$(first).draggable({ 
		containment: "parent",
		drag: function( event, ui ) {
			if(!$("#tr-"+$(this).data("id")).hasClass('info')){
				deselectAllBox("#people-table");
				changeSelectBox("#people-table",$(this));
			}
			$(second).offset({left: ui.offset.left + $(second).position().left - ui.originalPosition.left, 
							  top: ui.offset.top + $(second).position().top - ui.originalPosition.top });
			ui.originalPosition.left = ui.position.left;
			ui.originalPosition.top = ui.position.top;
		}
  	});*/

	$(first).resizable({ 
		containment: "parent",
		resize: function(event, ui){
			var w = ui['size']['width'];
			var h = ui['size']['height'];
			var l = $(first).offset().left;
			var t = $(first).offset().top;
			var w_c = $(second).outerWidth();
			var h_c = $(second).outerHeight();
			var l_c = $(second).offset().left;
			var t_c = $(second).offset().top;

			if (w_c + l_c > w + l - 4) {
				$(second).offset({top: $(second).offset().top, left: w + l - w_c});
			}
			if (h_c + t_c > h + t - 4) {
				$(second).offset({top: t + h - h_c, left: $(second).offset().left});
			}
			if (w_c > w-4) {
				$(second).width(w - 4);
				$(second).offset({top: $(second).offset().top, left: $(first).offset().left});
			}
			if (h_c > h-4) {
				$(second).height(h - 4);
				$(second).offset({top: $(first).offset().top, left: $(second).offset().left});
			}
		} 
	});
	
	/*$(second).resizable({ containment: "parent" });
	$(second).draggable({ containment: "parent" });*/
}
