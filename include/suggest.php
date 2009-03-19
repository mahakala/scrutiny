<?php
/*******************************************************
If 'Suggestion' is enabled (Configure::read('min_sug_chars') > 0), 
this script takes over to fetch the suggestion from Sphider-plus database.
Query input will be delivered via the JavaScript file 'scriptaculous.js' .
Suggestions will  be placed into <div id='auto_suggest'  /> by AJAX functionality,
performed again via the JavaScript file 'scriptaculous.js' .
***********************************************************/

    error_reporting(0);  //     suppress  PHP messages  
    $settings_dir   = "../settings"; 
    $lang_dir   = "../languages";

    include "$settings_dir/conf.php"; 
    include "$lang_dir/".Configure::read('language')."-language.php"; 
    
    // try to get the currently valid language
    if (Configure::read('auto_lng') == 1) {   //  if enabled in Admin settings get country code                
        if ( isset ( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) { 
            $cc = substr( htmlspecialchars($_SERVER['HTTP_ACCEPT_LANGUAGE']), 0, 2);            
            $handle = @fopen ("$lang_dir/$cc-language.php","r"); 
            if ($handle) { 
                Configure::write('language', $cc); // if available set language to users slang               
                include "$lang_dir/".Configure::read('language')."-language.php"; 
                @fclose($handle); 
            }
        }         
    } 
    
    $keyword = $_POST['query'];    // retrieve the keyword passed by AJAX framework
       
    if (get_magic_quotes_gpc()==1) {
        $keyword = stripslashes($keyword);
    } 

    if (Configure::read('utf8') == '1') {   
        $keyword = addslashes(($keyword));
       
        if (Configure::read('case_sensitive') == 0) {
            $keyword = strtr($keyword, "ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÜ",
                  "abcdefghijklmnopqrstuvwxyzäöü");
        }
    } else {
        $keyword = utf8_decode(addslashes(strtr($keyword, "ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÜ",
                  "abcdefghijklmnopqrstuvwxyzäöü")));
    }
       
    if (strlen($keyword)< Configure::read('min_sug_chars')) {                 //  if search string too small, do not search for keywords/phrases
        Configure::write('suggest_phrases', false);
        Configure::write('suggest_keywords', false);
    }
    
    $keyword = str_replace ("%20", " ", $keyword);          //  replace 'blank'

    if (!strpos($keyword,' ')) {                            //check if search string is phrase
        Configure::write('suggest_phrases', false);
    }
    
    $keyword = preg_replace("/!|\"|\*/", "",$keyword);      //     remove control character
                     
    if(strlen($keyword) >= Configure::read('min_sug_chars')) {                // build the SQL query that gets the suggestions from the database 
        if (Configure::read('suggest_history') && $keyword!='"') {            //  searches from saved queries (query_log table)
            $result = mysql_query($sql = "
            SELECT 	query as keyword, max(results) as results
            FROM {TABLE_PREFIX}query_log 
            WHERE results > 0 AND (query LIKE '$keyword%' OR query LIKE '\"$keyword%') 
            GROUP BY query ORDER BY results DESC
            LIMIT ".Configure::read('suggest_rows')."
            ");
            if($result && mysql_num_rows($result))
            {
                while($row = mysql_fetch_array($result))
                {
                    $values[$row['keyword']] = $row['results'];
                }    
            }
        }
      
        if (Configure::read('suggest_phrases')) {        //      for phrase search enter here
            $_words = substr_count($keyword,' ') + 1; 
         
            if (Configure::read('utf8') == '0') {
                $result = mysql_query($sql = "
                SELECT count(link_id) as results, SUBSTRING_INDEX(SUBSTRING(fulltxt,LOCATE('$keyword',LOWER(fulltxt))), ' ', '$_words') as keyword FROM {TABLE_PREFIX}links where LOWER(fulltxt) like '%$keyword%' 
                GROUP BY SUBSTRING_INDEX( SUBSTRING( LOWER(fulltxt), LOCATE( '$keyword', LOWER(fulltxt) ) ) , ' ', '$_words' ) LIMIT ".Configure::read('suggest_rows')."
                ");       
            }
            
            if (Configure::read('utf8') == '1' && Configure::read('case_sensitive') == '0') {
                $result = mysql_query($sql = "
                SELECT count(link_id) as results, SUBSTRING_INDEX(SUBSTRING(fulltxt,LOCATE('$keyword',CONVERT(LOWER(fulltxt)USING utf8))), ' ', '$_words') as keyword FROM {TABLE_PREFIX}links where CONVERT(LOWER(fulltxt)USING utf8) like '%$keyword%' 
                GROUP BY SUBSTRING_INDEX( SUBSTRING( CONVERT(LOWER(fulltxt)USING utf8), LOCATE( '$keyword', CONVERT(LOWER(fulltxt)USING utf8) ) ) , ' ', '$_words' ) LIMIT ".Configure::read('suggest_rows')."
                ");       
            }
            
            if (Configure::read('utf8') == '1' && Configure::read('case_sensitive') == '1') {               
                $result = mysql_query($sql = "
                SELECT count(link_id) as results, SUBSTRING_INDEX(SUBSTRING(fulltxt,LOCATE('$keyword',CONVERT((fulltxt)USING utf8))), ' ', '$_words') as keyword FROM {TABLE_PREFIX}links where CONVERT((fulltxt)USING utf8) like '%$keyword%' 
                GROUP BY SUBSTRING_INDEX( SUBSTRING( CONVERT((fulltxt)USING utf8), LOCATE( '$keyword', CONVERT((fulltxt)USING utf8) ) ) , ' ', '$_words' ) LIMIT ".Configure::read('suggest_rows')."
                ");
            }
      
            if($result && mysql_num_rows($result)) {
                while($row = mysql_fetch_array($result))
                {
                    $values[$row['keyword']] = $row['results'];
                }    
            }
      
        } elseif (Configure::read('suggest_keywords')) {        //  for single keyword search  enter here 
            for ($i=0;$i<=15; $i++) {
                $char = dechex($i);
                $result = mysql_query($sql = "
                SELECT keyword, count(keyword) as results 
                FROM {TABLE_PREFIX}keywords INNER JOIN {TABLE_PREFIX}link_keyword$char USING (keyword_id) 
                WHERE keyword LIKE '$keyword%'  
                GROUP BY keyword 
                ORDER BY results desc
                LIMIT ".Configure::read('suggest_rows')."
                ");
                if($result && mysql_num_rows($result)) {		
                    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                        $values[$row['keyword']] = $row['results'];
                    }    
                }
            }
        }      
    }
    
    if (Configure::read('min_sug_chars') != '0' && is_array($values)) {        // if we have results, loop through them and add them to the output . Max. = Configure::read('suggest_rows')    
        arsort($values);
        $sug_array = array_slice($values, 0, Configure::read('suggest_rows'));  
        echo '<ul>';        //  send 'start' tag
        
        foreach ($sug_array as $_key => $_val) {
            if ($_val > 1) {
                $count = $sph_messages["matches"];
                if (Configure::read('suggest_phrases')) {
                    $phr = $sph_messages["phraseSearch"];
                    $count = "$count, $phr";      // display "results from phrase search"
                }
            } else {
                $count = $sph_messages["match"];
                if (Configure::read('suggest_phrases')) {
                    $phr = $sph_messages["phraseSearch"];
                    $count = "$count, $phr";     // display "results from phrase search"
                }
            }
         
            if (Configure::read('utf8') == '1') {     //  build suggestion content and send it
                if (Configure::read('show_hits') == '1') {
                    echo '<li><b>'. $_key .'</b><span class="informal"><small>&nbsp;&nbsp;&nbsp;('. $_val .' '.$count.')</small></span></li>';
                } else {
                    echo '<li><b>'. $_key .'</b></li>';
                }
            } else {
                if (Configure::read('show_hits') == '1') {            
                    echo '<li><b>'. utf8_encode($_key) .'</b><span class="informal"><small>&nbsp;&nbsp;&nbsp;('. $_val .' '.$count.')</small></span></li>';
                } else {
                    echo '<li><b>'. utf8_encode($_key) .'</b></li>';
                }
            }
        }
        echo '</ul>';       //  send 'end' tag
    }
    unset ($result);             
    exit() ;

?>
