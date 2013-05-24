<?php

/**
 * Page signup
 *
 * 商家註冊頁
 * @package page
 * @author Kaede
 */
class signup {
	/**
	 * 註冊新商家
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://rd.richi.com/signup/ok?email=kaede@richi.com&name=里斯特&address=台北市光復南路102號14F里斯特&password=123456
	 * @global string email    Email
	 * @global string name     商店名稱
	 * @global string address  商店地址
	 * @global string password 密碼
	 * @return array error, merchant: merchant_id, shop_id, clerk.id, point_id
	 */
	public function ok() {
		$email     = _REQ('email', '/^[0-9a-z_.-]+@[0-9a-z_.-]+\.[0-9a-z_.-]+$/i');
		$name      = _REQ('name', '/.+/');
		$address   = _REQ('address', '/.+/');
		$password  = _REQ('password', '/.{6}/');

		$merchant_id = insertRow('merchant', array(
			'name'        => $name,
			'address'     => $address,
		));

		$shop_id = insertRow('shop', array(
			'merchant_id' => $merchant_id,
			'name'        => $name,
			'address'     => $address,
		));

		$clerk_id = insertRow('clerk', array(
			'shop_id'     => $shop_id,
			'name'        => $name,
			'email'       => $email,
			'hash'        => md5($password),
		));

		$point_id = insertRow('point', array(
			'merchant_id' => $merchant_id,
			'name'        => $name,
			'unit'        => 1,
			'rate'        => 1,
		));

		$merchant = array(
			'merchant_id' => $merchant_id,
			'shop_id'     => $shop_id,
			'clerk_id'    => $clerk_id,
			'point_id'    => $point_id,
		);
		
		$_SESSION['merchant'] = $merchant;

		return array(
			'error'    => 0,
			'merchant' => $merchant,
		);
	}
}
