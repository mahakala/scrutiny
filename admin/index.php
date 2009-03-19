<?php
/********************************************
* Sphider-plus
* Version 1.7 created 2008-11-27

* Based on original Sphider version 1.3.4
* released: 2008-04-29
* by Ando Saabas     http://www.sphider.eu
*
* This program is licensed under the GNU GPL by:
* Rolf Kellner  [Tec]   sphider(a t)ibk-kellner.de
* Original Sphider GNU GPL licence by:
* Ando Saabas   ando(a t)cs.ioc.ee
********************************************/

	error_reporting (E_ALL ^ E_NOTICE ^ E_WARNING);
	include("admin_header.php");

	if (Configure::read('real_log') == '1') {
		//  Delete old log information and define refresh rate  
		mysql_query ("truncate ".TABLE_PREFIX."real_log");
		if (DEBUG > '0') echo mysql_error();        
		mysql_query ("insert ".TABLE_PREFIX."real_log set `url`='' , `real_log`='' , `refresh` =".Configure::read('refresh'));
		if (DEBUG > '0') echo mysql_error();       
	}

	extract(getHttpVars());
	if (!isset($f)) {
		$f=2;
	}

	$site_funcs     = array(22=> "default",21=> "default",4=> "default", 19=> "default", 1=> "default", 2 => "default", "add_site" => "default", 20=> "default", 28=> "default", 30=> "default", 40=> "default", 45=> "default", 50=> "default", 51=> "default", "edit_site" => "default", 5=>"default");
	$stat_funcs     = array("statistics" => "default",  "delete_log"=> "default");
	$settings_funcs = array("settings" => "default", 41=> "default");
	$index_funcs    = array("index" => "default");
	$clean_funcs    = array("clean" => "default", 15=>"default", 16=>"default", 17=>"default", 23=>"default");
	$cat_funcs      = array(11=> "default", 10=> "default", "categories" => "default", "edit_cat"=>"default", "delete_cat"=>"default", "add_cat" => "default", 7=> "default");
	$database_funcs = array("database" => "default");

    echo "
        <div id='admin'>
        <div id='tabs'>
		<ul>
    ";
    
    if ($stat_funcs[$f] ) {
        $stat_funcs[$f] = "selected";
    } else {
        $stat_funcs[$f] = "default";
    }

    if ($site_funcs[$f] ) {
        $site_funcs[$f] = "selected";
    }else {
        $site_funcs[$f] = "default";
    }

    if ($settings_funcs[$f] ) {
        $settings_funcs[$f] = "selected";
    } else {
        $settings_funcs[$f] = "default";
    } 

    if ($index_funcs[$f] ) {
        $index_funcs[$f]  = "selected";
    } else {
        $index_funcs[$f] = "default";
    }

    if ($cat_funcs[$f] ) {
        $cat_funcs[$f]  = "selected";
    } else {
        $cat_funcs[$f] = "default";
    }

    if ($clean_funcs[$f] ) {
        $clean_funcs[$f]  = "selected";
    } else {
        $clean_funcs[$f] = "default";
    }

    if ($database_funcs[$f] ) {
        $database_funcs[$f]  = "selected";
    } else {
        $database_funcs[$f] = "default";
    }
    echo "<li><a title='Manage Sites' href='".WEBROOT_DIR."/admin/?f=2' class='$site_funcs[$f]'>Sites</a></li>
        <li><a title='Manage Categories' href='".WEBROOT_DIR."/admin/?f=categories' class='$cat_funcs[$f]'>Categories</a></li>
        <li><a title='Indexing Options' href='".WEBROOT_DIR."/admin/?f=index' class='$index_funcs[$f]'>Index</a></li>
        <li><a title='Database Cleaning Options' href='".WEBROOT_DIR."/admin/?f=clean' class='$clean_funcs[$f]'>Clean</a> </li>
        <li><a title='Main Settings' href='".WEBROOT_DIR."/admin/?f=settings' class='$settings_funcs[$f]'>Settings</a></li>
        <li><a  name='head' title='Indexing Statistics' href='".WEBROOT_DIR."/admin/?f=statistics' class='$stat_funcs[$f]'>Statistics</a> </li>
        <li><a title='Display Database Contents' href='".WEBROOT_DIR."/admin/?f=database' class='$database_funcs[$f]'>Database</a></li>
        <li><a title='Close Sphider' href='".WEBROOT_DIR."/admin/?f=24' class='default'>Log out</a></li>
        </ul>
    	</div>
    	<div id='main'>
	";

	function walk_through_cats($parent, $lev, $site_id) {
		$cattype = "Category";
		$inputclass = "";
		for ($x = 0; $x < $lev; $x++) {
			$cattype ="Sub-Category";
			$inputclass = " ";
		}
		$query = "SELECT * FROM ".TABLE_PREFIX."categories WHERE parent_num=$parent ORDER BY category";
		$result = mysql_query($query);
		if (DEBUG > '0') echo mysql_error();
		if (mysql_num_rows($result) <> '') {
			$n = 1;
			while ($row = mysql_fetch_array($result)) {
				$id = $row['category_id'];
				$cat = $row['category'];
				$state = '';
				if ($site_id <> '') {
					$result2 = mysql_query("select * from ".TABLE_PREFIX."site_category where site_id=$site_id and category_id=$id");
					if (DEBUG > '0') echo mysql_error();
					$rows = mysql_num_rows($result2);
					if ($rows > 0) {
						$state = " checked='checked'";
					}
				}
				if (!$inputclass =="") {
					while ($n < $lev) { 
						$inputclass .= "<span class='tree'>&raquo;</span>";
						$n++;
					}
					echo "
                        $inputclass&nbsp;<span title='Sub-category'>            
                        <input class='catlist' title='Click to select/deselect this sub-category' type='checkbox' name='cat[$id]' id='cat$id' ".$state."
                        />&nbsp;".$cat."</span><br />
                    ";
				} else {
                    echo "<label class='em' for='cat$id'>$cattype</label>
                        <input type='checkbox' title='Click to select/deselect this Category' name='cat[$id]' id='cat$id' ".$state."
                        /><span class='em warnok' title='Category Root'>".$cat."</span><br />
                    ";
				}
				walk_through_cats($id, $lev + 1, $site_id);
			}
		}
	}

	function addcatform($parent) {
		$par2 = "";
		$par2num = "";
		echo "<div class='submenu cntr'>| Add New Category Form |</div>
        ";
		if ($parent=='') {
			$par='(Top level)';
		} else {
			$query = "SELECT category, parent_num FROM ".TABLE_PREFIX."categories WHERE category_id='$parent'";
			$result = mysql_query($query);
			if (!mysql_error()) {
				if ($row = mysql_fetch_row($result)) {
					$par=$row[0];
					$query = "SELECT Category_ID, Category FROM ".TABLE_PREFIX."categories WHERE Category_ID='$row[1]'";
					$result = mysql_query($query);
					if (DEBUG > '0') echo mysql_error();
					if (mysql_num_rows($result)<>'') {
						$row = mysql_fetch_row($result);
						$par2num = $row[0];
						$par2 = $row[1];
					} else {
						$par2 = "Top level";
					}
				}
			} else {
				if (DEBUG > '0') echo mysql_error();
			}
			echo "
            ";
		}

		echo "<div class='panel x1'>
			<form class='txt' action='index.php' method='post'>
			<input type='hidden' name='f' value='7' />
			<input type='hidden' name='parent' value='".$parent."' />
			<div class='cntr tblhead'>Parent: <a href='".WEBROOT_DIR."/admin/?f=add_cat&amp;parent=$par2num'>$par2</a> &raquo; ".stripslashes($par)."</div>
			<br />
		";
		$query = "SELECT category_ID, Category FROM ".TABLE_PREFIX."categories WHERE parent_num='$parent'";
		$result = mysql_query($query);
		if (DEBUG > '0') echo mysql_error();
		if (mysql_num_rows($result)>0) {
			$subcats ="y";
			echo "<fieldset><legend>[ Create new subcategory under ]</legend>
				<div class='odrow'>&bull;
			";
		}
		$acats = "";
		while ($row = mysql_fetch_row($result)) {
			$acats .="<a title='Select as Main Category for new Sub-Category' href='".WEBROOT_DIR."/admin/?f=add_cat&amp;parent=".$row[0]."'>".stripslashes($row[1])."</a>&bull;&nbsp;";
		}
		$acats = substr($acats,0,strlen($acats)-13);
		echo $acats;
		if ($subcats=="y") {
			echo "</div>
				</fieldset>
			";
		}
		echo "<div class='w75'>
			<fieldset><legend>[ New category ]</legend>
			<label for='category'>Enter Category Name</label>
			<input type='text' name='category' id='category' size='40' title='Click and type in category name' />
			</fieldset>
			<fieldset><legend>[ Save ]</legend>
			<input type='submit' id='submit' value='Add New Category' title='Click to add New Category' />
			</fieldset></div></form>
			</div>
		";
	}


	function addcat ($category, $parent) {
			if ($category=="") return;
		$category = addslashes($category);
		if ($parent == "") {
			$parent = 0;
		}
		$query = "INSERT INTO ".TABLE_PREFIX."categories (category, parent_num) VALUES ('$category', ".$parent.")";
		mysql_query($query);
		if (!mysql_error()) {
			return "<p class='cntr'>Category <span class='em'>$category</span> now added...</p><br />";
		} else {
			return mysql_error();
		}
	}

	function approve_newsites() {
		echo "<div class='submenu cntr'>| Sites for Approval |</div>
			<div class='tblhead'>
			<p>\n\n</p>
		";

		$query = "SELECT * FROM `".TABLE_PREFIX."addurl` LIMIT 0 , 30";
		$result = mysql_query($query);
		if (DEBUG > '0') echo mysql_error();
		$count = 1;
		
		if (mysql_num_rows($result) <> '') {
			while ($row = mysql_fetch_array($result)) {
				echo "Site $count awaiting approval:
					<p>\n\n</p>
					<form action=index.php method=post>
						<input type=hidden name=f value=29>
						<table width='80%'>
							<tr class='odrow'>
								<td>
									Url: 
								</td>
								<td class='left' >
									<input size=50 type=text name=\"url\" value=\"".$row['url']."\">
									&nbsp;&nbsp;
									<a target=\"_blank\" href=\"".$row['url']."\">visit</a>
								</td>
							</tr>
							<tr class='odrow'>
								<td>
									Title: 
								</td>
								<td class='left'>
									<input size=50 type=text name=\"title\" value=\"".$row['title']."\">
								</td>
							</tr>
							<tr class='odrow'
								<td>
									Description: 
								</td>
								<td class='left' >
									<textarea rows=5 name=short_desc cols=38>" .$row['description']."</textarea>
								</td>
							</tr>
							";
				if(Configure::read('show_categories') =='1')
				{
					echo "
						<tr class='odrow'>
							<td>
							Category: 
							</td>
							<td class='left' ><select name=\"cat\">
						";
					$category_id = $row['category_id'];
					list_catsform (0, 0, "white", "", $category_id);
					echo "
							</select>
							</td>
						</tr>
					";
				}
				echo "<tr class='odrow'>
						<td>suggested: 
						</td>
						<td class='left' ><input size=50 type=text name=\"created\" value=\"".$row['created']."\">
						</td>
					</tr>
					<tr class='odrow'>
						<td>by: 
						</td>
						<td class='left' ><input size=50 type=text name=\"dispatcher\" value=\"".$row['account']."\">
						</td>
					</tr>
					</table>
					<table width=\"80%\">
						<tr class=\"x1 cntr odrow\">
							<td>
								<input type=\"submit\" name=\"approve\" value=\"Approve\" />&nbsp;&nbsp;&nbsp;&nbsp;
								<input type=\"submit\" name=\"delete\" value=\"Reject\" />&nbsp;&nbsp;&nbsp;&nbsp;
								<input type=\"submit\" name=\"bann\" value=\"Ban !\" />
								<input type=\"hidden\" name=\"domain\" value=\"".$row['url']."\" />
							</td>
						</tr>
					</table>
					</dd>
					</form>
					<p>\n\n\n</p>
					";
					$count++;  
			}
		}
		echo "
			</div>
		";
	}

	function banned_domains ($valid) {

		// Headline for Banned Domain Manager
		echo "<div class='submenu cntr'>| Banned domain Manager |</div>
			<div class='tblhead'>
			<p>\n</p>
		";

		if ($valid != '1') {
			echo "<div class='warnadmin cntr'>Invalid input for Banned domain name.</div>
				<p>\n</p>
			";        
		} else {
			echo "<table width='80%'>
				<tr class='headline x3 cntr'>
					<td>Banned domain</td>
					<td>Banned since</td>
					<td>Delete</td>
				</tr>
			";
			$bgcolor='odrow';
			$count_backup = 0;
 
			$Bquery = "SELECT * FROM `".TABLE_PREFIX."banned`ORDER By domain LIMIT 0 , 3000";
			$Bresult = mysql_query($Bquery);
			if (DEBUG > '0') echo mysql_error();

			if (mysql_num_rows($Bresult) <> '') {
				while ($Brow = mysql_fetch_array($Bresult)) {
					echo "<tr class='$bgcolor cntr'>
							<td>".$Brow['domain']."</td>
							<td>".$Brow['created']."</td>
							<form action=index.php method=post>
							<input type=hidden name=f value=31>
							<td><input type=hidden name=domain value=\"".$Brow['domain']."\">
							<input type=submit title='Click to permanently delete from database' value=\"Remove\"></td>
							</form>
						</tr>
					";
					if ($bgcolor=='odrow') {
						$bgcolor='evrow';
					} else {
						$bgcolor='odrow';
					}
				}
			} else {
				echo "<tr><td class='warnadmin red cntr'>No domains banned</td>
					<td class='odrow cntr'>&nbsp;</td>
					<td class='odrow cntr'>&nbsp;</td>
				</tr>
				";
			} 
			echo "
				</table>
				<p>\n</p>
				</div>
			";
			}
		echo "<p>\n</p>
			<div class='tblhead'>
				<p>\n</p>
				<form action=index.php method=post><input type=hidden name=f value=32>
				<div class='panel x2 cntr'>
					<p class='evrow cntr'>Add a new domain to be banned\n\n
					<input type=text name=\"new_banned\" size='20' maxlength='20'>
					<input type=submit value=\"Add\">
					</p>
				</div>
				<p>\n</p>
			</div>
			<p>\n</p>
		";
	}

	function addsiteform() {
		echo "<div class='submenu cntr'>
			<ul>
				<li><a href='javascript:history.go(-1)' title='Go back a Page'>Back</a></li>
			</ul>
		</div>
			<div class='panel'>
			<form class='txt' action='index.php' method='post'>
				<input type='hidden' name='f' value='1' />
				<fieldset>
					<legend>Add New Site</legend>
					
					<label class='em' for='url'>URL:</label>
					<input type='text' name='url' id='url' title='Enter New URL' size='60' value ='http://' />
					
					<label class='em' for='title'>Site Title:</label>
					<input type='text' name='title' id='title' title='Enter Web Site title' size='60' maxlength='60' />
					
					<label class='em' for='short_desc'>Short description:</label>
					<input type='text' name='short_desc' id='short_desc' title='Enter short site description' size='90' maxlength='90' />
				</fieldset>
		";
		$result = mysql_query("select count(site_id) from ".TABLE_PREFIX."sites");
		if (!$result = 0) {
			echo "<fieldset><legend>Category Selection</legend>\n";
			walk_through_cats(0, 0, '');
			echo "</fieldset>\n";
		}
		echo "
					<fieldset>
						<input type='submit' id='submit' value='Add New Site' title='Click to Add New Site for indexing' />
					</fieldset>
				</form>
			</div>
			";
	}

	function editsiteform($site_id) {
		$result = mysql_query("SELECT site_id, url, title, short_desc, spider_depth, required, disallowed, can_leave_domain from ".TABLE_PREFIX."sites where site_id=$site_id");
		if (DEBUG > '0') echo mysql_error();
		$row = mysql_fetch_array($result);
		$depth = $row['spider_depth'];        
		$fullchecked = "";
		$depthchecked = "";
		if ($depth == -1 ) {
			$fullchecked = 'checked="checked"';
			$depth ="";
		} else {
			$depthchecked = 'checked="checked"';
		}
		$leave_domain = $row['can_leave_domain'];
		if ($leave_domain == 1 ) {
			$domainchecked = 'checked="checked"';
		} else {
			$domainchecked = "";
		}
		echo "<div class='submenu em'>
			<ul>
				<li><a href='".WEBROOT_DIR."/admin/?f=20&amp;site_id=".$row['site_id']."' title='Go back a Page'>Back</a></li>
			</ul>
		</div>
				<div class='panel w75'>
				<form class='txt' action='index.php' method='post'>
					<input type='hidden' name='f' value='4' />
					<input type='hidden' name='site_id' value='$site_id' />
					<fieldset class='mains'>
						<legend>Edit Site Details</legend>
						<label class='em' for='url'>URL:</label>
						<input type='text' name='url' id='url' title='Enter URL' value='".$row['url']."' />
						<label class='em' for='title'>Title:</label>
						<input type='text' name='title' id='title' title='Enter Web Site title' value='".stripslashes($row['title'])."' />
						<label class='em' for='short_desc'>Short description:</label>
						<input type='text' name='short_desc' id='short_desc' title='Enter short site description' maxlength='68' value='".stripslashes($row['short_desc'])."' />
					</fieldset>
					<fieldset class='radiobuttons'>
						<legend>Spidering options:</legend>
						<input type='radio' name='soption' id='soption' value='full' $fullchecked /> <label for='soption'>Full</label><br />
						<input type='radio' name='soption' id='soptionlevel' value='level' $depthchecked /> <label for='soptionlevel'>Index Depth:</label> <input type='text' name='depth' size='2' value='$depth' />
					</fieldset>
					<fieldset class='checkbox'>
						<legend>Spider can leave domain?</legend>
						<input type='checkbox' name='domaincb' id='domaincb' value='1' title='Check box if Sphider can leave above domain' $domainchecked /> <label for='domaincb'>Check for Yes</label>
					</fieldset>
					<fieldset class='mains'>
						<legend>Include/Exclude Options</legend>
						<label class='em' for='in'>URL Must include:</label>
						<textarea name='in' id='in' cols='45' rows='5' title='Enter URLs that Must be included, one per line'>".$row['required']."</textarea>
						<label class='em' for='out'>URL must Not include:</label>
						<textarea name='out' cols='45' rows='5' title='Enter URLs that Must Not be included, one per line'>".$row['disallowed']."</textarea>
		";
		walk_through_cats(0, 0, $site_id);
		echo "</fieldset>
				<fieldset>
					<input type='submit'  id='submit'  value='Update'  title='Click to confirm Site Edit update' />
				</fieldset>
			</form>
		</div>
		";
	}

	function editsite ($site_id, $url, $title, $short_desc, $depth, $required, $disallowed, $domaincb, $cat) {
		$short_desc = addslashes($short_desc);
		$title = addslashes($title);
		mysql_query("delete from ".TABLE_PREFIX."site_category where site_id=$site_id");
		if (DEBUG > '0') echo mysql_error();
		$compurl=parse_url($url);
		if ($compurl['path']=='') {
			$url=$url."/";
		}
		mysql_query("UPDATE ".TABLE_PREFIX."sites SET url='$url', title='$title', short_desc='$short_desc', spider_depth =$depth, required='$required', disallowed='$disallowed', can_leave_domain='$domaincb' WHERE site_id=$site_id");
		if (DEBUG > '0') echo mysql_error();
		$result=mysql_query("select category_id from ".TABLE_PREFIX."categories");
		if (DEBUG > '0') echo mysql_error();
		while ($row=mysql_fetch_row($result)) {
			$cat_id=$row[0];
			if ($cat[$cat_id]=='on') {
				mysql_query("INSERT INTO ".TABLE_PREFIX."site_category (site_id, category_id) values ('$site_id', '$cat_id')");
				if (DEBUG > '0') echo mysql_error();
			}
		}
		if (!mysql_error()) {
			return "<p class='msg'>Site Indexing Options updated...</p>" ;
		} else {
			return mysql_error();
		}
	}

	function editcatform($cat_id) {
		$result = mysql_query("SELECT category FROM ".TABLE_PREFIX."categories where category_id='$cat_id'");
		if (DEBUG > '0') echo mysql_error();
		$row=mysql_fetch_array($result);
		$category=$row[0];
		echo "<div class='submenu cntr'>| Edit category |</div>
			<div class='panel x2'>
			<form class='txt' action='index.php' method='post'>
				<input type='hidden' name='f' value='10'
				/>
				<input type='hidden' name='cat_id' value='".$cat_id."'
				/>
				<fieldset><legend>[ Edit Index Category ]</legend>
				<label class='em' for='category'>Category:</label>
				<input type='text' name='category' id='category' value='$category' size='40'
				/></fieldset>
				<fieldset><legend>[ Save Category Edit ]</legend>
				<input class='sbmt' type='submit'  id='submit'  value='Update'
				/></fieldset>
			</form>
		</div>
		";
		}

	function editcat ($cat_id, $category) {
		$qry = "UPDATE ".TABLE_PREFIX."categories SET category='".addslashes($category)."' WHERE category_id='$cat_id'";
		mysql_query($qry);
		if (!mysql_error())
		{
			return "<p class='msg'>Category updated...</p>";
		} else {
			return mysql_error();
		}
	}

	function showsites($f) {
		global $start, $site_funcs;

		if (Configure::read('sites_alpha') == 1 ) {   // sort Admin Sites table in alphabetic order
			$result = mysql_query("SELECT site_id, url, title, indexdate from ".TABLE_PREFIX."sites ORDER By url, indexdate");        
		} else {    //sort  Admin Sites table by indexdate         
			$result = mysql_query("SELECT site_id, url, title, indexdate from ".TABLE_PREFIX."sites ORDER By indexdate, title");
		}

		if(DEBUG > '0') echo mysql_error();
		echo "<div class='submenu y5'>
			<ul>
			<li><a href='".WEBROOT_DIR."/admin/?f=add_site' title='Add new site for indexing'>&nbsp;&nbsp;Add site&nbsp;&nbsp;</a></li>
			<li><a href='".WEBROOT_DIR."/admin/?f=40' title='Import Url list from folder: urls'>&nbsp;&nbsp;Import / Export Url list&nbsp;&nbsp;</a></li>
		";
		if (mysql_num_rows($result) > 0) {
			if (DEBUG == '0') {
				error_reporting(0);  //     suppress  PHP messages  
			} else {
				error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
			}

			echo "
				<li><a href='".WEBROOT_DIR."/admin/?f=51'' title='Index all the sites not jet indexed'>&nbsp;&nbsp;Index only the new&nbsp;&nbsp;</a></li>
				<li><a href='".WEBROOT_DIR."/admin/?f=50' title='Re-index all sites'>&nbsp;&nbsp;Re-index all&nbsp;&nbsp;</a></li>
				<li><a href='".WEBROOT_DIR."/admin/?f=45' title='Erase entire existing index then Re-index'
				onclick=\"return confirm('Are you sure you want to Erase? Site details will be kept but all Indexing information will be lost!')\">&nbsp;&nbsp;Erase &amp; Re-index all&nbsp;&nbsp;</a></li>
				<li><a href='".WEBROOT_DIR."/admin/?f=28' title='Approve sites suggested by user'>&nbsp;&nbsp;Approve sites&nbsp;&nbsp;&nbsp;</a></li>
				<li><a href='".WEBROOT_DIR."/admin/?f=30' title='Banned domains Manager'>&nbsp;&nbsp;Banned domains&nbsp;&nbsp;&nbsp;</a></li>
				<br />
			";
		}
		echo "</ul>
			</div>
		";

		// Prepare header and all results for listing
		// Configure::read('results_per_page') = '100'; // if you prefer another count than used for Sphiders result pages, uncomment this row and place your count of URLs per page here.
		$num_rows = mysql_num_rows($result); 
		$pages = ceil($num_rows / Configure::read('results_per_page'));   // Calculate count of required pages 

		if (empty($start)) $start = '1';                // As $start is not jet defined this is required for the first result page 
		if ($start == '1') {
			$from = '0';                                // Also for first page in order not to multipy with 0 
		} else { 
		$from = ($start-1) * Configure::read('results_per_page');         // First $num_row of actual page 
		} 

		$to = $num_rows;                                // Last $num_row of actual page 
		$rest = $num_rows - $start; 
		if ($num_rows > Configure::read('results_per_page')) {            // Display more then one page? 
			$rest = $num_rows - $from; 
			$to = $from + $rest;                        // $to for last page 
			if ($rest > Configure::read('results_per_page')) $to = $from + (Configure::read('results_per_page')); // Calculate $num_row of actual page 
		} 

		if (mysql_num_rows($result) > 0) {
			$fromm = $from+1;                             
			echo "<div class='panel'>
			<div class='tblhead'>Displaying URLs $fromm - $to&nbsp;&nbsp;from $num_rows</div>
			<table width='100%'>
				<tr>
					<td class='headline x5'>Site name</td>
					<td class='headline'>Site Url</td>
					<td width='16%' class='headline'>Last indexed</td>
					<td width='9%' class='headline'>Site</td>
				</tr>
    		";
		} else {
			echo "
				<div class='cntr'>
				<p class='em'>
				Welcome to the Sphider-plus Admin section!
				<br /><br />
				At present there are no sites available in the database. So:
				<br /><br /><br />
				- Choose <a href='".WEBROOT_DIR."/admin/?f=add_site' title='Add new site for indexing'>'Add site'</a> from the submenu to add a new site, or...
				<br />
				<br />
				- Choose <a href='".WEBROOT_DIR."/admin/?f=40' title='Import Url list from folder: urls'
				onclick=\"return confirm('Are you sure you want to import? Current Url table will be lost and overwritten!')\">'Import Url list'</a> if currently available, or...
				<br />
				<br />                
				- Choose <a href='".WEBROOT_DIR."/admin/?f=index' title='Index directly a site'>'Index'</a> to directly go to the indexing section.</p>
				<p class='txt cntr warnadmin'>In either case you may like to review the <a href='".WEBROOT_DIR."/admin/?f=settings' title='Define all settings'>'Settings'</a> page first!</p>
				<br />
				</div>
			";
		}
		$class = "evrow";
		for ($i=$from; $i<$to; $i++) { 
			$site_id = mysql_result($result, $i, "site_id"); 
			$site_url = mysql_result($result, $i, "url"); 
			$title = mysql_result($result, $i, "title"); 
			$indexdate = mysql_result($result, $i, "indexdate"); 
			
			if ($indexdate=='') {
				$indexstatus="<span class='warnadmin cntr'>Not indexed</span>";
				$indexoption="<a href='".WEBROOT_DIR."/admin/?f=index&amp;url=$site_url' title='Click to start indexing this site'>Index</a>";
			} else {
				$result2 = mysql_query("SELECT site_id from ".TABLE_PREFIX."pending where site_id =$site_id");
				if (DEBUG > '0') echo mysql_error();
				$row2=mysql_fetch_array($result2);
				if ($row2['site_id'] == $site_id) {
					$indexstatus = "<span class='warn cntr'>Unfinished</span>";
					$indexoption="<a href='".WEBROOT_DIR."/admin/?f=index&amp;url=$site_url' title='Click to continue interrupted indexing'>Continue</a>";
				} else {
					$indexstatus = $indexdate;
					$indexoption="<a href='".WEBROOT_DIR."/admin/?f=index&amp;url=$site_url&amp;reindex=1' title='Click to start Re-indexing this site'>Re-index</a>";
				}
			}
			if ($class =="evrow") {
				$class = "odrow";
			}else{ 
				$class = "evrow";
			}
			
			echo "<tr class='$class'>
				<td>".stripslashes($title)."</td>
				<td><a href='".$site_url."' target='_blank' title='Visit site in new window'>".rtrim(substr($site_url,0,54))."</a></td>
				<td class='cntr08'>$indexstatus</td>
				<td class='options'><a href='".WEBROOT_DIR."/admin/?f=20&amp;site_id=$site_id' class='options' title='Click to browse site options'>Options</a>
				</td></tr>
			";
		}

			// Display end of table
		if ($num_rows > 0) {
			echo "</table>
				</div>
			"; 

			if ($pages > 1) { // If we have more than 1 result-page 
				echo " 
					<div class='submenu cntr'>
					Result page: $start from $pages&nbsp;&nbsp;&nbsp;
				";

				if($start > 1) { // Display 'First' 
					echo "
						<a href='".WEBROOT_DIR."/admin/?f=2&start=1&id=' print $site_funcs[$f]'>First</a>&nbsp;&nbsp;
					";
				
					if ($start > 5 ) { // Display '-5' 
						$minus = $start-5;
						echo " 
							<a href='".WEBROOT_DIR."/admin/?f=2&start=$minus&id=$site_funcs[$f]'>- 5</a>&nbsp;&nbsp; 
						"; 
					} 
				}
			
				if($start > 1) { // Display 'Previous' 
					$prev = $start-1;
					echo " 
						<a href='".WEBROOT_DIR."/admin/?f=2&start=$prev&id=$site_funcs[$f]'>Previous</a>&nbsp;&nbsp; 
					";
				} 
				
				if($rest >= Configure::read('results_per_page')) { // Display 'Next'
					$next = $start+1;
					echo "
						<a href='".WEBROOT_DIR."/admin/?f=2&start=$next&id=$site_funcs[$f]' >Next</a>&nbsp;&nbsp; 
					";
					
					if ($pages-$start > 5 ) { // Display '+5'
						$plus = $start+5;
						echo "
							<a href='".WEBROOT_DIR."/admin/?f=2&start=$plus&id=$site_funcs[$f]'>+ 5</a>&nbsp;&nbsp; 
						";
					}
				}
				
				if($start < $pages) { // Display 'Last'
					echo "
						<a href='".WEBROOT_DIR."/admin/?f=2&start=$pages&id=$site_funcs[$f]'>Last</a> 
					";
				}
				
				echo "</div>";
			}
		}
	}

	function deletecat($cat_id) {
		$list = implode(",", get_cats($cat_id));
		mysql_query("delete from ".TABLE_PREFIX."categories where category_id in ($list)");
		if (DEBUG > '0') echo mysql_error();
		mysql_query("delete from ".TABLE_PREFIX."site_category where category_id=$cat_id");
		if (DEBUG > '0') echo mysql_error();
		return "<p class='msg'>Category deleted.</p>";
	}

	function deletesite($site_id) {
		mysql_query("delete from ".TABLE_PREFIX."sites where site_id=$site_id");
		if (DEBUG > '0') echo mysql_error();
		mysql_query("delete from ".TABLE_PREFIX."site_category where site_id=$site_id");
		if (DEBUG > '0') echo mysql_error();
		$query = "select link_id from ".TABLE_PREFIX."links where site_id=$site_id";
		$result = mysql_query($query);
		if (DEBUG > '0') echo mysql_error();
		$todelete = array();
		while ($row=mysql_fetch_array($result)) {
			$todelete[]=$row['link_id'];
		}

		if (count($todelete)>0) {
			$todelete = implode(",", $todelete);
			for ($i=0;$i<=15; $i++) {
				$char = dechex($i);
				$query = "delete from ".TABLE_PREFIX."link_keyword$char where link_id in($todelete)";
				mysql_query($query);
				if (DEBUG > '0') echo mysql_error();
			}
		}

		mysql_query("delete from ".TABLE_PREFIX."links where site_id=$site_id");
		if (DEBUG > '0') echo mysql_error();
		mysql_query("delete from ".TABLE_PREFIX."pending where site_id=$site_id");
		if (DEBUG > '0') echo mysql_error();
		return "<br /><p class='msg'>Site deleted...</p>";
	}

	function deletePage($link_id) {
		mysql_query("delete from ".TABLE_PREFIX."links where link_id=$link_id");
		if (DEBUG > '0') echo mysql_error();
		for ($i=0;$i<=15; $i++) {
			$char = dechex($i);
			mysql_query("delete from ".TABLE_PREFIX."link_keyword$char where link_id=$link_id");
		}
		if (DEBUG > '0') echo mysql_error();
		return "<br /><p class='msg'>Page deleted...</p>";
	}

	
	function cleanTemp() {
		$result = mysql_query("delete from ".TABLE_PREFIX."temp where level >= 0");
		if (DEBUG > '0') echo mysql_error();
		$del = mysql_affected_rows();
		echo "<div class='submenu'>&nbsp;</div>
			<p class='msg'>Temp table cleared [<span class='warnok'> $del </span>] items deleted.</p>
			<br />
			<a class='bkbtn' href='".WEBROOT_DIR."/admin/?f=clean' title='Go back to Clean'>Back</a>
		";
	}

	function clearLog() {
		$result = mysql_query("delete from ".TABLE_PREFIX."query_log where time >= 0");
		if (DEBUG > '0') echo mysql_error();
		$del = mysql_affected_rows();
		echo "<div class='submenu'>&nbsp;</div>
			<p class='msg'>Search log cleared [<span class='warnok'> $del </span>] items deleted.</p>
			<br />
 			<a class='bkbtn' href='".WEBROOT_DIR."/admin/?f=clean' title='Go back to Clean'>Back</a>
		";
	}

	function clearBestClick() {
		$result = mysql_query("update ".TABLE_PREFIX."links set click_counter= 0, last_click= 0, last_query= '' where click_counter OR last_click > 0");
		if (DEBUG > '0') echo mysql_error();
		$del = mysql_affected_rows();
		echo "<div class='submenu'>&nbsp;</div>
			<p class='msg'>Most Popular Links cleared [<span class='warnok'> $del </span>] items deleted.</p>
			<br />
			<a class='bkbtn' href='".WEBROOT_DIR."/admin/?f=clean' title='Go back to Clean'>Back</a>
		";
	}

	function clearSpLog() {     //      Bulk delete for all spider log files
		$i = '0'; 
		
		if (is_dir(LOG_DIR)) {
			if ($dh = opendir(LOG_DIR)) {
				while (($logfile = readdir($dh)) !== false) {
					if (eregi("\.log$", $logfile) || eregi("\.html$", $logfile)) {  //	  only *.html and *.log are valid log-files
						@unlink(LOG_DIR."/$logfile");	//	  delete this log file
						$i++ ;	  //	  count all log-files
					}
				}
				closedir($dh);
			}
			echo "<div class='submenu'>&nbsp;</div>
				<p class='warnok'>Spider log cleared. [<span class='warnok'> $i </span>] files deleted.</p>
				<a class='bkbtn' href='".WEBROOT_DIR."/admin/?f=clean' title='Go back to Clean'>Back</a>
			";
		} else {
			echo "<p class='warnadmin'><br />
        		Folder '".LOG_DIR."' does not exist.<br />
        		No files deleted.</p>
    		";
		}
	}

	function cleanLinks() {
		$query = "select site_id from ".TABLE_PREFIX."sites";
		$result = mysql_query($query);
		if (DEBUG > '0') echo mysql_error();
		$todelete = array();
		if (mysql_num_rows($result)>0) {
			while ($row=mysql_fetch_array($result)) {
				$todelete[]=$row['site_id'];
			}
			$todelete = implode(",", $todelete);
			$sql_end = " not in ($todelete)";
		}
		
		$result = mysql_query("select link_id from ".TABLE_PREFIX."links where site_id".$sql_end);
		if (DEBUG > '0') echo mysql_error();
		$del = mysql_num_rows($result);
		while ($row=mysql_fetch_array($result)) {
			$link_id=$row[link_id];
			for ($i=0;$i<=15; $i++) {
				$char = dechex($i);
				mysql_query("delete from ".TABLE_PREFIX."link_keyword$char where link_id=$link_id");
				if (DEBUG > '0') echo mysql_error();
			}
			mysql_query("delete from ".TABLE_PREFIX."links where link_id=$link_id");
			if (DEBUG > '0') echo mysql_error();
		}

		$result = mysql_query("select link_id from ".TABLE_PREFIX."links where site_id is NULL");
		if (DEBUG > '0') echo mysql_error();
		$del += mysql_num_rows($result);
		while ($row=mysql_fetch_array($result)) {
			$link_id=$row[link_id];
			for ($i=0;$i<=15; $i++) {
				$char = dechex($i);
				mysql_query("delete from ".TABLE_PREFIX."link_keyword$char where link_id=$link_id");
				if (DEBUG > '0') echo mysql_error();
			}
			mysql_query("delete from ".TABLE_PREFIX."links where link_id=$link_id");
			if (DEBUG > '0') echo mysql_error();
		}
		echo "<div class='submenu'>&nbsp;</div>
			<p class='msg'>Links table cleared [<span class='warnok'> $del </span>] links deleted.</p>
			<br />
			<a class='bkbtn'  href='".WEBROOT_DIR."/admin/?f=clean' title='Go back to Clean'>Back</a>
		";
	}

	function cleanCats() {
		$del ='0';
		$result = mysql_query("select * from ".TABLE_PREFIX."site_category");
		if (DEBUG > '0') echo mysql_error();
		if (mysql_num_rows($result)>0) {
			while ($rows= mysql_fetch_array($result)) {
				$category_id=$rows[category_id];
				$site_id=$rows[site_id];
				if (!$site_id) {    //  delete all cats without any association
					mysql_query("delete from ".TABLE_PREFIX."site_category where category_id=$category_id");
					if (DEBUG > '0') echo mysql_error();
					$del++;
				} else {
					$res = mysql_query("select * from ".TABLE_PREFIX."sites where site_id=$site_id");
					if (DEBUG > '0') echo mysql_error();
					$site = mysql_num_rows($res);
					if (!$site) {    //  delete all cats without association to a valid site
						mysql_query("delete from ".TABLE_PREFIX."site_category where category_id=$category_id");
						if (DEBUG > '0') echo mysql_error();
						$del++;
					}
				}
			}
		}
		echo "<div class='submenu'>&nbsp;</div>
			<p class='msg'>Category table cleared [<span class='warnok'> $del </span>] entries deleted.</p>
			<br />
			<a class='bkbtn' href='javascript:history.go(-1)' title='Go back a Page'>Back</a>
		";
	}

	function cleanKeywords() {
		$query = "select keyword_id, keyword from ".TABLE_PREFIX."keywords";
		$result = mysql_query($query);
		if (DEBUG > '0') echo mysql_error();
		$del = 0;
		while ($row=mysql_fetch_array($result)) {
			$keyId=$row['keyword_id'];
			$keyword=$row['keyword'];
			$wordmd5 = substr(md5($keyword), 0, 1);
			$query = "select keyword_id from ".TABLE_PREFIX."link_keyword$wordmd5 where keyword_id = $keyId";
			$result2 = mysql_query($query);
			if (DEBUG > '0') echo mysql_error();
			if (mysql_num_rows($result2) < 1 && !strpos($keyword,"'")) {           
				mysql_query("delete from ".TABLE_PREFIX."keywords where keyword_id=$keyId");
				if (DEBUG > '0') echo mysql_error();
				$del++;
			}
		}
		echo "<div class='submenu'>&nbsp;</div>
			<p class='msg'>Keywords table cleared [<span class='warnok'> $del </span>] words deleted.</p>
			<br />
			<a class='bkbtn' href='javascript:history.go(-1)' title='Go back a Page'>Back</a>
		";
	}


	function eraseSite() {
		global $site_id;
		$keywordX =array("link_keyword0","link_keyword1","link_keyword2","link_keyword3","link_keyword4","link_keyword5","link_keyword6","link_keyword7","link_keyword8","link_keyword9","link_keyworda","link_keywordb","link_keywordc","link_keywordd","link_keyworde","link_keywordf"); 

		//  get current site-name 
		$query = "select url from ".TABLE_PREFIX."sites where site_id =$site_id";
		if (DEBUG > '0') echo mysql_error();            
		$result = mysql_query($query);            
		$row=mysql_fetch_array($result) ;
		$site=$row['url']; 

		//  clear table 'pending'
		mysql_query("delete from ".TABLE_PREFIX."pending where site_id=$site_id");
		if (DEBUG > '0') echo mysql_error();

		// get all links related to this site
		$query = "select link_id from ".TABLE_PREFIX."links where site_id=$site_id ";
		$result = mysql_query($query);
		if (DEBUG > '0') echo mysql_error();
		while ($row=mysql_fetch_array($result)) {
			$link_id=$row['link_id'];
			
			//  delete all keyword_id with their weights associated to this site
			foreach ($keywordX as $allthese) {
				mysql_query ("delete from ".TABLE_PREFIX."$allthese where link_id=$link_id"); 
				if (DEBUG > '0') echo mysql_error(); 
			} 
			
			//  delete all links related to this site
			mysql_query ("delete from ".TABLE_PREFIX."links where site_id=$site_id"); 
			if (DEBUG > '0') echo mysql_error(); 
		}

		// delete those keywords that are no longer required in table 'keywords'
		$query = "select keyword_id, keyword from ".TABLE_PREFIX."keywords";
		$result = mysql_query($query);
		if (DEBUG > '0') echo mysql_error();
		while ($row=mysql_fetch_array($result)) {
			$keyId=$row['keyword_id'];
			$keyword=$row['keyword'];
			$wordmd5 = substr(md5($keyword), 0, 1);
			$query = "select keyword_id from ".TABLE_PREFIX."link_keyword$wordmd5 where keyword_id = $keyId";
			$result2 = mysql_query($query);
			if (DEBUG > '0') echo mysql_error();
			if (mysql_num_rows($result2) < 1) {
				mysql_query("delete from ".TABLE_PREFIX."keywords where keyword_id=$keyId");
				if (DEBUG > '0') echo mysql_error();
			}
		}

		echo "<div class='submenu cntr'>
				<ul>
					<li>Erase & Re-index for site: $site</li>
				</ul>
			</div>
			<div class='panel'>
			Database cleared.
			<br /><br /><br />
			<a href='".WEBROOT_DIR."/admin/?f=index&amp;url=$site&amp;reindex=1' title='Click to start re-indexing of this site'>Okay, now re-index this site</a>
			<br /><br /><br />
			<a class='bkbtn' href='index.php' title='Back to admin'>Return to admin without re-index</a>
			</div>
		";
	}

	function getStatistics() {
		$stats = array();
		$keywordQuery = "select count(keyword_id) from ".TABLE_PREFIX."keywords";
		$linksQuery = "select count(url) from ".TABLE_PREFIX."links";
		$siteQuery = "select count(site_id) from ".TABLE_PREFIX."sites";
		$categoriesQuery = "select count(category_id) from ".TABLE_PREFIX."categories";

		$result = mysql_query($keywordQuery);
		if (DEBUG > '0') echo mysql_error();
		if ($row=mysql_fetch_array($result)) {
			$stats['keywords']=$row[0];
		}
		$result = mysql_query($linksQuery);
		if (DEBUG > '0') echo mysql_error();
		if ($row=mysql_fetch_array($result)) {
			$stats['links']=$row[0];
		}
		for ($i=0;$i<=15; $i++) {
			$char = dechex($i);
			$result = mysql_query("select count(link_id) from ".TABLE_PREFIX."link_keyword$char");
			if (DEBUG > '0') echo mysql_error();
			if ($row=mysql_fetch_array($result)) {
				$stats['index']+=$row[0];
			}
		}
		$result = mysql_query($siteQuery);
		if (DEBUG > '0') echo mysql_error();
		if ($row=mysql_fetch_array($result)) {
			$stats['sites']=$row[0];
		}
		$result = mysql_query($categoriesQuery);
		if (DEBUG > '0') echo mysql_error();
		if ($row=mysql_fetch_array($result)) {
			$stats['categories']=$row[0];
		}
		return $stats;
	}

	function addsite ($url, $title, $short_desc, $cat) {
		$short_desc = addslashes($short_desc);
		$title = addslashes($title);
		$compurl=parse_url("".$url);
		if ($compurl['path']=='')
			$url=$url."/";
		$result = mysql_query("select site_ID from ".TABLE_PREFIX."sites where url='$url'");
		if (DEBUG > '0') echo mysql_error();
		$rows = mysql_num_rows($result);
		if ($rows==0 ) {
			mysql_query("INSERT INTO ".TABLE_PREFIX."sites (url, title, short_desc) VALUES ('$url', '$title', '$short_desc')");
			if (DEBUG > '0') echo mysql_error();
			$result = mysql_query("select site_ID from ".TABLE_PREFIX."sites where url='$url'");
			if (DEBUG > '0') echo mysql_error();
			$row = mysql_fetch_row($result);
			$site_id = $row[0];
			$result=mysql_query("select category_id from ".TABLE_PREFIX."categories");
			if (DEBUG > '0') echo mysql_error();
			while ($row=mysql_fetch_row($result)) {
				$cat_id=$row[0];
				if ($cat[$cat_id]=='on') {
					mysql_query("INSERT INTO ".TABLE_PREFIX."site_category (site_id, category_id) values ('$site_id', '$cat_id')");
					if (DEBUG > '0') echo mysql_error();
				}
			}
		
			If (!mysql_error()) {
				$message =  "<p class='msg'>New Site added...</p>" ;
			} else {
				$message = mysql_error();
			}
		} else {
			$message = "<p class='msg'><span class='warnadmin'>Site already in database</span></p>";
		}
		return $message;
	}

	function indexscreen ($url, $reindex) {    
		$check = "";
		$levelchecked = 'checked="checked"';
		$spider_depth = 2;
		
		if ($url=="") {
			$url = "http://";
			$advurl = "";
		} else {
			$advurl = $url;
			$result = mysql_query("select spider_depth, required, disallowed, can_leave_domain from ".TABLE_PREFIX."sites " .
					"where url='$url'");
			if (DEBUG > '0') echo mysql_error();
			if (mysql_num_rows($result) > 0) {
				$row = mysql_fetch_row($result);
				$spider_depth = $row[0];
				if ($spider_depth == -1 ) {
					$fullchecked = 'checked="checked"';
					$spider_depth ="";
					$levelchecked = "";
				}
				$must = $row[1];
				$mustnot = $row[2];
				$canleave = $row[3];
			}
		}

		echo "<div class='submenu'>
			<ul>
			<li>
			";
		if ($must !="" || $mustnot !="" || $canleave == 1 ) {	
			$_SESSION['index_advanced']=1;
		}
		if ($_SESSION['index_advanced']==1){
			echo "<a href='".WEBROOT_DIR."/admin/?f=index&amp;adv=0&amp;url=$advurl' title='Click to Hide advanced options'>Hide advanced options</a>";
		} else {
			echo "<a href='".WEBROOT_DIR."/admin/?f=index&amp;adv=1&amp;url=$advurl'title='Click to Show advanced options'>Show Advanced options</a>";
		}
		echo "</li>
			</ul>
			</div>
			<div class='panel w75'>
			<form class='txt' action='spider.php' method='post'>
			<fieldset>
				<legend>Indexing Options</legend>
				<label class='em' for='url'>Address:</label>
				<input type='text' name='url' id='url' size='68' title='Enter new URL' value='$url' />
			</fieldset>
			
			<fieldset class='radiobuttons'>
				<legend class='em'>Spidering Level</legend>
				<input type='radio' name='soption' id='soption' title='Check box for Full indexing' value='full' $fullchecked /> <label for='soption'>Full</label><br />
				<input type='radio' name='soption' id='soptionlevel' value='level' title='Check box to limit indexing depth' $levelchecked /> <label for='soptionlevel'>Index depth:</label> <input type='text' name='maxlevel' size='2' title='Enter indexing depth level' value='$spider_depth' />
			</fieldset>
		";

		$not_use_robot = "";
		if ($reindex==1) {$check='checked="checked"';}
		echo "
			<fieldset class='checkbox'>
				<legend>Robots.txt</legend>
				<input type='checkbox' name='reindex' id='reindex' title='Check box to Re-index' value='1' $check /> <label for='reindex'>Check to Re-index</label><br /><br />
				<input type='checkbox' id='not_use_robot' name='not_use_robot' value='1' $not_use_robot /> <label for='not_use_robot'>Temporary ignore 'robots.txt'</label>
			</fieldset>
		";

		if ($_SESSION['index_advanced']==1) {
			if ($canleave==1) {$checkcan='checked="checked"' ;}
			echo "<fieldset class='checkbox'>
					<legend>Spider/Domain Binding</legend>
					<input type='checkbox' name='domaincb' id='domaincb' value='1' title='Check box if Sphider can leave above domain' $checkcan /> <label for='domaincb'>Check for Yes</label>
				</fieldset>
				
				<fieldset>
					<legend>Include/Exclude Options</legend>
					<label class='em' for='in'>URL Must include:</label>
					<textarea name='in' id='in' cols='35' rows='5' title='Enter URLs that Must be included, one per line'>$must</textarea>
					<label class='em' for='out'>URL must Not include:</label>
					<textarea name='out' id='out' cols='35' rows='5' title='Enter URLs that must Not be included, one per line'>$mustnot</textarea>
				</fieldset>
			";
		}
		
		echo "<fieldset>\n";
		
		if (Configure::read('real_log') == '1') {
			echo "
				<input class='cntr sbmt' type='submit' id='submit' value='&nbsp;Start&nbsp;' title='Click to start indexing process' onclick=\"window.open('real_log.php')\"/>
			";
		} else {
			echo "
				<input class='cntr sbmt' type='submit' id='submit' value='&nbsp;Start&nbsp;' title='Click to start indexing process' />
			";
		}
		echo "
			</fieldset>
			</form>
			</div>
		";
	}

	function siteScreen($site_id, $message)  {
		global $indexoption;
		$result = mysql_query("SELECT site_id, url, title, short_desc, indexdate from ".TABLE_PREFIX."sites where site_id=$site_id");
		if (DEBUG > '0') echo mysql_error();
		$row=mysql_fetch_array($result);
		$url = replace_ampersand($row[url]);
		if ($row['indexdate']=='') {
			$indexstatus="<span class='warnadmin cntr'>Not indexed</span>";
			$indexoption="<a href='".WEBROOT_DIR."/admin/?f=index&amp;url=$url' title='Click to start indexing this site'>Index</a>";
		} else {
			$site_id = $row['site_id'];
			$result2 = mysql_query("SELECT site_id from ".TABLE_PREFIX."pending where site_id =$site_id");
			if (DEBUG > '0') echo mysql_error();			
			$row2=mysql_fetch_array($result2);
			if ($row2['site_id'] == $row['site_id']) {
				$indexstatus = "Unfinished";
				$indexoption="<a href='".WEBROOT_DIR."/admin/?f=index&amp;url=$url' title='Continue paused or incomplete indexing'>Continue indexing</a>";
			} else {
				$indexstatus = $row['indexdate'];
				$indexoption="<a href='".WEBROOT_DIR."/admin/?f=index&amp;url=$url&amp;reindex=1' title='Re-index this site'>Re-index</a>";
			}
		}
		echo "<div class='submenu cntr'>
			<ul>
				<li><a href='".WEBROOT_DIR."/admin/?f=edit_site&amp;site_id=".$row['site_id']."' title='Edit indexing parameters'>Edit</a></li>
				<li title='Start indexing this site'>$indexoption</li>
				<li><a href='".WEBROOT_DIR."/admin/?f=48&amp;site_id=".$row['site_id']."' title='Erase all stored data of this site and afterwards perform a re-index'>Erase & Re-index</a></li>
				<li><a href='".WEBROOT_DIR."/admin/?f=5&amp;site_id=".$row['site_id']."' title='Delete Entire Site and Indexing' onclick=\"return confirm('Are you sure you want to Delete the $sitename site? All Site Details and Indexing will be lost!')\">Delete</a></li>
				<li><a href='".WEBROOT_DIR."/admin/?f=46&amp;site_id=".$row['site_id']."' title='Show all pages belonging to this site'>Pages</a></li>
				<li><a href='".WEBROOT_DIR."/admin/?f=21&amp;site_id=".$row['site_id']."' title='Browse indexed pages'>Browse</a></li>
				<li><a href='".WEBROOT_DIR."/admin/?f=19&amp;site_id=".$row['site_id']."' title='Generate site statistics'>Statistics</a></li>
			</ul>
		</div>\n";
		echo $message;
		$sitename = $row['title'];
		echo "<div class='panel'>
			<dl class='cntr'>
				<dt class='headline bd x6'>Title:</dt><dd class='headline em'>&nbsp;".stripslashes($row['title'])."</dd>
				<dt class='odrow bd x6'>URL:</dt>
				<dd class='odrow'><a href='".$row['url']."' target='_blank' title='Visit site in new window'>".rtrim(substr($row['url'],0,70))."</a></dd>
				<dt class='evrow bd x6'>Description:</dt><dd class='evrow'>&nbsp;".stripslashes($row['short_desc'])."</dd>
				<dt class='odrow bd x6'>&nbsp;Last indexed:</dt><dd class='odrow'>&nbsp;$indexstatus</dd>
			</dl>
			";
	}

	function show_links($site_id) {
		$result = mysql_query("SELECT site_id, url, title, short_desc, indexdate from ".TABLE_PREFIX."sites where site_id=$site_id");
		if (DEBUG > '0') echo mysql_error();
		$row=mysql_fetch_array($result);
		$url = replace_ampersand($row[url]);

		// Headline for Show links
		echo "<div class='submenu cntr'>
				<ul>
					<li>Show all Pages of Url ' $url '</li>
					<li><a class='bkbtn' href='".WEBROOT_DIR."/admin/?f=20&amp;site_id=$site_id' title='Go back to Site Options'>Back</a></li>
				</ul>
			</div>\n";

		// Get all links of this Url.
		$res=mysql_query("select * from ".TABLE_PREFIX."links where site_id = '$site_id'");
		if (DEBUG > '0') echo mysql_error();
		$num_rows = mysql_num_rows($res);
		$class = "evrow";
		if ($num_rows == 0)	 print "<br /><div id =\"result_report\" class='cntr'>The search didn't match any indexed links</div>";

		if($num_rows > 0) {    //      Display header row and all results
		echo "<div class='panel'>
			<table width='90%' class='cntr'>
				<tr>
					<td class='headline'>Count</td>
					<td class='headline'>Page Url</td>
					<td class='headline'>Last indexed</td>
					<td class='headline'>Page size</td>
				</tr>
		";

            for ($i=0; $i<$num_rows; $i++) {
                $url2       = mysql_result($res, $i, "url");
                $indexed    = mysql_result($res, $i, "indexdate");                
                $page_size  = mysql_result($res, $i, "size");
                $count =$i+1;
    			if ($class =="evrow") {
    				$class = "odrow";
    			}else{ 
    				$class = "evrow";
    			}
            
                echo "<tr class='$class'>
                    <td>$count</td>            
                    <td><a href='$url2' target='_blank' title='Visit in new window'>$url2</a></td>            
                    <td>$indexed</td>            
                    <td>$page_size kB</td>                     
                    </tr>
                ";
            }
            echo "</table>
            ";            
        }        
    }    

	function siteStats($site_id) {
		$result = mysql_query("select url from ".TABLE_PREFIX."sites where site_id=$site_id");
		if (DEBUG > '0') echo mysql_error();
		if ($row=mysql_fetch_array($result)) {
			$url=$row[0];

			$lastIndexQuery = "SELECT indexdate from ".TABLE_PREFIX."sites where site_id = $site_id";
			$sumSizeQuery = "select sum(length(fulltxt)) from ".TABLE_PREFIX."links where site_id = $site_id";
			$siteSizeQuery = "select sum(size) from ".TABLE_PREFIX."links where site_id = $site_id";
			$linksQuery = "select count(*) from ".TABLE_PREFIX."links where site_id = $site_id";

			$result = mysql_query($lastIndexQuery);
			if (DEBUG > '0') echo mysql_error();
			if ($row=mysql_fetch_array($result)) {
				$stats['lastIndex']=$row[0];
			}

			$result = mysql_query($sumSizeQuery);
			if (DEBUG > '0') echo mysql_error();
			if ($row=mysql_fetch_array($result)) {
				$stats['sumSize']=$row[0];
			}
			$result = mysql_query($linksQuery);
			if (DEBUG > '0') echo mysql_error();
			if ($row=mysql_fetch_array($result)) {
				$stats['links']=$row[0];
			}

			for ($i=0;$i<=15; $i++) {
				$char = dechex($i);
				$result = mysql_query("select count(*) from ".TABLE_PREFIX."links, ".TABLE_PREFIX."link_keyword$char where ".TABLE_PREFIX."links.link_id=".TABLE_PREFIX."link_keyword$char.link_id and ".TABLE_PREFIX."links.site_id = $site_id");
				if (DEBUG > '0') echo mysql_error();
				if ($row=mysql_fetch_array($result)) {
					$stats['index']+=$row[0];
				}
			}
			for ($i=0;$i<=15; $i++) {
				$char = dechex($i);
				$wordQuery = "select count(distinct keyword) from ".TABLE_PREFIX."keywords, ".TABLE_PREFIX."links, ".TABLE_PREFIX."link_keyword$char where ".TABLE_PREFIX."links.link_id=".TABLE_PREFIX."link_keyword$char.link_id and ".TABLE_PREFIX."links.site_id = $site_id and ".TABLE_PREFIX."keywords.keyword_id = ".TABLE_PREFIX."link_keyword$char.keyword_id";
				$result = mysql_query($wordQuery);
				if (DEBUG > '0') echo mysql_error();
				if ($row=mysql_fetch_array($result)) {
					$stats['words']+=$row[0];
				}
			}
			
			$result = mysql_query($siteSizeQuery);
			if (DEBUG > '0') echo mysql_error();
			if ($row=mysql_fetch_array($result)) {
				$stats['siteSize']=$row[0];
			}
			if ($stats['siteSize']=="")
				$stats['siteSize'] = 0;
			$stats['siteSize'] = number_format($stats['siteSize'], 2);
			echo "<div class='submenu'>
					<ul>
						<li><a href='".WEBROOT_DIR."/admin/?f=20&amp;site_id=$site_id' title='Go back to Site Options'>Back</a></li>
					</ul>
				</div>
				<div class='panel'>
					<dl class='tblhead'>
						<dt class='headline x5'>Statistics for site:</dt>
						<dd class='odrow'><a class='options' href='".WEBROOT_DIR."/admin/?f=20&amp;site_id=$site_id' title='Return to site options screen'>".rtrim(substr($url,0,65))."</a></dd>
						
						<dt class='evrow bd x5'>Last Indexed:</dt><dd class='evrow'>&nbsp;".$stats['lastIndex']."</dd>
						
						<dt class='odrow bd x5'>Pages indexed:</dt><dd class='odrow'>&nbsp;".$stats['links']."</dd>
						
						<dt class='evrow bd x5'>Total index size:</dt><dd class='evrow'>&nbsp;".$stats['index']."</dd>
						";
						$sum = number_format($stats['sumSize']/1024, 2);
						
						echo "<dt class='odrow bd x5'>Cached texts:</dt><dd class='odrow'>&nbsp;$sum Kb</dd>
						
						<dt class='evrow bd x5'>Keywords Total:</dt><dd class='evrow'>&nbsp;".$stats['words']."</dd>
						
						<dt class='odrow bd x5'>Site size:</dt><dd class='odrow'>&nbsp;".$stats['siteSize']."kb</dd>
						
					</dl>
				</div>
				";
		}
	}

	function browsePages($site_id, $start, $filter, $per_page) {
		$result = mysql_query("select url from ".TABLE_PREFIX."sites where site_id=$site_id");
		if (DEBUG > '0') echo mysql_error();
		$row = mysql_fetch_row($result);
		$url = $row[0];
		
		$query_add = "";
		if ($filter != "") {
			$query_add = "and url like '%$filter%'";
		}
		$linksQuery = "select count(*) from ".TABLE_PREFIX."links where site_id = $site_id $query_add";
		$result = mysql_query($linksQuery);
		if (DEBUG > '0') echo mysql_error();
		$row = mysql_fetch_row($result);
		$numOfPages = $row[0]; 

		$result = mysql_query($linksQuery);
		if (DEBUG > '0') echo mysql_error();
		$from = ($start-1) * 10;
		$to = min(($start)*10, $numOfPages);

		
		$linksQuery = "select link_id, url from ".TABLE_PREFIX."links where site_id = $site_id and url like '%$filter%' order by url limit $from, $per_page";
		$result = mysql_query($linksQuery);
		if (DEBUG > '0') echo mysql_error();
		echo "<div class='submenu cntr'>
				<ul>
					<li><a class='bkbtn'href='".WEBROOT_DIR."/admin/?f=20&amp;site_id=$site_id' title='Go back to Site Options'>Back</a></li>
				</ul>
			</div>
			<div class='panel'>
				<p class='headline'>
					Pages of site: <a href='".WEBROOT_DIR."/admin/?f=20&amp;site_id=$site_id' target='_blank' title='Open site in new window'>$url</a>
				</p>
				<div id='settings'>
					<table width='100%'>
			";
		$class = "evrow";
		while ($row = mysql_fetch_array($result)) {
			if ($class =="evrow"){
				$class = "odrow";
			}else{  
				$class = "evrow";
			}
			echo "<tr class='$class'>
    			<td><a title='Open page in new window' target='rel' href='".$row['url']."'>".rtrim(substr($row['url'],0,68))."</a></td>
        			<td width='8%'><a class='options' title='Click to delete!'
        			href='".WEBROOT_DIR."/admin/?link_id=".$row['link_id']."&amp;f=22&amp;site_id=$site_id&amp;start=1&amp;filter=$filter&amp;per_page=$per_page'
        			onclick=\"return confirm('Are you sure you want to delete? Page will be dropped.')\">Delete</a>
        		</td></tr>
    		";
		}
		echo "<tr><td colspan='2'></td></tr>
			</table>
		<div class='paginationcell'>
		";
		$pages = ceil($numOfPages / $per_page);
		$prev = $start - 1;
		$next = $start + 1;

		if ($pages > 0) {
			echo "Pages:
            ";
		}
		Configure::write('links_to_next', 10);
		$firstpage = $start - Configure::read('links_to_next');
		if ($firstpage < 1) {
		$firstpage = 1;
		}
		$lastpage = $start + Configure::read('links_to_next');
		if ($lastpage > $pages) {
		$lastpage = $pages;
		}
		for ($x=$firstpage; $x<=$lastpage; $x++) {
			if ($x<>$start) {
				echo "<a href='".WEBROOT_DIR."/admin/?f=21&amp;site_id=$site_id&amp;start=$x&amp;filter=$filter&amp;per_page=$per_page'
                    title='Go to Next Page'>$x</a>
        		";
			} else {
				echo "<span class='em'>$x </span>
                ";
			}
		}
		echo "
			</div>
		</div>
		<form class='txt' action='index.php' method='post'>
			<input type='hidden' name='start' value='1' />
			<input type='hidden' name='site_id' value='$site_id' />
			<input type='hidden' name='f' value='21' />
			<fieldset>
				<legend>Page Filtering</legend>
				<label class='em' for='per_page'>URLs per page</label>
				<input type='text' name='per_page' id='per_page' size='3' value='$per_page' /> URLs
				<label class='em' for='filter'>URL contains:</label>
				<input type='text' name='filter' id='filter' size='15' value='$filter' />
			</fieldset>
			<fieldset>
				<input class='sbmt' type='submit' id='submit' value='Filter' />
			</fieldset>
		</form>
	</div>
			";
	}


	function cleanForm () {
		$clicks = '0';        
		$result = mysql_query("select * from ".TABLE_PREFIX."links where click_counter > 0");
		if (DEBUG > '0') echo mysql_error();
		if ($row=mysql_num_rows($result)) {
			$clicks=$row;
		}
      
		$result = mysql_query("select count(*) from ".TABLE_PREFIX."query_log");
		if (DEBUG > '0') echo mysql_error();
		if ($row=mysql_fetch_array($result)) {
			$log=$row[0];
		}
        		
		$result = mysql_query("select count(*) from ".TABLE_PREFIX."temp");
		if (DEBUG > '0') echo mysql_error();
		if ($row=mysql_fetch_array($result)) {
			$temp=$row[0];
		}		
	   
		if (is_dir(LOG_DIR)) {
			if ($dh = opendir(LOG_DIR)) {
			$i = '0';
			while (($logfile = readdir($dh)) !== false) {
				if (eregi("\.log$", $logfile) || eregi("\.html$", $logfile)) {  //	  only *.html and *.log are valid log-files
					$i++ ;	  //	  count all log-files
				}
			}	
			closedir($dh);
			}
		}
			   
		echo "<div class='submenu'></div>
			<div class='panel'>
				<dl>
					<dt class='headline x2'>Database &amp; Log Cleaning Options</dt>
					<dd class='headline'>&nbsp;</dd>
					<dt class='bd x3'>
						<a href='".WEBROOT_DIR."/admin/?f=15' class='options' title='Click to remove redundant keywords'>Clean keywords</a>
					</dt>
					<dd class='odrow'>Delete all keywords not associated with any link</dd>
					<dt class='bd x3'>
						<a href='".WEBROOT_DIR."/admin/?f=16' class='options' title='Click to remove redundant links'>Clean links</a>
					</dt>
					<dd class='evrow'>Delete all links not associated with any site</dd>
					<dt class='bd x3'>
						<a href='".WEBROOT_DIR."/admin/?f=47' class='options' title='Click to delete all non used categories'>Clean Cat table</a>
					</dt>
					<dd class='odrow'>Delete all categories not associated with any site</dd>
					<dt class='bd x3'>
						<a href='".WEBROOT_DIR."/admin/?f=17' class='options' title='Click to erase the Temporary Link References'>Clear Temp table</a>
					</dt>
					<dd class='odrow'>".$temp." Items in Temporary table</dd>
					<dt class='bd x3'>
						<a href='".WEBROOT_DIR."/admin/?f=23' class='options' title='Click to erase the Search Log entries'>Clear Search log</a>
					</dt>
					<dd class='evrow'>".$log." Items in Query log</dd>
					<dt class='bd x3'>
						<a href='".WEBROOT_DIR."/admin/?f=25' class='options' title='Click to erase the Most Popular Links entries'>Clear Most Popular Links log</a>
					</dt>
					<dd class='evrow'>".$clicks." Items in Best Click log</dd>
					<dt class='bd x3'>
						<a href='".WEBROOT_DIR."/admin/?f=statistics&amp;type=spidering_log' class='options' title='Click to delete all Spidering Logs'>Clear Spider log</a>
					</dt>
					<dd class='odrow'>".$i." Files in Spidering log folder</dd>
				</dl>
			</div>
			";
	}

	function statisticsForm ($type) {
		echo "<div class='submenu y3'>
				<ul>
					<li><a href='".WEBROOT_DIR."/admin/?f=statistics&amp;type=keywords' title='Show list of Top 60 Keywords'>Top keywords</a></li>
					<li><a href='".WEBROOT_DIR."/admin/?f=statistics&amp;type=pages' title='Show list of Largest Pages and their indexed file size'>Largest pages</a></li>
					<li><a href='".WEBROOT_DIR."/admin/?f=statistics&amp;type=spidering_log' title='Show list of Spidering Logs'>Spidering logs</a></li>
					<li><a href='".WEBROOT_DIR."/admin/?f=statistics&amp;type=server_info' title='Show all available server info'>Server Info</a></li>
					<li><a href='".WEBROOT_DIR."/admin/?f=statistics&amp;type=log' title='Show Search Log activity'>Search log</a></li>
					<li><a href='".WEBROOT_DIR."/admin/?f=statistics&amp;type=top_searches' title='Show list of the most popular on-line Searches'>Most popular searches</a></li>
					<li><a href='".WEBROOT_DIR."/admin/?f=statistics&amp;type=top_links' title='Show list of the most popular links, clicked by the user'>Most popular links</a></li>
				</ul>
			</div>
		";

		if ($type == "") {
			$cachedSumQuery = "select sum(length(fulltxt)) from ".TABLE_PREFIX."links";
			$result=mysql_query("select sum(length(fulltxt)) from ".TABLE_PREFIX."links");
			if (DEBUG > '0') echo mysql_error();
			if ($row=mysql_fetch_array($result)) {
				$cachedSumSize = $row[0];
			}
			$cachedSumSize = number_format($cachedSumSize / 1024, 2);
			
			$sitesSizeQuery = "select sum(size) from ".TABLE_PREFIX."links";
			$result=mysql_query("$sitesSizeQuery");
			if (DEBUG > '0') echo mysql_error();
			if ($row=mysql_fetch_array($result)) {
				$sitesSize = $row[0];
			}
			$sitesSize = number_format($sitesSize, 2);
			
			$result = mysql_query("select count(*) from ".TABLE_PREFIX."query_log");
			if (DEBUG > '0') echo mysql_error();
			if ($row=mysql_fetch_array($result)) {
				$query_tot=$row[0];
			}
			
			$result = mysql_query("select sum(click_counter) from ".TABLE_PREFIX."links");
			if(!mysql_error()){
				if ($row=mysql_fetch_array($result)) {
					$click_tot=$row[0];
				}
				
				$stats = getStatistics();
				echo "<div class='panel w60'>
					<dl class='tblhead'>
						<dt class='headline x2'>Overall Statistics:</dt><dd class='headline'>&nbsp;Details</dd>
						<dt class='odrow bd x2'>Sites:</dt><dd class='odrow'>&nbsp;".$stats['sites']."</dd>
						<dt class='evrow bd x2'>Links:</dt><dd class='evrow'>&nbsp;".$stats['links']."</dd>
						<dt class='odrow bd x2'>Categories:</dt><dd class='odrow'>&nbsp;".$stats['categories']."</dd>
						<dt class='evrow bd x2'>Keywords:</dt><dd class='evrow'>&nbsp;".$stats['keywords']."</dd>
						<dt class='odrow bd x2'>Keyword link-relations:</dt><dd class='odrow'>&nbsp;".$stats['index']."</dd>
						<dt class='evrow bd x2'>Cached texts total:</dt><dd class='evrow'>&nbsp;$cachedSumSize kb</dd>
						<dt class='odrow bd x2'>Sites size total:</dt><dd class='odrow'>&nbsp;$sitesSize kb</dd>
						<dt class='evrow bd x2'>Queries total:</dt><dd class='evrow'>&nbsp;$query_tot</dd>
						<dt class='odrow bd x2'>Link clicks total:</dt><dd class='odrow'>&nbsp;$click_tot</dd>
					</dl>
				</div>
				";
			} else {
				echo "
					<div class='submenu cntr'>
					<span class='warnadmin'>
				";
				if (DEBUG > '0') echo mysql_error();
				echo "<br /><br /><br />
					Please run the .../admin/install_bestclick.php file.
					<b r />
					</span>
					</div>
					<br /><br />
				";
				die;
			}
			exit;
		}

		if($type=='keywords') {
			$class = "evrow";
			for ($i=0;$i<=15; $i++) {
				$char = dechex($i);
				$result=mysql_query("select keyword, count(".TABLE_PREFIX."link_keyword$char.keyword_id) as x from ".TABLE_PREFIX."keywords, ".TABLE_PREFIX."link_keyword$char where ".TABLE_PREFIX."keywords.keyword_id = ".TABLE_PREFIX."link_keyword$char.keyword_id group by keyword order by x desc limit 30");
				if (DEBUG > '0') echo mysql_error();
				while (($row=mysql_fetch_row($result))) {
					$topwords[$row[0]] = $row[1];
				}
			}
			arsort($topwords);
			echo "<div class='panel'>
				<p class='headline cntr'>Top 60 Keywords</p>
			";
			$nloops = 1;
			do {
				$nloops++;
				$count = 1;
				echo "<div class='ltfloat x3'>
					<dl>
						<dt class='headline x2'>Keyword</dt><dd class='headline'>:&nbsp;Instances</dd>
				";
				while ((list($word, $weight) = each($topwords)) && $count < 21) {
					$word = quote_replace($word);
					$count++;
					if ($class =="evrow") {
						$class = "odrow";
					} else {
						$class = "evrow";
					}
					echo "<dt class='$class'><a href='../".WEBROOT_DIR."/admin/?query=$word&amp;search=1' target='rel' title='View search results in new window'>".trim(substr($word,0,35))."</a></dt>
						<dd class='$class'>:&nbsp;".$weight."</dd>
					";
				}
				echo "</dl>
					</div>
				";
			} while ($nloops <=3);
				echo "<div class='clear'></div>
					<br />
					<a class='navup' href='#head' title='Jump to Page Top'>Top</a>
					<br />
					</div>
				";
				exit;
			}

		if ($type=='pages') {
            $class = "evrow";
            echo "<div class='panel'>
                <dl class='tblhead'>
                <dt class='headline x8'>File Size</dt><dd class='headline cntr'>Links to Largest Pages</dd>
            ";
            $result=mysql_query("select ".TABLE_PREFIX."links.link_id, url, length(fulltxt)  as x from ".TABLE_PREFIX."links order by x desc limit 20");
            if (DEBUG > '0') echo mysql_error();
            while ($row=mysql_fetch_row($result)) {
                if ($class =="evrow") 
                    $class = "odrow";
                else 
                    $class = "evrow";
                $url = $row[1];
                $sum = number_format($row[2]/1024, 2);
                echo "<dt class='$class x8'>".$sum."kb&nbsp;&nbsp;&nbsp;</dt>
                    <dd class='$class'><a href='$url' title='Open this page in new window' target='_blank'>".$url."</a></dd>
                ";
            }
            echo "</dl>
                    <br />
                    <a class='navup' href='#head' title='Jump to Page Top'>Top</a>                    
                    <br />
            
                </div>
            ";
            exit;            
        }
        
        if ($type=='top_searches') {
            $class = "evrow";
            echo "<div class='panel'>
                <p class='headline cntr'>Most Popular Searches (Top 50)</p>
                <table width='100%'>
                    <tr>
                        <td class='tblhead'>Query</td>
                        <td class='tblhead'>Count</td>
                        <td class='tblhead'>Average results</td>
                        <td class='tblhead'>Last queried</td>
                </tr>
            ";
            $allthese = '1';             
            $result=mysql_query("select query, count(*) as c, date_format(max(time), '%Y-%m-%d %H:%i:%s'), avg(results)  from ".TABLE_PREFIX."query_log group by query order by c desc");
            if (DEBUG > '0') echo mysql_error();
            while (($row=mysql_fetch_row($result)) && ($allthese <= '50')) {
                if ($class =="evrow") 
                    $class = "odrow";
                else 
                    $class = "evrow";
                $word = $row[0];
                $times = $row[1];
                $date = $row[2];
                $avg = number_format($row[3], 0);
                $word = str_replace("\"", "", $word);                
                echo "<tr class='$class '>
                    <td><a href='../".WEBROOT_DIR."/admin/?query=$word&amp;search=1' target='rel' title='View search results in new window'>".$word."</a></td>
                    <td class='cntr'> ".$times."</td>
                    <td class='cntr'> ".$avg."</td><td class='cntr'> ".$date."</td>
                    </tr>
                ";
                $allthese++;
            }
			echo "
                </table>
                <br />
                <a class='navup' href='#head' title='Jump to Page Top'>Top</a>                    
                <br />            
                </div>
            ";
            exit;            
		}
        
        if ($type=='top_links') {
            $class = "evrow";
            echo "<div class='panel'>
                <p class='headline cntr'>Most Popular Links (Top 50)</p>
                <table width='100%'>
                <tr>
                    <td class='tblhead'>Link</td>
                    <td class='tblhead'>Total clicks</td>
                    <td class='tblhead'>Last clicked</td>
                    <td class='tblhead'>Last query</td>
                </tr>
            ";
            $allthese = '1';                     
            $result=mysql_query("select url, click_counter, last_click, last_query  from ".TABLE_PREFIX."links order by click_counter DESC, url");
            if (DEBUG > '0') echo mysql_error();
            
            while (($row=mysql_fetch_row($result)) && ($allthese <= '50')) {
                if ($class =="evrow") 
                    $class = "odrow";
                else 
                    $class = "evrow";
                $url = $row[0];
                $click_counter = $row[1];
                $Timestamp = $row[2];
                $last_query = $row[3];
                if ($Timestamp != '0') {
                    $last_click = date("Y-m-d H:i:s", $Timestamp);
                    
                    echo "<tr class='$class'>
                        <td><a href='$url' target='rel' title='View link in new window'>".htmlentities($url)."</a></td>
                        <td class='cntr sml'> ".$click_counter."</td>
                        <td class='cntr sml'> ".$last_click."</td>
                        <td class='cntr sml'> ".$last_query."</td>
                        </tr>
                    ";
                    $allthese++;
                }
            }
			echo "
                </table>
                <br />
                <a class='navup' href='#head' title='Jump to Page Top'>Top</a>                    
                <br />            
                </div>
            ";
            exit;            
		}

        if ($type=='log') {
            $class = "evrow";
            echo "<div class='panel w75'>
                <p class='headline cntr'>Search Log (Latest 100)</p>
                <table width='100%'>
                <tr>
                    <td class='tblhead'>Query</td>
                    <td class='tblhead x6'>Results</td>
                    <td class='tblhead x3'>Queried at:</td>
                    <td class='tblhead x5'>Time taken</td>
                </tr>
            ";
            $num = '1';
            $result=mysql_query("select query,  date_format(time, '%Y-%m-%d %H:%i:%s'), elapsed, results from ".TABLE_PREFIX."query_log order by time desc");
            if (DEBUG > '0') echo mysql_error();
            while (($row=mysql_fetch_row($result)) && ($num <= '100')) {
                if ($class =="evrow") 
                    $class = "odrow";
                else 
                    $class = "evrow";
                $word = $row[0];              
                $time = $row[1];
                $elapsed = $row[2];
                $results = $row[3];
                echo "<tr class='$class'>
                    <td><a href='../".WEBROOT_DIR."/admin/?query=$word&amp;search=1' target='rel' title='View search results in new window'>".($word)."</a></td>
                    <td class='cntr'> ".$results."</td>
                    <td class='cntr'> ".$time."</td>
                    <td class='cntr'> ".$elapsed."</td>
                    </tr>
                ";
                $num++;                
            }
            echo "
                </table>
                <br />
                <a class='navup' href='#head' title='Jump to Page Top'>Top</a>                    
                <br />                
                </div>
            ";
            exit;            
        }

        if ($type=='spidering_log') {
            $class = "evrow";
            $files = get_dir_contents(LOG_DIR);
            if (count($files)>0) {
                echo "<div class='panel w75'>
                    <p class='headline cntr'>Spidering Logs</p>
                        <form action='' id='fdelfm'>
                        <table width='100%'>
                        <tr>
                            <td class='tblhead'>File</td>
                            <td class='tblhead'>Created</td>
                            <td class='tblhead' width='22%'>Option</td>
                        </tr>
                        <tr>
                            <td colspan='3' class='odrow cntr bd'>
                            <input type='hidden' name='f' value='44' />
                            <input class='sbmt' id='submit1' type='submit' value='Delete ALL log files' title='Start Log File deletion' onclick=\"return confirm('Are you sure you want to delete ALL log files?')\" />
                            </td>
                        </tr>
                    ";
                for ($i=0; $i<count($files); $i++) {
                    $file=$files[$i];
                    $year = substr($file, 0,2);
                    $month = substr($file, 2,2);
                    $day = substr($file, 4,2);
                    $hour = substr($file, 6,2);
                    $minute = substr($file, 8,2);
                    if ($class =="evrow") 
                        $class = "odrow";
                    else 
                        $class = "evrow";
                    echo "<tr class='$class'>
                        <td class='cntr'>
                        <a href='".LOG_DIR."/$file' target='_blank' title='Open this Log File in new window'>$file</a></td>
                        ";
                        if (strlen ($file) > '13') {
                            echo "  <td class='cntr'>20$year-$month-$day $hour:$minute</td>
                                    <td class='cntr options'><a href='?f=delete_log&amp;file=$file' class='options' title='Click to Delete this Log File' onclick=\"return confirm('Are you sure you want to delete? $file Indexing Log File will be lost.')\">Delete</a></td>
                            ";
                        } else {
                            echo "  <td></td><td></td
                            ";
                        }
                    echo "
                    </tr>
                    ";
                }
				echo "
					</table></form>
					<br />
					<a class='navup' href='#head' title='Jump to Page Top'>Top</a>
					<br />
					</div>
				";
			} else {
				echo "<br />
					<p class='cntr msg'>Note: <span class='warnadmin'>No saved spidering logs exist!</span></p>
					<br /> <br />
				";
			}
			exit;
		}

		if ($type = 'server_info') {
			$s_infos = $_SERVER;
			$e_infos = $_ENV;
			echo "<div class='submenu'>
					<ul>
						<li><a href='#serv_info'>Server</a></li>
						<li><a href='#en_info'>Environment</a></li>
						<li><a href='#mysql_info'>MySQL</a></li>
						<li><a href='#pdf_con'>PDF-converter</a></li>
						<li><a href='#php_ini'>php.ini file</a></li>
						<li><a href='".WEBROOT_DIR."/admin/?f=35'>PHP integration</a></li>
						<li><a href='#php_sec'>PHP security info</a></li>
					</ul>
				</div>
				<table width='98%'>
					<tr>
						<td class='headline' colspan='6'>
							<div class='headline cntr'><a name='serv_info'>Server</a></span> </div>
						</td>
					</tr>
					<tr>
						<td width='20%' class='tblhead'>Key</td>
						<td class='tblhead'>Value</td>
					</tr>
				";

			$bgcolor='odrow';
			$i=0;

			reset ($s_info);
			while (list($key, $value) = each ($s_infos)) {
				echo "<tr class='$bgcolor cntr'>
						<td>$key</td>
						<td class='bordl'>$value</td>
					</tr>
				";
				$i++;
				if ($bgcolor=='odrow') {
					$bgcolor='evrow';
				} else {
					$bgcolor='odrow';
				}
			}
			echo "
				</table><br />
				<a class='navup' href='#head' title='Jump to Page Top'>Top</a>
				<br /><br />
				<table width='98%'>
					<tr>
						<td class='headline' colspan='6'>
							<div class='headline cntr'><a name='en_info'>Environment</a></span> </div>
						</td>
					</tr>
					<tr>
						<td width='20%' class='tblhead'>Key</td>
						<td class='tblhead'>Value</td>
					</tr>
			";

			$bgcolor='odrow';
			$i=0;
            
            reset ($e_info);
            while (list($key, $value) = each ($e_infos)) {
                echo "<tr class='$bgcolor cntr'>               
                        <td>$key</td>
                        <td  class='bordl'>$value</td>
                    </tr>
                ";
                $i++;
                if ($bgcolor=='odrow') {
                    $bgcolor='evrow';
                } else {
                    $bgcolor='odrow';
                }
            }
            echo "
                </table><br />
                <a class='navup' href='#head' title='Jump to Page Top'>Top</a>
            ";
            $server_version = mysql_get_server_info();
            $host_info = mysql_get_host_info();        
            $client_info = mysql_get_client_info();
            $protocol_version = mysql_get_proto_info();
       
       		echo "<br /><br />
            	<table width='98%'>
            	<tr>
            		<td class='headline' colspan='6'>
            		<div class='headline cntr'><a name='mysql_info'>MySQL Info</a></span> </div>
            		</td>
            	</tr>
               	<tr>
                	<td width='35%' class='tblhead'>Key</td>
                	<td  class='tblhead'>Value</td>
                </tr>
            ";

        	$bgcolor='odrow';
            echo "
                <tr class='$bgcolor cntr'>
                    <td>MySQL Server version</td>
                    <td  class='bordl'>$server_version</td>
                </tr>
            ";
            
            $bgcolor='evrow';
            echo "
                <tr class='$bgcolor cntr'>
                    <td>Connection info</td>
                    <td  class='bordl'>$host_info</td>
                </tr>
            ";
            
         	$bgcolor='odrow';                       
            echo "
                <tr class='$bgcolor cntr'>
                    <td>Client library info</td>
                    <td  class='bordl'>$client_info</td>
                </tr>
            ";
            
            $bgcolor='evrow';
            echo "
                <tr class='$bgcolor cntr'>
                    <td>MySQL protocol version</td>
                    <td  class='bordl'>$protocol_version</td>
                </tr>
            ";
            
         	$bgcolor='odrow';                       
            echo "
                <tr class='$bgcolor cntr'>
                    <td>Support for mysqli</td>
                    <td  class='bordl'>See below as part of your PHP installation</td>
                </tr>
                </table><br />                
                <a class='navup' href='#head' title='Jump to Page Top'>Top</a>
            ";
            
            $os = '';
            $os = $_ENV['OS'];                              // not all shared hosting server will supply this info
            $admin_path = $_ENV['ORIG_PATH_TRANSLATED'];    // that might work for shared hosting server           
            $admin_file = $_SERVER['SCRIPT_FILENAME'];      // should present the physical path     
            $sdoc_root = $_SERVER['DOCUMENT_ROOT'];         // this should provide every hoster (???)
            $edoc_root = $_ENV['DOCUMENT_ROOT'];            // this should provide every hoster (???)
           
    		echo "<br /><br />
        	<table width='98%'>
        	<tr>
        		<td class='headline' colspan='6'>
        		<div class='headline cntr'><a name='pdf_con'>PDF-converter relevant Info</a></span> </div>
        		</td>
        	</tr>
           	<tr>
            	<td width='35%' class='tblhead'>Key</td>
            	<td  class='tblhead'>Value</td>
            </tr>
            ";

        	$bgcolor='odrow';            
            if ($os) {
                echo "
                    <tr class='$bgcolor cntr'>
                        <td>Operating System</td>
                        <td  class='bordl'>$os</td>
                    </tr>
                ";                
                $bgcolor='evrow';
            }
            if (!$os) {
                $s_soft = $_SERVER['SERVER_SOFTWARE'];                
                $sys_os = stripos($s_soft, "lin");                
                if (!$sys_os) {
                    $sys_os = stripos($s_soft, "uni");                   
                    if (!$sys_os) {
                        $sys_os = stripos($s_soft, "win");
                    }
                }                
                if ($sys_os) {
                    $os = substr($s_soft, $sys_os, '5');
                    echo "
                        <tr class='$bgcolor cntr'>
                            <td>Operating System</td>
                            <td  class='bordl'>$os</td>
                        </tr>
                    ";
                    $bgcolor='evrow';
                } else {
                    $s_sig = $_SERVER['SERVER_SIGNATURE'];                
                    $sys_os = stripos($s_sig, "lin");                
                    if (!$sys_os) {
                        $sys_os = stripos($s_sig, "uni");                   
                        if (!$sys_os) {
                            $sys_os = stripos($s_sig, "win");
                        }
                    }  
                }
                if ($sys_os) {
                    $os = substr($s_sig, $sys_os, '5');
                    echo "
                        <tr class='$bgcolor cntr'>
                            <td>Operating System</td>
                            <td  class='bordl'>$os</td>
                        </tr>
                    ";
                    $bgcolor='evrow';
                }                  
            }   
            //  if ENV or SERVER_SIGNATURE or SERVER_SOFTWARE do not deliver OperatingSystem info, we will use the PHPinfo to extract it
            if (!$os) {
                $phpinfo ='';
                ob_start();                     // redirect output into buffer
                phpinfo();
                $phpinfo = ob_get_contents();   // get all from phpinfo
                ob_end_clean();                 // clean buffer and close it

                //  extract OS information
                $start  = stripos($phpinfo, "\"v\"")+4;
                $end    = stripos($phpinfo, "</td>", $start);
                $length = $end - $start;         
                $os = substr($phpinfo, $start, $length); 
            
                echo "
                    <tr class='$bgcolor cntr'>
                        <td>Operating System</td>
                        <td  class='bordl'>$os</td>
                    </tr>
                ";
                $bgcolor='evrow';            
            }
            
            if ($admin_path) {            
                $admin_path = str_replace("\\\\", "/", $admin_path);
                $admin_path = str_replace("\\", "/", $admin_path);
            
                $pdf_path = str_replace("admin/index.php", "converter/", $admin_path);
                echo "
                    <tr class='$bgcolor cntr'>
                        <td>Physical path to Sphider-plus Admin</td>
                        <td  class='bordl'>$admin_path</td>
                    </tr>                    
                ";
                $bgcolor='odrow';
                echo "
                    <tr class='$bgcolor cntr'>
                        <td>Physical path to the Linux / UNIX PDF-converter</td>
                        <td  class='bordl'>$pdf_path</td>
                    </tr>                    
                ";
                $bgcolor='evrow';                
            } else {            
                if ($admin_file) {                
                    $admin_file = str_replace("\\\\", "/", $admin_file);
                    $admin_file = str_replace("\\", "/", $admin_file);

                    $pdf_path = str_replace("admin/index.php", "converter/", $admin_file);
                    
                    echo "
                        <tr class='$bgcolor cntr'>
                            <td>Physical path to Sphider-plus Admin</td>
                            <td  class='bordl'>$admin_file</td>
                        </tr>
                    ";
                    $bgcolor='odrow';
                    echo "
                        <tr class='$bgcolor cntr'>
                            <td>Physical path to the Linux / UNIX PDF-converter</td>
                            <td  class='bordl'>$pdf_path</td>
                        </tr>                    
                    ";
                    
                $bgcolor='evrow';              
                }            
            }

            if ($sdoc_root){                               
                echo "                
                    <tr class='$bgcolor cntr'>
                        <td>Physical path to document root</td>
                        <td  class='bordl'>$sdoc_root</td>
                    </tr>                    
                ";
            } else {
                if ($edoc_root){                               
                    echo "                
                        <tr class='$bgcolor cntr'>
                            <td>Physical path to document root</td>
                            <td  class='bordl'>$edoc_root</td>
                        </tr>                    
                    ";
                }             
            }
            
            if (!$admin_path && !$admin_file) {
                if ($sdoc_root){                               
                    echo "                
                        <tr class='$bgcolor cntr'>
                            <td>Physical path to document root</td>
                            <td  class='bordl'>$sdoc_root</td>
                        </tr>                    
                    ";
                } 
                
                if ($edoc_root){                               
                    echo "                
                        <tr class='$bgcolor cntr'>
                            <td>Physical path to document root</td>
                            <td  class='bordl'>$edoc_root</td>
                        </tr>                    
                    ";
                } else {
                    echo "
                        </table>
                    	<table width='98%'>
                    	<tr>
                            <td>
                            <span class='cntr warnadmin'><br />
                            Attention: Your server does not deliver information about the physical path to Sphider-plus.<br />
                            For LINUX and UNIX systems you will have to initialize the PDF converter manually.<br />
                            For details see the file readme.pdf, chapter: PDF converter for Linux/UNIX systems.<br />
                            <br /></span>
                            </td>
                        </tr>
                    ";               
                }             
            }            
                        
            echo "
                </table><br />
                <a class='navup' href='#head' title='Jump to Page Top'>Top</a><br /><br />
            ";                    
                
            echo "                
            	<table width='98%'>
            	<tr>
            		<td class='headline' colspan='6'>
            		<div class='headline cntr'>PHP Info</span> </div>
            		</td>
            	</tr>
                </table>
            ";
                      
    		echo "<br />
            	<table width='98%'>
            	<tr>
            		<td class='headline' colspan='6'>
            		<div class='headline cntr'><a name='php_ini'>php.ini file</a></span> </div>
            		</td>
            	</tr>
            	<tr>
            		<td width='20%' class='tblhead'>Key</td>
            		<td class='tblhead'>Value</td>

            	</tr>
        	";
            $php_ini = ini_get_all();

    		$bgcolor='odrow';
    		$i=0;
            
            reset ($php_ini);
            while (list($key, $value) = each ($php_ini)) {
    			echo "<tr class='$bgcolor cntr'>               
                        <td>$key</td>
                        <td   class='bordl'>
                        ";
                        // print_r($value);                        
                        "</td>
                    </tr>
                ";
    			$i++;
    			if ($bgcolor=='odrow') {
    				$bgcolor='evrow';
    			} else {
    				$bgcolor='odrow';
    			}
    		}
       		echo "
                </table>
                <br /><br />
                <a class='navup' href='#head' title='Jump to Page Top'>Top</a>
                <br /><br />
            	<table width='98%'>
            	<tr>
            		<td class='headline' colspan='6'>
            		<div class='headline cntr'><a name='php_sec'>PHP security info</a></span> </div>
            		</td>
            	</tr>
            	<tr>
            		<td width='20%' class='tblhead'></td>
            		<td class='tblhead'></td>

            	</tr>
        	";
            
            //phpsecinfo();   //  get PHP security information
            
       		echo "
                </table>
                <br /><br />
                <a class='navup' href='#head' title='Jump to Page Top'>Top</a>
                <br /><br />
                </div>
            ";


            
            exit;            
        }	
	}
           
	switch($f)	{
    
		case 1:
			$message = addsite($url, $title, $short_desc, $cat);
			$compurl=parse_url($url);
			if ($compurl['path']=='')
				$url=$url."/";

			$result = mysql_query("select site_id from ".TABLE_PREFIX."sites where url='$url'");
			if (DEBUG > '0') echo mysql_error();
			$row = mysql_fetch_row($result);
			if ($site_id != "")
				siteScreen($site_id, $message);
			else
				showsites($message);
		break;
        
		case 2:
			showsites($f);
		break;
        
		case edit_site:
			editsiteform($site_id);
		break;
        
		case 4:
			if (!isset($domaincb)) {
				$domaincb = 0;
            }
			if (!isset($cat)) {
				$cat = "";
            }    
			if ($soption =='full') {
				$depth = '-1' ;
			} 
			if ($soption =='level' && $depth =='') {
				$depth = '2' ;
            }
 			if (!isset($in)) {
				$in = '';
            } 
 			if (!isset($out)) {
				$out = '';
            }           
            
			$message = editsite ($site_id, $url, $title, $short_desc, $depth, $in, $out, $domaincb, $cat);
			showsites($message);
		break;
        
		case 5:
			deletesite ($site_id);
			showsites($message);
		break;
        
		case add_cat:
			if (!isset($parent))
				$parent = "";
			addcatform ($parent);
		break;
        
		case 7:
			if (!isset($parent)) {
				$parent = "";
			}
			$message = addcat ($category, $parent);
			list_cats (0, 0, "evrow", $message);
		break;
        
		case categories:
			list_cats (0, 0, "evrow", "");
		break;
        
		case edit_cat;
			editcatform($cat_id);
		break;
        
		case 10;
			$message = editcat ($cat_id, $category);
			list_cats (0, 0, "evrow", $message);
		break;
        
		case 11;
			deletecat($cat_id);
			list_cats (0, 0, "evrow");
		break;

		case 15;
			cleanKeywords();
		break;
		case 16;
			cleanLinks();
		break;

		case 17;
			cleanTemp();
		break;

		case 19;
			siteStats($site_id);
		break;
        
		case 20;
			siteScreen($site_id, $message);
		break;
        
		case 21;
			if (!isset($start))
				$start = 1;
			if (!isset($filter))
				$filter = "";
			if (!isset($per_page))
				$per_page = 10;

			browsePages($site_id, $start, $filter, $per_page);
		break;
        
		case 22;
			deletePage($link_id);
			if (!isset($start))
				$start = 1;
			if (!isset($filter))
				$filter = "";
			if (!isset($per_page))
				$per_page = 10;
			browsePages($site_id, $start, $filter, $per_page);
		break;
        
		case 23;
			clearLog();
		break;
        
		case 24;
			session_destroy();
			header("Location: index.php");
		break;
        
		case 25;
			clearBestClick();
		break;
        

		//	show menu 'Sites awaiting approval'
		case 28;       
			approve_newsites();
		break;
        
        //	show menus 'Approved', 'Rejected' or 'Banned'        
		case 29:        
     		$query = "SELECT * FROM ".TABLE_PREFIX."addurl where url ='$url'";
    		$result = mysql_query($query);
    		if (DEBUG > '0') echo mysql_error();
    		$row = mysql_fetch_array($result);
            $account = $row['account'];
            $created = $row['created']; 
            $mailer = "Addurl-mailer";
        	$header = "from: AuthorityDomain.com<".Configure::read('dispatch_email').">\r\n";
        	$header .= "Reply-To: ".Configure::read('dispatch_email')."\r\n";

            $subject2    = "URL Submitted: $url";
    
//      Text for e-mail to dispatcher when suggestion was approved               
$text2 = "On $created you suggested the site $url to be indexed by our search engine.\n
Your suggestion was accepted by the system administrator and will be indexed immediately.\n
We appreciate your help and effort in building this search engine.\n\n
This mail was automatically generated by $mailer.\n";
    
//      Text for e-mail to dispatcher when suggestion was rejected 
$text3 = "On $created you suggested the site $url to be indexed by our search engine.\n
Your suggestion was rejected by the system administrator and will not be indexed.\n
We appreciate your help and effort in building this search engine.\n\n
This mail was automatically generated by $mailer.\n";
    
//      Text for e-mail to dispatcher when suggestion was rejected and banned 
$text4 = "On $created you suggested the site $url to be indexed by our search engine.\n
Your suggestion was rejected and banned by the system administrator and will never be indexed.\n\n
This mail was automatically generated by $mailer.\n";
                
			if ($approve == "Approve") {
    			$message = addsite($url, $title, $short_desc, $cat);
    			$orig_url = $url;
    			$compurl=parse_url($url);
    			if ($compurl['path']=='')
    				$url=$url."/";

    			$result = mysql_query("select site_ID from ".TABLE_PREFIX."sites where url='$url'");
    			if (DEBUG > '0') echo mysql_error();
    			$row = mysql_fetch_row($result);
    			$site_id = $row[0];

    			mysql_query("INSERT INTO ".TABLE_PREFIX."site_category (site_id, category_id) values ('$site_id', '$cat')");
    			if (DEBUG > '0') echo mysql_error();
                
    			mysql_query("DELETE FROM ".TABLE_PREFIX."addurl WHERE url='$orig_url'");
    			if (DEBUG > '0') echo mysql_error();                
                echo "<div class='submenu cntr'>| Sites for Approval |</div>
                    <div class='cntr'>
                    <p>\n\n</p>
                    Site approved.
                    <p>\n\n</p>
                ";
 
                if (Configure::read('addurl_info') == 1) {
                    // e-mail to dispatcher "approved"
                    if (mail($account,$subject2,$text2,$header) or die ("Error ! Could not inform the dispatcher ( $account )<br />Unable to send the e-mail!"));		            
                    echo "
                        Dispatcher was informed by e-mail.
                        <p>\n\n</p>
                    ";
                }

                echo "(Don't forget to index the site $orig_url)
                    <p>\n\n\n</p>
                    </div>
                    <div class='odrow cntr'>
                    <p>\n\n</p>
            		<a class='bkbtn' href='".WEBROOT_DIR."/admin/?f=28' title='Reload Approve sites'>Complete this process</a>
                    <p>\n\n</p>
                    </div>
                    </body>
                    </html>
                ";
                die ('');                    
			}
			elseif ($delete == "Reject") {
				mysql_query("DELETE FROM ".TABLE_PREFIX."addurl WHERE url='$url'");
				if (DEBUG > '0') echo mysql_error();

                echo "<div class='submenu cntr'>| Sites for Approval |</div>
                    <div class='cntr'>
                    <p>\n\n</p>
                    Url $url rejected and deleted.
                    <p>\n\n</p>
                ";
                     
                if (Configure::read('addurl_info') == 1) {
                    // e-mail to dispatcher "rejected"
                    if (mail($account,$subject2,$text3,$header) or die ("<br />Error ! Could not inform the dispatcher ( $account )<br />Unable to send the e-mail!"));		            
                    echo "
                        Dispatcher was informed by e-mail.
                        <p>\n\n</p>
                    ";
                }
                    
                echo "</div>
                    <div class='odrow cntr'>
            		<a class='bkbtn' href='".WEBROOT_DIR."/admin/?f=28' title='Reload Approve sites'>Complete this process</a>
                    <p>\n\n</p>
                    </div>
                    <p>\n\n\n\n</p>
                    </body>
                    </html>
                ";
                die ('');  
			}
			elseif ($bann == "Ban !") {
				mysql_query("INSERT INTO `".TABLE_PREFIX."banned` (`domain`) VALUES ('".$domain."');");
				if (DEBUG > '0') echo mysql_error();
				mysql_query("DELETE FROM `".TABLE_PREFIX."addurl` WHERE url = ('".$url."') LIMIT 1"); 
				if (DEBUG > '0') echo mysql_error();
                
                echo "<div class='submenu cntr'>| Sites for Approval |</div>
                    <div class='cntr'>
                    <p>\n\n\n</p>
                    The Url $url is banned now!
                    <p>\n\n</p>
                    ";                    
                     
                if (Configure::read('addurl_info') == 1) {
                    // e-mail to dispatcher "rejected and banned"
                    if (mail($account,$subject2,$text4,$header) or die ("Error ! Could not inform the dispatcher ( $account )<br />Unable to send the e-mail!"));		            
                    echo "
                        Dispatcher was informed by e-mail.
                        <p>\n\n</p>
                    ";
                }
                
                echo "</div>
                    <div class='odrow cntr'>
                    <a class='bkbtn' href='".WEBROOT_DIR."/admin/?f=28' title='Reload Approve sites'>Complete this process</a>
                    <p>\n\n</p>
                    </div>
                    </body>
                    </html>
                ";
                die ('');  
			}
		break;
      
		case 30:
            $valid = '1';
            banned_domains($valid);   //	show menu 'Banned Domains Manager'  and get new 'Banned domains'            
		break;

        // remove from 'banned domains'
		case 31:        
    		mysql_query("DELETE FROM `".TABLE_PREFIX."banned` WHERE domain like ('".$domain."') LIMIT 1"); 
    		if (DEBUG > '0') echo mysql_error();
            $valid = '1';
            banned_domains($valid);
		break;

        // add to 'banned domains'        
		case 32:
            $www = '';
            $dot = '';
            $valid = '';            
            $new_banned = strtolower(trim($new_banned));
            $www = substr_count($new_banned, "www.");   //  minimum check for admin input
            $dot = substr_count($new_banned, ".");      //  minimum check for admin input           
			if($www == '1' && $dot >= '2') {
                mysql_query("INSERT INTO `".TABLE_PREFIX."banned` (`domain`) VALUES ('".$new_banned."');");
                if (DEBUG > '0') echo mysql_error();
                $valid = '1';
            }
            banned_domains($valid);
		break;
		
		case '':
			approve_newsites();	
		break;
               
        case 35:
            $phpinfo ='';
            ob_start();                     // redirect output into buffer
            phpinfo();
            $phpinfo = ob_get_contents();   // get all from phpinfo
            ob_end_clean();                 // clean buffer and close it

            //  extract the table content
            $start  = stripos($phpinfo, "<table ");
            $end    = strripos($phpinfo, "</table>")+8;
            $length = $end - $start;         
            $phpinfo = substr($phpinfo, $start, $length); 

            //  replace phpinfo() style with valid Sphider-plus design
            $phpinfo = str_replace("width=\"600\"", "width=\"98%\"", $phpinfo);
            $phpinfo = str_replace("class=\"h\"", "class=\"stats\"", $phpinfo);
            $phpinfo = str_replace("class=\"e\"", "class=\"odrow\"", $phpinfo);
            $phpinfo = str_replace("class=\"v\"", "class=\"evrow\"", $phpinfo);
            $phpinfo = str_replace("border=\"0\"", "border=\"1\"", $phpinfo);           
            $phpinfo = str_replace("<h2>", "<h1>", $phpinfo);
            $phpinfo = str_replace("</h2>", "</h1>", $phpinfo);

        
    		echo "
               	<table width='98%'>
                	<tr>
                		<td class='headline' colspan='6'>
                		<div class='headline cntr'>PHP Integration</span> </div>
                		</td>
                	</tr>
                </table>
                <br />
                <a class='bkbtn' href='".WEBROOT_DIR."/admin/?f=statistics&amp;type=server_info' title='Jump back to Server infos'>Back to Server Info</a>
                <br /><br />
                <center>  
                $phpinfo
                <br />
                <a class='navup' href='#head' title='Jump to Page Top'>Top</a>
                <br /><br />
            ";            
        break;
                
        //  Import / Export URL list
		case 40:
            include "url_backup.php";
        break;
        		
        //  Import / Export settings (conf.php)
		case 41:
            include "setting_backup.php";
        break;
 
        //      Used for bulk delete of spider log files 
		case 44;
			clearSpLog();
		break;

        //    Used for re-index with erase all sites         
        case 45;     
            $erase =array("domains","keywords","links","link_keyword0","link_keyword1","link_keyword2","link_keyword3","link_keyword4","link_keyword5","link_keyword6","link_keyword7","link_keyword8","link_keyword9","link_keyworda","link_keywordb","link_keywordc","link_keywordd","link_keyworde","link_keywordf","pending"); 
            foreach ($erase as $allthis){ 
                mysql_query ("TRUNCATE `".TABLE_PREFIX."$allthis`"); 
                if (DEBUG > '0') echo mysql_error(); 
            } 
                                    
			if (Configure::read('real_log') == '0'){
				echo "<div class='submenu cntr'>Erase & Re-index</div>
					<p class='cntr em'>
					<br /><br />
					Sphider database cleared.
					<br /><br /><br />        
					<a href='spider.php?all=1' title='Reindex now'>Okay, now re-index all</a>
					<br /><br /><br />        
					<a class='bkbtn' href='index.php' title='Back to admin'>Return to admin without reindex</a>
					<br /><br />
					</p>   
				";           
			} else {             
				echo "
					<div class='submenu cntr'>
						<ul>
							<li><a href='".WEBROOT_DIR."/' title='Back to admin'>Return to admin without re-index</a></li>
						</ul>
					</div>
					<p class='panel'>
						Erase & Re-index all
						<br /><br />
						Sphider database cleared.
						<br /><br /><br />
						<form action='".WEBROOT_DIR."/admin/spider.php' method='get'>
							<table class='searchBox'>
								<tr>
									<td>
										<input type='hidden' name='all' id='all' value='1'>	                                            
										
										<input type='submit' value='Start now to re-index all' onclick=\"window.open('".WEBROOT_DIR."/admin/real_log.php')\"> 
									</td>
								</tr>
							</table>
						</form>
					</p>
			";
		}
		break; 
		case 46;    // show all links of current site
			show_links($site_id);
		break;
		case 47;    // clean categories
			cleanCats();
		break;
		case 48;    // Erase & Re-index a single site
			eraseSite();
		break;
		case 50;    // Re-index all
			if (Configure::read('real_log') == '0') {
			echo "<div class='submenu cntr'><ul><li>Re-index all</li></ul></div>
				<p class='panel'>
					<br /><br /><br />
					<a href='spider.php?all=1' title='Reindex now'>Okay, now re-index all</a>
					<br /><br /><br />
					<a class='bkbtn' href='index.php' title='Back to admin'>Return to admin without reindex</a>
					<br /><br />
				</p>
			";
			} else {
			echo "
				<div class='submenu cntr'>
					<ul>
						<li><a href='".WEBROOT_DIR."/' title='Back to admin'>Return to admin without re-index</a></li>
					</ul>
				</div>
				<p class='panel'>
					<b>Re-index all</b>
					<br /><br /><br />
					<form action='spider.php' method='get'>
						<table class='searchBox'>
							<tr>
								<td>
									<input type='hidden' name='all' id='all' value='1'>
						
									<input type='submit' value='Start now to re-index all' onclick=\"window.open('real_log.php')\"> 
								</td>
							</tr>
						</table>
					</form>
				</p>
			";
			//createEndBody($lreal_handle);  
			fclose ($real_handle);
		}
 
		break;
        
		case 51;    // Index only the new       
            if (Configure::read('real_log') == '0'){
                echo "<div class='submenu cntr'>Index only the new sites</div>
                    <p class='cntr em'>
                    <br /><br /><br />        
                    <a href='spider.php?all=2' title='Index now'>Okay, now index all new sites</a>
                    <br /><br /><br />        
                    <a class='bkbtn' href='index.php' title='Back to admin'>Return to admin without reindex</a>
                    <br /><br />
                    </p>   
                ";           
            } else {                      
                echo "
    <div class='submenu cntr'>Index only new sites</div>
    <p class='cntr em'>
    <br /><br /><br />        
    <form action='spider.php' method='get'>
        <table class='searchBox'>
            <tr>
                <td>
                <input type='hidden' name='all' id='all' value='2'>	                                            
                
                <input type='submit' value='Start now to index all new sites' onclick=\"window.open('real_log.php')\"> 
                </td>
            </tr>
        </table>
    </form></p>
    <br /><br />
    <p class='cntr em'>       
    <a class='bkbtn' href='index.php' title='Back to admin'>Return to admin without re-index</a>
    <br /><br />
    </p>   
                ";

                //createEndBody($lreal_handle);  
                fclose ($real_handle);
            }
 
		break;

		case database;
			include "db_main.php";
		break;
        
		case settings;
			include('configset.php');
		break;
        
		case delete_log;
			unlink(LOG_DIR."/".$file);
			statisticsForm('spidering_log');
		break;
        
		case '':
			showsites();
		break;

		case statistics;
			if (!isset($type))
				$type = "";
			statisticsForm($type);
		break;

		case index;
			if (!isset($url))
				$url = "";
			if (!isset($reindex))
				$reindex = "";
			if (isset($adv)) {
					$_SESSION['index_advanced']=$adv;
			}
			indexscreen($url, $reindex);
		break;
        
		case add_site;
			addsiteform();
		break;
        
		case clean;
			cleanForm();
		break;
        
	}
    $stats = getStatistics();
	echo "	<p class='stats'>
		<span class='em'>Database contains: </span>".$stats['sites']." sites, ".$stats['links']." links, ".$stats['categories']." categories and ".$stats['keywords']." keywords</p>
    ";
    
// The following should only be removed if you contribute to the Sphider project..
// Note that this is a requirement under the GPL licensing agreement, which Sphider-plus acknowledges.	
    include "admin_footer.php" ;
?>