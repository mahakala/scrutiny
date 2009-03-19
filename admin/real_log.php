<?php
/***********************************************************
If 'Real-time Logging' is enabled, this script takes over to display latest logging data.
Requesting fresh data from the JavaScript file 'real_ping.js' ,
all new logging data will always been placed into <div id='realLogContainer'  />
***********************************************************/

	define("APP", dirname(dirname(__FILE__)).'/');
	include(APP."settings/conf.php");

	if (DEBUG == '0') {
		error_reporting(0);  // suppress  PHP messages  
	} 

	set_time_limit (0);

	echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
	<head>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
		<title>Log File real-time output</title>
		<link rel='stylesheet' href='".TEMPLATE_DIR."thisstyle.css' media='screen' type='text/css' />
		<link rel='stylesheet' href='".TEMPLATE_DIR."thisstyle.css' media='all' type='text/css' />
		<meta http-equiv='cache-control' content='no-cache'>
		<meta http-equiv='pragma' content='no-cache'>        
		<script type='text/javascript' src='real_ping.js'></script>   
	</head>
	<body onload='process()'>   
		<div class='submenu cntr y3'>Sphider-plus v.".Configure::read('plus_nr')." - Real-time Logging.
		<br /><br />     
		Update every ".Configure::read('refresh')." seconds.</div>     
		<div id='realLogContainer'>&nbsp;</div>
	</body>
</html>
    ";
?>

