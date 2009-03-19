<?
    session_start();
    //Define your variables here
    $fontsize = 20;
    $numcharacters = 7;
    $bgcolor = "BDE4D4";
    $textcolor = "000077";
    $num_interference_lines = 3;

    //Select what should appear as part of the Captcha string
    $str_includes_numbers = true;       //  if you want to see numbers
    $str_includes_uppercase = true;     //  if you want to see uppercase character
    $str_includes_lowercase = false;    //  if you want to see lowercase character

    //Generate string for Captcha
    $_SESSION['currentcaptcha'] = random_string($numcharacters, $str_includes_numbers, $str_includes_uppercase, $str_includes_lowercase);

    //Send the image to the browser
    create_image($numcharacters, $fontsize, $bgcolor, $textcolor, $num_interference_lines);
    exit();

    function create_image($length, $fontsize, $bgcolor, $textcolor, $numlines) {
        //Set the image size
    	$trifont = 1.5 * $fontsize;
        $width = ($trifont * $length);
        $height = 3 * $fontsize;

        //Create the image resource 
        $image = imagecreate($width, $height);  

        //Required colors
        $forecolor = imagecolorallocate($image, hexdec(substr($textcolor,0,2)), hexdec(substr($textcolor,2,2)), hexdec(substr($textcolor,4,2)));
        $backcolor = imagecolorallocate($image, hexdec(substr($bgcolor,0,2)), hexdec(substr($bgcolor,2,2)), hexdec(substr($bgcolor,4,2)));

        //Background colour 
        imagefill($image, 0, 0, $backcolor); 

        //Inform browser what will come in 
        header("Content-Type: image/jpeg");
    	
    	for($i = 0; $i < $length; $i++) {
    		$source = imagecreate($trifont, $trifont);

    		//  Create the colours
    		$forecolors = imagecolorallocate($source, hexdec(substr($textcolor,0,2)), hexdec(substr($textcolor,2,2)), hexdec(substr($textcolor,4,2)));
    		$backcolors = imagecolorallocate($source, hexdec(substr($bgcolor,0,2)), hexdec(substr($bgcolor,2,2)), hexdec(substr($bgcolor,4,2)));

    		imagefill($source, 0, 0, $backcolors); 		
    		imagettftext($source, $fontsize, 0, 0, 18, $forecolors, "captcha.ttf", substr($_SESSION['currentcaptcha'], $i, 1));

            //Rotate the character
    		switch(mt_rand(1,8)) {
                case 1:
    				$source = imagerotate($source, -20, $backcolors);
                    break;
                case 2:
    				$source = imagerotate($source, 20, $backcolors);
                    break;
                case 3:
    				$source = imagerotate($source, -15, $backcolors);
                    break;
                case 4:
    				$source = imagerotate($source, 15, $backcolors);
                    break;
                case 5:
    				$source = imagerotate($source, -10, $backcolors);
                    break;
                case 6:
    				$source = imagerotate($source, 10, $backcolors);
                    break;
                case 7:
    				$source = imagerotate($source, -2, $backcolors);
                    break;
                case 8:
    				$source = imagerotate($source, 2, $backcolors);
                    break;
    		}

    		$loc_x = 5+($trifont*$i);
    		$loc_y = $trifont/2 + mt_rand((-1 * $fontsize/10),($fontsize/10));
    		$size_x = $trifont * (mt_rand(100, 110) / 100);
    		$size_y = $trifont * (mt_rand(100, 110) / 100);
    		
    		imagecopymerge($image,$source,$loc_x,$loc_y,0,0,$size_x,$size_y,90);
    		imagedestroy($source);
    	}
    	
        //  Include some lines
    	imagesetstyle($image, array($forecolor, $forecolor, $forecolor, $forecolor, $forecolor, $forecolor, $forecolor, $forecolor, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT));
        imagerectangle($image,0,0,$width-1,$height-1,$forecolor);

    	for($i = 1; $i < (1 + $numlines); $i++) {
    		$from_x = mt_rand(0, $width);
    		$from_y = mt_rand(0, $height);
    		$to_x = mt_rand(0, $width);
    		$to_y = mt_rand(0, $height);
    		imageline($image, $from_x, $from_y, $to_x, $to_y, IMG_COLOR_STYLED); 
    	}
     
        //  Send the Captcha
        imagejpeg($image);
        imagedestroy($image);
    }

    function random_string($len, $num, $uc, $lc) {
        if (!$len || $len<1 || $len>100) {
            print "Error: \"Length\" out of range (1-100)<br>\n";
            return;
        }
        if (!$num && !$uc && !$lc) {
            print "Error: No character types specified<br>\n";
            return;
        }

        $s="";
        $i=0;
        do {
            switch(mt_rand(1,3)) {
                // Numbers - ASCII character (0:48 through 9:57)
                case 1:
                    if ($num==1) {
                        $s .= chr(mt_rand(48,57));
                        $i++;
                    }
                    break;

                // Uppercase letter - ASCII character (a:65 through z:90)
                case 2:
                    if ($uc==1) {
                        $s .= chr(mt_rand(65,90));
                        $i++;
                    }
                    break;

                // Lowercase letter - ASCII character (A:97 through Z:122)
                case 3:
                    if ($lc==1) {
                        $s .= chr(mt_rand(97,122));
                        $i++;
                    }
                    break;
            }
        } while ($i<$len);

        return $s;
    }
?>