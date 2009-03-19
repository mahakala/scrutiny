<?php
/********************************************
* Sphider-plus
* Version 1.7  created 2008-11-27

* Based on original Sphider version 1.3.4
* released: 2008-04-29
* by Ando Saabas	 http://www.sphider.eu
*
* This program is licensed under the GNU GPL by:
* Rolf Kellner	[Tec]	sphider(a t)ibk-kellner.de
* Original Sphider GNU GPL licence by:
* Ando Saabas	ando(a t)cs.ioc.ee
********************************************/

	error_reporting (E_ALL ^ E_NOTICE ^ E_WARNING);
	define("_SECURE",1);	// define secure constant

	define("APP", dirname(__FILE__).'/');
	require_once(APP."settings/conf.php");

	if(Configure::read('allow_default_search')!=1)
	{
		header('Location: /');
		exit();
	}
	
	if (DEBUG == '0')
	{
		error_reporting(0);	 // suppress  PHP messages
	}

	require_once(INCLUDE_DIR."commonfuncs.php");
	require_once(INCLUDE_DIR."class.SearchDisplay.php");
	
	$search = new SearchDisplay();
	
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=<?php print Configure::read('home_charset')?>"> 
		<meta http-equiv="Content-Language" content="en-us" />
		<title><?php echo $search->page_title(); ?></title>
		
		<!-- CSS -->
		<link rel="stylesheet" href="<?php echo TEMPLATE_DIR; ?>master.css" type="text/css" media="screen" />
		<!--[if IE]>
			<link rel="stylesheet" href="<?php echo TEMPLATE_DIR; ?>ie.css" type="text/css" media="screen" />
		<![endif]-->
		<script type="text/javascript" src="<?php echo WEBROOT_DIR; ?>/js/jquery-1.3.2.min.js"></script>
		<script type="text/javascript" src="<?php echo WEBROOT_DIR; ?>/js/front.js"></script>
	</head>
	<body class="<?php if(!empty($_GET)) { echo 'resultspage'; } else { echo 'searchpage'; } ?>">
		
		<?php if(!empty($_GET)) { echo '<div id="container">'; } ?>
		
		<h1><?php if(!empty($_GET)) { echo Configure::read('mytitle'); } else { echo Configure::read('maintitle'); } ?></h1>
		
		<?php echo $search->form(); ?>
		
		<?php $search->do_it(); ?>
		
		<?php $search->show_categories(); ?>
		
		<p id="ignored-words"><?php echo $search->ignored_words(); ?></p>
		
		<p id="did-you-mean"><?php echo $search->did_you_mean(); ?></p>
		
		<p id="search-report"><?php echo $search->display_report(); ?></p>
		
		<div id="results">
			<?php echo $search->display_results(); ?>
		</div>
		
		<div id="other_pages">
			<?php echo $search->pagination(false); ?>
		</div>
		
		<?php
		// I added in if(empty($_GET)) - wlk
		if(empty($_GET)) {
			/*
			?>
			
			<h2 id="most_popular_header">
				<?php // echo $sph_messages['mostpop']; ?>
			</h2>
			
			<?php
			*/
			echo $search->show_popular_searches();
		}
		
		echo $search->add_new_url_link();
		
		if(!empty($_GET)) { echo '</div>'; }
		?>
	</body>
</html>