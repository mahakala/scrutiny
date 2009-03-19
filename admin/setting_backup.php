<?php
/****************************************************************************
This script handles the import / export and delete functions for the Admin settings.
Called by 'index.php' via f=41, the backup files are processed.
In order to create your individual default backup file, rename the appropriate file 
with your personal name conventions that must include 'default' as part of the name (without apostrophes).
*****************************************************************************/
        
    $source_file    = "../settings/conf.php";   //  source file to be copied
    $set_path       = "./settings/";            //  subfolder of .../admin/ where all the settings files will be stored 
    
    $now        = date("Y.m.d-H.i.s");          //  build current date and timestamp
    $filename   = "config_$now.php";     


    $files = array();
    $send2 = '';
    
	extract($_POST);
	extract($_REQUEST);

//      Headline for Settings Import
	echo "<div class='submenu cntr'>| Settings Import/Export Management |</div>
	<div class='tblhead'>
		<form name='setimport' id='setimport' method='post' action='index.php'>
		<dl class='tblhead'>
			";

//      List available URL files
	if (!is_dir($set_path)) {
		mkdir($set_path, 0766);
	}

	$bgcolor='odrow';
	$is_first=1;
	$files = scandir($set_path);

	if($is_first==1){
		echo "<dt class='headline x2 cntr'>Setting Files</dt>
			<dd class='headline cntr'>Manage</dd>
			";
	}

	$is_first=0;
	$count_confs = 0;
	foreach ($files as $confname) {
		if (eregi("_",$confname)) {                             //show only files with a  _ in its name
            $confname = str_replace(".php", "", $confname);     //  suppress suffix
            $count_confs++ ;
            echo "<dt class='$bgcolor x2' style='padding:9px;'>$confname</dt>
                <dd class='$bgcolor cntr'>
                <input class='sbmt' type='button' name='lrestore'
                onclick=\"confirm_rest_set('./index.php?f=41&amp;file=$confname&amp;del=0');\" value='Restore' 
                title='Beware! Once started, the current database will be modified!'
                />
            ";
            if (strpos($confname, "default")) {
                echo"<input class='sbmt' type='button' name='delete'
                    onclick=\"confirm_protected('./index.php?f=41&amp;file=$confname&amp;del=1');\" value=' --------- '
                    title='Original Sphider-plus file is undeletable'
                    />
                    </dd>
                ";
                } else {
                echo"<input class='sbmt' type='button' name='delete'
                    onclick=\"confirm_del_set('./index.php?f=41&amp;file=$confname&amp;del=1');\" value='Delete'
                    title='Click to delete this URL file'
                    />
                    </dd>
                ";
                }
                
            if ($bgcolor=='odrow') {
                $bgcolor='evrow';
            } else {
                $bgcolor='odrow';
            }
        }                        
	}
	
	if($count_confs == 0){
		echo "<dt class='odrow x2 cntr'><span class='warnadmin'>No Setting File Exist!</span></dt>
			<dd class='odrow cntr'>You should create a backup file soon!</dd>
		";
	} 
	echo "</dl>
        <br />
		<div class='panel cntr'>
        	<input type='hidden' name='f' value='41'/>
			<p class='evrow cntr sml'>Create a new Backup file from current settings<input class='sbmt' type='submit' name='send2' value='Create'
			title='Create a new Settings file from current conf.php file'/></p>
		</div>    
		</form>
	</div>
	";

//      Enter here to create a new Settings file
	if($send2 == "Create") {
        echo "<p class='headline x1 cntr'><span class='bd'><br />Creating . . .</span></p>
            ";         
        if (!is_dir($set_path)) {
            mkdir($set_path, 0766);
        }

        if (!$hd1 = fopen($source_file, "r")) {
            print "Unable to open $source_file (source file)";
            fclose($hd1);            
            exit;
        }
        
        $dest_file   = "$set_path$filename";
        if (!$hd2 = fopen($dest_file, "w")) {
            print "Unable to open $dest_file (destination file)";
            fclose($hd2);
            exit;
        }
        fclose($hd1);        
        fclose($hd2);        
        echo "<br /><p class='alert'><span class='em'>
            Starting to backup settings into file: $dest_file</p>
            <br />
        ";

        if (!copy($source_file,$dest_file)) {
            print "Unable to copy $source_file to $dest_file";
            exit;
        }
        echo "<p class='headline x1 cntr'><span class='bd'><br />Done</span></p>
            <div>
    		<p class='evrow cntr'>
            <br />
    		<a class='bkbtn' href='index.php?f=41' title='Go back to Settings Management'>Complete this process</a></p></div>
            </div>
            </div>
            </body>
            </html>         
        ";
		die ('');
	}

//      Enter here to restore conf.php file
	if (isset($file) && $del==0) {
      
        echo "<p class='headline x1 cntr'><span class='bd'><br />Restore from Backup file</span></p>
            ";         
        if (!is_dir($set_path)) {
            mkdir($set_path, 0766);
        }

        if (!$hd1 = fopen($source_file, "r")) {
            print "Unable to open $source_file (source file)";
            fclose($hd1);            
            exit;
        }
     
        $dest_file   = "$set_path$file.php";
        if (!$hd2 = fopen($dest_file, "r")) {
            print "Unable to open $dest_file (destination file)";
            fclose($hd2);
            exit;
        }
        fclose($hd1);        
        fclose($hd2);        
        echo "<br /><p class='alert'><span class='em'>
            Starting to backup settings to file: $dest_file</p>
            <br />
        ";

        if (!copy($dest_file, $source_file)) {
            print "Unable to copy $source_file to $dest_file";
            exit;
        }



		echo "<div>
    		<p class='evrow cntr'>
            <br />
    		<a class='bkbtn' href='index.php' title='Go back to Admin'>Complete this process</a></p></div>
            </div>
            </div>
            </body>
            </html>         
        ";
		die ('');        

	}

//      Enter here to delete Settings files 
	if (isset($file) && $del==1) {
        echo "<p class='headline x1 cntr'><span class='bd'><br />Deleting . . .</span></p>
            ";        
        if (is_dir($set_path)) {
            if ($dh = opendir($set_path)) {
                while (($this_file = readdir($dh)) !== false) {
                    if ($this_file == "$file.php") {                    
                        if (!strpos($this_file, "default")) {                      
                            @unlink("$set_path/$this_file");    //    delete this file 
                        }
                    }
                }
            closedir($dh);
            }
        }
        if (!strpos($file, "default")) {        
    		echo "<div class='cntr'>
             <body onload='JumpBottom()'>
        	 <p class='odrow bd cntr'>Settings File '$file' deleted.</p>
             <br />
             ";
        }
        echo "
    	 <p class='evrow cntr'>
         <br />
    	 <a class='bkbtn' href='index.php?f=41' title='Go back to Settings Management'>Complete this process</a></p></div>
        </div>
        </div>
        </body>
        </html>
        ";
		die ('');
	}
	
    die ('');
?>



