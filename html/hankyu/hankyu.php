<?php

/**
 * hankyu API
 *
 * 阪急App API
 * @package API
 * @author Kaede
 */
class hankyu extends Febric {
	/**
	 * 設定環境
	 *
	 * 設定資料庫與時區等參數
	 */
	function __construct() {
		mysql_pconnect('192.168.0.210', 'hankyu', 'hankyu');
		mysql_select_db('hankyu');
		mysql_query('set names utf8');
		mysql_query("set time_zone = 'Asia/Taipei'");
	}

	/**
	 * 新增使用者
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://hankyu.richi.com/hankyu/addUser?id_no=A123456789&name=Kaede&gender=M&birthday=1976/10/07&phone=0912345678&email=kaede@richi.com&epaper=1
	 * @global string id_no    身分證字號
	 * @global string name     姓名
	 * @global string gender   性別 (M: 男性, F: 女性)
	 * @global string birthday 生日, 例: 1976-10-20
	 * @global string phone    電話, 例: 0910094490
	 * @global string email    信箱
	 * @global int    epaper   訂閱電子報 (0: 否, 1: 是)
	 * @return object error, message, user_id, card_no
	 */
	public function addUser() {
		//取得參數
		$id_no    = _REQ('id_no',    '/^[A-Z][0-9]{9}$/');
		$name     = _REQ('name',     '/.+/');
		$gender   = _REQ('gender',   '/^[MF]$/');
		$birthday = _REQ('birthday', '/^(19|20)[0-9]{2}-[01][0-9]-[0-3][0-9]$/');
		$phone    = _REQ('phone',    '/^[0-9-]+$/');
		$email    = _REQ('email',    '/^[0-9a-z._-]+@[0-9a-z_-]+(\.[0-9a-z_-]+)+$/i');
		$epaper   = _REQ('epaper',   '/^(0|1)$/');
		
		//取得卡號: TODO
		if (getRow('card', array('id_no' => $id_no))) {
			throw new ActionException(1, '帳號重複');
		}
		if (!update("UPDATE card SET id_no=? WHERE id_no='' LIMIT 1", $id_no)) {
			throw new ActionException(2, '卡號用盡');
		}
		$card = getRow('card', array('id_no' => $id_no));

		//新增帳號: TODO
		$user_id = addRow('user', array(
			'id_no'    => $id_no,
			'card_no'  => $card['card_no'],
			'name'     => $name,
			'gender'   => $gender,
			'birthday' => $birthday,
			'phone'    => $phone,
			'email'    => $email,
			'epaper'   => $epaper,
			'first_ts' => date('Y-m-d H:i:s')
		));
		if (!$user_id) {
			throw new ActionException(1, '帳號重複');
		}

		return array(
			'user_id' => $user_id,
			'card_no' => $card['card_no'],
		);
	}

	/**
	 * 登入
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://hankyu.richi.com/hankyu/signIn?id_no=A123456789&phone=0912345678
	 * @global string id_no   身分證字號
	 * @global string card_no 會員卡號
	 * @global string phone   電話
	 * @return object error, message, user_id, id_no, card_no, name, gender, birthday, phone, email, point
	 */
	public function signIn() {
		//取得參數
		$id_no   = _REQ('id_no',   '/^[A-Z][0-9]{9}$/');
		$card_no = _REQ('card_no', '/^[0-9]+$/');
		$phone   = _REQ('phone',   '/^[0-9-]+$/');

		//取得用戶
		$user = getRow('user', array('id_no' => $id_no, 'card_no' => $card_no, 'phone' => $phone));
		if (!$user) {
			throw new ActionException(1, '身分證字號、卡號或電話號碼錯誤!');
		}
		setRow('user', array('user_id' => $user['user_id']), 'login=login+1', true);

		$_SESSION['user'] = $user;

		return $user;
	}

	/**
	 * 加點
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://hankyu.richi.com/hankyu/addPoint?point=100&description=滿千送百
	 * @global int    point       點數
	 * @global string description 說明
	 * @return object error, message, point
	 */
	public function addPoint() {
		//取得參數
		$user_id     = getUserID();
		$point       = _REQ('point',       '/^[1-9][0-9]*$/');
		$description = _REQ('description', '/.+/');

		//加點: TODO
		setRow('user', array('user_id' => $user_id), "point=point+$point", true);

		//加點記錄
		addRow('point_log', array('user_id' => $user_id, 'point' => $point, 'description' => $description));

		//取得用戶
		$user = getRow('user', array('user_id' => $user_id), '*', true);

		return array('point' => $user['point']);
	}

