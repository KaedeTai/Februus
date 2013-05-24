<?php

class example8 {
	public function init() {
		return render('example8.html', array('vendors' => $this->getVendors()));
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
