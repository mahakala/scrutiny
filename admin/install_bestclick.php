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
<h1>Installation script to create the additional rows<br />in 'links' and 'keywords' tables for<br /><br />'Most popular links' logging and 'Query hits in fulltext'.</h1>
<p>
";


$error = 0;

// Additional rows for table 'links'
mysql_query("ALTER TABLE `".TABLE_PREFIX."links` 
ADD `click_counter` INT NULL DEFAULT '0',
ADD `last_click` INT NULL DEFAULT '0',
ADD `last_query` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL;");

if (mysql_errno() > 0) {
	print "Error: ";
	print mysql_error();
	print "<br />\n";
	$error += mysql_errno();
}

// Additional row for query scores in fulltext
for ($i=0;$i<=15; $i++) {
	$char = dechex($i);
	mysql_query("ALTER TABLE `".TABLE_PREFIX."link_keyword$char`
        ADD `hits` INT( 3 ) NULL DEFAULT '0';");

	if (mysql_errno() > 0) {
		print "Error: ";
		print mysql_error();
		print "<br />\n";
		$error += mysql_errno();
	}
}

if ($error >0) {
	echo "</p>\n<p class='warn em'>Creating of rows failed. Consult the above error messages.</p>\n";
} else {
	echo "</p>\n<p class='warnok em'>Creating additional rows successfully completed.<br /><br /></p>\n";
}
echo "</body>
</html>";

?>