<?php

require '../config.php';
require 'mysql.php';
require 'util.php';
require 'phptop.php';

#echo $_SERVER['REQUEST_URI'];exit;
$uri = explode('?', $_SERVER['REQUEST_URI'], 2);
$path = explode('/', $uri[0]);
if (substr($uri[0], -1) == '/') {
	$action = 'init';
	$dir = substr($uri[0], 1, -1);
}
else {
	$action = $path[count($path) - 1];
	$dir = substr($uri[0], 1, -1 - strlen($action));
}
if (is_dir($dir)) {
	chdir($dir);
	$module = $path[count($path) - 2];
}
else if ($dir == '') {
	chdir('home');
	$module = 'home';
}
else {
	home();
}

file_exists("$module.php") or home();
require "$module.php";
class_exists($module) or home();
is_callable("$module::$action") or home();

$m = new $module;
try {
	session_start();
	$r = $m->$action();
}
catch (Exception $e) {
	return json_encode(array(
		'error'   => $e->getCode(),
		'message' => $e->getMessage(),
		'trace'   => $e->getTrace(),
	));
}
echo is_array($r)? json_encode($r): $r;

function home() {
#	header('Location: /');
#	echo '<script>location.href="/";</script>';
	exit;
}

