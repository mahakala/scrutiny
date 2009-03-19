<?php

/* This shite srsly needs to be cleaned up */
require_once(INCLUDE_DIR."class.Search.php");
require_once(INCLUDE_DIR."class.Category.php");

// try to get the currently valid language
if(Configure::read('auto_lng') == 1) //  if enabled in Admin settings get country code of calling client
{
	if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
	{
		$cc = substr( htmlspecialchars($_SERVER['HTTP_ACCEPT_LANGUAGE']), 0, 2);
		if(file_exists(LANGUAGE_DIR.$cc."-language.php"))
		{
			Configure::write('language', $cc); // if available set language to users slang
		}
	}
}
require_once(LANGUAGE_DIR.Configure::read('language')."-language.php");
/* END - This shite srsly needs to be cleaned up */

class SearchDisplay extends Search
{
	private $start_links = '';
	private $domain = '';
	private $adv = '';
	private $catid = '';
	private $query = '';
	private $type = '';
	private $start = '';
	private $search = '';
	private $results = '';
	private $category = '';
	private $tpl_ = array();
	
	function __construct() {
		global $sph_messages;
		$this->default_charset = Configure::read('home_charset');
		
		if (Configure::read('utf8') == 1) {
			Configure::write('home_charset', 'utf-8'); // set HTTP header character encoding to UTF-8
		}
		
		if (Configure::read('mark') == $sph_messages['markbold']) Configure::write('mark', 'markbold');
		if (Configure::read('mark') == $sph_messages['markyellow']) Configure::write('mark', 'markyellow');
		if (Configure::read('mark') == $sph_messages['markgreen']) Configure::write('mark', 'markgreen');
		if (Configure::read('mark') == $sph_messages['markblue']) Configure::write('mark', 'markblue');
		
		if(isset($_GET['type'])) {
			$this->type = $_GET['type'];
		}
		
		if($this->type != "or" && $this->type != "and" && $this->type != "phrase" && $this->type != "tol")
		{
			$this->type = "and";
		}
		
		if(isset($_GET['domain']) && preg_match("/[^a-z0-9-.]+/", $_GET['domain']))
		{
			$this->domain = "";
		}
		
		if(isset($_GET['results']) && is_numeric($_GET['results']))
		{
			$this->results = $_GET['results'];
			Configure::write('results_per_page', $this->results);
		}
		
		if(isset($_GET['catid']) && !is_numeric($_GET['catid']))
		{
			$this->catid = "";
		} else {
			$this->catid = $_GET['catid'];
		}
		
		if(isset($_GET['search']) && !empty($_GET['search']))
		{
			$this->search = $_GET['search'];
		}
		
		if(isset($_GET['query']))
		{
			$this->query = $_GET['query'];
		}
		
		if(isset($_GET['start']))
		{
			$this->start = $_GET['start'];
		}
		
		if(!is_numeric($_GET['category']))
		{
			$this->category = "";
		} else {
			$this->category = $_GET['category'];
		}
		
		if($this->catid && is_numeric($this->catid))
		{
			$this->tpl_['category'] = sql_fetch_all('SELECT category FROM '.TABLE_PREFIX.'categories WHERE category_id='.(int)$this->catid);
		}
		
		$this->count_level0 = sql_fetch_all('SELECT count(*) FROM '.TABLE_PREFIX.'categories WHERE parent_num=0');
		$this->has_categories = 0;
		
		if ($this->count_level0) {
			$this->has_categories = $this->count_level0[0][0];
		}
	}
	
	function page_title() {
		/* Need to figure out what exactly this is doing. */
		
		// if ($catid && is_numeric($catid))
		// {
		// 	$cattree = array(" ",$sph_messages['Categories']);
		// 	$cat_info = Category::get_category_info($catid);
		// 	foreach ($cat_info['cat_tree'] as $_val)
		// 	{
		// 		$thiscat = $_val['category'];
		// 		array_push($cattree," > ",$thiscat);
		// 	}
		// 	$cattree = implode($cattree);
		// }
		
		if ($this->start < '2') $this->start = '1';
		$return = Configure::read('mytitle');
		if ($catid && is_numeric($catid)) $return .= "$cattree";
		if ($query !='') $this->return .= " Your search term: '$query'. Results from page: $this->start";
		
		return $return;
	}
	
