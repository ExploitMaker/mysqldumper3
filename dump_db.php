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
// Display Header
$header->display_header();

if(isset($_GET['db'])) {
	$database = $_GET['db'];
	echo "<div id='complete'></div>
		<br>
		Database Dump Progress:<br>
		<progress id='db_progress' max='' value=''></progress><br>
		Table Dump Progress:<br>
		<progress id='table_progress' max='' value=''></progress><br>
		Compression Progress:<br>
		<progress id='compression_progress' max='' value=''></progress><br>
		Log:<br>
		<textarea id='log' cols='110' rows='20' readonly></textarea><br>
		<input type='button' value='Start Dump' onclick=\"dump_database('$database', 0, 1);\">";
}
?>
</div>