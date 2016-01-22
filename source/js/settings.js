/**
 * Settings functions
 */

$(document).ready(function(){
	
	$('#db-form').submit(function(e){
		e.preventDefault();
		$.ajax({
			type: "POST",
			url: "php/config.php",
			data: {
				action: "update-database-connection",
				user: $('#db-user').val(),
				password: $('#db-password').val(),
				host: $('#db-host').val(),
				db: $('#db-database').val()
			},
			success: function(response){
				console.log(response);
				
			}
		});	
	})
	
});