	function form() {
		global $sph_messages;
		$return = '
		<form action="'.WEBROOT_DIR.'/" method="get" id="searchform">
			<input type="text" name="query" id="query" value="'.quote_replace($this->query).'" />
			<input type="hidden" name="search" value="1" />
			<input type="submit" id="submit" value="'.$sph_messages['Search'].'" />'."\r\n";
				if($this->adv==1 || Configure::read('advanced_search')==1) { // if Advanced-search should be shown enter here
					$return .= '<span id="show-advanced"><a href="#">Advanced Search</a></span>
					<div id="advanced-search">
						<fieldset class="radioset" id="searchtype">
							<input type="radio" name="type" id="andtype" value="and" ';
							$return .= $type=='and'?'checked':'';
							$return .= '><label for="andtype">'.$sph_messages['andSearch'].'</label>
							<input type="radio" name="type" id="ortype" value="or" ';
							$return .= $this->type=='or'?'checked':'';
							$return .= '><label for="ortype">'.$sph_messages['orSearch'].'</label>
							<input type="radio" name="type" id="phrasetype" value="phrase" ';
							$return .= $_REQUEST['type']=='phrase'?'checked':'';
							$return .= '><label for="phrasetype">'.$sph_messages['phraseSearch'].'</label>
							<input type="radio" name="type" id="toltype" value="tol" ';
							$return .= $_REQUEST['type']=='tol'?'checked':'';
							$return .= '><label for="toltype">'.$sph_messages['tolSearch'].'</label>
						</fieldset>';
				if(Configure::read('show_categories')==1) {	//	Show part of the Search-form :	Cat-search
					$return .= '<fieldset class="radioset" id="searchcategories">
						'.$sph_messages['Search'].': <input type="radio" name="category" value="'.$catid.'">'.$sph_messages['Only in category'].' "'.$tpl_['category'][0]['category'].'" <input type="radio" name="category" value="-1" checked>'.$sph_messages['All sites'].'
						<input type="hidden" name="catid" value="'.$catid.'">'."\r\n";
						if($has_categories && $search==1 && Configure::read('show_categories')) {
							$return .= '<a href="'.WEBROOT_DIR.'">'.$sph_messages['Categories'].'</a>'."\r\n";
						}
					$return .= '</fieldset>'."\r\n";
				} 
				// Show method of highlighting
				if (Configure::read('show_searchmark')==1) {	//	Show part of the Search-form : Mark query terms
				$return .= '<fieldset class="singlelineset" id="searchmark">
					<label for="mark">'.$sph_messages['mark'].'</label>
					<select name="mark" id="mark">
						<option ';
				if(Configure::read('mark')=='markbold') $return .= "selected";
				$return .= '>'.$sph_messages['markbold'].'</option>
						<option ';
				if(Configure::read('mark')=='markyellow') $return .= "selected";
				$return .= '>'.$sph_messages['markyellow'].'</option>
						<option ';
				if(Configure::read('mark')=='markgreen') $return .= "selected";
				$return .= '>'.$sph_messages['markgreen'].'</option>
						<option ';
				if(Configure::read('mark')=='markblue') $return .= "selected";
				$return .= '>'.$sph_messages['markblue'].'</option>
					</select>
				</fieldset>';
				}
				// Show results per page
				$return .= '<fieldset class="singlelineset" id="searchnumpage">
					<label for="results">'.$sph_messages['show'].'</label>
					<select name="results" id="results">
						<option ';
				if(Configure::read('results_per_page')==5) $return .= "selected";
				$return .= '>5</option>
						<option ';
				if(Configure::read('results_per_page')==10) $return .= "selected";
				$return .= '>10</option>
						<option ';
				if(Configure::read('results_per_page')==20) $return .= "selected";
				$return .= '>20</option>
						<option ';
				if(Configure::read('results_per_page')==30) $return .= "selected";
				$return .= '>30</option>
						<option ';
				if(Configure::read('results_per_page')==50) $return .= "selected";
				$return .= '>50</option>
					</select>
					<span class="post-label">'.$sph_messages['resultsPerPage'].'</span>
				</fieldset>
			</div>'."\r\n";
		}
		$return .= '</form>'."\r\n";
		
		return $return;
	}
	
