<?php
	set_time_limit(0);

	define("APP", dirname(dirname(__FILE__)).'/');
	include(APP."settings/conf.php");

	include(APP."admin/auth.php");

	if (DEBUG == '0') {
		error_reporting(0);  //     suppress  PHP messages  
	} else {
		error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
	}

	include(APP."include/commonfuncs.php");
	//require_once('PhpSecInfo/phpSecInfo.php');

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
		      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
				<meta http-equiv="Content-Language" content="en-us" />
				
				<title>Sphider &rsaquo; Administrator</title>
				
		<!-- CSS -->
		<link rel="stylesheet" href="'.TEMPLATE_DIR.'admin.css" type="text/css" media="screen" />
		<!--[if IE]>
			<link rel="stylesheet" href="'.TEMPLATE_DIR.'ie.css" type="text/css" media="screen" />
		<![endif]-->
	</head>
	<body>
	';
    $php_vers = phpversion();
    if (preg_match('/^4\./', trim($php_vers)) == '1') {
        echo "<br />
            <div id='main'>
            <h1 class='cntr'>
            Sphider-plus. The Open-Source PHP Search Engine
            </h1>
                <div class='cntr warnadmin'>
                    <br />
                    Your current PHP version is $php_vers
                    <br /><br />
                    Sorry, but Sphider-plus v. ".Configure::read('plus_nr')." requires PHP 5.x
                    <br /><br />
                </div>
            </div>
            </body>
            </html>
        ";
        die ('');
    }
 