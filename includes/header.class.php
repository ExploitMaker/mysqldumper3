<?php
require_once("configuration.php");
class header {
	public function display_header() {
		global $username;
		global $host;
		$hostinfo = mysql_get_host_info();
		$protov = mysql_get_proto_info();
		$serverv = mysql_get_server_info();
		$clientinfo = mysql_get_client_info();
		$charset = mysql_client_encoding();
		$yourip = $_SERVER['REMOTE_ADDR'];
		$serverip = $_SERVER['SERVER_ADDR'];

		$whoami = function_exists("posix_getpwuid") ? posix_getpwuid(posix_geteuid()) : exec("whoami");
		$whoami = function_exists("posix_getpwuid") ? $whoami['name'] : exec("whoami");
		$uname = php_uname();
		$serversoftware = $_SERVER['SERVER_SOFTWARE'];
		$gatewayinterface = $_SERVER['GATEWAY_INTERFACE'];
		$servername = $_SERVER['SERVER_NAME'];
		$safemode = ini_get('safe_mode') ? "Enabled" : "Disabled";
		$openbasedir = ini_get('open_basedir') ? "Enabled" : "Disabled";
		$phpversion = phpversion();

		echo "<div class='datagrid' style='width: 100%;'>
					<table width='100%'>
						<thead>
							<tr>
								<th>User</th>
								<th>MySQL Server Version</th>
								<th>Protocol Version</th>
								<th>Client Info</th>
								<th>Character Set</th>
								<th>Your IP</th>
								<th>Server IP</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>$username@$host</td>
								<td>$serverv</td>
								<td>$protov</td>
								<td>$clientinfo</td>
								<td>$charset</td>
								<td>$yourip</td>
								<td>$serverip</td>
							</tr>
						</tbody>
					</table>
				</div>
				<br>
				<br>
				<div class='datagrid' style='width: 100%;'>
					<table width='100%'>
						<thead>
							<tr>
								<th>User</th>
								<th>System</th>
								<th>Server Software</th>
								<th>Gateway Interface</th>
								<th>PHP Version</th>
								<th>Server Name</th>
								<th>safe_mode</th>
								<th>open_basedir</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>$whoami</td>
								<td>$uname</td>
								<td>$serversoftware</td>
								<td>$gatewayinterface</td>
								<td>$phpversion</td>
								<td>$servername</td>
								<td>$safemode</td>
								<td>$openbasedir</td>
							</tr>
						</tbody>
					</table>
				</div>
				<br>
				<br>
				<br>";
	}
}
?>