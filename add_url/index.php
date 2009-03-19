<?php
	error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
	define("APP", dirname(dirname(__FILE__)).'/');
	include(APP."settings/conf.php");
	
	if(Configure::read('add_url')!=1) {
		header('Location: '.WEBROOT_DIR.'/');
		exit();
	}

	$admin_dir 		= APP."admin/";
	$lang_dir_dir 	= APP."languages/";

	if (DEBUG == '0') {
		error_reporting(0);  // suppress PHP messages
	}

	require_once(INCLUDE_DIR."class.SearchDisplay.php");
	require_once(INCLUDE_DIR."class.Category.php");
	require_once    (INCLUDE_DIR."commonfuncs.php");

	$date           = strftime("%d.%m.%Y");                                 //      Format for date
	$time           = date("H:i");                                          //      Format for time
	$mailer         = "Configure::read('mytitle') Addurl-mailer";                             //      Name of mailer
	$subject1       = "A new site suggestion arrived for Sphider-plus";     //      Subject for administrator e-mail when a new suggestion arrived
	$category_id    = '';
	$B1             = '';

	if(Configure::read('auto_lng') == 1) {   //  if enabled in Admin settings get country code of calling client
		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			$cc = substr(htmlspecialchars($_SERVER['HTTP_ACCEPT_LANGUAGE']), 0, 2);
			$handle = @fopen($lang_dir."$cc-language.php","r");
			if($handle) {
				Configure::write('language', $cc); // if available set language to users slang
			} else {
				require_once($lang_dir.Configure::read('language')."-language.php");
			}
			@fclose($handle);
		} else {
			require_once($lang_dir.Configure::read('language')."-language.php");
		}
	} else {
		require_once($lang_dir.Configure::read('language')."-language.php");
	}
	
	extract(getHttpVars());
	
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<meta http-equiv="Content-type" content="text/html; charset='.Configure::read('home_charset').'"> 
			<meta http-equiv="Content-Language" content="en-us" />
			<title>'.Configure::read('mytitle').' Suggest a new site</title>
			<!-- CSS -->
			<link rel="stylesheet" href="<?php echo TEMPLATE_DIR; ?>master.css" type="text/css" media="screen" />
			<!--[if IE]>
				<link rel="stylesheet" href="<?php echo TEMPLATE_DIR; ?>ie.css" type="text/css" media="screen" />
			<![endif]-->
			<script type="text/javascript" src="'.WEBROOT_DIR.'/js/jquery-1.3.2.min.js"></script>
			<script type="text/javascript" src="'.WEBROOT_DIR.'/js/front.js"></script>
		</head>
		<body>
	';

	if ($B1 == $sph_messages['submit']) {
		if(Configure::read('captcha') == 1) {     // if Admin selected, evaluate Captcha
			error_reporting(E_ERROR);
			session_start();

			if (($_POST['captext']) != $_SESSION['currentcaptcha']) {
				echo "
						<h1>Configure::read('mytitle')</h1>
						<p class='em cntr warnadmin'>".$sph_messages['invalidCaptcha']."</p>
						<p><a class='bkbtn' href='".$_SERVER['PHP_SELF']."' title='Go back to Suggest form'>".$sph_messages['BackToSubForm']."</a></p>
					</body>
					</html>";
				die ('');
			}
			if (DEBUG == '0') {
				error_reporting(0);  // suppress  PHP messages
			} else {
				error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
			}
			session_destroy();
		}

		// 	clean input
		$url 		= 	cleaninput(cleanup_text(trim(substr ($url, 0,100))));
		$title 		= 	cleaninput(cleanup_text(trim(substr ($title, 0,100))));
		$description = 	cleaninput(cleanup_text(nl2br(trim(substr ($description, 0,250)))));
		$email 		= 	cleaninput(cleanup_text(trim(substr ($email, 0,100))));
		
		//	check Url
		$input  = $url;
		validate_url($input);
        $url = $input;
			
		//	check Title
		if(!preg_match('/^[[:print:]]{5,100}$/', $title)) {
			echo "
					<h1>Configure::read('mytitle')</h1>
					<p class='em cntr warnadmin'>".$sph_messages['InvTitle']."</p>
					<p><a class='bkbtn' href='".$_SERVER['PHP_SELF']."' title='Go back to Suggest form'>".$sph_messages['BackToSubForm']."</a></p>
				</body>
				</html>";
			die ('');
		}

		//	check Description input
		if(!preg_match('/^[[:print:]]{5,250}$/', $description)) {
			echo "
					<h1>Configure::read('mytitle')</h1>
					<p class='em cntr warnadmin'>".$sph_messages['InvDesc']."</p>
					<p><a class='bkbtn' href='addurl.php' title='Go back to Suggest form'>".$sph_messages['BackToSubForm']."</a></p>
				</body>
				</html>";
			die ('');
		}

		// check e-mail account 
		$input  = $email;
		validate_email($input);
		$email = $input;

		// Is the new URL banned ?
		$res = 0;
		$Burl = 0;
		$Bquery = "SELECT * FROM ".TABLE_PREFIX."banned LIMIT 0 , 30000";
		$Bresult = mysql_query($Bquery);
		if (DEBUG > '0') echo mysql_error();
		
		if (mysql_num_rows($Bresult) <> '') {
			while ($Brow = mysql_fetch_array($Bresult)) {
				if (!eregi($Brow['domain'],$url)){
					$Burl = 0;
				} else {
					$Burl = 1;
					echo "
							<h1>Configure::read('mytitle')</h1>
							<p class='em'>
								Sorry to tell you.<br />
								But the site you suggested is banned from this search engine.<br />
								We will not index that site.
							</p>
							<p><a class='bkbtn' href='index.php' title='Go back to Sphider-plus'>Back to Sphider-plus</a></p>
						</body>
						</html>";
					die();
				}
			}
		} else { $Burl = 0; }
		
		// suggested URL is already indexed?
		$new_url = 0;
		$query = "SELECT * FROM ".TABLE_PREFIX."sites where url like '%$url%'";
		$result = mysql_query($query);
		if (DEBUG > '0') echo mysql_error();
				
		if (mysql_num_rows($result) <> '') {
			$new_url = 0;
			echo "
						<h1>Configure::read('mytitle')</h1>
						<p class='em'>
							Thank you for your suggestion.<br />
							But the suggested site is already indexed by this search engine.
						</p>
						<p><a class='bkbtn' href='index.php' title='Go back to Sphider-plus'>Back to Sphider-plus</a></p>
					</body>
				</html>";
			die();
		}

		//	check if new URL was already suggested before
		$new_url = 0;
		$query = "SELECT * FROM ".TABLE_PREFIX."addurl LIMIT 0 , 300";
		$result = mysql_query($query);
		if (DEBUG > '0') echo mysql_error();
				
		if (mysql_num_rows($result) <> '') {
			while ($row = mysql_fetch_array($result)) {
				if ($url != $row['url']){
					$new_url = 1;
				} else {
					$new_url = 0;
					echo "
								<h1>Configure::read('mytitle')</h1>
								<p class='em'>
									Thank you for your suggestion.<br />
									But this Url was already suggested by someone else before.
								</p>
								<p><a class='bkbtn' href='index.php' title='Go back to Sphider-plus'>Back to Sphider-plus</a></p>
							</body>
						</html>";
					die();
				}
			}
		} else { 
			$new_url = 1; 
		}

		if($new_url == 1) {
			// Time to store all into database and output a thanks for suggestion		
			mysql_query("INSERT INTO ".TABLE_PREFIX."addurl (url, title, description, category_id,account) VALUES ('".$url."', '".$title."', '".$description."', '".$category_id."', '".$email."')");
			if (DEBUG > '0') echo mysql_error();

			echo "
				<h1>Configure::read('mytitle')</h1>
				<p class='em'>
					Thank you very much.<br />
					We will check your suggestion " .$url. " within the next future.<br />
					If the new site fulfills all requirements of this search engine, it will be indexed immediately.<br />
					About our decission we will inform you by e-mail.<br />
					Thanks again for your effort.
				</p>
				<p><a class='bkbtn' href='index.php' title='Go back to Sphider-plus'>Back to Sphider-plus</a></p>";

			// Finally inform the administrator about the new suggestion
			$title  = str_replace ('\\','',$title); // recover title
			$title	= str_replace ('&quot','"',$title);

			$description	= str_replace ('\\','',$description); // recover description
			$description	= str_replace ('&quot','"',$description);
			$cat = '';

			if ($category_id != 0) {
				$query = "SELECT * FROM ".TABLE_PREFIX."categories WHERE category_id = $category_id";
				$result = mysql_query($query);
				if (DEBUG > '0') echo mysql_error();
				mysql_close();
				$cat ='';
				if ($result !=0) {
					$row = mysql_fetch_array($result);
					$cat = $row['category']; // fetch name of category
				}
			}
			$header = "from: $mailer<".Configure::read('dispatch_email').">\r\n";
			$header .= "Reply-To: ".Configure::read('dispatch_email')."\r\n";
			$subject1 = "A new site suggestion arrived for Sphider-plus"; // Subject for e-mail to administrator when suggestion arrived

			if (Configure::read('addurl_info') == 1) { //  should we inform the admin by e-mail?
				// Text for e-mail to administrator when suggestion arrived
				$text1 = "On $date at $time a new site was suggested!\n
The following dates were submitted:\n\n
URL           : $url\n
Titel         : $title\n
Description   : $description\n
Category      : $cat\n
E-mail account: $email\n\n
This mail was automatically generated by: $mailer.\n\n";

				if (mail(Configure::read('admin_email'),$subject1,$text1,$header) or die ("<br /><br /><br />Error to inform the administrator of this site ( Configure::read('admin_email') )<br /><br />Never the less your data was stored on our database.<br /><br />They will be checked within the next future.<br /><br />About the result you will be informed as soon as possible by e-mail.<br /><br />"));		
			}
		}
	} else { //  Here we start the output of the Submission form
		echo "
			<h1>Configure::read('mytitle')</h1>
			<h2>".$sph_messages['SubForm']."</h2>
			
			<p class='advsrch'>
				".$sph_messages['SubmitHeadline']."
			</p>
			<p class='advsrch'>
				( ".$sph_messages['AllFields']." ! )
			</p>
			<br />
			<div class='panel w75'>
				<form class='txt' name='add_url' action='".$_SERVER['PHP_SELF']."'  method='post'>
					<label for='id'>".$sph_messages['New_url']."</label>
					<input type='text' id='url' name='url' value='http://' size='52' maxlength='100' />
					<label for='title'>".$sph_messages['Title']."</label>
					<input type='text' name='title' id='title' size='52' maxlength='100' />
					<label for='description'>".$sph_messages['Description']."</label>
					<textarea wrap='physical' class='farbig' rows='5' id='description' name='description' cols='40'></textarea>
		";

		if(Configure::read('show_categories') =='1') {     // if Admin selected, show categories
			echo "<label for='category_id'>".$sph_messages['Category']."</label>
				<select id='category_id' name=\"category_id\" size=\"1\">\r\n";
			list_catsform(0, 0, "white", "","");
			echo "</select>\r\n";
		}

		echo "\r\n<label for=\"email\">".$sph_messages['Account']."</label>
			<input id='email' type='text' name='email' size='52' maxlength='100' />\r\n";

		if(Configure::read('captcha') == 1) {     // if Admin selected, show Captcha
			echo "\r\n<label for='captext'>".$sph_messages['enterCaptcha']."</label>
				<img src='".WEBROOT_DIR."/include/make_captcha.php' name='capimage' border='0' />
				<br /><br />
				<input type='text' value='' id='captext' name='captext' /></textarea>\r\n";
		}
		$submit = $sph_messages['submit'];
		echo "\r\n<input class='submit-button' type='submit' value='$submit' name='B1' />
				</form>
			</div>\r\n";
	}

	// The following should only be removed if you contribute to the Sphider project..
	// Note that this is a requirement under the GPL licensing agreement, which Sphider-plus acknowledges.	
	footer();

	echo "
		</div>
		</body>
		</html>
	";
?>