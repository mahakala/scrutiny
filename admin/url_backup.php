<?php
/*******************************************************
This script handles the import / export and delete functions for the URL list
Called by 'index.php' via f=40, the backup files are processed.
*******************************************************/
        
    $url_path   = "./urls/";            //subfolder of .../admin/ where all the url files will be stored 
    $now        = date("Y.m.d-H.i.s");
    $filename   = "urls_$now.txt";     
    $delim      = "|";                  //  define delimiter for file 'url.txt'

    $files = array();
    $send2 = '';
    
	extract($_POST);
	extract($_REQUEST);

//      Headline for URL Import
	echo "<div class='submenu cntr'>| URL Import/Export Management |</div>
	<div class='tblhead'>
		<form name='urlimport' id='urlimport' method='post' action='index.php'>
		<dl class='tblhead'>
			";

//      List available URL files
	if (!is_dir($url_path)) {
		mkdir($url_path, 0766);
	}

	$bgcolor='odrow';
	$is_first=1;
	$files = scandir($url_path);

	if($is_first==1){
		echo "<dt class='headline x2 cntr'>URL Files</dt>
			<dd class='headline cntr'>Manage</dd>
			";
	}

	$is_first=0;
	$count_urls = 0;
	foreach ($files as $urlname) {
		if (eregi("_",$urlname)) {                           //show only files with a  _ in its name
            $urlname = str_replace(".txt", "", $urlname);    //  suppress suffix
            $count_urls++ ;
            echo "<dt class='$bgcolor x2' style='padding:9px;'>$urlname</dt>
                <dd class='$bgcolor cntr'>
                <input class='sbmt' type='button' name='lrestore'
                onclick=\"confirm_rest_url('./index.php?f=40&amp;file=$urlname&amp;del=0');\" value='Restore' 
                title='Beware! Once started, the current database will be modified!'
                />
                <input class='sbmt' type='button' name='ldelete'
                onclick=\"confirm_del_url('./index.php?f=40&amp;file=$urlname&amp;del=1');\" value='Delete'
                title='Click to delete this URL file'
                />
                </dd>
            ";

            if ($bgcolor=='odrow') {
                $bgcolor='evrow';
            } else {
                $bgcolor='odrow';
            }
        }                        
	}
	
	if($count_urls == 0){
		echo "<dt class='odrow x2 cntr'><span class='warnadmin'>No URL File Exist!</span></dt>
			<dd class='odrow cntr'>You should create a file</dd>
		";
	} 
	echo "</dl>
        <br />
		<div class='panel cntr'>
        	<input type='hidden' name='f' value='40'/>
			<p class='evrow cntr sml'>Create a new URL file from current database<input class='sbmt' type='submit' name='send2' value='Create'
			title='Create a new URL file from current sites table'/></p>
		</div>    
		</form>
	</div>
	";

//      Enter here to create a new URL file
	if($send2 == "Create") {
        echo "<p class='headline x1 cntr'><span class='bd'><br />Export Url list</span></p>
            "; 

        $file   = "$url_path$filename";
        
        if (!is_dir($url_path)) {
            mkdir($url_path, 0766);
        }
                
        if (!$handle = fopen($file, "w")) {
            print "Unable to open $file (destination file)";
            exit;
        }
        echo "<br /><p class='alert'><span class='em'>
            Starting to export to file: $file</p>
        ";

        //      Get url and spider_depth from database 
        $result = mysql_query("select * from ".TABLE_PREFIX."sites order by url");
        if (DEBUG > '0') echo mysql_error();
        $rows = mysql_num_rows($result);
        for ($i=0; $i<$rows; $i++) 
            {
                $site_id        = (mysql_result($result, $i, "site_id"));
                $url            = (mysql_result($result, $i, "url"));
                $spider_depth   = (mysql_result($result, $i, "spider_depth"));
                $num = $i+1;
                if ($num & 1) {
                    echo "	<p class='odrow'>\n";
                } else {
                    echo "	<p class='evrow'>\n";
                }
                echo "
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$num. $url</p>
                ";                    
                echo ("");

                //      Search for possible category_id
                $res_id = mysql_query("select * from ".TABLE_PREFIX."site_category where site_id ='$site_id'");
                if (DEBUG > '0') echo mysql_error();
                $cat = mysql_fetch_array($res_id);
                $cat_id = $cat['category_id'];

                //      If exist, get name of category
                if ($cat_id != '0') {
                    $res_cat = mysql_query("select * from ".TABLE_PREFIX."categories where category_id ='$cat_id'");
                    if (DEBUG > '0') echo mysql_error();
                    $cat = mysql_fetch_array($res_cat);
                    $category = $cat['category'];
                    if ($category) $category = "$category,";
                }                    
                //      Now write all data to file                    
                if (!fwrite($handle, "$url$delim$spider_depth$delim$category\n")) {
                    print "Unable to write to $file";
                    exit;
                } 
            }

        //      Close file 
        fclose($handle);

		echo "<div>
    		<p class='evrow cntr'>
    		<a class='bkbtn' href='index.php?f=40' title='Go back to URL Management'>Complete this process</a></p></div>
            </div>
            </div>
            </body>
            </html>         
        ";
		die ('');
	}

