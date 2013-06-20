<?php

require '../config.php';
require 'util.php';
require 'febric.php';
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
	header('Access-Control-Allow-Origin: *');
	$r = $m->$action();
}
catch (RequestException $e) {
	//debug only
	jsonp(array(
		'error'   => $e->getCode(),
		'message' => $e->getMessage(),
	));
	exit;
}
catch (ActionException $e) {
	jsonp(array(
		'error'   => $e->getCode(),
		'message' => $e->getMessage(),
	));
	exit;
}
catch (Exception $e) {
	jsonp(array(
		'error'   => $e->getCode(),
		'message' => $e->getMessage(),
		'trace'   => $e->getTrace(),
	));
	exit;
}
if (is_array($r)) {
	$r['error'] = 0;
	$r['message'] = '成功';
	jsonp($r);
}
else {
	echo $r;
}

function home() {
#	header('Location: /');
#	echo '<script>location.href="/";</script>';
	exit;
}

