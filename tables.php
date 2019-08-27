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
	echo "Tables in: <a href='?db=$database'>$database</a><br>";
	$get_tables = mysql_query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$database'");
	$num_tables = mysql_num_rows($get_tables);
	echo "<table width='100%' style='font-size: 12px;'>";
	while ($row = mysql_fetch_array($get_tables)) {
		$table = $row[0];
		echo "<tbody>
				<tr id='table_$table'>
					<td><a href='?explore&db=$database&table=$table&l=0'>$table</a></td>
				</tr>
			</tbody>";
	}
echo "</table>";
}
?>
	</div>
	<div id="right">
<?php
//Display Header
$header->display_header();

if(isset($_GET['db']) && empty($_GET['table'])) {
	if($settings->estimate_sizes) {
		$dbsize = mysql_fetch_assoc(mysql_query("SELECT table_schema AS \"database\", sum(data_length + index_length) AS \"size\" FROM information_schema.TABLES WHERE table_schema='".$_GET['db']."' GROUP BY table_schema"));
	}
	$dbchar = mysql_fetch_assoc(mysql_query("SELECT default_collation_name,default_character_set_name FROM information_schema.SCHEMATA WHERE schema_name = '".$_GET['db']."'"));
	echo "<center>
			<a href='./dump_db.php?db=$database' title='Dump Database'><img src='./styles/images/big_db_dump.png'></a>
			<a href='#' onclick=\"drop_database('".$_GET['db']."');\" title='Drop Database'><img src='./styles/images/big_db_drop.png'></a><br>
			<div class='datagrid' style='width: 60%;'>
				<table width='100%'>
					<thead>
						<tr>
							<th colspan='2'>".htmlspecialchars($_GET['db'])."</th>
						</tr>
					<thead>
					<tbody>
						<tr>
							<td>Tables:</td>
							<td>$num_tables</td>
						</tr>";
						if($settings->estimate_sizes) {
							echo "<tr class='alt'>
									<td>Estimated Size:</td>
									<td>".ByteConversion($dbsize['size'])."</td>
								</tr>";
						}
						echo "<tr>
								<td>Collation:</td>
								<td>".$dbchar['default_collation_name']."</td>
							</tr>
							<tr class='alt'>
								<td>Character Set:</td>
								<td>".$dbchar['default_character_set_name']."</td>
						</tbody>
					</table>
				</div>
			</center>";
}

