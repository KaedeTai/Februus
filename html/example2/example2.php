<?php

/**
 * 這是 Page example2
 *
 * 這是一個 Page 的示範
 * @package page
 * @author Kaede
 */
class example2 {
	/**
	 * 顯示網頁
	 *
	 * 每個有頁面的 Page 都要有一個 init() 來產生視覺頁面
	 * @return string 網頁的HTML
	 */
	public function init() {
		return render('example2.html', array('vendors' => $this->getVendors()));
	}

	/**
	 * 查詢商家列表
	 *
	 * 這是一個 private function, 無法被當成API呼叫
	 * @return array 商家列表
	 */
	private function getVendors() {
		return queryArray('SELECT point_id AS vendor_id, point_name as vendor_name FROM point
			WHERE is_vendor=1 AND is_web=1 ORDER BY `rank` DESC');
	}

	/**
	 * 查詢特定商家的商品列表
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://rd.richi.com/example2/getProducts?vendor_id=4
	 * @global int vendor_id 廠商代碼
	 * @return array 產品列表
	 */
	public function getProducts() {
		$vendor_id = _REQ('vendor_id', '/^[0-9]+$/');
		return queryArray("SELECT product_id, product_name, amount FROM product
			WHERE point_id=? AND start_ts<=NOW() AND end_ts>NOW() AND product_type='default'", $vendor_id);
	}
}
