<?php
require_once ("./includes/functions.php");

if(isset($_POST['dumpdb'])) {
	// Database we will be dumping table from
	$database = $_POST['dumpdb'];
	// Current table we will be dumping. This will be a number not the actual table name. We will fetch that later.
	$current = $_POST['current'];
	// The next table in line to be dumped. This will also be a number.
	$next = $_POST['next'];
	// Filename stuff
	$file_date = date("m_d_Y");
	$file_name = $database . "_$file_date.sql";
	// Let's how many tables are in the database
	$count = mysql_fetch_object(mysql_query("SELECT COUNT(TABLE_NAME) as num_rows FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$database'"));
	
	// If start of the database dump write the header to the file
	if($current == 0) {
		$mysqlver = mysql_get_server_info();
		$headerdate = date('F j, Y, g:i a');
		$dumpheader = "-- MySQL Database Dump 
-- Host: $host  Database: $database
-- -----------------------------------------------------
-- Server Version: $mysqlver
-- Dumped on: $headerdate\n\n";
		file_put_contents($file_name, $dumpheader);
		if($settings->logging) {
			$log->logaction("Started dump on database: $database");
		}
	}
	
	// If the current table number equals the total number of tables the dump is complete.
	if($current == $count->num_rows) {
		if($settings->logging) {
			$log->logaction("Completed dump on database: $database");
		}
		$filesize = get_file_size($file_name);
		if($filesize > 52428800) {
			$jsonarr = array(
						"status" => "compressing",
						"startingbyte" => "0",
						"filesize" => "$filesize",
						"filename" => "$file_name",
						"log" => "\nStarting split compression on: $file_name"
					);
			if($settings->logging) {
				$log->logaction("Started split compression on file: $file_name");
			}
			echo json_encode($jsonarr);
		} else {
			// Get contents of file
			$get = file_get_contents($file_name);
			// Open new gz file
			$gzopen = gzopen($file_name.".gz", 'w9');
			// Write file conents to new gz file
			gzwrite($gzopen, $get);
			// Close gz file
			gzclose($gzopen);
			// Return JSON
			$jsonarr = array(
						"status" => "complete",
						"filename" => $file_name,
						"log" => "\nCompleted database dump for: $database\nCompressed $file_name to $file_name.gz" 
					);
			if($settings->logging) {
				$log->logaction("Completed compression on file: $file_name");
			}
			if($settings->emailing) {
				$settings_email = decrypt($settings->email, $ekey);
				$content = "A database dump ($database) has just been completed.<br>";
				$content .= "Here are the links to the uncompressed and compressed versions of the table dump:<br><br>";
				$content .= "Compressed: <a href='$fullurl$file_name.gz'>$file_name.gz</a> (".ByteConversion(get_file_size("$file_name.gz")).")<br>";
				$content .= "Uncompressed: <a href='$fullurl$file_name'>$file_name</a> (".ByteConversion($filesize).")<br>";
				$email->send($settings_email, $content);
			}
			echo json_encode($jsonarr);
		}
	} else {
		// Let's get the name of the current table so we can dump it.
		$alltables = array();
		$showtables = mysql_query("SHOW TABLES FROM $database");
		while($row = mysql_fetch_array($showtables)) {
			array_push($alltables, $row[0]);
		}
		$currenttable = $alltables[$current];
		
		// Dump current table
		dump_table($currenttable, $database, $file_name, $next);
		// Return JSON with needed variables
		$jsonarr = array(
					"status" => "dumping",
					"database" => $database,
					"current" => $next,
					"next" => ($next + 1),
					"numrows" => $count->num_rows,
					"log" => "\nFinished dumping table: $currenttable"
				);
		echo json_encode($jsonarr);
	}
}

