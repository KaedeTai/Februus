<?php

/**
 * mwd API
 *
 * 麥味登App API
 * @package API
 * @author Kaede
 */
class mwd extends Febric {
	/**
	 * 設定環境
	 *
	 * 設定資料庫與時區等參數
	 */
	function __construct() {
		define('BONUS_ID', 92);
		define('BONUS_KEY', 'f6b5eb7f00f49ffcdf1ebd8f2df204a0a9d7b27e');
		define('BONUS_URL', 'https://qa.richi.com/api/external/');
		define('PREPAID_ID', 90);
		define('PREPAID_KEY', '1293e6c4fa2a3b1b9ecd899bb341a5742a1b38cb');
		define('PREPAID_URL', 'https://qa.richi.com/api/external/prepaid/');

		mysql_pconnect('rds.richi.com', 'mwd', 'mwd');
		mysql_select_db('mwd');
		mysql_query('set names utf8');
		mysql_query("set time_zone = 'Asia/Taipei'");
	}

	/**
	 * 會員登入(客戶App專用)
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://mwd.richi.com/mwd/login?phone=0912345678&birthday=1977-10-20
	 * @return object error, message, name, url, title, bonus, prepaid
	 */
	public function login() {
		$phone    = _REQ('phone',    RE_PHONE);
		$birthday = _REQ('birthday', RE_DATE);
	
		//取得認證
		$auth = getRow('auth', array('auth_type' => 'PHONE', 'auth_key' => $phone, 'passwd' => md5($phone . $birthday)));
		if (!$auth) {
			throw new ActionException(1, '登入失敗');
		}

		//取得卡片
		$card = getRow('card', array('card_id' => $auth['card_id']), 'card_id, user_id,name,url,birthday');
		if (!$card) {
			throw new ActionException(2, '無此卡號');
		}
	
		//取得剩餘點數
		$user = getUser($card['user_id']);
		$card['bonus']   = getBonus($user, $card['card_id']);
		$card['prepaid'] = getPrepaid($user, $card['card_id']);

		//將登入資訊寫入Session
		$_SESSION['card'] = $card;

		unset($card['card_id']);
		return $card;
	}

	/**
	 * 會員歷史紀錄(客戶App專用)
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://mwd.richi.com/mwd/getHistory?start=0&count=10
	 * @return object error, message, deals:[ts, deal_no, deal_type, shop_id, total, discount, bonus, prepaid, cash, bonus_left, prepaid_left], point_logs:[ts, point_type, amount]
	 */
	public function getHistory() {
		$card = getCard();
		$sql = 'SELECT ts, deal_no, deal_type, shop_id, total, discount, bonus, prepaid, cash, bonus_left, prepaid_left FROM deal WHERE user_id=? ORDER BY ts DESC';
		if (isset($_REQUEST['start']) && isset($_REQUEST['count'])) {
			$start = _REQ('start', RE_NUM);
			$count = _REQ('count', RE_NUM);
			$SQL .= " LIMIT $start, $count";
		}
		$deals = queryArray($sql, $card['user_id']);
		$point_logs = queryArray("SELECT last_ts AS ts, point_type, amount FROM point_log WHERE user_id=? AND error=0 AND reason='加點' ORDER BY last_ts DESC", $card['user_id']);
		return array('deals' => $deals, 'point_logs' => $point_logs);
	}

	/**
	 * 取得會員資訊(客戶App專用)
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://mwd.richi.com/mwd/getUserInfo
	 * @return object error, message, card_id, name, url, title, phone, birthday, bonus, prepaid
	 */
	public function getUserInfo() {
		$card = getCard();
		$user = getUser($card['user_id']);
		$card['bonus']   = getBonus($user, $card['card_id']);
		$card['prepaid'] = getPrepaid($user, $card['card_id']);
		return $card;
	}

