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
if (isset($_POST["createLeague"])) {
		$leagueTasks = new LeagueTasks();
		$message = $leagueTasks->createPrivate();
		echo $twig->render("createleague.twig", array("name" => $name, "message" => $message)); 
		die();
} 
if ($league == null) {
	echo $twig->render("createleague.twig", array("name" => $name));
} else {
	$leagueOwner = $getData->getLeagueOwner();
	
	if ($leagueOwner == $id) {
		$leagueName = $getData->getLeagueName();
		echo $twig->render("league.twig", array("name" => $name, "leagueCode" => $league, "leagueName" => $leagueName));
	}
}
?>