if(isset($_GET['explore'])) {
	$database = $_GET['db'];
	$table = $_GET['table'];
	$limit = $_GET['l'];
	$row_limit = 100;
	$getcolumns = mysql_query("SHOW COLUMNS FROM $database.$table");
	$numrows = mysql_num_rows($getcolumns);
	$rows = mysql_fetch_assoc(mysql_query("SELECT COUNT(*) as num_rows FROM $database.$table"));
	if($settings->estimate_sizes) {
		$tablesize = mysql_fetch_assoc(mysql_query("SELECT table_name AS \"table\", sum(data_length + index_length) AS \"size\" FROM information_schema.TABLES WHERE table_schema='".$database."' AND table_name='".$table."'"));
	}
	$status = mysql_fetch_assoc(mysql_query("SHOW TABLE STATUS FROM $database WHERE Name='".$table."'"));
	
	// echo table information
	echo "<center>
			<a href='./dump_table.php?dump_table&db=$database&table=$table' title='Dump Table'><img src='./styles/images/big_db_dump.png'></a>
			<a href='?insert&db=$database&table=$table' title='Insert Row'><img src='./styles/images/insert_row.png'></a>
			<a href='?truncate_table&db=$database&table=$table' title='Truncate Table'><img src='./styles/images/table_truncate.png'></a>
			<a href='?drop_table&db=$database&table=$table' title='Drop Table'><img src='./styles/images/big_db_drop.png'></a>
			<a href='./search.php?db=$database&table=$table' title='Search Table'><img src='./styles/images/table_search.png'></a><br>
			<div class='datagrid' style='width: 60%;'>
				<table width='100%'>
					<thead>
						<tr>
							<th colspan='2'>".htmlspecialchars($database).".".htmlspecialchars($table)."
						</tr>
					<thead>
					<tbody>
						<tr>
							<td>Rows:</td>
							<td>".number_format($rows['num_rows'])."</td>
						</tr>";
						if($settings->estimate_sizes) {
							echo "<tr class='alt'>
									<td>Estimated Size:</td>
									<td>".ByteConversion($tablesize['size'])."</td>
								</tr>";
						}
						echo "<tr>
								<td>Engine:</td>
								<td>".$status['Engine']."</td>
							</tr>
							<tr class='alt'>
								<td>Version:</td>
								<td>".$status['Version']."</td>
							</tr>
							<tr>
								<td>Row Format:</td>
								<td>".$status['Row_format']."</td>
							</tr>
							<tr class='alt'>
								<td>Average Row Length:</td>
								<td>".ByteConversion($status['Avg_row_length'])."</td>
							</tr>
							<tr>
								<td>Create Time:</td>
								<td>".$status['Create_time']."</td>
							</tr>
							<tr class='alt'>
								<td>Update Time:</td>
								<td>".$status['Update_time']."</td>
							</tr>
							<tr>
								<td>Collation:</td>
								<td>".$status['Collation']."</td>
							</tr>
							<tr class='alt'>
								<td>Comment:</td>
								<td>".$status['Comment']."</td>
							</tr>
						</tbody>
					</table>
				</div>
			</center><br><br><br>";

	// echo table data
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
	$columndata = mysql_query("SELECT * FROM $database.$table LIMIT $limit,$row_limit");
	echo "			<tbody>";
	$isOdd = true;
	$ii = 0;
	while($row = mysql_fetch_array($columndata)) {
		if($isOdd) { echo "<tr class='alt' id='row_$ii'>"; } else { echo "<tr id='row_$ii'>"; }
		for($i = 0; $i < $numrows + 2; $i++) {
			if($i == $numrows) {
				echo "<td><a href='?editrow&db=$database&table=$table&column=".$fieldarray[0]."&value=".$row[0]."' title='Edit Row'><img src='./styles/images/table_edit.png'></a></td>";
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
	echo "			<tfoot>
						<tr>
							<td colspan='".($numrows + 2)."'>
							<div id='paging'>
								<ul>
									<li><a href='?explore&db=$database&table=$table&l=".($limit - 100)."'><span>Previous</span></a></li>";
	$totalrows = mysql_fetch_object(mysql_query("SELECT COUNT(*) as num_rows FROM $database.$table"));
	$divide = floor($totalrows->num_rows / 100);
	for($i = 0; $i <= $divide; $i++) {
		echo "<li><a href='?explore&db=$database&table=$table&l=".($i * 100)."'><span>".($i + 1)."</span></a></li>";
	}
	echo "			 				<li><a href='?explore&db=$database&table=$table&l=".($limit + 100)."'><span>Next</span></a></li>
								</ul>
							</div>
						</tr>
					</tfoot>
					<tbody>
				</table>
			</div>
		</center>";
}

if(isset($_GET['drop_table'])) {
	$table = $_GET['table'];
	$database = $_GET['db'];
	if(mysql_query("DROP TABLE $database.$table")) {
		if($settings->logging) {
			$log->logaction("Dropped $database.$table");
		}
		echo "<script>humane.log('Dropped table: $table', function () { window.location = '?db=$database'; });</script>";
		exit;
	} else {
		$log->MySQLError();
		echo "<script>humane.log('Failed to drop table!', function () { window.location = '?explore&db=$database&table=$table&l=0'; });</script>";
		exit;
	}
}

if(isset($_GET['truncate_table'])) {
	$table = $_GET['table'];
	$database = $_GET['db'];
	if(mysql_query("TRUNCATE TABLE $database.$table")) {
		if($settings->logging) {
			$log->logaction("Truncated tabled $database.$table");
		}
		echo "<script>humane.log('Truncated table: $table', function () { window.location = '?explore&db=$database&table=$table&l=0'; });</script>";
		exit;
	} else {
		$log->MySQLError();
		echo "<script>humane.log('Failed to truncate table!', function () { window.location = '?explore&db=$database&table=$table&l=0'; });</script>";
		exit;
	}
}

if(isset($_POST['insert_row'])) {
	$database = $_GET['db'];
	$table = $_GET['table'];
	$query = "INSERT INTO $database.$table VALUES (";
	array_pop($_POST);
	foreach($_POST as $key => $val) {
		$query .= "'".mysql_real_escape_string($val)."',";
	}
	$query = rtrim($query, ",").")";
	if(mysql_query($query)) {
		if($settings->logging) {
			$log->logaction("Inserted row into $database.$table");
		}
		echo "<script>humane.log('Inserted row!', function () { window.location = '?insert&db=dbtech&table=$table'; });</script>";
		exit;
	} else {
		$log->MySQLerror();
		echo "<script>humane.log('Failed to insert row!', function () { window.location = '?insert&db=dbtech&table=$table'; });</script>";
		exit;
	}
}
if(isset($_GET['insert'])) {
	$database = $_GET['db'];
	$table = $_GET['table'];
	$getcolumns = mysql_query("SHOW COLUMNS FROM $database.$table");
	
	echo "<center>
			<div class='datagrid' style='width: 80%;'>
				<form action='' method='post'>
					<table width='100%'>
						<thead>
							<tr>
								<th>Column</th>
								<th>Type</th>
								<th>Value</th>
							</tr>
						</thead>
						<tbody>";
	$isOdd = true;
	while($row = mysql_fetch_array($getcolumns)) {
		if($isOdd) { echo "<tr class='alt'>"; } else { echo "<tr>"; }
		echo "  <td>".$row['Field']."</td>
				<td>".$row['Type']."</td>
				<td><input type='text' name='".$row['Field']."' size='35'></td>";
		echo "</tr>";
		$isOdd = ! $isOdd;
	}
	echo "<tr>
			<td colspan='3'><center><input type='submit' name='insert_row' value='Insert'></center></td>
		</tr>";
	echo "				<tbody>
					</table>
				</form>
			</div>
		</center>";
}
if(isset($_POST['edit_row'])) {
	$database = $_GET['db'];
	$table = $_GET['table'];
	$column = $_GET['column'];
	$value = $_GET['value'];
	$query = "UPDATE $database.$table SET ";
	array_pop($_POST);
	foreach($_POST as $key => $val) {
		$query .= "$key='".mysql_real_escape_string($val)."', ";
	}
	$query = rtrim($query, ", ")." WHERE $column='$value'";
	if(mysql_query($query)) {
		if($settings->logging) {
			$log->logaction("Edited row in $database.$table");
		}
		echo "<script>humane.log('Edited row!', function () { window.location = '?editrow&db=$database&table=$table&column=$column&value=$value'; });</script>";
		exit;
	} else {
		$log->MySQLerror();
		echo "<script>humane.log('Failed to edit row!', function () { window.location = '?editrow&db=$database&table=$table&column=$column&value=$value'; });</script>";
		exit;
	}
}
if(isset($_GET['editrow'])) {
	$database = $_GET['db'];
	$table = $_GET['table'];
	$column = $_GET['column'];
	$value = $_GET['value'];
	$getcolumns = mysql_query("SHOW COLUMNS FROM $database.$table");
	$columndata = mysql_fetch_array(mysql_query("SELECT * FROM $database.$table WHERE $column='$value'"));
	
	echo "<center>
			<div class='datagrid' style='width: 80%;'>
				<form action='' method='post'>
					<table width='100%'>
						<thead>
							<tr>
								<th>Column</th>
								<th>Type</th>
								<th>Value</th>
							</tr>
						</thead>
						<tbody>";
	$i = 0;
	while($row = mysql_fetch_array($getcolumns)) {
		echo "<tr>
				<td>".$row['Field']."</td>
				<td>".$row['Type']."</td>
				<td><textarea cols='50' rows='5' name='".$row['Field']."'>".htmlspecialchars($columndata[$i])."</textarea></td>
			</tr>";	
		$i++;
	}
	echo "<tr>
			<td colspan='3'><center><input type='submit' name='edit_row' value='Edit'></center></td>
		</tr>";
	echo "				<tbody>
					</table>
				</form>
			</div>
		</center>";
}
?>
</div>