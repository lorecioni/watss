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