	/**
	 * 取得菜單
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://mwd.richi.com/mwd/getMenu
	 * @return object error, message, kinds.kind_id: {kind_id, name, items: [item_id]}, items.item_id: {item_id, name, is_set, drink_type, price, set_price, drinks: [item_id]}
	 */
	public function getMenu() {
		//取得分類
		$kinds = queryHash('kind_id', 'SELECT kind_id, name FROM kind ORDER BY rank');
		foreach ($kinds as $kind_id => $kind) {
			$kinds[$kind_id]['items'] = queryColumn('SELECT item_id FROM item WHERE kind_id=? AND start_ts<=NOW() AND end_ts>NOW()', $kind_id);
		}

		//取得商品
		$items = queryHash('item_id', 'SELECT item_id, name, is_set, drink_type, price, set_price FROM item WHERE start_ts<=NOW() AND end_ts>NOW()');
		foreach ($items as $item_id => $item) {
			$items[$item_id]['drinks'] = queryColumn('SELECT s.drink_id FROM item_drink s LEFT JOIN item i ON s.item_id=i.item_id WHERE s.item_id=? AND start_ts<=NOW() AND end_ts>NOW()', $item_id);
		}

		return array('kinds' => $kinds, 'items' => $items);
	}

	/**
	 * 取得會員
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://mwd.richi.com/mwd/getUser?auth_type=NFC&auth_key=123
	 * @return object error, message, card_id, name, url, title, phone, birthday, bonus, prepaid
	 */
	public function getUser() {
		$auth_type = _REQ('auth_type', '/^(NFC|PHONE|QRCODE)$/');
		$auth_key  = _REQ('auth_key',  RE_STR);

		//取得認證
		$auth = getRow('auth', array('auth_type' => $auth_type, 'auth_key' => $auth_key));
		if (!$auth) {
			throw new ActionException(1, '認證失敗');
		}

		//取得卡片
		$card = getRow('card', array('card_id' => $auth['card_id']), 'card_id,user_id,name,url,title,phone,birthday');
		if (!$card) {
			throw new ActionException(2, '無此卡號');
		}

		//取得剩餘點數
		$user = getUser($card['user_id']);
		$card['bonus']   = getBonus($user, $card['card_id']);
		$card['prepaid'] = getPrepaid($user, $card['card_id']);
		return $card;
	}

	/**
	 * 加點紅利點數
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://mwd.richi.com/mwd/addBonus?card_id=1&amount=1
	 * @return object error, message, point
	 */
	public function addBonus() {
		$card_id = _REQ('card_id', RE_NUM);
		$amount  = _REQ('amount', RE_NUM);
		$card    = getRow('card', array('card_id' => $card_id));
		$user    = getUser($card['user_id']);
		$point   = addBonus($user, $amount, $card_id, 0, '加點');
		return array('point' => $point);
	}
	
	/**
	 * 扣點紅利點數
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://mwd.richi.com/mwd/deductBonus?card_id=1&amount=1
	 * @return object error, message, point
	 */
	public function deductBonus() {
		$card_id = _REQ('card_id', RE_NUM);
		$amount  = _REQ('amount', RE_NUM);
		$card    = getRow('card', array('card_id' => $card_id));
		$user    = getUser($card['user_id']);
		$point   = deductBonus($user, $amount, $card_id, 0, '扣點');
		return array('point' => $point);
	}
	
	/**
	 * 加點儲值點數
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://mwd.richi.com/mwd/addPrepaid?card_id=1&amount=1
	 * @return object error, message, point
	 */
	public function addPrepaid() {
		$card_id = _REQ('card_id', RE_NUM);
		$amount  = _REQ('amount', RE_NUM);
		$card    = getRow('card', array('card_id' => $card_id));
		$user    = getUser($card['user_id']);
		$point   = addPrepaid($user, $amount, $card_id, 0, '加點');
		return array('point' => $point);
	}
	
	/**
	 * 扣點儲值點數
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://mwd.richi.com/mwd/deductPrepaid?card_id=1&amount=1
	 * @return object error, message, point
	 */
	public function deductPrepaid() {
		$card_id = _REQ('card_id', RE_NUM);
		$amount  = _REQ('amount', RE_NUM);
		$card    = getRow('card', array('card_id' => $card_id));
		$user    = getUser($card['user_id']);
		$point   = deductPrepaid($user, $amount, $card_id, 0, '扣點');
		return array('point' => $point);
	}

