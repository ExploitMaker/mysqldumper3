<?php
require_once('configuration.php');
class login {
	public function display_login() {
		echo "</div>";
		echo "<div id='right'>";
		echo "<form action='./login.php' method='post'>
				<center>
					<div class='datagrid' style='width: 642px;'>
						<table width='100%'>
							<thead>
								<tr>
									<th colspan='2'>Login</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>Password:</td>
									<td>
										<input type='password' name='login_password' style='width: 300px;'>
										<br>
										<font size='1'><i>Password to login.</i></font>
									</td>
								</tr>
								<tr>
									<td colspan='2' align='center'><input type='submit' name='login' value='Login'></td>
								</tr>
							</tbody>
						</div>
					</center>
				</form>";
		exit;
	}
	
	public function do_login($password, $settingspassword) {
		global $cookiekey;
		if($password != $settingspassword) {
			echo "<script>humane.log('Incorrect password!', function() { window.location = './index.php'; });</script>";
		} else {
			setcookie("login_key", md5($cookiekey), time()+7200, '/', null, null, true);
			echo "<script>humane.log('Welcome', function() { window.location = './index.php'; });</script>";
		}
	}
}
?>