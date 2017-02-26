<?php
include_once "includes/classes.php";
require_once "../vendor/autoload.php";
// remove below in production
ini_set('display_errors', true);
error_reporting(E_ALL);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
// remove above in production

$loader = new Twig_Loader_Filesystem("templates");
$twig = new Twig_Environment($loader);

$redditAuth = new RedditAuth();

if (isset($_GET["logout"])) {
	$logout = new Logout();
}

if (!isset($_GET["r"])) {
	$redditUrl = $redditAuth->getUrl();
	echo $twig->render("login.twig", array("name" => $name, "redditUrl" => $redditUrl));
} elseif ($_GET["r"] == "reddit") {
	$redditAuth->authorizeUser();
	echo $twig->render("auth.twig", array("name" => $name));
}
?>