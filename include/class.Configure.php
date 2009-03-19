<?php

class Configure {
	
	static $values = array();
	
	function build() {
		$results = mysql_query("SELECT * FROM ".TABLE_PREFIX."configurations");
		
		while($row = mysql_fetch_array($results)) {
			switch($row['type']) {
				case 'numeric':
					$value = (int)$row['value'];
					break;
				case 'boolean':
					if($row['value']=='false') {
						$value = 0;
					} else if ($row['value']=='true') {
						$value = 1;
					}
					break;
				default:
					// treat as string
					$value = $row['value'];
			}
			
			Configure::$values[$row['slug']] = array(
				'name' => $row['name'],
				'value' => $value
			);
		}
	}
	
	function read($slug, $value=true, $name=false) {
		if(isset(Configure::$values[$slug]) && $value && $name) {
			return Configure::$values[$slug];
		} else if(isset(Configure::$values[$slug]['value']) && $value) {
			return Configure::$values[$slug]['value'];
		} else if(isset(Configure::$values[$slug]['name']) && $name) {
			return Configure::$values[$slug]['name'];
		}
	}
	
	function write($slug, $value, $name='') {
		Configure::$values[$slug] = array(
			'name' => $name,
			'value' => $value
		);
	}

}

?>