	function do_it() {
		if(!empty($this->search))
		{
			global $sph_messages;
			
			// If you want to search for all pages of a site by: site:abc.de
			$pos = strstr(strtolower($this->query),"site:");
			if(strlen($pos) > 5) require_once(INCLUDE_DIR."search_links.php");
			
			// For all other  search modes
			$strictpos = strpos($this->query, '!');
			$this->wildcount = substr_count($this->query, '*');
			
			if($this->wildcount || $strictpos === 0)
			{
				$this->type = 'and'; // if wildcard, or strict search mode, switch to AND search
			}
			
			if($this->wildcount || $strictpos === 0 || $this->type =='tol') {	//	if wildcard, strict or tolerant search mode, we have to search a lot but only for the first word
				$first = strpos($query, ' ');
				if($first) $this->query = substr($this->query, '0', $first);
			}
			
			$this->search_results = $this->get_search_results($this->query, $this->start, $this->category, $this->type, $this->results, $this->domain);
		}
	}
	
	function ignored_words() {
		global $sph_messages;
		
		$msg = '';
		if(isset($this->search_results['ignore_words']) && $this->type !='phrase')
		{
			while($thisword = each($this->search_results['ignore_words']))
			{
				$ignored .= ' '.$thisword[1];
			}
			$msg .= str_replace ('%ignored_words', $ignored, $sph_messages["ignoredWords"]);
		}
		return $msg;
	}

	function did_you_mean() {
		global $sph_messages;
		
		$return = '';
		if(isset($this->search_results['did_you_mean']) && !empty($this->search_results['did_you_mean'])) // if Sphider-plus found a suggestion
		{
			$return .= '<span class="did-you-mean-question">'.$sph_messages['DidYouMean'].':</span>
				<a href="index.php?query='.quote_replace($this->addmarks($this->search_results['did_you_mean'])).'&search=1&amp;type='.$this->type.'&amp;results='.$this->result.'&amp;mark='.$this->mark.'&amp;category='.$this->cat.'&amp;catid='.$this->catid.'">'.$this->search_results['did_you_mean_b'].'</a>?';
		}
		return $return;
	}

	function display_report() {
		global $sph_messages;
		
		$result = '';
		if($this->search_results['total_results'] != 0 && $this->search_results['from'] <= $this->search_results['to'])
		{
			// this is the standard results header
			$result = $sph_messages['Results'];
			$result = str_replace ('%from', $this->search_results['from'], $result);
			$result = str_replace ('%to', $this->search_results['to'], $result);
			$result = str_replace ('%all', $this->search_results['total_results'], $result);
			
			if (Configure::read('advanced_search') == 1 && Configure::read('show_categories') == 1 && $this->category != '-1') { // additional headline for category search results
				$catname = $tpl_['category'][0]['category'];
				if ($catname != '') {
					$result = $result;
					$catsearch = $sph_messages['catsearch']; 
					$result = $result.' '.$catsearch.' '.$catname;
				} else {
					$result = $sph_messages['catselect'];
				}
			}
			
			$matchword = $sph_messages["matches"];
			
			if ($total_results== 1) {
				$matchword= $sph_messages["match"];
			} else {
				$matchword= $sph_messages["matches"];
			}
			
			$result = str_replace ('%matchword', $matchword, $result);
			$result = str_replace ('%secs', $this->search_results['time'], $result);
		}
		return $result;
	}

	function results_order() {
		global $sph_messages;
		
		$result_order = '';
		if($this->search_results['total_results'] != 0 && $this->search_results['from'] <= $this->search_results['to']) {
			if (Configure::read('show_sort') == '1' && $wildcount != '1') {
				$res_order = $sph_messages['ResultOrder'];	  // show order of result listing
				if (Configure::read('sort_results') == '1') {
					$this_list = $sph_messages['order1'];
				}
				if (Configure::read('sort_results') == '2') {
					$this_list = $sph_messages['order2'];
				}
				if (Configure::read('sort_results') == '3') {
					$this_list = $sph_messages['order3'];
				}
				if (Configure::read('sort_results') == '4') {
					$this_list = $sph_messages['order4'];
				}
				if (Configure::read('sort_results') == '5') {
					$this_list = $sph_messages['order5'];
				}
				$result_order = $res_order.' '.$this_list;
			}
		}
		return $result_order;
	}

