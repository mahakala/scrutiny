<?php
	//define("_SECURE",1) ;    // define secure constant
	session_start();
	
	$admin = "admin";
	$admin_pw = "admin";

	if(isset($_POST['user']) && isset($_POST['pass'])) {
		$username = $_POST['user'];
		$password = $_POST['pass'];
		if (($username == $admin) && ($password ==$admin_pw)) {
			$_SESSION['admin'] = $username;
			$_SESSION['admin_pw'] = $password;
		}
		header("Location: ".WEBROOT_DIR."/admin/");
	} else if((isset($_SESSION['admin']) && isset($_SESSION['admin_pw']) &&$_SESSION['admin'] == $admin && $_SESSION['admin_pw'] == $admin_pw ) || ($_SERVER['REMOTE_ADDR']=="")) {
		
	} else {
		?><!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
	<head>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
		<title>Sphider-plus administrator tools</title>
		<link rel='stylesheet' type='text/css' href='<?php echo TEMPLATE_DIR; ?>admin.css' />
	</head>
	<body class="loginpage">
		<form class='txt' action='<?php echo WEBROOT_DIR ?>/admin/' method='post'>
			<fieldset>
				<h1>Sphider Login</h1>
				<div class="input">
					<label for='user'>Name</label>
					<input type='text' name='user' id='user' size='15' maxlength='15' title='Required - Enter your user name here' />
				</div>
				<div class="input">
					<label for='pass'>Password</label>
					<input type='password' name='pass' id='pass' size='15' maxlength='15' title='Required - Enter your password here' />
				</div>
				<div class="submit">
					<input class='sbmt' type='submit' id='submit' value='&nbsp;Login &raquo;&raquo; ' title='Click to confirm' />
				</div>
			</fieldset>
		</form>
	</body>
</html>
<?php
		exit();
	}
?>