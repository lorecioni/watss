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
		<div>
			<nav class="navbar navbar-default" role="navigation">
			  <div class="container-fluid">

				<!-- Collect the nav links, forms, and other content for toggling -->
				<div class="collapse navbar-collapse main-nav">
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

		    <h1>Press the Button to Export Results</h1>
			<a href="#" class="btn btn-default btn-sm" id="export_res"> Export </a>	
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
