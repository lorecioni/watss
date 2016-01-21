<?php
	//Include utils functions
	require_once 'utils.php';
	
	//error_reporting(E_ALL);
	//ini_set("display_errors",1);
	session_start();

	$conFile=parse_ini_file("./connection.ini");
	$conn=mysql_connect($conFile["host"],$conFile["user"],$conFile["password"]);
    if (! $conn) exit("Error: ".mysql_error());
	mysql_select_db($conFile['db']) or exit("Wrong Database");
	
	// check if a person already exists in the DB
	function check_person($id){
		$var = 0;

		$sql_1="SELECT * FROM `people` WHERE cameraid='".$_SESSION["camera_id"]."' AND frameid='F".$_SESSION["frame_id"]."' AND peopleid = ".$id;

		$result=mysql_query($sql_1) or
		$var = 0;
		if ( mysql_num_rows($result)==0 ) {
			$var = 0;
		}else{
			$var = 1;
		}
		
		return $var;
	}

	if( !isset($_REQUEST['action']) )
		error_500("missing action");

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
						$sql="SELECT userid FROM user WHERE name='".mysql_real_escape_string($_REQUEST["user"])."'";
						$result=mysql_query($sql) or die ("Error: ".mysql_error());
						if ( mysql_num_rows($result)==0 ) {					
							$to_return = false;
						} else{
							while ($row=mysql_fetch_array($result) ){
								$_SESSION["user"] = $row["userid"];
								$_SESSION["camera_id"] = $_REQUEST["camera_id"];
								$sql="SELECT g.groupid FROM `tgroup` as g, `user` as u WHERE g.userid=u.userid AND u.name='".$_REQUEST["user"]."' AND g.groupid='G0'";
								$result_zero = mysql_query($sql) or $to_return = false;
								if (mysql_num_rows($result_zero) == 0) {
									$sql_1= "SELECT `userid` FROM `user` WHERE `name`='".$_REQUEST["user"]."'";
									$result_1 = mysql_query($sql_1) or $to_return = false;
									while ($returned_id=mysql_fetch_array($result_1) ){							
										$sql_2="INSERT INTO `tgroup` (`groupid`, `name`, `userid`) VALUES
											('G0', 'Nessun Gruppo', '".$returned_id["userid"]."');";
										$final_result=mysql_query($sql_2) or $to_return = false;
									}
								}
							}
						}
						if ($_REQUEST["frame_id"] == 'first'){
								$sql= "SELECT MIN(CAST(n as UNSIGNED)) as id FROM (SELECT SUBSTRING(`frameid`,2) as n FROM video ) as tab";
								$result_2 = mysql_query($sql) or $to_return = false;						
								while ($final_res=mysql_fetch_array($result_2) ){
									$_SESSION["frame_id"]=(intval($final_res["id"]));
									$_SESSION["frame_id"]=sprintf("%06d",$_SESSION["frame_id"]);
								}					
						}else{
							if ($_REQUEST["frame_id"] == 'FUF'){
								$sql= "SELECT MIN(CAST(SUBSTRING(frameid,2) as UNSIGNED)) as id FROM video WHERE frameid not in (SELECT frameid FROM people WHERE userid='".$_SESSION["user"]."' AND cameraid='".$_SESSION["camera_id"]."')";
								$result_2 = mysql_query($sql) or $to_return = false;						
								while ($final_res=mysql_fetch_array($result_2) ){
									$_SESSION["frame_id"]=(intval($final_res["id"]));									
									$_SESSION["frame_id"]=sprintf("%06d",$_SESSION["frame_id"]);
								}				
							}else{
								if ($_REQUEST["frame_id"] == 'number'){
									$_SESSION["frame_id"] = intval(substr($_REQUEST["frame_number"],1));
									$_SESSION["frame_id"] = sprintf("%06d",$_SESSION["frame_id"]);
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
			$sql="SELECT c.cameraid FROM `camera` as c";
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
			$sql="SELECT name FROM `user` WHERE userid='".$_SESSION["user"]."'";
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

				$sql="SELECT `frameid` as id, `frameid` as txt FROM `video` WHERE `cameraid` = '".$_SESSION["camera_id"]."' AND `frameid` LIKE '%".$_REQUEST["query"]."%' ORDER BY CAST(SUBSTRING(`frameid`,2) as unsigned) ASC LIMIT ".$_REQUEST["limit"];
				$result=mysql_query($sql) or
				$frames = array();
				while ($row=mysql_fetch_array($result)){
					$frames[] = array("id"=>$row["id"],"text"=>$row["txt"]);
				}
				jecho($frames);
			} else {
				$sql="SELECT `frameid` as id, `frameid` as txt FROM `video` WHERE `cameraid` = '".$_SESSION["camera_id"]."' ORDER BY CAST(SUBSTRING(`frameid`,2) as unsigned) ASC LIMIT ".$_REQUEST["limit"];
				$result=mysql_query($sql) or
				$frames = array();
				while ($row=mysql_fetch_array($result)){
					$frames[] = array("id"=>$row["id"],"text"=>$row["txt"]);
				}
				jecho($frames);
			}
			break;

		// List of persons - DB
		case "get-people":			
			$people = array();	
			$people_2 = array();
			$people_add = array();

			$sql_1="SELECT * FROM `people` WHERE cameraid='".$_SESSION["camera_id"]."' AND frameid='F".$_SESSION["frame_id"]."'";

			$result=mysql_query($sql_1) or
			$people = array();	
			if ( mysql_num_rows($result)==0 ) $people = array();	
			while ($row=mysql_fetch_array($result) )
			{
				$people[] = array("id"=>$row["peopleid"],"color"=>$row["color"],"angle_face"=>$row["gazeAngle_face"],"angle_face_z"=>$row["gazeAngle_face_z"],"angle_body"=>$row["gazeAngle_body"],"angle_body_z"=>$row["gazeAngle_body_z"],"group"=>$row["groupid"],"artwork"=>$row["poiid"],"prev_frame"=>true, "bb"=>array(intval($row["bb_x"]), intval($row["bb_y"]),intval($row["bb_width"]),intval($row["bb_height"])),"bbV"=>array(intval($row["bbV_x"]), intval($row["bbV_y"]), intval($row["bbV_width"]), intval($row["bbV_height"])));
			}
			if ((intval($_SESSION["frame_id"])-1)>0){
				$prev_frame_id=sprintf("%06d",intval($_SESSION["frame_id"])-1);

				$sql_2="SELECT * FROM `people` WHERE cameraid='".$_SESSION["camera_id"]."' AND frameid='F".$prev_frame_id."'";

				$result=mysql_query($sql_2) or
				$people_2 = array();
				if ( mysql_num_rows($result)==0 ) $people_2 = array();
				while ($row=mysql_fetch_array($result) )
				{
					$people_2[] = array("id"=>$row["peopleid"],"color"=>$row["color"],"angle_face"=>$row["gazeAngle_face"],"angle_face_z"=>$row["gazeAngle_face_z"],"angle_body"=>$row["gazeAngle_body"],"angle_body_z"=>$row["gazeAngle_body_z"],"group"=>$row["groupid"],"artwork"=>$row["poiid"],"prev_frame"=>false, "bb"=>array(intval($row["bb_x"]), intval($row["bb_y"]),intval($row["bb_width"]),intval($row["bb_height"])),"bbV"=>array(intval($row["bbV_x"]), intval($row["bbV_y"]), intval($row["bbV_width"]), intval($row["bbV_height"])));
				}
				
				foreach ($people_2 as $person_2){
					$found = false;
					for ($i=0; $i < count($people); $i++){					
						if (intval($people[$i]["id"]) == intval($person_2["id"])){
							$found = true;
							$people[$i]["prev_frame"] = true;
						}
					}
					if (!$found){
						$people_add[] = $person_2;
					}
				}
				
				$people = array_merge($people, $people_add);
			}
			jecho($people);
			break;

		// List of groups - DB
		case "get-groups":

			$group = array();
			$group_del = array();
			$group_merge = array();
			if (isset($_REQUEST['query']) && strlen($_REQUEST['query']) > 0){	
					//$sql="select * from (select N.groupid,N.name, max(people) as people FROM ( SELECT g.groupid, g.name, count(p.groupid) as people FROM `people` as p right outer join `tgroup` as g on p.groupid = g.groupid WHERE  g.name LIKE '%".$_REQUEST['query']."%' GROUP BY g.groupid, p.cameraid,p.frameid ORDER BY g.groupid) as N group by N.groupid) as F order by F.people";
					$sql="SELECT N.groupid, N.name, COUNT( N.groupid ) AS people FROM ( SELECT g.groupid, p.peopleid, g.name, COUNT( p.groupid ) FROM  `people` AS p RIGHT OUTER JOIN  `tgroup` AS g ON p.groupid = g.groupid WHERE   g.name LIKE '%".$_REQUEST['query']."%' GROUP BY p.groupid, p.peopleid ORDER BY p.groupid ) AS N GROUP BY N.groupid";

					$result=mysql_query($sql) or
					$group = array();
					if ( mysql_num_rows($result)==0 ) $group = array();
					while ($row=mysql_fetch_array($result)){
						$group[] = array("id"=>$row["groupid"],"text"=>$row["name"],"people"=>$row["people"]);
					}

					//$sql="select * from (select N.groupid,N.name, max(people) as people FROM ( SELECT g.groupid, g.name, count(p.groupid) as people FROM `people` as p right outer join `tgroup` as g on p.groupid = g.groupid WHERE  g.name LIKE '%".$_REQUEST['query']."%' AND g.deleted=0 GROUP BY g.groupid, p.cameraid,p.frameid ORDER BY g.groupid) as N group by N.groupid) as F order by F.people";
					$sql="SELECT N.groupid, N.name, COUNT( N.groupid ) AS people FROM ( SELECT g.groupid, p.peopleid, g.name, COUNT( p.groupid ) FROM  `people` AS p RIGHT OUTER JOIN  `tgroup` AS g ON p.groupid = g.groupid WHERE   g.name LIKE '%".$_REQUEST['query']."%' AND g.deleted=0 GROUP BY p.groupid, p.peopleid ORDER BY p.groupid ) AS N GROUP BY N.groupid";

					$result=mysql_query($sql) or
					$group_del = array();
					while ($row=mysql_fetch_array($result) )
					{
						$group_del[] = array("id"=>$row["groupid"],"text"=>$row["name"],"people"=>0);
					}					
			} else {

					//$sql="select * from ( select N.groupid,N.name, max(people) as people FROM (SELECT g.groupid, g.name, count(p.groupid) as people FROM `people` as p right outer join `tgroup` as g on p.groupid = g.groupid   GROUP BY g.groupid, p.cameraid,p.frameid ORDER BY g.groupid )as N group by N.groupid ) as F order by F.people";
					$sql="SELECT N.groupid, N.name, COUNT( N.groupid ) AS people FROM ( SELECT g.groupid, p.peopleid, g.name, COUNT( p.groupid ) FROM  `people` AS p RIGHT OUTER JOIN  `tgroup` AS g ON p.groupid = g.groupid GROUP BY p.groupid, p.peopleid ORDER BY p.groupid ) AS N GROUP BY N.groupid";
					$result=mysql_query($sql) or
					$group = array();
					if ( mysql_num_rows($result)==0 ) $group = array();
					while ($row=mysql_fetch_array($result) ){
						$group[] = array("id"=>$row["groupid"],"text"=>$row["name"],"people"=>$row["people"]);
					}
					//$sql="select * from ( select N.groupid,N.name, max(people) as people FROM (SELECT g.groupid, g.name, count(p.groupid) as people FROM `people` as p right outer join `tgroup` as g on p.groupid = g.groupid WHERE g.deleted=0 GROUP BY g.groupid, p.cameraid,p.frameid  ORDER BY g.groupid) as N group by N.groupid)   as F order by F.people";
					$sql="SELECT N.groupid, N.name, COUNT( N.groupid ) AS people FROM ( SELECT g.groupid, p.peopleid, g.name, COUNT( p.groupid ) FROM  `people` AS p RIGHT OUTER JOIN  `tgroup` AS g ON p.groupid = g.groupid WHERE g.deleted=0 GROUP BY p.groupid, p.peopleid ORDER BY p.groupid ) AS N GROUP BY N.groupid";

					$result=mysql_query($sql) or $group_del = array();
					while ($row = mysql_fetch_array($result)){
						$group_del[] = array("id"=>$row["groupid"],"text"=>$row["name"],"people"=>0);
					}
			}
			foreach ($group_del as $g){
				$found=false;
				for ($i=0; $i<count($group); $i++){
					if ($group[$i]["id"]==$g["id"]){
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
		 * Find a group that can be removed
		 */
		case "get-deletable":
			$sql="SELECT g.groupid, count(p.groupid) as people FROM `people` as p right outer join `tgroup` as g on p.groupid = g.groupid WHERE g.userid='".$_SESSION["user"]."' AND g.deleted=0 GROUP BY g.groupid ORDER BY g.groupid ";
			$result=mysql_query($sql) or
			$group = array();
			if ( mysql_num_rows($result)==0 ) $group = array();
			while ($row=mysql_fetch_array($result) )
			{
				if (intval($row["people"])>0){
					$deletable=false;
				}else{
					$deletable=true;
				}
					
				$group[] = array("id"=>$row["groupid"],"deletable"=>$deletable);
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
			
			//Initial bounding box settings
  			//Full bounding box
			$bb = new stdClass(); 
			$bb->x = 300;
			$bb->y = 200;
			$bb->width = 40;
			$bb->height = 84;		
			//Visible bounding box
			$bbV = new stdClass(); 
			$bbV->x = 300;
			$bbV->y = 200;
			$bbV->width = 30;
			$bbV->height = 84;
			
		    //Query indicator: if true the query has been done
			$done = true;

			if (isset($_REQUEST["people_id"])){
				$sql2 = "INSERT INTO `people` (`peopleid`,`frameid`, `cameraid`, `bb_x`, `bb_y`, `bb_width`, `bb_height`, `bbV_x`, `bbV_y`, `bbV_width`, `bbV_height`, `gazeAngle_face`, `gazeAngle_face_z`, `gazeAngle_body`, `gazeAngle_body_z`,`color`, `poiid`, `userid`, `groupid`) VALUES
				(".$_REQUEST["people_id"].", 'F".$_SESSION["frame_id"]."', '".$_SESSION["camera_id"]."', ".$bb->x.", ".$bb->y.", ".$bb->width.", ".$bb->height.", ".$bbV->x.", ".$bbV->y.", ".$bbV->width.", ".$bbV->height.", 0, 0, 0, 0,'".$hex."', 'O0', '".$_SESSION["user"]."', 'G0');";
			
				$result = mysql_query($sql) or $done = false;
				if ($done){
					$person = array("id"=>$_REQUEST["people_id"], "color"=>$hex,"angle_face"=>0,"angle_face_z"=>0,"angle_body"=>0,"angle_body_z"=>0,"group"=>'G0',"artwork"=>'O0', "prev_frame"=>true, "bb"=>array($bb->x, $bb->y, $bb->width, $bb->height),"bbV"=>array($bbV->x, $bbV->y, $bbV->width, $bbV->height));
				}
			}else{
			    $sql="INSERT INTO `tgroup` (`groupid`,`name`,`deleted`,`userid`) VALUES ('G0', 'no group',0,'".$_SESSION["user"]."');";
				$result = mysql_query($sql);

				$sql="INSERT INTO `real_people` (`face`,`face_z`,`image`) VALUES (0,0,'../img/real_people/default.png');";
				$result = mysql_query($sql) or $done = false;
				if($done){
					$my_id = mysql_insert_id();
					$sql="INSERT INTO `people` (`peopleid`,`frameid`, `cameraid`, `bb_x`, `bb_y`, `bb_width`, `bb_height`, `bbV_x`, `bbV_y`, `bbV_width`, `bbV_height`, `gazeAngle_face`, `gazeAngle_face_z`, `gazeAngle_body`, `gazeAngle_body_z`, `color`, `poiid`, `userid`, `groupid`) VALUES
						(".$my_id.", 'F".$_SESSION["frame_id"]."', '".$_SESSION["camera_id"]."', ".$bb->x.", ".$bb->y.", ".$bb->width.", ".$bb->height.", ".$bbV->x.", ".$bbV->y.", ".$bbV->width.", ".$bbV->height.", 0, 0, 0, 0, '".$hex."', 'O0', '".$_SESSION["user"]."', 'G0');";
					$result=mysql_query($sql) or $done = false;
					if ($done){
						$person = array("id"=>$my_id,"color"=>$hex,"angle_face"=>0,"angle_face_z"=>0,"angle_body"=>0,"angle_body_z"=>0,"group"=>'G0',"artwork"=>'O0', "prev_frame"=>true, "bb"=>array($bb->x, $bb->y, $bb->width, $bb->height),"bbV"=>array($bbV->x, $bbV->y, $bbV->width, $bbV->height));
					}
				}
			}
			//Econding person to json				
			jecho($person);
		break;

		//Person attributes update - DB
		case "update-person-attribute":
			$success=true;
			if( !isset($_REQUEST['id']) ){
				error_500("missing id");
				jecho(0);
			}
			
			if( isset($_REQUEST['color']) ){
				if(check_person($_REQUEST['id']) == 1){

					$sql="UPDATE `people` SET `color`='".$_REQUEST['color']."' ,userid='".$_SESSION["user"]."' WHERE  `peopleid`=".$_REQUEST['id']." AND `frameid` = 'F".$_SESSION["frame_id"]."' AND `cameraid` = '".$_SESSION["camera_id"]."'";

					$result=mysql_query($sql) or
					$success=false;
				}else{
					$sql="INSERT INTO `people` (`peopleid`,`frameid`, `cameraid`, `bb_x`, `bb_y`, `bb_width`, `bb_height`, `bbV_x`, `bbV_y`, `bbV_width`, `bbV_height`, `gazeAngle_face`, `gazeAngle_face_z`, `gazeAngle_body`, `gazeAngle_body_z`, `color`, `poiid`, `userid`, `groupid`) VALUES
					(".intval($_REQUEST["id"]).", 'F".$_SESSION["frame_id"]."', '".$_SESSION["camera_id"]."', 300, 200, 20, 30, 300, 200, 20, 30, 0, 0, 0, 0, '".$_REQUEST['color']."', 'O0', '".$_SESSION["user"]."', 'G0');";

					$result=mysql_query($sql) or
					$success = false;
				}
			}
			
			if( isset($_REQUEST['group_id']) ){
				if(check_person($_REQUEST['id']) == 1){

					$sql="UPDATE `people` SET `groupid`='".$_REQUEST['group_id']."', userid='".$_SESSION["user"]."' WHERE  `peopleid`='".$_REQUEST['id']."' AND `frameid` = 'F".$_SESSION["frame_id"]."' AND `cameraid` = '".$_SESSION["camera_id"]."'";

					$result=mysql_query($sql) or
					$success=false;		
				}else{
					$sql="INSERT INTO `people` (`peopleid`,`frameid`, `cameraid`, `bb_x`, `bb_y`, `bb_width`, `bb_height`, `bbV_x`, `bbV_y`, `bbV_width`, `bbV_height`, `gazeAngle_face`, `gazeAngle_face_z`, `gazeAngle_body`, `gazeAngle_body_z`, `color`, `poiid`, `userid`, `groupid`) VALUES
					(".intval($_REQUEST["id"]).", 'F".$_SESSION["frame_id"]."', '".$_SESSION["camera_id"]."', 300, 200, 20, 30, 300, 200, 20, 30, 0, 0, 0, 0, '#000000', 'O0', '".$_SESSION["user"]."', '".$_REQUEST['group_id']."');";

					$result=mysql_query($sql) or
					$success = false;
				}
			}	
			
			if( isset($_REQUEST['bb']) ){
				if(check_person($_REQUEST['id']) == 1){
					$bb = array();
					$bb = $_REQUEST['bb'];

					$sql="UPDATE `people` SET `bb_x`=".$bb[0].",`bb_y`=".$bb[1].",`bb_width`=".$bb[2].",`bb_height`=".$bb[3].", userid='".$_SESSION["user"]."' WHERE  `peopleid`=".$_REQUEST['id']." AND `frameid` = 'F".$_SESSION["frame_id"]."' AND `cameraid` = '".$_SESSION["camera_id"]."'";

					$result=mysql_query($sql) or
					$success=false;
				}else{
					$bb = array();
					$bb = $_REQUEST['bb'];
					$sql="INSERT INTO `people` (`peopleid`,`frameid`, `cameraid`, `bb_x`, `bb_y`, `bb_width`, `bb_height`, `bbV_x`, `bbV_y`, `bbV_width`, `bbV_height`, `gazeAngle_face`, `gazeAngle_face_z`, `gazeAngle_body`, `gazeAngle_body_z`, `color`, `poiid`, `userid`, `groupid`) VALUES 					(".$_REQUEST["id"].", 'F".$_SESSION["frame_id"]."', '".$_SESSION["camera_id"]."', ".$bb[0].", ".$bb[1].", ".$bb[2].", ".$bb[3].", 300, 200, 20, 30, 0, 0, 0, 0, '#000000', 'O0', '".$_SESSION["user"]."', 'G0');";


					$result=mysql_query($sql) or
					$success = false;
				}
		    }
			
			if( isset($_REQUEST['bbV']) ){
				if(check_person($_REQUEST['id']) == 1){					
					 $bbV = array();
					 $bbV = $_REQUEST['bbV'];

					 $sql="UPDATE `people` SET `bbV_x`=".$bbV[0].",`bbV_y`=".$bbV[1].",`bbV_width`=".$bbV[2].",`bbV_height`=".$bbV[3].", userid='".$_SESSION["user"]."' WHERE  `peopleid`=".$_REQUEST['id']." AND `frameid` = 'F".$_SESSION["frame_id"]."' AND `cameraid` = '".$_SESSION["camera_id"]."'";

					 $result=mysql_query($sql) or
					 $success=false;
				}else{
					$bbV = array();
					$bbV = $_REQUEST['bbV'];
					$sql="INSERT INTO `people` (`peopleid`,`frameid`, `cameraid`, `bb_x`, `bb_y`, `bb_width`, `bb_height`, `bbV_x`, `bbV_y`, `bbV_width`, `bbV_height`, `gazeAngle_face`, `gazeAngle_face_z`, `gazeAngle_body`, `gazeAngle_body_z`, `color`, `poiid`, `userid`, `groupid`) VALUES
					(".$_REQUEST["id"].", 'F".$_SESSION["frame_id"]."', '".$_SESSION["camera_id"]."', 300, 200, 20, 30, ".$bbV[0].", ".$bbV[1].", ".$bbV[2].", ".$bbV[3].", 0, 0, 0, 0, '#000000', 'O0', '".$_SESSION["user"]."', 'G0');";
					$result=mysql_query($sql) or
					$success = false;
				
				}
				if ($success == true){
					$success = create_avatar($_REQUEST['bbV'], $_REQUEST['id']);
				}
			}	
			
			if( isset($_REQUEST['angle_face']) && isset($_REQUEST['angle_face_z'])  ){
				if(check_person($_REQUEST['id']) == 1){

					 $sql="UPDATE `people` SET `gazeAngle_face`=".$_REQUEST['angle_face'].", `gazeAngle_face_z`=".$_REQUEST['angle_face_z'].", userid='".$_SESSION["user"]."'   WHERE  peopleid=".$_REQUEST['id']." AND `frameid` = 'F".$_SESSION["frame_id"]."' AND `cameraid` = '".$_SESSION["camera_id"]."'";

					 $result=mysql_query($sql) or
					 $success=false;
				}else{
					$sql="INSERT INTO `people` (`peopleid`,`frameid`, `cameraid`, `bb_x`, `bb_y`, `bb_width`, `bb_height`, `bbV_x`, `bbV_y`, `bbV_width`, `bbV_height`, `gazeAngle_face`, `gazeAngle_face_z`, `gazeAngle_body`, `gazeAngle_body_z`, `color`, `poiid`, `userid`, `groupid`) VALUES
					(".intval($_REQUEST["id"]).", 'F".$_SESSION["frame_id"]."', '".$_SESSION["camera_id"]."', 300, 200, 20, 30, 300, 200, 20, 30,".$_REQUEST['angle_face'].",".$_REQUEST['angle_face_z'].", 0, 0, '#000000', 'O0', '".$_SESSION["user"]."', 'G0');";

					$result=mysql_query($sql) or
					$success = false;
				}
			}
			
			
			if( isset($_REQUEST['angle_body']) && isset($_REQUEST['angle_body_z'])  ){
				if(check_person($_REQUEST['id']) == 1){

					 $sql="UPDATE `people` SET `gazeAngle_body`=".$_REQUEST['angle_body'].", `gazeAngle_body_z`=".$_REQUEST['angle_body_z'].", userid='".$_SESSION["user"]."'  WHERE   peopleid=".$_REQUEST['id']." AND `frameid` = 'F".$_SESSION["frame_id"]."' AND `cameraid` = '".$_SESSION["camera_id"]."'";

					 $result=mysql_query($sql) or
					 $success=false;
				}else{
					$sql="INSERT INTO `people` (`peopleid`,`frameid`, `cameraid`, `bb_x`, `bb_y`, `bb_width`, `bb_height`, `bbV_x`, `bbV_y`, `bbV_width`, `bbV_height`, `gazeAngle_face`, `gazeAngle_face_z`, `gazeAngle_body`, `gazeAngle_body_z`, `color`, `poiid`, `userid`, `groupid`) VALUES
					(".intval($_REQUEST["id"]).", 'F".$_SESSION["frame_id"]."', '".$_SESSION["camera_id"]."', 300, 200, 20, 30, 300, 200, 20, 30, 0, 0, ".$_REQUEST['angle_body'].",".$_REQUEST['angle_body_z'].", '#000000', 'O0', '".$_SESSION["user"]."', 'G0');";

					$result=mysql_query($sql) or
					$success = false;
				}
			}
			
			if( isset($_REQUEST['opera_id']) ){
				if(check_person($_REQUEST['id']) == 1){

					$sql="UPDATE `people` SET `poiid`='".$_REQUEST['opera_id']."', userid='".$_SESSION["user"]."' WHERE `peopleid`='".$_REQUEST['id']."' AND `frameid` = 'F".$_SESSION["frame_id"]."' AND `cameraid` = '".$_SESSION["camera_id"]."'";

					$result=mysql_query($sql) or
					$success=false;	
				}else{	
					$sql="INSERT INTO `people` (`peopleid`,`frameid`, `cameraid`, `bb_x`, `bb_y`, `bb_width`, `bb_height`, `bbV_x`, `bbV_y`, `bbV_width`, `bbV_height`, `gazeAngle_face`, `gazeAngle_face_z`, `gazeAngle_body`, `gazeAngle_body_z`, `color`, `poiid`, `userid`, `groupid`) VALUES
					(".intval($_REQUEST["id"]).", 'F".$_SESSION["frame_id"]."', '".$_SESSION["camera_id"]."', 300, 200, 20, 30, 300, 200, 20, 30, 0, 0, 0, 0, '#000000', '".$_REQUEST['opera_id']."', '".$_SESSION["user"]."', 'G0');";

					$result=mysql_query($sql) or
					$success = false;
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
				$sql="DELETE FROM `people` WHERE cameraid='".$_SESSION["camera_id"]."' AND peopleid=".$_REQUEST['id']." AND frameid='F".$_SESSION["frame_id"]."'";
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

			$sql="SELECT MAX(CONVERT(SUBSTRING(groupid,2),SIGNED INTEGER)) as id FROM `tgroup`";
			$result=mysql_query($sql) or
			jecho($group);
			while ($row=mysql_fetch_array($result) )
			{
				$idvalue = $row["id"];
			}

			$sql="INSERT INTO `tgroup` (`groupid`, `name`, `userid`) VALUES
			('G".(intval($idvalue)+1)."', '".$_REQUEST['name']."', '".$_SESSION["user"]."');";
			$result=mysql_query($sql) or
			jecho($group);
			
			$group = array("id"=>"G".(intval($idvalue)+1),"text"=>$_REQUEST['name'],"people"=>0);			
			jecho($group);
		break;

		/**
		 * Remove a group (DO NOT REMOVE FROM DB)
		 */
		case "remove-group":
			$done=true;
			if(!isset($_REQUEST['id']))
				error_500("id mancante");			

			$sql="DELETE FROM `tgroup` WHERE  groupid='".$_REQUEST['id']."'";
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
				$myframe=sprintf("F%06d",$_SESSION["frame_id"]);

			}else{
				$myframe=sprintf("F%06d",$_REQUEST["frame_id"]);
                                $_SESSION["frame_id"] = sprintf("%06d",$_REQUEST["frame_id"]);
			}
			$sql="SELECT * FROM `video` WHERE `frameid`='".$myframe."' AND `cameraid`='".$_SESSION["camera_id"]."'";
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
		 * Returns previous or next frame
		 */
		case "get-nearframe":
			$dimension = array();
			$img = array();	
			$done = true;

			if( !isset($_REQUEST["frame"])){				
				error_500("Missing frame");
			}
			if ($_REQUEST["frame"] === "prev"){
				$sql="SELECT MAX(CAST(SUBSTRING(frameid,2) as UNSIGNED)) as n FROM video WHERE CAST(SUBSTRING(frameid,2) as UNSIGNED) < ".$_SESSION["frame_id"];
				$result=mysql_query($sql) or $done = false;
				while ($row=mysql_fetch_array($result) ){
					$myframe = intval($row["n"]);
				}
				if ($myframe != null){
					$_SESSION["frame_id"] =sprintf("%06d",$myframe);
				}			
			}else{
				$sql="SELECT MIN(CAST(SUBSTRING(frameid,2) as UNSIGNED)) as n FROM video WHERE CAST(SUBSTRING(frameid,2) as UNSIGNED) > ".$_SESSION["frame_id"];
				$result=mysql_query($sql) or $done = false;
				while ($row=mysql_fetch_array($result) ){
					$myframe = intval($row["n"]);
				}
				if ($myframe != null){
					$_SESSION["frame_id"] = sprintf("%06d",$myframe);
				}
			}
			$myframe=sprintf("F%06d",$_SESSION["frame_id"]);			
			$sql="SELECT * FROM `video` WHERE `frameid`='".$myframe."' AND `cameraid`='".$_SESSION["camera_id"]."'";
			$result=mysql_query($sql) or $done = false;
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
					$sql="SELECT * FROM `poi` WHERE name LIKE '%".$_REQUEST['query']."%' AND cameraid='".$_SESSION["camera_id"]."' ORDER BY poiid";
					$result=mysql_query($sql) or
					die ("Error: ".mysql_error());
					if ( mysql_num_rows($result)==0 ) $artwork = array();
					while ($row=mysql_fetch_array($result) ){
						$artwork[] = array("id"=>$row["poiid"],"cameraid"=>$row["cameraid"],"location_x"=>$row["location_x"],"location_y"=>$row["location_y"],"width"=>$row["width"],"height"=>$row["height"],"text"=>$row["name"] );
					}
			}else {
					$sql="SELECT * FROM `poi` WHERE cameraid='".$_SESSION["camera_id"]."'";
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

			$sql_1 = "SELECT r.* FROM `real_people` as r WHERE (r.peopleid not in (SELECT p2.peopleid FROM `people` as p2 WHERE p2.frameid='F".$_SESSION["frame_id"]."' AND p2.cameraid='".$_SESSION["camera_id"]."'))";
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
			
			$frame_id = intval(substr($_SESSION['frame_id'], 1));
			$output->current = $frame_id;
		
			//Retrieving previous and next frames
			if(isset($_REQUEST['limit'])){
				$sql = "SELECT frameid FROM video
                  WHERE (CAST(SUBSTRING(frameid,2) as UNSIGNED) >= ".($frame_id - $_REQUEST['limit']).")
                  		and (CAST(SUBSTRING(frameid,2) as UNSIGNED) <= ".($frame_id + $_REQUEST['limit']).")
                  		and cameraid = '".$_SESSION['camera_id']."';";
			} else {
				$sql = "SELECT frameid FROM video WHERE cameraid = '".$_SESSION['camera_id']."';";
			}
			$result=mysql_query($sql) or $done = false;
			while ($row = mysql_fetch_array($result) ){
				$frame = new stdClass();
				$frame->id = $row["frameid"];
				$frame->number =  intval(substr($row['frameid'], 1));
				array_push($frames, $frame);
			}
			
			//Loading people for each frame
			foreach ($frames as $frame){
				$people = array();
				$sql = "SELECT * FROM `people` 
						WHERE cameraid='".$_SESSION["camera_id"]."' AND frameid='".$frame->id."'";
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
//			$sql_1 = "SELECT p.peopleid,p.frameid,p.cameraid, p.bb_x, p.bb_y, p.bb_width, p.bb_height, p.bbV_x, p.bbV_y, p.bbV_width, p.bbV_height, bbV_height , p.gazeAngle_face, p.gazeAngle_face_z, p.gazeAngle_body, p.gazeAngle_body_z,  v.path , poi.name , g.groupid FROM people as p, video as v, poi, tgroup as g  WHERE p.frameid = v.frameid AND p.poiid=poi.poiid and p.groupid=g.groupid";
			$sql_1 = "SELECT p.peopleid,p.frameid,p.cameraid, p.bb_x, p.bb_y, p.bb_width, p.bb_height, p.bbV_x, p.bbV_y, p.bbV_width, p.bbV_height, p.gazeAngle_face, p.gazeAngle_face_z, p.gazeAngle_body, p.gazeAngle_body_z,  v.path , poi.name , g.groupid FROM people as p  LEFT JOIN video as v ON p.frameid = v.frameid and p.cameraid=v.cameraid LEFT JOIN poi ON p.poiid=poi.poiid and p.cameraid=poi.cameraid LEFT JOIN tgroup as g ON p.groupid=g.groupid;";

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
	

	function create_avatar($bbV, $id){
		$done = true;
		$first_change = false;
		$dimension = array();
		$alpha = 0.09;
		$gamma = 0.9;
		$beta = 1-($alpha+$gamma);
		// recover person avatar...
		$sql="SELECT * FROM `real_people` WHERE `peopleid`=".$id;
		$result=mysql_query($sql) or
		$done = false;
		if ($done == true){
			while ($row=mysql_fetch_array($result) )
			{
				if ($row["image"] != "../img/real_people/default.png"){
					$dimension = getimagesize($row["image"]);
				}else{
					$dimension[0] = 0.1;
					$dimension[1] = 0.1;
					$first_change = true;
				}
				$face = intval($row["face"]);
				$face_z = intval($row["face_z"]);
			}
		}
	
		$sql="SELECT gazeAngle_face, gazeAngle_face_z FROM `people` WHERE `peopleid`=".$id." AND cameraid = '".$_SESSION['camera_id']."' AND frameid = 'F".$_SESSION['frame_id']."'";
	
		$result=mysql_query($sql) or
		$done = false;
		if ($done == true){
			while ($row=mysql_fetch_array($result) )
			{
				$face_people = intval($row['gazeAngle_face']);
				$face_z_people = intval($row['gazeAngle_face_z']);
			}
		}
	
		$old_value = 1/($dimension[0]*$dimension[1]);
		$new_value = 1/($bbV[2]*$bbV[3]);
	
		if ( $new_value < $old_value ){
			$sql="SELECT path FROM `video` WHERE `frameid`='F".$_SESSION["frame_id"]."' AND `cameraid`='".$_SESSION["camera_id"]."'";
			$result=mysql_query($sql) or
			$done = false;
			if ($done == true){
				while ($row=mysql_fetch_array($result) )
				{
					$src = imagecreatefromjpeg("../frames/".$row["path"]);
					$dest = imagecreatetruecolor($bbV[2], $bbV[3]);
					$done = imagecopyresampled($dest, $src, 0, 0, $bbV[0],  $bbV[1],  $bbV[2],  $bbV[3], $bbV[2],  $bbV[3]);
					$done = imagejpeg($dest, "../img/real_people/".$id.".jpg");
						
					$src = $dest;
					$dest = imagecreatetruecolor(intval(100.0*$bbV[2]/$bbV[3]), 100);
					$done = imagecopyresampled($dest, $src, 0, 0, 0,  0, intval(100.0*$bbV[2]/$bbV[3]), 100, $bbV[2],$bbV[3]);
					$done = imagejpeg($dest, "../img/real_people/".$id."_100.jpg");
	
					chmod("../img/real_people/".$id.".jpg",0777);
					chmod("../img/real_people/".$id."_100.jpg",0777);
					imagedestroy($src);
					imagedestroy($dest);
						
				}
	
				$sql="UPDATE `real_people` SET face=".$face_people.", face_z=".$face_z_people." WHERE peopleid=".$id;
				$result=mysql_query($sql) or
				$done=false;
			}
				
			if ($first_change == true){
				$sql="UPDATE `real_people` SET `image`='../img/real_people/".$id.".jpg' WHERE peopleid=".$id;
				$result=mysql_query($sql) or
				$done=false;
			}
		}
	
		return $done;
	}
   

?>
