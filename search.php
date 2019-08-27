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

if(isset($_GET['db'])) {
	$database = $_GET['db'];
	echo "Tables in: <a href='./tables.php?db=$database'>$database</a><br>";
	$get_tables = mysql_query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$database'");
	$num_tables = mysql_num_rows($get_tables);
	echo "<table width='100%' style='font-size: 12px;'>";
	while ($row = mysql_fetch_array($get_tables)) {
		$table = $row[0];
		echo "<tbody>
				<tr id='table_$table'>
					<td><a href='./tables.php?explore&db=$database&table=$table&l=0'>$table</a></td>
				</tr>
			</tbody>";
}
echo "</table>";
	
}
?>
	</div>
	<div id="right">
<?php
// Display Header
$header->display_header();

if(isset($_GET['db']) && !empty($_GET['table'])) {
	$database = $_GET['db'];
	$table = $_GET['table'];
	$getcolumns = mysql_query("SHOW COLUMNS FROM $database.$table");
	$options = "";
	while($row = mysql_fetch_array($getcolumns)) {
		$options .= "<option value='".$row['Field']."'>".$row['Field']."</option>";
	}
	echo "<center>
			<form action='' method='post'>
				<div class='datagrid' style='width: 642px;'>
					<table width='100%'>
						<thead>
							<tr>
								<th colspan='2'>Search in ".htmlspecialchars($database).".".htmlspecialchars($table)."
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>Search for:</td>
								<td><input type='text' name='search_value' style='width: 300px;'></td>
							</tr>
							<tr class='alt'>
								<td>In column:</td>
								<td><select name='search_column' style='width: 300px;'>$options</select></td>
							</tr>
							<tr>
								<td>Exact Match</td>
								<td><input type='checkbox' name='search_exact_match'></td>
							</tr>
							<tr class='alt'>
								<td colspan='2' align='center'><input type='submit' name='do_search' value='Search'></td>
							</tr>
						</tbody>
					</table>
				</div>
			</form>
		</center>";
}

if(isset($_POST['do_search'])) {
	$database = $_GET['db'];
	$table = $_GET['table'];
	$searchvalue = mysql_real_escape_string($_POST['search_value']);
	$column = $_POST['search_column'];
	$exactmatch = @$_POST['search_exact_match'];
	if(!empty($exactmatch)) {
		$query = mysql_query("SELECT * FROM $database.$table WHERE $column='$searchvalue'");
	} else {
		$query = mysql_query("SELECT * FROM $database.$table WHERE $column LIKE '%$searchvalue%'");
	}
	
	// Log search action
	if($settings->logging) {
		$log->logaction("Searched for $searchvalue in column $column from $database.$table");
	}
	// Do search
	if(mysql_num_rows($query) == 0) {
		echo "<center>No results found for '".htmlspecialchars($searchvalue)."' in column '".htmlspecialchars($column)."' from ".htmlspecialchars($database).".".htmlspecialchars($table);
	} else {
		$getcolumns = mysql_query("SHOW COLUMNS FROM $database.$table");
		$numrows = mysql_num_rows($getcolumns);
		echo "<center>".mysql_num_rows($query)." search result(s) for  '".htmlspecialchars($searchvalue)."' in column '".htmlspecialchars($column)."' from ".htmlspecialchars($database).".".htmlspecialchars($table)."<br>";
		echo "<center>
				<div class='datagrid'>
					<table width='100%'>
						<thead>
							<tr>";
		$fieldarray = array();
		while($row = mysql_fetch_array($getcolumns)) {
			echo "<th>".$row['Field']."</th>";
			array_push($fieldarray, $row['Field']);
		}
		echo "					<th>Edit</th>
								<th>Delete</th>
							</tr>
						</thead>";
		echo "			<tbody>";
		$isOdd = true;
		$ii = 0;
		while($row = mysql_fetch_array($query)) {
			if($isOdd) { echo "<tr class='alt' id='row_$ii'>"; } else { echo "<tr id='row_$ii'>"; }
			for($i = 0; $i < $numrows + 2; $i++) {
				if($i == $numrows) {
					echo "<td><a href='./tables.php?editrow&db=$database&table=$table&column=".$fieldarray[0]."&value=".$row[0]."' title='Edit Row'><img src='./styles/images/table_edit.png'></a></td>";
				} elseif($i == $numrows + 1) {
					echo "<td><a href='#' onclick=\"delete_row('$table', '$database', '".$row[0]."', '".$fieldarray[0]."', '$ii');\" title='Delete Row'><img src='./styles/images/table_row_delete.png'></a></td>";
				} else {
					echo "<td>".htmlspecialchars($row[$i])."</td>";
				}
			}
			echo "</tr>";
			$isOdd = ! $isOdd;
			$ii++;
		}
		echo "			<tbody>
					</table>
				</div>
			</center>";
	}
}
?>
</div>