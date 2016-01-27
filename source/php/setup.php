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

	}
	
}


?>