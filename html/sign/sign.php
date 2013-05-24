<?php

/**
 * 簽名檔
 *
 * @package page
 * @author Kaede
 */
class sign {
	/**
	 * 顯示網頁
	 *
	 * 每個有頁面的 Page 都要有一個 init() 來產生視覺頁面
	 * @return string - 網頁的HTML
	 */
	public function init() {
		return render('sign.html', $this->getSign());
	}

	/**
	 * 取得簽名黨
	 *
	 * 這是一個 private function, 無法被當成API呼叫
	 * @return array - 簽名檔
	 */
	private function getSign() {
		return queryRow('SELECT * FROM sign ORDER BY `ts` DESC');
	}

	/**
	 * 儲存簽名檔
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://rd.richi.com/example1/getProducts?vendor_id=4
	 * @global string img - 圖檔
	 * @return int 
	 */
	public function addSign() {
		$img = _REQ('img');
		return insertRow('sign', array('img' => $img));
	}
}
