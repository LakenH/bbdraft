<?php
include_once "includes/classes.php";
require_once "../vendor/autoload.php";


$loader = new Twig_Loader_Filesystem("templates");
$twig = new Twig_Environment($loader);

if (!isset($id)) {
	echo "<script>window.location.replace('login.php');</script>";
	exit("redirecting...");
}

$getData = new getData();
$league = $getData->getLeague();
echo $league;
if ($league == null) {
	echo $twig->render("joinleague.twig", array("name" => $name));
}



?>