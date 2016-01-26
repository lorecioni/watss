/**
 * Settings functions
 */

$(document).ready(function(){
	
	init();
	
	$('#db-form').submit(function(e){
		e.preventDefault();
		updateDatabaseConfig($('#db-user').val(), $('#db-password').val(), 
				$('#db-host').val(), $('#db-database').val());
	});
	
	$('#db-test').click(function(){
		checkDatabaseConnection();
	});
	
	$('#add-user-form').submit(function(e){
		e.preventDefault();
		var name = $('#add-user-name').val();
		if(name.length > 0){
			$('#add-user-name').val('');
			var loading = $('<img></img>')
				.addClass('panel-heading-loading')
				.attr('src', 'img/loading.gif')
				.attr('alt', 'loading');
			$('#user-settings .panel-heading').append(loading);
			addUser(name);
		}
	});
	
	
	$('#add-camera-form').submit(function(e){
		e.preventDefault();
		var calib = $('#add-camera-calibration').val();
		if(calib.length > 0){
			$('#add-camera-calibration').val('');
			var loading = $('<img></img>')
				.addClass('panel-heading-loading')
				.attr('src', 'img/loading.gif')
				.attr('alt', 'loading');
			$('#camera-settings .panel-heading').append(loading);
			addCamera(calib);
		}
	});
	
	$('#add-poi-camera').select2({
		multiple: false,
		placeholder: 'Select a camera',
		ajax : {
			url : "php/settings.php",
			type : "POST",
			dataType : 'json',
			data : function ( term,page ) {
				console.log("[get-cameras] call query:"+term);
				return {
					action: 'get-cameras',
					query : term
				};
			},
			results : function ( data, page ) {
				var cameras = [];
				for ( var i in data) {
					cameras.push({id: data[i].id, text: data[i].id});
				}
				return {results : cameras};
			}
	  	}
	});
	
	$('#add-poi-form').submit(function(e){
		e.preventDefault();
		var id = $('#add-poi-id').val();
		var cameraid = $('#add-poi-camera').val();
		var name = $('#add-poi-name').val();
		var x = $('#add-poi-locx').val();
		var y = $('#add-poi-locy').val();
		var width = $('#add-poi-width').val();
		var height = $('#add-poi-height').val();
		
		if(id.length > 0 && cameraid.length > 0
				&& name.length > 0 && x.length > 0
				&& y.length > 0 && width.length > 0
				&& height.length > 0){
			$('#add-poi-form input').val('');
			var loading = $('<img></img>')
				.addClass('panel-heading-loading')
				.attr('src', 'img/loading.gif')
				.attr('alt', 'loading');
			$('#poi-settings .panel-heading').append(loading);
			addPoi({id: id, cameraid: cameraid, name: name, x: x, y: y, width: width, height: height});
		}
	});

});


/**
 * Init function
 */

function init(){
	var loading = $('<img></img>')
		.addClass('panel-loading')
		.attr('src', 'img/loading.gif')
		.attr('alt', 'loading');
	$('#user-settings .panel-body').append(loading);
	$('#camera-settings .panel-body').append(loading);
	
	$.ajax({
		type: "POST",
		url: "php/settings.php",
		data: {
			action: "init",
		},
		success: function(response){
			console.log('Loading configuration file');
			$('#db-user').val(response.user);
			$('#db-password').val(response.password);
			$('#db-host').val(response.host);
			$('#db-database').val(response.db);
			checkDatabaseConnection();
			getUsers();
			getCameras();
			getPoi();
		}
	});	
}

/**
 * Check database connection
 */
function checkDatabaseConnection(){
	console.log('Checking database connection');
	if($('#db-user').val() != '' && $('#db-pass').val() != ''
			&& $('#db-host').val() != '' && $('#db-database').val() != ''){
		
		$('#db-connection-settings .panel-title .label').remove();
		var loading = $('<img></img>')
				.addClass('panel-loading')
				.attr('src', 'img/loading.gif')
				.attr('alt', 'loading');
		
		$('#db-connection-settings .panel-heading').append(loading);		
		$.ajax({
			type: "POST",
			url: "php/settings.php",
			data: {
				action: "test-database-connection",
				user: $('#db-user').val(),
				password: $('#db-password').val(),
				host: $('#db-host').val(),
				db: $('#db-database').val()
			},
			success: function(response){
				$('#db-connection-settings .panel-loading').remove();
				var label = $('<span></span>')
					.addClass('label');
				
				if(response){
					console.log('Database connection success');
					label.addClass('label-success')
						.html('Connected');
				} else {
					console.log('Database connection error');
					label.addClass('label-danger')
						.html('Error');
				}
				$('#db-connection-settings .panel-title').append(label);
			},
			error: function(){
				$('#db-connection-settings .panel-loading').remove();
				var label = $('<span></span>')
					.addClass('label')
					.addClass('label-danger')
					.html('Error');
				$('#db-connection-settings .panel-title').append(label);				
			}
		});	
	}
}

