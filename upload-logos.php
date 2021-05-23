<?
/// check if logged in?

session_start();
//session_destroy(); // logout

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
	if(!($_SESSION['user'] == "kt"
	&& $_SESSION['pass'] == hash('sha256', '!Tregoe2017')))
	{ // if not logged in
		# Show login form. Request for username and password
		{?>
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
				</div>
			</fieldset>
			</form>
		<script src="js/jquery-2.1.1.js"></script>
		<script src="js/main.js"></script> <!-- Resource jQuery -->
		</body>
		</html>	
		<?}
	exit; }
 ?>

<?php

////// CLEAR OLD CACHE //////

$folderName = "logos/";
if (file_exists($folderName)) {
    foreach (new DirectoryIterator($folderName) as $fileInfo) {
        if ($fileInfo->isDot()) {
        continue;
        }
        // gooi oude files (>15min) weg die geen _XYZ_ in naam hebben
        if ((time() - $fileInfo->getCTime()&&(strpos($fileInfo, '_XYZ_') == false)) >= 15*60) {
            
            unlink($fileInfo->getRealPath());
        }
    }
}
////// END CLEAR OLD CACHE //////

///// get input from url /////
$id = htmlspecialchars_decode(rawurldecode($_GET['id']));

///// DELETE FILE? /////

if (!empty($id)&&(file_exists("logos/".$id))) { 
	//echo "delete: ".$id;exit;
	copy("logos/".$id, "logos-backup/".$id); // make backup
	unlink("logos/".$id);
	} else { 
	unset($id);
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
	<h1><b>LOGO UPLOADER</b></h1></div>
	<div class="DZ-form">
	<h2><b>ADD CLIENT LOGO</b></h2>
	<h4>Use JPG, GIF or PNG in high resolution.</h4>
	<div id="addLogo"><form action="uploading-logo-file.php" class="dropzone" id="myDropzone" >
	</form>	</div></div>

	<form class="cd-form floating-labels" action='uploading-logo-title.php' id=certform>
		<fieldset>
			<legend>Client Name</legend>

			<div class="icon">
				<label class="cd-label" for="cd-textarea">Client Name</label>
				<input class="message" type="text" name="name" id="name" required>
		    </div>
		    					
 			<!--<h4>Client Logo</h4>-->
			<input type="hidden" name="logo" id="logo" value="">
			
			
    		</fieldset>
		</div>

		<div><input type="submit" value="Submit"></div>
		
		
	</form>
		

	<div class="DZ-form">
	<h1><b>LOGO LIBRARY</b></h1></div>
	<div class='cd-form'>
		
		<?php 
		foreach(glob(dirname(__FILE__) . '/logos/*') as $filename){
		$filename = basename($filename);
		$filename2 = explode("_XYZ_",$filename);
		
		echo "<div id='sub-left'><img id='ClientLogo' src='logos/".$filename."' style='max-height: 30px; width: auto;' /> ";
		echo "</div>";
		echo "<div id='sub-right'> <a href='?id=".$filename."' onclick='return confirm(\"Are you sure you want to delete the ".$filename2[0]."-logo?\");'>Delete</a></div>";
		echo "<div id='sub-right'>".$filename2[0]."</div>";
		echo "<div id='clear-both'></div>";
		echo "";
		 }
		?>

	</div>	

<script src="js/jquery-2.1.1.js"></script>
<script src="js/main.js"></script> <!-- Resource jQuery -->

<script>

Dropzone.options.myDropzone = {
	addRemoveLinks: true,
    acceptedFiles: "image/jpeg,image/png,image/gif", //old: "image/*",
    maxFiles: 1,
    //resizeWidth: 750,
	
init: function() {      
        this.on('addedfile', function(file){
        document.getElementById('logo').value = file.name;	
     	});   		 	 
   	 }
  
};
    </script>
<p></p>
</body>
</html>