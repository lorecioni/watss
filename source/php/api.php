<?php
	//Include utils functions
	require_once 'config.php';
	require_once 'utils.php';
	require_once 'queries.php';
	
	if($config->debug){
		error_reporting(E_ALL);
		ini_set("display_errors", 1);
	}

	session_start();
	
	/**
	 * Database connection
	 */
		
	$conFile = parse_ini_file($config->connection);
	$conn = mysql_connect($conFile["host"],$conFile["user"],$conFile["password"]);
    if (!$conn) exit("Error: ".mysql_error());
	mysql_select_db($conFile['db']) or exit("Wrong Database");
	
	/**
	 * Checking requested action
	 */

	if( !isset($_REQUEST['action']) )
		error_500("missing action");

	/**
	 * Initializing queries utilities
	 */
	$QUERIES = new Queries();
	
	switch( $_REQUEST['action'] ) {

		/**
		 * Checking user permission and data validity. Check fram number
		 * camera id validity
		 * 
		 * @return boolean : true if user can proceed
		 */
		case "check-gt-login":
			$to_return = true;	
			if (!isset($_SESSION["user"])){			
				if($_REQUEST["user"]!="" &&  $_REQUEST["camera_id"]!="" && 
					($_REQUEST["frame_id"]!="number" || $_REQUEST["frame_number"]!="")){
						$sql = $QUERIES->getUserIdFromName($_REQUEST['user']);
						$result=mysql_query($sql) or $to_return = false;						
						if ( mysql_num_rows($result) == 0 ) {					
							$to_return = false;
						} else{
							while ($row=mysql_fetch_array($result) ){
								$_SESSION["user"] = $row["userid"];
								$_SESSION["camera_id"] = $_REQUEST["camera_id"];
								$sql = $QUERIES->countNoGroupsByUserName($_REQUEST['user']);
								$result_zero = mysql_query($sql) or $to_return = false;
								
								if (mysql_num_rows($result_zero) == 0) {
									$sql_1= $QUERIES->getUserIdFromName($_REQUEST['user']);
									$result_1 = mysql_query($sql_1) or $to_return = false;
									while ($returned_id = mysql_fetch_array($result_1) ){							
										$sql_2 = $QUERIES->insertGroup(0, 'No group', 0, $returned_id["userid"]);
										$final_result=mysql_query($sql_2) or $to_return = false;
									}
								}
							}
						}						
						
						if ($_REQUEST["frame_id"] == 'first'){
								$sql= $QUERIES->getFirstFrameId($_SESSION['camera_id']);
								$result_2 = mysql_query($sql) or $to_return = false;						
								while ($final_res=mysql_fetch_array($result_2) ){
									$_SESSION["frame_id"] = intval($final_res["id"]);
								}
						}else{
							if ($_REQUEST["frame_id"] == 'FUF'){
								$sql= $QUERIES->getFirstUntaggedFrameId($_SESSION['user'], $_SESSION['camera_id']);
								$result_2 = mysql_query($sql) or $to_return = false;
								while ($final_res=mysql_fetch_array($result_2) ){
									$_SESSION["frame_id"] = intval($final_res["id"]);									
								}		
							} else {
								if ($_REQUEST["frame_id"] == 'number'){
									$_SESSION["frame_id"] = intval($_REQUEST["frame_number"]);
								}
							}
						}
				} else {
					$to_return = false;
				}
			}
			
			jecho($to_return);
			break;

		/**
		 * Getting cameras list
		 */
		case "get-cameras":
			$camera = array();
			$sql = $QUERIES->getCamerasList();
			$result=mysql_query($sql) or
			die ("Error: ".mysql_error());
			while ($row=mysql_fetch_array($result)){
				$camera[] = array("id"=>$row["cameraid"],"text"=>$row["cameraid"]);
			}
			jecho($camera);
			break;
			
		/**
		 * Logging out
		 */
		case "logout":
			unset($_SESSION["user"]);
			unset($_SESSION["frame_id"]);
			unset($_SESSION["camera_id"]);					
			jecho(true);			
			break;

		/**
		 * Getting user name from user id
		 */
		case "get-user":
			$done = true;
			$sql = $QUERIES->getUserNameById($_SESSION['user']);
			$result=mysql_query($sql) or $done = false;
			if($done)
				while ($row=mysql_fetch_array($result))
					$name = $row["name"];
			if($done)
				jecho($name);
			else
				jecho("Unknown user");
			break;
		
		/**
		 * Set the current camera ID
		 */
		case "set-camera":
			$_SESSION["camera_id"] = $_REQUEST["camera_id"];		
		break;

		/**
		 * Returning frame list for navigation, limited by the query and the number of restults
		 */
		case "get-frames":
			$frames = array();
			if (isset($_REQUEST['query']) && strlen($_REQUEST['query']) > 0){
				$sql = $QUERIES->getFramesByQuery($_SESSION['camera_id'], $_REQUEST['query'], $_REQUEST['limit']);
				$result = mysql_query($sql) or
				$frames = array();
				while ($row = mysql_fetch_array($result)){
					array_push($frames, array("id"=>$row["id"],"text"=>$row["txt"]));
				}
				jecho($frames);
			} else {
				$sql = $QUERIES->getFramesForSelect($_SESSION['camera_id'], $_REQUEST['limit']);
				$result = mysql_query($sql) or
				$frames = array();
				while ($row=mysql_fetch_array($result)){
					array_push($frames, array("id"=>$row["id"],"text"=>$row["txt"]));
				}
				jecho($frames);
			}
			break;

		/**
		 * Get people list for the current frame. Retrieve all people of the current frame and generate proposals based
		 * on previous and next frame annotation
		 */
		case "get-people":			
			$people = array();
			$sql = $QUERIES->getPeople($_SESSION['camera_id'], $_SESSION['frame_id'], true);
			$result = mysql_query($sql) or $people = array();	

			while ($row = mysql_fetch_array($result)){
				$previous = true;
				if($row["previous"] == 0){
					//Person in previous frame but not in current, it is a proposal
					$previous = false;
				}
				
				$person = array( 
					"id" => $row["peopleid"], 
					"color" => $row["color"], 
					"angle_face" => $row["gazeAngle_face"], 
					"angle_face_z"=>$row["gazeAngle_face_z"],
					"angle_body" => $row["gazeAngle_body"],
					"angle_body_z" => $row["gazeAngle_body_z"],
					"group" => $row["groupid"],
					"artwork" => $row["poiid"],
					"prev_frame" => $previous, 
					"bb" => array(
							intval($row["bb_x"]),
							intval($row["bb_y"]),
							intval($row["bb_width"]),
							intval($row["bb_height"])
						),
					"bbV" => array(
							intval($row["bbV_x"]), 
							intval($row["bbV_y"]), 
							intval($row["bbV_width"]), 
							intval($row["bbV_height"])
						)
					);
				array_push($people, $person);
			}
			jecho($people);
			break;

		/**
		 * Retrieving group list from database
		 */
		case "get-groups":

			$group = array();
			$group_del = array();
			$group_merge = array();
			if (isset($_REQUEST['query']) && strlen($_REQUEST['query']) > 0){	
				$sql = $QUERIES->getGroups(false, $_REQUEST['query']);
				$result = mysql_query($sql) or	$group = array();
				while ($row=mysql_fetch_array($result)){
					$group[] = array("id"=>$row["groupid"],"text"=>$row["name"],"people"=>$row["people"]);
				}

				$sql = $sql = $QUERIES->getGroups(true, $_REQUEST['query']);
				$result = mysql_query($sql) or $group_del = array();
				while ($row=mysql_fetch_array($result)){
					$group_del[] = array("id"=>$row["groupid"],"text"=>$row["name"],"people"=>0);
				}					
			} else {
				$sql = $QUERIES->getGroups(false, null);
				$result = mysql_query($sql) or $group = array();

				while ($row = mysql_fetch_array($result) ){
					$group[] = array("id" => $row["groupid"], "text" => $row["name"], "people" => $row["people"]);
				}
				
				$sql = $QUERIES->getGroups(true, null);
				$result = mysql_query($sql) or $group_del = array();
				while ($row = mysql_fetch_array($result)){
					$group_del[] = array("id" => $row["groupid"], "text" => $row["name"], "people" => 0);
				}
			}
			foreach ($group_del as $g){
				$found = false;
				for ($i = 0; $i < count($group); $i++){
					if ($group[$i]["id"] == $g["id"]){
						$found=true;								
					}
				}
				if (!$found){
					$group_merge[] = $g;
				}
			}
			$group = array_merge($group, $group_merge);
			array_multisort($group, SORT_ASC);
			jecho($group);			
			break;
			
			
		/**
		 * Find groups that can be removed
		 */
		case "get-deletable":
			$group = array();
			$sql = $QUERIES->getDeletableGroups($_SESSION['user']);
			$result = mysql_query($sql) or $group = array();
			while ($row=mysql_fetch_array($result) ){
				if (intval($row["people"])>0){
					$deletable=false;
				}else{
					$deletable=true;
				}
				array_push($group, array("id"=>$row["groupid"],"deletable"=>$deletable));
			}
			jecho($group);
		break;

		/** 
		 * Adding person to database, if people_id is set a new istance of that person is made, 
		 * else a new person is registred.
		 * 
		 * - Generates random color for the bounding box
		 * - Insert person into the database
		 * **/
		case "add-person":	
			$bb = $config->bb;
			$bbV = $config->bbV;

		    //Query indicator: if true the query has been done
			$done = true;
			
			if (isset($_REQUEST["people_id"])){
				//Retrieve person color
				$sql = $QUERIES->getPersonColor($_REQUEST['people_id'], $_SESSION['camera_id']);
				$result = mysql_query($sql) or $done = false;
				$hex = "";
				while ($row = mysql_fetch_array($result) ){
					$hex = $row['color'];
				}
				
				$sql = $QUERIES->insertPerson($_REQUEST['people_id'], $_SESSION['frame_id'], $_SESSION['camera_id'], $bb->x, $bb->y, $bb->width, $bb->height, 
							$bbV->x, $bbV->y, $bbV->width, $bbV->height, 0, 0, 0, 0, $hex, 0, $_SESSION['user'], 0);	
						
				$result = mysql_query($sql) or $done = false;
				if ($done){
					$person = array("id" => $_REQUEST["people_id"], "color" => $hex,
							"angle_face"=>0,"angle_face_z"=>0,"angle_body"=>0,"angle_body_z"=>0,
							"group" => 0,"artwork" => 0, "prev_frame" => true, 
							"bb" => array($bb->x, $bb->y, $bb->width, $bb->height),
							"bbV" => array($bbV->x, $bbV->y, $bbV->width, $bbV->height));
				}
			} else {
				//Generate random HEX color
				$hex = getRandomColorHEX();
				
			    $sql = $QUERIES->insertGroup(0, 'No group', 0, $_SESSION['user']);
				$result = mysql_query($sql);
				
				$sql = $QUERIES->insertAvatar(0, 0, $config->realPeopleDefaultImg);
				$result = mysql_query($sql) or $done = false;
				if($done){
					$my_id = mysql_insert_id();
					$sql = $QUERIES->insertPerson($my_id, $_SESSION['frame_id'], $_SESSION['camera_id'], $bb->x, $bb->y, $bb->width, $bb->height,
							$bbV->x, $bbV->y, $bbV->width, $bbV->height, 0, 0, 0, 0, $hex, 0, $_SESSION['user'], 0);
					$result = mysql_query($sql) or $done = false;
					if ($done){
						$person = array("id" => $my_id, "color" => $hex, "angle_face" => 0,
								"angle_face_z" => 0, "angle_body" => 0, "angle_body_z" => 0,
								"group" => 0,"artwork" => 0, "prev_frame" => true, 
								"bb" => array($bb->x, $bb->y, $bb->width, $bb->height),
								"bbV" => array($bbV->x, $bbV->y, $bbV->width, $bbV->height));
					}
				}
			}
			//Econding person to json				
			jecho($person);
		break;

		/** 
		 * Updates person attributes
		 * **/
		case "update-person-attribute":
			
			$success=true;
			if( !isset($_REQUEST['id']) ){
				error_500("missing id");
				jecho(0);
			}
			
			$log = '';
			
			if( isset($_REQUEST['color']) ){
				if(checkPerson($_REQUEST['id']) == 1){
					$sql = $QUERIES->updatePersonColor($_REQUEST['color'], $_SESSION['user'], $_REQUEST['id'], $_SESSION['frame_id'], $_SESSION['camera_id']);
					$result=mysql_query($sql) or $success=false;
				} else {
					$bb = $config->bb;
					$bbV = $config->bbV;
					//If person is a proposal and it has been approved, insert it into frame people list
					$sql = $QUERIES->insertPerson(intval($_REQUEST["id"]), $_SESSION['frame_id'], $_SESSION['camera_id'], $bb->x, $bb->y, $bb->width, $bb->height, 
							$bbV->x, $bbV->y, $bbV->width, $bbV->height, 0, 0, 0, 0, $_REQUEST['color'], 0, $_SESSION['user'], 0);	
					$result=mysql_query($sql) or $success = false;
				}
			}
			
			if( isset($_REQUEST['group_id']) ){
				if(checkPerson($_REQUEST['id']) == 1){
					$sql = $QUERIES->updatePersonGroup($_REQUEST['group_id'], $_SESSION['user'], $_REQUEST['id'], $_SESSION['frame_id'], $_SESSION['camera_id']);
					$result = mysql_query($sql) or $success=false;
				} else {
					$bb = $config->bb;
					$bbV = $config->bbV;
					//If person is a proposal and it has been approved, insert it into frame people list
					$sql = $QUERIES->insertPerson(intval($_REQUEST["id"]), $_SESSION['frame_id'], $_SESSION['camera_id'], $bb->x, $bb->y, $bb->width, $bb->height,
							$bbV->x, $bbV->y, $bbV->width, $bbV->height, 0, 0, 0, 0, '#000000', 0, $_SESSION['user'], $_REQUEST['group_id']);
					$result = mysql_query($sql) or $success = false;
				}
			}	
			
			if( isset($_REQUEST['bb']) ){
				if(checkPerson($_REQUEST['id']) == 1){
					$bb = array();
					$bb = $_REQUEST['bb'];
					$sql = $QUERIES->updatePersonBB($bb, $_SESSION['user'], $_REQUEST['id'], $_SESSION['frame_id'], $_SESSION['camera_id']);
					$result = mysql_query($sql) or $success = false;										
				} else {
					$bb = array();
					$bb = $_REQUEST['bb'];
					$bbV = $config->bbV;
					//If person is a proposal and it has been approved, insert it into frame people list
					$sql = $QUERIES->insertPerson(intval($_REQUEST["id"]), $_SESSION['frame_id'], $_SESSION['camera_id'], $bb[0], $bb[1], $bb[2], $bb[3],
							$bbV->x, $bbV->y, $bbV->width, $bbV->height, 0, 0, 0, 0, '#000000', 0, $_SESSION['user'], 0);
					$result = mysql_query($sql) or $success = false;
					}
		    }
			
			if( isset($_REQUEST['bbV']) ){
				if(checkPerson($_REQUEST['id']) == 1){					
					 $bbV = array();
					 $bbV = $_REQUEST['bbV'];
					 $sql = $QUERIES->updatePersonBBV($bbV, $_SESSION['user'], $_REQUEST['id'], $_SESSION['frame_id'], $_SESSION['camera_id']);
					 $result=mysql_query($sql) or $success=false;
				} else {
					$bb = $config->bb;
					$bbV = array();
					$bbV = $_REQUEST['bbV'];
					//If person is a proposal and it has been approved, insert it into frame people list
					$sql = $QUERIES->insertPerson(intval($_REQUEST["id"]), $_SESSION['frame_id'], $_SESSION['camera_id'],$bb->x, $bb->y, $bb->width, $bb->height,
							$bbV[0], $bbV[1], $bbV[2], $bbV[3], 0, 0, 0, 0, '#000000', 0, $_SESSION['user'], 0);
					$result=mysql_query($sql) or $success = false;
				}
			}	
			
			if( isset($_REQUEST['angle_face']) && isset($_REQUEST['angle_face_z'])  ){
				if(checkPerson($_REQUEST['id']) == 1){
					 $sql = $QUERIES->updatePersonAngleFace($_REQUEST['angle_face'], $_REQUEST['angle_face_z'], $_SESSION['user'], $_REQUEST['id'], $_SESSION['frame_id'], $_SESSION['camera_id']);
					 $result = mysql_query($sql) or $success = false;
				} else {
					$bb = $config->bb;
					$bbV = $config->bbV;
					//If person is a proposal and it has been approved, insert it into frame people list
					$sql = $QUERIES->insertPerson(intval($_REQUEST["id"]), $_SESSION['frame_id'], $_SESSION['camera_id'], $bb->x, $bb->y, $bb->width, $bb->height,
							$bbV->x, $bbV->y, $bbV->width, $bbV->height, $_REQUEST['angle_face'], $_REQUEST['angle_face_z'], 0, 0, '#000000', 0, $_SESSION['user'], 0);
					$result = mysql_query($sql) or $success = false;
				}
			}
			
			
			if( isset($_REQUEST['angle_body']) && isset($_REQUEST['angle_body_z'])  ){
				if(checkPerson($_REQUEST['id']) == 1){
  					 $sql = $QUERIES->updatePersonAngleBody($_REQUEST['angle_body'], $_REQUEST['angle_body_z'], $_SESSION['user'], $_REQUEST['id'], $_SESSION['frame_id'], $_SESSION['camera_id']);
					 $result = mysql_query($sql) or $success = false;
				}else{
					$bb = $config->bb;
					$bbV = $config->bbV;
					//If person is a proposal and it has been approved, insert it into frame people list
					$sql = $QUERIES->insertPerson(intval($_REQUEST["id"]), $_SESSION['frame_id'], $_SESSION['camera_id'], $bb->x, $bb->y, $bb->width, $bb->height,
							$bbV->x, $bbV->y, $bbV->width, $bbV->height, 0, 0, $_REQUEST['angle_body'], $_REQUEST['angle_body_z'], '#000000', 0, $_SESSION['user'], 0);
					$result = mysql_query($sql) or $success = false;
				}
			}
			
			if( isset($_REQUEST['opera_id']) ){
				if(checkPerson($_REQUEST['id']) == 1){
					$sql = $QUERIES->updatePersonPoi($_REQUEST['opera_id'], $_SESSION['user'], $_REQUEST['id'], $_SESSION['frame_id'], $_SESSION['camera_id']);
					$result = mysql_query($sql) or $success = false;	
				} else {
					$bb = $config->bb;
					$bbV = $config->bbV;
					//If person is a proposal and it has been approved, insert it into frame people list
					$sql = $QUERIES->insertPerson(intval($_REQUEST["id"]), $_SESSION['frame_id'], $_SESSION['camera_id'], $bb->x, $bb->y, $bb->width, $bb->height,
							$bbV->x, $bbV->y, $bbV->width, $bbV->height, 0, 0, 0, 0, '#000000', $_REQUEST['opera_id'], $_SESSION['user'], 0);
					$result = mysql_query($sql) or $success = false;
				}
			}
						
			if ($success && isset($_REQUEST['id'])){
				$success = createAvatar($_REQUEST['id']);
			}
			
			jecho($success);			
		break;

		/**
		 * Remove person by ID
		 */
		case "remove-person":
			if(isset($_REQUEST['id'])){
				$success = true;
				$sql = $QUERIES->removePersonById($_REQUEST['id'], $_SESSION["frame_id"], $_SESSION["camera_id"]);
				$result = mysql_query($sql) or $success = false;					
				jecho($success);
			}
		break;

		/**
		 * Add a group to database
		 * To appear a group must have at least one person associated
		 */
		case "add-group":
			$success = true;
			$group = array();
			
			if(!isset($_REQUEST['name'])) {
				error_500("Missing parameter: group name");
			}

			$sql = $QUERIES->getMaxGroupId();
			$result = mysql_query($sql) or jecho($group);
			while ($row = mysql_fetch_array($result) ){
				$idvalue = $row["id"];
			}

			$sql = $QUERIES->insertGroup(intval($idvalue) + 1, $_REQUEST['name'], 0, $_SESSION["user"]);
			$result=mysql_query($sql) or $success = false;
			
			if($success){
				$group = array("id" => (intval($idvalue) + 1),"text" => $_REQUEST['name'], "people" => 0);
			}
			
			jecho($group);
		break;

		/**
		 * Remove a group from database
		 */
		case "remove-group":
			$done=true;
			if(!isset($_REQUEST['id']))
				error_500("Missing parameter: group id");			

			$sql = $QUERIES->removeGroup($_REQUEST['id']);
			$result = mysql_query($sql) or $done = false;
			jecho(true);
			break;
		
		/**
		 * Return the frame based on its ID from database
		 */
		case "get-frame":
			$dimension = array();
			$frame = array();	
			$done = true;

			if( !isset($_REQUEST["frame_id"])){
				$myframe = $_SESSION["frame_id"];

			} else {
				$myframe = $_REQUEST["frame_id"];
				$_SESSION["frame_id"] = $_REQUEST["frame_id"];
			}
			
			$sql = $QUERIES->getFrameById($myframe, $_SESSION['camera_id']);
			$result = mysql_query($sql) or $done = false;
			if ($done == true){
				while ($row = mysql_fetch_array($result) ){
					$dimension = getimagesize("../frames/".$row["path"]);
					$frame = array(
						"background" => "../frames/".$row["path"],
						"width" => $dimension[0], 
						"height" => $dimension[1], 
						"frame_id" => $myframe);
				}
			}
			jecho($frame);
		break;
		
		/**
		 * Returns the artworks list from the database
		 */
		case "get-artworks":
			$artwork = array();
			if (isset($_REQUEST['query']) && strlen($_REQUEST['query']) > 0){	
					$sql = $QUERIES->getPoisByQuery($_REQUEST['query'], $_SESSION["camera_id"]);
					$result = mysql_query($sql) or die ("Error: ".mysql_error());
					if ( mysql_num_rows($result) == 0 ) $artwork = array();
					while ($row = mysql_fetch_array($result) ){
						$artwork[] = array("id"=>$row["poiid"],"cameraid"=>$row["cameraid"],"location_x"=>$row["location_x"],"location_y"=>$row["location_y"],"width"=>$row["width"],"height"=>$row["height"],"text"=>$row["name"] );
					}
			} else {
					$sql = $QUERIES->getPois($_SESSION["camera_id"]);
					$result = mysql_query($sql) or die ("Error: ".mysql_error());
					if ( mysql_num_rows($result)==0 ) $artwork = array();
					while ($row = mysql_fetch_array($result) ){
						$artwork[] = array("id"=>$row["poiid"],"cameraid"=>$row["cameraid"],"location_x"=>$row["location_x"],"location_y"=>$row["location_y"],"width"=>$row["width"],"height"=>$row["height"],"text"=>$row["name"] );
					}
			}
			jecho($artwork);	
		
		break;
		
		/**
		 * Return list of id and image avatar url of the people at the selected frame id
		 */
		case "get-realpeople":			
			$realpeople = array();	
			$sql = $QUERIES->getAvatarList($_SESSION["frame_id"], $_SESSION["camera_id"]);
			$result = mysql_query($sql) or $realpeople = array();	
			if ( mysql_num_rows($result) == 0 ) $realpeople = array();	
			while ($row = mysql_fetch_array($result) ){
				if(end(explode("/", $row["image"])) != 'default.png'){
					$url = implode(".", explode(".", $row["image"], -1));
					$realpeople[] = array("id"=>$row["peopleid"],"image"=>$url.".jpg");
				} else {
					$realpeople[] = array("id"=>$row["peopleid"],"image"=>$row["image"]);
				}
			}
			jecho($realpeople);
		break;
		
		/**
		 * Returns the frames list for the timeline. Each frame has associated the list of person ID
		 * present in that frame; based on a frame ID (current).
		 * Timeline starts from k previous frames and end after k frames from the selected one.
		 *
		 * @param frame_id : the current frame id
		 * @param limit : k value
		 * @return frames list array (frame id, people id list for each frame)
		 */
		case "get-timeline-frames":
				
			$output = new stdClass();
			$frames = array();
			$done = true;
				
			//Check if params are valid
			if( !isset($_SESSION["frame_id"])){
				error_500("Missing information: frame id");
			}
			
			$frame_id = intval($_SESSION['frame_id']);
			$output->current = $frame_id;
		
			//Retrieving previous and next frames
			if(isset($_REQUEST['limit'])){
				$sql = $QUERIES->getFrameIdList($_SESSION['camera_id'], $_REQUEST['limit'], $frame_id);
			} else {
				$sql = $QUERIES->getFrameIdList($_SESSION['camera_id'], null, null);
			}
			$result = mysql_query($sql) or $done = false;
			while ($row = mysql_fetch_array($result) ){
				$frame = new stdClass();
				$frame->id = $row["frameid"];
				array_push($frames, $frame);
			}
			
			//Loading people for each frame
			foreach ($frames as $frame){
				$people = array();
				$sql = $QUERIES->getPeopleFrame($_SESSION["camera_id"], $frame->id);
				$result = mysql_query($sql) or $done = false;
				while ($row = mysql_fetch_array($result) ){
					$person = new stdClass();
					$person->id = $row["peopleid"];
					$person->color = $row["color"];					
					array_push($people, $person);
				}
				$frame->people = $people;
			}
			$output->frames = $frames;
				
			jecho($output);
			break;
			
		/**
		 * Generates proposals for a single person for next frames based on current and previous annotation
		 * Calls python script: uses a combination of person motion and a Kalman filter for a prediction based
		 * on previous person annotation.
		 * Called by extending person annotation in timeline
		 */
		case 'propagate':
			$success = true;
			$log = '';
			
			$personid = $_REQUEST['person'];
			$length = $_REQUEST['length'];
			$frame = $_REQUEST['frames'];
			$camera = $_SESSION['camera_id'];
			
			//Previous frames
			$path = '';
			$bb = new stdClass();
			$sql = $QUERIES->getPreviousFrameBB($frame, $camera, $personid);			
			$result = mysql_query($sql) or $success = false;
			while ($row = mysql_fetch_array($result)){
				$path = $row['path'];
				$bb->x = $row['bb_x'];
				$bb->y = $row['bb_y'];
				$bb->width = $row['bb_width'];
				$bb->height = $row['bb_height'];
			}
						
			//Building python command
			$command = $config->python_interpreter.' '.$config->predict_script_path;
			
			$command .= " -x ".$bb->x." -y ".$bb->y." -width ".$bb->width." -height ".$bb->height;
			$command .= " -camera ".$camera;
			$command .= " -frames ".$path;
			$command .= " -predict";
			
			$sql = $QUERIES->getNextFramesPath($frame, $camera, $length);
			$result = mysql_query($sql) or $success = false;
			while ($row = mysql_fetch_array($result)){
				$command .= " ".$row['path'];				
			}
		
			$output = shell_exec($command);
			$output = preg_replace('~[[:cntrl:]]~', '', $output);
			$output = preg_replace('~[.[:cntrl:]]~', '', $output);
			$predictions = json_decode($output);
		
			for ($i = 0; $i < count($predictions); $i++){
				//Retrieve person color
				$sql = $QUERIES->getPersonColor($personid, $camera);
				$result = mysql_query($sql) or $success = false;
				$hex = "";
				while ($row = mysql_fetch_array($result) ){
					$hex = $row['color'];
				}
				
				$sql = $QUERIES->insertPerson($personid, ($frame + $i + 1), $camera, $predictions[$i]->x, $predictions[$i]->y, $predictions[$i]->width, $predictions[$i]->height,
						$predictions[$i]->x, $predictions[$i]->y, $predictions[$i]->width, $predictions[$i]->height, 0, 0, 0, 0, $hex, 0, $_SESSION['user'], 0);
				$result = mysql_query($sql) or $success = false;
				if(!$success) break;
			}

			jecho($command);
			break;
					
		/**
		 * Export function for DB
		 */
		case "export":	
			
			//Output headers so that the file is downloaded rather than displayed
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename=data.csv');

			//Create a file pointer connected to the output stream	
			$output = fopen('php://output', 'w');		

			$people = array();	
			$sql = $QUERIES->getExportQuery();
			$result=mysql_query($sql) or $people = array();

			//Loop over the rows, outputting them
			while ($row = mysql_fetch_assoc($result)) fputcsv($output, $row);	
            break;

	}

	
	
	
	
   

?>