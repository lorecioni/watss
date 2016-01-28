<html>
<head>
<title>WATSS - Setup</title>
<meta name="description" content="Video tagging and results comparison">
<meta name="keywords" content="Video, Ground truth, Keyboard, Automatic">
<meta charset="UTF-8">

<!-- CSS -->
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/bootstrap-theme.min.css">
<link rel="stylesheet" href="css/bootstrap-tagsinput.css">
<link rel="stylesheet" href="css/setup.css">
<!--<script src="js/event-handler"></script>-->
</head>

<body>
		<div id="setup-wrapper">
			<header>
				<img src="img/logo.png" alt="WATSS" id="logo">
				<h1>Setup</h1>
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
				    <label for="frame-folder-name" class="col-sm-3 control-label">Frames folder</label>
				    <div class="col-sm-8">
				      <input type="text" autocomplete="off" class="form-control" id="frame-folder-name" placeholder="Name">
				    </div>
				  </div>
				  <div class="col-sm-offset-2 col-sm-10 form-button">
				  	<button id="verify-frame-folder" type="button" class="btn btn-default">Verify folder</button>
				  </div>	
				  <div class="clearfix"></div>
				  <div class="alert alert-warning" role="alert">No frame folder selected.</div>
				  
				  <!-- Cameras calibration -->
				  <div id="camera-settings">
				  	<h4>Cameras calibration</h4>
				  	<div id="camera-settings-container"></div>
				  </div>
				</form>
			</section>
			
			<!-- SQL script -->
			<section>
				<h3>Data</h3>
				<p>Import data or creating new database.</p>
				<div class="radio">
				  <label>
				    <input type="radio" name="import-data" id="create-new-data" value="new" checked>
				   	Create new data.
				  </label>
				</div>
				<div class="radio">
				  <label>
				    <input type="radio" name="import-data" id="import-data" value="import">
				    Import data from script.
				  </label>
				</div>
				<div class="clearfix"></div>
				<div id="new-data-container" class="data-container">
				  <div class="form-group">
				    <label for="db-name" class="col-sm-3 control-label">Database name</label>
				    <div class="col-sm-8">
				      <input type="text" class="form-control" id="db-name" placeholder="Name">
				    </div>
				  </div>
				  <div class="clearfix"></div>
				  	<h5>Users</h5>
					<p>Set the user list. Type username and press enter or comma to add user.</p>
					<input type="text" id="user-list-input" placeholder="Users" data-role="tagsinput" />
					<div class="clearfix"></div>
				</div>
				
								
				<div id="importing-data-container" class="data-container">
					<p>Import SQL script for loading data.</p>
					<div class="input-group">
		                <span class="input-group-btn" >
		                    <span class="btn btn-primary btn-file">
		                        Browse&hellip; <input id="input-file" type="file">
		                    </span>
		                </span>
		                <input id="filename" type="text" class="form-control" readonly>
		            </div>
		            <div class="col-sm-offset-2 col-sm-10 form-button" id="input-script-check">
					  	<button id="verify-script" type="button" class="btn btn-default">Verify script</button>
					  </div>	
		            <div class="clearfix"></div>
		            <div class="alert alert-warning" role="alert">No SQL script selected.</div>			
				</div>
				</section>
				
				<!-- Install section  -->
				<section id="install-section">
					<h3>Install</h3>
					<p>Check if all the provided information above are correct, then you are ready to install WATSS.</p>
					<div id="checks-container">
						<p class="check-label" id="check-db-connection">Database connection</p>
						<p class="check-label" id="check-db-name">Database name</p>
						<p class="check-label" id="check-frames-folder">Frames folder</p>
						<p class="check-label" id="check-camera-settings">Cameras settings</p>
						<p class="check-label" id="check-users">Users</p>
					</div>
					<button type="button" class="disabled btn btn-primary btn-lg" id="install-button">Install WATSS</button>
					<div class="progress" id="install-progress">
					  <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar"
					  aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%"></div>
					</div>
				</section>

		</div>
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	    <script src="js/jquery-1.11.1.min.js"></script>
	    <!-- Include all compiled plugins (below), or include individual files as needed -->
	    <script src="js/bootstrap.min.js"></script>
	    <script src="js/bootstrap-tagsinput.js"></script>
	    <script src="js/setup.js"></script>
	</body>
</html>
