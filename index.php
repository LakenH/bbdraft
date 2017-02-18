<?php
session_start([
    'cookie_lifetime' => 86400,
]);
error_reporting( E_ALL );
ini_set('display_errors', 1);
require_once '../vendor/autoload.php';

$name = null;

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader);

echo $twig->render('home.twig', array('name' => $name));

?>