	/**
	 * 歷史紀錄
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://hankyu.richi.com/hankyu/getHistory
	 * @return object error, message, history: [point, description, ts]
	 */
	public function getHistory() {
		//查詢歷史紀錄: TODO
		$history = queryArray('SELECT point, description, ts FROM point_log WHERE user_id=? ORDER BY log_id', getUserID());

		return array('history' => $history);
	}

	/**
	 * 列出所有活動
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://hankyu.richi.com/hankyu/showMessages
	 * @return object error, message, events: [event_id, brand_id, brand, url, title, digest, content, location, event_type_id, start_ts, end_ts, sub]
	 */
	public function showMessages() {
		//取得參數
		$user_id  = getUserID();
	
		//取得活動資訊: TODO
		$sql = 'SELECT e.event_id, e.brand_id, b.name AS brand, url, title, digest, content, location, event_type_id, start_ts, end_ts,
			IF(ISNULL(ue.user_id), 0, 1) AS sub
			FROM event e
			LEFT JOIN brand b ON e.brand_id=b.brand_id
			LEFT JOIN user_event ue ON e.event_id=ue.event_id AND ue.user_id=?
			WHERE end_ts>NOW()
			HAVING sub=?
			ORDER BY start_ts DESC';
		$reminds = array();
		$subs = queryArray($sql, $user_id, 1);
		$tasks = queryArray('SELECT * FROM task WHERE start_ts<=NOW() AND end_ts>NOW() ORDER BY create_ts DESC');
		$gifts = queryArray('SELECT * FROM gift WHERE start_ts<=NOW() AND end_ts>NOW() ORDER BY create_ts DESC');
		$events = queryArray($sql, $user_id, 0);

		return array('reminds' => $reminds, 'subs' => $subs, 'tasks' => $tasks, 'gifts' => $gifts, 'events' => $events);
	}
	
