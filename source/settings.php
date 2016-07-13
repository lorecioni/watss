<?php 
	//If connection ini not exists, redirect to setup page
	if(!file_exists('php/connection.ini')){
		header("Location: setup.php");
		exit;
	}
	//Checking if user is logged in
	session_start();
	if(!isset($_SESSION["user"])){
		header("Location: gt-making/gt-making.php");
	}
?>
<!DOCTYPE html>
<html>
    <head>
    <title>WATSS - Settings</title>
	<meta name="description" content="Video tagging and results comparison">
	<meta name="keywords" content="Video, Ground truth, Keyboard, Automatic">
	<meta charset="UTF-8">
	
	<!-- CSS -->
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/bootstrap-theme.min.css">
	<link rel="stylesheet" href="css/bootstrap-editable.css">
	<link rel="stylesheet" href="css/select2.css">
	<link rel="stylesheet" href="css/select2-bootstrap.css">
	<link rel="stylesheet" href="css/style.css">
    <!--<script src="js/event-handler"></script>-->
    </head>

    <body>
      <div id="content">
		<nav class="navbar navbar-default" role="navigation">
			  <div class="container-fluid">
				<div class="collapse navbar-collapse">
				<a class="navbar-brand" href="index.php">
			        <img alt="Brand" src="img/logo_small.png">
			      </a>
				  <ul class="nav navbar-nav main-nav">
					<li><a href="index.php">Home</a></li>
					<li><a href="gt-making/gt-making.php">GT Making</a></li>
					<li><a href="export.php">Export</a></li>					
					<li><a href="legend.html">Legend</a></li>
					<li class="active" ><a href="#">Settings</a></li>
				  </ul>
				</div><!-- /.navbar-collapse -->
			  </div><!-- /.container-fluid -->
			</nav>
			<div id="content-wrapper">
		    	<h1>Settings</h1>
		    	<div class="clearfix"></div>
		    	<div class="settings row">
		    	  
		    	  <!-- Database connection -->
				  <div class="col-xs-6 col-sm-4">
					    <div id="db-connection-settings" class="settings-panel panel panel-default">
						  <div class="panel-heading">
						    <h3 class="panel-title">Database connection</h3>
						  </div>
						  <div class="panel-body">
						    <form id="db-form" class="form-horizontal">
							  <div class="form-group">
							    <label for="db-user" class="col-sm-3 control-label">User</label>
							    <div class="col-sm-8">
							      <input type="text" class="form-control" id="db-user" placeholder="User">
							    </div>
							  </div>
							  <div class="form-group">
							    <label for="db-password" class="col-sm-3 control-label">Password</label>
							    <div class="col-sm-8">
							      <input type="password" class="form-control" id="db-password" placeholder="Password">
							    </div>
							  </div>
							  <div class="form-group">
							    <label for="db-host" class="col-sm-3 control-label">Host</label>
							    <div class="col-sm-8">
							      <input type="text" class="form-control" id="db-host" placeholder="Host" >
							    </div>
							  </div>
							  <div class="form-group">
							    <label for="db-database" class="col-sm-3 control-label">Database</label>
							    <div class="col-sm-8">
							      <input type="text" class="form-control" id="db-database" placeholder="Database" >
							    </div>
							  </div>
							  <div class="col-sm-offset-6 col-sm-10">
							  	  <button id="db-test" type="button" class="btn btn-default">Test</button>
							      <button type="submit" class="btn btn-primary">Save</button>
							    </div>	
							</form>
							<div class="clearfix"></div>
						  </div>
						</div>				
				  </div>
				  <!-- end of database settings -->
				  
				  <!-- Users settings -->
				  <div class="col-xs-6 col-sm-4">
				  	<div id="user-settings" class="settings-panel panel panel-default">
						  <div class="panel-heading">
						    <h3 class="panel-title">Users</h3>
						  </div>
						  <div class="panel-body">
						  	<form id="add-user-form" class="form-inline">
							  <div class="form-group">
							    <input type="text" class="form-control" id="add-user-name" placeholder="Username">
							  </div>
							  <button type="submit" class="btn btn-primary">Add user</button>
							</form>
							<div class="scrollable-container">
								<div class="scrollable"></div>
							</div>
						  </div>
						</div>
				  
				  </div>
				  <!-- end users settings -->
				  
				  		    	 <!-- Propagation settings -->
				  <div class="col-xs-6 col-sm-4">
				  	<div id="propagation-settings" class="settings-panel panel panel-default">
						  <div class="panel-heading">
						    <h3 class="panel-title">Propagation</h3>
						  </div>
						  <div class="panel-body">
						  	<form id="propagation-form" class="form-horizontal">
							  <div class="form-group">
							    <label for="propagation-motion" class="col-sm-6 control-label">Use motion</label>
							    <div class="col-sm-4">
							      <div class="checkbox">
 						 			<label><input type="checkbox" id="propagation-motion" value=""></label>
    							  </div>
							    </div>
							  </div>
							  <div class="form-group">
							    <label for="propagation-people" class="col-sm-6 control-label">Use people detector</label>
							    <div class="col-sm-4">
							      <div class="checkbox">
 						 			<label><input type="checkbox" id="propagation-people" value=""></label>
    							  </div>
							    </div>
							  </div>
							  <div class="form-group">
							    <label for="propagation-kf" class="col-sm-6 control-label">Use Kalman filter</label>
							    <div class="col-sm-4">
							      <div class="checkbox">
 						 			<label><input type="checkbox" id="propagation-kf" value=""></label>
    							  </div>
							    </div>
							  </div>
							  <div class="col-sm-offset-6 col-sm-10">
							      <button type="submit" class="btn btn-primary">Save</button>
							    </div>	
							</form>
							<div class="clearfix"></div>
						  </div>
						</div>
				  </div>
				  
				  </div>
				  <div class="settings row">
				  
				  
				  <!-- Cameras settings -->
				  <div class="col-xs-6 col-sm-6">
				  	<div id="camera-settings" class="settings-panel panel panel-default">
						  <div class="panel-heading">
						    <h3 class="panel-title">Cameras</h3>
						  </div>
						  <div class="panel-body">
						  	<form id="add-camera-form" class="form-inline">
							  <div class="form-group">
							    <input type="number" class="form-control" id="add-camera-calibration" placeholder="Calibration">
							  </div>
							  <button type="submit" class="btn btn-primary">Add camera</button>
							</form>
							<div class="scrollable-container">
								<div class="scrollable"></div>
							</div>
						  </div>
						</div>
				  </div>		    	
		    	 <!-- end of camera setting -->
		    	 

		    	 
		    	 <!-- POI settings -->
				  <div class="col-xs-6 col-sm-6">
				  	<div id="poi-settings" class="settings-panel panel panel-default">
						  <div class="panel-heading">
						    <h3 class="panel-title">Points of Interest</h3>
						  </div>
						  <div class="panel-body">
						  	<form id="add-poi-form" class="form-inline">
							  <div class="form-group">
							   <input type="number" class="form-control number" id="add-poi-id" placeholder="ID">
							    <label for="add-poi-camera">Camera</label>
							    <input type="number" class="form-control" id="add-poi-camera">
							    <input type="text" class="form-control" id="add-poi-name" placeholder="Name">
							    <input type="number" class="form-control number" id="add-poi-locx" placeholder="X">
							    <input type="number" class="form-control number" id="add-poi-locy" placeholder="Y">
							    <input type="number" class="form-control number" id="add-poi-width" placeholder="Width">	
							    <input type="number" class="form-control number" id="add-poi-height" placeholder="Height">
							    <button type="submit" id="add-poi-submit" class="btn btn-primary">Add POI</button>						    
							  </div>		  
							</form>
							<div class="scrollable-container">
								<div class="scrollable">
								<table class="table table-bordered">
									<thead>
											<tr>
												<th>ID</th>
												<th>Camera</th>
												<th>Name</th>
												<th>X</th>
												<th>Y</th>
												<th>Width</th>
												<th>Height</th>
											</tr>
										</thead>
										<tbody></tbody> 
									</table>
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
		 <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	    <script src="js/jquery-1.11.1.min.js"></script>
	    <!-- Include all compiled plugins (below), or include individual files as needed -->
	    <script src="js/bootstrap.min.js"></script>
	    <script src="js/bootstrap-editable.js"></script>
	    <script src="js/select2.js"></script>
	    <script src="js/settings.js"></script>
	</body>
</html>
