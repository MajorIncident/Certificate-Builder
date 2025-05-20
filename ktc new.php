<?php
// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if logged in?
session_start();

# Check for session timeout, else initialize time
if (isset($_SESSION['timeout'])) {
    # Check Session Time for expiry
    #
    # Time is in seconds. 10 * 60 = 600s = 10 minutes
    if ($_SESSION['timeout'] + 30 * 60 < time()) {
        session_destroy();
    }
} else {
    # Initialize variables
    $_SESSION['user'] = "";
    $_SESSION['pass'] = "";
    $_SESSION['timeout'] = time();
}

# Store POST data in session variables
if (isset($_POST["user"])) {
    $_SESSION['user'] = $_POST['user'];
    $_SESSION['pass'] = hash('sha256', $_POST['pass']);
}

# Check Login Data
#
if ($_SESSION['user'] == "kt" && $_SESSION['pass'] == hash('sha256', '!Tregoe2021')) {

    require_once('fpdf/fpdf.php');
    require_once('fpdi/fpdi.php');
    require_once('fpdi/FPDI_Protection.php');

    // get input from url
    $name = htmlspecialchars_decode(rawurldecode($_GET['name']));
    $names = htmlspecialchars_decode(rawurldecode($_GET['names']));
    $session = htmlspecialchars_decode(rawurldecode($_GET['session']));
    $sessiondd = htmlspecialchars_decode(rawurldecode($_GET['sessiondd']));
    $location = htmlspecialchars_decode(rawurldecode($_GET['location']));
    $date = htmlspecialchars_decode(rawurldecode($_GET['date']));
    $bottomleft = htmlspecialchars_decode(rawurldecode($_GET['bottomleft']));
    $language = htmlspecialchars_decode(rawurldecode($_GET['language']));
    $download = htmlspecialchars_decode(rawurldecode($_GET['download']));
    $certtype = htmlspecialchars_decode(rawurldecode($_GET['certtype']));
    $newlogo = htmlspecialchars_decode(rawurldecode($_GET['logo']));
    $tolibrary = htmlspecialchars_decode(rawurldecode($_GET['library']));
    $clientname = htmlspecialchars_decode(rawurldecode($_GET['client']));
    $clientlogo2 = htmlspecialchars_decode(rawurldecode($_GET['existing-logo']));

    ///// CHECK IMAGE UPLOAD SUCCESSFUL /////
    if (!empty($newlogo) && file_exists("uploads/" . $newlogo)) {
    } else {
        unset($newlogo);
    }
    ///// END CHECK IMAGE UPLOAD SUCCESSFUL /////


    ////// IMAGE RESIZE & CORRECT EXTENSION //////
    if (!empty($newlogo)) {
        //Image Processing
        $cover = $newlogo;
        $cover_tmp_name = $_FILES['cover']['tmp_name'];
        $cover_img_path = 'uploads/';
        $type = exif_imagetype('uploads/' . $cover);

        if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_BMP])) {
            $cover_pre_name = md5($cover);  //Just to make a image name random and cool :D
            /**
             * @description : possible exif_imagetype() return values in $type
             * 1 - gif image
             * 2 - jpg image
             * 3 - png image
             * 6 - bmp image
             */
            switch ($type) {    #There are more type you can choose. Take a look in php manual -> http://www.php.net/manual/en/function.exif-imagetype.php
                case IMAGETYPE_GIF:
                    $cover_format = 'gif';
                    break;
                case IMAGETYPE_JPEG:
                    $cover_format = 'jpg';
                    break;
                case IMAGETYPE_PNG:
                    $cover_format = 'png';
                    break;
                case IMAGETYPE_BMP:
                    $cover_format = 'bmp';
                    break;
                default:
                    die('There is an error processing the image -> please try again with a new image');
                    break;
            }
            $cover_name = $cover_pre_name . '.' . $cover_format;
            //Checks whether the uploaded file exist or not
            // if (file_exists($cover_img_path . $cover_name)) {
            //     $extra = 1;
            //     while (file_exists($cover_img_path . $cover_name)) {
            //         $cover_name = md5($cover) . $extra . '.' . $cover_format;
            //         $extra++;
            //     }
            // }
            rename('uploads/' . $cover, 'uploads/' . $cover_name);
            $newlogo = $cover_name;

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
                                        $string = null,
                                        $width = 0,
                                        $height = 0,
                                        $proportional = false,
                                        $output = 'file',
                                        $delete_original = true,
                                        $use_linux_commands = false,
                                        $quality = 100
            )
            {

                if ($height <= 0 && $width <= 0) return false;
                if ($file === null && $string === null) return false;

                # Setting defaults and meta
                $info = $file !== null ? getimagesize($file) : getimagesizefromstring($string);
                $image = '';
                $final_width = 0;
                $final_height = 0;
                list($width_old, $height_old) = $info;
                $cropHeight = $cropWidth = 0;

                # Calculating proportionality
                if ($proportional) {
                    if ($width == 0) $factor = $height / $height_old;
                    elseif ($height == 0) $factor = $width / $width_old;
                    else $factor = min($width / $width_old, $height / $height_old);

                    $final_width = round($width_old * $factor);
                    $final_height = round($height_old * $factor);
                } else {
                    $final_width = ($width <= 0) ? $width_old : $width;
                    $final_height = ($height <= 0) ? $height_old : $height;
                    $widthX = $width_old / $width;
                    $heightX = $height_old / $height;

                    $x = min($widthX, $heightX);
                    $cropWidth = ($width_old - $width * $x) / 2;
                    $cropHeight = ($height_old - $height * $x) / 2;
                }

                # Loading image to memory according to type
                switch ($info[2]) {
                    case IMAGETYPE_JPEG:
                        $file !== null ? $image = imagecreatefromjpeg($file) : $image = imagecreatefromstring($string);
                        break;
                    case IMAGETYPE_GIF:
                        $file !== null ? $image = imagecreatefromgif($file) : $image = imagecreatefromstring($string);
                        break;
                    case IMAGETYPE_PNG:
                        $file !== null ? $image = imagecreatefrompng($file) : $image = imagecreatefromstring($string);
                        break;
                    default:
                        return false;
                }


                # This is the resizing/resampling/transparency-preserving magic
                $image_resized = imagecreatetruecolor($final_width, $final_height);
                if (($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG)) {
                    $transparency = imagecolortransparent($image);
                    $palletsize = imagecolorstotal($image);

                    if ($transparency >= 0 && $transparency < $palletsize) {
                        $transparent_color = imagecolorsforindex($image, $transparency);
                        $transparency = imagecolorallocate($image_resized, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                        imagefill($image_resized, 0, 0, $transparency);
                        imagecolortransparent($image_resized, $transparency);
                    } elseif ($info[2] == IMAGETYPE_PNG) {
                        imagealphablending($image_resized, false);
                        $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
                        imagefill($image_resized, 0, 0, $color);
                        imagesavealpha($image_resized, true);
                    }
                }
                imagecopyresampled($image_resized, $image, 0, 0, $cropWidth, $cropHeight, $final_width, $final_height, $width_old - 2 * $cropWidth, $height_old - 2 * $cropHeight);


                # Taking care of original, if needed
                if ($delete_original) {
                    if ($use_linux_commands) exec('rm ' . $file);
                    else @unlink($file);
                }

                # Preparing a method of providing result
                switch (strtolower($output)) {
                    case 'browser':
                        $mime = image_type_to_mime_type($info[2]);
                        header("Content-type: $mime");
                        $output = null;
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
                switch ($info[2]) {
                    case IMAGETYPE_GIF:
                        imagegif($image_resized, $output);
                        break;
                    case IMAGETYPE_JPEG:
                        imagejpeg($image_resized, $output, $quality);
                        break;
                    case IMAGETYPE_PNG:
                        $quality = 9 - (int)((0.9 * $quality) / 10.0);
                        imagepng($image_resized, $output, $quality);
                        break;
                    default:
                        return false;
                }

                return true;
            }

            // resize image to max width 600 and height 750
            smart_resize_image('uploads/' . $cover_name, null, 600, 750, true, 'file', true, false, 85);
        }
    }
    ////// END IMAGE RESIZE & CORRECT EXTENSION //////

    //// NEW LOGO TO LIBRARY ////////

    if (!empty($newlogo)) {

        $clientname = str_replace(' ', '_', $clientname);
        $clientname = $clientname . "_XYZ_" . $cover_name;
        copy('uploads/' . $cover_name, 'logos/' . $clientname);
    }
    //// END NEW LOGO TO LIBRARY ////////

    function formatURL($unformatted)
    {

        $url = strtolower(trim($unformatted));

        //DISABLED BY SHANE 6/4/2021 - To try and get chinese characters working
        //replace accent characters, foreign languages
        //$search = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
        //$replace = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
        //$url = str_replace($search, $replace, $url);

        //replace common characters
        $search = array('&', '£', '$');
        $replace = array('and', 'pounds', 'dollars');
        $url = str_replace($search, $replace, $url);

        // remove - for spaces and union characters
        $find = array(' ', '&', '\r\n', '\n', '+', ',', '//');
        $url = str_replace($find, '-', $url);

        //delete and replace rest of special chars
        $find = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/');
        $replace = array('', '-', '');
        $uri = preg_replace($find, $replace, $url);

        return $uri;
    }


    function pdfEncrypt($origFile, $user_password, $owner_password, $destFile)
    {

        /**
         * Function to set permissions as well as user and owner passwords
         *
         * - permissions is an array with values taken from the following list:
         *   copy, print, modify, annot-forms
         *   If a value is present it means that the permission is granted
         * - If a user password is set, user will be prompted before document is opened
         * - If an owner password is set, document can be opened in privilege mode with no
         *   restriction if that password is entered
         */

        $pdf = new FPDI_Protection('L', 'mm', 'A4');
        $pagecount = $pdf->setSourceFile($origFile);
        for ($pageNo = 1; $pageNo <= $pagecount; $pageNo++) {
            $tplidx = $pdf->importPage($pageNo);
            $pdf->addPage();
            $pdf->useTemplate($tplidx, 0, 0, 297, 210);
            $pdf->SetFont('Helvetica');
            $pdf->SetTextColor(0, 155, 255);
            $pdf->SetXY(5, 5);
            $min = 2;
            $max = 10;

            // add LOGO
            global $clientlogo2; // from Library

            if (!empty($clientlogo2)) {
                $image1 = "logos/" . $clientlogo2;
                list($width, $height, $type, $attr) = getimagesize("logos/" . $clientlogo2);
                $heightmm = $height * 25.4 / 300;
                $pdf->Image($image1, 30, $pdf->GetY() + 175 - $heightmm, -300);
            } else {
                global $newlogo; // uploaded new
                if (!empty($newlogo)) {
                    $image1 = "uploads/" . $newlogo;
                    list($width, $height, $type, $attr) = getimagesize("uploads/" . $newlogo);
                    $heightmm = $height * 25.4 / 300;

                    $pdf->Image($image1, 30, $pdf->GetY() + 175 - $heightmm, -300);
                }
            }
        }

        $pdf->SetProtection(array('print'), $user_password, $owner_password);
        $pdf->Output($destFile, 'F');

        return $destFile;
    }

    ////// CLEAR OLD CACHE //////
    $folderName = "cache/";

    if (file_exists($folderName)) {
        foreach (new DirectoryIterator($folderName) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            if (time() - $fileInfo->getCTime() >= 15 * 60) {
                unlink($fileInfo->getRealPath());
            }
        }
    }

    $folderName = "uploads/";
    if (file_exists($folderName)) {
        foreach (new DirectoryIterator($folderName) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            if (time() - $fileInfo->getCTime() >= 15 * 60) {
                unlink($fileInfo->getRealPath());
            }
        }
    }

    $folderName = "logos/";
    if (file_exists($folderName)) {
        foreach (new DirectoryIterator($folderName) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            // gooi oude files (>15min) weg die geen _XYZ_ in naam hebben
            if ((time() - $fileInfo->getCTime() && (strpos($fileInfo, '_XYZ_') == false)) >= 15 * 60) {
                unlink($fileInfo->getRealPath());
            }
        }
    }
    ////// END CLEAR OLD CACHE //////


    /////  COMMON TO BOTH SINGLE OR MULTIPLE PDFS

    // CHECK INPUT DOWNLOAD / LANGUAGE / CERTIFICATE TYPE
    if (empty($download) || !ctype_alpha($download)) {
        echo "No/incorrect inline / download option given";
        exit;
    }
    if (empty($language) || !ctype_alpha($language)) {
        echo "No language option given";
        exit;
    }
    if (empty($certtype) || !ctype_alpha($certtype)) {
        echo "No certificate type given";
        exit;
    }

    // CHECK INPUT $SESSION
    // Check if empty
    $session = $sessiondd . " " . $session;
    if (empty($session)) {
        echo "No session";
        exit;
    }
    // Check if only letters
    if (!preg_match("/^(?:[\s,.'-]*[a-zA-Z\pL][\s,.'-]*)+$/u", str_replace(array(' ', "\'", ".", ",", '-', '&', '(', ')'), '', preg_replace('/[0-9]+/', '', $session)))) {
        echo "session = " . $session . "<br>Session format incorrect";
        exit;
    }
    // Check length
    $wlenght = strlen($session);
    if ($wlenght > 80) {
        echo "session must be 2-80 characters";
        exit;
    }
    if ($wlenght < 2) {
        echo "session must be 2-80 characters";
        exit;
    }

    // CHECK INPUT $LOCATION
    // Check if empty
    if (empty($location)) {
        //Shane Edit: Do nothing and do not throw error as not required - old code: // echo "No location"; exit;
    } else {
        // Check if only letters
        if (!preg_match("/^(?:[\s,.'-]*[a-zA-Z\pL][\s,.'-]*)+$/u", str_replace(array(' ', "\'", "/", ".", ",", '-', '&', '(', ')'), '', preg_replace('/[0-9]+/', '', $location)))) {
            echo "location = " . $location . "<br>Location format incorrect";
            exit;
        }
        // Check length
        $wlenght = strlen($location);
        if ($wlenght > 80) {
            echo "location must be 2-80 characters";
            exit;
        }
        if ($wlenght < 2) {
            echo "location must be 2-80 characters";
            exit;
        }
    }

    // CHECK INPUT $DATE
    // Check if empty
    if (empty($date)) {
        echo "No date";
        exit;
    }
    // Check if only letters
    $date_clean = str_replace(array(' ', "\'", "/", ".", ",", '-', '(', ')'), '', $date);
    if (!preg_match("/^(?:[\s,.'-]*[a-zA-Z\pL][\s,.'-]*)+$/u", str_replace(array(' ', "\'", "/", ".", ",", '-', '&', '(', ')'), '', $date))) {
        if (!ctype_alnum($date_clean)) {
            echo "date = " . $date . "<br>Date format incorrect";
            exit;
        }
    }
    // Check length
    $wlenght = strlen($date);
    if ($wlenght > 80) {
        echo "date must be 2-80 characters";
        exit;
    }
    if ($wlenght < 2) {
        echo "date must be 2-80 characters";
        exit;
    }

    // make special characters work
    $name = str_replace("\\", "", $name);
    //Disabled by Shane to try and get chinese characters to work $name = iconv('UTF-8', 'windows-1252', $name);
    $session = iconv('UTF-8', 'windows-1252', $session);
    $location = iconv('UTF-8', 'windows-1252', $location);
    $date = iconv('UTF-8', 'windows-1252', $date);


    // SINGLE OR MULTIPLE PDFs?
    if (empty($names)) {


        ////// SINGLE //////

        // CHECK INPUT $NAME
        // Check if empty
        if (empty($name)) {
            echo "No name";
            exit;
        }
        // Check if only letters
        if (!preg_match("/^(?:[\s,.'-]*[a-zA-Z\pL][\s,.'-]*)+$/u", str_replace(array(' ', "\'", ".", ",", '-', '(', ')'), '', $name))) {
            echo "name = " . $name . "<br>Name only letters";
            exit;
        }
        // Check length
        $wlenght = strlen($name);
        if ($wlenght > 80) {
            echo "name must be 2-80 characters";
            exit;
        }
        if ($wlenght < 2) {
            echo "name must be 2-80 characters";
            exit;
        }


        // define output filename
        $outputfilename = formatURL($name) . '_KT_' . formatURL($session) . '.pdf';

        //// CREATE A SINGLE PDF

        // You can create an object of the FPDI class.
        // The FDPI class, by default detects end extends the TCPDF or FPDF class (whichever is available),
        // so you need not create a new TCPDF or FPDF object.
        // L = landscape, P = Portrait
        //test commented out shane $pdf = new FPDI('L','mm','A4');
        $pdf = new FPDI();
        // Specify the source PDF document by calling setSourceFile function.
        $pdf->setSourceFile($certtype . "_" . $language . ".pdf");

        // Specify which page of the document is to be imported.
        // I’m importing 1st page and setting the second parameter – boxtype to ‘/Mediabox’.
        // http://www.prepressure.com/pdf/basics/page-boxes
        $tplIdx = $pdf->importPage(1, '/MediaBox');

        //new lines from shane for size setting from https://www.reddit.com/r/PHPhelp/comments/jf36m/question_how_can_i_get_the_height_and_width_of_an/
        $size = $pdf->getTemplateSize($tplIdx);
        $w = $size['w'];
        $h = $size['h'];

        // Detect Portrait or Landscape?
        if ($h > $w) { //PDF is Portrait (Service Certificates)

            //Display Message and stop as Service Certificate Code Not Available Yet
            echo "Service Certificates are still being worked on at this time at this time - shane 06/03/2021";
            exit;

            //Re-Initialize in Portrait

            $pdf->addPage('P');
            $pdf->useTemplate($tplIdx, 0, 0, $w, $h);

            // Now the document and the page to be used as template is successfully loaded.
            // Text or image can be added anywhere on the loaded page by specifying XY co-ordinates of the position
            $pdf->SetFont('Times', '', 12);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(148, 145);

            // Set Mid Point of PDF Screen
            $mid_x = $w / 2;

            // ADD NAME
            $pdf->SetFont('Times', 'B', 36);
            $text = $name;
            $pdf->Text($mid_x - ($pdf->GetStringWidth($text) / 2), 64, $text);

            // ADD DATE
            $pdf->SetFont('Times', '', 12);
            $text = $date;
            $pdf->Text($mid_x - ($pdf->GetStringWidth($text) / 2), 154, $text);


        } else { // PDF Certificate is Landscape (Standard Certificates)

            $pdf->addPage();

            // old code from gijs $pdf->useTemplate($tplIdx, 0, 0, 0, 0, true);
            // Shane Edit: This method does not stretch during import and allows multiple formats to be entered and created correctly
            //$pdf ->useTemplate($tplIdx, null, null, $size['w'], 0, FALSE);
            $pdf->useTemplate($tplIdx, null, null, null, null, true);

            // Now the document and the page to be used as template is successfully loaded.
            // Text or image can be added anywhere on the loaded page by specifying XY co-ordinates of the position
            $pdf->SetFont('Times', '', 12);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(148, 145);

            // To write text, call the write() function. The first parameter takes line height value.
            // $pdf->Write(0, 'Date', 'C');

            // Shane Edit: Set Mid Point of PDF Screen
            $mid_x = $w / 2;

            // Shane Edit: Set Bottom Left Text Locations
            if ($language == "EN") {
                //$mid_x = 141.7; // Middle of PDF for 8.5x11 documents - not required as using math now
                $mid_xBL = 50; // bottom left text for 8.5x11 document
                $h_xBL = 195; // bottom left height for 8.5x11 document
            } else {
                //$mid_x = 148.3; // Middle of PDF for A4 documents - not required as using math now
                $mid_xBL = 57; // bottom left text for A4 documents
                $h_xBL = 185; // bottom left height for 8.5x11 document
            }

            // ADD NAME
            $pdf->SetFont('Times', 'B', 36);
            $text = $name;
            $pdf->Text($mid_x - ($pdf->GetStringWidth($text) / 2), 64, $text);

            // ADD SESSION
            $pdf->SetFont('Times', '', 24);
            $text = $session;
            $pdf->Text($mid_x - ($pdf->GetStringWidth($text) / 2), 135, $text);

            // ADD LOCATION
            $pdf->SetFont('Times', '', 12);
            $text = $location;
            $pdf->Text($mid_x - ($pdf->GetStringWidth($text) / 2), 147, $text);

            // ADD DATE
            $pdf->SetFont('Times', '', 12);
            $text = $date;
            $pdf->Text($mid_x - ($pdf->GetStringWidth($text) / 2), 154, $text);

            // Shane Edit: ADD Bottom Left Text
            $pdf->SetFont('Times', '', 9);
            $text = $bottomleft;
            $pdf->Text($mid_xBL - ($pdf->GetStringWidth($text) / 2), $h_xBL, $text);

        }


        // Call the Output() function to output the PDF document on the clients browser. I=in browser D=download
        //$pdf->Output($outputfilename,$download);
        $pdf->Output('cache/' . $outputfilename, "F");

        $user_pass = '';
        $owner_pass = $outputfilename;
        $origFile = __DIR__ . '/cache/' . $outputfilename;
        $destFile = __DIR__ . '/cache/' . $outputfilename;

        pdfEncrypt($origFile, $user_pass, $owner_pass, $destFile);

        ///END CREATE A SINGLE PDF


        //// OUTPUT PDF TO DISPLAY/DOWNLOAD

        $filepath = $_SERVER['SCRIPT_FILENAME'];
        $filepath = rtrim($filepath, "ktc.php") . "cache/";

        if ($download == "D") {

            // http headers for pdf downloads
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-type: application/octet-stream");
            header("Content-Disposition: attachment; filename=\"" . $outputfilename . "\"");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: " . filesize($filepath . $outputfilename));
            ob_end_flush();
            @readfile($filepath . $outputfilename);

        } else if ($download == "I") {

            $file = $filepath . $outputfilename;
            $filename = $outputfilename;
            header('Content-type: application/pdf');
            header('Content-Disposition: inline; filename="' . $filename . '"');
            header('Content-Transfer-Encoding: binary');
            header('Accept-Ranges: bytes');
            @readfile($file);

        }
    } ////// END SINGLE //////
    else {


        ////// MULTIPLE //////


        // SET $DOWNLOAD
        $download = "F";

        // CHECK INPUT $NAMES
        // Check if empty
        if (empty($names)) {
            echo "No name";
            exit;
        }
        // Check if only letters
        if (!preg_match("/^(?:[\s,.'-]*[a-zA-Z\pL][\s,.'-]*)+$/u", str_replace(array(' ', "\'", ".", ",", '-', '(', ')'), '', $names))) {
            echo "names = " . $names . "<br>Names only letters";
            exit;
        }
        // Check length
        $wlenght = strlen($names);
        if ($wlenght > 8000) {
            echo "names must be 2-8000 characters";
            exit;
        }
        if ($wlenght < 2) {
            echo "name must be 2-80 characters";
            exit;
        }


        // define output filename
        $outputfilenames = $names;
        $outputfilenames = preg_replace('/\s*,\s*/', ',', $outputfilenames); // remove spaces around commas
        $outputfilenames = preg_replace('/,+/', ',', $outputfilenames); // remove multiple commas
        $outputfilenamesArray = explode(',', $outputfilenames);

        foreach ($outputfilenamesArray as &$value) {

            // BUILD ARRAY OF FILENAMES FOR FUTURE ZIP-file
            $pdf_filename = formatURL($value) . '_KT_' . formatURL($session) . '.pdf';
            $files[] = $pdf_filename;

        }
        unset($value);

        // make special characters work
        $names = str_replace("\\", "", $names);
        $names = iconv('UTF-8', 'windows-1252', $names);

        //// LOOP TO GENERATE PDFs ////

        $names = preg_replace('/\s*,\s*/', ',', $names); // remove spaces around commas
        $names = preg_replace('/,+/', ',', $names); // remove multiple commas
        $nameArray = explode(',', $names);

        foreach ($nameArray as $key => $value) {

            $name = $value;

            //// CREATE A SINGLE PDF

            //if (strpos($certtype, "service") !== false) {
            //  echo "service found!";
            //  exit;
            //} else {
            //  echo "no service found";
            //  exit;
            //}


            // You can create an object of the FPDI class.
            // The FDPI class, by default detects end extends the TCPDF or FPDF class (whichever is available),
            // so you need not create a new TCPDF or FPDF object.
            // L = landscape, P = Portrait
            //test commented out shane $pdf = new FPDI('L','mm','A4');
            $pdf = new FPDI();
            // Specify the source PDF document by calling setSourceFile function.
            $pdf->setSourceFile($certtype . "_" . $language . ".pdf");

            // Specify which page of the document is to be imported.
            // I’m importing 1st page and setting the second parameter – boxtype to ‘/Mediabox’.
            // http://www.prepressure.com/pdf/basics/page-boxes
            $tplIdx = $pdf->importPage(1, '/MediaBox');

            //new line from shane for size setting
            $size = $pdf->getTemplateSize($tplIdx);
            $w = $size['w'];
            $h = $size['h'];

            $pdf->addPage();

            // old code from gijs $pdf->useTemplate($tplIdx, 0, 0, 0, 0, true);
            // new shane update - 310 is a magic number to result in non cropped pdf as per https://stackoverflow.com/questions/6674753/problem-with-size-of-the-imported-pdf-template-with-fpditcpdf
            //$pdf ->useTemplate($tplIdx, null, null, $size['w'], 0, FALSE);
            $pdf->useTemplate($tplIdx, null, null, null, null, true);

            // Now the document and the page to be used as template is successfully loaded.
            // Text or image can be added anywhere on the loaded page by specifying XY co-ordinates of the position
            $pdf->SetFont('Times', '', 12);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(148, 145);

            // To write text, call the write() function. The first parameter takes line height value.
            // $pdf->Write(0, 'Date', 'C');

            // Set Mid Point of PDF Screen
            $mid_x = $w / 2;

            // Set Bottom Left Text Locations
            if ($language == "EN") {
                //$mid_x = 141.7; // Middle of PDF for 8.5x11 documents - not required as using math now
                $mid_xBL = 50; // bottom left text for 8.5x11 document
                $h_xBL = 195; // bottom left height for 8.5x11 document
            } else {
                //$mid_x = 148.3; // Middle of PDF for A4 documents - not required as using math now
                $mid_xBL = 57; // bottom left text for A4 documents
                $h_xBL = 185; // bottom left height for 8.5x11 document
            }

            // ADD NAME
            $pdf->SetFont('Times', 'B', 36);
            $text = $name;
            $pdf->Text($mid_x - ($pdf->GetStringWidth($text) / 2), 64, $text);

            // ADD SESSION
            $pdf->SetFont('Times', '', 24);
            $text = $session;
            $pdf->Text($mid_x - ($pdf->GetStringWidth($text) / 2), 135, $text);

            // ADD LOCATION
            $pdf->SetFont('Times', '', 12);
            $text = $location;
            $pdf->Text($mid_x - ($pdf->GetStringWidth($text) / 2), 147, $text);

            // ADD DATE
            $pdf->SetFont('Times', '', 12);
            $text = $date;
            $pdf->Text($mid_x - ($pdf->GetStringWidth($text) / 2), 154, $text);

            // ADD Bottom Left
            $pdf->SetFont('Times', '', 9);
            $text = $bottomleft;
            $pdf->Text($mid_xBL - ($pdf->GetStringWidth($text) / 2), $h_xBL, $text);


            // Call the Output() function to output the PDF document on the clients browser. I=in browser D=download
            $pdf->Output('cache/' . $files[$key], $download);

            $user_pass = '';
            $owner_pass = $files[$key];
            $origFile = __DIR__ . '/cache/' . $files[$key];
            $destFile = __DIR__ . '/cache/' . $files[$key];

            pdfEncrypt($origFile, $user_pass, $owner_pass, $destFile);


        } //// END LOOP GENERATE PDFs ////

        unset($value);

        //// GENERATE ZIP FILE

        # create new zip object
        $zip = new ZipArchive();

        # create a temp file & open it
        $tmp_file = tempnam('cache/', 'zipfile');
        $zip->open($tmp_file, ZipArchive::CREATE);

        # loop through each file
        foreach ($files as $file) {

            # download file
            $download_file = file_get_contents('cache/' . $file);

            #add it to the zip
            $zip->addFromString(basename($file), $download_file);

        }

        # close zip
        $zip->close();
        $zip_download_name = "cache/KT_certificates_" . formatURL($session) . "_" . formatURL($date) . "_" . formatURL($location) . ".zip";
        rename($tmp_file, $zip_download_name);
        $zip_download_name = "KT_certificates_" . formatURL($session) . "_" . formatURL($date) . "_" . formatURL($location) . ".zip";

        //// OUTPUT ZIP TO DOWNLOAD

        $filepath = $_SERVER['SCRIPT_FILENAME'];
        $filepath = rtrim($filepath, "ktc.php") . "cache/";

        // http headers for zip downloads
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"" . $zip_download_name . "\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($filepath . $zip_download_name));
        ob_end_flush();
        @readfile($filepath . $zip_download_name);

        //// END GENERATE ZIP FILE

    } ////// END MULTIPLE //////

} else // if not logged in
{
    # Show login form. Request for username and password
    ?>
    <!doctype html>
    <html lang="en" class="no-js">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,700' rel='stylesheet' type='text/css'>

        <link rel="stylesheet" href="css/reset.css"> <!-- CSS reset -->
        <link rel="stylesheet" href="css/style.css"> <!-- Resource style -->
        <script src="js/modernizr.js"></script> <!-- Modernizr -->

        <title>Please login</title>
    </head>
    <body>
    <form class="cd-form floating-labels" method="POST" action="">
        <fieldset>
            <legend>Please login</legend>

            <div class="icon">
                <label class="cd-label" for="cd-textarea">User</label>
                <input class="user" type="text" name="user" id="user" required>
            </div>
            <div class="icon">
                <label class="cd-label" for="cd-textarea">Password</label>
                <input class="password" type="password" name="pass" id="pass" required>
            </div>
            <input type="submit" name="submit" value="Login">
    </form>
    <script src="js/jquery-2.1.1.js"></script>
    <script src="js/main.js"></script> <!-- Resource jQuery -->
    </body>
    </html>
<?php
}
?>
