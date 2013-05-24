<?php

/**
 * 共用的 API 模組
 * @package page
 * @author Kaede
 */
class home {
	/**
	 * 查詢商品資訊
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://rd.richi.com/getProduct?product_id=4
	 * @global int product_id 商品代碼
	 * @return array 商品資訊
	 */
	public function getProduct() {
		$product_id = _REQ('product_id', '/^[0-9]+$/');
		return queryRow("SELECT product_id, product_name, amount, descr FROM product
			WHERE product_id=? AND start_ts<=NOW() AND end_ts>NOW() AND product_type='default'", $product_id);
	}
}
