<?php
	if(strpos($_SERVER['REQUEST_URI'], basename($_SERVER['SCRIPT_FILENAME']))!==false) {
		header('Location: /admin/');
		exit();
	}

	error_reporting (E_ALL ^ E_NOTICE ^ E_WARNING);
	extract(getHttpVars());
	include("db_backup.php");

	$backup_path = "./backup/"; //subfolder of .../admin/ where all the backups will be stored
	$delimiter = ";#%%";        //delimiter for sql insert

	if (!get_extension_funcs('zlib')) {
		echo "<p class='warn'>Compression module status notice:
			<font color='red'>Zlib is NOT installed on the server! Backup disabled!</p>
	";
	}

//      Headline for BACKUP MANAGEMENT
	echo "<div class='submenu dbsidemenu cntr'>
		<ul>
			<li>Backup Management for Database:&nbsp;".$db->default['database']."</li>
		</ul>
		<form name='dobackup' id='dbform1' method='post' action='".WEBROOT_DIR."/admin/'>
		<dl class='tblhead'>
			";

//      List available backup files
	if (!is_dir($backup_path)) {
		mkdir($backup_path, 0766);
	}

	$bgcolor='odrow';
	$is_first=1;
	$folder = scandir($backup_path);

	// if($is_first==1){
	// 	echo "<dt class='headline x2 cntr'>Backup Files</dt>
	// 		<dd class='headline cntr'>Manage</dd>
	// 		";
	// }

	$is_first=0;
	$count_backup = 0;
	foreach ($folder as $backname) {
		if (eregi("__",$backname)) {    //show only folder with two _ in its name 
        
            $han = opendir("$backup_path$backname");
            $fcount = '0';
            while (false !== ($backfiles = readdir($han))) {
                $fcount++;
            }
            closedir($han);
            
            if ($fcount > '2') {        //show all folder that are not empty              
    			$count_backup++ ;
    			echo "<dt class='$bgcolor x2 bkupcomplicated' style='padding:9px;'>";
				$r = explode('_', $backname);
				echo $r[0]. ' <span>('.$r[3].')</span>';
				echo "</dt>
        			<dd class='$bgcolor cntr'>
        			<input class='sbmt' type='button' name='lrestore'
        			onclick=\"confirm_rest_prompt('./index.php?f=database&amp;file=$backname&amp;del=0');\" value='Restore' title='Beware! Once started, the database restore could take some while to complete!' />
        			<input class='sbmt' type='button' name='ldelete' onclick=\"confirm_del_prompt('./index.php?f=database&amp;file=$backname&amp;del=1');\" value='Delete' title='Click to Permanently Delete database backup' />
        			</dd>
        		";

    			if ($bgcolor=='odrow') {
    				$bgcolor='evrow';
    			} else {
    				$bgcolor='odrow';
    			}
            }                        
		}
	}
	
	if($count_backup == 0){
		echo "<dt class='odrow x2 cntr'><span class='warnadmin'>No Backup Files Exist!</span></dt>
			<dd class='odrow cntr'>Create them soon!</dd>
		";
	} 
	echo "</dl>
			<p style='border-top:1px solid #efefef;' class='evrow'>Create Backup:<br />
				<input type='hidden' name='f' value='database' />
				<input type='checkbox' name='structonly' value='Yes' /> Structure only<br />
				<input class='sbmt' type='submit' name='send2' value='Backup' title='Beware! Once started, the database backup could take some while to complete!' />
			</p>
		</div>
		</form>
	</div>
	<div class='panel x2 cntr'>
	";

