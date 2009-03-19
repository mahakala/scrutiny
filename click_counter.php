<?php

	/******************************************************
	This script updates the columns click_counter and last_click in table 'links'
	after a user clicked  a link on the result listing.
	*******************************************************/
	
	error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
	define("APP", dirname(__FILE__).'/');
	include(APP."settings/conf.php");
	
	// if(Configure::read('add_url')!=1) {
	// 	header('Location: '.WEBROOT_DIR.'/');
	// 	exit();
	// }
	
	$url    = $_GET['url'];
	$query  = $_GET['query'];
	
	$url = str_replace("-_-", "&", $url); // decrypt the & character
	$time   = time();
	
	header("Location: $url");	//  this is where the user really wants to get when clicking the link.
								//  Okay, we will let him go. But also we will store the destination.
	
	$result = mysql_query("select last_click from ".TABLE_PREFIX."links  where url = '$url' LIMIT 1");
	echo mysql_error();
	$last_click = mysql_result($result, '0'); // get time of last click
	
	if($last_click+Configure::read('click_wait') < $time) { // prevent promoted clicks, else remember this click
		mysql_query ("update ".TABLE_PREFIX."links set click_counter=click_counter+1, last_click='$time', last_query='$query' where url = '$url' LIMIT 1");
		echo mysql_error();
	}
	exit(); // Good-bye, we've got your click.

?>