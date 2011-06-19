<?php

	/* 
		Perch on-the-fly image resizer
		** CAUTION ** DIRTY PHP: use at own risk. 
		Please add to, enhance, improve.
		Cole Henley
		@cole007
	*/

	// get the URI
	$uri = $_SERVER['REQUEST_URI'];
	
	// pull out the bit after the last '/'
	$pos = strrpos($uri,'/')+1;
	$src = substr($uri,$pos);
	
	// pull out the position of the last '-'
	$tok = strrpos($src,'-');
	
	// pull out the position of the last '-'
	$ext = strrpos($src,'.');
	
	// pull out the variables for height and width (if present)
	$var = substr($src,$tok+1);
	$var = substr($var,0,strrpos($var,'.'));
	$h = strrpos($var,'h');
	$hi = substr($var, $h + 1);
	$w = substr($var, 1);
	$wi = substr($w, 0, strpos($w,'h'));
	
	// pull out src file
	$tok = substr($src,0,$tok);
	$ext = substr($src,$ext);
	$src_file = $tok . $ext;
	
	// redraw image and save
	if (file_exists($src_file)) {
		
		// get source image size 
		$image = getimagesize($src_file);
		// calculate aspect ratio of src file
		$ratio = $hi / $wi;
		
		// if JPEG
		if (eregi('(jpg|jpeg)$',$image['mime'])) {
			$imageCopy = imagecreatefromjpeg($src_file);
		// if PNG 
		} elseif (eregi('png$',$image['mime'])) {
			$imageCopy = imagecreatefrompng($src_file);
		} 
		
		// if successful
		if (isset($imageCopy)) {
			$src_ratio = $image[1] / $image [0];
			
			// if landscape and aspect ratio too wide
			if ($image[0] > $image[1] && $src_ratio > $ratio) {
				$src_w = $image[0];
				$src_h = $image[0] * $ratio;
				$src_x = 0;
				$src_y = ($image[1] - $src_h) / 2;
			// if landscape and aspect ratio too tall
			} elseif ($image[0] > $image[1]) {
				$src_w = $image[1] / $ratio;
				$src_h = $image[1];
				$src_x = ($image[0] - $src_w) / 2;
				$src_y = 0;
			// if portrait (or square) and aspect ratio too tall
			} elseif ($image[1] > $image[0] && $src_ratio < $ratio) {
				$src_w = $image[1] / $ratio;
				$src_h = $image[1];
				$src_x = ($image[0] - $src_w) / 2;
				$src_y = 0;
			// if portrait (or square) and aspect ratio too wide
			} else {
				$src_w = $image[0];
				$src_h = $image[0] * $ratio;
				$src_x = 0;
				$src_y = ($image[1] - $src_h) / 2;
			}
			
			// create new image resource
			$new = imagecreatetruecolor($wi,$hi);
			imagecopyresampled($new,$imageCopy,0,0,$src_x,$src_y,$wi,$hi,ceil($src_w),ceil($src_h)) or die('could not redraw image (imagecopyresampled)'); 
			// if jpeg
			if ($ext == '.jpg') {
				header('Content-type: image/jpeg');
				imagejpeg($new,$src,85);
				imagejpeg($new);
			// if png	
			} elseif($ext == '.png') {
				header('Content-type: image/png');
				imagepng($new,$src,8);
				imagepng($new);
			}
			// kill all used image resources
			imagedestroy($new);
			imagedestroy($imageCopy);
			exit;
		}
	} 
	
	// return 404
	header("HTTP/1.0 404 Not Found");
	
?>