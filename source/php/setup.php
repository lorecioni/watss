<?php
/**
 * Setup API
 */

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
			$connectionIni = 'connection.ini';			
			$dbUser = $data['user'];
			$dbPass = $data['password'];
			$dbHost = $data['host'];
			$dbName = $data['name'];
			
			$success = createConnectionIniFile($connectionIni, $dbUser, $dbPass, $dbHost, $dbName);				
			jecho($success);
			break;
			
		/** Create connection ini function **/
		case 'create-schema':
			$success = true;
			$data = $_REQUEST['data'];
			$dbUser = $data['user'];
			$dbPass = $data['password'];
			$dbHost = $data['host'];
			$dbName = $data['name'];
			
			$dbConnection = mysql_connect($dbHost, $dbUser, $dbPass) or $success = false;
			if($success){
				$sql = 'CREATE DATABASE `'.$dbName.'`';
				$result = mysql_query($sql) or $success = false;
				if($success){
					mysql_select_db($dbName) or $success = false;
					if($success){
						$success = generateSchemaScript();
					}
				}
			}
				
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
function generateSchemaScript(){
	return true;
}

?>