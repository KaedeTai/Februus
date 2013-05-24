<?php

class example5 {
	public function init() {
		return render('example5.html', array('vendors' => $this->getVendors()));
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
	
	private function getVendors() {
		return queryArray('SELECT point_id AS vendor_id, point_name AS vendor_name, point_dict AS vendor_dict FROM point
			WHERE is_vendor=1 AND is_web=1 ORDER BY `rank` DESC');
	}

	public function getProducts() {
		$vendor_id = _REQ('vendor_id', '/^[0-9]+$/');
		return queryArray("SELECT product_id, product_name, product_dict, amount FROM product
			WHERE point_id=? AND start_ts<=NOW() AND end_ts>NOW() AND product_type='default'", $vendor_id);
	}
}
?>
