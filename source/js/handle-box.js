
/**
 * Set draggable and resizable bounding box
 * @param bb : bounding box (jQuery obj)
 * @param bbV : bounding box visible (jQuery obj)
 * @param bbF : bounding box face (jQuery obj)
 */


(function() {

	$('#video-box').click(function(e){
		var geometryEnabled = $('#enable-geometry').is(':checked');
		if(geometryEnabled){
			e.preventDefault();
			$('#video-overlay').remove();
			var bb = $('.bb.init').first();
			$('.init').removeClass('init');
			bb.click()
		}	
	});
	
	/**
	 * Enabling bounding box creation, bb is atteched to the pointer
	 * until click on a frame position
	 */
	$('#video-box').bind( "mousedown mouseup mousemove mouseleave", function(e) {
		if($('.bb.init').length > 0){
			var geometryEnabled = $('#enable-geometry').is(':checked');
			
			switch (e.type){
				case 'mousedown':
					if(!geometryEnabled){
						if(!$('#video-overlay').is(':visible')){
							$(this).append('<div id="video-overlay"></div>');
						}
						$('#video-overlay').append('<div id="bb-selection"></div>');
						var x = e.offsetX;
						var y = e.offsetY;
						var color = $('.bb.init').first().css('border-color');
						$('#video-overlay #bb-selection').css({
							'left': x, 
							'top': y,
							'border': '2px solid ' + color
							});
					}
					break;
					
				case 'mouseup':			
					if(!geometryEnabled){
						if($('#video-overlay').is(':visible')){
							var x = $('#bb-selection').css('left');
							var y = $('#bb-selection').css('top');
							var w = $('#bb-selection').css('width');
							var h = $('#bb-selection').css('height');
							
							$('.bb.init').first().css({
								'top': y,
								'left': x,
								'width' : w,
								'height': h
							});
							$('.bbV.init').first().css({
								'top': y,
								'left': x
							});
							$('.face.init').first().css({
								'top': y,
								'left': x
							});
							$('.bb.init').first().click();
							$('.init').removeClass('init');
						}
						$('#video-overlay').remove();
					}
					break;
			
				case 'mouseleave':
					$('#video-overlay').remove();
					break;
					
					
				case 'mousemove':
					if(geometryEnabled){
						//Attach bounding box to the pointer
						var addedId = $('.bb.init').first().data('id');
						var x = e.pageX - $('#video-box').offset().left;
						var y = e.pageY - $('#video-box').offset().top;
						
						$('#video-box #box-' + addedId).css({
							'left': x,
							'top': y
						});
						$('#video-box #box-' + addedId + '-bbV').css({
							'left': x,
							'top': y
						});
						$('#video-box #box-' + addedId + '-face').css({
							'left': x,
							'top': y
						});
					} else {
						if ($('#video-overlay').is(':visible')) {
				            var startX = parseInt($('#bb-selection').css('left'));
				            var startY = parseInt($('#bb-selection').css('top'));
				  
				            var width = e.offsetX - startX;
				            var height = e.offsetY - startY;
				            
				            $('#bb-selection').css('width', width);
				            $('#bb-selection').css('height', height);
				        }
					}
					break;
			}
		}
	});
	
	/** Enabling bounding box scaling with mousewheel **/
	$('#video-box').on('mousewheel', function(e){
		if($('.bb.bb-selected').length > 0){
			e.preventDefault();
			var sel = $(e.toElement);
			if(sel.hasClass('bb-selected')){
				var delta = 2;
				if(Math.abs(e.deltaY) != 0){
					if(e.deltaY > 0){
						$('.bb-selected').css('left', parseFloat(sel.css('left')) - delta);
						$('.bb-selected').css('width', parseFloat(sel.css('width')) + 2 * delta);
						$('.bb-selected').css('top', parseFloat(sel.css('top')) - delta);
						$('.bb-selected').css('height', parseFloat(sel.css('height')) + 2 * delta);
					} else {
						$('.bb-selected').css('left', parseFloat(sel.css('left')) + delta);
						$('.bb-selected').css('width', parseFloat(sel.css('width')) - 2 * delta);
						$('.bb-selected').css('top', parseFloat(sel.css('top')) + delta);
						$('.bb-selected').css('height', parseFloat(sel.css('height')) - 2 * delta);
					}
				}
			}
		}
	});
	

	$(document).bind('keydown', 'esc', function (e){
		if($('.bb.init').length > 0){
			var addedId = $('.bb.init').first().data('id');
			$('#people-table tr[data-id=' + addedId + '] .remove-person').click();
		}
	});
})();

function setDragResize(bb, bbV, bbF) {
	
	//Set bounding box draggable
	bb.draggable({ 
		containment: "parent",
		drag: function( event, ui ) {
			if(!bb.hasClass('selected')){
				bb.click();
			}		
			bbV.offset({left: ui.offset.left + bbV.position().left - ui.originalPosition.left, 
							  top: ui.offset.top + bbV.position().top - ui.originalPosition.top });
			bbF.offset({left: ui.offset.left + bbF.position().left - ui.originalPosition.left, 
				  top: ui.offset.top + bbF.position().top - ui.originalPosition.top });
			ui.originalPosition.left = ui.position.left;
			ui.originalPosition.top = ui.position.top;
			
			/** updates bounding box dimension wrt zoom and pan **/
			bb.attr('data-top', bb.css('top'));
			bb.attr('data-left', bb.css('left'));
			bb.attr('data-width', bb.css('width'));
			bb.attr('data-height', bb.css('height'));
		}
  	});

	//Set global bounding box resizable
	bb.resizable({ 
		containment: "parent",
		resize: function(event, ui){
			if(!bb.hasClass('selected')){
				bb.click();
			}	
			var border = bb.outerWidth() - bb.width();
			//Updating visible bounding box
			if(bb.width() < bbV.width()){
				bbV.width(bb.width() - border);
			}
			if(bb.height() < bbV.height()){
				bbV.height(bb.height() - border)
			}
			//Updating face bounding box
			bbF.width(bb.width());
			bbF.height(bb.height());
		},
		alsoResize : bbF
	});
	
	//Set bounding box visible draggable
	bbV.draggable({ 
		containment: "parent",
		drag: function( event, ui ) {
			bb.offset({left: ui.offset.left + bb.position().left - ui.originalPosition.left, 
							  top: ui.offset.top + bb.position().top - ui.originalPosition.top });
			bbF.offset({left: ui.offset.left + bbF.position().left - ui.originalPosition.left, 
				  top: ui.offset.top + bbF.position().top - ui.originalPosition.top });
			ui.originalPosition.left = ui.position.left;
			ui.originalPosition.top = ui.position.top;
			
			console.log('bbv');
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
