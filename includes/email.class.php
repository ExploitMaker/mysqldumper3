<?php
require_once("configuration.php");

class email {
	public function send($email, $content) {
		global $version;
		global $username;
		global $host;
		$emailtimestamp = date("D M d H:i:s Y");
		$subject = "MySQL Dumper $version: Dump Notification";
		$bodystart = "<h1>MySQL Dumper $version</h1>";
		$bodyend = "<br>-MySQL Dumper<br><br>Email Timestamp: [$emailtimestamp]";
		$body = $bodystart.$content.$bodyend;
		$headers = "From:$username@$host \r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		$explode = explode(",", $email);
		if(count($explode) == 1) {
			mail($email, $subject, $body, $headers); 
		} else {
			foreach($explode as $emails) {
				mail(trim($emails), $subject, $body, $headers);
			}
		}
	}
}
?>