<?php

/**
 * 
 * A set of utils function for WATSS annotation tool
 * 
 */

/** Create and return a random color HEX value **/
function getRandomColorHEX(){
	$done = true;
	global $QUERIES;

	$sql = $QUERIES->getColorListByCamera($_SESSION['camera_id']);
	$result = mysql_query($sql) or $done = false;
	$colors = array();
	if ($done == true){
		while ($row = mysql_fetch_array($result) ){
			array_push($colors, $row['color']);
		}
		$found = false;
		while(!$found){
			$red = rand(0,255);
			$green = rand(0,255);
			$blue = rand(0,255);
			$rgb = array($red, $green, $blue);
			$hex = "#";
			$hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
			$hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
			$hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);
		
			if (!in_array($hex, $colors)) {
				$found = true;
			}
		}
		return $hex;
	} else {
		return "#000";
	}
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
	$dim = array();

	$log = "";
	
	$sql = $QUERIES->getAvatar($id);
	$result = mysql_query($sql) or $done = false;
	
	if ($done == true){
		while ($row = mysql_fetch_array($result) ){
			if (strcmp($row["image"], $config->realPeopleDefaultImg) != 0){
				$dim = getimagesize($row["image"]);
			}else{
				$dim[0] = 0.1;
				$dim[1] = 0.1;
				$defaultAvatar = true;
			}
			$oldFace = intval($row["face"]);
			$oldFaceZ = intval($row["face_z"]);
		}
	}
	
	//Retrieving current people in frame information
	$sql = $QUERIES->getPersonInFrame($id, $_SESSION['camera_id'], $_SESSION['frame_id']);
	$result = mysql_query($sql) or $done = false;
	
	$bbV = new stdClass();
	$facePeople = 0;
	$facePeopleZ = 0;
	
	if ($done == true){
		while ($row=mysql_fetch_array($result)){
			$bbV->x = intval($row['bbV_x']);
			$bbV->y = intval($row['bbV_y']);
			$bbV->width = intval($row['bbV_width']);
			$bbV->height = intval($row['bbV_height']);
			$facePeople = intval($row['gazeAngle_face']);
			$facePeopleZ = intval($row['gazeAngle_face_z']);
		}
	}
	
	$oldScore = computeAvatarScore($oldFace, $oldFaceZ, $dim[0], $dim[1]);
	$log .= "OLD: ".$oldScore;
	
	$newScore = computeAvatarScore($facePeople, $facePeopleZ, $bbV->width, $bbV->height);
	$log .= " NEW: ".$newScore;
	
	if ( $newScore > $oldScore || $defaultAvatar){
		$sql = $QUERIES->getFrameById($_SESSION["frame_id"], $_SESSION["camera_id"]);
		$result = mysql_query($sql) or $done = false;		
		
		if ($done){
			while ($row = mysql_fetch_array($result) ){
				$crop = array('x' => $bbV->x , 'y' => $bbV->y, 'width' => $bbV->width, 'height'=> $bbV->height);
				$src = imagecreatefromjpeg("../frames/".$row["path"]);
				
				$percent = 0.5;
				$resizedWidth = $bbV->width * $percent;
				$resizedHeight = $bbV->height * $percent;
				$dest = imagecrop($src, $crop);
				imagejpeg($dest, "../img/avatars/".$id.".jpg", 100);	
				
				if($defaultAvatar){
					$sql = $QUERIES->setAvatarImage($id);
					$result = mysql_query($sql) or $done = false;
				}
				
				chmod("../img/avatars/".$id.".jpg", 0777);
				imagedestroy($src);
				imagedestroy($dest);
			}
			
			if($done){
				$sql = $QUERIES->setAvatarFace($id, $facePeople, $facePeopleZ);
				$result = mysql_query($sql) or $done = false;
			}		
		}	
	}

	return $done;
}

/**
 * Compute avatar score
 * @param float $y
 * @param float $z
 * @param float $w
 * @param float $h
 */
function computeAvatarScore($y, $z, $w, $h){
	$alpha = 0.2;
	$gamma = 0.7;
	$beta = 1 - ($alpha + $gamma);
	
	return exp(-($alpha * abs($y - 180))) + $beta * abs($z)/360 
		+ 2 * $gamma * abs(($w * $h)/(320 * 200));
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

/** SQL Script Query parser
 */

function SQLParse($sql){
	$sql = trim($sql);
	$sql = preg_replace('~[[:cntrl:]]~', '', $sql); 
	$sql = preg_replace('~[.[:cntrl:]]~', '', $sql);
	$stms = explode(";", $sql);
	
	$response = array(
		'name' => '',
		'tables' => array()
	);
	
	foreach ($stms as $query){
		//Matching database name
		preg_match('/(?:CREATE DATABASE) (?:`)?(\w*)(?:`)?/', $query, $matches);
		if($matches != null && count($matches) > 0){
			$response['name'] =  $matches[1];
		} else {
			//Matching table names
			preg_match('/(?:CREATE TABLE) (?:IF NOT EXISTS)? (?:`)?(\w*)(?:`)?/', $query, $matches);
			if($matches != null && count($matches) > 1){
				array_push($response['tables'], $matches[1]);
			} else {
				preg_match('/(?:CREATE TABLE) (?:`)?(\w*)(?:`)?/', $query, $matches);
				if($matches != null && count($matches) > 1){
					array_push($response['tables'], $matches[1]);
				}
			}
		}

	}
	
	return $response;
}


/**
 * Creates annotations file in CSV format for export
 * @param output file $file
 * @return file with annotations
 */
function createAnnotationsCSV($file){
	global $QUERIES;
	
	if(!isset($_REQUEST['exclude'])){
		$sql = $QUERIES->getAnnotationExportQueryBase();
	} else {
		$sql = $QUERIES->getAnnotationExportQuery($_REQUEST['exclude']);
	}
	
	$result = mysql_query($sql);
	
	//Inserting header
	/*$header = array("people", "frame", "camera", "bb_x", "bb_y", "bb_width",
	 "bb_height", "bbV_x", "bbV_y", "bbV_width", "bbV_height",
			"gazeAngle_face", "gazeAngle_face_z", "gazeAngle_body", "gazeAngle_body_z",
			"path", "poi", "group");
	fputcsv($output, $header);
	*/
	//Loop over the rows, outputting them
	while ($row = mysql_fetch_assoc($result))
		fputcsv($file, $row);
	
	return $file;
}


/**
 * Remove a directory
 * @param unknown $path
 */
function removeDirectory($path) {
	$files = glob($path . '/*');
	foreach ($files as $file) {
		is_dir($file) ? removeDirectory($file) : unlink($file);
	}
	rmdir($path);
	return;
}


?>