<?php

/**
 * Page signin
 *
 * 商家註冊頁
 * @package page
 * @author Kaede
 */
class signin {
	/**
	 * 商家登入
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://rd.richi.com/signin/ok?email=kaede@richi.com&password=123456
	 * @global string email    Email
	 * @global string password 密碼
	 * @return array error, merchant: merchant_id, shop_id, clerk.id, point_id
	 */
	public function ok() {
		$email    = _REQ('email');
		$password = _REQ('password');

		$merchant = queryRow('SELECT m.merchant_id, s.shop_id, c.clerk_id, p.point_id
			FROM clerk c, shop s, merchant m, point p
			WHERE c.email=? AND c.hash=?
			AND c.shop_id=s.shop_id
			AND s.merchant_id=m.merchant_id
			AND p.merchant_id=m.merchant_id',
			$email, md5($password));

		if (!$merchant) {
			return array('error' => 1);
		}

		$_SESSION['merchant'] = $merchant;

		return array(
			'error'    => 0,
			'merchant' => $merchant,
		);
	}
}