	/**
	 * 交易試算
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://mwd.richi.com/mwd/calculate?card_id=1&order=[{"item_id":1,"drink_type":"非飲料","drink":{"item_id":11,"drink_type":"冷飲"}},{"item_id":12,"drink_type":"熱飲"}]
	 * @return object error, message, deal_no, total, discount, bonus, prepaid, bonus_rate, prepaid_rate, CASH/BONUS/PREPAID/BONUS_PREPAID/PREPAID_BONUS: {bonus, prepaid, cash}
	 */
	public function calculate() {
		//取得訂單資訊
		$card_id = _REQ('card_id', RE_NUM);
		$order = json_decode(_REQ('order'), true);
		if (!$order) {
			throw new ActionException(1, '無效的訂單');
		}

		//取得商品資訊
		$items = queryHash('item_id', 'SELECT * FROM item');
		$total = 0;
	
		//計算總價
		try {
			foreach ($order as $o) {
				$item = @$items[$o['item_id']];
				if (isset($o['drink'])) {
					$drink = @$items[$o['drink']['item_id']];
					$total += $item['set_price'] + $drink['set_price'];
				}
				else {
					$total += $item['price'];
				}
			}
		}
		catch (Exception $e) {
			throw new ActionException(1, '無效的訂單');
		}

		//計算折扣
		$card = getRow('card', array('card_id' => $card_id), '*', true);
		$user = getUser($card['user_id']);
		$discount = ($user['user_type'] == 'STAFF')? floor($total * 0.1): 0;

		//新增交易資料
		mysql_begin();

		$deal_no = getSerialNo(8);
		while (!($deal_id = addRow('deal', array(
			'deal_no'   => $deal_no,
			'user_id'   => $card['user_id'],
			'card_id'   => $card_id,
			'auth_id'   => 1, //TODO
			'shop_id'   => 1, //TODO
			'total'     => $total,
			'discount'  => $discount,
		)))) {
			$deal_no = getSerialNo(8);
		}

		//新增交易明細
		foreach ($order as $o) {
			$item = $items[$o['item_id']];
			if (isset($o['drink'])) {
				$drink  = @$items[$o['drink']['item_id']];
				$price  = $item['set_price'];
				$is_set = 1;
			}
			else {
				$drink  = false;
				$price  = $item['price'];
				$is_set = 0;
			}
			$detail_id = addRow('detail', array(
				'deal_id'    => $deal_id,
				'item_id'    => $o['item_id'],
				'is_set'     => $is_set,
				'price'      => $price,
				'drink_type' => $o['drink_type']
			));
		
			//新增付餐飲料
			if (is_array($drink)) {
				addRow('detail', array(
					'deal_id'    => $deal_id,
					'item_id'    => $drink['item_id'],
					'set_id'     => $detail_id,
					'is_set'     => 1,
					'price'      => $drink['set_price'],
					'drink_type' => $o['drink']['drink_type']
				));
			}
		}
		
		//完成資料新增
		mysql_commit();

		//計算方案
		$bonus   = getBonus($user, $card_id, $deal_id, '試算');
		$prepaid = getPrepaid($user, $card_id, $deal_id, '試算');
		$remain = $total - $discount;
		$br = 10;
		$pr = 1;
		$bv = floor($bonus / $br);
		$pv = floor($prepaid / $pr);
		$deal = array('deal_no' => $deal_no, 'total' => $total, 'discount' => $discount, 'bonus' => $bonus, 'prepaid' => $prepaid, 'bonus_rate' => $br, 'prepaid_rate' => $pr);

		//只用現金
		$deal['CASH'] = array('bonus' => 0, 'prepaid' => 0, 'cash' => $remain);

		//使用紅利點數
		$bu = ($bv < $remain)? $bv * $br: $remain * $br;
		$deal['BONUS'] = array('bonus' => $bu, 'prepaid' => 0, 'cash' => $remain - $bu / $br);

		//使用紅利點數
		$pu = ($pv < $remain)? $pv * $pr: $remain * $pr;
		$deal['PREPAID'] = array('bonus' => 0, 'prepaid' => $pu, 'cash' => $remain - $pu / $pr);

		//使用紅利點數加儲值點數
		$deal['BONUS_PREPAID'] = $deal['BONUS'];
		if ($bv < $remain) {
			$bu = $bv * $br;
			$left = $remain - $bu / $br;
			$pu = ($pv > $left)? $left * $pr: $pv * $pr;
			$deal['BONUS_PREPAID'] = array('bonus' => $bu, 'prepaid' => $pu, 'cash' => $left - $pu / $pr);
		}

		//使用儲值點數加紅利點數
		$deal['PREPAID_BONUS'] = $deal['PREPAID'];
		if ($pv < $remain) {
			$pu = $pv * $pr;
			$left = $remain - $pu / $pr;
			$bu = ($bv > $left)? $left * $br: $bv * $br;
			$deal['PREPAID_BONUS'] = array('bonus' => $bu, 'prepaid' => $pu, 'cash' => $left - $bu / $br);
		}

		//將試算結果存在Session
		$_SESSION['deal'] = $deal;

		return $deal;
	}

