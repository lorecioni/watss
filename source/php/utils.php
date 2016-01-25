<?php

/**
 * 
 * A set of utils function for WATTS annotation tool
 * 
 */

/** Create and return a random color HEX value **/
function getRandomColorHEX(){
	$red = rand(0,255);
	$green = rand(0,255);
	$blue = rand(0,255);
	$rgb = array($red,$green,$blue);
	$hex = "#";
	$hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
	$hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
	$hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);
	return $hex;
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

?>