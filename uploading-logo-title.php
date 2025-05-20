<?
/// check if logged in?

session_start();

	# Check for session timeout, else initiliaze time
	if (isset($_SESSION['timeout'])) {	
		# Check Session Time for expiry
		#
		# Time is in seconds. 10 * 60 = 600s = 10 minutes
		if ($_SESSION['timeout'] + 30 * 60 < time()){
			session_destroy();
		}
	}
	else {
		# Initialize variables
		$_SESSION['user']="";
		$_SESSION['pass']="";
		$_SESSION['timeout']=time();
	}

	# Store POST data in session variables
	if (isset($_POST["user"])) {	
		$_SESSION['user']=$_POST['user'];
		$_SESSION['pass']=hash('sha256',$_POST['pass']);
	}

	# Check Login Data
	#
	if($_SESSION['user'] == "kt"
	&& $_SESSION['pass'] == hash('sha256', '!Tregoe2017'))
	{
	

// get input from url
$name = htmlspecialchars_decode(rawurldecode($_GET['name']));
$name = str_replace(' ', '_', $name);
$clientlogo = htmlspecialchars_decode(rawurldecode($_GET['logo']));

////// IMAGE RESIZE & CORRECT EXTENSION //////

   //Image Processing
    $cover = $clientlogo;
    $cover_tmp_name = $_FILES['cover']['tmp_name'];
    $cover_img_path = 'logos/';
    $type = exif_imagetype('logos/'.$cover);

if ($type == (IMAGETYPE_PNG || IMAGETYPE_JPEG || IMAGETYPE_GIF || IMAGETYPE_BMP)) {
        $cover_pre_name = md5($cover);  //Just to make a image name random and cool :D
/**
 * @description : possible exif_imagetype() return values in $type
 * 1 - gif image
 * 2 - jpg image
 * 3 - png image
 * 6 - bmp image
 */
        switch ($type) {    #There are more type you can choose. Take a look in php manual -> http://www.php.net/manual/en/function.exif-imagetype.php
            case '1' :
                $cover_format = 'gif';
                break;
            case '2' :
                $cover_format = 'jpg';
                break;
            case '3' :
                $cover_format = 'png';
                break;
            case '6' :
                $cover_format = 'bmp';
                break;

            default :
                die('There is an error processing the image -> please try again with a new image');
                break;
        }
    $cover_name = $cover_pre_name . '.' . $cover_format;
      //Checks whether the uploaded file exist or not
      //      if (file_exists($cover_img_path . $cover_name)) {
      //          $extra = 1;
      //          while (file_exists($cover_img_path . $cover_name)) {
      //  $cover_name = md5($cover) . $extra . '.' . $cover_format;
      //              $extra++;
      //          }
      //      }
    rename('logos/'.$cover, 'logos/'.$name."_XYZ_".$cover_name);
    $clientlogo = $name."_XYZ_".$cover_name;



///////// RESIZE IMAGES 
/**
 * easy image resize function
 * @param  $file - file name to resize
 * @param  $string - The image data, as a string
 * @param  $width - new image width
 * @param  $height - new image height
 * @param  $proportional - keep image proportional, default is no
 * @param  $output - name of the new file (include path if needed)
 * @param  $delete_original - if true the original image will be deleted
 * @param  $use_linux_commands - if set to true will use "rm" to delete the image, if false will use PHP unlink
 * @param  $quality - enter 1-100 (100 is best quality) default is 100
 * @return boolean|resource
 */
  function smart_resize_image($file,
                              $string             = null,
                              $width              = 0, 
                              $height             = 0, 
                              $proportional       = false, 
                              $output             = 'file', 
                              $delete_original    = true, 
                              $use_linux_commands = false,
  							  $quality = 100
  		 ) {
      
    if ( $height <= 0 && $width <= 0 ) return false;
    if ( $file === null && $string === null ) return false;

    # Setting defaults and meta
    $info                         = $file !== null ? getimagesize($file) : getimagesizefromstring($string);
    $image                        = '';
    $final_width                  = 0;
    $final_height                 = 0;
    list($width_old, $height_old) = $info;
	$cropHeight = $cropWidth = 0;

    # Calculating proportionality
    if ($proportional) {
      if      ($width  == 0)  $factor = $height/$height_old;
      elseif  ($height == 0)  $factor = $width/$width_old;
      else                    $factor = min( $width / $width_old, $height / $height_old );

      $final_width  = round( $width_old * $factor );
      $final_height = round( $height_old * $factor );
    }
    else {
      $final_width = ( $width <= 0 ) ? $width_old : $width;
      $final_height = ( $height <= 0 ) ? $height_old : $height;
	  $widthX = $width_old / $width;
	  $heightX = $height_old / $height;
	  
	  $x = min($widthX, $heightX);
	  $cropWidth = ($width_old - $width * $x) / 2;
	  $cropHeight = ($height_old - $height * $x) / 2;
    }

    # Loading image to memory according to type
    switch ( $info[2] ) {
      case IMAGETYPE_JPEG:  $file !== null ? $image = imagecreatefromjpeg($file) : $image = imagecreatefromstring($string);  break;
      case IMAGETYPE_GIF:   $file !== null ? $image = imagecreatefromgif($file)  : $image = imagecreatefromstring($string);  break;
      case IMAGETYPE_PNG:   $file !== null ? $image = imagecreatefrompng($file)  : $image = imagecreatefromstring($string);  break;
      default: return false;
    }
    
    
    # This is the resizing/resampling/transparency-preserving magic
    $image_resized = imagecreatetruecolor( $final_width, $final_height );
    if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) {
      $transparency = imagecolortransparent($image);
      $palletsize = imagecolorstotal($image);

      if ($transparency >= 0 && $transparency < $palletsize) {
        $transparent_color  = imagecolorsforindex($image, $transparency);
        $transparency       = imagecolorallocate($image_resized, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
        imagefill($image_resized, 0, 0, $transparency);
        imagecolortransparent($image_resized, $transparency);
      }
      elseif ($info[2] == IMAGETYPE_PNG) {
        imagealphablending($image_resized, false);
        $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
        imagefill($image_resized, 0, 0, $color);
        imagesavealpha($image_resized, true);
      }
    }
    imagecopyresampled($image_resized, $image, 0, 0, $cropWidth, $cropHeight, $final_width, $final_height, $width_old - 2 * $cropWidth, $height_old - 2 * $cropHeight);
	
	
    # Taking care of original, if needed
    if ( $delete_original ) {
      if ( $use_linux_commands ) exec('rm '.$file);
      else @unlink($file);
    }

    # Preparing a method of providing result
    switch ( strtolower($output) ) {
      case 'browser':
        $mime = image_type_to_mime_type($info[2]);
        header("Content-type: $mime");
        $output = NULL;
      break;
      case 'file':
        $output = $file;
      break;
      case 'return':
        return $image_resized;
      break;
      default:
      break;
    }
    
    # Writing image according to type to the output destination and image quality
    switch ( $info[2] ) {
      case IMAGETYPE_GIF:   imagegif($image_resized, $output);    break;
      case IMAGETYPE_JPEG:  imagejpeg($image_resized, $output, $quality);   break;
      case IMAGETYPE_PNG:
        $quality = 9 - (int)((0.9*$quality)/10.0);
        imagepng($image_resized, $output, $quality);
        break;
      default: return false;
    }

    return true;
  }
}

// resize image to max width 600 and height 750
smart_resize_image('logos/'.$clientlogo,null,600,750, true,'file',true,false,85);

////// END IMAGE RESIZE & CORRECT EXTENSION //////

header("Location: upload-logos.php");

		
	}
	 // logged in?
?>