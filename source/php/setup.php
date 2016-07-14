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
			mysqli_close($dbConnection);
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
			$data = json_decode($_REQUEST['data']['connection']);
			$dbUser = $data->user;
			$dbPass = $data->password;
			$dbHost = $data->host;
			$dbName = $data->name;
			
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
			$data = json_decode($_REQUEST['data']['connection']);
			$dbUser = $data->user;
			$dbPass = $data->password;
			$dbHost = $data->host;
			$dbName = $data->name;
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
				mysqli_close($dbConnection);
			} else {
				error_500($log);
			}
			
			break;
			
		/**
		 *  Inserting cameras in database
		 */
		case 'insert-cameras':
			$success = true;
			
			$dbInfo = json_decode($_REQUEST['data']['connection']);
			$dbUser = $dbInfo->user;
			$dbPass = $dbInfo->password;
			$dbHost = $dbInfo->host;
			$dbName = $dbInfo->name;
			$dbConnection = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName) or $success = false;	
			
			$sql = 'INSERT INTO `cameras` (`cameraid`, `calibration`, `intrinsic`, `omography`, `param`) VALUES ';
			
			$cameras = json_decode($_REQUEST['data']['cameras']);
			for($i = 0; $i < count($cameras); $i++){
				$calibActive = $cameras[$i]->active ? 1 : 0;
				$param =  strlen($cameras[$i]->param) > 0 ? $cameras[$i]->param : 0.5;
				$sql .= "(".$cameras[$i]->id.", ".$calibActive.", '".$cameras[$i]->intrinsic."', '".$cameras[$i]->omography."', ".$param.")";
				if($i < count($cameras) - 1){
					$sql .= ',';
				}
			}
									
			mysqli_query($dbConnection, $sql) or $success = false;
			
			if($success){
				jecho($success);
			} else {				
				error_500('Error importing cameras on database.');
			}
			mysqli_close($dbConnection);			
			break;
			
			/**
			 *  Inserting frames in database
			 */
			case 'insert-frames':
				$success = true;
					
				$dbInfo = json_decode($_REQUEST['data']['connection']);
				$dbUser = $dbInfo->user;
				$dbPass = $dbInfo->password;
				$dbHost = $dbInfo->host;
				$dbName = $dbInfo->name;
				$dbConnection = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName) or $success = false;
				
				$framesFolder = '../'.$_REQUEST['data']['framesFolder'];

				$sql = 'INSERT INTO `frames` (`frameid`, `cameraid`, `path`, `date`) VALUES ';
					
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
				
				mysqli_query($dbConnection, $sql) or $success = false;

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
						
					$dbInfo = json_decode($_REQUEST['data']['connection']);
					$dbUser = $dbInfo->user;
					$dbPass = $dbInfo->password;
					$dbHost = $dbInfo->host;
					$dbName = $dbInfo->name;
					$dbConnection = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName) or $success = false;
						
					$sql = 'INSERT INTO `users` (`name`, `password`) VALUES ';
						
					$users = json_decode($_REQUEST['data']['users']);
					for($i = 0; $i < count($users); $i++){
						$sql .= "('".$users[$i]."', '')";
						if($i < count($users) - 1){
							$sql .= ',';
						}
					}
						
					mysqli_query($dbConnection, $sql) or $success = false;
						
					if($success){
						jecho($success);
					} else {
						$log = mysqli_error($dbConnection);
						error_500($log);
					}
					mysqli_close($dbConnection);
					break;
					
					/**
					 *  Inserting poi in database
					 */
					case 'insert-poi':
						$success = true;
					
						$dbInfo = json_decode($_REQUEST['data']['connection']);
						$dbUser = $dbInfo->user;
						$dbPass = $dbInfo->password;
						$dbHost = $dbInfo->host;
						$dbName = $dbInfo->name;
						$dbConnection = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName) or $success = false;
					
						$sql = 'INSERT INTO `poi` (`poiid`, `cameraid`, `location_x`, `location_y`, `width`, `height`, `name`) VALUES ';
					
						$cameras = json_decode($_REQUEST['data']['cameras']);
						
						for($i = 0; $i < count($cameras); $i++){
							$sql .= "(0, ".$cameras[$i]->id.", 0, 0, 0, 0, 'Not set')";
							if($i < count($cameras) - 1){
								$sql .= ',';
							}
						}
					
						mysqli_query($dbConnection, $sql) or $success = false;
					
						if($success){
							jecho($success);
						} else {
							$log = mysqli_error($dbConnection);
							error_500($log);
						}
						mysqli_close($dbConnection);
						break;
						
					case 'parse-sql-script':
						if ( $_FILES['file']['error'] > 0) {
							error_500('Error: ' . $_FILES['file']['error']);
						}
						else {
							require_once 'config.php';
							global $config;
							
							$sql = file_get_contents($_FILES['file']['tmp_name']);
							$result = SQLParse($sql);
							
							$response = array(
								'name' => $result['name'],
								'tables' => array()
							);
							 
							foreach ($result['tables'] as $t){		
								if(in_array($t, $config->tables)){
									array_push($response['tables'], array('name' => $t, 'success' => true));	
								} else {
									array_push($response['tables'], array('name' => $t, 'success' => false));
								}
							}
							move_uploaded_file($_FILES['file']['tmp_name'], "../database/import.sql");
							jecho($response);
						}
						
						break;
						
				case 'import-data':
					$success = true;
					$dbInfo = json_decode($_REQUEST['data']['connection']);
					$dbUser = $dbInfo->user;
					$dbPass = $dbInfo->password;
					$dbHost = $dbInfo->host;
					$dbName = $dbInfo->name;
					
					if($dbName != ''){
						$dbConnection = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName) or $success = false;
						
					} else {
						$dbConnection = mysqli_connect($dbHost, $dbUser, $dbPass) or $success = false;
					}
					
					$sql = file_get_contents("../database/import.sql");
					mysqli_query($dbConnection, $sql) or $success = false;
					
					jecho($success);
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