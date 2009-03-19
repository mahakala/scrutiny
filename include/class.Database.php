<?php

	class DATABASE {
		
		function __construct() {
			global $database_configuration;
			
			$host_r = explode('.', $_SERVER['SERVER_NAME']);
			if(count($host_r)>2)
				while(count($host_r)>2)array_shift($host_r);
			$mainhost = implode('.', $host_r);
			
			foreach($database_configuration as $db) {
				if($db['domain'] == $mainhost) {
					$this->default = $db;
					if (!defined('TABLE_PREFIX')) {
						define('TABLE_PREFIX', $this->default['prefix']);
					}
				}
			}
			
			if(!isset($this->default)) {
				$this->default = $database_configuration[0];
				if (!defined('TABLE_PREFIX')) {
					define('TABLE_PREFIX', $this->default['prefix']);
				}
			}
			
			$success = mysql_pconnect($this->default['host'], $this->default['user'], $this->default['password']);
			if (!$success)
				die ('<br />Cannot connect to database, check if username, password and host are correct.<br />' . mysql_error());
			$success = mysql_select_db($this->default['database'], $success);
			if (!$success) {
				die ('<br />Cannot choose database, check if database name is correct:<br />'  . mysql_error());
			}
		}
		
		function DATABASE() {
			$this->__construct();
		}
		
	}

?>