<?php

require_once 'config.php';
require_once 'util.php';

file_put_contents(LOG_PATH . '/access.log', date('Y-m-d H:i:s') . "\t{$_SERVER['REQUEST_URI']}\n", FILE_APPEND);
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
	mysql_rollback();
	jsonp(array(
		'error'   => $e->getCode(),
		'message' => $e->getMessage(),
	));
	exit;
}
catch (ActionException $e) {
	mysql_rollback();
	jsonp(array(
		'error'   => $e->getCode(),
		'message' => $e->getMessage(),
	));
	exit;
}
catch (Exception $e) {
	mysql_rollback();
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

//回首頁
function home() {
#	header('Location: /');
#	echo '<script>location.href="/";</script>';
	exit;
}

/**
 * Febric
 * @author Kaede
 * @package Febric
 */

class Febric {
	function __construct() {
		mysql_pconnect(DB_HOST, DB_USER, DB_PASS);
		mysql_select_db(DB_NAME);
		mysql_query('set names utf8');
		mysql_query("set time_zone = 'Asia/Taipei'");
	}
}

/**
 * 從$_REQUEST取出值並驗證格式, 同 _REQ()
 * @param string $name    GET/POST 變數名稱
 * @param string $pattern 驗證規則(Regular Expression), 非必要
 * @param string $errno   驗證失敗之錯誤代碼
 * @param string $errmsg  驗證失敗之錯誤訊息
 * @return string GET/POST 變數值
 */
function get($name, $pattern = null, $errno = null, $errmsg = null)
{
	if (!isset($_REQUEST[$name]))
	{
		if (!isset($pattern))
			return '';
		if (isset($errno))
			throw new ActionException($errno, $errmsg);
		throw new RequestException(-99, "Undefine variable $name");
	}
	if (!isset($pattern) || preg_match($pattern, $_REQUEST[$name]))
		return $_REQUEST[$name];
	if (isset($errno))
		throw new ActionException($errno, $errmsg);
	throw new RequestException(-99, "Pattern not matched $name $pattern");
}

//MySQL執行一個query, 其中SQL的參數用問號表示, 然後把值用逗點帶入
//function query($sql, ...)
function query($sql)
{
	//Compose SQL
	$args = func_get_args();
	$a = explode('?', $sql);
	for ($i = 1; $i < count($args); $i ++) {
		$a[$i - 1] .= "'" . mysql_real_escape_string($args[$i]) . "'";
	}
	$sql = implode('', $a);

	$rs = mysql_query($sql);
	$err = mysql_error();
	if ($err != '') {
		throw new SQLException("Error: $err. SQL: $sql");
	}
	return $rs;
}

function insert($sql)
{
	//Compose SQL
	$args = func_get_args();
	$a = explode('?', $sql);
	for ($i = 1; $i < count($args); $i ++)
		$a[$i - 1] .= "'" . mysql_real_escape_string($args[$i]) . "'";
	$sql = implode('', $a);

	mysql_query($sql);
	$err = mysql_error();
	if ($err != '') {
		throw new SQLException("Error: $err. SQL: $sql");
	}
	if (mysql_affected_rows() == 0) {
		throw new SQLException("Insert failed: $sql");
	}
	$id = mysql_insert_id();
	if (!$id) {
		return true;
	}
	return $id;
}

function update($sql)
{
	//Compose SQL
	$args = func_get_args();
	$a = explode('?', $sql);
	for ($i = 1; $i < count($args); $i ++) {
		$a[$i - 1] .= "'" . mysql_real_escape_string($args[$i]) . "'";
	}
	$sql = implode('', $a);

	mysql_query($sql);
	$err = mysql_error();
	if ($err != '') {
		throw new SQLException("Error: $err. SQL: $sql");
	}
	return mysql_affected_rows();
}

//function queryCount($sql, ...)
function queryCount($sql)
{
	//Compose SQL
	$args = func_get_args();
	$a = explode('?', $sql);
	for ($i = 1; $i < count($args); $i ++) {
		$a[$i - 1] .= "'" . mysql_real_escape_string($args[$i]) . "'";
	}
	$sql = implode('', $a);

	$rs = mysql_query($sql);
	$err = mysql_error();
	if ($err != '') {
		throw new SQLException("Error: $err. SQL: $sql");
	}
	return mysql_result($rs, 0, 0);
}

//function queryRow($sql, ...)
function queryRow($sql)
{
	//Compose SQL
	$args = func_get_args();
	$a = explode('?', $sql);
	for ($i = 1; $i < count($args); $i ++) {
		$a[$i - 1] .= "'" . mysql_real_escape_string($args[$i]) . "'";
	}
	$sql = implode('', $a);

	$rs = mysql_query("$sql LIMIT 1");
	$err = mysql_error();
	if ($err != '') {
		throw new SQLException("Error: $err. SQL: $sql");
	}
	return mysql_fetch_assoc($rs);
}

//function queryColumn($sql, ...)
function queryColumn($sql)
{
	//Compose SQL
	$args = func_get_args();
	$a = explode('?', $sql);
	for ($i = 1; $i < count($args); $i ++) {
		$a[$i - 1] .= "'" . mysql_real_escape_string($args[$i]) . "'";
	}
	$sql = implode('', $a);

	$rs = mysql_query($sql);
	$err = mysql_error();
	if ($err != '') {
		throw new SQLException("Error: $err. SQL: $sql");
	}
	$arr = array();
	while ($row = mysql_fetch_row($rs)) {
		$arr[] = $row[0];
	}
	return $arr;
}

//function queryArray($sql, ...)
function queryArray($sql)
{
	//Compose SQL
	$args = func_get_args();
	$a = explode('?', $sql);
	for ($i = 1; $i < count($args); $i ++) {
		$a[$i - 1] .= "'" . mysql_real_escape_string($args[$i]) . "'";
	}
	$sql = implode('', $a);

	$rs = mysql_query($sql);
	$err = mysql_error();
	if ($err != '') {
		throw new SQLException("Error: $err. SQL: $sql");
	}
	$arr = array();
	while ($a = mysql_fetch_assoc($rs)) {
		$arr[] = $a;
	}
	return $arr;
}

//function queryHash($key, $sql, ...)
function queryHash($key, $sql)
{
	//Compose SQL
	$args = func_get_args();
	$a = explode('?', $sql);
	for ($i = 2; $i < count($args); $i ++) {
		$a[$i - 2] .= "'" . mysql_real_escape_string($args[$i]) . "'";
	}
	$sql = implode('', $a);

	$rs = mysql_query($sql);
	$err = mysql_error();
	if ($err != '') {
		throw new SQLException("Error: $err. SQL: $sql");
	}
	$arr = array();
	while ($a = mysql_fetch_assoc($rs)) {
		$arr[strval($a[$key])] = $a;
	}
	return $arr;
}

//取得一行資料
function getRow($table, $where, $fields = '*', $must = false)
{
	if (is_array($where)) {
		$a = array();
		foreach ($where as $field => $value) {
			$a[] = "`$field`='" . mysql_real_escape_string($value) . "'";
		}
		$where = implode(' AND ', $a);
	}
	if ($where == '') {
		throw new SQLException("\$where cannot be empty!");
	}

	$sql = "SELECT $fields FROM `$table` WHERE $where LIMIT 1";
	$rs = mysql_query($sql);
	$err = mysql_error();
	if ($err != '') {
		throw new SQLException("Error: $err. SQL: $sql");
	}
	if ($a = mysql_fetch_assoc($rs)) {
		return $a;
	}
	if ($must) {
		throw new SQLException("Select failed: $sql");
	}
	return false;
}

//新增一行資料
function addRow($table, $map, $replace = false)
{
	$fields = array();
	$values = array();
	foreach ($map as $field => $value) {
		$fields[] = $field;
		$values[] = mysql_real_escape_string($value);
	}
	$sql = ($replace? 'REPLACE': 'INSERT') . " INTO `$table` (`" . implode('`,`', $fields) . "`) VALUES ('" . implode("','", $values) . "')";
	mysql_query($sql);
	$err = mysql_error();
	if ($err != '') {
		throw new SQLException("Error: $err. SQL: $sql");
	}
	if (mysql_affected_rows() == 0) {
		throw new SQLException("Insert failed: $sql");
	}
	$id = mysql_insert_id();
	if (!$id) {
		return true;
	}
	return $id;
}

//設定一行資料
function setRow($table, $where, $set, $must = false)
{
	if (is_array($where)) {
		$a = array();
		foreach ($where as $field => $value) {
			$a[] = "`$field`='" . mysql_real_escape_string($value) . "'";
		}
		$where = implode(' AND ', $a);
	}
	if ($where == '') {
		throw new SQLException("\$where cannot be empty!");
	}
	if (is_array($set)) {
		$a = array();
		foreach ($set as $field => $value)
			$a[] = "`$field`='" . mysql_real_escape_string($value) . "'";
		$set = implode(',', $a);
	}
	if ($set == '') {
		throw new SQLException("\$set cannot be empty!");
	}

	mysql_query("UPDATE `$table` SET $set WHERE $where");
	$err = mysql_error();
	if ($err != '') {
		throw new SQLException("Error: $err. SQL: $sql");
	}
	$affected = mysql_affected_rows();
	if ($must && $affected == 0) {
		throw new SQLException("Update failed: $sql");
	}
	return $affected;
}

//設定一行資料
function delRow($table, $where, $must = false)
{
	if (is_array($where)) {
		$a = array();
		foreach ($where as $field => $value) {
			$a[] = "`$field`='" . mysql_real_escape_string($value) . "'";
		}
		$where = implode(' AND ', $a);
	}
	if ($where == '') {
		throw new SQLException("\$where cannot be empty!");
	}

	mysql_query("DELETE FROM `$table` WHERE $where");
	$err = mysql_error();
	if ($err != '') {
		throw new SQLException("Error: $err. SQL: $sql");
	}
	$affected = mysql_affected_rows();
	if ($must && $affected == 0) {
		throw new SQLException("Delete failed: $sql");
	}
	return $affected;
}
