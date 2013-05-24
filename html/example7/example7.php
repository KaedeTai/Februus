<?php

class example7 {
	public function init() {
		session_start();
		if($_SESSION['shop_id'] != null && $_SESSION['email'] != null && $_SESSION['passwd'] != null ) {
			return render('example7.html');
		} else {
			return render('example7.html');
		}
		
	}
	
	public function login() {		
		$email = _REQ('email','/^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/');
		$passwd = _REQ('password');
		
		$result = null;
		$check = false;
		try {
			$result = queryRow('SELECT shop_id FROM shop WHERE email=? AND passwd=?',$email,$passwd);
			if ($result) {
				$check = true;
			}
		} catch (SQLException $e) {
			$check = false;
		}
		
		if($check) {
			session_start();
			$_SESSION['shop_id'] = $result['shop_id'];
			$_SESSION['email'] = $email;
			$_SESSION['passwd'] = $passwd;
		}
		
		return array('result' => $check);
	}
	
	public function createAccount() {

		$name = _REQ('name');
		$email = _REQ('email','/^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/');
		$passwd = _REQ('password');
		
		$result = null;
		$check = false;
		try {
			//$id = insert("INSERT INTO shop (shop_name,email,passwd) VALUES ('$name','$email','$password')");
			$map['shop_name'] = $name;
			$map['email'] = $email;
			$map['passwd'] = $passwd;
			$result = insertRow('shop',$map);
			if ($result) {
				$check = true;
			}
		} catch (SQLException $e) {
			$check = false;
		}
		
		if($check) {
			session_start();
			$_SESSION['shop_id'] = $result;
			$_SESSION['email'] = $email;
			$_SESSION['passwd'] = $passwd;
		}
		
		return array('result' => $check);
	}
	
	public function logout() {
		session_start();
		unset($_SESSION['shop_id']);
		unset($_SESSION['email']);
		unset($_SESSION['passwd']);
		return array('result' => true);
	}
	
	public function checkLogin() {
		session_start();
		if($_SESSION['shop_id'] != null && $_SESSION['email'] != null && $_SESSION['passwd'] != null ) {
			$sess_email = $_SESSION['email'];
			$sess_passwd = $_SESSION['passwd'];
			
			$result = null;
			$check = false;
			try {
				$result = queryRow('SELECT shop_id FROM shop WHERE email=? AND passwd=?',$sess_email,$sess_passwd);
				if ($result) {
					$check = true;
				}
			} catch (SQLException $e) {
				$check = false;
			}
			return array('result' => true);
		} else {
			return array('result' => false);
		}
	}
	
	public function getPromotions() {
		session_start();
		if($_SESSION['shop_id'] != null && $_SESSION['email'] != null && $_SESSION['passwd'] != null ) {
			$sess_id = $_SESSION['shop_id'];
			
			$result = null;
			try {
				$result = queryArray('SELECT promotion_id, name, point,amount, start_date,end_date FROM promotion WHERE shop_id=?',$sess_id);
			} catch (SQLException $e) {
				$result = null;
			}
			return $result;
		} else {
			return array('result' => false);
		}
	}
	
	public function getPromotionInfo() {
		session_start();
		if($_SESSION['shop_id'] != null && $_SESSION['email'] != null && $_SESSION['passwd'] != null ) {
			$promotion_id = _REQ('promotion_id');
				
			$result = null;
			try {
				$result = queryArray('SELECT name, point,amount, start_date,end_date FROM promotion WHERE promotion_id=?',$promotion_id);
			} catch (SQLException $e) {
				$result = null;
			}
			return $result;
		} else {
			return array('result' => false);
		}
	}
	
	public function addPromotion() {
		session_start();
		
		if($_SESSION['shop_id'] != null && $_SESSION['email'] != null && $_SESSION['passwd'] != null ) {
			
			$map['shop_id'] = $_SESSION['shop_id'];
			$map['name'] = _REQ('name');
			$map['point'] = _REQ('point');
			$map['amount'] = _REQ('amount');
			$map['start_date'] = _REQ('start_date');
			$map['end_date'] = _REQ('end_date');
			
			$check = false;
			try {
				$result = insertRow('promotion',$map);
				if ($result) {
					$check = true;
				}
			} catch (SQLException $e) {
				$check = false;
			}
			
			return array('result' => $check);
		} else {
			return array('result' => false);
		}
	}
	
	public function editPromotion() {
		session_start();
	
		if($_SESSION['shop_id'] != null && $_SESSION['email'] != null && $_SESSION['passwd'] != null ) {

			$promotion_id = _REQ('promotion_id');
			$map['shop_id'] = $_SESSION['shop_id'];
			$map['name'] = _REQ('name');
			$map['point'] = _REQ('point');
			$map['amount'] = _REQ('amount');
			$map['start_date'] = _REQ('start_date');
			$map['end_date'] = _REQ('end_date');

			$check = false;
			try {
				$result = updateRow('promotion',$map,'promotion_id="'.$promotion_id.'"');
				if ($result) {
					$check = true;
				}
			} catch (SQLException $e) {
				$check = false;
			}
			
			return array('result' => $check);
		} else {
			return array('result' => false);
		}
	}
	
	private function getVendors() {
		return queryArray('SELECT point_id AS vendor_id, point_name as vendor_name FROM point
				WHERE is_vendor=1 AND is_web=1 ORDER BY `rank` DESC');
	}

	public function getProducts() {
		$vendor_id = _REQ('vendor_id', '/^[0-9]+$/');
		return queryArray("SELECT product_id, product_name, amount FROM product
				WHERE point_id=? AND start_ts<=NOW() AND end_ts>NOW() AND product_type='default'", $vendor_id);
	}
}
?>
