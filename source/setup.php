<html>
<head>
<title>WATSS - Setup</title>
<meta name="description" content="Video tagging and results comparison">
<meta name="keywords" content="Video, Ground truth, Keyboard, Automatic">
<meta charset="UTF-8">

<!-- CSS -->
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/bootstrap-theme.min.css">
<link rel="stylesheet" href="css/bootstrap-editable.css">
<link rel="stylesheet" href="css/select2.css">
<link rel="stylesheet" href="css/select2-bootstrap.css">
<link rel="stylesheet" href="css/setup.css">
<!--<script src="js/event-handler"></script>-->
</head>

<body>
		<div id="setup-wrapper">
			<header>
				<h1>WATSS Setup</h1>
			</header>

		    <!-- Welcome section -->
			<section>
				<h3>Welcome</h3>
				<p>Welcome to the WATSS installation process. Fill information below to start using this tool.
				Read the <a href="help.html">Readme</a> if you need help.</p>
			</section>
			
			<!-- Database section -->
			<section>
				<h3>Database connection</h3>
				<p>Provide information about database connection.</p>
				<form id="db-connection-form" class="form-horizontal">
				  <div class="form-group">
				    <label for="db-user" class="col-sm-2 control-label">Username</label>
				    <div class="col-sm-10">
				      <input type="text" autocomplete="off" class="form-control" id="db-user" placeholder="User">
				    </div>
				  </div>
				  <div class="form-group">
				    <label for="db-password" class="col-sm-2 control-label">Password</label>
				    <div class="col-sm-10">
				      <input type="password" autocomplete="off" class="form-control" id="db-password" placeholder="Password">
				    </div>
				  </div>
				  <div class="form-group">
				    <label for="db-host" class="col-sm-2 control-label">Host</label>
				    <div class="col-sm-10">
				      <input type="text" autocomplete="off" class="form-control" id="db-host" placeholder="Host">
				    </div>
				  </div>
				  <div class="col-sm-offset-2 col-sm-10 form-button">
				  	<button type="submit" class="btn btn-default">Test connection</button>
				  </div>				  
				</form>
				<div class="clearfix"></div>
			</section>	
			
			<!-- Frame path section -->
			<section>
				<h3>Cameras</h3>
				Provide frames information. Follow this steps:
				<ul>
					<li>Create a frame folder in the root directory.</li>
					<li>Create a folder inside it the for each camera to use with WATSS (<i>e.g. 1,2,...N  in the frames folder, using only numeric notation</i>).
					<li>Copy image files inside the correct camera folder.</li>
				
				</ul>
				<p>Images have to be ordered considering the name and the extension of the images.<br>
				Provide the main frames folder name below.</p>
				
				<form id="cameras-form" class="form-horizontal">
				  <div class="form-group">
				    <label for="frame-folder" class="col-sm-3 control-label">Frames folder</label>
				    <div class="col-sm-8">
				      <input type="text" autocomplete="off" class="form-control" id="frame-folder" placeholder="Name">
				    </div>
				  </div>
				  <div class="col-sm-offset-2 col-sm-10 form-button">
				  	<button type="submit" class="btn btn-default">Verify folder</button>
				  	<div class="clearfix"></div>
				  </div>	
				  
				  <p id="camera-number-message">No cameras detected on selected folder.</p>
				</form>
			</section>
			
			<!-- SQL script -->
			<section>
				<h3>Data</h3>
				<p>Import data or creating new database.</p>
			</section>
			
			
			<!-- Users section -->
			<section id="users-section">
				<h3>Users</h3>
				<p>Provide users information below. Each user will be able to create new users.</p>
				<form class="form-horizontal">
				  <div class="form-group">
				    <label for="database-user" class="col-sm-2 control-label">User</label>
				    <div class="col-sm-10">
				      <input type="text" autocomplete="off" class="form-control" value="root" id="database-user" placeholder="Name">
				    </div>
				  </div>
				  <div class="col-sm-offset-2 col-sm-10">
				  	<button type="submit" id="add-user-row" class="btn btn-default">Add new user</button>
				  </div>				  
				</form>
				<div class="clearfix"></div>
			</section>
		</div>
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	    <script src="js/jquery-1.11.1.min.js"></script>
	    <!-- Include all compiled plugins (below), or include individual files as needed -->
	    <script src="js/bootstrap.min.js"></script>
	    <script src="js/bootstrap-editable.js"></script>
	    <script src="js/select2.js"></script>
	    <script src="js/setup.js"></script>
	</body>
</html>
