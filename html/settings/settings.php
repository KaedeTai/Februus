<?php

/**
 * Page signup
 *
 * 商家註冊頁
 * @package page
 * @author Kaede
 */
class settings {
	
	/**
	 * 取得廠商資訊
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://rd.richi.com/settings/info
	 * @return array error, email, name, address
	 */
	
	public function info() {
		//沒有登入無法使用
		if (!isset($_SESSION['merchant'])) {
			return array('error' => -1);
		}

		//取得參數
		$merchant_id = $_SESSION['merchant']['merchant_id'];
		$clerk_id    = $_SESSION['merchant']['clerk_id'];
		
		//產生查詢SQL
		$merchant_sql = 'SELECT address FROM merchant WHERE merchant_id=?';
		$clerk_sql = 'SELECT name, email FROM clerk WHERE clerk_id=?';
		
		
		$result = queryRow($merchant_sql, $merchant_id);
		$address = $result['address']; 
		
		$result = queryRow($clerk_sql, $clerk_id);
		$name = $result['name'];
		$email = $result['email'];
		
		return array(
			'error'    => 0,
			'name' 	   => $name,
			'email'    => $email,
			'address'    => $address,
		);
	}
	
	/**
	 * 更新資料
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://rd.richi.com/settings/update?email=kaede@richi.com&name=里斯特&address=台北市光復南路102號14F里斯特&password=123456
	 * @global string email    Email
	 * @global string name     商店名稱
	 * @global string address  商店地址
	 * @global string password 密碼
	 * @return array error, merchant: merchant_id, shop_id, clerk.id, point_id
	 */
	public function update() {
		
		//沒有登入無法使用
		if (!isset($_SESSION['merchant'])) {
			return array('error' => -1);
		}
		
		//取得參數
		$merchant_id = $_SESSION['merchant']['merchant_id'];
		$clerk_id = $_SESSION['merchant']['clerk_id'];
		$point_id = $_SESSION['merchant']['point_id'];
		$shop_id = $_SESSION['merchant']['shop_id'];
		$email     = _REQ('email', '/^[0-9a-z_.-]+@[0-9a-z_.-]+\.[0-9a-z_.-]+$/i');
		$name      = _REQ('name', '/.+/');
		$address   = _REQ('address', '/.+/');
		$password  = _REQ('password', '/.{6}/');

		$merchant_id = updateRow('merchant', array(
			'name'        => $name,
			'address'     => $address,
		), 'merchant_id='.$merchant_id );
		
		$shop_id = updateRow('shop', array(
				'name'        => $name,
				'address'     => $address,
		), 'shop_id='.$shop_id );
		
		//XXX 暫時使用 ######## 當做無更改密碼
		$map;
		if ($password == '########') {
			$map = array(
				'name'        => $name,
				'email'       => $email,
			);
		} else {
			$map = array(
				'name'        => $name,
				'email'       => $email,
				'hash'        => md5($password),
			);
		}
		
		$clerk_id = updateRow('clerk', $map, 'clerk_id='.$clerk_id);

		$point_id = updateRow('point', array(
			'name'        => $name
		), 'point_id='.$point_id);

		return array(
			'error'    => 0
		);
	}
}
