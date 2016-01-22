<?php
/**
 * Configuration script for retrieving information from database for the settings page
 */

$configFile = "php/connection.ini";

if(file_exists($configFile)){
	$dbConnection = parse_ini_file($configFile);
} else {
	$dbConnection = array(
		'user' => '',
		'password' => '',
		'host' => '',
		'db' => ''
	);
}
var_dump($_REQUEST['action']);
/*
if(isset($_REQUEST['action'])){
	
	require_once 'php/utils.php';
	
	switch($_REQUEST['action']) {
		
		case 'update-database-connection':
			$success = true;
			
			$user = $_REQUEST['user'];
			$password = $_REQUEST['password'];
			$host = $_REQUEST['host'];
			$db = $_REQUEST['db'];
			
			$ini = fopen($configFile, "w") or $success = false;
			if($success){
				$txt = "user=\"".$user."\"\n";
				$txt .= "password=\"".$password."\"\n";
				$txt .= "host=\"".$host."\"\n";
				$txt .= "db=\"".$db."\"";
				fwrite($ini, $txt);
				fclose($ini);
			}
			jecho($success);
			break;
		
	}
	
}
*/

?>