	/**
	 * 結帳
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://mwd.richi.com/mwd/checkout?card_no=1&deal_no=ABCDEFGH&deal_type=CASH
	 * @return object error, message, old_bonus, old_prepaid, new_bonus, new_prepaid, got_bonus
	 */
	public function checkout() {
		//取得訂單資訊
		$card_id   = _REQ('card_id', RE_NUM);
		$deal_no   = _REQ('deal_no', RE_STR);
		$deal_type = _REQ('deal_type', '/^(CASH|BONUS|PREPAID|BONUS_PREPAID|PREPAID_BONUS)$/');
		$card      = getRow('card', array('card_id' => $card_id));
		$user      = getUser($card['user_id']);
		$deal      = getRow('deal', array('deal_no' => $deal_no, 'card_id' => $card_id, 'state' => 0));

		//檢查訂單
		if (!$deal || !isset($_SESSION['deal']) || $deal_no != $_SESSION['deal']['deal_no']) {
			throw new ActionException(1, '方案資訊不存在');
		}
		$bonus   = $_SESSION['deal'][$deal_type]['bonus'];
		$prepaid = $_SESSION['deal'][$deal_type]['prepaid'];
		$cash    = $_SESSION['deal'][$deal_type]['cash'];
		$deal_id = $deal['deal_id'];
		
		//決定方案
		update('UPDATE deal SET deal_type=?, bonus=?, prepaid=?, cash=?, state=1 WHERE deal_no=?', $deal_type, $bonus, $prepaid, $cash, $deal_no);

		//檢查點數是否足夠
		$new_bonus   = $old_bonus   = getBonus($user, $card_id, $deal_id, '交易');
		$new_prepaid = $old_prepaid = getPrepaid($user, $card_id, $deal_id, '交易');
		if ($bonus > $old_bonus) {
			throw new ActionException(2, '紅利點數不足');
		}
		if ($prepaid > $old_prepaid) {
			throw new ActionException(3, '儲值點數不足');
		}

		//扣紅利點數
		if ($bonus > 0) {
			try {
				$new_bonus = deductBonus($user, $bonus, $card_id, $deal_id, '交易');
			}
			catch (Exception $e) {
				update('UPDATE deal SET state=-2 WHERE deal_id=?', $deal_id);
				throw new ActionException(4, '扣紅利點數失敗');
			}
			update('UPDATE deal SET bonus_left=?, state=2 WHERE deal_id=?', $new_bonus, $deal_id);
		}

		//扣儲值點樹
		if ($prepaid > 0) {
			try {
				$new_prepaid = deductPrepaid($user, $prepaid, $card_id, $deal_id, '交易');
			}
			catch (Exception $e) {
				if ($bonus > 0) {
					//退回紅利點數
					try {
						$new_bonus = addBonus($user, $bonus, $card_id, $deal_id, '交易失敗');
					}
					catch (Exception $e) {
						update('UPDATE deal SET state=-4 WHERE deal_id=?', $deal_id);
						throw new ActionException(5, '儲值扣點失敗, 紅利點數已扣且補回失敗');
					}
					update('UPDATE deal SET bonus_left=?, state=-5 WHERE deal_id=?', $new_bonus, $deal_id);
					throw new ActionException(6, '儲值扣點失敗, 紅利點數已補回');
				}
				update('UPDATE deal SET state=-3 WHERE deal_id=?', $deal_id);
				throw new ActionException(7, '儲值扣點失敗, 未扣除紅利點數');
			}
			update('UPDATE deal SET prepaid_left=?, state=3 WHERE deal_id=?', $new_prepaid, $deal_id);
		}

		//交易成功
		$got_bonus = floor(($prepaid + $cash) / 10);
		if ($got_bonus > 0) {
			//加紅利點數
			try {
				$new_bonus = addBonus($user, $got_bonus, $card_id, $deal_id, '交易');
				update('UPDATE deal SET bonus_left=?, prepaid_left=?, state=5 WHERE deal_id=?', $new_bonus, $new_prepaid, $deal_id);
			}
			catch (Exception $e) {
				//交易成功但紅利點數加點失敗
				update('UPDATE deal SET bonus_left=?, prepaid_left=?, state=4 WHERE deal_id=?', $new_bonus, $new_prepaid, $deal_id);
			}
		}
		else {
			update('UPDATE deal SET bonus_left=?, prepaid_left=?, state=5 WHERE deal_id=?', $new_bonus, $new_prepaid, $deal_id);
		}

		//清除交易暫存
		unset($_SESSION['deal']);

		return array(
			'old_bonus'   => $old_bonus,
			'old_prepaid' => $old_prepaid,
			'new_bonus'   => $new_bonus,
			'new_prepaid' => $new_prepaid,
			'got_bonus'   => $got_bonus
		);
	}
}

