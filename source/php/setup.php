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

	}
	
}


?>