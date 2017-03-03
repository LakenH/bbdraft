<?php
include_once "includes/classes.php";
require_once "../vendor/autoload.php";

//REMOVE BELOW IN PPRODUCTION
ini_set('display_errors', true);
error_reporting(E_ALL);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
//REMOVE ABOVE IN PRODUCTION

$loader = new Twig_Loader_Filesystem("templates");
$twig = new Twig_Environment($loader);

if (!isset($id)) {
	echo "<script>window.location.replace('login.php');</script>";
	exit("redirecting...");
}

$getData = new getData();
$league = $getData->getLeague();
if ($league == null) {
	echo $twig->render("createleague.twig", array("name" => $name));
	
	if (isset($_POST["createLeague"])) {
		$leagueTasks = new LeagueTasks();
		$leagueTasks->createPrivate();
	} 
} else {
	echo "you have a league";
}
?>