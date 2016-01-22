<html>
    <head>
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
    
    	<?php
    	include 'php/config.php';
		?>
    	
		<nav class="navbar navbar-default" role="navigation">
			  <div class="container-fluid">

				<!-- Collect the nav links, forms, and other content for toggling -->
				<div class="collapse navbar-collapse">
				  <ul class="nav navbar-nav main-nav">
					<li><a href="index.php">Home</a></li>
					<li><a href="gt-making/gt-making.php">GT Making</a></li>
					<li><a href="export.php">Export Results</a></li>					
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
				  <div class="col-xs-6 col-sm-4">
				  <!-- Database connection -->
					    <div class="settings-panel panel panel-default">
						  <div class="panel-heading">
						    <h3 class="panel-title">Database connection</h3>
						  </div>
						  <div class="panel-body">
						    <form id="db-form" class="form-horizontal">
							  <div class="form-group">
							    <label for="db-user" class="col-sm-3 control-label">User</label>
							    <div class="col-sm-8">
							      <input type="text" class="form-control" id="db-user" placeholder="User" value="<?php echo $dbConnection["user"];?>">
							    </div>
							  </div>
							  <div class="form-group">
							    <label for="db-password" class="col-sm-3 control-label">Password</label>
							    <div class="col-sm-8">
							      <input type="text" class="form-control" id="db-password" placeholder="Password" value="<?php echo $dbConnection["password"];?>">
							    </div>
							  </div>
							  <div class="form-group">
							    <label for="db-host" class="col-sm-3 control-label">Host</label>
							    <div class="col-sm-8">
							      <input type="text" class="form-control" id="db-host" placeholder="Host" value="<?php echo $dbConnection["host"];?>">
							    </div>
							  </div>
							  <div class="form-group">
							    <label for="db-database" class="col-sm-3 control-label">Database</label>
							    <div class="col-sm-8">
							      <input type="text" class="form-control" id="db-database" placeholder="Database" value="<?php echo $dbConnection["db"];?>">
							    </div>
							  </div>
							  <div class="col-sm-offset-8 col-sm-10">
							      <button type="submit" class="btn btn-primary">Save</button>
							    </div>	
							</form>
						  </div>
						</div>
						<!-- end of database settings -->
				  
				  
				  </div>
				  <div class="col-xs-6 col-sm-4">
				  	<div class="settings-panel panel panel-default">
						  <div class="panel-heading">
						    <h3 class="panel-title">Users</h3>
						  </div>
						  <div class="panel-body">
						  </div>
						</div>
				  
				  </div>
				  <div class="col-xs-6 col-sm-4">
				  	<div class="settings-panel panel panel-default">
						  <div class="panel-heading">
						    <h3 class="panel-title">Cameras</h3>
						  </div>
						  <div class="panel-body">
						    
						  </div>
						</div>
				  
				  </div>
				</div>
		    	
		    
		   		
		    
		    </div>
		 <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	    <script src="js/jquery-1.11.1.min.js"></script>
	    <!-- Include all compiled plugins (below), or include individual files as needed -->
	    <script src="js/bootstrap.min.js"></script>
	    <script src="js/settings.js"></script>
	</body>
</html>
