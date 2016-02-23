$(document).ready(function(){
	
	/** Exporting data **/
	$('#export_data').click(function(e){
		e.preventDefault();
		window.location.href = "php/api.php?action=exportAnnotations&limit" + $('input[name="frames"]:checked').val();
	})
	
	/** Exporting annotations as CSV file **/
	$('#export_annotations').click(function(e){
		e.preventDefault();
		var unchecked =  $('input[name="attributes"]:not(:checked)').map(function() {return '&exclude[]=' + this.value;}).get().join("")
		window.location.href = "php/api.php?action=exportAnnotations" + unchecked; 
	})
	
	/** Exporting database schema **/
	$('#export_schema').click(function(e){
		e.preventDefault();
		window.location.href = "php/api.php?action=exportSchema"; 
	})
	
	/** Exporting database as SQL script **/
	$('#export_database').click(function(e){
		e.preventDefault();
		var unchecked =  $('input[name="tables"]:not(:checked)').map(function() {return '&exclude[]=' + this.value;}).get().join("")
		window.location.href = "php/api.php?action=exportDatabase" + unchecked; 
	})
	
});