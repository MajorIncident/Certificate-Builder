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
	&& $_SESSION['pass'] == hash('sha256', '!Tregoe2021')))
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

	<title>KT Certificate Builder</title>
</head>
<body>

	<div class="DZ-form">
	<h1><b>KT CERTIFICATE BUILDER</b></h1></div>
	<div class="DZ-form">
		<h2>Optional: Client Logo</h2>
		<h4><br>Upload logo (JPG, GIF or PNG in high resolution)</h4>
		<form action="upload.php" class="dropzone" id="myDropzone" >
		</form>	
		
	</div></div>

	<form class="cd-form floating-labels" action='ktc.php' id=certform>
			
				<fieldset>
				<div id="addLogo2" style="display: none;">
				<ul class="cd-form-list">
					<li>
						<input type="checkbox" name="library" value="yes" id="library" onclick="ToggleShow('addLogo3');ToggleReq('client')">
						<label for="library">Store in Library?</label>
					</li>
				</ul>
				</div>
				
				<div class="icon" id="addLogo3" style="display: none;">
						<label class="cd-label" for="cd-textarea">Client name</label>
						<input class="message" type="text" name="client" id="client" >

				</div>
				
		    	<div id="addLogo4" class="icon">
				<h4>Or use from Library:</h4>
				<select class="arrow" name="existing-logo" id="existing-logo">
				<option value=''></option>

				<?php 
				foreach(glob(dirname(__FILE__) . '/logos/*') as $filename){
				$filename = basename($filename);
				$filename2 = explode("_XYZ_",$filename);
				echo "<option value='" . $filename . "'>".$filename2[0]."</option>";
				 }
				?>

				</select> 
				</div>
				<div id="showLogo" style="display: none;"><img id="ClientLogo" src="" style="max-width: 150px; height: auto;" />
				</div>
				</fieldset>
			<fieldset>
			<legend>Certificate Details</legend>

			<div class="icon">
				<label class="cd-label" for="cd-textarea">Session/Course Name</label>
				<input class="message" type="text" name="session" id="session" required>
		    </div>
		    <div class="icon">
				<label class="cd-label" for="cd-textarea">Date(s)</label>
				<input class="message" type="text" name="date" id="date" required>
		    </div>
		    <div class="icon">
				<label class="cd-label" for="cd-textarea">Location</label>
				<input class="message" type="text" name="location" id="location" required>
		    </div>
			
			<h4>Certificate Language</h4>
		    
		    <ul class="cd-form-list">
					<li>
						<input type="radio" name="language" id="radio-1" value="en" checked>
						<label for="radio-1">English</label>
					</li>
						
					<li>
						<input type="radio" name="language" id="radio-2" value="de">
						<label for="radio-2">German</label>
					</li>

					<li>
						<input type="radio" name="language" id="radio-3" value="nl">
						<label for="radio-2">Dutch</label>
					</li>

					<li>
						<input type="radio" name="language" id="radio-4" value="fr">
						<label for="radio-2">French</label>
					</li>

				</ul>
				<h4>Certificate Type</h4>
		    
		    <ul class="cd-form-list">
					<li>
						<input type="radio" name="certtype" id="radio-1" value="ST" checked>
						<label for="radio-1">Standard</label>
					</li>
						
					<li>
						<input type="radio" name="certtype" id="radio-2" value="PL">
						<label for="radio-2">Program Leader</label>
					</li>

					<li>
						<input type="radio" name="certtype" id="radio-3" value="SA">
						<label for="radio-3">Service: 5 Years</label>
					</li>

					<li>
						<input type="radio" name="certtype" id="radio-4" value="SB">
						<label for="radio-4">Service: 10 Years</label>
					</li>

					<li>
						<input type="radio" name="certtype" id="radio-5" value="SC">
						<label for="radio-5">Service: 15 Years</label>
					</li>

					<li>
						<input type="radio" name="certtype" id="radio-6" value="SD">
						<label for="radio-6">Service: 20 Years</label>
					</li>

					<li>
						<input type="radio" name="certtype" id="radio-7" value="SE">
						<label for="radio-7">Service: 25 Years</label>
					</li>
					

				</ul>
				
				<h4>Destination</h4>
 			
 			<ul class="cd-form-list">
					<li>
						<input type="radio" name="download" id="radio-2" value="D" checked>
						<label for="radio-2">Download PDF</label>
					</li>
					
					<li>
						<input type="radio" name="download" id="radio-1" value="I">
						<label for="radio-1">Show PDF in browser</label>
					</li>
				</ul>
					
 			<!--<h4>Client Logo</h4>-->
			<input type="hidden" name="logo" id="logo" value="">					
		</fieldset>
		</div>

		<legend>Names</legend>
			<div><h2><b>Option 1: SINGLE CERTIFICATE</b></h2>
				<div class="icon">
				<label class="cd-label" for="cd-name">Full Name</label>
				<input class="user" type="text" name="name" id="name">
		    </div>
		    
			<div><h2><b>Option 2: .ZIP of MULTIPLE CERTIFICATES</b></h2>
			<h4>Use commas to separate names.</h4><div class="icon">
				<label class="cd-label" for="cd-textarea">Full Name1,Full Name2,Full Name3</label>
      			<textarea class="message" name="names" id="names"></textarea>
			</div>
		      	
		    </div>
		    <div><input type="submit" value="Create Certificate(s)"></div>
	</form>
		

