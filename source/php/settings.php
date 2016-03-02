<?php
/**
 * Configuration API for retrieving information from database for the settings page
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
	require_once 'queries.php';
	
	$QUERIES = new Queries();
	
	switch($_REQUEST['action']) {
		
		case 'init':
			$useMotion = false;
			$usePeople = false;
			$useKalman = false;
			$trackingconf = file_get_contents('../script/trackingconf.conf');
			$parsed = explode("\n", $trackingconf);
			foreach ($parsed as $row){
				$row = explode("=", $row);
				$key = trim($row[0]);
				$value = trim($row[1]);
				if(strcmp($key, "USE_MOTION") == 0){
					$useMotion = $value == 'True' ? true : false;
				} else if(strcmp($key, "USE_PEDESTRIAN_DETECTOR") == 0){
					$usePeople = $value == 'True' ? true : false;
				} else if(strcmp($key, "USE_KALMAN_FILTER") == 0){
					$useKalman = $value == 'True' ? true : false;
				}	
			}
			
			$info = $dbInfo;
			$info['useMotion'] = $useMotion;
			$info['usePeople'] = $usePeople;
			$info['useKalman'] = $useKalman;
			
			jecho($info);
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
			$sql = $QUERIES->getUsers();
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
						$sql = $QUERIES->insertUser($name);
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
						$sql = $QUERIES->deleteUser($id);
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
					$sql = $QUERIES->getCameras();
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
							$sql = $QUERIES->insertCamera($calibration);
							$result = mysql_query($sql) or $success = false;
							
							$sql = $QUERIES->insertPOI(0, mysql_insert_id(), 0, 0, 0, 0, 'Not set');
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
							$sql = $QUERIES->deleteCamera($id);
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
							$sql = $QUERIES->updateCamera($id, $calibration);
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
				 * Retrieving poi from db
				 */
				case "get-poi":
					$success = true;
					$sql = $QUERIES->getUsefulPoi();
					$pois = array();
					if($checkDatabaseConnection){
						$result = mysql_query($sql) or $success = false;
						if($success){
							while ($row = mysql_fetch_array($result)){
								$poi = new stdClass();
								$poi->id = $row["poiid"];
								$poi->cameraid = $row["cameraid"];
								$poi->x = $row["location_x"];
								$poi->y = $row["location_y"];
								$poi->width = $row["width"];
								$poi->height = $row["height"];
								$poi->name = $row["name"];
								array_push($pois, $poi);
							}
						}
					}
					jecho($pois);
					break;							
				
				/**
				 * Adding poi to db
				 */
					case "add-poi":
					$success = true;
					if (isset($_REQUEST['poiid']) && isset($_REQUEST['cameraid'])
						&& isset($_REQUEST['name']) && isset($_REQUEST['x'])
						&& isset($_REQUEST['y']) && isset($_REQUEST['width'])
						&& isset($_REQUEST['height'])){
														
						if($checkDatabaseConnection){
							$sql = $QUERIES->insertPOI($_REQUEST['poiid'], $_REQUEST['cameraid'], $_REQUEST['x'], $_REQUEST['y'], $_REQUEST['width'], $_REQUEST['height'], $_REQUEST['name']);
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
					 * Update propagation settings
					 */
					case 'update-propagation':
						$useMotion = $_REQUEST['useMotion'] == 1 ? "True" : "False";
						$usePeople = $_REQUEST['usePeople'] == 1 ? "True" : "False";
						$useKalman = $_REQUEST['useKalman'] == 1 ? "True" : "False";
						
						$trackconf = "";
						$trackingconf = file_get_contents('../script/trackingconf.conf');
						$parsed = explode("\n", $trackingconf);
						foreach ($parsed as $conf){
							$row = explode("=", $conf);
							$key = trim($row[0]);
							$value = trim($row[1]);
							if(strcmp($key, "USE_MOTION") == 0){
								$trackconf .= "USE_MOTION = ".$useMotion."\n";
							} else if(strcmp($key, "USE_PEDESTRIAN_DETECTOR") == 0){
								$trackconf .= "USE_PEDESTRIAN_DETECTOR = ".$usePeople."\n";
							} else if(strcmp($key, "USE_KALMAN_FILTER") == 0){
								$trackconf .= "USE_KALMAN_FILTER = ".$useKalman."\n";
							} else {
								if($conf != "")	
									$trackconf .= $conf."\n";
							}
						}
						$out = fopen("../script/trackingconf.conf", "w");
						fwrite($out, $trackconf);
						fclose($out);
						break;
	}
	
}


?>