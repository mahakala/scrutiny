<?php 

	$common_dir = APP."include/common/";   //subfolder of .../include/ where all the common files are stored 
	
	/**
	* Returns the result of a query as an array
	* 
	* @param string $query SQL päring stringina
	* @return array|null massiiv
	 */
	function sql_fetch_all($query) {
		// global DEBUG;
		$data = array();    
		$result = mysql_query($query);
		if($mysql_err = mysql_errno()) {
			if (DEBUG > '0') {
				print $query.'<br>'.mysql_error();
			}
		} else {
			while($row=mysql_fetch_array($result)) {
				$data[]=$row;
			}
		}
		return $data;
	}

	/*
	Removes duplicate elements from an array
	*/
	function distinct_array($arr) {
		rsort($arr);
		reset($arr);
		$newarr = array();
		$i = 0;
		$element = current($arr);
		
		for ($n = 0; $n < sizeof($arr); $n++) {
			if (next($arr) != $element) {
				$newarr[$i] = $element;
				$element = current($arr);
				$i++;
			}
		}
		return $newarr;
	}

	function get_cats($parent) {
		// global $db, DEBUG;
		$query = "SELECT * FROM ".TABLE_PREFIX."categories WHERE parent_num=$parent";
		$result = mysql_query($query);
		if (DEBUG > '0') echo mysql_error();
		$arr[] = $parent;
		if (mysql_num_rows($result) <> '') {
			while ($row = mysql_fetch_array($result)) {
				$id = $row[category_id];
				$arr = add_arrays($arr, get_cats($id));
			}
		}
		return $arr;
	}
	
	function add_arrays($arr1, $arr2) {
		foreach ($arr2 as $elem) {
			$arr1[] = $elem;
		}
		return $arr1;
	}

	$entities = array(
		"&amp" => "&",
		"&apos" => "'",
		"&THORN;"  => "Þ",
		"&szlig;"  => "ß",
		"&agrave;" => "à",
		"&aacute;" => "á",
		"&acirc;"  => "â",
		"&atilde;" => "ã",
		"&auml;"   => "ä",
		"&aring;"  => "å",
		"&aelig;"  => "æ",
		"&ccedil;" => "ç",
		"&egrave;" => "è",
		"&eacute;" => "é",
		"&ecirc;"  => "ê",
		"&euml;"   => "ë",
		"&igrave;" => "ì",
		"&iacute;" => "í",
		"&icirc;"  => "î",
		"&iuml;"   => "ï",
		"&eth;"    => "ð",
		"&ntilde;" => "ñ",
		"&ograve;" => "ò",
		"&oacute;" => "ó",
		"&ocirc;"  => "ô",
		"&otilde;" => "õ",
		"&ouml;"   => "ö",
		"&oslash;" => "ø",
		"&ugrave;" => "ù",
		"&uacute;" => "ú",
		"&ucirc;"  => "û",
		"&uuml;"   => "ü",
		"&yacute;" => "ý",
		"&thorn;"  => "þ",
		"&yuml;"   => "ÿ",
		"&THORN;"  => "Þ",
		"&szlig;"  => "ß",
		"&Agrave;" => "à",
		"&Aacute;" => "á",
		"&Acirc;"  => "â",
		"&Atilde;" => "ã",
		"&Auml;"   => "ä",
		"&Aring;"  => "å",
		"&Aelig;"  => "æ",
		"&Ccedil;" => "ç",
		"&Egrave;" => "è",
		"&Eacute;" => "é",
		"&Ecirc;"  => "ê",
		"&Euml;"   => "ë",
		"&Igrave;" => "ì",
		"&Iacute;" => "í",
		"&Icirc;"  => "î",
		"&Iuml;"   => "ï",
		"&ETH;"    => "ð",
		"&Ntilde;" => "ñ",
		"&Ograve;" => "ò",
		"&Oacute;" => "ó",
		"&Ocirc;"  => "ô",
		"&Otilde;" => "õ",
		"&Ouml;"   => "ö",
		"&Oslash;" => "ø",
		"&Ugrave;" => "ù",
		"&Uacute;" => "ú",
		"&Ucirc;"  => "û",
		"&Uuml;"   => "ü",
		"&Yacute;" => "ý",
		"&Yhorn;"  => "þ",
		"&Yuml;"   => "ÿ"
	);

	//Apache multi indexes parameters
	$apache_indexes = array(
		"N=A" => 1,
		"N=D" => 1,
		"M=A" => 1,
		"M=D" => 1,
		"S=A" => 1,
		"S=D" => 1,
		"D=A" => 1,
		"D=D" => 1,
		"C=N;O=A" => 1,
		"C=M;O=A" => 1,
		"C=S;O=A" => 1,
		"C=D;O=A" => 1,
		"C=N;O=D" => 1,
		"C=M;O=D" => 1,
		"C=S;O=D" => 1,
		"C=D;O=D" => 1
	);

	function remove_accents($string) {
		return (strtr($string, "ÀÁÂÃÄÅÆàáâãäåæÒÓÔÕÕÖØòóôõöøÈÉÊËèéêëðÇçÐÌÍÎÏìíîïÙÚÛÜùúûüÑñÞßÿý", "aaaaaaaaaaaaaaoooooooooooooeeeeeeeeecceiiiiiiiiuuuuuuuunntsyy"));
	}

	function lower_case($string) {
		global $charSet, $default_charset;

		if ($charSet =='') {
			$charSet = $default_charset;    //  this is needed for query search
		}  

		// if required, convert Cyrillic charset into lower case
		if ($charSet == 'ISO-8859-5' || $charSet == 'WINDOWS-1251' || $charSet == 'CP855') {
			$lower = array(
				"A" => "a",
				"B" => "b",
				"C" => "c",
				"D" => "d",
				"E" => "e",
				"F" => "f",
				"G" => "g",
				"H" => "h",
				"I" => "i",
				"J" => "j",
				"K" => "k",
				"L" => "l",
				"M" => "m",
				"N" => "n",
				"O" => "o",
				"P" => "p",
				"Q" => "q",
				"R" => "r",
				"S" => "s",
				"T" => "t",
				"U" => "u",
				"V" => "v",
				"W" => "w",
				"X" => "x",
				"Y" => "y",
				"Z" => "z",

				"А" => "а",
				"Б" => "б",
				"В" => "в",
				"Г" => "г",
				"Д" => "д",
				"Е" => "е",
				"Ж" => "ж",
				"З" => "з",
				"И" => "и",
				"Й" => "й",
				"К" => "к",
				"Л" => "л",
				"М" => "н",
				"О" => "о",
				"П" => "п",
				"Р" => "р",
				"С" => "с",
				"Т" => "т",
				"У" => "у",
				"Ф" => "ф",
				"Х" => "х",
				"Ц" => "ц",
				"Ч" => "ч",
				"Ш" => "ш",
				"Щ" => "щ",
				"Ъ" => "ъ",
				"Ы" => "ы",
				"Ь" => "ь",
				"Э" => "э",
				"Ю" => "ю",
				"Я" => "я",

				"Ё" => "ё",
				"Ђ" => "ђ",
				"Ѓ" => "ѓ",
				"Є" => "є",
				"Ѕ" => "ѕ",
				"І" => "і",
				"Ї" => "ї",
				"Ј" => "ј",
				"Љ" => "љ",
				"Њ" => "њ",
				"Ћ" => "ћ",
				"Ќ" => "ќ",
				"Ў" => "ў",
				"Џ" => "џ"
			);
			reset($lower);
			while ($char = each($lower)) {
				$string = preg_replace("/".$char[0]."/i", $char[1], $string);
			}
			return ($string);
		} else {
			return (strtr($string,  "ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÜÉÈÁÀÚÙÍÌ", "abcdefghijklmnopqrstuvwxyzäöüéèáàúùíì"));
		}
	}

	$all        = array();                  //  intermediate array fo ignored words
	$common     = array();                  //  array fo ignored words
	$ext        = array();                  //  array for ignored file suffixes
	$whitelist  = array();                  //  array for whitelist
	$white      = array();
	$blacklist  = array();                  //  array for blacklist
	$black      = array();

	if (is_dir($common_dir)) {
		$handle = opendir($common_dir);
		if (Configure::read('use_common') == '1') {
			while (false !== ($common_file = readdir($handle))) {   //  get all common files
				if (strpos($common_file, "ommon_")) {  
					$act = @file($common_dir.$common_file);         //  get content of actual common file
					$all = array_merge($all, $act);                 //  build a complete array of common words
				}
			} 
		}
		$suffix = @file($common_dir.'suffix.txt');              //  get all file suffixes to be ignored during index procedure 
		if (Configure::read('use_white') == '1') $white = @file($common_dir.'whitelist.txt');        //  get all words to enable page indexing     
		if (Configure::read('use_black') == '1') $black = @file($common_dir.'blacklist.txt');        //  get all words to prevent indexing of page
		closedir($handle);
		if (is_array($all)) {
		while (list($id, $word) = each($all))
		$common[trim($word)] = 1;
		}
		if (is_array($suffix)) {
			while (list($id, $word) = each($suffix))
			$ext[] = trim($word);
			$ext = array_unique($ext);
			sort($ext);
		}
		if (is_array($white)) {
			while (list($id, $word) = each($white))
			$whitelist[] = trim($word);
			$whitelist = array_unique($whitelist);
			sort($whitelist);
		}
		if (is_array($black)) {
			while (list($id, $word) = each($black))
			$blacklist[] = trim($word);
			$blacklist = array_unique($blacklist);
			sort($blacklist);
		}
	}

	function is_num($var) {
		for ($i=0;$i<strlen($var);$i++) {
			$ascii_code=ord($var[$i]);
			if ($ascii_code >=49 && $ascii_code <=57){
				continue;
			} else {
				return false;
			}
		}
		return true;
	}

	function getHttpVars() {
		$superglobs = array(
			'_POST',
			'_GET',
			'HTTP_POST_VARS',
			'HTTP_GET_VARS');
		
		$httpvars = array();
		
		// extract the right array
		foreach ($superglobs as $glob) {
			global $$glob;
			if (isset($$glob) && is_array($$glob)) {
				$httpvars = $$glob;
			}
			if (count($httpvars) > 0)
				break;
		}
		return $httpvars;
	}

	function countSubstrs($haystack, $needle) {
		$count = 0;
		while(strpos($haystack,$needle) !== false) {
			$haystack = substr($haystack, (strpos($haystack,$needle) + 1));
			$count++;
		}
		return $count;
	}

	function quote_replace($str) {
		$str = str_replace("\"", "&quot;", $str);
		return str_replace("'","&apos;", $str);
	}

	function fst_lt_snd($version1, $version2) {
		$list1 = explode(".", $version1);
		$list2 = explode(".", $version2);
		$length = count($list1);
		$i = 0;
		while ($i < $length) {
			if ($list1[$i] < $list2[$i])
				return true;
			if ($list1[$i] > $list2[$i])
				return false;
			$i++;
		}
		
		if ($length < count($list2)) {
			return true;
		}
		return false;
	}

	function get_dir_contents($dir) {
		$contents = Array();
		if ($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					$contents[] = $file;
				}
			}
			closedir($handle);
		}
		return $contents;
	}

	function replace_ampersand($str) {
		return str_replace("&", "%26", $str);
	}

	/**
	 * Stemming algorithm
	 * Copyright (c) 2005 Richard Heyes (http://www.phpguru.org/)
	 * All rights reserved.
	 * This script is free software.
	 * Modified to work with php versions prior 5 by Ando Saabas
	 */

	/**
	 * Regex for matching a consonant
	 */
	$regex_consonant = '(?:[bcdfghjklmnpqrstvwxz]|(?<=[aeiou])y|^y)';

	/**
	* Regex for matching a vowel
	*/
	$regex_vowel = '(?:[aeiou]|(?<![aeiou])y)';

	/**
	* Stems a word. Simple huh?
	*
	* @param  string $word Word to stem
	* @return string       Stemmed word
	*/
	function stem($word)
	{
		if (strlen($word) <= 2) {
			return $word;
		}
		
		$word = step1ab($word);
		$word = step1c($word);
		$word = step2($word);
		$word = step3($word);
		$word = step4($word);
		//$word = step5($word);
		
		return $word;
	}


	/**
	* Step 1
	*/
	function step1ab($word) {
		global $regex_vowel, $regex_consonant;
		// Part a
		if (substr($word, -1) == 's') {
			replace($word, 'sses', 'ss') || replace($word, 'ies', 'i') || replace($word, 'ss', 'ss') || replace($word, 's', '');
		}
		
		// Part b
		if (substr($word, -2, 1) != 'e' OR !replace($word, 'eed', 'ee', 0)) { // First rule
			$v = $regex_vowel;
			// ing and ed
			if(preg_match("#$v#", substr($word, 0, -3)) && replace($word, 'ing', '') || preg_match("#$v#", substr($word, 0, -2)) && replace($word, 'ed', '')) { // Note use of && and OR, for precedence reasons
				// If one of above two test successful
				if(!replace($word, 'at', 'ate') && !replace($word, 'bl', 'ble') && !replace($word, 'iz', 'ize')) {
					// Double consonant ending
					if(doubleConsonant($word) && substr($word, -2) != 'll' && substr($word, -2) != 'ss' && substr($word, -2) != 'zz') {
						$word = substr($word, 0, -1);
					} else if (m($word) == 1 AND cvc($word)) {
						$word .= 'e';
					}
				}
			}
		}
		return $word;
	}


	/**
	* Step 1c
	*
	* @param string $word Word to stem
	*/
	function step1c($word) {
		global $regex_vowel, $regex_consonant;
		$v = $regex_vowel;
		if (substr($word, -1) == 'y' && preg_match("#$v#", substr($word, 0, -1))) {
			replace($word, 'y', 'i');
		}
		return $word;
	}


	/**
	* Step 2
	*
	* @param string $word Word to stem
	*/
	function step2($word) {
		switch (substr($word, -2, 1)) {
			case 'a':
				   replace($word, 'ational', 'ate', 0)
				OR replace($word, 'tional', 'tion', 0);
				break;
			case 'c':
				   replace($word, 'enci', 'ence', 0)
				OR replace($word, 'anci', 'ance', 0);
				break;
			case 'e':
				replace($word, 'izer', 'ize', 0);
				break;
			case 'g':
				replace($word, 'logi', 'log', 0);
				break;
			case 'l':
				   replace($word, 'entli', 'ent', 0)
				OR replace($word, 'ousli', 'ous', 0)
				OR replace($word, 'alli', 'al', 0)
				OR replace($word, 'bli', 'ble', 0)
				OR replace($word, 'eli', 'e', 0);
				break;
			case 'o':
				   replace($word, 'ization', 'ize', 0)
				OR replace($word, 'ation', 'ate', 0)
				OR replace($word, 'ator', 'ate', 0);
				break;
			case 's':
				   replace($word, 'iveness', 'ive', 0)
				OR replace($word, 'fulness', 'ful', 0)
				OR replace($word, 'ousness', 'ous', 0)
				OR replace($word, 'alism', 'al', 0);
				break;
			case 't':
				   replace($word, 'biliti', 'ble', 0)
				OR replace($word, 'aliti', 'al', 0)
				OR replace($word, 'iviti', 'ive', 0);
				break;
		}
		return $word;
	}


	/**
	* Step 3
	*
	* @param string $word String to stem
	*/
	function step3($word) {
		switch (substr($word, -2, 1)) {
			case 'a':
				replace($word, 'ical', 'ic', 0);
				break;
			case 's':
				replace($word, 'ness', '', 0);
				break;
			case 't':
				   replace($word, 'icate', 'ic', 0)
				OR replace($word, 'iciti', 'ic', 0);
				break;
			case 'u':
				replace($word, 'ful', '', 0);
				break;
			case 'v':
				replace($word, 'ative', '', 0);
				break;
			case 'z':
				replace($word, 'alize', 'al', 0);
				break;
		}
		return $word;
	}


	/**
	* Step 4
	*
	* @param string $word Word to stem
	*/
	function step4($word) {
		switch (substr($word, -2, 1)) {
			case 'a':
				replace($word, 'al', '', 1);
				break;
			case 'c':
				   replace($word, 'ance', '', 1)
				OR replace($word, 'ence', '', 1);
				break;
			case 'e':
				replace($word, 'er', '', 1);
				break;
			case 'i':
				replace($word, 'ic', '', 1);
				break;
			case 'l':
				   replace($word, 'able', '', 1)
				OR replace($word, 'ible', '', 1);
				break;
			case 'n':
				   replace($word, 'ant', '', 1)
				OR replace($word, 'ement', '', 1)
				OR replace($word, 'ment', '', 1)
				OR replace($word, 'ent', '', 1);
				break;
			case 'o':
				if (substr($word, -4) == 'tion' OR substr($word, -4) == 'sion') {
				   replace($word, 'ion', '', 1);
				} else {
					replace($word, 'ou', '', 1);
				}
				break;
			case 's':
				replace($word, 'ism', '', 1);
				break;
			case 't':
				   replace($word, 'ate', '', 1)
				OR replace($word, 'iti', '', 1);
				break;
			case 'u':
				replace($word, 'ous', '', 1);
				break;
			case 'v':
				replace($word, 'ive', '', 1);
				break;
			case 'z':
				replace($word, 'ize', '', 1);
				break;
		}
		return $word;
	}


	/**
	* Step 5
	*
	* @param string $word Word to stem
	*/
	function step5($word) {
		// Part a
		if (substr($word, -1) == 'e') {
			if (m(substr($word, 0, -1)) > 1) {
				replace($word, 'e', '');
			} else if (m(substr($word, 0, -1)) == 1) {
				if (!cvc(substr($word, 0, -1))) {
					replace($word, 'e', '');
				}
			}
		}
		
		// Part b
		if (m($word) > 1 AND doubleConsonant($word) AND substr($word, -1) == 'l') {
			$word = substr($word, 0, -1);
		}
		
		return $word;
	}


	/**
	* Replaces the first string with the second, at the end of the string. If third
	* arg is given, then the preceding string must match that m count at least.
	*
	* @param  string $str   String to check
	* @param  string $check Ending to check for
	* @param  string $repl  Replacement string
	* @param  int    $m     Optional minimum number of m() to meet
	* @return bool          Whether the $check string was at the end
	*                       of the $str string. True does not necessarily mean
	*                       that it was replaced.
	*/
	function replace(&$str, $check, $repl, $m = null) {
		$len = 0 - strlen($check);
		
		if (substr($str, $len) == $check) {
			$substr = substr($str, 0, $len);
			if (is_null($m) OR m($substr) > $m) {
				$str = $substr . $repl;
			}
			return true;
		}
		
		return false;
	}


	/**
	* What, you mean it's not obvious from the name?
	*
	* m() measures the number of consonant sequences in $str. if c is
	* a consonant sequence and v a vowel sequence, and <..> indicates arbitrary
	* presence,
	*
	* <c><v>       gives 0
	* <c>vc<v>     gives 1
	* <c>vcvc<v>   gives 2
	* <c>vcvcvc<v> gives 3
	*
	* @param  string $str The string to return the m count for
	* @return int         The m count
	*/
	function m($str) {
		global $regex_vowel, $regex_consonant;
		$c = $regex_consonant;
		$v = $regex_vowel;
		
		$str = preg_replace("#^$c+#", '', $str);
		$str = preg_replace("#$v+$#", '', $str);
		
		preg_match_all("#($v+$c+)#", $str, $matches);
		
		return count($matches[1]);
	}


	/**
	* Returns true/false as to whether the given string contains two
	* of the same consonant next to each other at the end of the string.
	*
	* @param  string $str String to check
	* @return bool        Result
	*/
	function doubleConsonant($str) {
		// global $regex_consonant;
		$c = $regex_consonant;
		
		return preg_match("#$c{2}$#", $str, $matches) AND $matches[0]{0} == $matches[0]{1};
	}


	/**
	* Checks for ending CVC sequence where second C is not W, X or Y
	*
	* @param  string $str String to check
	* @return bool        Result
	*/
	function cvc($str) {
		$c = $regex_consonant;
		$v = $regex_vowel;
		
		return preg_match("#($c$v$c)$#", $str, $matches)
			AND strlen($matches[1]) == 3
			AND $matches[1]{2} != 'w'
			AND $matches[1]{2} != 'x'
			AND $matches[1]{2} != 'y';
	}

	function list_cats($parent, $lev, $color, $message) {
		// global $db, DEBUG;
		if ($lev == 0) {
			echo "<div class='submenu'>
				<ul>
					<li><a href='index.php?f=add_cat'>Add category</a></li>
				</ul>
			</div>";
			echo $message;
			echo "<div class='panel'>
				<table width='100%'>
				<tr>
					<td class='tblhead' colspan='3'>Categories</td>
				</tr>";
		}
		$space = "";
		for ($x = 0; $x < $lev; $x++) {
			$space .= "<span class='tree'>&raquo;</span>&nbsp;";
		}
		
		$query = "SELECT * FROM ".TABLE_PREFIX."categories WHERE parent_num=$parent ORDER BY category";
		$result = mysql_query($query);
		if (DEBUG > '0') echo mysql_error();
		
		if (mysql_num_rows($result) <> '') {
			while ($row = mysql_fetch_array($result)) {
				if ($color =="odrow") {
					$color = "evrow";
				} else {
					$color = "odrow";
				}
				$id = $row['category_id'];
				$cat = $row['category'];
				echo "<tr class='$color'>";
				if (!$space=="") {
					echo "<td width='90%'>
							<div>$space<a class='options' href='index.php?f=edit_cat&amp;cat_id=$id'
								title='Edit this Sub-Category'>".stripslashes($cat)."</a></div></td>
							<td class='options'><a href='index.php?f=edit_cat&amp;cat_id=$id' class='options' title='Edit this Sub-Category'>Edit</a></td>
							<td class='options'><a href='index.php?f=11&amp;cat_id=$id' title='Delete this Sub-Category'
								onclick=\"return confirm('Are you sure you want to delete? Subcategories will be lost.')\" class='options'>Delete</a></td>
						</tr>";
				} else {
					echo"<td width='90%'><a class='options' href='index.php?f=edit_cat&amp;cat_id=$id'
								title='Edit this Category'>".stripslashes($cat)."</a></td>
							<td class='options'><a href='index.php?f=edit_cat&amp;cat_id=$id' class='options' title='Edit this Category'>Edit</a></td>
							<td class='options'><a href='index.php?f=11&amp;cat_id=$id' title='Delete this Category'
								onclick=\"return confirm('Are you sure you want to delete? Subcategories will be lost.')\" class='options'>Delete</a></td>
						</tr>";
				}
				$color = list_cats($id, $lev + 1, $color, "");
			}
		}
		if ($lev == 0) {
			echo "</table>
	</div>
	";
		}
		return $color;
	}

    
	function list_catsform($parent, $lev, $color, $message, $category_id) {
		// global $db, DEBUG;
		if ($lev == 0) {
			print "\n";
		}
		$space = "";
		for ($x = 0; $x < $lev; $x++)
			$space .= "&nbsp;&nbsp;&nbsp;-&nbsp;";

		$query = "SELECT * FROM ".TABLE_PREFIX."categories WHERE parent_num=$parent ORDER BY category LIMIT 0 , 300";
		$result = mysql_query($query);
		if (DEBUG > '0') echo mysql_error();
		
		if (mysql_num_rows($result) <> '')
			while ($row = mysql_fetch_array($result)) {
				
				$id = $row['category_id'];
				$cat = $row['category'];
				$selected = " selected ";
				if ($category_id != $id) { $selected = ""; }
				print "<option ".$selected." value=\"".$id."\">".$space.stripslashes($cat)."</option>\n";
				
				$color = list_catsform($id, $lev + 1, $color, "", $category_id);
			}
		return $color;
	}

	function getmicrotime(){
		list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
	}

	function saveToLog ($query, $elapsed, $results) {
		// global $db, DEBUG;
		
		$query = trim($query);
		if(!empty($query)) {
			if ($results =="") {
				$results = 0;
			}
		
			$query =  "insert into ".TABLE_PREFIX."query_log (query, time, elapsed, results) values ('$query', now(), '$elapsed', '$results')";
			mysql_query($query);
			if (DEBUG > '0') echo mysql_error();
		}
	}

	function validate_url($input) {
		// global Configure::read('mytitle');
		//Standard Url test
		if (! preg_match('=(https?|ftp)://[a-z0-9]([a-z0-9-]*[a-z/0-9])?\.[a-z0-9]=i', ($input))) {
			echo "<h1>Configure::read('mytitle')</h1>
				<br />
				<p class='warnadmin cntr'>
				Invalid input for 'Url'
				</p>
				<a class='bkbtn' href='addurl.php' title='Go back to Submission Form'>Back</a>                                                                    
				</body>
				</html>
			";
			die ('');
		}
		
		// Do we have a valid DNS ? This test is disabled for localhost application as checkdnsrr needs internet access
		$localhost = strstr(htmlspecialchars(@$_SERVER['HTTP_REFERER']), "localhost");
		if (!$localhost) { 
			if (preg_match("/www/i", $input)) {
				$input = ereg_replace ('http://','',$input);
				$input1 = $input;
				$pos = strpos($input1,"/");
				if($pos != '') $input1 = substr($input1,0,$pos);
				if(!checkdnsrr($input1, "A")) {
					echo "<h1>Configure::read('mytitle')</h1>
						<br />
						<p class='warnadmin cntr'>                       
						Invalid url input. No DNS resource available for this url
						<a class='bkbtn' href='addurl.php' title='Go back to Submission Form'>Back</a>                                                                    
						</body>
						</html>
					";
					die ('');
				}
				$input = str_replace("www", "http://www", $input);
			}
		}
		return ($input);
	}

	function validate_email($input) {
		//Standard e-mail test
		if(!preg_match('/^[\w.+-]{2,}\@[\w.-]{2,}\.[a-z]{2,6}$/', $input)) {
			echo "<h1>Configure::read('mytitle')</h1>
				<br />           
				<p class='warnadmin cntr'>
				Invalid input for 'e-mail account'
				</p>
				<a class='bkbtn' href='addurl.php' title='Go back to Submission Form'>Back</a>                                                                    
				</body>
				</html>
			";
			die ('');
		}
		
		// Check if Mail Exchange Resource Record (MX-RR)  is valid and also is stored in Domain Name System (DNS) 
		// This test is disabled for localhost applications as getmxrr needs internet access 
		$localhost = strstr(htmlspecialchars(@$_SERVER['HTTP_REFERER']), "localhost");        
		if (!$localhost) { 
			if(!getmxrr(substr(strstr($input, '@'), 1), $mxhosts)) {
				echo "<h1>Configure::read('mytitle')</h1>
					<br />
					<p class='warnadmin cntr'>
					Invald e-mail account.<br />
					There is no valid Mail Exchange Resource Record (MX-RR)<br />
					on the Domain Name System (DNS)
					</p>
					<a class='bkbtn' href='addurl.php' title='Go back to Submission Form'>Back</a>                                                                    
					</body>
					</html>
				";
				die ('');   
			}	
		}        
		return ($input);
	}

	function cleanup_text ($input='', $preserve='', $allowed_tags='') {
		if (empty($preserve)) 
			{ 
				$input = strip_tags($input, $allowed_tags);
			}
		$input = htmlspecialchars($input, ENT_QUOTES);
		return $input;
	}

	function cleaninput($input) {
		
		if (get_magic_quotes_gpc()) {
			$input = stripslashes($input); // delete quotes
		}
		
		// prevent Directory Traversal attacks
		if(preg_match("/..\/|..\\\/i", $input)) {
			$input = '';
		}
		if(substr_count($input,"'") != '1') {
			$input = mysql_real_escape_string($input); // prevent SQL-injection
		} else {       
			$input = str_replace('\\','\\\\', $input); // if one slash is part of the query, we have to allow it...
			$input = str_replace('"','\"', $input); // never the less we need to prevent SQL attacks
		}
		
		//prevent XSS-attack and Shell-execute
		if (preg_match("/cmd|CREATE|DELETE|DROP|eval|EXEC|File|INSERT/i",$input)) {
			$input = '';
		}
		if (preg_match("/LOCK|PROCESSLIST|SELECT|shell|SHOW|SHUTDOWN/i",$input)) {
			$input = '';
		}
		if (preg_match("/SQL|SYSTEM|TRUNCATE|UNION|UPDATE|DUMP/i",$input)) {
			$input = '';
		}
		
		return $input;
	}
    
	function footer () {
		// global Configure::read('add_url'), Configure::read('most_pop'), $db;

		echo "
			<p class='stats'>
				<a href='http://www.sphider-plus.eu' title='Link: Visit Sphider-plus site in new window' target='rel'>Visit
				<img class='mid' src='".WEBROOT_DIR."sphider-plus-logo.gif' alt='Visit Sphider site in new window' height='39' width='42' /> Sphider-plus</a>
			</p>
		";
	}

    function error_handler($errNo, $errStr, $errFile, $errLine){
        if(ob_get_length()) ob_clean();             // clear any output that has already been generated

        $error_message = 'ERRNO: ' . $errNo . chr(10) .
                        'TEXT: ' . $errStr . chr(10) .
                        'LOCATION: ' . $errFile . 
                        ', line ' . $errLine;
        echo $error_message;
        exit;       // stop executing any script
    }
    
?>