/**
 * Updates atabase connection configuration file 
 * @param user
 * @param password
 * @param host
 * @param db
 */
function updateDatabaseConfig(user, password, host, db){
	console.log('Updating database configuration file');
	$.ajax({
		type: "POST",
		url: "php/settings.php",
		data: {
			action: "update-database-connection",
			user: user,
			password: password,
			host: host,
			db: db
		},
		success: function(response){
			if(response){
				displayMessage($('#db-form').parent(), 'Configuration file updated correctly', 'success');
			} else {
				displayMessage($('#db-form').parent(), 'Error updating configuration file', 'error');
			}
		},
		error: function(){
			displayMessage($('#db-form').parent(), 'Error updating configuration file', 'error');
		}
	});	
}

/**
 * Function for displaying alert message
 * @param container : jQuery object to append message
 * @param message
 * @param type : error or success
 */
function displayMessage(container, message, type){
	var alert = $('<div></div>')
		.addClass('alert alert-' + type)
		.attr('role', 'alert')
		.append(message);
	container.append(alert);
}

/**
 * Retrieving users from database
 */
function getUsers(){
	console.log('Retrieving users');
	$.ajax({
		type: "POST",
		url: "php/settings.php",
		data: {
			action: "get-users"
		},
		success: function(users){
			$('#user-settings .panel-loading').remove();
			var list = $('<ul></ul>')
				.addClass('list-group');
			
			for ( var i in users) {
				var item = $('<li></li>')
					.addClass('list-group-item')
					.attr('id', 'user-' + users[i].id)
					.attr('data-id', users[i].id);
				var badge = $('<span></span>')
					.addClass('badge')
					.append('<span class="glyphicon glyphicon-remove" ' +
							'aria-hidden="true">');	
				badge.click(function(){
					var id = $(this).parent().data('id');
					removeUser(id);
				});
				item.append(badge);
				item.append(users[i].name);
				list.append(item);
			}
			$('#user-settings .panel-body .scrollable').append(list);
		},
		error: function(){
			$('#user-settings .panel-loading').remove();
		}
	});	
}

/**
 * Adding user to database with given name
 * @param name
 */
function addUser(name){
	$.ajax({
		type: "POST",
		url: "php/settings.php",
		data: {
			action: "add-user",
			name: name
		},
		success: function(response){
			$('#user-settings .panel-heading-loading').remove();
			if(response.success){
				console.log('Added user');
				var item = $('<li></li>')
					.addClass('list-group-item')
					.attr('data-id', response.id)
					.attr('id', 'user-' + response.id);
				var badge = $('<span></span>')
					.addClass('badge')
					.append('<span class="glyphicon glyphicon-remove" ' +
							'aria-hidden="true">');
				badge.click(function(){
					var id = $(this).parent().data('id');
					removeUser(id);
				});
				item.append(badge);
				item.append(name);
				
				$('#user-settings .panel-body .scrollable').prepend(item);
			} else {
				console.log('Error adding user');
			}
		},
		error: function(){
			console.log('Error adding user');
		}
	});	
}

/**
 * Removing user to database with given id
 * @param id
 */
function removeUser(id){
	$.ajax({
		type: "POST",
		url: "php/settings.php",
		data: {
			action: "remove-user",
			userid: id
		},
		success: function(response){
			if(response){
				console.log('Removed user');
				$('#user-' + id).remove();
			} else {
				console.log('Error removing user');
			}
		},
		error: function(){
			console.log('Error removing user');
		}
	});	
}

/**
 * Retrieving cameras from database
 */
function getCameras(){
	console.log('Retrieving cameras');
	$.ajax({
		type: "POST",
		url: "php/settings.php",
		data: {
			action: "get-cameras"
		},
		success: function(cameras){
			$('#camera-settings .panel-loading').remove();
			$('#camera-settings .panel-body .scrollable').empty();
			var list = $('<ul></ul>')
				.addClass('list-group');
			
			for ( var i in cameras) {
				
				var item = $('<li></li>')
					.addClass('list-group-item')
					.attr('id', 'camera-' + cameras[i].id)
					.attr('data-id', cameras[i].id);
				
				var calib = $('<a></a>')
					.attr('href', '#')
					.attr('id', 'calibration-' + cameras[i].id)
					.addClass('editable editable-click')
					.append('Calibration: ' + cameras[i].calibration)
					.attr('data-type', 'text')
					.attr('data-title', 'Set calibration')
					.attr('data-value', 0)
					.attr('data-pk', cameras[i].id);
				
				calib.editable({
					type: 'text',
				    url: 'php/settings.php',
				    mode: 'inline',
					params: function(params) {
						params.action = "set-camera-calibration";
						params.cameraid = params.pk;
						params.calibration = params.value;
						return params;
					},
					success: function(response, newValue) {		
							console.log(response);
					}
				});

				item.append(calib);
				item.append(cameras[i].id);
				list.append(item);
			}
			$('#camera-settings .panel-body .scrollable').append(list);
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