	function display_results() {
		global $sph_messages;
		
		$result = '';
		
		if($this->search_results['total_results']===0) // if query did not match any keyword
		{
			$msg = str_replace ('%query', $this->query, $sph_messages["noMatch"]);
			$result .= '<p class="no-matches">'.$msg.'</p>';
		} else if(isset($this->search_results) && $this->search_results['total_results']!=0 && $this->search_results['from'] <= $this->search_results['to']) {
			if(isset($this->search_results['qry_results'])) // start of result listing
			{
				$result .= '<dl class="results">'."\r\n";
				foreach ($this->search_results['qry_results'] as $_key => $_row)
				{
					$last_domain = $domain_name;
					extract($_row);
					if (Configure::read('show_query_scores') == 0 || Configure::read('sort_results') > '2' || ($wildcount == '1' && Configure::read('query_hits') =='0')) {
						$weight = '';
					} else {
						if (Configure::read('query_hits') == '1') {
							$high_hits = "span class='mak_1 blue'";
							$text = $sph_messages['queryhits'];
							$weight = "<$high_hits>[$text $weight]</$high_hits>";
						} else {
							$weight = '<span class="weight">[$weight %]</span>';
						}
					}
					$result .= '<dt';
					if ($num & 1) {
						$result .= ' class="odrow"';
					}
					$result .= '>';
					
					// if(ceil($num/10) == $num/10) {		// this routine places a "to page top" link on every 10th record
					// 	echo "<a class='navup' href='#top' title='Jump to Page Top'>Top</a>";
					// }
			
					$url_crypt = str_replace("&", "-_-", $url);	   //  crypt the & character
					$title1 = strip_tags($title);
					$url = WEBROOT_DIR.'/click_counter.php?query='.$this->query.'&amp;url='.$url_crypt;	  //  redirect users click in order to update Most Popular Links
					$urlx = $url2;
					
					if(Configure::read('show_search_results_count')==1) {
						$result .= '<span class="result-num">'.$num.'</span>';
					}
					$result .= '
						<span class="result-url">
							<a href="'.$url.'"';
							
							if(Configure::read('pop_result_link')==1) {
								$result .= ' title="'.$sph_messages['New_window'].'" target="_blank"';
							} else {
								$result .= ' title="Visit Site"';
							}
							
							$result .= '>'.($title?$title:$sph_messages['Untitled']).'</a>
							</span>
						</dt>
						<dd';
					if ($num & 1) {
						$result .= ' class="odrow"';
					}
					$result .= '>
						<div class="description">&hellip;'.strip_tags($fulltxt).'&hellip;</div>
						<div class="url">';
					if(Configure::read('show_result_weight')==1) {
						$result .= $weight.' ';
					}
					$result .= '<a href="'.$url.'">'.$urlx.'</a>';
					if(Configure::read('show_page_size')==1) {
						$result .= '<span class="page-size"> - '.$page_size.'</span>';
					}
					$result .= '</div>'."\r\n";
					$result .= '</dd>';
				} // end of result listing
				$result .= '</dl>';
			}
		}
		return $result;
	}

