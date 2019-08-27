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
$version = "3.0 Beta";

//MySQL Configuration
$host = 'localhost';
$port = '3306';
$username = 'root';
$password = 'root';

// Randomly generated file names
$errorlog = "error_saidsalidhsaoasd.log";
$settingsfile = "settings_sadasdsadsad.json";
$actionlog = "action_sakdasdsadhsajdsad.log";

// Encryption key
$ekey = "sadhasldlasdksahdasdksasdsad";

// Cookie key
$cookiekey = "sadasdasdsadsadjsaldjsahdjkashdksad";

// Get install dir
$dir = str_replace("\\", "/", str_replace('includes', '', pathinfo(__FILE__, PATHINFO_DIRNAME)));

// Full domain path
$domain = $_SERVER['HTTP_HOST'];
$script = $_SERVER['SCRIPT_NAME'];
$currentfile = basename($script);
$fullurl = "http://$domain".str_replace($currentfile, '', $script);

// Start new classes
$log = new log();
$settings = new settings();
$email = new email();
$login = new login();
$header = new header();

// Check connection
if(!mysql_connect("$host:$port", $username, $password)) {
	die('Could not connect to MySQL: ' . mysql_error());
}
?>