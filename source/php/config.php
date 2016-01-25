<?php
/**
 * Configuration script for retrieving information from database for the settings page
 */

$dbIniFile = 'connection.ini';

$checkIniFile = false;
$checkDatabaseConnection = false;

if(file_exists($dbIniFile)){
	$checkIniFile = true;
	$dbInfo = parse_ini_file($dbIniFile);
	$user = $dbInfo['user'];
	$password = $dbInfo['password'];
	$host = $dbInfo['host'];
	$db = $dbInfo['db'];
	if($user == '' || $host == '' || $db == ''){
		$checkIniFile = false;
	} else {
		$checkDatabaseConnection = true;
 		$dbConnection = mysql_connect($host, $user, $password) 
 				or $checkDatabaseConnection = false;
 		if ($checkDatabaseConnection) {
 			mysql_select_db($db) or $checkDatabaseConnection = false;
  		}
	}
} else {
	$dbInfo = array(
		'user' => '',
		'password' => '',
		'host' => '',
		'db' => ''
	);
}

if(isset($_REQUEST['action'])){
	require_once 'utils.php';
	
	switch($_REQUEST['action']) {
		
		case 'init':
			jecho($dbInfo);
			break;
		
		/**
		 * Updates database configuration file (connection.ini)
		 */
		case "update-database-connection":
			$success = true;

			$ini = fopen($dbIniFile, "w") or $success = false;
			if($success){
				$txt = "user=\"".$_REQUEST['user']."\"\n";
				$txt .= "password=\"".$_REQUEST['password']."\"\n";
				$txt .= "host=\"".$_REQUEST['host']."\"\n";
				$txt .= "db=\"".$_REQUEST['db']."\"";
				fwrite($ini, $txt);
				fclose($ini);
			}
			jecho($success);
			break;
			
		/**
		 * Checking database connection
		 */
		case "test-database-connection":
			$checkDatabaseConnection = true;
			$user = $_REQUEST['user'];
			$password = $_REQUEST['password'];
			$host = $_REQUEST['host'];
			$db = $_REQUEST['db'];
			$dbConnection = mysql_connect($host, $user, $password) or $checkDatabaseConnection = false;
			if ($checkDatabaseConnection) {
				mysql_select_db($db) or $checkDatabaseConnection = false;
			}
			jecho($checkDatabaseConnection);
			break;
			
		/**
		 * Retrieving users from db
		 */
		case "get-users":
			$success = true;
			$sql = "SELECT * FROM user";
			$users = array();
			if($checkDatabaseConnection){
				$result = mysql_query($sql) or $success = false;
				if($success){
					while ($row = mysql_fetch_array($result)){
						$user = new stdClass();
						$user->id = $row["userid"];
						$user->name = $row["name"];
						array_push($users, $user);
					}
				}
			}
			jecho($users);
			break;
			
			/**
			 * Adding user to db
			 */
			case "add-user":
				$success = true;
				if (isset($_REQUEST['name'])){
					$name = mysql_escape_string($_REQUEST['name']);
					
					
					if($checkDatabaseConnection){
						$sql = "INSERT INTO `user` (`name`) VALUES ('".$name."');";
						$result = mysql_query($sql) or $success = false;
					} else {
						$success = false;
					}
				} else {
					$success = false;
				}

				$output = new stdClass();
				$output->success = $success;
				$output->id = mysql_insert_id();
				
				jecho($output);
				break;
			
			/**
			 * Removing user from db
			 */
			case "remove-user":
				$success = true;
				if (isset($_REQUEST['userid'])){
					$id = $_REQUEST['userid'];
					if($checkDatabaseConnection){
						$sql = "DELETE FROM `user` WHERE `userid` = ".$id."";
						$result = mysql_query($sql) or $success = false;
					} else {
						$success = false;
					}
				} else {
					$success = false;
				}
				
				jecho($success);
				break;

				/**
				 * Retrieving cameras from db
				 */
				case "get-cameras":
					$success = true;
					$sql = "SELECT * FROM camera";
					$cameras = array();
					if($checkDatabaseConnection){
						$result = mysql_query($sql) or $success = false;
						if($success){
							while ($row = mysql_fetch_array($result)){
								$cam = new stdClass();
								$cam->id = $row["cameraid"];
								$cam->calibration = $row["calibration"];
								array_push($cameras, $cam);
							}
						}
					}
					jecho($cameras);
					break;
						
				/**
				 * Adding camera to db
				 */
				case "add-camera":
					$success = true;
					if (isset($_REQUEST['calibration'])){
						$calibration = $_REQUEST['calibration'];		
							
						if($checkDatabaseConnection){
							$sql = "INSERT INTO `camera` (`calibration`) VALUES ('".$calibration."');";
							$result = mysql_query($sql) or $success = false;
						} else {
							$success = false;
						}
					} else {
						$success = false;
					}
				
					$output = new stdClass();
					$output->success = $success;
					$output->id = mysql_insert_id();
				
					jecho($output);
					break;
						
				/**
				 * Removing camera from db
				 */
				case "remove-camera":
					$success = true;
					if (isset($_REQUEST['cameraid'])){
						$id = $_REQUEST['cameraid'];
						if($checkDatabaseConnection){
							$sql = "DELETE FROM `camera` WHERE `cameraid` = ".$id."";
							$result = mysql_query($sql) or $success = false;
						} else {
							$success = false;
						}
					} else {
						$success = false;
					}
				
					jecho($success);
					break;

				/**
				 * Removing camera from db
				 */
				case "set-camera-calibration":
					$success = true;
					if (isset($_REQUEST['cameraid']) && isset($_REQUEST['calibration'])){
						$id = $_REQUEST['cameraid'];
						$calibration = $_REQUEST['calibration'];
						if($checkDatabaseConnection){
							$sql = "UPDATE `camera` SET `calibration` = ".$calibration." WHERE `cameraid` = ".$id."";
							$result = mysql_query($sql) or $success = false;
						} else {
							$success = false;
						}
					} else {
						$success = false;
					}
					
					jecho($success);
					break;
							
		
	}
	
}


?>