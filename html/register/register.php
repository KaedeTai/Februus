<?php

/**
 * Page register
 *
 * 商家註冊頁
 * @package page
 * @author Kaede
 */
class register{
	/**
	 * 註冊新商家
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://rd.richi.com/signup/ok?email=kaede@richi.com&name=里斯特&address=台北市光復南路102號14F里斯特&password=123456
	 * @global string email    Email
	 * @global string name     姓名
	 * @global string phone    電話
	 * @global string password 密碼
	 * @return array error
	 */
	public function ok() {
		//沒有登入無法使用
		if (!isset($_SESSION['merchant'])) {
			return array('error' => -1);
		}
		//取得參數
		$point_id = $_SESSION['merchant']['point_id'];
		$email     = _REQ('email', '/^[0-9a-z_.-]+@[0-9a-z_.-]+\.[0-9a-z_.-]+$/i');
		$name      = _REQ('name', '/.+/');
		$phone     = _REQ('phone', '/.+/');
		
		$merchant_id = insertRow('account', array(
			'name'        => $name,
			'email'     	  => $email,
			'phone'     	  => $phone,
			'point_id'	  => $point_id,
			'login'		  => 1
		));
		
		return array(
			'error'    => 0
		);
	}
}
