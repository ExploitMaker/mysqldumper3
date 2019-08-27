<link rel="stylesheet" href="./styles/stylesheet.css" type="text/css" />
<link rel='stylesheet' href='./styles/humane-jackedup.css'/>
<script src='./js/jquery.min.js'></script>
<script src='./js/humane.min.js'></script>
<script src='./js/functions.js'></script>
<?php
//Install Page
function gen_random($length) {
	$characters = array("a","A","b","B","c","C","d","D","e","E","f","F","g","G","h","H","i","I","j","J","k","K","l","L","m","M","n","N","o","O","p","P","q","Q","r","R","s","S","t","T","u","U","v","V","w","W","x","X","y","Y","z","Z","1","2","3","4","5","6","7","8","9");
	$i = 0;
	$random = "";
	while($i < $length) {
		$arrand = array_rand($characters, 1);
		$random .= $characters[$arrand];
		$i++;
	}
	return $random;
}

echo "<form action='' method='post'>
				<center>
					<br>
					<br>
					<br>
					<img src='./styles/images/logo3.png'><br>
					<div class='datagrid' style='width: 642px;'>
						<table width='100%'>
							<thead>
								<tr>
									<th colspan='2'>Installation</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>Host:</td>
									<td>
										<input type='text' name='mysql_host' style='width:300px;'>
										<br>
										<font size='1'><i>MySQL Host</i></font>
									</td>
								</tr>
								<tr class='alt'>
									<td>MySQL Username</td>
									<td>
										<input type='text' name='mysql_username' style='width: 300px;'>
										<br>
										<font size='1'><i>MySQL Username</i></font>
									</td>
								</tr>
								<tr>
									<td>MySQL Password:</td>
									<td>
										<input type='password' name='mysql_password' style='width: 300px;'>
										<br>
										<font size='1'><i>MySQL Password</i></font>
									</td>
								</tr>
								<tr class='alt'>
									<td>MySQL Port:</td>
									<td>
										<input type='text' name='mysql_port' style='width: 75px;'>
										<br>
										<font size='1'><i>MySQL Port</i></font>
									</td>
								</tr>
								<tr>
									<td colspan='2' align='center'><input type='submit' name='install' value='Install'></td>
								</tr>
							</tbody>
						</table>
					</div>
				</center>
			</form>";
if(isset($_POST['install'])) {
	$host = $_POST['mysql_host'];
	$port = $_POST['mysql_port'];
	$username = $_POST['mysql_username'];
	$password = $_POST['mysql_password'];
	
	if(!mysql_connect("$host:$port", $username, $password)) {
		echo "<script>humane.log('MySQL Info Incorrect');</script>";
	} else {
		$errorlogfile = "error_".gen_random("15").".log";
		$settingsfile = "settings_".gen_random("15").".json";
		$actionlogfile = "action_".gen_random("15").".log";
		$randencryptionkey = gen_random("25");
		$randcookiekey = gen_random("40");
		$defaultsettings = '{"password_enabled":false,"password":"","logging_enabled":true,"emailing_enabled":false,"email":"","estimate_sizes_enabled":false}';
		
		$configuration = <<<CONFIG
<?php
// Require classes
require_once("log.class.php");
require_once("settings.class.php");
require_once("email.class.php");
require_once("login.class.php");
require_once("header.class.php");

//Set some ini settings
//These are crucial so don't edit them
@ini_set("memory_limit","9999M");
@ini_set("max_input_time", 0);
@ini_set("max_execution_time", 0);

// Version
$\0version = "3.0 Beta";

//MySQL Configuration
$\0host = '$host';
$\0port = '$port';
$\0username = '$username';
$\0password = '$password';

// Randomly generated file names
$\0errorlog = "$errorlogfile";
$\0settingsfile = "$settingsfile";
$\0actionlog = "$actionlogfile";

// Encryption key
$\0ekey = "$randencryptionkey";

// Cookie key
$\0cookiekey = "$randcookiekey";

// Get install dir
$\0dir = str_replace("\\\", "/", str_replace('includes', '', pathinfo(__FILE__, PATHINFO_DIRNAME)));

// Full domain path
$\0domain = $\0_SERVER['HTTP_HOST'];
$\0script = $\0_SERVER['SCRIPT_NAME'];
$\0currentfile = basename($\0script);
$\0fullurl = "http://$\0domain".str_replace($\0currentfile, '', $\0script);

// Start new classes
$\0log = new log();
$\0settings = new settings();
$\0email = new email();
$\0login = new login();
$\0header = new header();

// Check connection
if(!mysql_connect("$\0host:$\0port", $\0username, $\0password)) {
	die('Could not connect to MySQL: ' . mysql_error());
}
?>
CONFIG;
		if(file_put_contents('./includes/configuration.php', str_replace("\0", "", $configuration)) && file_put_contents('./includes/'.$settingsfile, $defaultsettings)) {
			unlink(__FILE__);
			echo "<script>humane.log('Installed!', function () { window.location = './index.php'; });</script>";
		} else {
			echo "<script>humane.log('Failed To Install!');</script>";
		}
	}
}
?>