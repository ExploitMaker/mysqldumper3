<link rel="stylesheet" href="./styles/stylesheet.css" type="text/css" />
<link rel='stylesheet' href='./styles/humane-jackedup.css'/>
<script src='./js/jquery.min.js'></script>
<script src='./js/humane.min.js'></script>
<script src='./js/functions.js'></script>

<div id="container">
	<div id="left">
			<center>
				<a href='./index.php'><img src="./styles/images/small_logo2.png" style="margin-top: 10px;"></a><br>
				<a href='./index.php' title='Home'><img src='./styles/images/home.png'></a>
				<a href='./users.php' title='Users'><img src="./styles/images/users.png"></a>
				<a href='./processes.php' title='Processes'><img src="./styles/images/processes.png"></a>
				<a href='./logs.php?s=0' title='Log Viewer'><img src='./styles/images/logs.png'></a>
				<a href='./dumps.php' title='Dumps'><img src='./styles/images/dumps.png'></a>
				<a href='./column_dump.php' title='Column Dumper'><img src='./styles/images/column_dumper.png'></a>
			</center>
<?php
require_once ("./includes/functions.php");

// Check to see if password protection is enabled
if($settings->password) {
	// Check to see if user is logged in
	if(!logged_in()) {
		$login->display_login();
	}
}

if(isset($_POST['update_settings'])) {
	$passwordprotect = $_POST['password_protection'];
	$password = $_POST['password'];
	$reenterpassword = $_POST['re_enter_password'];
	$emailing = $_POST['emailing'];
	$email = $_POST['email'];
	$logging = $_POST['logging'];
	$estimatesizes = $_POST['estimate_sizes'];
	
	if($passwordprotect == "true") {
		if($password != $reenterpassword) {
			echo "<script>humane.log('Passwords do not match!', function() {window.location = './index.php';});</script>";
			exit;
		} else {
			$password = encrypt($password, $ekey);
			$passwordprotect = true;
		}
	} else {
		$password = "";
		$passwordprotect = false;
	}
	if($emailing == "true") {
		$explode = explode(",", $email);
		if(count($explode) == 1) {
			if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$email = encrypt($email, $ekey);
				$emailing = true;
			} else {
				echo "<script>humane.log('Email is not valid!', function() {window.location = './index.php';});</script>";
				exit;
			}
		} else {
			foreach($explode as $emails) {
				if(!filter_var(trim($emails), FILTER_VALIDATE_EMAIL)) {
					echo "<script>humane.log('An email is not valid!', function() {window.location = './index.php';});</script>";
					exit;
				}
			}
			$email = encrypt($email, $ekey);
			$emailing = true;
		}
	} else {
		$email = "";
		$emailing = false;
	}
	if($logging == "true") {
		$logging = true;
	} else {
		$logging = false;
	}
	if($estimatesizes == "true") {
		$estimatesizes = true;
	} else {
		$estimatesizes = false;
	}
	$settingsarray = array(
					"password_enabled" => $passwordprotect,
					"password" => $password,
					"logging_enabled" => $logging,
					"emailing_enabled" => $emailing,
					"email" => $email,
					"estimate_sizes_enabled" => $estimatesizes
				);
	$settings->update_settings($settingsarray);
}

$get_dbs = mysql_query("SHOW DATABASES");
echo "<table width='100%' style='font-size: 12px;'>";
while ($row = mysql_fetch_array($get_dbs)) {
    $db = $row[0];
    echo "<tbody>";
			echo "<tr id='database_$db'>";
			echo "<td><a href='./tables.php?db=$db'>$db</a></td>
				<td align='center'><a href='./dump_db.php?db=$db' title='Dump Database'><img src='./styles/images/small_db_dump.png'></a></td>
				<td align='center'><a href='#' onclick=\"drop_database('$db');\" title='Drop Database'><img src='./styles/images/small_db_drop.png'></a></td>
			</tr>
		</tbody>";
}
echo "</table>";
?>
	</div>
	<div id="right">
<?php
$header->display_header();
$settings->display_settings();
?>
</div>