/**
 * 取得卡號
 *
 * 這是一個區域模組
 */
function getCard() {
	if (!isset($_SESSION['card'])) {
		throw new ActionException(-1, '尚未登入');
	}

	return $_SESSION['card'];
}

/**
 * 取得會員
 *
 * 這是一個區域模組
 */
function getUser($user_id) {
	$user = getRow('user', array('user_id' => $user_id));
	if (!$user) {
		throw new ActionException(1, '卡號錯誤');
	}
	return $user;
}

/**
 * 寫入點數API記錄
 *
 * 這是一個區域模組
 */
function pointLog($user_id, $card_id, $deal_id, $point_type, $amount, $reason, $req, $res, $tx_no, $error, $req_ts, $res_ts) {
	addRow('point_log', array(
		'user_id'    => $user_id,
		'card_id'    => $card_id,
		'shop_id'    => 1, //TODO
		'deal_id'    => $deal_id,
		'point_type' => $point_type,
		'amount'     => $amount,
		'reason'     => $reason,
		'req'        => $req,
		'res'        => $res,
		'tx_no'      => $tx_no,
		'error'      => $error,
		'req_ts'     => $req_ts,
		'res_ts'     => $res_ts,
	));
}

/**
 * 呼叫點數API
 *
 * 這是一個區域模組
 */
function callPoint($user_id, $card_id, $deal_id, $point_type, $amount, $reason, $req) {
	$error = 0;
	$req_ts = date('Y-m-d H:i:s');
	$res = @file_get_contents($req);
	$res_ts = date('Y-m-d H:i:s');
	$error = 0;
	if (!$res) {
		pointLog($user_id, $card_id, $deal_id, $point_type, $amount, $reason, $req, $res, '', -2, $req_ts, $res_ts);
		throw new ActionException(-2, '網路連線失敗');
	}
	$json = @json_decode($res, true);
	if (is_null($json)) {
		pointLog($user_id, $card_id, $deal_id, $point_type, $amount, $reason, $req, $res, '', -3, $req_ts, $res_ts);
		throw new ActionException(-3, '回應格式錯誤');
	}
	if (!isset($json['success']) || !$json['success']) {
		pointLog($user_id, $card_id, $deal_id, $point_type, $amount, $reason, $req, $res, '', -4, $req_ts, $res_ts);
		throw new ActionException(-4, 'API請求失敗');
	}
	$tx_no = isset($json['transaction_no'])? $json['transaction_no']: '';
	pointLog($user_id, $card_id, $deal_id, $point_type, $amount, $reason, $req, $res, $tx_no, $error, $req_ts, $res_ts);
	return $json;
}

/**
 * 取得Bonus
 *
 * 這是一個區域模組
 */
function getBonus($user, $card_id = 0, $deal_id = 0, $reason = '查詢') {
	$user_id = $user['user_id'];
	$phone = $user['phone'];
	$key = md5('GetBalance' . BONUS_KEY . BONUS_ID . $phone . time());
	$req = BONUS_URL . 'get-balance?scheme_id=' . BONUS_ID . "&user_id=$phone&return_type=json&key=$key";
	$json = callPoint($user_id, $card_id, $deal_id, 'BONUS', 0, $reason, $req);
	$balance = (int) $json['balance'];
	update('UPDATE user SET bonus=? WHERE user_id=?', $balance, $user_id);
	return $balance;
}