	function pagination($result_label=null) {
		global $sph_messages;
		
		$return = '';
		if(isset($this->search_results['other_pages'])) //	links to other result pages
		{
			if($this->adv==1) {
				$adv_qry = '&amp;adv=1';
			}
			if ($this->type != '') {
				$type_qry = '&amp;type='.$this->type;
			}
			if($result_label===false) {
				// do nothing (don't label the pagination)
			} else if($result_label==null) {
				$return .= $sph_messages["Result page"];
			} else {
				$return .= $result_label;
			}
			
			$return .= '
				<ul>'."\r\n";
			
			if ($this->start > 1) { // if we do have more than 1 result page
				$return .= '<li class="previouslink">
					<a href="'.WEBROOT_DIR.'/?query='.quote_replace($this->addmarks($this->query)).'&amp;start='.$this->search_results['prev'].'&amp;search=1&category='.$category.'&catid='.$catid.'&mark='.Configure::read('mark').'&results='.'&results='.Configure::read('results_per_page').$type_qry.$adv_qry.'&domain='.$domain.'">'.$sph_messages['Previous'].'</a>
				</li>'."\r\n";
			}
			foreach ($this->search_results['other_pages'] as $page_num) {
				if ($page_num !=$this->start)
				{
					$return .= '<li class="pagelink">
							<a href="'.WEBROOT_DIR.'/?query='.quote_replace($this->addmarks($this->query)).'&amp;start='.$page_num.'&amp;search=1&amp;category='.$this->search_results['category'].'&amp;catid='.$this->search_results['catid'].'&amp;mark='.Configure::read('mark').'&amp;results='.Configure::read('results_per_page').$type_qry.$adv_qry.'&amp;domain='.$this->domain.'">'.$page_num.'</a>
						</li>'."\r\n";
				} else {
					$return .= '<li class="pagelink current-page"><span>'.$page_num.'</span></li>';
				}  
			}
			if($this->search_results['next'] <= $this->search_results['pages'])
			{
				$return .= '<li class="nextlink">
						<a href="'.WEBROOT_DIR.'/?query='.quote_replace($this->addmarks($this->query)).'&amp;start='.$this->search_results['next'].'&amp;search=1&amp;category='.$category.'&amp;catid='.$catid.'&amp;mark='.Configure::read('mark').'&amp;results='.Configure::read('results_per_page').$type_qry.$adv_qry.'&amp;domain='.$domain.'">'.$sph_messages['Next'].'</a>
					</li>'."\r\n";
			}
			$return .= '</ul>';
		}
		return $return;
	}
	
	function show_categories() {
		global $sph_messages;
		
		if(Configure::read('show_categories')!=0)
		{
			if(empty($this->search))
			{
				if ($_REQUEST['catid']	&& is_numeric($catid))
				{
					$cat_info = Category::get_category_info($catid);
				} else {
					$cat_info = Category::get_categories_view();
				}
			} else {
				if ($catid && is_numeric($catid)) // category tree
				{
					echo "<div id='results'>
						<p class='mainlist'>".$sph_messages['Back'].":
							<a href='".WEBROOT_DIR."/?setcss1=$thestyle' title='".$sph_messages['tipBackCat']."'>".$sph_messages['Categories']."</a>
						</p>
						<div class='odrow'>
							<p class='title'>
					";
					$acats = "";
					$i = 0;
					foreach ($cat_info['cat_tree'] as $_val){
						$i++;
						$acats .= "<a href='".WEBROOT_DIR."/?catid=".$_val['category_id']."&amp;setcss1=$thestyle' title='".$sph_messages['tipSelCat']."'>".$_val['category']."</a> &raquo; ";
						if ($i > 5) {
							$i = 0;
							$acats = substr($acats,0,strlen($acats)-9)."<br /> &raquo; ";
						}
					}
					$acats = substr($acats,0,strlen($acats)-9);
					echo "$acats</p></div>
					";
	
					if ($cat_info['subcats']) // list of sub-categories
					{
						echo "<p class='mainlist'>".$sph_messages['SubCats']."</p>
							<div class='odrow'><p class='title'>
						";
						$bcats = "";
						foreach ($cat_info['subcats'] as $_key => $_val)
						{
							$bcats .= "<a href='".WEBROOT_DIR."/?catid=".$_val['category_id']."&amp;setcss1=$thestyle' title='".$sph_messages['tipSelBCat']."'>".$_val['category']."</a> (".$_val['count'][0][0].") &raquo; ";
						}
					$bcats = substr($bcats,0,strlen($bcats)-9);
					echo "$bcats</p></div>
						</div>
					";
					} else {
						echo "</div>
						";
					}
		
					// get name of current category
					$result = mysql_query("select category from ".TABLE_PREFIX."categories where category_id = '$catid'");
					if (DEBUG > '0') echo mysql_error();
					$catname = mysql_result($result, 0);
			 
					if (!$cat_info['cat_sites']) {	 // if no site is attached to this cat
						echo "\r\n<p class='mainlist'><a href='".WEBROOT_DIR."' title='".$sph_messages['tipBackCat']."'>".$sph_messages['noSites']." $catname</a></p>\r\n";
	
					} else {  // list of web pages in current category
						echo "<p class='mainlist'>".$sph_messages['Web pages'] . $catname."</p>
						";
			
						foreach ($cat_info['cat_sites'] as $_key => $_val){
							if ($_key & 1) {
								echo "<div class='odrow'>
								";
							} else {
								echo "<div class='evrow'>
								";
							}
							$count = ($_key+1);
							echo "<p class='title'>";
							if(Configure::read('show_search_results_count')) {
								echo "<span class='em sml'>".$count.".</span>";
							}
							echo "
										<a href='".$_val['url']."'>".$_val['title']."</a>
									</p>
									<p class='description'>".$_val['short_desc']."</p>
									<p class='url'>".$_val['url']."</p>
								</div>
							";
						}
						echo "</div></div>
						";
					}
				} else {
					if ($cat_info['main_list']) // category selection
					{
						echo "<div id='results'>
							<div class='headline cntr'><em>".$sph_messages['Categories']."</em></div>
						";
						foreach ($cat_info['main_list'] as $_key => $_val)
						{
							if ($_key & 1)
							{
								echo '<div class="odrow">'."\r\n";
							} else {
								echo '<div class="evrow">'."\r\n";
							}
							echo "<p class='title'>
								<a class='em' href='".WEBROOT_DIR."/?catid=".$_val['category_id']."&amp;setcss1=$thestyle' title='".$sph_messages['tipSelCat']."'>".$_val['category']."</a><br />
							";
							if (is_array($_val['sub']))
							{
								$ccats = "";
								foreach ($_val['sub'] as $__key => $__val)
								{
									$ccats .= "<a href='".WEBROOT_DIR."/?catid=".$__val['category_id']."&amp;setcss1=$thestyle' title='".$sph_messages['tipSelBCat']."'>".$__val['category']."</a> &raquo; ";
								}
								echo $ccats;
							}
							echo '</p>'."\r\n".'</div>'."\r\n";
						}
						echo '</div>'."\r\n";
					}
				}
			}
		}
	}
	
