/**
 * Settings functions
 */

var loading = $('<img></img>')
	.addClass('loading')
	.attr('src', 'img/loading.gif')
	.attr('alt', 'loading');

$(document).ready(function(){
	
	$('#db-connection-form').submit(function(e){
		e.preventDefault();
		checkDatabaseConnection();
	});
	
	$('#verify-frame-folder').click(function(){
		getCameras($('#frame-folder-name').val());
	});

});



/**
 * Check database connection
 */
function checkDatabaseConnection(){
	console.log('Checking database connection');
	if($('#db-user').val() != '' && $('#db-password').val() != ''
			&& $('#db-host').val() != ''){

		$('#db-connection-form .form-button .message').remove();
		$('#db-connection-form .form-button').append(loading);

		$.ajax({
			type: "POST",
			url: "php/setup.php",
			data: {
				action: "test-database-connection",
				user: $('#db-user').val(),
				password: $('#db-password').val(),
				host: $('#db-host').val(),
				db: $('#db-database').val()
			},
			success: function(response){
				
				$('#db-connection-form .loading').remove();
				var label = $('<span></span>')
					.addClass('message glyphicon');
				
				
				if(response){
					console.log('Database connection success');
					label.addClass('glyphicon-ok');
				} else {
					console.log('Database connection error');
					label.addClass('glyphicon-remove');
				}
				$('#db-connection-form .form-button .message').remove();
				$('#db-connection-form .form-button').append(label);
			},
			error: function(){
				$('#db-connection-form .loading').remove();
				var label = $('<span></span>')
					.addClass('glyphicon')
					.addClass('glyphicon-remove');
				$('#db-connection-form .form-button .message').remove();
				$('#db-connection-form .form-button').append(label);				
			}
		});	
	}
}

/**
 * Retrieving cameras from database
 */
function getCameras(folderName){
	console.log('Retrieving cameras');
	$.ajax({
		type: "POST",
		url: "php/setup.php",
		data: {
			action: "get-cameras",
			folder: folderName
		},
		success: function(cameras){
			console.log(cameras);
		},
		error: function(){
			$('#camera-settings .panel-loading').remove();
		}
	});	
}


/**
 * Adding camera to database with given calibration value
 * @param value
 */
function addCamera(value){
	$.ajax({
		type: "POST",
		url: "php/settings.php",
		data: {
			action: "add-camera",
			calibration: value
		},
		success: function(response){
			$('#camera-settings .panel-heading-loading').remove();
			if(response.success){
				console.log('Added camera');
				getCameras();
			} else {
				console.log('Error adding camera');
			}
		},
		error: function(){
			console.log('Error adding camera');
		}
	});	
}

/**
 * Retrieving poi from database
 */
function getPoi(){
	console.log('Retrieving cameras');
	$.ajax({
		type: "POST",
		url: "php/settings.php",
		data: {
			action: "get-poi"
		},
		success: function(poi){
			$('#poi-settings .panel-loading').remove();
			$('#poi-settings .panel-body tbody').empty();
			
			for ( var i in poi) {
				
				var row = $('<tr></tr>');
				row.append('<th scope="row">' + poi[i].id + '</th>');
				row.append('<td>' + poi[i].cameraid + '</td>');
				row.append('<td>' + poi[i].name + '</td>');
				row.append('<td>' + poi[i].x + '</td>');
				row.append('<td>' + poi[i].y + '</td>');
				row.append('<td>' + poi[i].width + '</td>');
				row.append('<td>' + poi[i].height + '</td>');
				
				$('#poi-settings .panel-body tbody').append(row);
			}
		},
		error: function(){
			$('#poi-settings .panel-loading').remove();
		}
	});	
}

/**
 * Adding poi to database with given attributes
 * @param poi
 */
function addPoi(poi){
	$.ajax({
		type: "POST",
		url: "php/settings.php",
		data: {
			action: "add-poi",
			poiid: poi.id,
			cameraid: poi.cameraid,
			name: poi.name,
			x: poi.x,
			y: poi.y,
			width: poi.width,
			height: poi.height
		},
		success: function(response){
			$('#poi-settings .panel-heading-loading').remove();
			if(response.success){
				console.log('Added poi');
				getPoi();
			} else {
				console.log('Error adding poi');
			}
		},
		error: function(){
			console.log('Error adding poi');
		}
	});	
}