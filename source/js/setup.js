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
	
	$('.btn-file :file').change(function(){
		var input = $(this),
        label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
		$('#importing-data-container #filename').val(label)
		console.log('Selected file ' + label);
	});
	
	
	$('input[name=import-data]').change(function(){
		switch($(this).val()){
			case 'new':
				$('#new-data-container').show();
				$('#importing-data-container').hide();
				break;
			
			case 'import':
				$('#new-data-container').hide();
				$('#importing-data-container').show();
				break;
		}
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
					.addClass('message glyphicon')
					.addClass('glyphicon-remove');
				$('#db-connection-form .form-button .message').remove();
				$('#db-connection-form .form-button').append(label);				
			}
		});	
	} else {
		$('#db-connection-form .loading').remove();
		var label = $('<span></span>')
			.addClass('message glyphicon')
			.addClass('glyphicon-remove');
		$('#db-connection-form .form-button .message').remove();
		$('#db-connection-form .form-button').append(label);
	}
}

/**
 * Retrieving cameras from database
 */
function getCameras(folderName){
	console.log('Retrieving cameras');
	
	$('#cameras-form .form-button .message').remove();
	$('#cameras-form .form-button').append(loading);
	
	$.ajax({
		type: "POST",
		url: "php/setup.php",
		data: {
			action: "get-cameras",
			folder: folderName
		},
		success: function(cameras){
			$('#cameras-form .loading').remove();
			var label = $('<span></span>')
				.addClass('message glyphicon');		
			
			if(cameras.length > 0){
				$('#cameras-form .alert')
					.removeClass('alert-warning alert-danger')
					.addClass('alert-success')
					.html('Found <b>' + cameras.length + '</b> cameras in the selected folder.');
				label.addClass('glyphicon-ok');
				
				var settings = '';
				for ( var i in cameras) {
					var id = cameras[i];
					settings += '<div class="form-group">';
					settings += '<label for="camera-calibration-' + id + '" class="col-sm-3 control-label">Camera ' + id + '</label>';
					settings += '<div class="col-sm-8">';
					settings += '<input type="text" class="form-control" id="camera-calibration-' + id + '" value="0" placeholder="Calibration">';
					settings += '</div></div>';
				}
				$('#camera-settings-container').html(settings);
				$('#camera-settings').slideDown('fast');
			} else {
				$('#cameras-form .alert')
					.removeClass('alert-warning alert-success')
					.addClass('alert-danger')
					.html('No cameras in the selected folder.');
				label.addClass('glyphicon-remove');
				$('#camera-settings').hide();
			}
			$('#cameras-form .form-button .message').remove();
			$('#cameras-form .form-button').append(label);	
		},
		error: function(){
			$('#camera-settings .loading').remove();
			$('#cameras-form .form-button .message').remove();
			var label = $('<span></span>')
				.addClass('glyphicon')
				.addClass('glyphicon-remove');
			$('#cameras-form .form-button').append(label);
			$('#camera-settings').hide();
		}
	});	
}