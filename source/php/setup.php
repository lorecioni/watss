<?php
/**
 * Setup API
 */

$connectionIniPath = 'connection.ini';
$createSchemaScript = '../database/createSchema.sql';

if(isset($_REQUEST['action'])){
	
	require_once 'utils.php';
	
	switch($_REQUEST['action']) {
		
		case 'init':
			jecho($dbInfo);
			break;
			
		/**
		 * Test database connection
		 */
		case "test-database-connection":
			$checkDatabaseConnection = true;
			$user = $_REQUEST['user'];
			$password = $_REQUEST['password'];
			$host = $_REQUEST['host'];
			$dbConnection = mysqli_connect($host, $user, $password) or $checkDatabaseConnection = false;
			jecho($checkDatabaseConnection);
			break;
			
		/**
		 * Get cameras list
		 */
		case 'get-cameras':
			$cameras = array();
			if(isset($_REQUEST['folder'])){
				$dir = '../'.$_REQUEST['folder'];
				if(file_exists($dir)){
					foreach(scandir($dir) as $f) {
						if(!$f || $f[0] == '.') {
							continue; // Ignore hidden files
						}
						if(is_dir($dir . '/' . $f) && is_numeric($f)) {
							array_push($cameras, $f);
						}
					}
				}
			}
			
			jecho($cameras);
			break;
			
		/** Create connection ini function **/
		case 'create-connection':
			$data = $_REQUEST['data'];
			$dbUser = $data['user'];
			$dbPass = $data['password'];
			$dbHost = $data['host'];
			$dbName = $data['name'];
			
			$success = createConnectionIniFile($connectionIniPath, $dbUser, $dbPass, $dbHost, $dbName);				
			
			if($success){
				jecho($success);
			} else {
				error_500('Cannot create connection ini file. Check folder permissions');
			}
			break;
			
		/** Create connection ini function **/
		case 'create-schema':
			$success = true;
			$log = '';
						
			$data = $_REQUEST['data'];
			$dbUser = $data['user'];
			$dbPass = $data['password'];
			$dbHost = $data['host'];
			$dbName = $data['name'];
			$dbConnection = mysqli_connect($dbHost, $dbUser, $dbPass) or $success = false;
			
			if($success){
				$sql = "CREATE DATABASE IF NOT EXISTS `".$dbName."`";
				$result = mysqli_query($dbConnection, $sql) or $success = false;
				if($success){
					mysqli_select_db($dbConnection, $dbName) or $success = false;
					if($success){
						$success = generateSchema($dbConnection, $createSchemaScript);
					}
				} else {
					$log .= mysqli_error($dbConnection);
				}
			}
			
			if($success){
				jecho($success);
			} else {
				error_500($log);
			}
			mysqli_close($dbConnection);
			break;
			
		/**
		 *  Inserting cameras in database
		 */
		case 'insert-cameras':
			$success = true;
			
			$dbInfo = $_REQUEST['data']['connection'];
			$dbUser = $dbInfo['user'];
			$dbPass = $dbInfo['password'];
			$dbHost = $dbInfo['host'];
			$dbName = $dbInfo['name'];
			$dbConnection = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName) or $success = false;	
			
			$sql = 'INSERT INTO `camera` (`cameraid`, `calibration`) VALUES ';
			
			$cameras = json_decode($_REQUEST['data']['cameras']);
			for($i = 0; $i < count($cameras); $i++){
				$sql .= '('.$cameras[$i]->id.', '.$cameras[$i]->calibration.')';
				if($i < count($cameras) - 1){
					$sql .= ',';
				}
			}
			
			//FIXME remove comment
			//mysqli_query($dbConnection, $sql) or $success = false;
			
			if($success){
				jecho($success);
			} else {
				$log = mysqli_error($dbConnection);
				error_500($log);
			}
			mysqli_close($dbConnection);			
			break;
			
			/**
			 *  Inserting frames in database
			 */
			case 'insert-frames':
				$success = true;
					
				$dbInfo = $_REQUEST['data']['connection'];
				$dbUser = $dbInfo['user'];
				$dbPass = $dbInfo['password'];
				$dbHost = $dbInfo['host'];
				$dbName = $dbInfo['name'];
				$dbConnection = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName) or $success = false;
				
				$framesFolder = '../'.$_REQUEST['data']['framesFolder'];

				$sql = 'INSERT INTO `video` (`frameid`, `cameraid`, `path`, `date`) VALUES ';
					
				$cameras = json_decode($_REQUEST['data']['cameras']);

				for($i = 0; $i < count($cameras); $i++){
					$cameraid = $cameras[$i]->id;
					$dir = $framesFolder.'/'.$cameraid.'/';
					
					$count = 1;
					if(file_exists($dir)){
						$files = scandir($dir);
						for ($j = 0; $j < count($files); $j++){
							$f = $files[$j];
							if(!$f || $f[0] == '.') {
								// Ignore hidden files
								continue;
							}
							if(!is_dir($dir . '/' . $f) && exif_imagetype ($dir . '/' . $f)) {
								$sql .= "(".$count.", ".$cameraid.", '".($cameraid . '/' . $f)."', '".date('Y-m-d')."')";
								if($j < count($files) - 1){
									$sql .= ',';
								}
								$count += 1;
							}
						}
					}
					if($i < count($cameras) - 1){
						$sql .= ',';
					}	
				}
				
				//FIXME remove comment
				//mysqli_query($dbConnection, $sql) or $success = false;

				if($success){
					jecho($success);
				} else {
					$log = mysqli_error($dbConnection);
					error_500($log);
				}
				mysqli_close($dbConnection);
				break;
				
				
				/**
				 *  Inserting users in database
				 */
				case 'insert-users':
					$success = true;
						
					$dbInfo = $_REQUEST['data']['connection'];
					$dbUser = $dbInfo['user'];
					$dbPass = $dbInfo['password'];
					$dbHost = $dbInfo['host'];
					$dbName = $dbInfo['name'];
					$dbConnection = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName) or $success = false;
						
					$sql = 'INSERT INTO `user` (`name`) VALUES ';
						
					$users = json_decode($_REQUEST['data']['users']);
					for($i = 0; $i < count($users); $i++){
						$sql .= "('".$users[$i]."')";
						if($i < count($users) - 1){
							$sql .= ',';
						}
					}
						
					//FIXME remove comment
					//mysqli_query($dbConnection, $sql) or $success = false;
						
					if($success){
						jecho($success);
					} else {
						$log = mysqli_error($dbConnection);
						error_500($log);
					}
					mysqli_close($dbConnection);
					break;
	}
	
}

/**
 * Creates the connection ini file used for connecting to the database
 * @param Database $user
 * @param Database $passwd
 * @param Database $host
 * @param Database $name
 */
function createConnectionIniFile($path, $user, $passwd, $host, $name){
	$success = true;
	$ini = fopen($path, "w") or $success = false;
	if($success){
		$txt = "user=\"".$user."\"\n";
		$txt .= "password=\"".$passwd."\"\n";
		$txt .= "host=\"".$host."\"\n";
		$txt .= "db=\"".$name."\"";
		fwrite($ini, $txt);
		fclose($ini);
	}
	return $success;
}

/**
 * Generate database schema
 * @return boolean
 */
function generateSchema($connection, $script){
	$success = true;
	$sql = file_get_contents($script);
	$result = mysqli_multi_query($connection, $sql) or $success = false;
	return $success;
}

?>