<script src="js/jquery-2.1.1.js"></script>
<script src="js/main.js"></script> <!-- Resource jQuery -->
<script>
function ToggleShow(a) {
    var x = document.getElementById(a);
    if (x.style.display === 'none') {
        x.style.display = 'block';
    } else {
        x.style.display = 'none';
    }
}

function ToggleReq(b) {
    var y = document.getElementById(b);
    if (y.required === true) {
        y.removeAttribute("required");
        } else {
        y.required = true;
        }
}

var activities = document.getElementById("existing-logo");

activities.addEventListener("click", function() {
    var e = document.getElementById("existing-logo");
    if (e.value.length > 0){
	var imagesrc = "logos/" + e.value;
	document.getElementById('showLogo').style.display = 'block';
    document.getElementById('ClientLogo').src = imagesrc;
    } else { document.getElementById('showLogo').style.display = 'none';}
});

activities.addEventListener("change", function() {
    var e = document.getElementById("existing-logo");
    if (e.value.length > 0){
	var imagesrc = "logos/" + e.value;
	document.getElementById('showLogo').style.display = 'block';
    document.getElementById('ClientLogo').src = imagesrc;
    } else { document.getElementById('showLogo').style.display = 'none';}
});

</script>

<script>

Dropzone.options.myDropzone = {
	addRemoveLinks: true,
    acceptedFiles: "image/jpeg,image/png,image/gif", //old: "image/*",
    maxFiles: 1,
    //resizeWidth: 750,
	
init: function() {      
        this.on('addedfile', function(file){
        document.getElementById('logo').value = file.name;	
        document.getElementById('addLogo2').style.display = 'block';
        document.getElementById('addLogo4').style.display = 'none';
        document.getElementById('showLogo').style.display = 'none';
        document.getElementById('existing-logo').value = "";
     	});
     	this.on('reset', function(file){
        document.getElementById('addLogo2').style.display = 'none';
        document.getElementById('addLogo3').style.display = 'none';
        document.getElementById('addLogo4').style.display = 'block';
        document.getElementById("library").checked = false;
        document.getElementById("client").removeAttribute("required");
        
     	});   		 	 
   	 }
  
};
    </script>
<p></p>
<p style="color:#ccc;font-size: 50%;">Copyright (c) 2013-2018 Gijs Verrest - forked and updated May 2021 by Shane Chagpar - THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED.</p>
</body>
</html>