if(isset($_POST['dump_table'])) {
	// Database we will be dumping table from
	$database = $_POST['database'];
	$table = $_POST['table'];
	$splitdone = $_POST['split_done'];
	// Filename stuff
	$file_date = date("m_d_Y");
	$file_name = $database."_".$table."_$file_date.sql";
	// File header stuff
	$mysqlver = mysql_get_server_info();
	$headerdate = date('F j, Y, g:i a');
	$dumpheader = "-- MySQL Database Dump 
-- Host: $host  Database: $database
-- -----------------------------------------------------
-- Server Version: $mysqlver
-- Dumped on: $headerdate\n\n";
	
	if($splitdone != "true") {
		// If logging is enabled log action
		if($settings->logging) {
			$log->logaction("Started dump on table: $database.$table");
		}
		
		// Write the header
		file_put_contents($file_name, $dumpheader);
		
		// Dump table
		dump_table($table, $database, $file_name, 0);
	}
	
	$filesize = get_file_size($file_name);
	if($filesize > 52428800) {
		$jsonarr = array(
					"status" => "compressing",
					"startingbyte" => "0",
					"filesize" => "$filesize",
					"filename" => "$file_name",
					"log" => "\nStarting split compression on: $file_name"
				);
			if($settings->logging) {
				$log->logaction("Started split compression on file: $file_name");
			}
			echo json_encode($jsonarr);
		} else {
			// Get contents of file
			$get = file_get_contents($file_name);
			// Open new gz file
			$gzopen = gzopen($file_name.".gz", 'w9');
			// Write file conents to new gz file
			gzwrite($gzopen, $get);
			// Close gz file
			gzclose($gzopen);
			// Return JSON
			$jsonarr = array(
						"status" => "complete",
						"filename" => $file_name,
						"log" => "\nCompleted table dump for: $database.$table\nCompressed $file_name to $file_name.gz" 
					);
			if($settings->logging) {
				$log->logaction("Completed compression on file: $file_name");
			}
			if($settings->emailing) {
				$settings_email = decrypt($settings->email, $ekey);
				$content = "A table dump ($database.$table) has just been completed.<br>";
				$content .= "Here are the links to the uncompressed and compressed versions of the table dump:<br><br>";
				$content .= "Compressed: <a href='$fullurl$file_name.gz'>$file_name.gz</a> (".ByteConversion(get_file_size("$file_name.gz")).")<br>";
				$content .= "Uncompressed: <a href='$fullurl$file_name'>$file_name</a> (".ByteConversion($filesize).")<br>";
				$email->send($settings_email, $content);
			}
			echo json_encode($jsonarr);
		}
}

if(isset($_POST['split_table'])) {
	// Database we will be splitting table from.
	$database = $_POST['db'];
	// The table we will be splitting
	$table = $_POST['split_table'];
	// Variables to be passed back to start the database dump where we left off
	// These won't change throughout this process of splitting the table
	$current = $_POST['current'];
	$next = $_POST['next'];
	
	// The filename to write to
	$filename = $_POST['filename'];
	
	// Total number of sections that we will be splitting
	$sections = $_POST['sections'];
	// The current section that we are splitting
	$lim = $_POST['limit'];
	
	// If the current section is greater than the total number of sections the splitting is complete
	if($lim > $sections) {
		if($_POST['onlytable'] == 'true') {
			$onlytable = true;
		} else {
			$onlytable = false;
		}
		$jsonarr = array(
				"status" => "dumping",
				"database" => $database,
				"current" => $current,
				"next" => $next,
				"table_only" => $onlytable,
				"log" => "\nFinished splitting table: $table"
		);
		echo json_encode($jsonarr);
	} else {
		split_table($table, $database, $filename, $lim, $sections);
		$jsonarr = array(
				"status" => "splitting",
				"database" => $database,
				"current" => $current,
				"next" => $next,
				"limit" => ($lim + 1),
				"table" => $table,
				"sections" => $sections,
				"log" => "\nSplitting section $lim of $sections from $table"
		);
		echo json_encode($jsonarr);
	}
}

