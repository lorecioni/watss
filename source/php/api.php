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
						$result=mysql_query($sql) or die ("Error: ".mysql_error());
						if ( mysql_num_rows($result)==0 ) {					
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
									while ($returned_id=mysql_fetch_array($result_1) ){							
										$sql_2 = $QUERIES->insertGroup(0, 'No group',  $returned_id["userid"]);
										$final_result=mysql_query($sql_2) or $to_return = false;
									}
								}
							}
						}
						if ($_REQUEST["frame_id"] == 'first'){
								$sql= $QUERIES->getFirstFrameId();
								$result_2 = mysql_query($sql) or $to_return = false;						
								while ($final_res=mysql_fetch_array($result_2) ){
									$_SESSION["frame_id"] = intval($final_res["id"]);
								}					
						}else{
							if ($_REQUEST["frame_id"] == 'FUF'){
								$sql= $QUERIES->getFirstUntaggedFrame($_SESSION['user'], $_SESSION['camera_id']);
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
			$sql = $QUERIES->getCameras();
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
		    //Generate random HEX color 
			$hex = getRandomColorHEX();

			$bb = $config->bb;
			$bbV = $config->bbV;

		    //Query indicator: if true the query has been done
			$done = true;			
			if (isset($_REQUEST["people_id"])){
				$sql = $QUERIES->insertPerson($_REQUEST['people_id'], $_SESSION['frame_id'], $_SESSION['camera_id'], $bb->x, $bb->y, $bb->width, $bb->height, 
							$bbV->x, $bbV->y, $bbV->width, $bbV->height, 0, 0, 0, 0, $hex, 0, $_SESSION['user'], 0);	
						
				$result = mysql_query($sql) or $done = false;
				if ($done){
					$person = array("id" => $_REQUEST["people_id"], "color"=>$hex,"angle_face"=>0,"angle_face_z"=>0,"angle_body"=>0,"angle_body_z"=>0,
							"group"=>0,"artwork"=>0, "prev_frame"=>true, "bb"=>array($bb->x, $bb->y, $bb->width, $bb->height),
							"bbV"=>array($bbV->x, $bbV->y, $bbV->width, $bbV->height));
				}
			} else {
			    $sql="INSERT INTO `groups` (`groupid`,`name`,`deleted`,`userid`) VALUES (0, 'No group', 0,".$_SESSION["user"].");";
				$result = mysql_query($sql);
				
				$sql="INSERT INTO `real_people` (`face`,`face_z`,`image`) VALUES (0, 0,'../img/real_people/default.png')";
				$result = mysql_query($sql) or $done = false;
				if($done){
					$my_id = mysql_insert_id();
					$sql="INSERT INTO `people` (`peopleid`,`frameid`, `cameraid`, `bb_x`, `bb_y`, `bb_width`, `bb_height`, `bbV_x`, `bbV_y`, `bbV_width`, `bbV_height`, `gazeAngle_face`, `gazeAngle_face_z`, `gazeAngle_body`, `gazeAngle_body_z`, `color`, `poiid`, `userid`, `groupid`) VALUES
						(".$my_id.", ".$_SESSION["frame_id"].", ".$_SESSION["camera_id"].", ".$bb->x.", ".$bb->y.", ".$bb->width.", ".$bb->height.", ".$bbV->x.", ".$bbV->y.", ".$bbV->width.", ".$bbV->height.", 0, 0, 0, 0, '".$hex."', 0, ".$_SESSION["user"].", 0);";
					$result=mysql_query($sql) or $done = false;
					if ($done){
						$person = array("id"=>$my_id,"color"=>$hex,"angle_face"=>0,"angle_face_z"=>0,"angle_body"=>0,"angle_body_z"=>0,"group"=>0,"artwork"=>0, "prev_frame"=>true, "bb"=>array($bb->x, $bb->y, $bb->width, $bb->height),"bbV"=>array($bbV->x, $bbV->y, $bbV->width, $bbV->height));
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

					$sql="UPDATE `people` SET `color`='".$_REQUEST['color']."' ,userid='".$_SESSION["user"]."' WHERE  `peopleid`=".$_REQUEST['id']." AND `frameid` = ".$_SESSION["frame_id"]." AND `cameraid` = ".$_SESSION["camera_id"]."";

					$result=mysql_query($sql) or $success=false;
				} else {
					//If person is a proposal and it has been approved, insert it into frame people list
					$sql="INSERT INTO `people` (`peopleid`,`frameid`, `cameraid`, `bb_x`, `bb_y`, `bb_width`, `bb_height`, `bbV_x`, `bbV_y`, `bbV_width`, `bbV_height`, `gazeAngle_face`, `gazeAngle_face_z`, `gazeAngle_body`, `gazeAngle_body_z`, `color`, `poiid`, `userid`, `groupid`) VALUES
						(".intval($_REQUEST["id"]).", ".$_SESSION["frame_id"].", ".$_SESSION["camera_id"].", 300, 200, 20, 30, 300, 200, 20, 30, 0, 0, 0, 0, '".$_REQUEST['color']."', 0, ".$_SESSION["user"].", 0);";

					$result=mysql_query($sql) or $success = false;
				}
			}
			
			if( isset($_REQUEST['group_id']) ){
				if(checkPerson($_REQUEST['id']) == 1){

					$sql="UPDATE `people` SET `groupid`='".$_REQUEST['group_id']."', userid='".$_SESSION["user"]."' WHERE  `peopleid`=".$_REQUEST['id']." AND `frameid` = ".$_SESSION["frame_id"]." AND `cameraid` = ".$_SESSION["camera_id"]."";

					$result=mysql_query($sql) or $success=false;	

				} else {
					$sql="INSERT INTO `people` (`peopleid`,`frameid`, `cameraid`, `bb_x`, `bb_y`, `bb_width`, `bb_height`, `bbV_x`, `bbV_y`, `bbV_width`, `bbV_height`, `gazeAngle_face`, `gazeAngle_face_z`, `gazeAngle_body`, `gazeAngle_body_z`, `color`, `poiid`, `userid`, `groupid`) VALUES
						(".intval($_REQUEST["id"]).", ".$_SESSION["frame_id"].", ".$_SESSION["camera_id"].", 300, 200, 20, 30, 300, 200, 20, 30, 0, 0, 0, 0, '#000000', 0, ".$_SESSION["user"].", ".$_REQUEST['group_id'].");";

					$result=mysql_query($sql) or $success = false;
				}
			}	
			
			if( isset($_REQUEST['bb']) ){
				if(checkPerson($_REQUEST['id']) == 1){
					$bb = array();
					$bb = $_REQUEST['bb'];

					$sql="UPDATE `people` SET `bb_x`=".$bb[0].",`bb_y`=".$bb[1].",`bb_width`=".$bb[2].",`bb_height`=".$bb[3].", userid=".$_SESSION["user"]." WHERE  `peopleid`=".$_REQUEST['id']." AND `frameid` = ".$_SESSION["frame_id"]." AND `cameraid` = ".$_SESSION["camera_id"]."";

					$result=mysql_query($sql) or $success=false;
				}else{
					$bb = array();
					$bb = $_REQUEST['bb'];
					$sql="INSERT INTO `people` (`peopleid`,`frameid`, `cameraid`, `bb_x`, `bb_y`, `bb_width`, `bb_height`, `bbV_x`, `bbV_y`, `bbV_width`, `bbV_height`, `gazeAngle_face`, `gazeAngle_face_z`, `gazeAngle_body`, `gazeAngle_body_z`, `color`, `poiid`, `userid`, `groupid`) VALUES (".$_REQUEST["id"].", ".$_SESSION["frame_id"].", ".$_SESSION["camera_id"].", ".$bb[0].", ".$bb[1].", ".$bb[2].", ".$bb[3].", 300, 200, 20, 30, 0, 0, 0, 0, '#000000', 0, ".$_SESSION["user"].", 0);";

					$result=mysql_query($sql) or $success = false;
				}
		    }
			
			if( isset($_REQUEST['bbV']) ){
				if(checkPerson($_REQUEST['id']) == 1){					
					 $bbV = array();
					 $bbV = $_REQUEST['bbV'];

					 $sql="UPDATE `people` SET `bbV_x`=".$bbV[0].",`bbV_y`=".$bbV[1].",`bbV_width`=".$bbV[2].",`bbV_height`=".$bbV[3].", userid=".$_SESSION["user"]." WHERE  `peopleid`=".$_REQUEST['id']." AND `frameid` = ".$_SESSION["frame_id"]." AND `cameraid` = ".$_SESSION["camera_id"]."";

					 $result=mysql_query($sql) or $success=false;
				} else {
					$bbV = array();
					$bbV = $_REQUEST['bbV'];
					$sql="INSERT INTO `people` (`peopleid`,`frameid`, `cameraid`, `bb_x`, `bb_y`, `bb_width`, `bb_height`, `bbV_x`, `bbV_y`, `bbV_width`, `bbV_height`, `gazeAngle_face`, `gazeAngle_face_z`, `gazeAngle_body`, `gazeAngle_body_z`, `color`, `poiid`, `userid`, `groupid`) VALUES
						(".$_REQUEST["id"].", ".$_SESSION["frame_id"].", ".$_SESSION["camera_id"].", 300, 200, 20, 30, ".$bbV[0].", ".$bbV[1].", ".$bbV[2].", ".$bbV[3].", 0, 0, 0, 0, '#000000', 0, ".$_SESSION["user"].", 0);";
					$result=mysql_query($sql) or $success = false;
				}
				if ($success == true){
					$success = createAvatar($_REQUEST['bbV'], $_REQUEST['id']);
				}
			}	
			
			if( isset($_REQUEST['angle_face']) && isset($_REQUEST['angle_face_z'])  ){
				if(checkPerson($_REQUEST['id']) == 1){

					 $sql="UPDATE `people` SET `gazeAngle_face`='".$_REQUEST['angle_face']."', `gazeAngle_face_z`='".$_REQUEST['angle_face_z']."', `userid`='".$_SESSION["user"]."'   WHERE  `peopleid` = '".$_REQUEST['id']."' AND `frameid` = '".$_SESSION["frame_id"]."' AND `cameraid` = ".$_SESSION["camera_id"]."";

					 $result=mysql_query($sql) or $success=false;
				}else{
					$sql="INSERT INTO `people` (`peopleid`,`frameid`, `cameraid`, `bb_x`, `bb_y`, `bb_width`, `bb_height`, `bbV_x`, `bbV_y`, `bbV_width`, `bbV_height`, `gazeAngle_face`, `gazeAngle_face_z`, `gazeAngle_body`, `gazeAngle_body_z`, `color`, `poiid`, `userid`, `groupid`) VALUES
					(".intval($_REQUEST["id"]).", ".$_SESSION["frame_id"].", ".$_SESSION["camera_id"].", 300, 200, 20, 30, 300, 200, 20, 30,".$_REQUEST['angle_face'].",".$_REQUEST['angle_face_z'].", 0, 0, '#000000', 0, ".$_SESSION["user"].", 0);";

					$result=mysql_query($sql) or $success = false;
				}
			}
			
			
			if( isset($_REQUEST['angle_body']) && isset($_REQUEST['angle_body_z'])  ){
				if(checkPerson($_REQUEST['id']) == 1){

					 $sql="UPDATE `people` SET `gazeAngle_body`='".$_REQUEST['angle_body']."', `gazeAngle_body_z`='".$_REQUEST['angle_body_z']."', `userid`='".$_SESSION["user"]."'  WHERE   peopleid=".$_REQUEST['id']." AND `frameid` = ".$_SESSION["frame_id"]." AND `cameraid` = ".$_SESSION["camera_id"]."";

					 $result=mysql_query($sql) or $success=false;
				}else{
					$sql="INSERT INTO `people` (`peopleid`,`frameid`, `cameraid`, `bb_x`, `bb_y`, `bb_width`, `bb_height`, `bbV_x`, `bbV_y`, `bbV_width`, `bbV_height`, `gazeAngle_face`, `gazeAngle_face_z`, `gazeAngle_body`, `gazeAngle_body_z`, `color`, `poiid`, `userid`, `groupid`) VALUES
					(".intval($_REQUEST["id"]).", ".$_SESSION["frame_id"].", ".$_SESSION["camera_id"].", 300, 200, 20, 30, 300, 200, 20, 30, 0, 0, ".$_REQUEST['angle_body'].",".$_REQUEST['angle_body_z'].", '#000000', 0, ".$_SESSION["user"].", 0);";
					$result=mysql_query($sql) or $success = false;
				}
			}
			
			if( isset($_REQUEST['opera_id']) ){
				if(checkPerson($_REQUEST['id']) == 1){

					$sql="UPDATE `people` SET `poiid`='".$_REQUEST['opera_id']."', `userid`='".$_SESSION["user"]."' WHERE `peopleid`='".$_REQUEST['id']."' AND `frameid` = ".$_SESSION["frame_id"]." AND `cameraid` = ".$_SESSION["camera_id"]."";

					$result=mysql_query($sql) or $success=false;	
				} else {	
					$sql="INSERT INTO `people` (`peopleid`,`frameid`, `cameraid`, `bb_x`, `bb_y`, `bb_width`, `bb_height`, `bbV_x`, `bbV_y`, `bbV_width`, `bbV_height`, `gazeAngle_face`, `gazeAngle_face_z`, `gazeAngle_body`, `gazeAngle_body_z`, `color`, `poiid`, `userid`, `groupid`) VALUES
					(".intval($_REQUEST["id"]).", ".$_SESSION["frame_id"].", ".$_SESSION["camera_id"].", 300, 200, 20, 30, 300, 200, 20, 30, 0, 0, 0, 0, '#000000', ".$_REQUEST['opera_id'].", ".$_SESSION["user"].", 0);";

					$result=mysql_query($sql) or $success = false;
				}
				
			}	
			
			if ($success==true){
					jecho(1);	
			}else{
					jecho(0);				
			}
			
		break;

		/**
		 * Remove person by ID
		 */
		case "remove-person":
			if(isset($_REQUEST['id'])){
				$sql="DELETE FROM `people` WHERE cameraid=".$_SESSION["camera_id"]." AND peopleid=".$_REQUEST['id']." AND frameid=".$_SESSION["frame_id"]."";
				$result=mysql_query($sql) or
				die ("Errore comando select: ".mysql_error());						
				jecho(1); //or false (error)
			}
		break;

		/**
		 * Add a group to DB - To appear a group must have at least one person associated
		 */
		case "add-group":
			$group = array();
			if(!isset($_REQUEST['name'])) {
				error_500("Missing parameter");
			}

			$sql="SELECT MAX(groupid) as id FROM `groups`";
			$result=mysql_query($sql) or jecho($group);
			while ($row=mysql_fetch_array($result) ){
				$idvalue = $row["id"];
			}

			$sql="INSERT INTO `groups` (`groupid`, `name`, `userid`) VALUES (".(intval($idvalue) + 1).", '".$_REQUEST['name']."', ".$_SESSION["user"].");";
			$result=mysql_query($sql) or
			jecho($group);
			
			$group = array("id"=>(intval($idvalue) + 1),"text"=>$_REQUEST['name'],"people"=>0);			
			jecho($group);
		break;

		/**
		 * Remove a group (DO NOT REMOVE FROM DB)
		 */
		case "remove-group":
			$done=true;
			if(!isset($_REQUEST['id']))
				error_500("id mancante");			

			$sql="DELETE FROM `groups` WHERE  groupid=".$_REQUEST['id']."";
			$result=mysql_query($sql) or $done=false;
			if($done==true){
				jecho(1);
			}else{
				jecho(0);
			}
			break;
		
		/**
		 * Return the frame based on its ID - DB
		 */
		case "get-frame":
			$dimension = array();
			$img = array();	
			$done = true;

			if( !isset($_REQUEST["frame_id"])){
				$myframe = $_SESSION["frame_id"];

			}else{
				$myframe = $_REQUEST["frame_id"];
				$_SESSION["frame_id"] = $_REQUEST["frame_id"];
			}
			$sql="SELECT * FROM `video` WHERE `frameid`=".$myframe." AND `cameraid`=".$_SESSION["camera_id"]."";
			$result=mysql_query($sql) or
			$done = false;
			if ($done == true){
				while ($row=mysql_fetch_array($result) ){
					$dimension = getimagesize("../frames/".$row["path"]);
					$img = array("background"=>"../frames/".$row["path"],"width"=>$dimension[0], "height"=>$dimension[1], "frame_id"=>$myframe);

				}
			}
			jecho($img);
		break;
		
		/**
		 * Returns the artworks list from the DB
		 */
		case "get-artworks":
			$artwork = array();
			if (isset($_REQUEST['query']) && strlen($_REQUEST['query']) > 0){	
					$sql="SELECT * FROM `poi` WHERE name LIKE '%".$_REQUEST['query']."%' AND cameraid=".$_SESSION["camera_id"]." ORDER BY poiid";
					$result=mysql_query($sql) or
					die ("Error: ".mysql_error());
					if ( mysql_num_rows($result)==0 ) $artwork = array();
					while ($row=mysql_fetch_array($result) ){
						$artwork[] = array("id"=>$row["poiid"],"cameraid"=>$row["cameraid"],"location_x"=>$row["location_x"],"location_y"=>$row["location_y"],"width"=>$row["width"],"height"=>$row["height"],"text"=>$row["name"] );
					}
			}else {
					$sql="SELECT * FROM `poi` WHERE cameraid=".$_SESSION["camera_id"]."";
					$result=mysql_query($sql) or
					die ("Error: ".mysql_error());
					if ( mysql_num_rows($result)==0 ) $artwork = array();
					while ($row=mysql_fetch_array($result) ){
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

			$sql_1 = "SELECT r.* FROM `real_people` as r WHERE (r.peopleid not in (SELECT p2.peopleid FROM `people` as p2 WHERE p2.frameid=".$_SESSION["frame_id"]." AND p2.cameraid=".$_SESSION["camera_id"]."))";
			$result=mysql_query($sql_1) or
			$realpeople = array();	
			if ( mysql_num_rows($result)==0 ) $realpeople = array();	
			while ($row=mysql_fetch_array($result) )
			{
				if(end(explode("/", $row["image"])) != 'default.png'){
					$url = implode(".", explode(".", $row["image"], -1));
					$realpeople[] = array("id"=>$row["peopleid"],"image"=>$url."_100.jpg");
				}else{
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
				error_500("Missing information");
			}
			
			$frame_id = intval($_SESSION['frame_id']);
			$output->current = $frame_id;
		
			//Retrieving previous and next frames
			if(isset($_REQUEST['limit'])){
				$sql = "SELECT frameid FROM video
                  WHERE frameid >= ".($frame_id - $_REQUEST['limit'])."
                  		and frameid <= ".($frame_id + $_REQUEST['limit'])."
                  		and cameraid = '".$_SESSION['camera_id']."';";
			} else {
				$sql = "SELECT frameid FROM video WHERE cameraid = ".$_SESSION['camera_id'].";";
			}
			$result=mysql_query($sql) or $done = false;
			while ($row = mysql_fetch_array($result) ){
				$frame = new stdClass();
				$frame->id = $row["frameid"];
				array_push($frames, $frame);
			}
			
			//Loading people for each frame
			foreach ($frames as $frame){
				$people = array();
				$sql = "SELECT * FROM `people` 
						WHERE cameraid=".$_SESSION["camera_id"]." AND frameid=".$frame->id."";
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
		 * Export function for DB
		 */
		case "export":	
			
			// output headers so that the file is downloaded rather than displayed
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename=data.csv');

			// create a file pointer connected to the output stream	
			$output = fopen('php://output', 'w');		

			$people = array();	
//			$sql_1 = "SELECT p.peopleid,p.frameid,p.cameraid, p.bb_x, p.bb_y, p.bb_width, p.bb_height, p.bbV_x, p.bbV_y, p.bbV_width, p.bbV_height, bbV_height , p.gazeAngle_face, p.gazeAngle_face_z, p.gazeAngle_body, p.gazeAngle_body_z,  v.path , poi.name , g.groupid FROM people as p, video as v, poi, groups as g  WHERE p.frameid = v.frameid AND p.poiid=poi.poiid and p.groupid=g.groupid";
			$sql_1 = "SELECT p.peopleid,p.frameid,p.cameraid, p.bb_x, p.bb_y, p.bb_width, p.bb_height, p.bbV_x, p.bbV_y, p.bbV_width, p.bbV_height, p.gazeAngle_face, p.gazeAngle_face_z, p.gazeAngle_body, p.gazeAngle_body_z,  v.path , poi.name , g.groupid FROM people as p  LEFT JOIN video as v ON p.frameid = v.frameid and p.cameraid=v.cameraid LEFT JOIN poi ON p.poiid=poi.poiid and p.cameraid=poi.cameraid LEFT JOIN groups as g ON p.groupid=g.groupid;";

			$result=mysql_query($sql_1) or
			$people = array();

			// loop over the rows, outputting them
			while ($row = mysql_fetch_assoc($result)) fputcsv($output, $row);	

//			if ( mysql_num_rows($result)==0 ) $people = array();	
//			while ($row=mysql_fetch_array($result) )
//			{
//				$people[] = array("id"=>$row["peopleid"],"frame"=>$row["path"], "angle_face"=>$row["gazeAngle_face"],"angle_face_z"=>$row["gazeAngle_face_z"],"angle_body"=>$row["gazeAngle_body"],"angle_body_z"=>$row["gazeAngle_body_z"],"group"=>$row["groupid"],"artwork"=>$row["poiid"], "frameid"=>$row["frameid"], "cameraid"=>$row["cameraid"], "userid"=>$row["userid"],"bb"=>array(intval($row["bb_x"]), intval($row["bb_y"]),intval($row["bb_width"]),intval($row["bb_height"])),"bbV"=>array(intval($row["bbV_x"]), intval($row["bbV_y"]), intval($row["bbV_width"]), intval($row["bbV_height"])));
//			}
//			header('Content-disposition: attachment; filename=results.json');
//			header('Content-type: application/json');
//			jecho($people);
            break;

	}
	
   

?>