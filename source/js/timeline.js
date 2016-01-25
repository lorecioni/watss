/**
 * jQuery plugin for displaying frames timeline. 
 * Designed for WATSS annotation tool
 * 
 * @param title: title displayed above timeline
 * @param limit: query limit for retrieving frame from the current (if undefined there is no limit)
 * @param mouseScrolling: true/false, enabling/disabling mouse scrolling for navigating timeline
 * @param cursorDraggable: enabling/disabling draggable cursor (not yet finished)
 * @param loadedFrames: number of frames loaded in timeline (if undefined there all frames are preloaded)
 * @param getFrames: function for getting frames
 * @param onFrameSelected: callback function after a frame is selected in timeline
 * 
 * @author Lorenzo Cioni
 */

(function($) {
	
	var config = {
		title: 'Timeline',
		limit: undefined,
		cursorDraggable: false,
		mouseScrolling: true,
		loadedFrames: undefined,		
	}
	
	//List of frames
	var timelineFrames = [];
	//Preloaded frames in timeline
	var currentFrame;
	var loadedFrames;
	var displayedFrames = [];
	
	/**
	 * Timeline public methods
	 */
	var methods = {
		/**
		 * Display timeline frames
		 */
		showFrames : function(data) {
			timelineFrames = data.frames;
			currentFrame = data.current;
			if(loadedFrames != undefined){
				if(currentFrame < loadedFrames/2){
					displayedFrames = timelineFrames.slice(0, loadedFrames);
				} else {
					displayedFrames = timelineFrames.slice(currentFrame - loadedFrames/2, currentFrame + loadedFrames/2);
				}
				initTimelineFrames(displayedFrames);
			} else {
				initTimelineFrames(timelineFrames);
			}
	    },
	    /**
		 * Go to the previous frame
		 */
	    previousFrame : function(){
	    	var prev = currentFrame - 1;    	
	    	if($('#timeline-frame-' + prev).data('id') != undefined){
	    		selectFrame(prev);
	    		currentFrame = prev;
	    	}
	    },
	    /**
		 * Go to the next frame
		 */
	    nextFrame : function(){
	    	var next = currentFrame + 1;    	
	    	if($('#timeline-frame-' + next).data('id') != undefined){
	    		selectFrame(next);
	    		currentFrame = next;
	    	}	 
	    },
	    /**
		 * Function handler on frame selected
		 */
	    onFrameSelected : function(id){},
	    /**
		 * Function handler on person selected
		 */
	    selectPerson : function(person){
	    	selectPerson(person);
	    },
	    /**
		 * Deselect all people in timeline, removing annotation
		 */
	    deselectAll : function(){
	    	deselectAll();
	    },
	    /**
		 * Adding person to the current frame in timeline
		 */
	    addPerson : function(person){
	    	timelineFrames[currentFrame - 1].people.push(person);
	    	loadPeople(timelineFrames[currentFrame - 1].people);
	    },
	    /**
		 * Remove person from the current frame in timeline with the given id
		 */
	    removePerson : function(id){
	    	for ( var i = 0; i < timelineFrames[currentFrame - 1].people.length; i++) {
				if(timelineFrames[currentFrame - 1].people[i].id == id){
					timelineFrames[currentFrame - 1].people.splice(i, 1);
				}
			}
	    	$('.timeline-annotation-' + id).remove();
	    	loadPeople(timelineFrames[currentFrame - 1].people)
	    }
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
    			title: config.title,
    			limit: config.limit,
    			cursorDraggable: config.cursorDraggable,
    			mouseScrolling: config.mouseScrolling,
    			loadedFrames: config.loadedFrames,
    			getFrames: function(){},
    			onFrameSelected: function(){}
    		}, value);
    		loadedFrames = params.loadedFrames;
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
				'<div class="scrollable"></div>' +
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
				var direction = '';
				var offset = $('.timeline-frames-container').position().left;
				var containerWidth = $('.timeline-frames-container').width();
				if(Math.abs(e.deltaX) != 0){
					if(e.deltaX > 0){
						direction = 'right';
					} else {
						direction = 'left';
					}
					offset += e.deltaX; //If necessary multiply for e.deltaFactor
				} else if(Math.abs(e.deltaY) != 0){
					if(e.deltaY > 0){
						direction = 'right';
					} else {
						direction = 'left';
					}
					offset += e.deltaY; //If necessary multiply for e.deltaFactor
				}
				//Limiting scolling
				if(offset > 0)
					offset = 0;
				if(offset < - (containerWidth - 25)) 
					offset = - (containerWidth - 25);
				
				$('.timeline-frames .timeline-frames-container').css({
					left: offset
				})
				
				if(loadedFrames != undefined){
					extendTimelineFrame(direction);
				}		
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
	function initTimelineFrames(frames){
		$('.timeline-frames-container').empty();
		for (var i in frames) {
			var frame = $('<div></div>')
				.addClass('timeline-frame')
				.attr('id', 'timeline-frame-' + frames[i].id)
				.attr('data-id', frames[i].id)
				.append('<div class="timeline-frame-indicator"></div>')
				.append('<span class="timeline-frame-number">' + frames[i].id + '</span>');
			if(frames[i].id == currentFrame){
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
		//Centering timeline on current frame
		if(currentFrame > 20){
			$('.timeline-frames-container').css('left', - $('.timeline-frame.current').position().left 
					+ $('.timeline-frames').width()/2);
		}
		updateCursor();
	}
	
	//Extend timeline frames
	function extendTimelineFrame(direction){
		var frame;
		var id;
		var start = displayedFrames[0].id;
		var end = displayedFrames[displayedFrames.length - 1].id;
		if(direction == 'right'){
			frame = displayedFrames[0];
			if (frame.id <= 1) return;
			$('#timeline-frame-' + end).remove();
			id = frame.id - 1;
			start = start - 1;
			end = end - 1;
		} else {
			frame = displayedFrames[displayedFrames.length - 1];
			if(frame.id >= timelineFrames[timelineFrames.length - 1].id) return;
			$('#timeline-frame-' + start).remove();
			id = frame.id + 1;
			start = start + 1;
			end = end + 1;
		}
		var frame = $('<div></div>')
			.addClass('timeline-frame')
			.attr('id', 'timeline-frame-' + id)
			.attr('data-id', id)
			.append('<div class="timeline-frame-indicator"></div>')
			.append('<span class="timeline-frame-number">' + id + '</span>');
		
		if(id == currentFrame){
			frame.addClass('current');
			updateCursor();
		}
		
		//On click change frame
		frame.click(function(){
			selectFrame($(this).data('id'));
		});
			
		if(direction == 'right'){
			$('.timeline-frames-container').prepend(frame);
		} else {
			$('.timeline-frames-container').append(frame);
		}
		
		//Updating displayed frames list
		displayedFrames = timelineFrames.slice(start - 1, end);
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
			$('.timeline-people .scrollable').html(list);
		} else {
			$('.timeline-people .scrollable').html('<div class="people-list-error">No people in this frame</div>');
		}

	}
	
	//Selects frame with the given id in timeline
	function selectFrame(id){
		$('.timeline-frame.current').removeClass('current');
		$('#timeline-frame-' + id).addClass('current');
		$('.timeline-annotation').remove();
		currentFrame = id;
		updateCursor();
		methods.onFrameSelected(id);
		loadPeople(timelineFrames[id - 1].people)
	}
	
	//Updates cursor position pointing to the current frame
	function updateCursor(){
		if($('.timeline-frame.current').parent().length > 0){
			$('.timeline-cursor').css({
				left: $('.timeline-frame.current').parent().position().left 
							+ $('.timeline-frame.current').position().left
			});
		} else {
			$('.timeline-cursor').css({
				left: -100
			});
		}

	}
	
	//Displaying annotation duration for a selected person
	function selectPerson(person){
		
		if($('#timeline-person-' + person.id).length == 0){
			methods.addPerson(person);
		}

			//Person is already present in timeline
			if(!$('#timeline-person-' + person.id).hasClass('selected')){
				$('#timeline-person-' + person.id).addClass('selected');
				$('.timeline-annotation-' + person.id).remove();
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
				
				var annotationContainer = $('<div></div>')
					.addClass('timeline-annotation-container')
					.addClass('timeline-annotation-' + person.id);
				
				for ( var i in intervals) {
					var over = $('<div></div>')
						.addClass('timeline-annotation')
						.css({
							'background-color': person.color,
							'width' : $('#timeline-frame-' + intervals[i].start).width() * (intervals[i].end - intervals[i].start)
									+ $('#timeline-frame-' + intervals[i].start).width(),
							'left' : $('#timeline-frame-' + intervals[i].start).position().left,
							'top' :  10 + $('.timeline-annotation-container').length * 20
						});
					annotationContainer.append(over);
				}
				$('.timeline-frames-container').prepend(annotationContainer);
			} else {
				$('#timeline-person-' + person.id).removeClass('selected');
				$('.timeline-annotation-' + person.id).remove();
			}
		

	}
	
	//Deselect all people
	function deselectAll(){
		$('.timeline-person').removeClass('selected');
		$('.timeline-annotation-container').remove();
	}
	
})(jQuery);
