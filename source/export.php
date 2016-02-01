<?php 
	//If connection ini not exists, redirect to setup page
	if(!file_exists('php/connection.ini')){
		header("Location: setup.php");
		exit;
	}
?>
<!DOCTYPE html>
<html>
    <head>
    <title>WATSS - Export</title>
	<meta name="description" content="Video tagging and results comparison">
	<meta name="keywords" content="Video, Ground truth, Keyboard, Automatic">
	<meta charset="UTF-8">
	
	<!-- CSS -->
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/bootstrap-theme.min.css">
	<link rel="stylesheet" href="css/style.css">

        <!--<script src="js/event-handler"></script>-->
    </head>

    <body>
    	<div id="content">
		<div>
			<nav class="navbar navbar-default" role="navigation">
			  <div class="container-fluid">
				<div class="collapse navbar-collapse main-nav">
				<a class="navbar-brand" href="index.php">
			        <img alt="Brand" src="img/logo_small.png">
			      </a>
				  <ul class="nav navbar-nav">
					<li><a href="index.php">Home</a></li>
					<li><a href="gt-making/gt-making.php">GT Making</a></li>
					<li class="active"><a href="export.php">Export</a></li>					
					<li><a href="legend.html">Legend</a></li>
					<li><a href="settings.php">Settings</a></li>
				  </ul>
				</div><!-- /.navbar-collapse -->
			  </div><!-- /.container-fluid -->
			</nav>
			
			<div id="content-wrapper">

		    <h1>Export Results</h1>
		    <p>Press the button below to export results.</p>
			<a href="#" class="btn btn-default btn-sm" id="export_res"> Export </a>	
			</div>
		</div>
		</div>
	
	    <script src="js/jquery-1.11.1.min.js"></script>
	    <!-- Include all compiled plugins (below), or include individual files as needed -->
	    <script src="js/bootstrap.min.js"></script>
		<script>
		$(document).ready(function(){
			$('#export_res').click(function(){
				console.log("exporting");
                window.location.href="php/api.php?action=export"
			});			
		});
		</script>
	</body>
</html>
