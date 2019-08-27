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
$options = "";
while ($row = mysql_fetch_array($get_dbs)) {
    $db = $row[0];
	$options .= "<option value='$db'>$db</option>";
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

//Display Header
$header->display_header();

echo "<div id='column_dumper'>
		<center>
			<div class='datagrid'>
				<table width='100%' style='width: 642px;' id='column_dumper_table'>
					<thead>
						<tr>
							<th colspan='2'>Column Dumper</th>
					</thead>
					<tbody>
						<tr>
							<td>Database:</td>
							<td><select name='database' style='width: 300px;' id='database_select'>".$options."</select></td>
						</tr>
						<tr id='tables_row'></tr>
						<tr id='format_row' style='display: none;'>
							<td>Format</td>
							<td>
								<select name='format' style='width: 300px;' id='format_select'>
									<option value='0'>Format</option>
									<option value='custom'>Custom</option>
								</select>
							</td>
						</tr>
						<tr id='separator_row' style='display: none;'>
							<td>Seperator:</td>
							<td><input type='text' name='separator' value=':' size='2' id='separator'></td>
						</tr>
						<tr id='submit_row' style='display: none;'>
							<td colspan='2' id='submit_cell'><button type='submit' name='dump_columns' onclick=\"parse_column_data();\">Dump</button></td>
						</tr>
					</tbody>
				</table>
			</div>
		</center>
	</div>
</div>";
echo "<script>
		$('#database_select').change(function(){
			update_table_row($(this).val());
		});
	</script>";
?>