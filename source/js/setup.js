/**
 * Settings functions
 */

var loading = $('<img></img>')
	.addClass('loading')
	.attr('src', 'img/loading.gif')
	.attr('alt', 'loading');

var cameras = [];

/** install steps **/
var installSteps = 5;
var currentStep = 0;


$(document).ready(function(){
	
	$('#db-connection-form').submit(function(e){
		e.preventDefault();
		checkDatabaseConnection();
	});
	
	$('#verify-frame-folder').click(function(){
		getCameras($('#frame-folder-name').val());
	});
	
	$('#db-name').change(function(){
		$('#check-db-name .icon').remove();
		if($(this).val().length > 0 && $(this).val() != ''){	
			$('#check-db-name').append('<span class="icon glyphicon glyphicon-ok"></span>');
		} else {
			$('#check-db-name').append('<span class="icon glyphicon glyphicon-remove"></span>');
		}
		validateInstall()
	});
	
	$('#user-list-input').change(function(){
		$('#check-users .icon').remove();
		var users = $(this).tagsinput('items');
		if(users.length > 0){
			$('#check-users').append('<span class="icon glyphicon glyphicon-ok"></span>');
		} else {
			$('#check-users').append('<span class="icon glyphicon glyphicon-remove"></span>');
		}
		validateInstall()
	})
	
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
	
	$('#install-button').click(function(){
		if(!$(this).hasClass('disabled')){
			install();
		}
	});
	
});


/**
 * Check database connection
 */
function checkDatabaseConnection(){
	console.log('Checking database connection');
	$('#check-db-connection .icon').remove();
	
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
					
					//Updates install summary (bottom page)
					$('#check-db-connection').append('<span class="icon glyphicon glyphicon-ok"></span>');
				} else {
					console.log('Database connection error');
					label.addClass('glyphicon-remove');
					//Updates install summary (bottom page)
					$('#check-db-connection').append('<span class="icon glyphicon glyphicon-remove"></span>');
				}
				$('#db-connection-form .form-button .message').remove();
				$('#db-connection-form .form-button').append(label);
				
				validateInstall()		
			},
			error: function(){
				$('#db-connection-form .loading').remove();
				var label = $('<span></span>')
					.addClass('message glyphicon')
					.addClass('glyphicon-remove');
				$('#db-connection-form .form-button .message').remove();
				$('#db-connection-form .form-button').append(label);	
				//Updates install summary (bottom page)
				$('#check-db-connection').append('<span class="icon glyphicon glyphicon-remove"></span>');
				validateInstall()
			}
		});	
	} else {
		$('#db-connection-form .loading').remove();
		var label = $('<span></span>')
			.addClass('message glyphicon')
			.addClass('glyphicon-remove');
		$('#db-connection-form .form-button .message').remove();
		$('#db-connection-form .form-button').append(label);
		$('#check-db-connection').append('<span class="icon glyphicon glyphicon-remove"></span>');
		validateInstall()
	}
}

/**
 * Retrieving cameras from database
 */
