<?php
	if(strpos($_SERVER['REQUEST_URI'], basename($_SERVER['SCRIPT_FILENAME']))!==false) {
		header('Location: /admin/');
		exit();
	}
	
	/* Do savin' here.... */
	if(!empty($_POST)) {
		// get all the configurations so that we know type
		$results = mysql_query("SELECT * FROM configurations");
		$configs = array();
		while($row = mysql_fetch_array($results)) {
			$configs[$row['slug']] = $row;
		}
		
		$settings_saved = false;
		
		// Now go through the posted data and save it
		foreach($_POST as $key => $value) {
			$save = true;
			if(substr($key, 0, 1)=='_') {
				// skip it
			} else {
				$type = $configs[$key]['type'];
				switch($type) {
					case 'boolean':
						if(isset($_POST['_'.$key])) {
							$value = 'true';
						} else {
							$value = 'false';
						}
					break;
					case 'numeric':
						// should we not save it if it's not numeric? ...yes, but we'll add in error states at a later date.
						if(!is_numeric($value)) { $save = false; }
					break;
					default:
						// don't need to do anything here....it's probably just a string
				}
				if($save) {
					mysql_query("UPDATE configurations SET `value`='".$value."' WHERE `slug`='".$key."'");
					$settings_saved = true;
				}
			}
		}
		/* End Savin' Stuff */
		
		if(Configure::read('real_log')=="1") {
			$truncate = mysql_query ("TRUNCATE `".TABLE_PREFIX."real_log`");     //  reset the real_log table
			if(!$truncate) {   //  enter here if the table for real logging was not jet installed
				echo "
					<div class='submenu cntr'>
						<span class='warnadmin'>";
				if(DEBUG > '0') echo mysql_error();
				echo "
							Please run the .../admin/install_reallog.php file.
						</span>
					</div>";
				die;
			}
		}
	}

	echo "
		<div class='submenu cntr'>
			<ul>
				<li><a href='#set_1'>- General Settings</a></li>
				<li><a href='#set_2'>- Index Log Settings</a></li>
				<li><a href='#set_3'>- Spider settings</a></li>
				<li><a href='#set_4'>- Search Settings</a></li>
				<li><a href='#set_5'>- Suggest Options</a></li>
				<li><a href='#set_6'>- Page Indexing Weights</a></li>
			</ul>
		</div>
		<div class=\"panel\">
			<div class='headline cntr'>Settings for Sphider-plus version ".Configure::read('plus_nr')." based on original Sphider v. ".Configure::read('version_nr')."</div>
			";
			if($settings_saved) {
				echo '<p class="warnadmin">Your configuration settings have been saved</p>';
			}
			echo "
			<div id='settings'>
				<form class='txt' name='form1' method='post' action='".WEBROOT_DIR."/admin/'>
					<fieldset>
						<legend><a name=\"set_1\">General Settings</a></legend>
						<div class='input'>
							<label for='home_charset'>Enter your preferred charset:</label>
							<input name='home_charset' id='home_charset' value='".Configure::read('home_charset')."' type='text' size='24' title='Enter your local charset (e.g. ISO-8859-1).' />
						</div>
						<div class='input'>
							<label for='admin_email'>Administrator e-mail address:</label>
							<input name='admin_email' id='admin_email' value='".Configure::read('admin_email')."' type='text' size='24' title='Enter email address for info and log posting' />
						</div>
						<div class='input'>
							<label for='dispatch_email'>Dispatcher e-mail address:</label>
							<input name='dispatch_email' id='dispatch_email' value='".Configure::read('dispatch_email')."' type='text' size='24' title='Enter email address for log and info mail transmission' />
						</div>
						<div class='input'>
							<label for='local'>Address to localhost document root:</label>
							<input name='local' id='local' value='".Configure::read('local')."' type='text' size='24' title='Enter the address to your local root folder' />
						</div>
						<div class='select'>
							<label for='template'>Template design:</label>
							<select name='template' size='3'>
								";
								$directories = get_dir_contents(APP.'templates/');
								if(count($directories)>0) {
									for ($i=0; $i<count($directories); $i++) {
											$tdir=$directories[$i];
										if(substr($tdir, 0, 1)!='.') {
											echo "<option id='template' value='".$tdir."'";
											if($tdir==Configure::read('template')) {
												echo " selected='selected'";
											}
											echo ">$tdir</option>";
										}
									}
								}
						echo "
							</select>
						</div>
						<div class='checkbox'>
							<input name='sites_alpha' type='hidden' value='1' />
							<input name='_sites_alpha' type='checkbox' id='sites_alpha' value='1' title=\"Select for Admin's Site Table sorted in alphabetic order\"";
							if(Configure::read('sites_alpha')==1) {
								echo " checked='checked'";
							}
							echo " />
								<label for='sites_alpha'>Admin's Sites Table sorted in alphabetic order</label>
								<p>(Deselect to sort by indexdate)</p>
						</div>
						<div class='checkbox'>
							<input name='clear' type='hidden' value='1' />
							<input name='_clear' type='checkbox' id='clear' value='1' title='Select, if you index large amount of URLs.'";
							if(Configure::read('clear')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='clear'>Clean resources during index / re-index</label>
							<p>(Check only if you index large amount of URLs.<br />Selection will reduce performance for index/re-index process)</p>
						</div>
						<div class='submit'>
							<input type='submit' value='Save' title='Click once to save these settings' />
						</div>
					</fieldset>
					<fieldset>
						<legend><a name=\"set_2\">Index Log Settings</a></legend>
						<div class='checkbox'>
							<input name='keep_log' type='hidden' value='1' />
							<input name='_keep_log' type='checkbox' id='keep_log' value='1' title='Select to enable spider logging'";
							if(Configure::read('keep_log')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='keep_log'>Log spidering results?</label>
						</div>
						<div class='select'>
							<label for='log_format'>Log file format:</label>
							<select name='log_format' id='log_format' title='Select default log file output option'>
							<option value='text'";
							if(Configure::read('log_format') == "text") {
								echo " selected='selected'";
							}
							echo ">Text</option>
							<option value='html'";
							if(Configure::read('log_format') == "html") {
							    echo " selected='selected'";
							}
							echo ">HTML</option></select>
						</div>
						<div class='checkbox'>
							<input name='real_log' type='hidden' value='1' />
							<input name='_real_log' type='checkbox' id='real_log' title='Select for real-time output of logging data to your browser'";
							if(Configure::read('real_log')==1) {
							    echo " checked='checked'";
							}
							echo " />
							<label for='real_log'>Enable real-time output of logging data:</label>
						</div>
						<div class='input'>
							<label for='refresh'>Update intervall for logging data (max.10 seconds): </label>
							<input name='refresh' type='text' id='refresh' size='1' maxlength='2' value='".Configure::read('refresh')."' title='Every x seconds the real-time output will be updated.' />
						</div>
						<div class='checkbox'>
							<input name='print_results' type='hidden' value='1' />
							<input name='_print_results' type='checkbox' id='print_results' title='Select for viewing indexing log'
							";
							if(Configure::read('print_results')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='print_results'>Print spidering results to standard out?</label>
						</div>
						<div class='checkbox'>
							<input name='email_log' type='hidden' value='1' />
							<input name='_email_log' type='checkbox' id='email_log' title='Select to auto-send log file by email'
							";
							if(Configure::read('email_log')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='email_log'>Send spidering log to e-mail?</label>
						</div>
						<div class='input'>
							<label for='click_wait'>Timeout in order to prevent promoted clicks until next click <br />for 'Most Popular Links' will be accepted [seconds]: </label>
							<input name='click_wait' type='text' id='click_wait' size='2' maxlength='2' value='".Configure::read('click_wait')."' title='Every x seconds a new click will be used to increase popularity of a link.' />
						</div>
						<div class='submit'>
							<input type='submit' value='Save' title='Click once to save these settings' />
						</div>
					</fieldset>
					<fieldset>
						<legend><a name=\"set_3\">Spider settings</a></legend>
						<p class='warnadmin'>If you modify any settings in this section after first index, you are obliged to invoke 'Erase &amp; Re-index'.</p>
						<div class='checkbox'>
							<input name='smap_unique' type='hidden' value='1' />
							<input name='_smap_unique' type='checkbox' id='smap_unique' title='Select only if all names should be equal'";
							if(Configure::read('smap_unique')==1) {
								echo " checked='checked'";
							}
							echo" />
							<label for='smap_unique'>Use a unique name (sitemap.xml) for all created sitemap files? </label>
							<p>
								(Should be checked only, if one single Site is to be indexed.<br />
								Do not check if several Sites are in the Sites table.<br />
								Otherwise all sitemap files will be overwritten.)
							</p>
						</div>
						<div class='input'>
							<label for='max_links'>Max. links to be followed for each Site:</label>
							<input name='max_links' type='text'  value='".Configure::read('max_links')."' id='max_links' size='5' title='Enter max. links to be followed for each url' />
						</div>
						<div class='input'>
							<label for='min_words_per_page'>Required number of words in a page in order to be indexed:</label>
							<input name='min_words_per_page' value='".Configure::read('min_words_per_page')."' type='text' id='min_words_per_page' size='5' maxlength='5' title='Enter minimum number of unique words to qualify for indexing' />
						</div>
						<div class='input'>
							<label for='min_word_length'>Minimum word length in order to be indexed:</label>
							<input name='min_word_length' type='text' value='".Configure::read('min_word_length')."' id='min_word_length' size='5' maxlength='2' title='Enter minimum length of keywords to index' />
						</div>
						<div class='input'>
							<label for='word_upper_bound'>Keyword weight:</label>
							<input name='word_upper_bound' type='text' value='".Configure::read('word_upper_bound')."' id='word_upper_bound' size='5' maxlength='3' title='Enter capping value of indexing weights' />
							<p>(Capped at this value depending on the number of times it appears in a page)</p>
						</div>
						<div class='checkbox'>
							<input name='utf8' type='hidden' value='1' />
							<input name='_utf8' type='checkbox' id='utf8' title='Select if complete text should be translated into UTF-8'";
							if(Configure::read('utf8')==1) {
								echo " checked='checked'";
							}
							echo" />
							<label for='utf8'>Convert all into UTF-8 charset: </label>
						</div>
						<div class='checkbox'>
							<input name='case_sensitive' type='hidden' value='1' />
							<input name='_case_sensitive' type='checkbox' id='case_sensitive' title='Leave blank for same rusults'";
							if(Configure::read('case_sensitive')==1) {
								echo " checked='checked'";
							}
							echo" />
							<label for='case_sensitive'>Enable distinct results for upper- and lower-case queries: <br />(Valid only for activated UTF-8 support)</label>
						</div>
						<div class='checkbox'>
							<input name='follow_sitemap' type='hidden' value='1' />
							<input name='_follow_sitemap' type='checkbox' id='follow_sitemap' title='Select for indexing and reindexing with sitemap.xml'";
							if(Configure::read('follow_sitemap')==1) {
								echo " checked='checked'";
							}
							echo" />
							<label for='follow_sitemap'>If available follow sitemap.xml: </label>
						</div>
						<div class='checkbox'>
							<input name='index_numbers' type='hidden' value='1' />
							<input name='_index_numbers' type='checkbox' id='index_numbers' title='Select for indexing of numbers in page text' ";
							if(Configure::read('index_numbers')==1) {
								echo " checked='checked'";
							}
							echo" />
							<label for='index_numbers'>Index numbers?</label>
						</div>
						<div class='checkbox'>
							<label for='index_host'>Index words in Domain Name and URL path?</label>
							<input name='index_host' type='hidden' value='1' />
							<input name='_index_host' type='checkbox' id='index_host' title='Select to enable domain name and URL path indexing'";
							if(Configure::read('index_host')==1) {
								echo " checked='checked'";
							}
							echo " />
						</div>
						<div class='checkbox'>
							<input name='index_meta_keywords' type='hidden' value='1' />
							<input name='_index_meta_keywords' type='checkbox' id='index_meta_keywords' title='Select to enable indexing of keyword Meta Tags'";
							if(Configure::read('index_meta_keywords')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='index_meta_keywords'>Index keyword Meta Tags?</label>
						</div>
						<div class='checkbox'>
							<input name='index_pdf' type='hidden' value='set' />
							<input name='_index_pdf' type='checkbox'  value='1' id='index_pdf' title='Select for indexing .pdf files'";
							if(Configure::read('index_pdf')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='index_pdf'>Index PDF files?</label>
						</div>
						<div class='checkbox'>
							<input name='index_doc' type='hidden' value='set' />
							<input name='_index_doc' type='checkbox'  value='1' id='index_doc' title='Select for indexing .doc files. Not available for LINUX/UNIX systems.'";
							if(Configure::read('index_doc')==1) {
								echo " checked='checked'";
							}
							echo" />
							<label for='index_doc'>Index DOC files?</label>
						</div>
						<div class='checkbox'>
							<input name='index_rtf' type='hidden' value='set' />
							<input name='_index_rtf' type='checkbox'  value='1' id='index_rtf' title='Select for indexing .rtf files. Not available for LINUX/UNIX systems.'";
							if(Configure::read('index_rtf')==1) {
								echo " checked='checked'";
							}
							echo" />
							<label for='index_rtf'>Index RTF files?</label>
						</div>
						<div class='checkbox'>
							<input name='index_xls' type='hidden' value='set' />
							<input name='_index_xls' type='checkbox'  value='1' id='index_xls' title='Select for indexing .xls files. Not available for LINUX/UNIX systems.'";
							if(Configure::read('index_xls')==1) {
								echo " checked='checked'";
							}
							echo" />
							<label for='index_xls'>Index XLS files?</label>
						</div>
						<div class='checkbox'>
							<input name='_index_ppt' type='checkbox'  value='1' id='index_ppt' title='Select for indexing .ppt files. Not available for LINUX/UNIX systems.'";
							if(Configure::read('index_ppt')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='index_ppt'>Index PPT files?</label>
						</div>
						<div class='checkbox'>
							<input name='use_common' type='hidden' value='1' />
							<input name='_use_common' type='checkbox' id='use_common' title='Select for excusion of words in commonlist.'";
							if(Configure::read('use_common')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='use_common'>Use commonlist for words to be ignored during index / re-index?</label>
						</div>
						<div class='checkbox'>
							<label for='use_white'>Use whitelist in order to enable index / re-index only those pages<br />that include any of the words in whitelist?</label>
							<input name='use_white' type='hidden' value='1' />
							<input name='_use_white' type='checkbox' id='use_white' title='Select for indexing only pages including any of the words in whitelist.'";
							if(Configure::read('use_white')==1) {
								echo " checked='checked'";
							}
							echo "/>
						</div>
						<div class='checkbox'>
							<input name='use_black' type='hidden' value='1' />
							<input name='_use_black' type='checkbox' id='use_black' title='Select for not indexing pages including any of the words in blacklist.'";
							if(Configure::read('use_black')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='use_black'>Use blacklist to prevent index / re-index of pages<br />that contain any of the words in blacklist?</label>
						</div>
						<div class='checkbox'>
							<input name='del_secchars' type='hidden' value='1' />
							<input name='_del_secchars' type='checkbox' id='del_secchars' title='Select for deleting secundary characters and index only the pure words.'";
							if(Configure::read('del_secchars')==1) {
								echo " checked='checked'";
							}
							echo "/>
							<label for='del_secchars'>Delete special characters like dots, commas, quotes,<br />exclamation and question marks etc. as part of words?</label>
							<p>
								(Symbols like ( and \" in front of words and also . , ) : ! ? as final charachters<br />of words will be deleted. So only the pure words will be indexed.)
							</p>
						</div>
						<div class='input'>
							<label for='user_agent'>User agent string:</label>
							<input name='user_agent' value='".Configure::read('user_agent')."' type='text' id='user_agent' size='20' title='Enter identifier of your spider for remote log files' />
						</div>
						<div class='input'>
							<label for='min_delay'>Minimal delay between page downloads:</label>
							<input name='min_delay' value='".Configure::read('min_delay')."' type='text' id='min_delay' size='5' title='Enter delay time in seconds between pages downloaded' />
						</div>
						<div class='checkbox'>
							<input name='stem_words' type='hidden' value='1' />
							<input name='_stem_words' type='checkbox' id='stem_words' title='Select to enable word-stemming'";
							if(Configure::read('stem_words')==1) {
								echo " checked='checked'";
							}
							echo "/>
							<label for='stem_words'>Use word stemming :</label>
							<p>(e.g. find sites containing 'runs' and 'running' when searching for 'run')</p>
						</div>
						<div class='checkbox'>
							<input name='strip_sessids' type='hidden' value='1' />
							<input name='_strip_sessids' type='checkbox' id='strip_sessids' title='Select to enable session ID stripping'";
							if(Configure::read('strip_sessids')==1) {
								echo " checked='checked'";
							}
							echo "/>
							<label for='strip_sessids'>Strip session ids? (phpsessid, jsessionid, aspsessionid, sid):</label>
						</div>
						<div class='checkbox'>
							<input name='link_check' type='hidden' value='1' />
							<input name='_link_check' type='checkbox' id='link_check' title='Select for link-check'";
							if(Configure::read('link_check')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='link_check'>Perform a link-check instead of re-index: </label>
							<p>(leave unchecked for standard re-index)</p>
						</div>
						<div class='checkbox'>
							<input name='dup_content' type='hidden' value='1' />
							<input name='_dup_content' type='checkbox' id='dup_content' title='Select to index pages with content already indexed with other pages'";
							if(Configure::read('dup_content')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='dup_content'>Enable index and re-index for pages with duplicate content</label>
							<p>(leave blank if pages with duplicate content should not be indexed)</p>
						</div>
						<div class='submit'>
							<input type='submit' value='Save' title='Click once to save these settings' />
						</div>
					</fieldset>
					<fieldset>
						<legend><a name=\"set_4\">Search Settings</a></legend>
						<div class='checkbox'>
							<input name='allow_default_search' type='hidden' value='1' />
							<input name='_allow_default_search' type='checkbox' id='allow_default_search' title='Check to disallow public facing search (if you are using API or EE plugin, for instance.)'";
							if(Configure::read('allow_default_search')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='allow_default_search'>Check to allow public facing search</label>
							<p>(uncheck if you are using API or EE plugin, for instance.)</p>
						</div>
						<div class='checkbox'>
							<input name='pop_result_link' type='hidden' value='1' />
							<input name='_pop_result_link' type='checkbox' id='pop_result_link' title='Check to have links in search results pop up a new page when clicked'";
							if(Configure::read('allow_default_search')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='pop_result_link'>Check to have links in search results pop up in new page.</label>
						</div>
						<div class='radio'>
							<p class='radio_header'>Default search results per page :</p>
							<input type='radio' name='_results_per_page' id='ten_results_per_page' value='10' title='Select default results per search page'";
							if(Configure::read('results_per_page')==10) {
								echo " checked='checked'";
							}
							echo " /> <label for='ten_results_per_page'>10</label>
							<input class='ltfloat' type='radio' name='_results_per_page' id='twenty_results_per_page' value='20'";
							if(Configure::read('results_per_page')==20) {
								echo " checked='checked'";
							}
							echo " /> <label for='twenty_results_per_page'>20</label>
							<input class='ltfloat' type='radio' name='_results_per_page' id='thirty_results_per_page' value='30'";
							if(Configure::read('results_per_page')==30) {
								echo " checked='checked'";
							}
							echo " />  <label for='thirty_results_per_page'>30</label>
							<input class='ltfloat' type='radio' name='_results_per_page' id='fifty_results_per_page' value='50'";
							if(Configure::read('results_per_page')==50) {
								echo " checked='checked'";
							}
							echo "/>  <label for='fifty_results_per_page'>50</label>
						</div>
						<div class='select'>
							<label for='language'>Search Page results language: </label>
							<select name='_language' id='language' title='Select your preferred Search Page language'>
								<option value='ar'";
								if(Configure::read('language') == 'ar'){
									echo " checked='checked'";
								}
								echo ">Arabic</option>
								<option value='bg'";
								if(Configure::read('language') == 'bg') {
									echo " selected='selected'";
								}
								echo ">Bulgarian</option>
								<option value='hr'";
								if(Configure::read('language') == 'hr') {
									echo " selected='selected'";
								}
								echo ">Croatian</option>
								<option value='cns'";
								if(Configure::read('language') == 'cns') {
									echo " selected='selected'";
								}
								echo ">Simple Chinese</option>
								<option value='cnt'";
								if(Configure::read('language') == 'cnt') {
									echo " selected='selected'";
								}
								echo ">Traditional Chinese</option>
								<option value='cz'";
								if(Configure::read('language') == 'cz') {
									echo " selected='selected'";
								}
								echo">Czech</option>
								<option value='dk'";
								if(Configure::read('language') == 'dk') {
									echo " selected='selected'";
								}
								echo ">Danish</option>
								<option value='nl'";
								if(Configure::read('language') == 'nl') {
									echo " selected='selected'";
								}
								echo ">Dutch</option>
								<option value='en'";
								if(Configure::read('language') == 'en') {
								echo " selected='selected'";
								}
								echo ">English</option>
								<option value='ee'";
								if(Configure::read('language') == 'ee') {
								echo " selected='selected'";
								}
								echo ">Estonian</option>
								<option value='fi'";
								if(Configure::read('language') == 'fi') {
								echo " selected='selected'";
								}
								echo ">Finnish</option>
								<option value='fr'";
								if(Configure::read('language') == 'fr') {
								echo " selected='selected'";
								}
								echo ">French</option>
								<option value='de'";
								if(Configure::read('language') == 'de') {
									echo " selected='selected'";
								}
								echo ">German</option>
								<option value='hu'";
								if(Configure::read('language') == 'hu') {
									echo " selected='selected'";
								}
								echo ">Hungarian</option>
								<option value='it'";
								if(Configure::read('language') == 'it') {
									echo " selected='selected'";
								}
								echo ">Italian</option>
								<option value='lv'";
								if(Configure::read('language') == 'lv') {
									echo " selected='selected'";
								}
								echo ">Latvian</option>
								<option value='pl'";
								if(Configure::read('language') == 'pl') {
									echo " selected='selected'";
								}
								echo ">Polish</option>
								<option value='pt'";
								if(Configure::read('language') == 'pt') {
									echo " selected='selected'";
								}
								echo ">Portuguese</option>
								<option value='ro'";
								if(Configure::read('language') == 'ro') {
									echo " selected='selected'";
								}
								echo ">Romanian</option>
								<option value='ru'";
								if(Configure::read('language') == 'ru') {
									echo " selected='selected'";
								}
								echo ">Russian</option>
								<option value='sr'";
								if(Configure::read('language') == 'sr') {
									echo " selected='selected'";
								}
								echo ">Serbian</option>
								<option value='sk'";
								if(Configure::read('language') == 'sk') {
									echo " selected='selected'";
								}
								echo ">Slovak</option>
								<option value='si'";
								if(Configure::read('language') == 'si') {
									echo " selected='selected'";
								}
								echo ">Slovenian</option>
								<option value='es'";
								if(Configure::read('language') == 'es') {
									echo " selected='selected'";
								}
								echo ">Spanish</option>
								<option value='se'";
								if(Configure::read('language') == 'se') {
									echo " selected='selected'";
								}
								echo ">Swedish</option>
								<option value='tr'";
								if(Configure::read('language') == 'tr') {
									echo " selected='selected'";
								}
								echo ">Turkish</option>
							</select>
						</div>
						<div class='checkbox'>
							<input name='auto_lng' type='hidden' value='1' />
							<input name='_auto_lng' type='checkbox' value='1' id='auto_lng' title='Select to enable the automatic detection of user dialog language'";
							if(Configure::read('auto_lng')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='auto_lng'>Automatically detect user dialog language: </label>
						</div>
						<div class='input'>
							<label for='title'>Title for Result Page:</label>
							<input name='mytitle' type='text' id='mytitle' value='".Configure::read('mytitle')."' size='19' maxlength='50' title='Enter your personal Title for Result Page' />
						</div>
						<div class='select'>
							<label for='mark'>Select method of highlighting for found keywords in result listing: </label>
							<select name='_mark' id='mark' title='Select highlighting for found keywords in result listing'>
								<option value='markbold'";
								if(Configure::read('mark') == 'markbold') {
									echo " checked='checked'";
								}
								echo ">bold text</option>
								<option value='markyellow'";
								if(Configure::read('mark') == 'markyellow') {
									echo " selected='selected'";
								}
								echo ">marked yellow</option>
								<option value='markgreen'";
								if(Configure::read('mark') == 'markgreen') {
									echo " selected='selected'";
								}
								echo ">marked green</option>
								<option value='markblue'";
								if(Configure::read('mark') == 'markblue') {
									echo " selected='selected'";
								}
								echo ">marked blue</option>
							</select>
						</div>
						<div class='input'>
							<label for='bound_search_results'>Bound number of search results:</label>
							<input name='bound_search_result' type='text' value='".Configure::read('bound_search_result')."' id='bound_search_results' size='5' title='Change to limit total search results found - 0 = unlimited' />
							<p>(Can speed up searches on large database - Should be Zero)</p>
						</div>
						<div class='input'>
						<label for='length_of_link_desc'>Length of description string queried when displaying search results:</label>
							<input name='length_of_link_desc' type='text' value='".Configure::read('length_of_link_desc')."' id='length_of_link_desc' size='5' maxlength='4' title='Enter value for maximum text length in search results page' />
							<p>
								(Can significantly speed up searching on very slow machines)<br />
								(If set to a lower value [e.g. 250 or 1000; 0 is unlimited],<br /> otherwise doesn't have an effect)
							</p>
						</div>
						<div class='input'>
							<label for='links_to_next'>Number of links shown to \"Next\" pages:</label>
							<input name='links_to_next' type='text' value='".Configure::read('links_to_next')."' id='links_to_next' size='5' maxlength='2' title='Enter default number of \"Next\" page links to display' />
						</div>
						<div class='checkbox'>
							<input name='show_meta_description' type='hidden' value='1' />
							<input name='_show_meta_description' type='checkbox' id='show_meta_description' title='Select to enable display of description meta tag content'";
							if(Configure::read('show_meta_description')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='show_meta_description'>Show Description Meta Tags, if they exist, on results page?</label>
							<p>(Otherwise show an extract from the page text)</p>
						</div>
						<div class='checkbox'>
							<label for='show_warning'>Show warning message if Search string was not found<br /> in description, but only in Site title or URL ?</label>
							<input name='show_warning' type='hidden' value='1' />
							<input name='_show_warning' type='checkbox' id='show_warning' title='Select to enable the warning message'";
							if(Configure::read('show_warning')==1) {
								echo " checked='checked'";
							}
							echo " />
						</div>";
/*
					<label for='case_sensitive'>If utf-8 is supported, separate between upper- and lower-case queries ?</label>
					<input name='_case_sensitive' type='checkbox' value='1' id='case_sensitive'
					title='Select to separate'
					";

					if(Configure::read('case_sensitive')==1) {
					echo " checked='checked'";
					}
					echo " />
				*/    
					echo "
						<div class='checkbox'>
							<label for='advanced_search'>Advanced search? (Shows 'AND/OR/PHRASE/TOLERANT'):</label>
							<input name='advanced_search' type='hidden' value='1' />
							<input name='_advanced_search' type='checkbox' id='advanced_search' title='Select to enable \"Advanced Search\" in Search Box'";
							if(Configure::read('advanced_search')==1) {
								echo " checked='checked'";
							}
							echo " />
						</div>
						<div class='checkbox'>
							<label for='show_categories'>Show categories?</label>
							<input name='show_categories' type='hidden' value='1' />
							<input name='_show_categories' type='checkbox' id='show_categories' title='Select to display Categories on results pages'";
							if(Configure::read('show_categories')==1) {
								echo " checked='checked'";
							}
							echo " />
						</div>
						<div class='checkbox'>
							<input name='show_query_scores' type='hidden' value='1' />
							<input name='_show_query_scores' type='checkbox' id='show_query_scores' title='Select to enable display of Result Scores'";
							if(Configure::read('show_query_scores')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='show_query_scores'>Show result scores (weighting %) calculated by Sphider-plus ?</label>
						</div>
						<div class='checkbox'>
							<input name='query_hits' type='hidden' value='1' />
							<input name='_query_hits' type='checkbox' id='query_hits' title='Select to enable display of hits in fulltext'";
							if(Configure::read('query_hits')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='query_hits'>Instead of weighting %, show count of query hits in full text?</label>
						</div>
						<div class='input'>
							<label for='title_length'>Maximum length of page title displayed in search results:</label>
							<input name='title_length' type='text' id='title_length' size='5' maxlength='4' value='".Configure::read('title_length')."' title='Enter value to limit maximum number of characters for page title in result listing' />
							<p>(Title will be broken at the end of the word exceeding the defined length)</p>
						</div>
						<div class='input'>
							<label for='desc_length'>Maximum length of page summary displayed in search results:</label>
							<input name='desc_length' type='text' id='desc_length' size='5' maxlength='4' value='".Configure::read('desc_length')."' title='Enter value to limit maximum number of characters for page summaries in result listing' />
						</div>
						<div class='input'>
							<label for='url_length'>Maximum length of URL displayed in search results:</label>
							<input name='url_length' type='text' id='url_length' size='5' maxlength='4' value='".Configure::read('url_length')."' title='Enter value to limit maximum number of characters of URL in result listing' />
						</div>
						<div class='input'>
							<label for='max_hits'>Define maximum count of result hits per page, displayed in <br />search results (if multiple occurrence is available on a page):</label>
							<input name='max_hits' type='text' id='max_hits' size='1' maxlength='1' value='".Configure::read('max_hits')."' title='Enter value to limit maximum number of shown hits per page in result listing' />
						</div>
						<div class='checkbox'>
							<input name='did_you_mean_enabled' type='hidden' value='1' />
							<input name='_did_you_mean_enabled' type='checkbox' id='did_you_mean_enabled' title='Select to enable \"Did You Mean?\" suggestions on results page'";
							if(Configure::read('did_you_mean_enabled')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='did_you_mean_enabled'>Enable spelling suggestions? (Did you mean?)</label>
						</div>
						<div class='checkbox'>
							<input name='show_sort' type='hidden' value='1' />
							<input name='_show_sort' type='checkbox' id='show_sort' title='Select to display the chronological order for results pages'";
							if(Configure::read('show_sort')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='show_sort'>Show mode of chronological order for result listing as additional headline?</label>
						</div>
						<div class='checkbox'>
							<input name='most_pop' type='hidden' value='1' />
							<input name='_most_pop' type='checkbox' value='1' id='most_pop' title='Select to enable most popular searches table displayed at the bottom of result pages'";
							if(Configure::read('most_pop')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='most_pop'>Show 'Most popular searches' table at the bottom of result pages: </label>
						</div>
						<div class='input'>
							<label for='pop_rows'>Define number of rows for 'Most popular searches': </label>
							<input name='pop_rows' type='text' id='pop_rows' size='2' maxlength='2' value='".Configure::read('pop_rows')."' title='If selected above, define here how many rows should be presented.' />
						</div>
						<div class='input'>
							<label for='relevance'>Define min. relevance level (weight in %) <br /> for results to be presented at results pages: </label>
							<input name='relevance' type='text' id='relevance' size='2' maxlength='2' value='".Configure::read('relevance')."' title='Enter 0 to get all results.' />
						</div>
						<div class='checkbox'>
							<input name='add_url' type='hidden' value='1' />
							<input name='_add_url' type='checkbox' id='add_url' title='Select to enable User may suggest a new Url,displayed at the bottom of result pages'";
							if(Configure::read('add_url')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='add_url'>Allow user to suggest a Url to be indexed</label>
						</div>
						<div class='checkbox'>
							<input name='captcha' type='hidden' value='1' />
							<input name='_captcha' type='checkbox' id='captcha' title='Select for user security input when suggesting a new Url'";
							if(Configure::read('captcha')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='captcha'>Captcha protection for URL Submission Form</label>
						</div>
						<div class='checkbox'>
							<input name='addurl_info' type='hidden' value='1' />
							<input name='_addurl_info' type='checkbox' value='1' id='addurl_info' title=\"Select to enable e-mail notification for user suggestion of new Url's\"";
							if(Configure::read('addurl_info')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='addurl_info'>Inform about user suggestion by e-mail</label>
						</div>
						<div class='submit'>
							<input type='submit' value='Save' title='Click once to save these settings' />
						</div>
					</fieldset>
					<fieldset>
						<legend><a name=\"set_5\">Suggest Options</a></legend>
						<div class='input'>
							<label for='min_sug_chars'>Define minimum count of query letters in order to get a suggestion :</label>
							<input name='min_sug_chars' type='text' id='min_sug_chars' size='1' maxlength='1' value='".Configure::read('min_sug_chars')."' title='Enter minimum number of characters for suggestions to be presented' />
							<p>( 0 = No suggestion will be presented)</p>
						</div>
						<div class='checkbox'>
							<input name='suggest_history' type='hidden' value='1' />
							<input name='_suggest_history' type='checkbox' id='suggest_history' title='Select to enable suggestions from Query Log'";
							if(Configure::read('suggest_history')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='suggest_history'>Search for suggestions in query log?</label>
						</div>
						<div class='checkbox'>
							<input name='suggest_keywords' type='hidden' value='1' />
							<input name='_suggest_keywords' type='checkbox' id='suggest_keywords' title='Select to enable suggestions from Keywords'";
							if(Configure::read('suggest_keywords')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='suggest_keywords'>Search for suggestions in keywords?</label>
						</div>
						<div class='checkbox'>
							<input name='suggest_phrases' type='hidden' value='1' />
							<input name='_suggest_phrases' type='checkbox' id='suggest_phrases' title='Select to enable suggestions from Phrases'";
							if(Configure::read('suggest_phrases')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='suggest_phrases'>Search for suggestions in phrases?</label>
						</div>
						<div class='checkbox'>
							<input name='show_hits' type='hidden' value='1' />
							<input name='_show_hits' type='checkbox' id='show_hits' title='Select to show result hits in database'";
							if(Configure::read('show_hits')==1) {
								echo " checked='checked'";
							}
							echo " />
							<label for='show_hits'>Show amount of found keywords in suggestion table?</label>
						</div>
						<div class='input'>
							<label for='suggest_rows'>Limit number of suggestions to:</label>
							<input name='suggest_rows' type='text' id='suggest_rows' size='3' maxlength='2' value='".Configure::read('suggest_rows')."' title='Enter default number of rows for suggestions' />
						</div>
			
						<div class='submit'>
							<input type='submit' value='Save' title='Click once to save these settings' />
						</div>
					</fieldset>
					<fieldset>
						<legend><a name=\"set_6\">Page Indexing Weights</a></legend><br />
						<p class='warnadmin'>If you modify any settings in this section after first index, you are obliged to invoke 'Erase &amp; Re-index'.</p>
				
						<div class='input'>
							<label for='title_weight'>Relative weight of a word in web page Title tag:</label>
							<input name='title_weight' type='text' id='title_weight' size='5' maxlength='2' value='".Configure::read('title_weight')."' title='Enter default weight for words in a Web page title tag' />
						</div>
						<div class='input'>
							<label for='domain_weight'>Relative weight of a word in the Domain Name:</label>
							<input name='domain_weight' type='text' id='domain_weight' size='5' maxlength='2' value='".Configure::read('domain_weight')."' title='Enter default weight for words in a Domain Name' />
						</div>
						<div class='input'>
							<label for='path_weight'>Relative weight of a word in the Path Name:</label>
							<input name='path_weight' type='text' id='path_weight' size='5' maxlength='2' value='".Configure::read('path_weight')."' title='Enter default weight for words in a Path Name' />
						</div>
						<div class='input'>
							<label for='meta_weight'>Relative weight of a word in web page Keywords tag:</label>
							<input name='meta_weight' type='text' id='meta_weight' size='5' maxlength='2' value='".Configure::read('meta_weight')."' title='Enter default weight for words in Keyword Meta Tags' />
						</div>
						<div class='select'>
							<label for='sort_results'>Define the default chronological order for result listing</label>
							<select name='sort_results' id='sort_results' title='Select how to present the result listing'>
								<option value='1'";
								if(Configure::read('sort_results') == '1'){
									echo " selected='selected'";
								}
								echo ">By relevance (weight / hits) </option>
								<option value='2'";
								if(Configure::read('sort_results') == '2') {
									echo " selected='selected'";
								}
								echo ">Main URLs (domains) on top </option>
								<option value='3'";
								if(Configure::read('sort_results') == '3') {
									echo " selected='selected'";
								}
								echo ">By URL names</option>
								<option value='4'";    
								if(Configure::read('sort_results') == '4') {
									echo " selected='selected'";
								}
								echo ">Like Google (Top 2 per URL)</option>
								<option value='5'";
								if(Configure::read('sort_results') == '5') {
									echo " selected='selected'";
								}
								echo ">'Most Popular Links' on top</option>
							</select>
						</div>
						<div class='input'>
							<label for='domain_mul'>Multiplier for words in main URLs (domains): </label>
							<input name='domain_mul' type='text' id='domain_mul' size='1' maxlength='1' value='".Configure::read('domain_mul')."' title='Defines factor for all words in Domains.' />
						</div>
						<input class='hide' type='hidden' name='f' value='settings' />
						<input class='hide' type='hidden' name='Submit' value='1' />
						<input class='hide' type='hidden' name='_plus_nr' value='".Configure::read('plus_nr')."' />
						<input class='hide' type='hidden' name='_version_nr' value='".Configure::read('version_nr')."' />
						<div class='submit'>
							<input type='submit' value='Save' title='Click once to save these settings' />
						</div>
					</fieldset>
				</form>
			</div>
		</div>";
?>
	</body>
</html>