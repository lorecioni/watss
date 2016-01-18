<!DOCTYPE html>
<html>
	<head>
		<title>Bargello - Frame</title>
		<meta name="description" content="Video tagging and results comparison">
		<meta name="keywords" content="Video, Ground truth, Keyboard, Automatic">
		<meta charset="UTF-8">

		<!-- CSS -->
		<link rel="stylesheet" href="../css/bootstrap.min.css">
		<link rel="stylesheet" href="../css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="../css/bootstrap-colorpicker.min.css">
		<link rel="stylesheet" href="../css/typeahead.css">
		<link rel="stylesheet" href="../css/bootstrap-editable.css">
		<link rel="stylesheet" href="../css/select2.css">
		<link rel="stylesheet" href="../css/select2-bootstrap.css">
		<link rel="stylesheet" href="../css/image-picker.css">
		<link rel="stylesheet" href="../css/gt-making.css">
		<link rel="stylesheet" href="../css/ui-lightness/jquery-ui-1.10.4.css">
	</head>

	<body>
		<nav class="navbar navbar-default" role="navigation">
			<div class="container-fluid">
				<!-- Collect the nav links, forms, and other content for toggling -->
				<div class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
						<li><a href="../index.php">Home</a></li>
						<li class="active"><a href="#">GT Making</a></li>
						<li><a href="../export.php">Export Results</a></li>				
						<li><a href="../legend.html">Legend</a></li>
					</ul>