/**
 * 加Bonus
 *
 * 這是一個區域模組
 */
function addBonus($user, $amount, $card_id, $deal_id, $reason) {
	$user_id = $user['user_id'];
	$phone = $user['phone'];
	$key = md5('AddCredit' . BONUS_KEY . BONUS_ID . $phone . $amount . time());
	$req = BONUS_URL . 'add-credit?scheme_id=' . BONUS_ID . "&user_id=$phone&amount=$amount&return_type=json&key=$key";
	$json = callPoint($user_id, $card_id, $deal_id, 'BONUS', $amount, $reason, $req);
	$balance = (int) $json['balance'];
	update('UPDATE user SET bonus=? WHERE user_id=?', $balance, $user_id);
	return $balance;
}

/**
 * 扣Bonus
 *
 * 這是一個區域模組
 */
function deductBonus($user, $amount, $card_id, $deal_id, $reason) {
	$user_id = $user['user_id'];
	$phone = $user['phone'];
	$key = md5('DeductCredit' . BONUS_KEY . BONUS_ID . $phone . $amount . time());
	$req = BONUS_URL . 'deduct-credit?scheme_id=' . BONUS_ID . "&user_id=$phone&amount=$amount&return_type=json&key=$key";
	$json = callPoint($user_id, $card_id, $deal_id, 'BONUS', $amount, $reason, $req);
	$balance = (int) $json['balance'];
	update('UPDATE user SET bonus=? WHERE user_id=?', $balance, $user_id);
	return $balance;
}

/**
 * 取得Prepaid
 *
 * 這是一個區域模組
 */
function getPrepaid($user, $card_id = 0, $deal_id = 0, $reason = '查詢') {
	$user_id = $user['user_id'];
	$phone = $user['phone'];
	$key = hash_hmac('sha256', PREPAID_ID . "-$phone-" . time(), PREPAID_KEY);
	$req = PREPAID_URL . 'get-balance?type=consumer&filter=transaction-overview&scheme_id=' . PREPAID_ID . "&mobile_number=$phone&format=json&key=$key";
	$json = callPoint($user_id, $card_id, $deal_id, 'PREPAID', 0, $reason, $req);
	$balance = (int) $json['overview']['balance_point'];
	update('UPDATE user SET prepaid=? WHERE user_id=?', $balance, $user_id);
	return $balance;
}

/**
 * 加Prepaid
 *
 * 這是一個區域模組
 */
function addPrepaid($user, $amount, $card_id, $deal_id, $reason) {
	$user_id = $user['user_id'];
	$phone = $user['phone'];
	$key = hash_hmac('sha256', PREPAID_ID . "-$phone-$amount-" . time(), PREPAID_KEY);
	$req = PREPAID_URL . 'add-credit?type=consumer&scheme_id=' . PREPAID_ID . "&mobile_number=$phone&amount=$amount&format=json&key=$key";
	$json = callPoint($user_id, $card_id, $deal_id, 'PREPAID', $amount, $reason, $req);
	$balance = (int) $json['balance'];
	update('UPDATE user SET prepaid=? WHERE user_id=?', $balance, $user_id);
	return $balance;
}


/**
 * 扣Prepaid
 *
 * 這是一個區域模組
 */
function deductPrepaid($user, $amount, $card_id, $deal_id, $reason) {
	$user_id = $user['user_id'];
	$phone = $user['phone'];
	$key = hash_hmac('sha256', PREPAID_ID . "-$phone-$amount-" . time(), PREPAID_KEY);
	$req = PREPAID_URL . 'deduct-credit?type=consumer&scheme_id=' . PREPAID_ID . "&mobile_number=$phone&amount=$amount&format=json&key=$key";
	$json = callPoint($user_id, $card_id, $deal_id, 'PREPAID', $amount, $reason, $req);
	$balance = (int) $json['balance'];
	update('UPDATE user SET prepaid=? WHERE user_id=?', $balance, $user_id);
	return $balance;
}
