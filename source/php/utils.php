<?php

/**
 * 
 * A set of utils function for WATSS annotation tool
 * 
 */

/** Create and return a random color HEX value **/
function getRandomColorHEX(){
	$red = rand(0,255);
	$green = rand(0,255);
	$blue = rand(0,255);
	$rgb = array($red,$green,$blue);
	$hex = "#";
	$hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
	$hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
	$hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);
	return $hex;
}

/** Return json encoded content value
 * @param $value: content to be encoded
 **/
function jecho ($value){
	header("Content-type: application/json");
	echo json_encode($value);
}

/**
 * Generate internal server error
 * @param $msg : displayed message
 */
function error_500 ($msg){
	header("HTTP/1.0 500 Internal Server Error");
	echo $msg;
	exit;
}


/**
 * Create person avatar
 * @param unknown $bbV
 * @param unknown $id
 * @return boolean
 */
function createAvatar($id){
	$done = true;
	global $config;
	global $QUERIES;
	$defaultAvatar = false;
	$dimension = array();
	
	$log = 'creating avatar';
	
	$alpha = 0.09;
	$gamma = 0.9;
	$beta = 1 - ($alpha + $gamma);

	$sql = $QUERIES->getRealPeopleInfo($id);
	$result = mysql_query($sql) or $done = false;
	
	
	if ($done == true){
		while ($row = mysql_fetch_array($result) ){
			if (strcmp($row["image"], $config->realPeopleDefaultImg) != 0){
				$dimension = getimagesize($row["image"]);
			}else{
				$dimension[0] = 0.1;
				$dimension[1] = 0.1;
				$defaultAvatar = true;
			}
			$face = intval($row["face"]);
			$face_z = intval($row["face_z"]);
		}
	}
	
	
	
	$sql = $QUERIES->getPersonInFrame($id, $_SESSION['camera_id'], $_SESSION['frame_id']);
	$result = mysql_query($sql) or $done = false;
	
	$bb = new stdClass();
	$bbV = new stdClass();

	$facePeople = 0;
	$facePeopleZ = 0;
	
	if ($done == true){
		while ($row=mysql_fetch_array($result)){
			$bb->x = intval($row['bb_x']);
			$bb->y = intval($row['bb_y']);
			$bb->width = intval($row['bb_width']);
			$bb->height = intval($row['bb_height']);
			$bbV->x = intval($row['bbV_x']);
			$bbV->y = intval($row['bbV_y']);
			$bbV->width = intval($row['bbV_width']);
			$bbV->height = intval($row['bbV_height']);
			$facePeople = intval($row['gazeAngle_face']);
			$facePeopleZ = intval($row['gazeAngle_face_z']);
		}
	}
	
	$old_value = 1/($dimension[0] * $dimension[1]);
	$new_value = 1/($bbVwidth * $bbVheight);

	if ( $new_value < $old_value ){
		$sql = $QUERIES->getFrameById($_SESSION["frame_id"], $_SESSION["camera_id"]);
		$result = mysql_query($sql) or $done = false;
		
		
		if ($done == true){
			$log .= 'dentro';
			while ($row=mysql_fetch_array($result) )
			{
				$src = imagecreatefromjpeg("../frames/".$row["path"]);
				$dest = imagecreatetruecolor($bbV[2], $bbV[3]);
				$done = imagecopyresampled($dest, $src, 0, 0, $bbV->x,  $bbV->y,  $bbV->width,  $bbV->heigth,$bbV->width,  $bbV->height);
				$done = imagejpeg($dest, "../img/real_people/".$id.".jpg");

				
				//TODO using classes
				$src = $dest;
				$dest = imagecreatetruecolor(intval(100.0*$bbV[2]/$bbV[3]), 100);
				$done = imagecopyresampled($dest, $src, 0, 0, 0,  0, intval(100.0*$bbV[2]/$bbV[3]), 100, $bbV[2],$bbV[3]);
				$done = imagejpeg($dest, "../img/real_people/".$id."_100.jpg");

				chmod("../img/real_people/".$id.".jpg",0777);
				chmod("../img/real_people/".$id."_100.jpg",0777);
				imagedestroy($src);
				imagedestroy($dest);

			}

			//$sql="UPDATE `real_people` SET face=".$face_people.", face_z=".$face_z_people." WHERE peopleid=".$id;
			//$result=mysql_query($sql) or $done=false;

		}

		if ($first_change == true){
			//$sql="UPDATE `real_people` SET `image`='../img/real_people/".$id.".jpg' WHERE peopleid=".$id;
			//$result=mysql_query($sql) or $done=false;

		}
	}
	return $log;
//	return $done;
}


/**
 * Check if a person is already present in database
 * @param person $id
 * @return number
 */

function checkPerson($id){
	$var = 0;

	$sql_1="SELECT * FROM `people` WHERE cameraid=".$_SESSION["camera_id"]." AND frameid=".$_SESSION["frame_id"]." AND peopleid = ".$id;

	$result=mysql_query($sql_1) or
	$var = 0;
	if ( mysql_num_rows($result)==0 ) {
		$var = 0;
	}else{
		$var = 1;
	}

	return $var;
}

?>