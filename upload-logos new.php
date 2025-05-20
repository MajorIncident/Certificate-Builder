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
?>

<?php
////// CLEAR OLD CACHE //////

$folderName = "logos/";
if (file_exists($folderName)) {
    foreach (new DirectoryIterator($folderName) as $fileInfo) {
        if ($fileInfo->isDot()) {
            continue;
        }
        // Remove old files (>15min) that don't have _XYZ_ in their name
        if ((time() - $fileInfo->getCTime() >= 15 * 60) && strpos($fileInfo->getFilename(), '_XYZ_') === false) {
            unlink($fileInfo->getRealPath());
        }
    }
}
////// END CLEAR OLD CACHE //////

///// Get input from URL /////
$id = htmlspecialchars_decode(rawurldecode($_GET['id'] ?? ''));

///// DELETE FILE? /////
if (!empty($id) && file_exists("logos/" . $id)) {
    // Make backup and delete the file
    copy("logos/" . $id, "logos-backup/" . $id);
    unlink("logos/" . $id);
}
///// END DELETE /////
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="css/reset.css"> <!-- CSS reset -->
    <link rel="stylesheet" href="css/style.css"> <!-- Resource style -->
    <link rel="stylesheet" href="css/dropzone.css"> <!-- Dropzone -->
    <script src="js/modernizr.js"></script> <!-- Modernizr -->
    <script src="js/dropzone.js"></script> <!-- Dropzone -->
    <title>Logo Uploader</title>
</head>
<body>

<div class="DZ-form">
    <h1><b>LOGO UPLOADER</b></h1>
    <div class="DZ-form">
        <h2><b>ADD CLIENT LOGO</b></h2>
        <h4>Use JPG, GIF or PNG in high resolution.</h4>
        <div id="addLogo">
            <form action="uploading-logo-file.php" class="dropzone" id="myDropzone"></form>
        </div>
    </div>
</div>

<form class="cd-form floating-labels" action='uploading-logo-title.php' method="POST" id="certform">
    <fieldset>
        <legend>Client Name</legend>
        <div class="icon">
            <label class="cd-label" for="name">Client Name</label>
            <input class="message" type="text" name="name" id="name" required>
        </div>
        <input type="hidden" name="logo" id="logo" value="">
    </fieldset>
    <div><input type="submit" value="Submit"></div>
</form>

<div class="DZ-form">
    <h1><b>LOGO LIBRARY</b></h1>
    <div class='cd-form'>
        <?php
        foreach (glob(dirname(__FILE__) . '/logos/*') as $filename) {
            $filename = basename($filename);
            $filename2 = explode("_XYZ_", $filename);
            echo "<div id='sub-left'><img id='ClientLogo' src='logos/" . htmlspecialchars($filename, ENT_QUOTES, 'UTF-8') . "' style='max-height: 30px; width: auto;' />";
            echo "</div>";
            echo "<div id='sub-right'> <a href='?id=" . htmlspecialchars($filename, ENT_QUOTES, 'UTF-8') . "' onclick='return confirm(\"Are you sure you want to delete the " . htmlspecialchars($filename2[0], ENT_QUOTES, 'UTF-8') . "-logo?\");'>Delete</a></div>";
            echo "<div id='sub-right'>" . htmlspecialchars($filename2[0], ENT_QUOTES, 'UTF-8') . "</div>";
            echo "<div id='clear-both'></div>";
        }
        ?>
    </div>
</div>

<script src="js/jquery-2.1.1.js"></script>
<script src="js/main.js"></script> <!-- Resource jQuery -->
<script>
Dropzone.options.myDropzone = {
    addRemoveLinks: true,
    acceptedFiles: "image/jpeg,image/png,image/gif",
    maxFiles: 1,
    init: function() {
        this.on('addedfile', function(file) {
            document.getElementById('logo').value = file.name;
        });
        this.on('reset', function() {
            document.getElementById('logo').value = "";
        });
    }
};
</script>
</body>
</html>
