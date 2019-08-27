<link rel="stylesheet" href="./styles/stylesheet.css" type="text/css" />
<link rel='stylesheet' href='./styles/humane-jackedup.css'/>
<script src='./js/jquery.min.js'></script>
<script src='./js/humane.min.js'></script>
<script src='./js/functions.js'></script>
<?php
require_once('./includes/functions.php');

if(isset($_POST['login'])) {
	$login->do_login($_POST['login_password'], decrypt($settings->login_password, $ekey));
}
?>