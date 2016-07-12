
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
			$('.video-overlay').remove();
			var bb = $('.bb.init').first();
			$('.init').removeClass('init');
			bb.click()
		}	
	});
	
	/**
	 * Enabling bounding box creation, bb is attached to the pointer
	 * until click on a frame position
	 */
	$(document).on("mousedown mouseup mousemove mouseleave", '.video-overlay', function(e){
		if($('.bb.init').length > 0){
			var geometryEnabled = $('#enable-geometry').is(':checked');
			
			switch (e.type){
				case 'mousedown':
					if(!geometryEnabled){
						$('.video-overlay').append('<div id="bb-selection"></div>');
						var x = e.offsetX;
						var y = e.offsetY;
						var color = $('.bb.init').first().css('border-color');
						$('.video-overlay #bb-selection').css({
							'left': x, 
							'top': y,
							'border': '2px solid ' + color
							});
					}
					break;
					
				case 'mouseup':			
					if(!geometryEnabled){
						var x = $('#bb-selection').css('left');
						var y = $('#bb-selection').css('top');
						var w = $('#bb-selection').css('width');
						var h = $('#bb-selection').css('height');
						var bb = $('.bb.init').first();
						var bbV = $('.bbV.init').first();
						var face = $('.face.init').first();
						
						bb.css({
							'top': y,
							'left': x,
							'width' : w,
							'height': h
						});
						bbV.css({
							'top': y,
							'left': x
						});
						face.css({
							'top': y,
							'left': x
						});
						bb.first().click();
						$('.init').removeClass('init');
						updateBoundingBoxesData(bb, bbV, face)
						$(this).remove();
					}
					break;
			
				case 'mouseleave':
					$(this).remove();
					break;
					
					
				case 'mousemove':
					if(geometryEnabled){
						//Attach bounding box to the pointer
						var addedId = $('.bb.init').first().data('id');
						var x = e.pageX - $('#video-wrapper').offset().left;
						var y = e.pageY - $('#video-wrapper').offset().top;
						
						$('#video-wrapper #box-' + addedId).css({
							'left': x,
							'top': y
						});
						$('#video-wrapper #box-' + addedId + '-bbV').css({
							'left': x,
							'top': y
						});
						$('#video-wrapper #box-' + addedId + '-face').css({
							'left': x,
							'top': y
						});
					} else {
				        var startX = parseInt($('#bb-selection').css('left'));
				        var startY = parseInt($('#bb-selection').css('top'));
				  
				        var width = e.offsetX - startX;
				        var height = e.offsetY - startY;
				            
				        $('#bb-selection').css('width', width);
				        $('#bb-selection').css('height', height);
					}
					break;
			}
		}
	});
	
	/** Enabling bounding box scaling with mousewheel **/
	$(document).on('mousewheel', '.bb-selected', function(e){
		e.preventDefault();
		var videoBoxWidth = $('#video-box').width();
		var videoBoxHeight = $('#video-box').height();
		
		var bb = $('.bb.bb-selected').first();
		var bbV = $('.bbV.bb-selected').first();
		var face = $('.face.bb-selected').first();
		
		var delta = 2;
		if(Math.abs(e.deltaY) != 0){
			if(e.deltaY > 0){		
				//Check if box is going out box
				if(parseFloat(bb.css('left')) + parseFloat(bb.css('width')) < videoBoxWidth
					&& parseFloat(bb.css('top')) + parseFloat(bb.css('height')) < videoBoxHeight){
					
					bb.css('left', parseFloat(bb.css('left')) - delta);
					bb.css('width', parseFloat(bb.css('width')) + 2 * delta);
					bb.css('top', parseFloat(bb.css('top')) - delta);
					bb.css('height', parseFloat(bb.css('height')) + 2 * delta);
				}
			} else {
				bb.css('left', parseFloat(bb.css('left')) + delta);
				bb.css('width', parseFloat(bb.css('width')) - 2 * delta);
				bb.css('top', parseFloat(bb.css('top')) + delta);
				bb.css('height', parseFloat(bb.css('height')) - 2 * delta);
			}
			
			bbV.css({'top': bb.css('top'), 'left': bb.css('left'), 'width': bb.css('width'), 'height': bb.css('height')});
			face.css({'top': bb.css('top'), 'left': bb.css('left'), 'width': bb.css('width'), 'height': bb.css('height')});
		}
		updateBoundingBoxesData(bb, bbV, face);
	});
	
	$(document).bind('keydown', 'delete backspace', function (e){
		if($('.bb-selected').length > 0){
			e.preventDefault();
			var sel = $('.bb-selected').first().data('id');
			$('#people-table tr[data-id=' + sel + '] .remove-person').click();
			$('.timeline').timeline('removePerson', sel);
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
			if(!bb.hasClass('bb-selected')){
				bb.click();
			}
			bbV.css({
				'left': ui.position.left,
				'top': ui.position.top
			});
			bbF.css({
				'left': ui.position.left,
				'top': ui.position.top
			});
			updateBoundingBoxesData(bb, bbV, bbF);
		}
  	});

	//Set global bounding box resizable
	bb.resizable({ 
		containment: "parent",
		resize: function(event, ui){
			if(!bb.hasClass('bb-selected')){
				bb.click();
			}
			bbV.css({
				'top': bb.position().top,
				'left': bb.position().left
			})
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
			updateBoundingBoxesData(bb, bbV, bbF);
		},
		alsoResize : bbF
	});
	
	//Set bounding box visible draggable
	bbV.draggable({ 
		containment: "parent",
		drag: function( event, ui ) {
			bb.css({
				'left': ui.position.left,
				'top': ui.position.top
			});
			bbF.css({
				'left': ui.position.left,
				'top': ui.position.top
			});
			updateBoundingBoxesData(bb, bbV, bbF);
		}
  	});
	
	//Set bounding box visible resizable
	bbV.resizable({
		containment: "parent", 
		resize: function(event, ui){
			bbV.css({
				'top': bb.position().top,
				'left': bb.position().left
			})
			//Contaning resize
			if(bb.width() <= bbV.width()){
				bbV.width(bb.width());
			}
			if(bb.height() <= bbV.height()){
				bbV.height(bb.height())
			}
			updateBoundingBoxesData(bb, bbV, bbF);
		},
	});
}