	function show_popular_searches() {
		global $sph_messages;
		
		$return = '';
		if(Configure::read('most_pop') == 1 ) // if selected in Admin settings, show most popular searches
		{
			$bgcolor='odrow';
			$return .= '
				<table cellpadding="0" cellspacing="0">
					<thead>
						<tr>
							<th>'.$sph_messages['query'].'</th>
							<th>'.$sph_messages['count'].'</th>
							<th>'.$sph_messages['results'].'</th>
							<th>'.$sph_messages['lastquery'].'</th>
						</tr>
					</thead>
					<tbody>'."\r\n";
			
			$result=mysql_query("SELECT query, count(*) AS c, date_format(max(time), '%Y-%m-%d %H:%i:%s'), avg(results)  FROM ".TABLE_PREFIX."query_log GROUP BY query ORDER BY c DESC");
			if(DEBUG > '0') echo mysql_error();
			$count = 0;
			while (($row=mysql_fetch_row($result)) && $count < Configure::read('pop_rows'))
			{
				$count++;
				$word = $row[0];
				$times = $row[1];
				$date = $row[2];
				$avg = intval($row[3]);
				$word = str_replace("\"", "", $word);
				$return .= '<tr class="'.$bgcolor.' cntr">
						<td class="pop-query">
							<a href="'.WEBROOT_DIR.'/?query='.$word.'&amp;search=1&amp;type='.$type.'&amp;category='.$category.'&amp;catid='.$catid.'&amp;mark='.Configure::read('mark').'&amp;results=$results">'.$word.'</a>
						</td>
						<td class="pop-times"> '.$times.'</td>
						<td class="pop-results"> '.$avg.'</td>
						<td class="pop-last-queried"> '.$date.'</td>
					</tr>'."\r\n";
				if ($bgcolor=='odrow')
				{
					$bgcolor='';
				} else {
					$bgcolor='odrow';
				}
			}
			$return .= '</tbody>
			</table>'."\r\n";
		}
		return $return;
	}
	
	function add_new_url_link() {
		global $sph_messages;
		
		$return = '';
		if(Configure::read('add_url')==1)
		{ // if selected in Admin settings, allow user to suggest a Url to be indexed
			$return .= '<a href="'.WEBROOT_DIR.'/addurl.php" title="Suggest a new URL to be indexed">'.$sph_messages['suggest'].'</a>';
		}
		
		return $return;
	}
}

?>