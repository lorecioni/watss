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
			$dbConnection = mysql_connect($host, $user, $password) or $checkDatabaseConnection = false;
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
			$log ='';
						
			$data = $_REQUEST['data'];
			$dbUser = $data['user'];
			$dbPass = $data['password'];
			$dbHost = $data['host'];
			$dbName = $data['name'];
			$dbConnection = mysql_connect($dbHost, $dbUser, $dbPass) or $success = false;
			if($success){
				$sql = "CREATE DATABASE IF NOT EXISTS `".$dbName."`";
				$result = mysql_query($sql) or $success = false;
				if($success){
					mysql_select_db($dbName) or $success = false;
					if($success){
						$log  .= generateSchema($dbConnection, $createSchemaScript);
					}
				} else {
					$log .= mysql_error();
				}
			}
				
			if($success){
				jecho($log);
			} else {
				error_500($log);
			}
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
	$log = $script;
	$sql = file_get_contents($script);
	$log .= $sql;
	return $log;
}

?>