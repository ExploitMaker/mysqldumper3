<?php
if(!file_exists('./includes/configuration.php')) {
	echo "<script>window.location = './install.php';</script>";
	exit;
}
require_once ("./includes/configuration.php");

function dump_table($table, $database, $filename, $next) {
	// Let's declare the dump file so we can add to it.
    $dump_file = "";
	// Let's count the rows from this table
    $count = mysql_fetch_object(mysql_query("SELECT COUNT(*) as num_rows FROM $database.$table"));
    $num_rows = $count->num_rows;
	
	// If the number of rows is more than 50,000 let's start splitting this table
    if ($num_rows > 50000) {
		$jsonarr = array(
					"status" => "splitting",
					"database" => $database,
					"table" => $table,
					"filename" => $filename,
					"current" => $next,
					"next" => ($next +1),
					"limit" => 0,
					"sections" => floor($count->num_rows / 10000)
				);
		echo json_encode($jsonarr);
        exit;
    } else {
		// Let's start getting the data from the table and format it.
		$table_dump = mysql_query("SELECT * FROM $database.$table");
		$dump_file .= "INSERT INTO `$table` VALUES ";
		while ($table_data = mysql_fetch_assoc($table_dump)) {
			$avalues = array_values($table_data);
			$akeys = array_keys($table_data);
			for($i = 0; $i < count($akeys); $i++) {
				$fetchfields = mysql_fetch_field($table_dump, $i);
				$fieldflags = mysql_field_flags($table_dump, $i);
				if($fetchfields->blob && stristr($fieldflags, 'BINARY')) {
					$avalues[$i] = "0x".bin2hex($avalues[$i]);
				}
			}
			$addslash = array_map('mysql_real_escape_string', $avalues);
			$fields = implode("`, `", $akeys);
			$values = implode("', '", $addslash);
			$dump_file.= "('$values'),";
		}
		if($num_rows == 0) {
			// Table is null we need to make sure nothing is written!
			$dump_file = "";
		} else {
			// Table is not null. Let's trim the , off the end and add a ; and line break
			$dump_file = rtrim($dump_file, ',');
			$dump_file = $dump_file.";\n";
		}
		
		// Let's start formatting the table dump
		// We need to get the create table code
        $createtable = mysql_fetch_row(mysql_query("SHOW CREATE TABLE $database.$table"));
		$createtable = $createtable[1].";\n";
        $dropc = "DROP TABLE IF EXISTS `$table`;\n";
        $lock = "LOCK TABLES `$table` WRITE;\n";
        $unlock = "UNLOCK TABLES;\n\n";
		// Write drop table if exists line
        file_put_contents($filename, $dropc, FILE_APPEND);
		// Write create table 
        file_put_contents($filename, $createtable, FILE_APPEND);
		// Write lock table
        file_put_contents($filename, $lock, FILE_APPEND);
		// Write the data of the table
        file_put_contents($filename, $dump_file, FILE_APPEND);
		// Write unlock table
        file_put_contents($filename, $unlock, FILE_APPEND);
    }
}

