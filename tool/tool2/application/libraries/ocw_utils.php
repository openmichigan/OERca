<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * OCW_utils Class
 *
 * @package		OCW Tool
 * @subpackage	Libraries
 * @category	Utilities
 * @author		David Hutchful <dkhutch@umich.edu>
 */
class OCW_utils {

	/**
	 * Constructor
	 *
	 * @access	public
	 */	
	function OCW_utils()
	{
		$this->object =& get_instance();
		log_message('debug', "OCW_utils Class Initialized");
	}

	// calculate the difference between two dates.
	function time_diff_in_words($from_time, $incl_secs=false)
	{
        $from_time = strtotime($from_time); 
        $to_time = mktime(); 
        $diff_in_min = round(abs($to_time - $from_time) / 60);
        $diff_in_sec = round(abs($to_time - $from_time));
		$secs = 0;
	
		if ($diff_in_sec >= 0 && $diff_in_sec <= 5) {
            $secs = 'less than 5 seconds'; } 
		elseif ($diff_in_sec >= 6 && $diff_in_sec <= 10) {
            $secs = 'less than 10 seconds'; }
		 elseif ($diff_in_sec >= 11 && $diff_in_sec <= 20) {
            $secs = 'less than 20 seconds'; }
		 elseif ($diff_in_sec >= 21 && $diff_in_sec <= 40) {
            $secs = 'half a minute'; }
		 elseif ($diff_in_sec >= 41 && $diff_in_sec <= 59) {
            $secs = 'less than a minute'; 
		} else  { $secs = '1 minute'; }

		if ($diff_in_min >= 0 && $diff_in_min <= 1) {
			return 'Today'; }
            #return ($diff_in_min==0) ? 'less than a minute' 
			#						 : (($incl_secs) ? $secs : '1 minute'); }
		elseif ($diff_in_min >= 2 && $diff_in_min <= 45) {
          	return 'Today'; }
          	#return 'about 1 hour'; }
		elseif ($diff_in_min >= 46 && $diff_in_min <= 90) {
          	return 'Today'; }
          	#return  $diff_in_min.' minutes'; } 
		elseif ($diff_in_min >= 90 && $diff_in_min < 1440) {
          	return 'Today'; }
            #return 'about '.round(floatval($diff_in_min) / 60.0).' hours'; } 
		elseif ($diff_in_min >= 1441 && $diff_in_min < 2880) {
          	return '1 day ago'; }
	    else {  return round($diff_in_min / 1440).' days ago'; }
	}

	/**
     * send_response  - return a value in html/xml/JSON format to a 
     * XMLHTTP object request (AJAX call)
     *
     * @param mixed $value response to send 
     */
    function send_response($value, $type='plain')
    {
		include_once 'JSON.php';

		$json = new Services_JSON();

        if ($type == 'html') {
            header('Content-Type: text/html');
            echo $value;
        } elseif ($type == 'xml') {
            header('Content-Type: text/xml');
            echo $value;
        } elseif ($type == 'plain') {
            header('Content-Type: text/plain');
            echo $value;
        } elseif ($type == 'php') {
            header('Content-Type: text/plain');
            echo serialize($value);
        } elseif ($type == 'phpdump') {
            header('Content-Type: text/html');
            $this->dump($value);
        } else {
            $json = $json->encode($value);
            header('Content-Type: text/plain');
            echo $json;
        }
        exit;
    }