	/**
	 * 取得活動內容
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://hankyu.richi.com/hankyu/getEvent?event_id=1
	 * @global int event_id 活動代碼
	 * @return object error, message, event_id, brand_id, brand, url, title, digest, content, location, event_type_id, is_sub, start_ts, end_ts, sub_event, sub_brand, rounds: [round_id, title, start_ts, end_ts, sub], others: [event_id, brand_id, brand, url, title, digest, content, event_type_id, start_ts, end_ts, sub]
	 */
	public function getEvent() {
		//取得參數
		$user_id  = getUserID();
		$event_id = _REQ('event_id', '/^[1-9][0-9]*$/');

		//取得活動資訊
		$event = queryRow('SELECT e.event_id, e.brand_id, b.name AS brand, url, title, digest, content, location, event_type_id, is_sub, start_ts, end_ts,
			IF(ISNULL(ue.user_id), 0, 1) AS sub_event,
			IF(ISNULL(ub.user_id), 0, 1) AS sub_brand
			FROM event e
			LEFT JOIN brand b ON e.brand_id=b.brand_id
			LEFT JOIN user_event ue ON e.event_id=ue.event_id AND ue.user_id=?
			LEFT JOIN user_brand ub ON e.event_id=ub.brand_id AND ub.user_id=?
			WHERE e.event_id=?', $user_id, $user_id, $event_id
		);
		if (!$event) {
			throw new ActionException(1, '無此活動');
		}

		//會員讀取記錄
		addRow('event_log', array('user_id'  => $user_id, 'event_id' => $event_id));

		$brand_id = $event['brand_id'];
		$event['rounds'] = queryArray('SELECT r.round_id, title, start_ts, end_ts,
			IF(ISNULL(ur.user_id), 0, 1) AS sub
			FROM round r
			LEFT JOIN user_round ur ON r.round_id=ur.round_id AND ur.user_id=?
			WHERE r.event_id=?
			ORDER BY start_ts', $user_id, $event_id);
		$event['others'] = queryArray('SELECT e.event_id, e.brand_id, b.name AS brand, url, title, digest, content, location, event_type_id, is_sub, start_ts, end_ts,
			IF(ISNULL(ue.user_id), 0, 1) AS sub
			FROM event e
			LEFT JOIN brand b ON e.brand_id=b.brand_id
			LEFT JOIN user_event ue ON e.event_id=ue.event_id AND ue.user_id=?
			WHERE end_ts>NOW() AND e.event_id<>?
			ORDER BY start_ts DESC', $user_id, $event_id);

		return $event;
	}

	/**
	 * 訂閱品牌
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://hankyu.richi.com/hankyu/subBrand?brand_id=1
	 * @global int brand_id 品牌代碼
	 * @return object error, message
	 */
	public function subBrand() {
		//取得參數
		$user_id  = getUserID();
		$brand_id = _REQ('brand_id', '/^[1-9][0-9]*$/');

		//訂閱
		addRow('user_brand', array('user_id' => $user_id, 'brand_id' => $brand_id), true);

		return array();
	}
	
	/**
	 * 取消訂閱品牌
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://hankyu.richi.com/hankyu/unsubBrand?brand_id=1
	 * @global int brand_id 品牌代碼
	 * @return object error, message
	 */
	public function unsubBrand() {
		//取得參數
		$user_id  = getUserID();
		$brand_id = _REQ('brand_id', '/^[1-9][0-9]*$/');

		//訂閱
		delRow('user_brand', array('user_id' => $user_id, 'brand_id' => $brand_id));

		return array();
	}

	/**
	 * 訂閱活動
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://hankyu.richi.com/hankyu/subEvent?event_id=1
	 * @global int event_id 活動代碼
	 * @return object error, message
	 */
	public function subEvent() {
		//取得參數
		$user_id  = getUserID();
		$event_id = _REQ('event_id', '/^[1-9][0-9]*$/');

		//訂閱
		addRow('user_event', array('user_id' => $user_id, 'event_id' => $event_id), true);

		return array();
	}
	
	/**
	 * 取消訂閱活動
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://hankyu.richi.com/hankyu/unsubEvent?event_id=1
	 * @global int event_id 活動代碼
	 * @return object error, message
	 */
	public function unsubEvent() {
		//取得參數
		$user_id  = getUserID();
		$event_id = _REQ('event_id', '/^[1-9][0-9]*$/');

		//訂閱
		delRow('user_event', array('user_id' => $user_id, 'event_id' => $event_id));

		return array();
	}

	/**
	 * 訂閱場次
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://hankyu.richi.com/hankyu/subRound?round_id=1
	 * @global int round_id 場次代碼
	 * @return object error, message
	 */
	public function subRound() {
		//取得參數
		$user_id  = getUserID();
		$round_id = _REQ('round_id', '/^[1-9][0-9]*$/');

		//訂閱
		addRow('user_round', array('user_id' => $user_id, 'round_id' => $round_id), true);

		return array();
	}
	
	/**
	 * 取消訂閱場次
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://hankyu.richi.com/hankyu/unsubRound?round_id=1
	 * @global int round_id 場次代碼
	 * @return object error, message
	 */
	public function unsubRound() {
		//取得參數
		$user_id  = getUserID();
		$round_id = _REQ('round_id', '/^[1-9][0-9]*$/');

		//訂閱
		delRow('user_round', array('user_id' => $user_id, 'round_id' => $round_id));
	
		return array();
	}

	/**
	 * 完成步驟(掃描QRCODE)
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://hankyu.richi.com/hankyu/doStep?qrcode=afjkqelfjf
	 * @global string qrcode 步驟的QRCODE
	 * @return object error, message, todo: 當todo為零表示該任務完成並已經加點, step_id: 完成步驟的代碼
	 */
	public function doStep() {
		//取得參數
		$user_id = getUserID();
		$qrcode  = _REQ('qrcode');

		//取得步驟
		$step = getRow('step', array('qrcode' => $qrcode));
		if (!$step) {
			throw new ActionException(1, '無法辨識的QRCODE!');
		}
		$step_id = $step['step_id'];

		//取得任務
		$task = getRow('task', array('task_id' => $step['task_id']));
		if (!$task) {
			throw new ActionException(2, '查無此任務!');
		}
		$task_id = $task['task_id'];

		//檢查狀態
		if (getRow('user_step', array('user_id' => $user_id, 'step_id' => $step_id))) {
			throw new ActionException(3, '您之前就已經完成此步驟了!');
		}

		//完成步驟
		addRow('user_step', array('user_id' => $user_id, 'step_id' => $step_id));

		//確認是否完成全部任務步驟
		$result = queryRow('SELECT SUM(IF(ISNULL(us.user_id), 1, 0)) AS todo FROM step s LEFT JOIN user_step us ON s.step_id=us.step_id AND us.user_id=? WHERE s.task_id=?', $user_id, $task_id);
		$result['step_id'] = $step_id;

		return $result;
	}

	/**
	 * 完成任務並送點(打卡後)
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://hankyu.richi.com/hankyu/doTask?task_id=1
	 * @global int task_id 任務代碼
	 * @return object error, message, reward, point
	 */
	public function doTask() {
		//取得參數
		$user_id = getUserID();
		$task_id = _REQ('task_id', '/^[1-9][0-9]*$/');

		//取得任務
		$task = getRow('task', array('task_id' => $task_id));
		if (!$task) {
			throw new ActionException(1, '查無此任務!');
		}

		//確認是否完成全部任務步驟
		$result = queryRow('SELECT SUM(IF(ISNULL(us.user_id), 1, 0)) AS todo FROM step s LEFT JOIN user_step us ON s.step_id=us.step_id AND us.user_id=? WHERE s.task_id=?', $user_id, $task_id);
		if ($result['todo'] > 0) {
			throw new ActionException(2, '尚有步驟未完成!');
		}

		//檢查狀態
		if (getRow('user_task', array('user_id' => $user_id, 'task_id' => $task_id))) {
			throw new ActionException(3, '您之前就已經完成此任務了!');
		}

		//完成任務進行加點: TODO
		$point = $task['reward'];
		$description = '完成任務 ' . $task['title'];
		if (!setRow('user', array('user_id' => $user_id), "point=point+$point")) {
			throw new ActionException(4, '完成任務但加點失敗!');
		}

		//加點記錄
		addRow('point_log', array('user_id' => $user_id, 'point' => $point, 'description' => $description));

		//完成任務
		addRow('user_task', array('user_id' => $user_id, 'task_id' => $task['task_id']));

		//取得加點後狀態
		$user = getRow('user', array('user_id' => $user_id), 'point', true);

		return array('reward' => $task['reward'], 'point' => $user['point']);
	}


	/**
	 * 列出所有任務
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://hankyu.richi.com/hankyu/showTasks
	 * @return object error, message, tasks: [task_id, title, digest, content, reward, create_ts, start_ts, end_ts, done]
	 */
	public function showTasks() {
		//取得參數
		$user_id  = getUserID();

		//取得任務資訊
		$tasks = queryArray('SELECT t.*,
			IF(ISNULL(ut.user_id), 0, 1) AS done
			FROM task t
			LEFT JOIN user_task ut ON t.task_id=ut.task_id AND ut.user_id=?
			WHERE start_ts<=NOW() AND end_ts>NOW()
			ORDER BY start_ts DESC', $user_id
		);

		return array('tasks' => $tasks);
	}

	/**
	 * 取得任務內容
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://hankyu.richi.com/hankyu/getTask?task_id=1
	 * @global int task_id 活動代碼
	 * @return object error, message, task_id, url, title, digest, content, location, reward, create_ts, start_ts, end_ts, done, steps: [step_id, qrcode, title, content, location, done]
	 */
	public function getTask() {
		//取得參數
		$user_id = getUserID();
		$task_id = _REQ('task_id', '/^[1-9][0-9]*$/');

		//取得任務資訊
		$task = queryRow('SELECT t.*,
			IF(ISNULL(ut.user_id), 0, 1) AS done
			FROM task t
			LEFT JOIN user_task ut ON t.task_id=ut.task_id AND ut.user_id=?
			WHERE t.task_id=?', $user_id, $task_id
		);
		if (!$task) {
			throw new ActionException(1, '無此任務');
		}

		$task['steps'] = queryArray('SELECT s.step_id, qrcode, title, content, location,
			IF(ISNULL(us.user_id), 0, 1) AS done
			FROM step s
			LEFT JOIN user_step us ON s.step_id=us.step_id AND us.user_id=?
			WHERE s.task_id=?
			ORDER BY s.step_id', $user_id, $task_id);

		return $task;
	}

	/**
	 * 取得兌換贈品
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://hankyu.richi.com/hankyu/getGift?gift_id=1
	 * @global int gift_id 贈品代碼
	 * @return object error, message, gift_id, url, title, digest, content, amount, create_ts, start_ts, end_ts
	 */
	public function getGift() {
		//取得參數
		$user_id = getUserID();
		$gift_id = _REQ('gift_id', '/^[1-9][0-9]*$/');

		//取得任務資訊
		$gift = queryRow('SELECT * FROM gift WHERE gift_id=?', $gift_id);
		if (!$gift) {
			throw new ActionException(1, '無此贈品');
		}

		return $gift;
	}

}

/**
 * 取得使用者代碼
 *
 * 這是一個區域模組
 */
function getUserID() {
	if (!isset($_SESSION['user'])) {
		throw new ActionException(-1, '尚未登入');
	}

	return $_SESSION['user']['user_id'];
}
