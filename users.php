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
	
$users = mysql_query("SELECT * FROM mysql.user");
echo "<center>
		<div class='datagrid'>
			<table width='100%'>
				<thead>
					<tr>
						<th>User</th>
						<th>Host</th>
						<th>Password</th>
						<th>Select *</th>
						<th>Insert *</th>
						<th>Update *</th>
						<th>Delete *</th>
						<th>Create *</th>
						<th>Drop *</th>
						<th>Reload *</th>
						<th>Shutdown *</th>
						<th>Process *</th>
						<th>File *</th>
						<th>Grant *</th>
						<th>Refrences *</th>
						<th>Index *</th>
						<th>Alter *</th>
						<th>Show DB *</th>
						<th>Super *</th>
						<th>Create tmp Table *</th>
						<th>Lock Tables *</th>
						<th>Execute *</th>
						<th>Repl Slave *</th>
						<th>Repl Client *</th>
						<th>Create View *</th>
						<th>Show View *</th>
						<th>Create Routine *</th>
						<th>Alter Routine *</th>
						<th>Create User *</th>
					</tr>
				</thead>
				<tbody>";
$isOdd = true;
while($row = mysql_fetch_array($users)) {
	if($isOdd) { echo "<tr class='alt'>"; } else { echo "<tr>"; }
	echo "<td>".$row['User']."</td>";
	echo "<td>".$row['Host']."</td>";
	echo "<td>".$row['Password']."</td>";
	echo "<td>".$row['Select_priv']."</td>";
	echo "<td>".$row['Insert_priv']."</td>";
	echo "<td>".$row['Update_priv']."</td>";
	echo "<td>".$row['Delete_priv']."</td>";
	echo "<td>".$row['Create_priv']."</td>";
	echo "<td>".$row['Drop_priv']."</td>";
	echo "<td>".$row['Reload_priv']."</td>";
	echo "<td>".$row['Shutdown_priv']."</td>";
	echo "<td>".$row['Process_priv']."</td>";
	echo "<td>".$row['File_priv']."</td>";
	echo "<td>".$row['Grant_priv']."</td>";
	echo "<td>".$row['References_priv']."</td>";
	echo "<td>".$row['Index_priv']."</td>";
	echo "<td>".$row['Alter_priv']."</td>";
	echo "<td>".$row['Show_db_priv']."</td>";
	echo "<td>".$row['Super_priv']."</td>";
	echo "<td>".$row['Create_tmp_table_priv']."</td>";
	echo "<td>".$row['Lock_tables_priv']."</td>";
	echo "<td>".$row['Execute_priv']."</td>";
	echo "<td>".$row['Repl_slave_priv']."</td>";
	echo "<td>".$row['Repl_client_priv']."</td>";
	echo "<td>".$row['Create_view_priv']."</td>";
	echo "<td>".$row['Show_view_priv']."</td>";
	echo "<td>".$row['Create_routine_priv']."</td>";
	echo "<td>".$row['Alter_routine_priv']."</td>";
	echo "<td>".$row['Create_user_priv']."</td>";
	echo "</tr>";
	$isOdd = ! $isOdd;
}
echo "			</tbody>
			</table>
		</div>
	</center>
	Key:<br>
	* = Privilages<br>
	Y = Yes<br>
	N = No<br>
</div>";
?>