	/**
	 * Display the innards of a variable 
	 *
	 * @access	public
	 * @param	mixed 
	 * @return	void
	 */
	function dump($var)
	{
		print '<pre>'; print_r($var); print '</pre>';
	}

	
	/**
	 * Fetch a single line of text from the language array
	 *
	 * @access	public
	 * @param	string	the language line
	 * @return	string
	 */
	function icon($mimetype='', $width='', $height='')
	{
		$file = '';
		$width = ($width=='') ? '' : $width;
		$height = ($height=='') ? '15' : $height;

		 switch($mimetype) {
    		case 'application/mspowerpoint': $file = 'ppt.gif'; break;
    		case 'application/msword': $file = 'msword.gif'; break;
    		case 'application/octet-stream': $file =''; break;
    		case 'application/pdf': $file = 'file_acrobat.gif'; break;
    		case 'application/postscript': $file = ''; break;
    		case 'application/smil': $file = ''; break;
    		case 'application/vnd.ms-excel': $file = ''; break;
    		case 'application/x-cdlink': $file = ''; break;
    		case 'application/x-gzip': $file = ''; break;
  		  	case 'application/x-shockwave-flash': $file = ''; break;
    		case 'application/x-tar': $file = ''; break;
    		case 'application/zip': $file = ''; break;
   			case 'audio/midi': $file = ''; break;
    		case 'audio/mpeg': $file = ''; break;
    		case 'audio/TSP-audio': $file = ''; break;
    		case 'audio/x-pn-realaudio': $file = ''; break;
    		case 'audio/x-realaudio': $file = ''; break;
    		case 'audio/x-wav': $file = ''; break;
    		case 'image/gif': $file = ''; break;
    		case 'image/jpeg': $file = ''; break;
    		case 'image/png': $file = ''; break;
    		case 'image/tiff': $file = ''; break;
    		case 'image/x-xbitmap': $file = ''; break;
    		case 'model/vrml': $file = ''; break;
    		case 'text/css': $file = 'page.png'; break;
    		case 'text/html': $file = ''; break;
    		case 'text/plain': $file = 'page.png'; break;
    		case 'text/rtf': $file = 'page.png'; break;
    		case 'text/xml': $file = ''; break;
    		case 'video/mpeg': $file = ''; break;
    		case 'video/quicktime': $file = ''; break;
    		case 'video/vnd.vivo': $file = ''; break;
    		case 'video/x-msvideo': $file = ''; break;
    		case 'folder': $file = 'folder.gif'; break;
        	default: $file='page.png';
    	}

		$img = property('app_img').'/mimetypes/'; 
		return ($file=='') 
		   ? ''
		   : '<img src="'.$img.$file.'" width="'.$width.'" height="'.
			  $height.'" />';
	}

	function new_image($text) { new create_image($text); }
	
	function create_co_list($cid,$mid,$objs)
	{
		$size = sizeof($objs);
		$list = '<li id="carousel-item-0">';
	
		for($i = 0; $i < $size; $i++) {
			$y = $this->create_co_img($cid, $mid, 
									  $objs[$i]['name'],
									  $objs[$i]['location']);
	
			if ((($i+1) % 3) == 0) {
				$list .= '<div class="column span-3 last">'.$y.'</div></li>';
				if (($i+1) < $size) {
					$list .=  ((($i + 1) % 12) == 0) 
						  ? "\n<li id=\"carousel-item-".(($i+1)/12)."\">"  
					  	: "\n<li>";
				}
			} else {
				$list .= '<div class="column span-3 colborder">'.$y.'</div>';
			}
		} 
	
		if (preg_match('/<li>$/',$list)) {
			$list = preg_replace('/<li>$/','',$list);
		}
		if (!preg_match('/<\/li>$/',$list)) { $list .= '</li>';  }
		return $list;
	}
	
	function create_co_img($cid, $mid, $name, $loc, $linkable=true, $shrink=true) 
	{
	   $imgpath = ereg_replace("\.",'/',$name);
	   $p_imgurl = property('app_uploads_url').$imgpath.'/'.$name.'_grab.png';
	   $p_imgpath = property('app_uploads_path').$imgpath.'/'.$name.'_grab.png';
	   $j_imgurl = property('app_uploads_url').$imgpath.'/'.$name.'_grab.jpg';
	   $j_imgpath = property('app_uploads_path').$imgpath.'/'.$name.'_grab.jpg';
	   $imgurl = '';

	   if (is_readable($p_imgpath) || is_readable($j_imgpath)) {
			$thumb_found = true;	
			$imgurl = (is_readable($p_imgpath)) ? $p_imgurl : $j_imgurl;
	   } else {
			$thumb_found = false;	
	   }

	   $imgUrl = ($thumb_found) 
			   ? $imgurl 
			   : property('app_site_url')."materials/make_image/$name";
	   $aurl = '<a href="'.site_url("materials/object_info/$cid/$mid/$name").'?TB_iframe=true&height=500&width=520" class="smoothbox">';

		$size = ($shrink) ? 'width="85" height="85"':'width="300" height="300"';
		$title = 'title="CO: '.$name.' :: Location: Page '.$loc.'<br>Click image to edit"';

	   return ($linkable) 
				? $aurl.'<img id="'.$name.'" class="carousel-image tooltip" '.$title.' src="'.$imgUrl.'" '. $size .'"/></a>'.$this->create_slide($cid, $mid, $loc)
				: '<img id="'.$name.'" class="carousel-image tooltip" '.$title.' src="'.$imgUrl.'" '.$size.' />';
	}

