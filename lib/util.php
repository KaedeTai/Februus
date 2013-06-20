<?php
/**
 * Shared utilities
 * 
 * Lots of things TODO
 */

class FatalException extends Exception {
	public function __construct($message = 'Fatal', $error = -99)
	{
		parent::__construct($message, $error);
		file_put_contents(LOG_PATH . '/fatal.log', date('Y-m-d H:i:s') . "\t$error\t$message\n", FILE_APPEND);
	}
}

class SQLException extends Exception {
	public function __construct($message = 'SQL', $error = -99)
	{
		parent::__construct($message, $error);
		file_put_contents(LOG_PATH . '/sql.log', date('Y-m-d H:i:s') . "\t$error\t$message\n", FILE_APPEND);
	}
}

class NetworkException extends Exception 
{
	protected $msg;
	public function __construct($error = -99, $message = '系統忙碌中請稍後再試')
	{
		$this->msg = $message;
		parent::__construct($message, $error);
		file_put_contents(LOG_PATH . '/network.log', date('Y-m-d H:i:s') . "\t$error\t$message\n", FILE_APPEND);
	}
	public function getMsg()
	{
		return $this->msg;
	}
}

class RequestException extends Exception 
{
	protected $msg;
	public function __construct($error = -99, $message = '系統忙碌中請稍後再試')
	{
		$this->msg = $message;
		parent::__construct($message, -99);
		file_put_contents(LOG_PATH . '/req.log', date('Y-m-d H:i:s') . "\t$error\t$message\n", FILE_APPEND);
	}
	public function getMsg()
	{
		return $this->msg;
	}
}

class ActionException extends Exception
{
	protected $msg;
	public function __construct($error = -99, $message = '系統忙碌中請稍後再試')
	{
		$this->msg = $message;
		parent::__construct($message, $error);
		file_put_contents(LOG_PATH . '/act.log', date('Y-m-d H:i:s') . "\t$error\t$message\n", FILE_APPEND);
	}
	public function getMsg()
	{
		return $this->msg;
	}
}
class AlertException extends Exception
{
	protected $msg;
	public function __construct($error = -99, $message = '系統忙碌中請稍後再試')
	{
		$this->msg = $message;
		parent::__construct($message, $error);
		file_put_contents(LOG_PATH . 'alert.log', date('Y-m-d H:i:s') . "\t$error\t$message\n", FILE_APPEND);
		sendMimeMail('david@richi.com,sam@richi.com,lavenderchang@richi.com,kc@richi.com,regretless@richi.com,yenkuan@richi.com,kaede@richi.com,beiyi@richi.com,jamie.lin@richi.com,powerlilian@richi.com', '紅色警戒！有商品沒送出！！！', $message, $message, 'Richi Inc. <email@richimail.com>');
	}
	public function getMsg()
	{
		return $this->msg;
	}
	
}
/*
function succeed($result = '')
{
	echo json_encode(array('error' => 0, 'message' => '成功', 'result' => $result), JSON_FORCE_OBJECT);
	exit;
}
*/

//從$_REQUEST取出值並驗證格式
function _REQ($name, $pattern = null, $errno = null, $errmsg = null)
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

//從$_GET取出值並驗證格式
function _GET($name, $pattern = null, $errno = null, $errmsg = null)
{
	if (!isset($_GET[$name]))
	{
		if (!isset($pattern))
			return '';
		if (isset($errno))
			throw new ActionException($errno, $errmsg);
		throw new RequestException(-99, "Undefine variable $name");
	}
	if (!isset($pattern) || preg_match($pattern, $_GET[$name]))
		return $_GET[$name];
	if (isset($errno))
		throw new ActionException($errno, $errmsg);
	throw new RequestException(-99, "Pattern not matched $name $pattern");
}

//從$_POST取出值並驗證格式
function _POST($name, $pattern = null, $errno = null, $errmsg = null)
{
	if (!isset($_POST[$name]))
	{
		if (!isset($pattern))
			return '';
		if (isset($errno))
			throw new ActionException($errno, $errmsg);
		throw new RequestException(-99, "Undefine variable $name");
	}
	if (!isset($pattern) || preg_match($pattern, $_POST[$name]))
		return $_POST[$name];
	if (isset($errno))
		throw new ActionException($errno, $errmsg);
	throw new RequestException(-99, "Pattern not matched $name $pattern");
}

//組成xml or json
/*
function respond($arr)
{
//	if (isset($_REQUEST['is_json']) && $_REQUEST['is_json'] == 1)
//		echo json_encode($arr, JSON_FORCE_OBJECT);
//	else
//		echo arr2xml($arr);
	echo json_encode($arr);
	exit;
}
*/

//組成xml
function arr2xml($arr, $root = 'xml')
{
	$xml = "<$root>";
	foreach ($arr as $key => $value)
	{
		if (is_numeric($key))
			$key = 'item';
		if (is_array($value))
			$xml .= arr2xml($value, $key);
		else
			$xml .= "<$key>" . htmlspecialchars($value) . "</$key>";
	}
	$xml .= "</$root>";
	return $xml;
}

//取得XML中某個key的值
function xmlValue($xml, $name, $default = '')
{
	if (preg_match("/<$name>([^<]+)</", $xml, $a))
				return urldecode($a[1]);
	return $default;
}

function mysql_begin()
{
	mysql_query('BEGIN');
}

function mysql_commit()
{
	mysql_query('COMMIT');
}