if(isset($_POST['compress_file'])) {
	$filename = $_POST['compress_file'];
	$startingbyte = $_POST['startingbyte'];
	$filesize = $_POST['filesize'];
	$length = "52428800"; //50 MB in bytes
	
	if($startingbyte > $filesize) {
		$jsonarr = array(
					"status" => "complete",
					"filename" => $filename,
					"log" => "\nCompressed $filename to $filename.gz"
				);
		if($settings->logging) {
			$log->logaction("Completed split compression on file: $filename");
		}
		if($settings->emailing) {
			$settings_email = decrypt($settings->email, $ekey);
			$content = "A dump has just been completed and compressed ($filename).<br>";
			$content .= "Here are the links to the uncompressed and compressed versions of the dump:<br><br>";
			$content .= "Compressed: <a href='$fullurl$filename.gz'>$filename.gz</a> (".ByteConversion(get_file_size("$filename.gz")).")<br>";
			$content .= "Uncompressed: <a href='$fullurl$filename'>$filename</a> (".ByteConversion(get_file_size("$filename")).")<br>";
			$email->send($settings_email, $content);
		}
		echo json_encode($jsonarr);
	} else {
		// Open file
		$open = fopen($filename, 'r');
		// Place file pointer
		fseek($open, $startingbyte);
		$read = fread($open, $length);
		$gzopen = gzopen($filename.".gz", 'a9');
		gzwrite($gzopen, $read);
		gzclose($gzopen);
		fclose($open);
		$jsonarr = array(
					"status" => "compressing",
					"startingbyte" => ($startingbyte + $length),
					"filename" => $filename,
					"filesize" => $filesize,
					"log" => "\nCompressed ".ByteConversion($startingbyte)." out of ".ByteConversion($filesize)
				);
		echo json_encode($jsonarr);
	}
}

if(isset($_POST['dropdb'])) {
	$database = $_POST['dropdb'];
	if(mysql_query("DROP DATABASE $database")) {
		$jsonarr = array(
					"status" => "success"
				);
		echo json_encode($jsonarr);
		if($settings->logging) {
			$log->logaction("Dropped database: $database");
		}
	} else {
		$jsonarr = array(
					"status" => "fail"
				);
		echo json_encode($jsonarr);
		$log->MySQLerror();
	}
}

if(isset($_POST['delete_row'])) {
	$database = $_POST['database'];
	$table = $_POST['table'];
	$column = $_POST['column'];
	$value = $_POST['value'];
	if(mysql_query("DELETE FROM $database.$table WHERE $column='$value'")) {
		$jsonarr = array(
					"status" => "success"
				);
		echo json_encode($jsonarr);
		if($settings->logging) {
			$log->logaction("Deleted row from $database.$table");
		}
	} else {
		$jsonarr = array(
					"status" => "fail"
				);
		echo json_encode($jsonarr);
		$log->MySQLerror();
	}
}

if(isset($_POST['delete_dump'])) {
	$filename = $_POST['filename'];
	if(unlink($filename)) {
		$jsonarr = array(
					"status" => "success"
				);
		if($settings->logging) {
			$log->logaction("Deleted dump: $filename");
		}
		echo json_encode($jsonarr);
	} else {
		$jsonarr = array(
					"status" => "fail"
				);
		echo json_encode($jsonarr);
	}
}

if(isset($_POST['refresh_processes'])) {
$processes = mysql_query("SHOW PROCESSLIST");
echo "<table width='100%'>
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
	echo "<td><a href='#' onclick=\"kill_process('".$row['Id']."', '$i');\">Kill</a></td>";
	echo "</tr>";
	$isOdd = ! $isOdd;
	$i++;
}
echo "	</tbody>
	</table>";
}

if(isset($_POST['kill_process'])) {
	$processid = $_POST['kill_process'];
	if(mysql_query("KILL $processid")) {
		if($settings->logging) {
			$log->logaction("Killed process $processid");
		}
		$jsonarr = array(
			"status" => "success"
		);
		echo json_encode($jsonarr);
	} else {
		$log->MySQLError();
		$jsonarr = array(
			"status" => "fail"
		);
		echo json_encode($jsonarr);
	}
}

