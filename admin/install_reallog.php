<?php
error_reporting(E_ALL);
$settings_dir = "../settings";
include "$settings_dir/conf.php";
$template_dir = "../templates";
$template_path = "$template_dir/Configure::read('template')";
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1' />
<meta http-equiv='Content-Style-Type' content='text/css' />
<title>Sphider-plus installation script for real-time logging</title>
<link rel='stylesheet' href='".TEMPLATE_DIR."thisstyle.css' type='text/css' />
</head>
<body>
<h1>Installation script to create the additional table for real-time logging.</h1>
<p>
";


$error = 0;

// Structure for table 'real_log'
mysql_query("create table `".TABLE_PREFIX."real_log`(
  url varchar(255) not null,
  real_log mediumtext,
  refresh integer not null primary key,  
  created timestamp NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br />\n";
	$error += mysql_errno();
}

if ($error >0) {
	echo "</p>\n<p class='warn em'>Creating of table failed. Consult the above error messages.</p>\n";
} else {
	echo "</p>\n<p class='warnok em'>Creating table successfully completed.<br /><br /></p>\n";
}
echo "</body>
</html>";

?>