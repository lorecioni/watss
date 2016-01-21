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
	
	//List of frames
	var timelineFrames = [];
	
	/**
	 * Timeline public methods
	 */
	var methods = {
		showFrames : function(data, current) {
			timelineFrames = data.frames;
			showTimelineFrames(timelineFrames, data.current);
	    },
	    previousFrame : function(){
	    	var prev = $('.timeline-frame.current').data('id') - 1;    	
	    	if($('#timeline-frame-' + prev).data('id') != undefined){
	    		selectFrame(prev);
	    	}	    	
	    },
	    nextFrame : function(){
	    	var next = $('.timeline-frame.current').data('id') + 1;    	
	    	if($('#timeline-frame-' + next).data('id') != undefined){
	    		selectFrame(next);
	    	}	 
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
    			cursorDraggable: false,
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
			
			//Enabling draggable cursor
			if(params.cursorDraggable){
				cursor.draggable({
					containment: "parent",
					drag: function( event, ui ) {
						ui.position.top = ui.originalPosition.top;
						
						if(ui.position.left) console.log(ui.position.left)
					}
				});
			}
			cursorContainer.append(cursor);
			
			//Timeline heading
			var heading = $('<div>')
				.addClass('panel-heading')
				.append('<span>' + params.title + '</span>')
				.append(cursorContainer);
			
			//Timeline body
			var tableBody = '<tbody><tr><th class="timeline-people col-md-2" scope="row">' +
				'</th><td class="timeline-frames col-md-10"><div class="timeline-frames-container"></div></td>' + 
				'</tr></tbody>'; 
			
			var table = $('<table></table>')
				.addClass('table table-bordered')
				.append(tableBody)
				
			panel.append(heading);
			panel.append(table);
			$(this).html(panel);	
		});
		
		//Enable mouse scrolling for navigating timeline
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
				
				updateCursor();
			});
		}
		
		//Displaying loading gif
		var loading = $('<img></img>')
			.addClass('timeline-loading')
			.attr('src', '../img/loading.gif')
			.attr('alt', 'loading');
		$('.timeline-frames').append(loading);
		
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
				.attr('id', 'timeline-frame-' + frames[i].number)
				.attr('data-id', frames[i].number)
				.append('<div class="timeline-frame-indicator"></div>')
				.append('<span class="timeline-frame-number">' + frames[i].number + '</span>');
			if(frames[i].number == current){
				frame.addClass('current');
				loadPeople(frames[i].people)
			}
			
			//On click change frame
			frame.click(function(){
				selectFrame($(this).data('id'));
			});
				
			$('.timeline-frames-container').append(frame);
		}
		$('.timeline-loading').remove();
		updateCursor();
	}
	
	//Loading current frame people
	function loadPeople(people){
		$('.timeline-people-list').empty();
		if(people.length > 0){
			var list = $('<ul></ul>')
				.addClass('timeline-people-list list-group');
			for ( var i in people) {
				var person = $('<li></li>')
					.addClass('timeline-person list-group-item')
					.attr('data-id', people[i].id)
					.attr('id', 'timeline-person-' + people[i].id)
					.append('<div><a href="#" class="popover-img" data-container="body" href="#" data-toggle="popover" data-placement="right" data-content=\'' 
							+ '<img src="../img/real_people/'+ people[i].id + '_100.jpg">' + '\'>' + people[i].id + '</a></div>')
					.append('<div id="color-' + people[i].id +'" class="thumbnail pickthb" '
						+ 'style="background-color:'+ people[i].color + '"></div>');
				
				person.click(function(){
					selectPerson({id: $(this).data('id'), color: $(this).find('.pickthb').css('background-color')});
				});
				
				list.append(person);
			} 
			$('.timeline-people').html(list);
		} else {
			$('.timeline-people').html('<div class="people-list-error">No people in this frame</div>');
		}

	}
	
	//Selects frame with the given id in timeline
	function selectFrame(id){
		$('.timeline-frame.current').removeClass('current');
		$('#timeline-frame-' + id).addClass('current');
		updateCursor();
		methods.onFrameSelected(id);
		loadPeople(timelineFrames[id - 1].people)
	}
	
	//Updates cursor position pointing to the current frame
	function updateCursor(){
		$('.timeline-cursor').css({
			left: $('.timeline-frame.current').parent().position().left 
						+ $('.timeline-frame.current').position().left
		});
	}
	
	//Displaying annotation duration for a selected person
	function selectPerson(person){
		if(!$('#timeline-person-' + person.id).hasClass('selected')){
			$('.timeline-person.selected').removeClass('selected');
			$('#timeline-person-' + person.id).addClass('selected');
			$('.timeline-annotation').remove();
			
			var matches = [];
			for ( var i in timelineFrames) {
				if(timelineFrames[i].people.length > 0){
					for ( var j in timelineFrames[i].people) {
						if(timelineFrames[i].people[j].id == person.id){
							matches.push(parseInt(i) + 1);
						}
					}
				}
			}
			
			var intervals = [];
			var start = matches[0];
			var end = 0;
			for (var k = 0; k < matches.length; k++){
				if(matches[k] + 1 != matches[k + 1]){
					end = matches[k];
					intervals.push({start: start, end: end});
					start = matches[k + 1];
				}
			}
			
			for ( var i in intervals) {
				var over = $('<div></div>')
					.addClass('timeline-annotation')
					.attr('id', 'timeline-annotation-' + person.id)
					.css({
						'background-color': person.color,
						'width' : $('#timeline-frame-' + intervals[i].start).width() * (intervals[i].end - intervals[i].start)
								+ $('#timeline-frame-' + intervals[i].start).width(),
						'left' : $('#timeline-frame-' + intervals[i].start).position().left,
						'top' : 20 
					});
				$('.timeline-frames-container').prepend(over);
			}
			
			console.log(matches)
			console.log(intervals)
			
		} else {
			$('.timeline-person.selected').removeClass('selected');
			$('.timeline-annotation').remove();
		}
	}
	
})(jQuery);