if(isset($_POST['fetch_table_row'])) {
	$database = $_POST['fetch_table_row'];
	$query = mysql_query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$database'");
	echo "<td>Table:</td><td><select id='table_select' name='table' style='width: 300px;'>";
	while($row = mysql_fetch_array($query)) {
		$table = $row[0];
		echo "<option value='$table'>$table</option>";
	}
	echo "</select></td>";
}

if(isset($_POST['fetch_column_row'])) {
	$database = $_POST['database'];
	$table = $_POST['table'];
	$first = $_POST['first'];
	echo "<tr><td>Column:</td><td><select class='column_select' style='width: 300px;'>";
	$query = mysql_query("SHOW COLUMNS FROM $database.$table");
	while($row = mysql_fetch_array($query)) {
		echo  "<option value='".$row['Field']."'>".$row['Field']."</option>";
	}
	if($first == "true") {
		echo "</select></td></tr>";
	} else {
		echo "</select><button onclick=\"$(this).closest('tr').remove();\">Remove</button></td></tr>";
	}
}

if(isset($_POST['custom_column_dump'])) {
	$database = $_POST['database'];
	$table = $_POST['table'];
	$separator = $_POST['separator'];
	$customcolumns = $_POST['customcolumns'];
	$count = mysql_fetch_object(mysql_query("SELECT COUNT(*) as num_rows FROM $database.$table"));
	
	if($count->num_rows > 50000) {
		$file_date = date("m_d_Y");
		$filename = "custom_column_dump_".$file_date.".txt";
		$jsonarr = array(
					"status" => "splitting",
					"database" => $database,
					"table" => $table,
					"customcolumns" => $customcolumns,
					"filename" => $filename,
					"limit" => 0,
					"sections" => floor($count->num_rows / 10000)
			);
		echo json_encode($jsonarr);
		exit;
	} else {
		$file_date = date("m_d_Y");
		$filename = "custom_column_dump_".$file_date.".txt";
		$mysqlver = mysql_get_server_info();
		$headerdate = date('F j, Y, g:i a');
		$dumpheader = "-- MySQL Custom Column Dump 
-- Host: $host  Database: $database  Table: $table
-- Columns: ".implode(",", $customcolumns)."
-- -----------------------------------------------------
-- Server Version: $mysqlver
-- Dumped on: $headerdate\n\n";
		file_put_contents($filename, $dumpheader);
		
		$query = mysql_query("SELECT ".implode(",", $customcolumns)." FROM $database.$table");
		while($row = mysql_fetch_assoc($query)) {
			$line = "";
			foreach($row as $colvalue) {
				$line .= $colvalue.$separator;
			}
			$line = rtrim($line, $separator)."\n";
			file_put_contents($filename, $line, FILE_APPEND);
		}
		
		//Dump is complete
		if($settings->logging) {
			$log->logaction("Completed column dump on $database.$table columns: "+implode(",", $customcolumns));
		}
		$filesize = get_file_size($filename);
		if($filesize > 52428800) {
			$jsonarr = array(
						"status" => "compressing",
						"startingbyte" => "0",
						"filesize" => "$filesize",
						"filename" => "$filename",
						"log" => "\nStarting split compression on: $filename"
					);
			if($settings->logging) {
				$log->logaction("Started split compression on file: $filename");
			}
			echo json_encode($jsonarr);
		} else {
			// Get contents of file
			$get = file_get_contents($filename);
			// Open new gz file
			$gzopen = gzopen($filename.".gz", 'w9');
			// Write file conents to new gz file
			gzwrite($gzopen, $get);
			// Close gz file
			gzclose($gzopen);
			// Return JSON
			$jsonarr = array(
						"status" => "complete",
						"filename" => $filename,
					);
			if($settings->logging) {
				$log->logaction("Completed compression on file: $filename");
			}
			if($settings->emailing) {
				$settings_email = decrypt($settings->email, $ekey);
				$content = "A colum dump on $database.$table has just been completed.<br>";
				$content .= "The following columns were dumped: ".implode(",", $custcolumns);
				$content .= "Here are the links to the uncompressed and compressed versions of the table dump:<br><br>";
				$content .= "Compressed: <a href='$fullurl$file_name.gz'>$file_name.gz</a> (".ByteConversion(get_file_size("$file_name.gz")).")<br>";
				$content .= "Uncompressed: <a href='$fullurl$file_name'>$file_name</a> (".ByteConversion($filesize).")<br>";
				$email->send($settings_email, $content);
			}
			echo json_encode($jsonarr);
		}
	}
}

