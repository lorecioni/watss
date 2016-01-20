/**
 * jQuery plugin for displaying frames timeline. 
 * Designed for WATSS annotation tool
 * 
 * @param title: title displayed above timeline
 * @param limit: query limit for retrieving frame from the current
 * @param mouseScrolling: true/false, enabling/disabling mouse scrolling for navigating timeline
 * @param getFrames: function for getting frames
 * @param onFrameSelected: callback function after a frame is selected in timeline
 * 
 * @author Lorenzo Cioni
 */

(function($) {
	
	/**
	 * Timeline public methods
	 */
	var methods = {
		showFrames : function(data, current) {
			showTimelineFrames(data.frames, data.current);
	    },
	    onFrameSelected : function(id){}
	};
	
	/**
	 * Timeline plugin
	 * @param value: can be a method or an object for initializing params
	 */
	$.fn.timeline = function(value) {
		
		if (methods[value]) {
			//Call public method
            return methods[ value ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof value === 'object' || ! value ) {
            //Setting default parameters
    		params = $.extend({
    			title: 'Timeline',
    			limit: 100,
    			mouseScrolling: true,
    			getFrames: function(){},
    			onFrameSelected: function(){}
    		}, value);
    		methods.onFrameSelected = params.onFrameSelected;
        } else {
            $.error( 'Method ' +  value + ' does not exist on timeline' );
        }    

		//Build timeline
		this.each(function() {
			var panel = $('<div></div>')
				.addClass('timeline panel panel-default');
			
			//Timeline cursor
			var cursorContainer = $('<div></div>')
				.addClass('timeline-cursor-container');	
			
			var cursor = $('<span></span>')
				.addClass('timeline-cursor glyphicon glyphicon-arrow-down');
			
			cursor.draggable({
				containment: "parent",
				drag: function( event, ui ) {
					ui.position.top = ui.originalPosition.top;
				}
			});
			
			cursorContainer.append(cursor);
			
			//Timeline heading
			var heading = $('<div>')
				.addClass('panel-heading')
				.append('<span>' + params.title + '</span>')
				.append(cursorContainer);
			
			//Timeline body
			var tableBody = '<tbody><tr><th class="col-md-2" scope="row">' +
				'<ul class="list-group"><li class="list-group-item">1</li><li class="list-group-item">2</li></ul>' +
				'</th><td class="timeline-frames col-md-10"><div class="timeline-frames-container"></div></td>' + 
				'</tr></tbody>'; 
			
			var table = $('<table></table>')
				.addClass('table table-bordered')
				.append(tableBody)
				
			panel.append(heading);
			panel.append(table);
			$(this).html(panel);	
		});
		
		if(params.mouseScrolling){
			$('.timeline .timeline-frames').on('mousewheel', function(e) {
				e.preventDefault();
				var offset = $('.timeline-frames-container').position().left;
				var containerWidth = $('.timeline-frames-container').width();
				if(Math.abs(e.deltaX) != 0){
					offset += e.deltaX * e.deltaFactor;
				} else if(Math.abs(e.deltaY) != 0){
					offset += e.deltaY * e.deltaFactor;
				}
				//Limiting scolling
				if(offset > 0)
					offset = 0;
				if(offset < - (containerWidth - 25)) 
					offset = - (containerWidth - 25);
				
				$('.timeline-frames .timeline-frames-container').css({
					left: offset
				})
			});
		}
		
		//Getting frames
		params.getFrames(params.limit);
		
		return this;
	};
	
	//Build and display timeline frames
	function showTimelineFrames(frames, current){
		$('.timeline-frames-container').empty();
		for (var i in frames) {
			var frame = $('<div></div>')
				.addClass('timeline-frame')
				.attr('data-id', frames[i].number)
				.append('<div class="timeline-frame-indicator"></div>')
				.append('<span class="timeline-frame-number">' + frames[i].number + '</span>');
			if(frames[i].number == current){
				frame.addClass('current');
			}
			
			frame.click(function(){
				methods.onFrameSelected($(this).data('id'));
			});
			
			$('.timeline-frames-container').append(frame);
		}
	}
})(jQuery);
