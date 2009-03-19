<?php
/***********************************************************
If Real-time Logging is enabled, this script delivers refresh rate and 
latest logging data, requested from the JavaScript file 'real_ping.js'.
Also reset of the real_log table is performed. 
This is the server-side part of the AJAX function.
***********************************************************/ 
   
    // make sure that user's browser doesn't cache the results
    header('Expires: Wed, 23 Dec 1980 00:30:00 GMT');   // time in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');

	define("APP", dirname(dirname(__FILE__)).'/');
	include(APP."settings/conf.php");

    include(INCLUDE_DIR."commonfuncs.php");
    
    if (DEBUG == '0') {
        error_reporting(0);  //     suppress  PHP messages  
    } 
    
    //set_error_handler('error_handler', E_ALL);    // local error_handler only for debugging. DO NOT USE ON SHARED HOSTING SYSTEMS ! ! !    
    set_time_limit (0);    
    $action = ''; 

    $action = $_GET['action'];                          // what to do now?
    $action = substr(cleaninput($action), '0', '6');    // clean input as it comes from a far away client
    
    if ($action == 'GetLog') {                          //  enter here for fresh log info

        $result = mysql_query("select real_log from ".TABLE_PREFIX."real_log  LIMIT 1");
        if (DEBUG > '0') echo mysql_error();
        mysql_query ("update ".TABLE_PREFIX."real_log set `real_log`='' LIMIT 1");
        if (DEBUG > '0') echo mysql_error();                        
        $log_data = stripslashes(mysql_result($result, '0'));   //  get actual real-log info and clean data
        $real_buf = "<p class='evrow'>$log_data";          
        echo $real_buf;
        
        mysql_free_result($result) ;  
        unset ($real_buf);
        
    }
    elseif ($action == 'Ready')         //      enter here to catch refresh rate 
    {
        $result = mysql_query("select refresh from ".TABLE_PREFIX."real_log  LIMIT 1");
        if (DEBUG > '0') echo mysql_error();
        $rate = mysql_result($result, '0'); 
        
        echo $rate;
    } else {
        echo "
            Error talking to the server. Transfered action: '$action'.
        ";
    }
    
?>