if(isset($_POST['split_custom_column_dump'])) {
	$database = $_POST['database'];
	$table = $_POST['table'];
	$separator = $_POST['separator'];
	$customcolumns = $_POST['customcolumns'];
	$limit = $_POST['limit'];
	$sections = $_POST['sections'];
	$filename = $_POST['filename'];
	
	if($limit == 0) {
		$mysqlver = mysql_get_server_info();
		$headerdate = date('F j, Y, g:i a');
		$dumpheader = "-- MySQL Custom Column Dump 
-- Host: $host  Database: $database  Table: $table
-- Columns: ".implode(",", $customcolumns)."
-- -----------------------------------------------------
-- Server Version: $mysqlver
-- Dumped on: $headerdate\n\n";
		file_put_contents($filename, $dumpheader);
	}
	
	if($limit > $sections) {
		//Dump is complete
		if($settings->logging) {
			$log->logaction("Completed column dump on $database.$table columns: "+implode(",", $customcolumns));
		}
		$filesize = get_file_size($filename);
		if($filesize > 52428800) {
			$jsonarr = array(
						"status" => "compressing",
						"startingbyte" => "0",
						"filesize" => "$filesize",
						"filename" => "$filename",
						"log" => "\nStarting split compression on: $filename"
					);
			if($settings->logging) {
				$log->logaction("Started split compression on file: $filename");
			}
			echo json_encode($jsonarr);
		} else {
			// Get contents of file
			$get = file_get_contents($filename);
			// Open new gz file
			$gzopen = gzopen($filename.".gz", 'w9');
			// Write file conents to new gz file
			gzwrite($gzopen, $get);
			// Close gz file
			gzclose($gzopen);
			// Return JSON
			$jsonarr = array(
						"status" => "complete",
						"filename" => $filename,
						"log" => "\nCompleted column dump.\nCompressed $filename to $filename.gz" 
					);
			if($settings->logging) {
				$log->logaction("Completed compression on file: $filename");
			}
			if($settings->emailing) {
				$settings_email = decrypt($settings->email, $ekey);
				$content = "A colum dump on $database.$table has just been completed.<br>";
				$content .= "The following columns were dumped: ".implode(",", $custcolumns);
				$content .= "Here are the links to the uncompressed and compressed versions of the table dump:<br><br>";
				$content .= "Compressed: <a href='$fullurl$file_name.gz'>$file_name.gz</a> (".ByteConversion(get_file_size("$file_name.gz")).")<br>";
				$content .= "Uncompressed: <a href='$fullurl$file_name'>$file_name</a> (".ByteConversion($filesize).")<br>";
				$email->send($settings_email, $content);
			}
			echo json_encode($jsonarr);
		}
	} else {
		$limit2 = $limit * 10000;
		$query = mysql_query("SELECT ".implode(",", $customcolumns)." FROM $database.$table LIMIT $limit2, 10000");
		while($row = mysql_fetch_assoc($query)) {
			$line = "";
			foreach($row as $colvalue) {
				$line .= $colvalue.$separator;
			}
			$line = rtrim($line, $separator)."\n";
			file_put_contents($filename, $line, FILE_APPEND);
		}
		
		$jsonarr = array(
				"status" => "splitting",
				"log" => "Dumping section $limit out of $sections\n",
				"database" => $database,
				"table" => $table,
				"customcolumns" => $customcolumns,
				"filename" => $filename,
				"limit" => $limit+1,
				"sections" => $sections
		);
		echo json_encode($jsonarr);
		exit;
	}
}
?>