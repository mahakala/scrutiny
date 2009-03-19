<?php
	if(strpos($_SERVER['REQUEST_URI'], basename($_SERVER['SCRIPT_FILENAME']))!==false) {
		header('Location: /admin/');
		exit();
	}
	
	function get_def($database,$table,$fp) {        //      get content of structure
		global $delimiter;    
		$def = "";

		$def .= "DROP TABLE IF EXISTS $table$delimiter\n";    
		$def .= "CREATE TABLE IF NOT EXISTS $table (\n";
		$result = mysql_db_query($database, "SHOW FIELDS FROM $table") or die("Table $table not existing in database");
		while($row = mysql_fetch_array($result)) {
			$def .= "    $row[Field] $row[Type]";   //      prepare all structur commands for later SQL-restore
			if ($row["Default"] != "" && $row["Default"] != "CURRENT_TIMESTAMP") $def .= " DEFAULT '$row[Default]'";
			if ($row["Default"] == "CURRENT_TIMESTAMP") $def .= " NOT NULL default $row[Default] on update CURRENT_TIMESTAMP";
			if ($row["Null"] != "YES" && $row["Default"] != "CURRENT_TIMESTAMP") $def .= " NOT NULL"; 
			if ($row["Extra"] != "") $def .= " $row[Extra]";
			$def .= ",\n"; 
		}
		$def = ereg_replace(",\n$","", $def);
		$result = mysql_db_query($database, "SHOW KEYS FROM $table");
		
		while($row = mysql_fetch_array($result)) {
			$kname=$row["Key_name"];           
			if(($kname != "PRIMARY") && ($row["Non_unique"] == 0)) $kname="UNIQUE|$kname";      //  define attributes
			if(!isset($index[$kname])) $index[$kname] = array();
			$index[$kname][] = $row["Column_name"];
		}
		while(list($x, $columns) = @each($index)) {     //      now build one insert row
			$def .= ",\n";
			if($x == "PRIMARY") $def .= "   PRIMARY KEY (" . implode($columns, ", ") . ")";
			else if (substr($x,0,6) == "UNIQUE") $def .= "   UNIQUE ".substr($x,7)." (" . implode($columns, ", ") . ")";
			else $def .= "   KEY $x (" . implode($columns, ", ") . ")";
		}
		$def .= "\n)$delimiter\n\n";      //      create row delimiter
		$def=stripslashes($def);
		gzwrite ($fp,$def);         //      now write all prepared structur commands into backup file
	}

    
    function get_content($database,$table,$fp) {        //      get content of data
        global $delimiter;
        
    	$result = mysql_db_query($database, "SELECT * FROM $table") or die("Cannot get content of table");
    	while($row = mysql_fetch_row($result)) {
    		$insert = "INSERT INTO $table VALUES (";    //      command for later SQL-restore
    		for($j=0; $j<mysql_num_fields($result);$j++) {//      content for later SQL-restore
    			if(!isset($row[$j])) $insert .= "NULL,";
    			elseif(isset($row[$j])) $insert .= "'".addslashes($row[$j])."',";
    			else $insert .= "'',";
    		}
    		$insert  = ereg_replace(",$","",$insert);
    		$insert .= ")$delimiter\n";       //      create row delimiter
    		gzwrite ($fp,$insert);      //      now write the complete content into backup file
    	}
    	gzwrite ($fp,"\n\n");
        mysql_free_result($result);
    }
    
    
    function optimize($database) {
        $result = mysql_query("SHOW TABLE STATUS LIKE 'TABLE_PREFIX%'");
        if (DEBUG > '0') echo mysql_error();        
        set_time_limit(1800);   //      increase timeout                                 
       	$i = 0;         
        while ($row = mysql_fetch_array($result)) {         
            mysql_query("CHECK TABLE $row[0]") or die("<body onload='JumpBottom()'><br /><center><span class='warn bd'>Unable to check table '$row[0]'.</span><br /><br /></center>\n</body>\n</html>");                      
            mysql_query("REPAIR TABLE $row[0]") or die("<body onload='JumpBottom()'><br /><center><span class='warn bd'>Unable to repair table '$row[0]'.</span><br /><br /></center>\n</body>\n</html>");            
            mysql_query("OPTIMIZE TABLE $row[0]") or die("<body onload='JumpBottom()'><br /><center><span class='warn bd'>Unable to optimize table '$row[0]'.</span><br /><br /></center>\n</body>\n</html>");
            //  DO NOT USE THE NEXT ROW ON SHARED HOSTING SYSTEMS ! ! !   'flush table' could be forbidden.
            mysql_query("FLUSH TABLE $row[0]") or die("<body onload='JumpBottom()'><br /><center><span class='warn bd'>Unable to flush table '$row[0]'.</span><br /><br /></center>\n</body>\n</html>");                		
    		$i++;
    	}
        mysql_free_result($result);        
        return($i);        
    }   
    
?>