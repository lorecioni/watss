<?php
/**
 * Configuration file for WATTS API
 */

$config = new stdClass();

//Enable debug
$config->debug = false;

//Database connection file
$config->connection = "./connection.ini";

//Bounding box settings
$config->bb = new stdClass();
$config->bb->x = -100;
$config->bb->y = -100;
$config->bb->width = 80;
$config->bb->height = 120;
$config->bbV = new stdClass();
$config->bbV->x = -100;
$config->bbV->y = -100;
$config->bbV->width = 40;
$config->bbV->height = 50;

//Frames path
$config->framesDir = 'frames';

//Real people
$config->realPeopleDefaultImg = "../img/avatars/default.png";

//Database table names
$config->tables = array('cameras', 'poi', 'users', 'groups', 'avatars', 'people', 'frames');

//Python configuration
$config->python_interpreter = '/usr/local/bin/python3';
$config->predict_script_path = '/Applications/MAMP/htdocs/watss/source/script/predict.py';
