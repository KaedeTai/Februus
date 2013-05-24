<?php

/**
 * Page debit
 *
 * 扣點
 * @package page
 * @author sam
 */
class debit {
	/**
	 * 取得帳號資訊
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://rd.richi.com/account/info
	 * @global string account_id 帳號代碼
	 * @return array error, name, phone, email
	 */
	public function info() {
		//沒有登入無法使用
		if (!isset($_SESSION['merchant'])) {
			return array('error' => -1);
		}
		//取得參數
		$point_id   = $_SESSION['merchant']['point_id'];
		$account_id = _REQ('account_id');
	
		//確認帳號
		$account = queryRow('SELECT * FROM account WHERE account_id=? AND point_id=?', $account_id, $point_id);
		if (!$account) {
			return array('error' => 1, 'message' => '沒有這個帳號');
		}
		
		return array(
				'error'   => 0,
				'account' => $account,
		);
	}
	
	/**
	 * 增加一筆扣款記錄
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://rd.richi.com/debit/add?account_id=1&amount=100
	 * @global string account_id 帳號代碼
	 * @global string amount 交易金額
	 * @return array error, amount, message
	 */
	public function add() {
		//沒有登入無法使用
		
		if (!isset($_SESSION['merchant'])) {
			return array('error' => -1);
		}
		
		//取得參數
		$point_id   = $_SESSION['merchant']['point_id'];
		$clerk_id   = $_SESSION['merchant']['clerk_id'];
		$account_id = _REQ('account_id', '/^[0-9]+$/');
		$amount		= _REQ('amount', '/^(-?\d+)(\.\d+)?$/');
		
		//檢查金額格式是否正確
		if (!$amount || $amount <= 0) {
			return array('error' => 1, 'message' => '金額格式不正確', 'amount' => $amount);
		}
		
		//確認帳號
		$account = queryRow('SELECT * FROM account WHERE account_id=? AND point_id=?', $account_id, $point_id);
		if (!$account) {
			return array('error' => 1, 'message' => '沒有這個帳號');
		}

		//查詢餘額是否足夠
		if ($account['balance'] <  $amount) {
			return array('error' => 1, 'message' => '餘額不足');
		}
		
		try {
			mysql_begin();
			//新增扣款記錄
			$data['account_id'] = $account_id;
			$data['clerk_id'] = $clerk_id;
			$data['type'] = '-1'; //1:儲值,-1:扣點
			$data['amount'] = $amount;
		
			$id = insertRow('trans', $data);
			
			if (!$id) {
				mysql_rollback();
				return array('error' => 1, 'message' => '扣點失敗');
			}
			
			$strSQL = 'UPDATE account SET balance = balance - ? , trans= trans + 1  WHERE account_id = ?';
			if (!update($strSQL, $amount, $account_id)) {
				mysql_rollback();
				return array('error' => 2, 'message' => '扣點失敗');
			}
			
			mysql_commit();
		}
		catch (Exception $e) {
			mysql_rollback();
			return array('error' => 3, 'message' => '扣點失敗');
		}
		
		return array('error'=> 0, 'amount' => $amount);
	}
}
