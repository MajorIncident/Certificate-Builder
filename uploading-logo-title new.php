<?php
// Start session
session_start();

// Check for session timeout, else initialize time
if (isset($_SESSION['timeout'])) {
    // Check Session Time for expiry
    // Time is in seconds. 10 * 60 = 600s = 10 minutes
    if ($_SESSION['timeout'] + 30 * 60 < time()) {
        session_destroy();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
} else {
    // Initialize session variables
    $_SESSION['user'] = "";
    $_SESSION['pass'] = "";
    $_SESSION['timeout'] = time();
}

// Store POST data in session variables
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user']) && isset($_POST['pass'])) {
        $_SESSION['user'] = $_POST['user'];
        $_SESSION['pass'] = hash('sha256', $_POST['pass']);
        $_SESSION['timeout'] = time();
    }
}

// Check Login Data
if (!(isset($_SESSION['user']) && $_SESSION['user'] === "kt" &&
    isset($_SESSION['pass']) && $_SESSION['pass'] === hash('sha256', '!Tregoe2017'))) {
    // If not logged in, show login form
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
                <label class="cd-label" for="user">User</label>
                <input class="user" type="text" name="user" id="user" required>
            </div>
            <div class="icon">
                <label class="cd-label" for="pass">Password</label>
                <input class="password" type="password" name="pass" id="pass" required>
            </div>
            <input type="submit" name="submit" value="Login">
        </fieldset>
    </form>
    <script src="js/jquery-2.1.1.js"></script>
    <script src="js/main.js"></script> <!-- Resource jQuery -->
    </body>
    </html>
    <?php
    exit;
}

// Get input from URL
$name = htmlspecialchars_decode(rawurldecode($_GET['name'] ?? ''));
$name = str_replace(' ', '_', $name);
$clientlogo = htmlspecialchars_decode(rawurldecode($_GET['logo'] ?? ''));

////// IMAGE RESIZE & CORRECT EXTENSION //////

// Image Processing
$cover = $clientlogo;
$cover_img_path = 'logos/';
$type = exif_imagetype($cover_img_path . $cover);

if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_BMP])) {
    $cover_pre_name = md5($cover);  // Just to make the image name random and cool :D

    switch ($type) {
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
    }
    $cover_name = $cover_pre_name . '.' . $cover_format;

    // Rename the file
    rename($cover_img_path . $cover, $cover_img_path . $name . "_XYZ_" . $cover_name);
    $clientlogo = $name . "_XYZ_" . $cover_name;

    // Function to resize image
    function smart_resize_image($file, $string = null, $width = 0, $height = 0, $proportional = false, $output = 'file', $delete_original = true, $use_linux_commands = false, $quality = 100)
    {
        if ($height <= 0 && $width <= 0) return false;
        if ($file === null && $string === null) return false;

        $info = $file !== null ? getimagesize($file) : getimagesizefromstring($string);
        $image = '';
        $final_width = 0;
        $final_height = 0;
        list($width_old, $height_old) = $info;
        $cropHeight = $cropWidth = 0;

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

        $image_resized = imagecreatetruecolor($final_width, $final_height);
        if ($info[2] == IMAGETYPE_GIF || $info[2] == IMAGETYPE_PNG) {
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

        if ($delete_original) {
            if ($use_linux_commands) exec('rm ' . $file);
            else @unlink($file);
        }

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

    // Resize image to max width 600 and height 750
    smart_resize_image('logos/' . $clientlogo, null, 600, 750, true, 'file', true, false, 85);
}

////// END IMAGE RESIZE & CORRECT EXTENSION //////

header("Location: upload-logos.php");
exit;
?>