<ul class="nav navbar-nav navbar-right">
         <li><a id="welcome-person"></a></li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-cog"></span></a>
          <ul class="dropdown-menu">
            <li><a href="#" id="logout-button">Logout</a></li>
          </ul>
        </li>
      </ul>
				</div><!-- /.navbar-collapse -->
			</div><!-- /.container-fluid -->
		</nav>

		<!-- video goes here -->
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-7">
				<div style="width:680px;height:425px;">
					<div id="video-box">					
						
					</div>
				</div>
					<div class="form-inline frame-form row">
						<div class="form-group col-md-6">
							<label for="goto-frame">Change Frame:&nbsp;</label>
							<input type="text" class="form-control frame-number" id="goto-frame" placeholder="Frame number">
  						</div>
						<div class="col-md-4">						
							<a href="#" class="btn btn-default btn-sm" id="prev-frame">
								<span class="glyphicon glyphicon-chevron-left"></span> Prev Frame</a>
							<a href="#" class="btn btn-primary btn-sm" id="next-frame">
								Next Frame <span class="glyphicon glyphicon-chevron-right"></span></a>
						</div>
						<div class="form-group col-md-2">
							<button class="zoom-out btn btn-default btn-sm"><span class="glyphicon glyphicon-minus"></span></button>
							<button class="zoom-in btn btn-primary btn-sm"><span class="glyphicon glyphicon-plus"></span></button>
						</div>
					</div>			
				</div>


				<div class="col-md-5">
					<table id="people-table" class="table table-condesed table-hover not-update">
						<thead>
							<tr>
								<th>ID</th>
								<th>Color</th>
								<th>Face</th>
								<th>Body</th>
								<th>Group</th>
								<th>POI</th>
								<th></th>
							</tr>
						</thead>
						<tbody id="people-table-tbody">
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
						</tbody>
					</table>
					<div class="row">					
						<div class="add-people-link">
							<a id="open-modal-add-person" data-toggle="modal" data-target="#insertPersonModal" href>Add person</a>
						</div>
					</div>
					<!-- Add person modal -->
					<div class="modal fade" id="insertPersonModal" tabindex="-1" role="dialog" aria-labelledby="insertPersonModalLabel" aria-hidden="true">
					  <div class="modal-dialog modal-lg">
						<div class="modal-content">
						  <div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title" id="insertPersonModalLabel" id="add-person">Insert Person</h4>
						  </div>
						  <div class="modal-body">
								<div class="panel panel-default">
								  <div class="panel-heading"><div class="radio">
									<input type="radio" name="personRadios" id="personRadios1" value="add-person" checked>
									Brand New Person</div></div>	
								</div>
								<div class="panel panel-default">
								  <div class="panel-heading"><div class="radio">
									<input type="radio" name="personRadios" id="personRadios2" value="add-prev-person">
									Choose a Person Already in the DB</div></div>
								  <div class="panel-body">
									<select class="image-picker" id="prev-person-picker"></select>
								  </div>
								</div>
						  </div>
						  <div class="modal-footer">
							<button type="button" class="btn btn-primary" data-dismiss="modal" id="add-person">Add Person</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>						
						  </div>
						</div>
					  </div>
					</div>					

					<table id="groups-table" class="table table-condesed table-hover">
						<thead>
							<tr>
								<th>ID</th>
								<th>Name</th>
								<th>NPeople</th>
								<th></th>
							</tr>
						</thead>
						<tbody id="groups-table-tbody">
							<tr>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>
						</tbody>
					</table>					
						<div id="groups-pagination"></div>
						<div class="row">					
							<div class="add-group-link">
								<a data-toggle="modal" data-target="#insertGroupModal" href>Add group</a>
							</div>
						</div>
					</div>
					<!-- Add group modal -->
					<div class="modal fade" id="insertGroupModal" tabindex="-1" role="dialog" aria-labelledby="insertGroupModalLabel" aria-hidden="true">
					  <div class="modal-dialog">
						<div class="modal-content">
						  <div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title" id="insertGroupModalLabel" id="open-modal-add-group">Insert Group</h4>
						  </div>
						  <div class="modal-body">
								<div class="row">
									<div class="col-xs-6">
										<label for="addGroupName">Name</label>
										<input type="text" class="form-control" id="addGroupName"/>
									</div>									
								</div>
						  </div>
						  <div class="modal-footer">
							<button type="button" class="btn btn-primary" data-dismiss="modal" id="add-group">Add Group</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>						
						  </div>
						</div>
					  </div>
					</div>
				</div>
							
			</div>			
		</div>

		<!-- Check info modal -->
		<div class="modal fade" id="checkInfoModal" tabindex="-1" role="dialog" aria-labelledby="checkInfoModalLabel" aria-hidden="true">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
				<h4 class="modal-title" id="checkInfoModalLabel">Insert Info</h4>
			  </div>
			  	<div class="modal-body">
					<div class="row">
						<div class="col-xs-6">
							<label for="username">Name</label>
							<input type="text" class="form-control" id="username">
						</div>	
						<div class="col-xs-4">
							<label for="cameras">Camera id</label>
							<input type="text" class="form-control" id="cameras">
						</div>								
					</div>
					<br>
					<label>Frame id:</label>
					<div class="radio">
					  <label>
						<input type="radio" name="frameRadios" id="optionsRadios1" value="FUF" checked>
						First untagged frame (by me)
					  </label>
					</div>
					<div class="radio">
					  <label>
						<input type="radio" name="frameRadios" id="optionsRadios2" value="first">
						First frame
					  </label>
					</div>
					<div class="radio">
					  <label>
						<input type="radio" name="frameRadios" id="optionsRadios3" value="number">
						Frame number: <input type="text" id="frame-number" class="frame-number">
					  </label>
					</div>
			  	</div>
			  <div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal" id="check-info">Send</button>					
			  </div>
			</div>
		  </div>
		</div>

		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>-->
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="../js/jquery-1.11.1.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
		<script type="text/javascript" src="../js/jquery.bootpag.min.js"></script>
		<script src="../js/bootstrap-colorpicker.min.js"></script>
		<script src="../js/jquery.hotkeys.js"></script>
		<script src="../js/bloodhound.min.js"></script>
		<script src="../js/typeahead.bundle.min.js"></script>
		<script src="../js/typeahead.jquery.min.js"></script>
		<script src="../js/handlebars-v1.3.0.js"></script>
		<script src="../js/bootstrap-editable.js"></script>
		<script src="../js/select2.js"></script>
		<script src="../js/jquery.dataTables.min.js"></script>
		<script src="../js/jquery-DT-pagination.js"></script>
		<script src="../js/jquery-ui-1.10.4.js"></script>
		<script src="../js/three.min.js"></script>
		<script src="../js/image-picker.min.js"></script>
		<script src="../js/handle-box.js"></script>
		<script src="../js/handle-keyboard.js"></script>
		<script src="../js/handle-cone.js"></script>
		<script src="../js/functions.js"></script>
		<script src="../js/event-handler.js"></script>
		<script src="../js/panzoom.js"></script> 
		<script src="../js/jquery.mousewheel.js"></script> 

	</body>
</html>