function getCameras(folderName){
	console.log('Retrieving cameras');
	
	$('#check-frames-folder .icon').remove();
	$('#check-camera-settings .icon').remove();
	$('#cameras-form .form-button .message').remove();
	$('#cameras-form .form-button').append(loading);
	
	$.ajax({
		type: "POST",
		url: "php/setup.php",
		data: {
			action: "get-cameras",
			folder: folderName
		},
		success: function(data){
			$('#cameras-form .loading').remove();
			var label = $('<span></span>')
				.addClass('message glyphicon');		
			
			if(data.length > 0){
				cameras = data;
				$('#cameras-form .alert')
					.removeClass('alert-warning alert-danger')
					.addClass('alert-success')
					.html('Found <b>' + data.length + '</b> cameras in the selected folder.');
				label.addClass('glyphicon-ok');
				
				var settings = '';
				for ( var i in data) {
					var id = data[i];
					settings += '<div class="form-group camera" data-id="' + id + '">';
					settings += '<label for="camera-calibration-' + id + '" class="col-sm-3 control-label">Camera ' + id + '</label>';
					settings += '<div class="col-sm-8">';
					settings += '<input type="text" class="form-control" id="camera-calibration-' + id + '" value="0" placeholder="Calibration">';
					settings += '</div></div>';
				}
				$('#camera-settings-container').html(settings);
				$('#camera-settings').slideDown('fast');
				
				//Updates install summary
				$('#check-frames-folder').append('<span class="icon glyphicon glyphicon-ok"></span>');
				$('#check-camera-settings').append('<span class="icon glyphicon glyphicon-ok"></span>');
			} else {
				$('#cameras-form .alert')
					.removeClass('alert-warning alert-success')
					.addClass('alert-danger')
					.html('No cameras in the selected folder.');
				label.addClass('glyphicon-remove');
				$('#camera-settings').hide();
				
				//Updates install summary
				$('#check-frames-folder').append('<span class="icon glyphicon glyphicon-remove"></span>');
				$('#check-camera-settings').append('<span class="icon glyphicon glyphicon-remove"></span>');
			}
			$('#cameras-form .form-button .message').remove();
			$('#cameras-form .form-button').append(label);	
			validateInstall();
		},
		error: function(){
			$('#camera-settings .loading').remove();
			$('#cameras-form .form-button .message').remove();
			var label = $('<span></span>')
				.addClass('glyphicon')
				.addClass('glyphicon-remove');
			$('#cameras-form .form-button').append(label);
			$('#camera-settings').hide();
			//Updates install summary
			$('#check-frames-folder').append('<span class="icon glyphicon glyphicon-remove"></span>');
			$('#check-camera-settings').append('<span class="icon glyphicon glyphicon-remove"></span>');
			validateInstall()
		}
	});	
}


function validateInstall(){
	var numChecks = $('p.check-label').length;
	var checked = $('p.check-label .icon.glyphicon-ok').length;
	if(checked >= numChecks){
		if($('#install-button').hasClass('disabled')){
			$('#install-button').removeClass('disabled');		
		}
		return true;
	} else {
		if(!$('#install-button').hasClass('disabled')){
			$('#install-button').addClass('disabled');
		}
		return false;
	}
}

function install(){
	if(validateInstall()){
		//WATSS is ready to be installed
		console.log('Installing WATSS');
		
		if(!$('#install-button').hasClass('disabled')){
		//	$('#install-button').addClass('disabled');
		}
		
		$('#install-progress').fadeIn('fast');
		var progress = $('#install-progress .progress-bar');
		progress.attr('aria-valuenow', 0).css('width', 0 + '%');
		progress.addClass('active');
		currentStep = 0;
		var type = $('input[name=import-data]').val();
		
		//Database connection
		var dbUser = $('#db-user').val();
		var dbPassword = $('#db-password').val();
		var dbHost = $('#db-host').val();
		
		//Frames
		var framesFolder = $('#frame-folder-name').val();
		
		//Cameras
		var cameraSettings = cameras;
		for ( var i in cameraSettings) {
			var id = cameraSettings[i].id;
			var calibration = $('#camera-calibration-' + id).val();
			cameraSettings[i] = {id: id, calibration: calibration}
		}
		
		var data = [];
		
		switch(type){
			case 'new':
				var dbName = $('#db-name').val();
				//Users
				var users = $('#user-list-input').tagsinput('items');
				
				data = {
					connection: {user: dbUser, password: dbPassword, host: dbHost, name: dbName},
					framesFolder: framesFolder,
					cameras: cameraSettings,
					users: users
				};
				
				createDatabaseConnection(progress, data);
				
			case 'import':
				break;
		}
		
	}
}

function createDatabaseConnection(progress, data){
	$.ajax({
		type: "POST",
		url: "php/setup.php",
		data: {
			action: "create-connection",
			data: data.connection
		},
		success: function(response){
			if(response){
				currentStep += 1;
				var value = currentStep * 100/installSteps;
				
				progress.attr('aria-valuenow', value).css('width', value + '%');
				progress.html(value + '%');
				generateDatabaseSchema(progress, data);
			}
		},
		error: function(error){
			console.log(error);
		}
	});	
}

function generateDatabaseSchema(progress, data){
	$.ajax({
		type: "POST",
		url: "php/setup.php",
		data: {
			action: "create-schema",
			data: data.connection
		},
		success: function(response){
			console.log(response)
			if(response){
				currentStep += 1;
				var value = currentStep * 100/installSteps;
				
				progress.attr('aria-valuenow', value).css('width', value + '%');
				progress.html(value + '%');
			}
		},
		error: function(error){
			console.log(error);
		}
	});	
}