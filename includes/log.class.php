<?php
require_once("configuration.php");
require_once("functions.php");
class log {
	public function MySQLerror() {
		global $errorlog;
		global $dir;
		$timestamp = date("D M d H:i:s Y");
		$clientip = $_SERVER['REMOTE_ADDR'];
		$ercode = mysql_errno();
		$ermessage = mysql_error();
		$line = "[$timestamp] [client $clientip] Error Code: $ercode, Error Message: $ermessage\n";
		file_put_contents($dir."logs/".$errorlog, $line, FILE_APPEND);
	}
	public function logaction($action) {
		global $actionlog;
		global $dir;
		global $ekey;
		$timestamp = encrypt(date("D M d H:i:s Y"), $ekey);
		$clientip = encrypt($_SERVER['REMOTE_ADDR'], $ekey);
		$action = encrypt($action, $ekey);
		$line = "$timestamp:$clientip:$action\n";
		file_put_contents($dir."logs/".$actionlog, $line, FILE_APPEND);
	}
	public function displaylogs($start) {
		global $dir;
		global $ekey;
		global $actionlog;
		
		$file = array_reverse(file($dir."logs/".$actionlog));
		$limit = 30;
		$slice = array_slice($file, $start, $limit);
		echo "<center>
				<div class='datagrid' style='width: 100%;'>
					<table width='100%'>
						<thead>
							<tr>
								<th>Timestamp</th>
								<th>IP</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>";
		$isOdd = true;
		foreach($slice as $lines) {
			if($isOdd) { echo "<tr class='alt'>"; } else { echo "<tr>"; }
			$explode = explode(":", $lines);
			foreach($explode as $values) {
				echo "<td>".decrypt($values, $ekey)."</td>";
			}
			echo "</tr>";
			$isOdd = ! $isOdd;
		}
		echo "			</tbody>
						<tfoot>
							<tr>
								<td colspan='3'>
								<div id='paging'>
									<ul>
										<li><a href='?s=".($start - 30)."'><span>Previous</span></a></li>";
		$divide = floor(count($file) / 30);
		for($i = 0; $i <= $divide; $i++) {
			echo "<li><a href='?s=".($i * 30)."'><span>".($i + 1)."</span></a></li>";
		}
		echo "			 				<li><a href='?s=".($start + 30)."'><span>Next</span></a></li>
									</ul>
								</div>
							</tr>
						</tfoot>
					</table>
				</div>
			</center>";
	}
}
?>