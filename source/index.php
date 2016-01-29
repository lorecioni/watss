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
    <title>WATSS - Home</title>
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
		<nav class="navbar navbar-default" role="navigation">
			  <div class="container-fluid">

				<!-- Collect the nav links, forms, and other content for toggling -->
				<div class="collapse navbar-collapse">
				 <a class="navbar-brand" href="#">
			        <img alt="Brand" src="img/logo_small.png">
			      </a>
				  <ul class="nav navbar-nav main-nav">
					<li class="active"><a href="#">Home</a></li>
					<li><a href="gt-making/gt-making.php">GT Making</a></li>
					<li><a href="export.php">Export</a></li>					
					<li><a href="legend.html">Legend</a></li>
					<li><a href="settings.php">Settings</a></li>
				  </ul>
				</div><!-- /.navbar-collapse -->
			  </div><!-- /.container-fluid -->
			</nav>
		<div id="content-wrapper">
		
			<header>
		  		<img id="logo" src="img/logo_header.png" alt="WATSS Logo">
		  		<h4>Web Annotation Tool for Surveillance Scenarios</h4>
		  	</header>
		<div class="row">
		  <div class="col-xs-8 col-sm-6 col-md-6">
		  	<div id=""></div>
		  		  <p>This tool is designed to annotate person and group bounding boxes, visible area, head gaze, body gaze and observed points of interest (poi) on surveillance datasets.</p>
		  		  <p>You may try it on sequences acquired from the <i>Bargello Museum</i>, go to <a href="gt-making/gt-making.php">GT Making</a> section and enter with the user <i>Guest</i>.<p> 
		  		<div id="features-list">
		  			<h4>Features</h4>
		  			<ul>
		  				<li>Bounding Box<div class="arrow arrow-bb"></div></li>
		  				<li>Visible area<div class="arrow arrow-bbv"></div></li>
		  				<li>Head gaze<div class="arrow arrow-hgaze"></div></li>
		  				<li>Body gaze<div class="arrow arrow-bgaze"></div></li>
		  				<li>Points of interest<div class="arrow arrow-poi"></div></li>
		  			</ul>
		  		</div>
		  </div>
		  <div class="preview col-xs-6 col-md-6">
		  	<h3>Preview</h3>
		  	<img src="img/frame.jpg" alt="Frame" id="frame-preview">		  
		  </div>
		</div>
		</div>
		</div>
		<footer>
			&copy; 2016, MICC University of Florence
		</footer>
 <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	    <!-- Include all compiled plugins (below), or include individual files as needed -->
	    <script src="js/bootstrap.min.js"></script>
	    <script src="js/jquery.hotkeys.js"></script>
	</body>
</html>