//      Enter here to backup current db
	if($send2 == "Backup") {
		$starttime = time();

		if ($structonly == "Yes") {
			$folder_name = date("Y-m-d__H-i")."_structure-only"; //create foldername for backup files from current date and time
		} else {
			$folder_name = date("Y-m-d__H-i")."_structure-plus-data";
		}
		if (!is_dir($backup_path)) mkdir($backup_path, 0777); //if not exist, create folder for backup

		$path = "$backup_path$folder_name"; 
		if (!is_dir("$path")) mkdir("$path", 0777); //create individual sub-folder for backup-files 
		optimize($db->default['database']);//      before backup, preventively repair and optimize current database    

		$header = "-- ------------------------------------------------------------ \n".
		"-- \n". 
		"-- Backup from Sphider database\n".
		"-- Creation date: ".date("d-M-Y H:i",time())."\n".
		"-- Database: ".$db->default['database']."\n".
		"-- MySQL Server version: ".mysql_get_server_info()."\n".
		"-- \n".		
		"-- ------------------------------------------------------------ \n\n" ;

		$result = mysql_db_query($db->default['database'], "SHOW TABLES FROM ".$db->default['database']) or die("Database ".$db->default['database']." not existing.");
		while($row = mysql_fetch_array($result)) {   
			$tab    = "$row[0]";             //name of actual table    
			$back   = ("$path/$tab.sql.gz"); //create path, filename and suffix for this backup-file
			$fp     = gzopen ("$back","w");
				
			gzwrite ($fp,$header);           //write header into backup-file    
			get_def($db->default['database'],$tab,$fp);     //get structure of this table
			if (!isset($structonly) || $structonly!="Yes") {
				get_content($db->default['database'],$tab,$fp); //get content of this table
			}
			if ($structonly =="Yes"){        //print warning into backup file
				$struct_warn = "\n\n-- During backup 'structure only' was selected.\n\n";
				gzwrite ($fp,$struct_warn);
			}
			gzwrite ($fp,"--  Valid end of table: '$tab'\n");
			gzclose ($fp);
		}
		mysql_free_result($result);
		$endtime = time();
		$consum = ($endtime-$starttime);
		echo "<body onload='JumpBottom()'>
        <p class='warnok bd cntr'>Backup of current database, ".$db->default['database'].", done in $consum
		";
		if ($consum == 1) {
			echo " second.</p>
		";
		} else {
			echo " seconds.</p>
		";
		}
		
		if ($structonly == "Yes") {
			echo "<p class='odrow cntr'>
				Keep in mind that only database structure has been stored in this backup file.</p>
		";
		}

		echo "<div>
    		<p class='evrow cntr'>
    		<a class='bkbtn' href='index.php?f=database' title='Go back to Database'>Complete this process</a></p></div>
            </div>
            </div>
            </body>
            </html>         
        ";
		die ('');
	}

//      Enter here to restore backup files into database
	if (isset($file) && $del==0) {      //first check for too large backup-files
		$starttime = time();   
		if (eregi("__",$file)) {
			$dir = ("$backup_path$file"); //folder with backup-files to be restored in database
		
			if ($dh = opendir($dir)) {  
				$delilength = strlen($delimiter);
				while (($dbfile = readdir($dh)) !== false) {  
					if (eregi("\.gz$", $dbfile)) {
						$zp = @gzopen("$dir/$dbfile", "rb"); //open backup-file of one table
						if(!$zp) {
                            die("Cannot read backup-file: $dbfile");
						}
						flush();                //clear buffer
						set_time_limit(1800);   //the rest of this backup-file should be done in 30 minutes (increase timeout)
						$temp= '';

						while(!gzeof($zp)){
							$temp=$temp.gzgets($zp, '8192');            //get  one row from current backup-file
							if ($endoff = strpos($temp, $delimiter)) {  //find end of sql insert 
								$temp = substr($temp, 0, $endoff);       //delete delimiter
								$temp = str_replace("\n\n","\n", $temp); //delete blank rows
								$query = substr($temp, 0, $endoff);      //this part of tempfile is the current query  
								mysql_db_query($db->default['database'],$query) or die(mysql_error()); //insert into table 
								$temp = '';
							}
						}
						gzclose($zp);     
					}
				}
				$endtime = time();
				$consum = ($endtime-$starttime);
				echo " <p class='odrow cntr'>
                 <body onload='JumpBottom()'>
            	 Your restore request was processed in $consum seconds.<br />
            	 If you did not receive any errors on the screen,<br />
            	 then you should find that your database tables have been restored.</p>
                 <p class='evrow cntr'>
                 <a class='bkbtn' href='index.php?f=database' title='Go back to Database'>Complete this process</a></p>                 
                ";

            } else {
                echo " <p class='odrow cntr'>
                 <body onload='JumpBottom()'>
                 <span class='warn bd'>Invalid folder for Backup-files selected ! <br />
                 '$dir' does not exist.</span></p>
                ";
            }
        } else {
            echo "<p class='odrow cntr'>
             <body onload='JumpBottom()'> 
             <span class='warn bd'>Invalid Backup '$file' selected.</span></p>
            ";
        }
        echo"</div>
        </div>
        </body>
        </html>
        ";
        die ('');
	}

