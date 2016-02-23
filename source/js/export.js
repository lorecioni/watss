$(document).ready(function(){
	
	/** Exporting database as SQL script **/
	$('#export_databasesss').click(function(e){
		e.preventDefault();
		
		$.ajax({
			url: 'php/api.php',
			type: 'post',
			data: {
				action: 'exportDatabaseScript'
			},
			success: function(data){
				
			},
			error: function(err){
				
			},
			async: false
		});
		
	})
	
	/** Exporting annotations as CSV file **/
	$('#export_annotations').click(function(e){
		e.preventDefault();
		var unchecked =  $('input[name="attributes"]:not(:checked)').map(function() {return '&exclude[]=' + this.value;}).get().join("")
		window.location.href = "php/api.php?action=exportAnnotations" + unchecked; 
	})
	
	
	/** Exporting database as SQL script **/
	$('#export_database').click(function(e){
		e.preventDefault();
		var unchecked =  $('input[name="tables"]:not(:checked)').map(function() {return '&exclude[]=' + this.value;}).get().join("")
		window.location.href = "php/api.php?action=exportDatabase" + unchecked; 
	})
	
});