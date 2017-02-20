<?php
//REMOVE BELOW IN PPRODUCTION
ini_set('display_errors', true);
error_reporting(E_ALL);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
//REMOVE ABOVE IN PRODUCTION
class Connection {
	function createConnection() {
		include "/var/www/secure_includes/data.php";
		$mysqli = mysqli_connect($HOST, $USER, $PASSWORD, $DATABASE);
		return $mysqli;
	}
	function closeConnection() {
		mysqli_close();
	}
}
?>