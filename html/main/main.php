<?php

/**
 * Page main
 *
 * 商家註冊頁
 * @package page
 * @author Kaede
 */
class main {
	/**
	 * 取得帳號列表
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://rd.richi.com/main/getAccounts
	 * @global string start 從第幾筆開始讀取, 第一筆是 0, 不設代表從頭
	 * @global string count 總共要讀幾筆, 不設代表全部
	 * @global string key 搜尋關鍵字
	 * @return array error, accounts, total
	 */
	public function accounts() {
		//沒有登入無法使用
		if (!isset($_SESSION['merchant'])) {
			return array('error' => -1);
		}

		//取得參數
		$point_id = $_SESSION['merchant']['point_id'];
		$start = _REQ('start');
		$count = _REQ('count');
		$key   = _REQ('key');

		//產生查詢SQL
		$count_sql = 'SELECT COUNT(*) AS total FROM account WHERE point_id=?';
		$query_sql = 'SELECT account_id, name, phone, email, balance, ts FROM account WHERE point_id=?';
		if ($key != '') {
			$key = mysql_real_escape_string($key);
			$where = " AND (name LIKE '%$key%' OR phone LIKE '%$key%' OR email LIKE '%$key%')";
			$count_sql .= $where;
			$query_sql .= $where;
		}
		$query_sql .= ' ORDER BY ts DESC';
		if (is_numeric($count) && $count >= 0) {
			if (is_numeric($start) && $start >= 0) {
				$query_sql .= " LIMIT $start,$count";
			}
			else {
				$query_sql .= " LIMIT $count";
			}
		}

		//查詢資料
		$accounts = queryArray($query_sql, $point_id);
		$a = queryRow($count_sql, $point_id);
		$total = $a['total'];
		
		return array(
			'error'    => 0,
			'accounts' => $accounts,
			'total'    => $total,
		);
	}
}
