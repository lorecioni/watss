/**
 * Frames timeline functions
 */

var timelineConfig = {
	framesDisplayed : 20,
	offset: 20
};

function loadTimeline(){
	var limit = timelineConfig.framesDisplayed * 5;
	var framesDisplayed = timelineConfig.framesDisplayed; //FIXME to be adjusted
	
	$.ajax({
		type: "POST",
		url: "../php/api.php",
		data: { 
			action:"get-timeline-frames",
			limit: limit},
		success: function(response){
			var currentFrame = response.current;
			displayTimelineFrames(response.frames);
			if(currentFrame <= framesDisplayed/2){
				//Starts from the beginning
				
				
			} else {
				//Display frames in windowed time
				
			}
			
		}
	});	
	
	$('.timeline .timeline-cursor').draggable({
		containment: "parent",
		drag: function( event, ui ) {
			ui.position.top = ui.originalPosition.top;
		}
	});
}

/**
*	Display frames in timeline
*/
function displayTimelineFrames(frames){
	var offset = 0;
	$('.timeline-frames').empty();
	for (var i in frames) {
		var frame = $('<div></div>')
			.addClass('timeline-frame')
			.append('<span class="timeline-frame-number">' + frames[i].number + '</span>')
			.append('<div class="timeline-frame-indicator"></div>')
			.css('left', offset);
		offset += timelineConfig.offset;
		$('.timeline-frames').append(frame);
	}

}