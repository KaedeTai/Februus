<?php
/**
 * Shared utilities
 * 
 * Lots of things TODO
 */

/**
 * 設定 Regular Expression 常用 Pattern
 */
define('RE_EMAIL',  '/^[0-9a-z._-]+@[0-9a-z_-]+(\.[0-9a-z_-]+)+$/i');
define('RE_DATE',   '/^(19|20)[0-9]{2}[\/-][01][0-9][\/-][0-3][0-9]$/');
define('RE_TIME',   '/^([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/');
define('RE_TS',     '/^(19|20)[0-9]{2}[\/-][01][0-9][\/-][0-3][0-9] ([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/');
define('RE_PHONE',  '/^[0-9-]+$/');
define('RE_IDNO',   '/^[A-Z][0-9]{9}$/');
define('RE_INT',    '/^(-?[1-9]|0)[0-9]*$/');
define('RE_NUM',    '/^[1-9][0-9]*$/');
define('RE_NUMSTR', '/^[0-9]+$/');
define('RE_01',     '/^(0|1)$/');
define('RE_STR',    '/.+/');

/**
 * 定義意外
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

//從Array取出一個Array
function _ARR($arr, $name, $errno = null, $errmsg = null)
{
	if (!is_array($arr)) {
		if (isset($errno)) {
			throw new ActionException($errno, $errmsg);
		}
		throw new RequestException(-99, serialize($arr) . ' is not an array');
	}
	if (!isset($arr[$name])) {
		if (isset($errno)) {
			throw new ActionException($errno, $errmsg);
		}
		throw new RequestException(-99, "Undefine variable $name");
	}
	if (is_array($arr[$name])) {
		return $arr[$name];
	}
	if (isset($errno)) {
		throw new ActionException($errno, $errmsg);
	}
	throw new RequestException(-99, "Pattern not matched $name $pattern");
}

//從Array取出一個值並檢查格式
function _VAL($arr, $name, $pattern = null, $errno = null, $errmsg = null)
{
	if (!is_array($arr)) {
		if (isset($errno)) {
			throw new ActionException($errno, $errmsg);
		}
		throw new RequestException(-99, serialize($arr) . ' is not an array');
	}
	if (!isset($arr[$name])) {
		if (is_null($pattern)) {
			return '';
		}
		if (isset($errno)) {
			throw new ActionException($errno, $errmsg);
		}
		throw new RequestException(-99, "Undefine variable $name");
	}
	if (is_null($pattern) || preg_match($pattern, $arr[$name])) {
		return $arr[$name];
	}
	if (isset($errno)) {
		throw new ActionException($errno, $errmsg);
	}
	throw new RequestException(-99, "Pattern not matched $name $pattern");
}

//從Array取出一系列值並檢查格式
function listVal($arr, $map, $errno = null, $errmsg = null)
{
	if (!is_array($arr)) {
		if (isset($errno)) {
			throw new ActionException($errno, $errmsg);
		}
		throw new RequestException(-99, serialize($arr) . ' is not an array');
	}
	$out = array();
	foreach ($map as $name => $pattern) {
		if (!isset($arr[$name])) {
			if (is_null($pattern)) {
				$out[] = '';
				continue;
			}
			if (isset($errno))
				throw new ActionException($errno, $errmsg);
			throw new RequestException(-99, "Undefine variable $name");
		}
		if (is_null($pattern) || preg_match($pattern, $arr[$name])) {
			$out[] = $arr[$name];
			continue;
		}
		if (isset($errno)) {
			throw new ActionException($errno, $errmsg);
		}
		throw new RequestException(-99, "Pattern not matched $name $pattern");
	}
	return $out;
}

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

function render($tmpl_file, $context) {
	require_once 'Mustache/Autoloader.php';
	Mustache_Autoloader::register();

	file_exists($tmpl_file) or die;
	$tmpl = file_get_contents($tmpl_file);
	$m = new Mustache_Engine;
	return $m->render($tmpl, $context);
}