	function create_corep_img($cid, $mid, $name, $loc, $linkable=true, $shrink=true) 
	{
	   $imgpath = ereg_replace("\.",'/',$name);
	   $p_imgurl = property('app_uploads_url').$imgpath.'/'.$name.'_rep.png';
	   $p_imgpath = property('app_uploads_path').$imgpath.'/'.$name.'_rep.png';
	   $j_imgurl = property('app_uploads_url').$imgpath.'/'.$name.'_rep.jpg';
	   $j_imgpath = property('app_uploads_path').$imgpath.'/'.$name.'_rep.jpg';
	   $imgurl = '';

	   if (is_readable($p_imgpath) || is_readable($j_imgpath)) {
			$thumb_found = true;	
			$imgurl = (is_readable($p_imgpath)) ? $p_imgurl : $j_imgurl;
	   } else {
			$thumb_found = false;	
			$name = "none";
	   }

	   $imgUrl = ($thumb_found) 
			   ? $imgurl 
			   : property('app_site_url')."materials/make_image/$name";
	   $aurl = '<a href="'.site_url("materials/object_info/$cid/$mid/$name").'?TB_iframe=true&height=500&width=520" class="smoothbox">';

		$size = ($shrink) ? 'width="85" height="85"':'width="300" height="300"';
		$title = 'title="CO: '.$name.' :: Location: Page '.$loc.'<br>Click image to edit"';

	   return ($linkable) 
				? $aurl.'<img id="'.$name.'" class="carousel-image tooltip" '.$title.' src="'.$imgUrl.'" '.$size.'/></a>'.$this->create_slide($cid, $mid, $loc)
				: '<img id="'.$name.'" class="carousel-image" '.$title.' src="'.$imgUrl.'" '.$size.'/>';
	}

	function create_slide($cid,$mid,$loc,$text='view context')
	{
	   $p_imgurl = property('app_uploads_url')."c$cid/m$mid/c$cid.m$mid.slide_$loc.png";
	   $p_imgpath = property('app_uploads_path')."c$cid/m$mid/c$cid.m$mid.slide_$loc.png";
	   $j_imgurl = property('app_uploads_url')."c$cid/m$mid/c$cid.m$mid.slide_$loc.jpg";
	   $j_imgpath = property('app_uploads_path')."c$cid/m$mid/c$cid.m$mid.slide_$loc.jpg";
	   $imgurl = '';

	   if (is_readable($p_imgpath) || is_readable($j_imgpath)) {
			$thumb_found = true;	
			$imgurl = (is_readable($p_imgpath)) ? $p_imgurl : $j_imgurl;
	   } else {
			$thumb_found = false;	
	   }
	   
	   $aurl = '<a href="'.$imgurl.'" class="smoothbox" title="" rel="gallery-slide">'.$text.'</a>';
	   return ($thumb_found) ? $aurl : '<small>no context view found</small>';

	}

    /**
     * Escape url 
     *
     * @access  public
     * @param   string  the url to be escaped
     * @return  string
     */
    function escapeUrl($url)
    {
        return rawurlencode($url);
    }
}


/*
    Dynamic Heading Generator
    By Stewart Rosenberger
    http://www.stewartspeak.com/headings/    

    This script generates PNG images of text, written in
    the font/size that you specify. These PNG images are passed
    back to the browser. Optionally, they can be cached for later use. 
    If a cached image is found, a new image will not be generated,
    and the existing copy will be sent to the browser.

    Additional documentation on PHP's image handling capabilities can
    be found at http://www.php.net/image/    
*/

class create_image 
{

	function create_image($text)
	{
		$font_file  = property('app_fonts').'AppleGaramond.ttf' ;
		$font_size  = 15 ;
		$font_color = '#000000' ;
		$background_color = '#ffffff' ;
		$transparent_background  = true ;
		$cache_images = true ;
		$cache_folder = 'cache' ;
	
	
	/*
	 ---------------------------------------------------------------------
	   For basic usage, you should not need to edit anything below this comment.
	   If you need to further customize this script's abilities, make sure you
	   are familiar with PHP and its image handling capabilities.
	 ----------------------------------------------------------------------
	*/
		$mime_type = 'image/png' ;
		$extension = '.png' ;
		$send_buffer_size = 4096 ;

		// check for GD support
		if(!function_exists('ImageCreate'))
	    	$this->fatal_error('Error: Server does not support PHP image generation') ;
	
		// clean up text
		if(empty($text))
	    	$this->fatal_error('Error: No text specified.');
	    
		if(get_magic_quotes_gpc())
	    	$text = stripslashes($text) ;
		$text = $this->javascript_to_html($text) ;
	
		// look for cached copy, send if it exists
		$hash = md5(basename($font_file) . $font_size . $font_color .  $background_color . $transparent_background . $text) ;
		$cache_filename = $cache_folder . '/' . $hash . $extension ;
		if($cache_images && ($file = @fopen($cache_filename,'rb')))
		{
	    	header('Content-type: ' . $mime_type) ;
	    	while(!feof($file))
	        	print(($buffer = fread($file,$send_buffer_size))) ;
	    	fclose($file) ;
	    	exit ;
		}
	
		// check font availability
		$font_found = is_readable($font_file) ;
		if(!$font_found)
		{
	    	$this->fatal_error('Error: The server is missing the specified font.') ;
		}
	
		// create image
		$background_rgb = $this->hex_to_rgb($background_color) ;
		$font_rgb = $this->hex_to_rgb($font_color) ;
		$dip = $this->get_dip($font_file,$font_size) ;
		$box = @ImageTTFBBox($font_size,0,$font_file,$text) ;
		#$image = @ImageCreate(abs($box[2]-$box[0]),abs($box[5]-$dip)) ;
		$image = @ImageCreate(abs($box[2]-$box[0]) + 1,85) ;
		if(!$image || !$box)
		{
		    $this->fatal_error('Error: The server could not create this heading image.') ;
		}
	
		// allocate colors and draw text
		$background_color = @ImageColorAllocate($image,$background_rgb['red'],
	    	$background_rgb['green'],$background_rgb['blue']) ;
		$font_color = ImageColorAllocate($image,$font_rgb['red'],
	    $font_rgb['green'],$font_rgb['blue']) ;   
		ImageTTFText($image,$font_size,0,-$box[0],abs($box[5]-$box[3])-$box[1],
	    $font_color,$font_file,$text) ;
	
		// set transparency
		if($transparent_background)
	    	ImageColorTransparent($image,$background_color) ;
	
		header('Content-type: ' . $mime_type) ;
		ImagePNG($image) ;
	
		// save copy of image for cache
		if($cache_images)
		{
	    	@ImagePNG($image,$cache_filename) ;
		}	
	
		ImageDestroy($image) ;
		exit ;
	}	
	
