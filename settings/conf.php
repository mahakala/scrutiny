<?php

/*********************** 
Debug Mode
***********************/

if (!defined('DEBUG')) {
	define('DEBUG', 2);
}

/*********************** 
Database Settings
***********************/

$database_configuration = array(
	array(
		"database" => "pc_ee",
		"user" => "root",
		"password" => "root",
		"host" => "localhost",
		"prefix" => "",
		"domain" => "ee.dev"
	),
	array(
		"database" => "sphider",
		"user" => "root",
		"password" => "root",
		"host" => "localhost",
		"prefix" => "ee_",
		"domain" => "walker.dev"
	),
	array(
		"database" => "sphider",
		"user" => "root",
		"password" => "root",
		"host" => "localhost",
		"prefix" => "ee_",
		"domain" => "signalfade.com"
	)
);

/* Server-side Paths*/
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('INCLUDE_DIR')) {
	define('INCLUDE_DIR', APP.'include'.DS);
}

if (!defined('LANGUAGE_DIR')) {
	define('LANGUAGE_DIR', APP.'languages'.DS);
}

//Path to PDF converter
if (!defined('PDFTOTEXT')) {
	define("PDFTOTEXT", APP.'converter'.DS.'pdftotext.exe');
}

//Path to DOC converter
if (!defined('CATDOC')) {
	define("CATDOC", APP.'converter'.DS.'catdoc.exe');
}

//Path to XLS converter
if (!defined('XLSCSV')) {
	define("XLSCSV", APP.'converter'.DS.'xls2csv.exe');
}

//Path to PPT converter
if (!defined('CATPPT')) {
	define("CATPPT", APP.'converter'.DS.'catppt.exe');
}

// Temporary directory, this should be readable and writable
if (!defined('TMP_DIR')) {
	define('TMP_DIR', APP.'admin'.DS.'tmp');
}

//Log directory, this should be readable and writable
if (!defined('LOG_DIR')) {
	define('LOG_DIR', APP.'admin'.DS.'log');
}

// Sitemap directory, this should be readable and writable 
if (!defined('SMAP_DIR')) {
	define('SMAP_DIR', APP.'admin'.DS.'sitemaps');
}

require_once(APP.'include'.DS.'class.Database.php');
require_once(APP.'include'.DS.'class.Configure.php');

if(!defined('DB_NOSTART')) {
	$db = new DATABASE();
	Configure::build();
}

/* WEB-based Paths*/
if (!defined('WEBROOT_DIR')) {
	define('WEBROOT_DIR', DS.basename(dirname(dirname(__FILE__))));
}

define('TEMPLATE_DIR', WEBROOT_DIR.'/templates/'.Configure::read('template').'/');

?>