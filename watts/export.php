<html>
    <head>
	<meta name="description" content="Video tagging and results comparison">
	<meta name="keywords" content="Video, Ground truth, Keyboard, Automatic">
	<meta charset="UTF-8">
	
	<!-- CSS -->
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/bootstrap-theme.min.css">

        <!--<script src="js/event-handler"></script>-->
    </head>

    <body>
		<div style="margin: 15px 15px;">
			<nav class="navbar navbar-default" role="navigation">
			  <div class="container-fluid">
				<!-- Brand and toggle get grouped for better mobile display -->
				<!--<div class="navbar-header">
				  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				  </button>
				  <a class="navbar-brand" href="#">Home</a>
				</div>-->

				<!-- Collect the nav links, forms, and other content for toggling -->
				<div class="collapse navbar-collapse">
				  <ul class="nav navbar-nav">
					<li><a href="#">Home</a></li>
					<li><a href="gt-making/gt-making.php">GT Making</a></li>
					<li class="active"><a href="export.php">Export Results</a></li>					
					<li><a href="legend.html">Legend</a></li>
				  </ul>
				</div><!-- /.navbar-collapse -->
			  </div><!-- /.container-fluid -->
			</nav>

		    <h1>Press the Button to Export Results</h1>
			<a href="#" class="btn btn-default btn-sm" id="export_res"> Export </a>	
		</div>
			
 <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	    <!-- Include all compiled plugins (below), or include individual files as needed -->
	    <script src="../js/bootstrap.min.js"></script>
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
