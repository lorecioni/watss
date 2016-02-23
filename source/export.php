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

		    <h1>Export</h1>
		    <p>Export annotations or the whole database in a SQL script.</p>
		    <div class="export row">
				  <div class="col-xs-6 col-sm-6">
					    <div id="db-connection-settings" class="settings-panel panel panel-default">
						  <div class="panel-heading">
						    <h3 class="panel-title">Annotations</h3>
						  </div>
						  <div class="panel-body">
								<p>Select annotation attributes you want to export</p>
							    <div class="checkbox">
								  <label>
								    <input type="checkbox" name="attributes" value="peopleid" checked>
								    Person
								  </label>
								</div>
								<div class="checkbox">
								  <label>
								    <input type="checkbox" name="attributes" value="frameid" checked>
								    Frame
								  </label>
								</div>
								<div class="checkbox">
								  <label>
								    <input type="checkbox" name="attributes" value="cameraid" checked>
								    Camera
								  </label>
								</div>
								<div class="checkbox">
								  <label>
								    <input type="checkbox" name="attributes" value="bb" checked>
								    Bounding Box
								  </label>
								</div>
								<div class="checkbox">
								  <label>
								    <input type="checkbox" name="attributes" value="bbV" checked>
								    Bounding Box visible
								  </label>
								</div>
								<div class="checkbox">
								  <label>
								    <input type="checkbox" name="attributes" value="face" checked>
								    Gaze angle face
								  </label>
								</div>
								<div class="checkbox">
								  <label>
								    <input type="checkbox" name="attributes"  value="body" checked>
								    Gaze angle body
								  </label>
								</div>
								<div class="checkbox">
								  <label>
								    <input type="checkbox" name="attributes"  value="poiid" checked>
								    Point of Interest
								  </label>
								</div>
								<div class="checkbox">
								  <label>
								    <input type="checkbox" name="attributes"  value="groupid" checked>
								    Group
								  </label>
								</div>				
							<div class="clearfix"></div>
							
							<p>With the "Export all" button you can download all data (frames and annotations).</p>
							<div class="checkbox">
								  <label>
								    <input type="radio" name="frames" value="annotated" checked>
								    Export only annotated frames
								  </label>
								</div>
								<div class="checkbox">
								  <label>
								    <input type="radio" name="frames" value="all">
								    Export all frames
								  </label>
								</div>	
							<div class="col-sm-offset-6 col-sm-10">
								<a href="#" class="btn btn-default" id="export_annotations">Export Annotations</a> <a href="#" class="btn btn-default" id="export_data">Export All</a>
							</div>
						  </div>
						</div>				
				  </div>
				  
				  <div class="col-xs-4">
				  	<div id="user-settings" class="settings-panel panel panel-default">
						  <div class="panel-heading">
						    <h3 class="panel-title">Database</h3>
						  </div>
						  <div class="panel-body">
						  <p>Select database table you want to export</p>
							    <div class="checkbox">
								  <label>
								    <input type="checkbox" name="tables" value="avatars" checked>
								    Avatars
								  </label>
								</div>
								<div class="checkbox">
								  <label>
								    <input type="checkbox" name="tables" value="cameras" checked>
								    Cameras
								  </label>
								</div>
								<div class="checkbox">
								  <label>
								    <input type="checkbox" name="tables" value="frames" checked>
								    Frames
								  </label>
								</div>
								<div class="checkbox">
								  <label>
								    <input type="checkbox" name="tables" value="groups" checked>
								    Groups
								  </label>
								</div>
								<div class="checkbox">
								  <label>
								    <input type="checkbox" name="tables" value="people" checked>
								    People
								  </label>
								</div>
								<div class="checkbox">
								  <label>
								    <input type="checkbox" name="tables" value="poi" checked>
								    Poi
								  </label>
								</div>
								<div class="checkbox">
								  <label>
								    <input type="checkbox" name="tables" value="users" checked>
								    Users
								  </label>
								</div>
						  	<div class="col-sm-offset-2">
								<a href="#" class="btn btn-default" id="export_schema">Export Schema</a><a href="#" class="btn btn-default" id="export_database"> Export SQL script</a>
							</div>
						  </div>
						</div>
				  
				  </div>		    
		    </div>
			</div>
		</div>
		</div>
		<footer>
			&copy; 2016, MICC University of Florence
		</footer>
	    <script src="js/jquery-1.11.1.min.js"></script>
	    <!-- Include all compiled plugins (below), or include individual files as needed -->
	    <script src="js/bootstrap.min.js"></script>
		<script src="js/export.js"></script>
	</body>
</html>