function mysql_rollback()
{
	mysql_query('ROLLBACK');
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
	if ($a = mysql_fetch_array($rs))
	{
		$len = count($a);
		for ($i = 0; $i < $len / 2; $i ++) {
			unset($a[$i]);
		}
		return $a;
	}
	return false;
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
	while ($a = mysql_fetch_array($rs))
	{
		$len = count($a);
		for ($i = 0; $i < $len / 2; $i ++) {
			unset($a[$i]);
		}
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
	while ($a = mysql_fetch_array($rs))
	{
		$len = count($a);
		for ($i = 0; $i < $len / 2; $i ++) {
			unset($a[$i]);
		}
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

	$rs = mysql_query("SELECT $fields FROM `$table` WHERE $where LIMIT 1");
	$err = mysql_error();
	if ($err != '') {
		throw new SQLException("Error: $err. SQL: $sql");
	}
	if ($a = mysql_fetch_array($rs)) {
		$len = count($a);
		for ($i = 0; $i < $len / 2; $i ++) {
			unset($a[$i]);
		}
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

//寄信
function sendMail($email, $title, $text, $from = 'Richi Inc. <email@richimail.com>')
{
	$subject = u2pq($title);
	$encoded = base64_encode($text);

	$headers = "From: $from\nMIME-Version: 1.0\nContent-Type: text/plain; charset=\"utf-8\"\nContent-Transfer-Encoding: base64";

	return mail($email, $subject, $encoded, $headers);
}

//寄信
function sendMimeMail($email, $title, $text, $html, $from = 'Richi Inc. <email@richimail.com>')
{
	$subject = u2pq($title);
	$html64 = base64_encode($html);
	$message = chunk_split(base64_encode($text));

	$boundary = "==Multipart_Boundary_" . md5(uniqid()); 
	$boundary2 = "==Multipart_Boundary_" . md5(uniqid()); 
	$headers = "From: $from\nMIME-Version: 1.0\nContent-Type: multipart/mixed; boundary=\"{$boundary}\"";
	$message = "This is a multi-part message in MIME format

--{$boundary}
Content-Type: multipart/alternative; boundary=\"{$boundary2}\"

--{$boundary2}
Content-Type: text/plain; charset=\"utf-8\"
Content-Transfer-Encoding: base64

{$message}

--{$boundary2}
Content-Type: text/html; charset=\"utf-8\"
Content-Transfer-Encoding: base64

{$html64}

--{$boundary2}--
--{$boundary}--
"; 

	return mail($email, $subject, $message, $headers);
}

//MIME-Mail的文字編碼
function u2pq($ustr)
{
	return "=?utf-8?B?" . base64_encode($ustr) . "?=";
}

function now()
{
	return date('Y-m-d H:i:s');
}

function getSerialNo($length = 6, $chars = '3456789ABCDFGHJKLMNPQRSTVWXY')
{
	$sn = '';
	for ($i = 0; $i < $length; $i ++)
		$sn .= substr($chars, rand(0, strlen($chars) - 1), 1);
	return $sn;
}

function post($url, $post = "", $useragent = "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_3_3 like Mac OS X; zh-tw) AppleWebKit/533.17.9 (KHTML, like Gecko) Mobile/8J3")
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		"Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
		"User-Agent: $useragent",
		"Connection: keep-alive"
	));
	$res = curl_exec($ch);

	curl_close($ch);
	return $res;
}

//get remote address
function getRemoteAddr()
{
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	if (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
		return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
	return $_SERVER['REMOTE_ADDR'];
}

function jsonp($object)
{
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type: application/json');

	if(isset($_GET['callback']))
		echo $_GET['callback']."(";
	echo json_encode($object);
	if(isset($_GET['callback']))
		echo ");";
	exit;
}

/**
 * 驗證權限
 *
 * 這個 function, 僅用來檢查權限，輸入格式為『陣列』或『JSON』,
 * 例： 使用者權限設定為 $data = {'account':'1', 'debit':'5', 'topup':'7'}
 * 某按鈕或頁面設定為 canDo($data,'account', 'a') ，輸出為false則該使用者無新增權限。
 * canDo($data,'account', 'r')，輸出為ture，該使用者有讀取權限
 *
 * @author sam
 *
 * @param string $data	- 傳遞資料
 * @param string $key	- 任意定義字串
 * @param string $act	- 檢查權限等級『 r：讀取(1), a：新增(3), e：編輯(4)』
 *
 * @return bool
 */
function canDo($data, $key, $act="")
{
	//檢查資料格式是否為符和
	if(!(is_string($data) || is_array($data))) {
		return false;
	}

	//檢查是否為Array或JSON
	if (!is_array($data)) {
		$arr = @json_decode($data,true);
		if (!is_array($arr)) {
			return false;
		}
	}
	else {
		$arr = $data;
	}

	//檢查第一階段權限
	if ($act === "") {
		return isset($arr[$key]);
	}

	if (!isset($arr[$key])) {
		return false;
	}

	//驗證細部權限
	$permission = $arr[$key];

	$chmod['r'] = ($permission%2 === 1);
	$chmod['a'] = ($permission === 2 || $permission === 3 || $permission === 6 || $permission === 7);
	$chmod['e'] = ($permission >= 4);

	return  isset($chmod[$act]) ? $chmod[$act] : false;
}

require_once 'Mustache/Autoloader.php';
Mustache_Autoloader::register();

function render($tmpl_file, $context) {
	file_exists($tmpl_file) or die;
	$tmpl = file_get_contents($tmpl_file);
	$m = new Mustache_Engine;
	return $m->render($tmpl, $context);
}

