<?php
    $starttime  = getmicrotime();   
    $query      = strtolower($query);
    $pos        = strpos($query,":");
    $urlquery   = strip_tags(trim(substr($query,$pos+1)));

    //      Search for URLs that were already indexed.
    $res=mysql_query("select * from ".TABLE_PREFIX."sites where url like '%$urlquery%' AND indexdate != ''"); 
    if (DEBUG > '0') echo mysql_error(); 
    $num_rows = mysql_num_rows($res);
      
    if ($num_rows == 0) {   //      Nothing found
    $noMatch = str_replace ('%query', $urlquery, $sph_messages["noSiteMatch"]);
        echo "
            <br>
            <div class='tblhead red'>
            $noMatch
            </div>
            </body>
            </html>            
        ";            
        die('');
    }    
    if ($num_rows > '1') {  //      Multiple choice
        echo "
            <div class='panel'>
            <table width='100%'>
            <div class='tblhead red'>
            ".$sph_messages['mulChoice']."
            </div>
        ";
        
		$class = "evrow";        
        for ($i=0; $i<$num_rows; $i++) {
            $url2           = mysql_result($res, $i, "url");
            $indexdate      = mysql_result($res, $i, "indexdate");
            $num = $i+1;
            
            echo "
                <tr class='$class'>
                <td>
                $num.
                </td>
                <td>
                <a href='./index.php?query=site:$url2&search=1'> $url2 </a>
                </td>
                <td>                    
                indexed: $indexdate
            ";
            
            if(ceil($num/10) == $num/10) {      // This routine places a "to page top" link on every 10th record
                echo "<a class='navup' href='#top' title='Jump to Page Top'>Top</a>
                ";
            }
            
            echo "                         
                </td>
                </tr>
            ";
            
			if ($class =="evrow") {
				$class = "odrow";
			}else{ 
				$class = "evrow";
			}            
        }
        
        echo "
            </table>
            </div>
            </body>
            </html>
        ";
        die('');    
    }   
       
    //      Get all links of this URL.
    $site_id  = mysql_result($res,"site_id");    
    $res=mysql_query("select * from ".TABLE_PREFIX."links where site_id = '$site_id'"); 
    if (DEBUG > '0') echo mysql_error(); 
    $num_rows = mysql_num_rows($res);       
    if ($num_rows == 0) {   //      No links found
        echo "<br />
            <div class='warn cntr'>             
            ".$sph_messages['noLinks']."
            </div>         
        ";            
    }             
 
    //Prepare header and all results for listing         
    $pages = ceil($num_rows / Configure::read('results_per_page'));   // Calculate count of required pages 
    
    if (empty($start_links)) $start_links = '1';    // As $start_links is not jet defined this is required for the first result page 
    if ($start_links == '1') { 
        $from = '0';                                // Also for first page in order not to multipy with 0 
    }else{ 
        $from = ($start_links-1) * Configure::read('results_per_page');   // First $num_row of actual page 
    } 

    $to = $num_rows;                                // Last $num_row of actual page 
    $rest = $num_rows - $start_links; 
    if ($num_rows > Configure::read('results_per_page')) {            // Display more then one page? 
        $rest = $num_rows - $from; 
        $to = $from + $rest;                        // $to for last page 
        if ($rest > Configure::read('results_per_page')) {
            $to = $from + (Configure::read('results_per_page'));      // Calculate $num_row of actual page 
        }
    }
    
    if ($num_rows > 0) {    //      Display header and results
        $endtime = getmicrotime() - $starttime;
        $time = round($endtime*100)/100;    
        echo "<br />
            <div id =\"result_report\">
            <font color=\"red\">
            ".$sph_messages['LinkSearch']."
            </font>
            <br />
            ".$sph_messages['Resfor']." \"$urlquery\" 
            <br />
            ($time ".$sph_messages['seconds'].")
            </div>
        ";
 
        $fromm = $from+1; 
                        
        echo "<div class='panel'>
            <table width='100%'>
        ";
        if ($pages > 1) {
        
        echo "<div class='tblhead'>".$sph_messages['matches']." $fromm - $to&nbsp;&nbsp;".$sph_messages['from']." $num_rows</div>
        ";
        }
        
		$class = "evrow";       
        for ($i=$from; $i<$to; $i++) {     //      get all results and show them             
            $url2           = mysql_result($res, $i, "url");
            $title          = mysql_result($res, $i, "title");
            $description    = mysql_result($res, $i, "description");
            $page_size      = mysql_result($res, $i, "size");
            $num = $i+1;
            
            if ($num == 1){            
            echo "
                <tr class='$class bd'>
            ";
            } else {
                echo "
                    <tr class='$class '>
                ";
            }
            echo "          
                <td>
                $num. <a href='$url2' target='_blank' title='Open Link in a new window'>$title 
            ";
            if (!$title) {
                echo "
                    ".$sph_messages['notitle']."
                ";
            }
            echo "
                </a>
                <br />
                $description
            ";
            if (!$description) {
                echo "
                    ".$sph_messages['nodes']."
                ";
            }
            echo "
                <br />
                $url2 &nbsp;&nbsp;($page_size kB)
            ";

            if ($num == 1) {
                echo "&nbsp;&nbsp;&nbsp;&nbsp;
                    ".$sph_messages['MainURL']."
                ";    
            }
            
            if(ceil($num/10) == $num/10) {      // This routine places a "to page top" link on every 10th record
                echo "<a class='navup' href='#top' title='Jump to Page Top'>Top</a>
                ";
            }
            if ($num_rows == 1) {   //      No links found
                echo "<br />
                    <div class='warn cntr'>             
                    ".$sph_messages['noLinks']."
                    </div>         
                ";            
            }             
             echo "             
                </tr>
            ";
                    
			if ($class =="evrow") {
				$class = "odrow";
			}else{ 
				$class = "evrow";
			}               
        }
    }

     // Display end of table 
    if ($num_rows > 0) { 
        echo "</table>
            </div>
        "; 

        if ($pages > 1) { // If we have more than 1 result-page 
            echo " 
                <div class='submenu cntr'>
                ".$sph_messages['Result page'].": $start_links ".$sph_messages['from']." $pages
                &nbsp;&nbsp;&nbsp;
            ";
            
            if($start_links > 1) { // Display 'First'            
                echo "
                    <a href='index.php?query=$query&start_links=1&search=1'>".$sph_messages['First']."</a>&nbsp;&nbsp;
                ";
                
                if ($start_links > 5 ) { // Display '-5' 
                $minus = $start_links-5;
                echo " 
                    <a href='index.php?query=$query&start_links=$minus&search=1'>- 5</a>&nbsp;&nbsp; 
                "; 
                } 
            } 
            if($start_links > 1) { // Display 'Previous' 
                $prev = $start_links-1;
                echo " 
                    <a href='index.php?query=$query&start_links=$prev&search=1'>".$sph_messages['Previous']."</a>&nbsp;&nbsp; 
                ";
            } 
            if($rest >= Configure::read('results_per_page')) { // Display 'Next'
                $inc = $start_links+1;               
                echo " 
                    <a href='index.php?query=$query&start_links=$inc&search=1' >".$sph_messages['Next']."</a>&nbsp;&nbsp; 
                ";
                
                if ($pages-$start_links > 5 ) { // Display '+5' 
                    $plus = $start_links+5;
                    echo " 
                     <a href='index.php?query=$query&start_links=$plus&search=1'>+ 5</a>&nbsp;&nbsp; 
                    "; 
                } 
            } 
            if($start_links < $pages) { // Display 'Last' 
            echo " 
                 <a href='index.php?query=$query&start_links=$pages&search=1'>".$sph_messages['Last']."</a> 
                "; 
            }
            echo "</div>
            ";
        }
    }      

// The following should only be removed if you contribute to the Sphider project..
// Note that this is a requirement under the GPL licensing agreement, which Sphider-plus acknowledges.	    

    echo "<p class='stats'>
        <a href='https://sourceforge.net/project/showfiles.php?group_id=214642' title='Link: Visit Sphider-plus site in new window' target='rel'>Visit
        <img class='mid' src='sphider-logo.png' alt='Visit Sphider site in new window' height='15' width='80'
        /> -plus
        </a>                
        </p>
        </div>
        </body>
        </html>        
    ";

    die ('') //     wait for next query   

?>
