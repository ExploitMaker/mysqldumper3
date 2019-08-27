<?php
require_once("configuration.php");
class settings {
	public $password;
	public $logging;
	public $emailing;
	public $email;
	
	public function __construct() {
		global $settingsfile;
		global $dir;
		$getsettings = json_decode(file_get_contents($dir."includes/".$settingsfile), true);
		$this->password = $getsettings['password_enabled'];
		$this->login_password = $getsettings['password'];
		$this->logging = $getsettings['logging_enabled'];
		$this->emailing = $getsettings['emailing_enabled'];
		$this->email = $getsettings['email'];
		$this->estimate_sizes = $getsettings['estimate_sizes_enabled'];
	}
	
	public function display_settings() {
		global $ekey;
		echo "<form action='' method='post'>
				<center>
					<div class='datagrid' style='width: 642px;'>
						<table width='100%'>
							<thead>
								<tr>
									<th colspan='2'>Settings</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>Password Protection:</td>
									<td>
										<select name='password_protection' style='width: 300px;'>
											<option value='false'>Off</option>";
											if($this->password) { echo "<option value='true' selected>On</option>"; }
											else { echo "<option value='true'>On</option>"; }
								echo "</select>
										<br>
										<font size='1'><i>Add a login to the dumper.</i></font>
									</td>
								</tr>
								<tr class='alt'>
									<td>Password:</td>
									<td>
										<input type='password' name='password' style='width: 300px;'>
										<br>
										<font size='1'><i>Password for login. Leave blank if you have password protection disabled.</i></font>
									</td>
								</tr>
								<tr>
									<td>Re-Enter Password:</td>
									<td>
										<input type='password' name='re_enter_password' style='width: 300px;'>
										<br>
										<font size='1'><i>Re-enter password for login. Leave blank if you have password protection disabled.</i></font>
									</td>
								</tr>
								<tr class='alt'>
									<td>Emailing:</td>
									<td>
										<select name='emailing' style='width: 300px;'>
											<option value='false'>Off</option>";
											if($this->emailing) { echo "<option value='true' selected>On</option>"; }
											else { echo "<option value='true'>On</option>"; }
									echo "</select>
										<br>
										<font size='1'><i>Send emails when dump are complete.</i></font>
									</td>
								</tr>
								<tr>
									<td>Email:</td>
									<td>";
										if($this->emailing) { echo "<input type='text' name='email' style='width: 300px;' value='".decrypt($this->email, $ekey)."'>"; }
										else { echo "<input type='text' name='email' style='width: 300px;'>"; }
								   echo "<br>
										<font size='1'><i>Email to send dump notifications to. Separate multiple emails with a comma.</i></font>
									</td>
								</tr>
								<tr class='alt'>
									<td>Logging:</td>
									<td>
										<select name='logging' style='width: 300px;'>
											<option value='false'>Off</option>";
											if($this->logging) { echo "<option value='true' selected>On</option>"; }
											else { echo "<option value='true'>On</option>"; }
									echo "</select>
										<br>
										<font size='1'><i>Log actions done in the dumper.</i></font>
									</td>
								</tr>
								<tr>
									<td>Estimate DB and Table Sizes:</td>
									<td>
										<select name='estimate_sizes' style='width: 300px;'>
											<option value='false'>Off</option>";
											if($this->estimate_sizes) { echo "<option value='true' selected>On</option>"; }
											else { echo "<option value='true'>On</option>"; }
									echo "</select>
										<br>
										<font size='1'><i>Esitmate the size of DB's and tables. Turning this off will improve load times on pages.</i></font>
									</td>
								</tr>
								<tr class='alt'>
									<td colspan='2' align='center'><input type='submit' name='update_settings' value='Update'></td>
								</tr>
							</tbody>
						</table>
					</div>
				</center>
			</form>";
	}
	public function update_settings($settingsarray) {
		global $dir;
		global $settingsfile;
		$json = json_encode($settingsarray);
		file_put_contents($dir."includes/".$settingsfile, $json);
		echo "<script>humane.log('Settings updated!', function() { window.location = './index.php'; });</script>";
	}
}
?>