function split_table($table, $database, $filename, $limit, $sections) {
	// Let's declare the dump file so we can add data to it
    $dump_file = "";
    $limit2 = $limit * 10000;
	// Let's start getting the data from table. We will limit this to 10,000 rows
    $table_dump = mysql_query("SELECT * FROM $database.$table LIMIT $limit2, 10000");
	$dump_file .= "INSERT INTO `$table` VALUES ";
    while ($table_data = mysql_fetch_assoc($table_dump)) {
        $avalues = array_values($table_data);
        $akeys = array_keys($table_data);
		for($i = 0; $i < count($akeys); $i++) {
			$fetchfields = mysql_fetch_field($table_dump, $i);
			$fieldflags = mysql_field_flags($table_dump, $i);
			if($fetchfields->blob && stristr($fieldflags, 'BINARY')) {
				$avalues[$i] = "0x".bin2hex($avalues[$i]);
			}
		}
        $addslash = array_map('mysql_real_escape_string', $avalues);
        $fields = implode("`, `", $akeys);
        $values = implode("', '", $addslash);
        $dump_file.= "('$values'),";
    }
	$dump_file = rtrim($dump_file, ',');
	$dump_file = $dump_file.";\n";
	
	// If this is the first part of the splitting we need to write a few things
    if ($limit == 0) {
		// Let's start to format the dump
		// We need to get the create table code
		$createtable = mysql_fetch_row(mysql_query("SHOW CREATE TABLE $database.$table"));
		$createtable = $createtable[1].";\n";
		$dropc = "DROP TABLE IF EXISTS `$table`;\n";
		$lock = "LOCK TABLES `$table` WRITE;\n";
		// Write drop table if exists
        file_put_contents($filename, $dropc, FILE_APPEND);
		// Write create table
        file_put_contents($filename, $createtable, FILE_APPEND);
		// Write lock table
        file_put_contents($filename, $lock, FILE_APPEND);
    }
	// If it's not the start or the end of splitting let's just write the table data
    file_put_contents($filename, $dump_file, FILE_APPEND);
	// If this is the last section of table we need to write unlock
    if ($limit == $sections) {
		$unlock = "UNLOCK TABLES;\n\n";
		// Write unlock table
        file_put_contents($filename, $unlock, FILE_APPEND);
    }		
}

function ByteConversion($bytes, $precision = 2) {
    $kilobyte = 1024;
    $megabyte = $kilobyte * 1024;
    $gigabyte = $megabyte * 1024;
    $terabyte = $gigabyte * 1024;

    if (($bytes >= 0) && ($bytes < $kilobyte)) {
        return $bytes . ' B';
    } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
        return round($bytes / $kilobyte, $precision) . ' KB';
    } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
        return round($bytes / $megabyte, $precision) . ' MB';
    } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
        return round($bytes / $gigabyte, $precision) . ' GB';
    } elseif ($bytes >= $terabyte) {
        return round($bytes / $terabyte, $precision) . ' TB';
    } else {
        return $bytes . ' B';
    }
}

function get_file_size($filename) {
	// Let's declare the full URL of the file to get the size of
	$domain = $_SERVER['HTTP_HOST'];
	$script = $_SERVER['SCRIPT_NAME'];
	$currentfile = basename($script);
	$fullurl = "http://$domain".str_replace($currentfile, '', $script).$filename;
	
	// Context for file_get_contents HEAD request
	$context = stream_context_create(array('http'=>array('method'=>'HEAD')));
	
	// file_get_contents HEAD request
	$request = file_get_contents($fullurl, false, $context);
	
	// Go through each response header and search for Content-Length
	foreach($http_response_header as $hrh) {
		if(strpos($hrh, 'Content-Length') !== false) {
			$size = str_replace('Content-Length:', '', $hrh);
			$size = trim($size);
		}
	}
	return $size;
}

function decrypt($base64, $key)
{
    return rtrim(
        mcrypt_decrypt(
            MCRYPT_RIJNDAEL_256, 
            $key, 
            base64_decode($base64), 
            MCRYPT_MODE_ECB,
            mcrypt_create_iv(
                mcrypt_get_iv_size(
                    MCRYPT_RIJNDAEL_256,
                    MCRYPT_MODE_ECB
                ), 
                MCRYPT_RAND
            )
        ), "\0"
    );
}
function encrypt($string, $key)
{
    return base64_encode(rtrim(
        mcrypt_encrypt(
            MCRYPT_RIJNDAEL_256, 
            $key, 
            $string, 
            MCRYPT_MODE_ECB,
            mcrypt_create_iv(
                mcrypt_get_iv_size(
                    MCRYPT_RIJNDAEL_256,
                    MCRYPT_MODE_ECB
                ), 
                MCRYPT_RAND
            )
        ), "\0"
    ));
}

function logged_in() {
	global $cookiekey;
	if(@$_COOKIE['login_key'] != md5($cookiekey)) {
		return false;
	} else {
		return true;
	}
}
?>