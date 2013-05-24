<?php

/**
 * Page topup
 *
 * 儲值頁
 * @package page
 * @author Kaede
 */
class topup {
	/**
	 * 取得帳號資訊
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://rd.richi.com/account/info
	 * @global string account_id 帳號代碼
	 * @global string amount 儲值金額
	 * @return array error
	 */
	public function info() {
		//沒有登入無法使用
		if (!isset($_SESSION['merchant'])) {
			return array('error' => -1);
		}

		//取得參數
		$point_id   = $_SESSION['merchant']['point_id'];
		$account_id = _REQ('account_id');
		$amount     = _REQ('amount', '/[0-9]+/');

		//確認帳號
		$account = queryRow('SELECT * FROM account WHERE account_id=? AND point_id=?', $account_id, $point_id);
		if (!$account) {
			return array('error' => 1, 'message' => '沒有這個帳號');
		}

		//儲值
		
		$trans = queryArray('SELECT * FROM trans WHERE account_id=? ORDER BY ts DESC', $account_id);
		
		return array(
			'error'   => 0,
			'account' => $account,
			'trans'   => $trans,
		);
	}
}