function updateBoundingBoxesData(bb, bbV, bbF){
	/** updates bounding box dimension wrt zoom and pan **/	
	var videoBoxWidth = $('#video-box').width();
	var videoBoxHeight = $('#video-box').height();
	var videoWidth = $("#video-box").data("width");
	var videoHeight = $("#video-box").data("height");
	var defactorWidth = videoWidth/videoBoxWidth;
	var defactorHeight = videoHeight/videoBoxHeight;
	
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
	
	bb.data('top',  (parseFloat(bb.css('top')) + top) / scale);
	bb.data('left',  (parseFloat(bb.css('left')) + left) / scale);
	bb.data('width',  parseFloat(bb.css('width')) / scale);
	bb.data('height',  parseFloat(bb.css('height')) / scale);
	
	bbV.data('top',  (parseFloat(bbV.css('top')) + top) / scale);
	bbV.data('left',  (parseFloat(bbV.css('left')) + left) / scale);
	bbV.data('width',  parseFloat(bbV.css('width')) / scale);
	bbV.data('height',  parseFloat(bbV.css('height')) / scale);
	
	bbF.data('top',  (parseFloat(bb.css('top')) + top) / scale);
	bbF.data('left',  (parseFloat(bb.css('left')) + left) / scale);
	bbF.data('width',  parseFloat(bb.css('width')) / scale);
	bbF.data('height',  parseFloat(bb.css('height')) / scale);
}
