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
	$.ajax({
		type: "POST",
		url: "php/config.php",
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
			url: "php/config.php",
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
		url: "php/config.php",
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

function getUsers(){
	console.log('Retrieving users');
	$.ajax({
		type: "POST",
		url: "php/config.php",
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
					.attr('data-id', users[i].id);
				var badge = '<span class="badge"><span class="glyphicon glyphicon-remove" ' +
					'aria-hidden="true"></span></span>';
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
		url: "php/config.php",
		data: {
			action: "add-user",
			name: name
		},
		success: function(users){
			$('#user-settings .panel-loading').remove();
			var list = $('<ul></ul>')
				.addClass('list-group');
			
			for ( var i in users) {
				var item = $('<li></li>')
					.addClass('list-group-item')
					.attr('data-id', users[i].id);
				var badge = '<span class="badge"><span class="glyphicon glyphicon-remove" ' +
					'aria-hidden="true"></span></span>';
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
