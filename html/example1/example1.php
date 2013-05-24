<?php

/**
 * 這是 Page example1
 *
 * 這是一個 Page 的示範
 * @package page
 * @author Kaede
 */
class example1 {
	/**
	 * 顯示網頁
	 *
	 * 每個有頁面的 Page 都要有一個 init() 來產生視覺頁面
	 * @return string 網頁的HTML
	 */
	public function init() {
		return render('example1.html', array('vendors' => $this->getVendors()));
	}

	public function initDB() {
		$a = queryArray('SELECT * FROM point');
		foreach ($a as $p) {
			update('UPDATE point SET point_dict=? WHERE point_id=?', json_encode(array('zh-tw' => $p['point_name'], 'en-us' => 'Vendor' . $p['point_id'])), $p['point_id']);
		}
		$a = queryArray('SELECT * FROM product');
		foreach ($a as $p) {
			update('UPDATE product SET product_dict=? WHERE product_id=?', json_encode(array('zh-tw' => $p['product_name'], 'en-us' => 'Product' . $p['product_id'])), $p['product_id']);
		}
	}
	public function initSong() {
		mysql_select_db('test');
		for ($i = 1; $i <= 1000000; $i ++) {
			$type = rand(1, 100);
			insertRow('song', array(
				'song_id' => $i,
				'type_id' => $type,
				'song_name' => "Song $i",
				'expiry_date' => '2014/01/01 00:00:00'
			));
		}
	}
	public function initAlbum() {
		mysql_select_db('test');
		for ($i = 1; $i <= 100000; $i ++) {
			$type = rand(1, 100);
			insertRow('album', array(
				'album_id' => $i,
				'type_id' => $type,
				'album_name' => "Album $i",
				'expiry_date' => '2014/01/01 00:00:00'
			));
		}
	}
	public function initUser() {
		mysql_select_db('test');
		for ($i = 1; $i <= 100000; $i ++) {
			insertRow('user', array(
				'user_id' => $i,
				'user_name' => "User $i",
			));
		}
	}
	public function initTrans() {
		mysql_select_db('test');
		$ts = time();
		for ($i = 1; $i <= 100000; $i ++) {
			$ts += rand(0, 5);
			insertRow('trans', array(
				'user_id' => rand(1, 100000),
				'song_id' => rand(1, 100000),
				'ts' => date('Y/m/d H:i:s', $ts)
			));
		}
	}
	/**
	/**
	 * 查詢商家列表
	 *
	 * 這是一個 private function, 無法被當成API呼叫
	 * @return array 商家列表
	 */
	private function getVendors() {
		return queryArray('SELECT point_id AS vendor_id, point_name AS vendor_name, point_dict AS vendor_dict FROM point
			WHERE is_vendor=1 AND is_web=1 ORDER BY `rank` DESC');
	}

	/**
	 * 查詢特定商家的商品列表
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://rd.richi.com/example1/getProducts?vendor_id=4
	 * @global int vendor_id 廠商代碼
	 * @return array 產品列表
	 */
	public function getProducts() {
		$vendor_id = _REQ('vendor_id', '/^[0-9]+$/');
		return queryArray("SELECT product_id, product_name, product_dict, amount FROM product
			WHERE point_id=? AND start_ts<=NOW() AND end_ts>NOW() AND product_type='default'", $vendor_id);
	}
}