//      Enter here to restore URL files into database
	if (isset($file) && $del==0) {
      
        $file   = "$url_path$file.txt";            
        $short_desc 		= '';
        $title 				= '';
        $required			= '';
        $disallowed			= '';
        $can_leave_domain	= '';
        $parent_num         = "0";
        $theFile 			= file_get_contents($file);
        $lines 				= array();
        $lines 				= explode("\n", $theFile);
      
        echo "<br /><p class='alert'><span class='em'>
            Starting to import:</p>
        ";
        
        $num = '1';			
        foreach ($lines as $new) {
            $new	= cleanup_text (nl2br(trim(substr ($new, 0,150))));
            //echo "<br>NEW:<br><pre>";print_r($new);echo "</pre>";
            if (strlen($new) > 10) {
                $new = explode($delim,$new);
                $url = $new[0];
                $spider_depth = $new[1];
                if ($spider_depth == ('')) $spider_depth = '-1';
                $category = $new[2];
                if (strlen($spider_depth) > '2') $category = $spider_depth;
                if ($num & 1) {
                    echo "	<p class='odrow'>\n";
                } else {
                    echo "	<p class='evrow'>\n";
                }
                echo "
                    $num. $url<br />
                ";

                //  clean url
                $compurl		= parse_url("".$url);
                if ($compurl['path']=='')
                    $url=$url."/";						
                $result = mysql_query("select site_ID from ".TABLE_PREFIX."sites where url='$url'");
                if (DEBUG > '0') echo mysql_error();
                $rows = mysql_num_rows($result);
                if ($rows===0 ) {
                    //  save new url and spider-depth
                    mysql_query("INSERT INTO ".TABLE_PREFIX."sites (url,spider_depth) VALUES ('$url', '$spider_depth')");
                    if (DEBUG > '0') echo mysql_error();
                    
                    //  handle the category if we do have one
                    if ($category != ('')) {
                        $result = mysql_query("select category from ".TABLE_PREFIX."categories where category='$category'");
                        if (DEBUG > '0') echo mysql_error();
                        $rows = mysql_num_rows($result);
                        if ($rows==0 ) {
                            // if new category
                            mysql_query("INSERT INTO ".TABLE_PREFIX."categories (category, parent_num) VALUE ('$category', '$parent_num')");
                            if (DEBUG > '0') echo mysql_error();
                        }
                        
                        //  get category_id
                        $result = mysql_query("select * from ".TABLE_PREFIX."categories where category='$category'");
                        if (DEBUG > '0') echo mysql_error();
                        $cat = mysql_fetch_array($result);
                        $cat_id = $cat['category_id'];
                        
                        //  get site_id
                        $result = mysql_query("select * from ".TABLE_PREFIX."sites where url='$url'");
                        if (DEBUG > '0') echo mysql_error();
                        $sit = mysql_fetch_array($result);
                        $site_id = $sit['site_id'];
                        
                        //  save new site_id and category_id
                        mysql_query("INSERT INTO ".TABLE_PREFIX."site_category (site_id, category_id) VALUES ('$site_id', '$cat_id')");
                        if (DEBUG > '0') echo mysql_error();
                        
                       //echo "<br>cat id:<br><pre>";print_r($cat);echo "</pre>"; 
                    }
                                           
                } else 	{
                    echo "	
                        <span class='warnadmin'>
                        Attention: Site is already in database. Currently not imported a second time.</span>
                    ";
                    }
                echo "</p>
                ";
            }
            $num++ ;            
        }

		echo "<div>
    		<p class='evrow cntr'>
    		<a class='bkbtn' href='index.php?f=40' title='Go back to URL Management'>Complete this process</a></p></div>
            </div>
            </div>
            </body>
            </html>         
        ";
		die ('');        

	}

//      Enter here to delete URL files 
	if (isset($file) && $del==1) {
        if (is_dir($url_path)) {
            if ($dh = opendir($url_path)) {
                while (($this_file = readdir($dh)) !== false) {
                    if ($this_file == "$file.txt") {
                        @unlink("$url_path/$this_file");    //    delete this file                      
                    }
                }
            closedir($dh);
            }
        }

		echo "<div class='cntr'>
         <body onload='JumpBottom()'>
    	 <p class='odrow bd cntr'>URL File '$file' deleted.</p>
    	 <p class='evrow cntr'>
    	 <a class='bkbtn' href='index.php?f=40' title='Go back to URL Management'>Complete this process</a></p></div>
        </div>
        </div>
        </body>
        </html>
        ";
		die ('');
	}
	
    die ('');
?>



