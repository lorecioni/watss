$(document).ready(function(){
	
	/** Exporting data **/
	$('#export_data').click(function(e){
		e.preventDefault();
		var unchecked =  $('input[name="attributes"]:not(:checked)').map(function() {return '&exclude[]=' + this.value;}).get().join("");
		$.ajax({
			url: "php/api.php?action=exportAll&limit=" + $('input[name="frames"]:checked').val() + unchecked,
			method: 'get',
			success: function(data){
				console.log(data);
				window.location.href = "php/api.php?action=download&type=zip&name=MuseumVisitors.zip&location=" + data; 
			},
			error: function(error){
				console.log(error);
			}
		});
	});
	
	/** Exporting annotations as CSV file **/
	$('#export_annotations').click(function(e){
		e.preventDefault();
		var unchecked =  $('input[name="attributes"]:not(:checked)').map(function() {return '&exclude[]=' + this.value;}).get().join("")
		$.ajax({
			url: "php/api.php?action=exportAnnotations" + unchecked,
			method: 'get',
			success: function(data){
				window.location.href = "php/api.php?action=download&type=csv&name=annotations.csv&location=" + data; 
			},
			error: function(error){
				console.log(error);
			}
		});
	});
	
	/** Exporting database schema **/
	$('#export_schema').click(function(e){
		e.preventDefault();
		$.ajax({
			url: "php/api.php?action=exportSchema",
			method: 'get',
			success: function(data){
				window.location.href = "php/api.php?action=download&type=sql&name=schema.sql&location=" + data; 
			},
			error: function(error){
				console.log(error);
			}
		});
	})
	
	/** Exporting database as SQL script **/
	$('#export_database').click(function(e){
		e.preventDefault();
		var unchecked =  $('input[name="tables"]:not(:checked)').map(function() {return '&exclude[]=' + this.value;}).get().join("")
		$.ajax({
			url: "php/api.php?action=exportDatabase" + unchecked,
			method: 'get',
			success: function(data){
				window.location.href = "php/api.php?action=download&type=sql&name=database.sql&location=" + data; 
			},
			error: function(error){
				console.log(error);
			}
		});
	})
	
});