	/*
		try to determine the "dip" (pixels dropped below baseline) of this
		font for this size.
	*/
		function get_dip($font,$size)
		{
			$test_chars = 'abcdefghijklmnopqrstuvwxyz' .
					      'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
						  '1234567890' .
						  '!@#$%^&*()\'"\\/;.,`~<>[]{}-+_-=' ;
			$box = @ImageTTFBBox($size,0,$font,$test_chars) ;
			return $box[3] ;
		}


		/*
	    attempt to create an image containing the error message given. 
	    if this works, the image is sent to the browser. if not, an error
	    is logged, and passed back to the browser as a 500 code instead.
		*/
		function fatal_error($message)
		{
	    	// send an image
	    	if(function_exists('ImageCreate'))
	    	{
	        	$width = ImageFontWidth(5) * strlen($message) + 10 ;
	        	$height = ImageFontHeight(5) + 10 ;
	        	if($image = ImageCreate($width,$height))
	        	{
	            	$background = ImageColorAllocate($image,255,255,255) ;
	            	$text_color = ImageColorAllocate($image,0,0,0) ;
	            	ImageString($image,5,5,5,$message,$text_color) ;    
	            	header('Content-type: image/png') ;
	            	ImagePNG($image) ;
	            	ImageDestroy($image) ;
	            	exit ;
	        	}
	    	}
	
	    	// send 500 code
	    	header("HTTP/1.0 500 Internal Server Error") ;
	    	print($message) ;
	   		 exit ;
		}


		/* 
    	decode an HTML hex-code into an array of R,G, and B values.
    	accepts these formats: (case insensitive) #ffffff, ffffff, #fff, fff 
		*/    
		function hex_to_rgb($hex)
		{
    		// remove '#'
    		if(substr($hex,0,1) == '#')
        		$hex = substr($hex,1) ;

    		// expand short form ('fff') color
    		if(strlen($hex) == 3)
    		{
        		$hex = substr($hex,0,1) . substr($hex,0,1) .
               	substr($hex,1,1) . substr($hex,1,1) .
               	substr($hex,2,1) . substr($hex,2,1) ;
    		}

    		if(strlen($hex) != 6)
        		$this->fatal_error('Error: Invalid color "'.$hex.'"') ;

    		// convert
    		$rgb['red'] = hexdec(substr($hex,0,2)) ;
    		$rgb['green'] = hexdec(substr($hex,2,2)) ;
    		$rgb['blue'] = hexdec(substr($hex,4,2)) ;

    		return $rgb ;
		}


		/*
    	convert embedded, javascript unicode characters into embedded HTML
    	entities. (e.g. '%u2018' => '&#8216;'). returns the converted string.
		*/
		function javascript_to_html($text)
		{
    		$matches = null ;
    		preg_match_all('/%u([0-9A-F]{4})/i',$text,$matches) ;
    		if(!empty($matches)) for($i=0;$i<sizeof($matches[0]);$i++)
        	$text = str_replace($matches[0][$i],
                            '&#'.hexdec($matches[1][$i]).';',$text) ;

    		return $text ;
		}
}
?>
