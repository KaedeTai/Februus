<?php

/**
 * vmc API
 *
 * 販賣機VMC API
 * @package API
 * @author Kaede
 */
class vmc extends Febric {
	/**
	 * 設定環境
	 *
	 * 設定資料庫與時區等參數
	 */
	function __construct() {
		mysql_pconnect('rds.richi.com', 'vmc', 'vmc');
		mysql_select_db('vmc');
		mysql_query('set names utf8');
		mysql_query("set time_zone = 'Asia/Taipei'");
		ini_set('default_socket_timeout', 5);
	}

	/**
	 * 保留商品
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://api.febric.me/vmc/reserve?track_id=1
	 * @return object error, message, code
	 */
	public function reserve() {
		$track_id = _REQ('track_id', RE_NUM);
		$track = getRow('track', array('track_id' => $track_id));
		if (!$track) {
			throw new ActionException(1, 'track_id error');
		}
		$machine = getRow('machine', array('machine_id' => $track['machine_id']));
		if (!$machine) {
			throw new ActionException(2, 'machine_id error');
		}
		$tracks = queryArray('SELECT * FROM track WHERE machine_id=? AND item_id=?', $track['machine_id'], $track['item_id']);
		$no = array();
		foreach ($tracks as $t) {
			$no[] = $t['track_no'];
		}
		$json = @file_get_contents($machine['url'] . 'reserve/' . implode(',', $no));
		if (!$json) {
			throw new ActionException(3, 'connection timeout');
		}
		return json_decode($json, true);
	}

	/**
	 * 擊出商品
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://api.febric.me/vmc/deliver?code=12345678
	 * @return object error, message
	 */
	public function deliver() {
		$code = _REQ('code', RE_STR);
		$machine_id = 1; //TODO
		$machine = getRow('machine', array('machine_id' => $machine_id));
		if (!$machine) {
			throw new ActionException(2, 'machine_id error');
		}
		$json = @file_get_contents($machine['url'] . 'deliver/' . $code);
		if (!$json) {
			throw new ActionException(3, 'connection timeout');
		}
		return json_decode($json, true);
	}
}