//      Enter here to delete backup files 
	if (isset($file) && $del==1) {
		$dir = ("$backup_path$file"); //backup folder to be deleted
			if (is_dir($dir)) {
				if ($dh = opendir($dir)) {
					while (($dbfile = readdir($dh)) !== false) {
						//echo "Deleted file: $dbfile <br />";
						@unlink("$dir/$dbfile");    //first delete all files in folder
					}
				closedir($dh);
				}
			}
		rmdir($dir); //  now delete empty backup folder

		echo "<div class='cntr'>
         <body onload='JumpBottom()'>
    	 <p class='odrow bd cntr'>Backup File '$file' deleted.</p>
    	 <p class='evrow cntr'>
    	 <a class='bkbtn' href='index.php?f=database' title='Go back to Database'>Complete this process</a></p></div>
        </div>
        </div>
        </body>
        </html>
        ";
		die ('');
	}

	// List current database tables 
	$pstr = TABLE_PREFIX;
	
	if(!empty($pstr)) {
		$stats  = mysql_query("SHOW TABLE STATUS FROM ".$db->default['database']." LIKE '".TABLE_PREFIX."%'");
	} else {
		$stats = mysql_query("SHOW TABLE STATUS FROM ".$db->default['database']);
	}
	$num_tables = mysql_num_rows($stats);
	if ($num_tables != 0) {
		echo "<br />
    	<table width='98%'>
    	<tr>
    		<td class='headline' colspan='6'>
    		<div class='headline cntr'>Main Table Overview: <span class='odrow'>".$db->default['database']."</span> </div>
    		</td>
    	</tr>
    	<tr>
    		<td width='20%' class='tblhead'>Table</td>
    		<td width='20%' class='tblhead'>Rows</td>
    		<td width='25%' class='tblhead'>Created on</td>
    		<td width='15%' class='tblhead'>Data Size kB</td>
    		<td width='19%' class='tblhead'>Index Size kB</td>
    	</tr>
    	";

		$bgcolor='odrow';
		$i=0;
		while ($rows=mysql_fetch_array($stats) ) {
			echo "<tr class='$bgcolor cntr'>
                <td>".$rows["Name"]."</td>
                <td >".$rows['Rows']."</td>
                <td>".$rows['Create_time']."</td>
                <td>".number_format($rows['Data_length']/1024,1)."</td>
                <td>".number_format($rows['Index_length']/1024,1)."</td>
            </tr>
            ";
			$i++;
			if ($bgcolor=='odrow') {
				$bgcolor='evrow';
			} else {
				$bgcolor='odrow';
			}
		}
		echo "</table>
    	<form name='optimize' id='dbform2' method='post' action='".WEBROOT_DIR."/admin/'>
    		<p class='tblhead'>
    		<input type='hidden' name='f' value='database'
    		/>
    		<span class='bd'>Repair and optimize current database</span>
    		<input class='sbmt' type='submit' name='send2' value='Optimize' title='Attempt to minimize database'
    		/>
    		</p>
    	</form>
    	";

	} else {
		echo "<p class='odrow cntr'>
            <span class='warn bd'>Warning: Database contains no tables</p>
    	";
	}

//Enter here to repair and optimize database 
	if($send2=="Optimize"){
		optimize($db->default['database']);    
		echo "<p class='odrow cntr'>
            <span class='bd'>Completed!</span> $i tables processed.<br />
            Current database, ".$db->default['database'].", repaired and optimized.</p>
    	";
	}

?>