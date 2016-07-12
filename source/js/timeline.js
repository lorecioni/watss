/**
 * jQuery plugin for displaying frames timeline. 
 * Designed for WATSS annotation tool
 * 
 * @param title: title displayed above timeline
 * @param mouseScrolling: true/false, enabling/disabling mouse scrolling for navigating timeline
 * @param getFrames: function for getting frames
 * @param onFrameSelected: callback function after a frame is selected in timeline
 * 
 * @author Lorenzo Cioni
 */

(function($) {
	
	var config = {
		title: 'Timeline',
		mouseScrolling: true,
		loadedFrames: 100
	}
	
	//List of frames
	var timelineFrames = [];
	//Preloaded frames in timeline
	var displayStartIndex = 0;
	var displaEndIndex = 0;
	//Current frame number in timeline
	var currentFrame;
	//Current intervals displayed on timeline
	var currentPerson;
	var currentIntervals;

	//Loading image over timeline
	var loading = $('<img></img>')
		.addClass('timeline-loading')
		.attr('src', '../img/loading.gif')
		.attr('alt', 'loading');
	
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
			if(data.current < config.loadedFrames/2){
				displayStartIndex = 0;
				displayEndIndex = config.loadedFrames;
			} else {
				displayStartIndex = currentFrame - config.loadedFrames/2;
				displayEndIndex = currentFrame + config.loadedFrames/2;
			}		
			showFrames();
		},
	    /**
		 * Go to the previous frame
		 */
	    previousFrame : function(){
	    	var prev = currentFrame - 1;  
	    	extendTimelineFrame('right');
	    	if($('#timeline-frame-' + prev).data('id') != undefined){
	    		selectFrame(prev);
	    		currentFrame = prev;
	    	} else {
	    		currentFrame = prev;
	    		if(data.current < config.loadedFrames/2){
					displayStartIndex = 0;
					displayEndIndex = config.loadedFrames;
				} else {
					displayStartIndex = currentFrame - config.loadedFrames/2;
					displayEndIndex = currentFrame + config.loadedFrames/2;
				}
	    		showFrames();
	    		selectFrame(prev);
	    	}
	    	centerTimeline();
	    },
	    /**
		 * Go to the next frame
		 */
	    nextFrame : function(){
	    	var next = currentFrame + 1;   
	    	extendTimelineFrame('left')
	    	if($('#timeline-frame-' + next).data('id') != undefined){
	    		selectFrame(next);
	    		currentFrame = next;
	    	} else {
	    		currentFrame = next;
	    		if(data.current < config.loadedFrames/2){
					displayStartIndex = 0;
					displayEndIndex = config.loadedFrames;
				} else {
					displayStartIndex = currentFrame - config.loadedFrames/2;
					displayEndIndex = currentFrame + config.loadedFrames/2;
				}
	    		showFrames();
	    		selectFrame(next);
	    	}
	    	centerTimeline();
	    	updateCursor();
	    	
	    	if(currentPerson != undefined){
	    		$('#people-table #tr-' + currentPerson.id).click();
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
	    	timelineFrames[currentFrame].people.push(person);
	    	loadPeople(timelineFrames[currentFrame].people);
	    },
	    /**
		 * Remove person from the current frame in timeline with the given id
		 */
	    removePerson : function(id){
	    	for ( var i = 0; i < timelineFrames[currentFrame].people.length; i++) {
				if(timelineFrames[currentFrame].people[i].id == id){
					timelineFrames[currentFrame].people.splice(i, 1);
				}
			}
	    	$('.timeline-annotation-' + id).remove();
	    	loadPeople(timelineFrames[currentFrame].people)
	    	if(currentPerson != undefined && id == currentPerson.id){
	    		currentPerson = undefined;
	    		currentIntervals = undefined;
	    	}
	    },
	    /** Go to frame in timeline **/
	    gotoFrame: function(id){
	    	gotoFrame(id);
	    },
	    /** Update person color **/
	    updatePersonColor: function(data){
	    	updatePersonColor(data.id, data.color);
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
    			mouseScrolling: config.mouseScrolling,
    			loadedFrames: config.loadedFrames,
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
				
				if(config.loadedFrames != undefined){
					extendTimelineFrame(direction);
				}		
				updateCursor();
				
				if(currentPerson != undefined){
					selectPerson(currentPerson, currentIntervals)
				}
				
			});
		}
		
		//Displaying loading gif
		$('.timeline-frames').append(loading);
		
		//Getting frames
		params.getFrames();
		
		return this;
	};
	
	//Build and display timeline frames
	function showFrames(){
		$('.timeline-frames-container').empty();
		for (var i = displayStartIndex; i <= displayEndIndex; i++) {
			var frame = $('<div></div>')
				.addClass('timeline-frame')
				.attr('id', 'timeline-frame-' + i)
				.attr('data-id', i)
				.attr('data-frame-id', timelineFrames[i].id)
				.attr('title', 'Go to frame ' + timelineFrames[i].id)
				.append('<div class="timeline-frame-indicator"></div>')
				.append('<span class="timeline-frame-number">' + timelineFrames[i].id + '</span>');
			
			if(timelineFrames[i].people.length > 0){
				frame.addClass('people');
			}
			
			if(i == currentFrame){
				frame.addClass('current');
				loadPeople(timelineFrames[i].people)
			}
			
			//On click change frame
			frame.click(function(){
				selectFrame($(this).data('id'));
			});
				
			$('.timeline-frames-container').append(frame);
		}
		$('.timeline-loading').remove();
		centerTimeline();
		updateCursor();
	}

	
	//Extend timeline frames
	function extendTimelineFrame(direction){
		var frame, id;

		if(direction == 'right'){
			frame = timelineFrames[displayStartIndex];
			if (displayStartIndex > 0){
				$('#timeline-frame-' + displayEndIndex).remove();
				id = displayStartIndex - 1;
				num = frame.id;
				displayStartIndex = displayStartIndex - 1;
				displayEndIndex = displayEndIndex - 1;
			} else {
				id = displayStartIndex;		
			}
		} else {
			frame = timelineFrames[displayEndIndex];
			if(displayEndIndex < timelineFrames.length){
				$('#timeline-frame-' + displayStartIndex).remove();
				id = displayEndIndex + 1;
				num = frame.
				displayStartIndex = displayStartIndex + 1;
				displayEndIndex = displayEndIndex + 1;
			} else {
				id = displaEndIndex;
			}
		}
		
		if($('#timeline-frame-' + id).length > 0){
			return;
		}
		var num = frame.id;
		
		var frame = $('<div></div>')
			.addClass('timeline-frame')
			.attr('id', 'timeline-frame-' + id)
			.attr('data-id', id)
			.append('<div class="timeline-frame-indicator"></div>')
			.append('<span class="timeline-frame-number">' + num + '</span>');
		
		if(timelineFrames[id].people.length > 0){
			frame.addClass('people');
		}
		
		if(id == currentFrame){
			frame.addClass('current');
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
		
		$( ".timeline-annotation" ).each(function() {
			var start = $(this).data('start');
			var left = 600;
			if($('#timeline-frame-' + start).length > 0 ){
				left = $('#timeline-frame-' + start).position().left;
			}
			$(this).css('left', left);
		});
		
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
					.append('<div><a href="#" class="popover-img-timeline" data-container="body" href="#" data-toggle="popover" data-placement="right" data-content=\'' 
							+ '<img style="max-height: 100px;" src="../img/avatars/'+ people[i].id + '.jpg">' + '\'>' + people[i].id + '</a></div>')
					.append('<div id="color-' + people[i].id +'" class="thumbnail pickthb" '
						+ 'style="background-color:'+ people[i].color + '"></div>');
				
				person.click(function(){
					//Callback on person selected (for updating interface)
					$('#box-' + $(this).data('id')).click();
				});
				
				list.append(person);
			} 
			$('.timeline-people .scrollable').html(list);
		} else {
			$('.timeline-people .scrollable').html('<div class="people-list-error">No people in this frame</div>');
		}
		
		//Updating frame color in timeline
		if($('.timeline-person').length > 0){
			$('#timeline-frame-' + currentFrame).addClass('people');
		} else {
			$('#timeline-frame-' + currentFrame).removeClass('people');
		}
		
		$(".popover-img-timeline").popover({
			trigger: 'hover',
			html: true
		});

	}
	
	//Goes to frame (no callback)
	function gotoFrame(id){	
		id = getIndexFromId(id);
		currentFrame = id;
		if(currentFrame < config.loadedFrames/2){
			displayStartIndex = 0;
			displayEndIndex = config.loadedFrames;
		} else {
			displayStartIndex = currentFrame - config.loadedFrames/2;
			displayEndIndex = currentFrame + config.loadedFrames/2;
		}			
		showFrames();
		$('.timeline-annotation').remove();
		centerTimeline();
		updateCursor();
		loadPeople(timelineFrames[id].people)
	}
	
	//Selects frame with the given id in timeline
	function selectFrame(id){
		$('.timeline-frame.current').removeClass('current');
		$('#timeline-frame-' + id).addClass('current');
		$('.timeline-annotation').remove();
		currentFrame = id;
		updateCursor();
		methods.onFrameSelected(timelineFrames[id].id);
		loadPeople(timelineFrames[id].people)	
		//Selecting current person
		if(currentPerson != undefined){
			selectPerson(currentPerson, currentIntervals, true)
		}
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
	function selectPerson(person, intervals, previous){
		deselectAll();
		var drawAnnotations = true;
		
		if($('#timeline-person-' + person.id).length == 0 ){
			if(previous == undefined || !previous){
				methods.addPerson(person);
			} else{
				drawAnnotations = false;
			}
		} 

		if(drawAnnotations && !$('#timeline-person-' + person.id).hasClass('selected')){
			currentPerson = person;
			$('#timeline-person-' + person.id).addClass('selected');
			$('.timeline-annotation-' + person.id).remove();
			
			//Updating intervals
			if(intervals == undefined){
				var intervals = getPersonIntervals(person.id);
			}		
			currentIntervals = intervals;
						
			var annotationContainer = $('<div></div>')
				.addClass('timeline-annotation-container')
				.addClass('timeline-annotation-' + person.id);
				
			for (var i = 0; i < intervals.length; i++) {
				var width = 0;
				var left = -100;
				var offset = 0;
								
				if($('#timeline-frame-' + intervals[i].start).length > 0 ){
					width = $('#timeline-frame-' + intervals[i].start).width();
					left = $('#timeline-frame-' + intervals[i].start).position().left;
					offset = width * (intervals[i].end - intervals[i].start) + width;
				} else {
					if($('#timeline-frame-' + intervals[i].end).length > 0 ){
						width = $('#timeline-frame-' + intervals[i].end).width();
						offset = width * (intervals[i].end - intervals[i].start) + width;
						left = $('#timeline-frame-' + intervals[i].end).position().left - offset;
					} else {
						console.log('color everything')
					}
				}
				
				var over = $('<div></div>')
					.addClass('timeline-annotation')
					.css({
						'position': 'absolute',
						'background-color': person.color,
						'width' :  offset,
						'height': '18px',
						'left' : left,
						'top' :  '40px'
					})
					.attr('data-start', intervals[i].start)
					.attr('data-end', intervals[i].end)
					.attr('data-person', person.id);
				
				var maxWidth = null;
				if(intervals[i + 1] != undefined){
					maxWidth = over.width() + (intervals[i + 1].start - intervals[i].end - 1) * 20;
				}
				
				over.resizable({
					handles: 'e',
					minWidth: over.width(),
					maxWidth: maxWidth,
					grid: 20,
					start: function(e, ui){
						console.log('Start propagation selection');
					},
					stop: function(e, ui){
						console.log('End propagation selection');
						var start = $(this).data('start');
						var end = $(this).data('end');
						var offset = ($(this).width() - (end - start + 1) * 20)/20;
						if(offset > 0){
							var person = $(this).data('person');
							$('.timeline-frames').append('<div class="timeline-loading-container"></div>');
							$('.timeline-loading-container').append(loading);
							propagate(person, offset, start, end);
						}
					}
				});

				annotationContainer.append(over);
			}
			$('.timeline-frames-container').prepend(annotationContainer);
			
		} else {
			$('#timeline-person-' + person.id).removeClass('selected');
			$('.timeline-annotation-' + person.id).remove();
			currentPerson = undefined;
			currentIntervals = undefined;
		}
		
	}
	
	//Returning intervals list
	function getPersonIntervals(id){
		var matches = [];
		for ( var i in timelineFrames) {
			if(timelineFrames[i].people.length > 0){
				for ( var j in timelineFrames[i].people) {
					if(timelineFrames[i].people[j].id == id){
						matches.push(parseInt(i));
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
		return intervals;
	}
	
	//Deselect all people
	function deselectAll(){
		$('.timeline-person').removeClass('selected');
		$('.timeline-annotation-container').remove();
		currentPerson = undefined;
		currentIntervals = undefined;
	}
	
	//Center timeline on cursor
	function centerTimeline(){
		//Centering timeline on current frame
		if(currentFrame > 20){
			$('.timeline-frames-container').css('left', - $('.timeline-frame.current').position().left 
					+ $('.timeline-frames').width()/2);
		} else {
			$('.timeline-frames-container').css('left', 0);
		}

	}
	
	//Updates person color in timeline
	function updatePersonColor(id, color){		
		for(var i in timelineFrames){
			for(var j in timelineFrames[i].people){
				if(timelineFrames[i].people[j].id == id){
					timelineFrames[i].people[j].color = color;
				}
			}
		}
		loadPeople(timelineFrames[currentFrame].people);
	}
	
	
	/**
	 * Propagating annotation with computer vision
	 * @param len: length od the propagation
	 */
	function propagate(person, len, start, end){
		var frames = []
		for(var i = start; i <= end; i++){
			frames.push($('#timeline-frame-' + i).data('frame-id'));
		}
		
		var color = $('#color-' + person).css('background-color');
		$.ajax({
			type: "POST",
			url: "../php/api.php",
			data: {
				action: "propagate",
				person: person,
				length: len,
				frames: frames
			},
			success: function(response){
				console.log(response)
				if(response){	
					for (var i = 0; i < len; i++){
						timelineFrames[end + i].people.push({id: person, color: color});
						$('#timeline-frame-' + (end + 1 + i)).addClass('people');
						$('.timeline-loading-container').remove();
						currentIntervals = getPersonIntervals(person)
					}
				}	
			},
			error: function(error){
				console.log(error);
				$('.timeline-loading-container').remove();
			}
		});
	}
	
	
	function getIndexFromId(id){
		for ( var i = 0; i < timelineFrames.length; i++) {
			if(timelineFrames[i].id == id){
				return i;
			}			
		}
	}
})(jQuery);
