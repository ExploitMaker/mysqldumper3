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
echo "</table>
	</div>
	<div id='right'>";

// Display Header
$header->display_header();

$processes = mysql_query("SHOW PROCESSLIST");
echo "<center>
		Will refresh every 2 seconds.<br>
		<div class='datagrid' id='processes'>
			<table width='100%'>
				<thead>
					<tr>
						<th>ID</th>
						<th>User</th>
						<th>Host</th>
						<th>Database</th>
						<th>Command</th>
						<th>Time</th>
						<th>State</th>
						<th>Info</th>
						<th>Kill</th>
					</tr>
				</thead>
				<tbody>";
$isOdd = true;
$i = 0;
while($row = mysql_fetch_array($processes)) {
	if($isOdd) { echo "<tr class='alt' id='row_$i'>"; } else { echo "<tr id='row_$i'>"; }
	echo "<td>".$row['Id']."</td>";
	echo "<td>".$row['User']."</td>";
	echo "<td>".$row['Host']."</td>";
	if($row['db'] == NULL) { echo "<td>NULL</td>"; } else { echo "<td>".$row['db']."</td>"; }
	echo "<td>".$row['Command']."</td>";
	echo "<td>".$row['Time']."</td>";
	if($row['State'] == NULL) { echo "<td>NULL</td>"; } else { echo "<td>".$row['State']."</td>"; }
	echo "<td>".$row['Info']."</td>";
	echo "<td><a href='' onclick=\"kill_process('".$row['Id']."', '$i');\">Kill</a></td>";
	echo "</tr>";
	$isOdd = ! $isOdd;
	$i++;
}
echo "			</tbody>
			</table>
		</div>
	</center>
</div>
<script>setInterval(function(){refresh_processes();},2000);</script>";

?>