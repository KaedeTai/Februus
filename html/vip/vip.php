<?php

/**
 * vip API
 *
 * 販賣機vip API
 * @package API
 * @author Kaede
 */
class vip extends Febric {
	/**
	 * 設定環境
	 *
	 * 設定資料庫與時區等參數
	 */
	function __construct() {
		mysql_pconnect('rds.richi.com', 'vip', 'vip');
		mysql_select_db('vip');
		mysql_query('set names utf8');
		mysql_query("set time_zone = 'Asia/Taipei'");
		ini_set('default_socket_timeout', 5);
	}

	/**
	 * 取得訂單
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://api.febric.me/vip/getOrder
	 * @return object error, message, order[]
	 */
	public function getOrder() {
		//TODO: 檢查session
		$shop_id = 1;

		//設定SQL
		$order_sql   = "SELECT o.*, u.user_name, u.phone
						FROM   `order` o LEFT JOIN user u ON o.user_id=u.user_id
						WHERE  o.state IN ('Create', 'Confirm') AND shop_id=?";
		$item_sql    = 'SELECT * FROM item WHERE order_id=?';
		$toggle_sql  = 'SELECT * FROM item_toggle WHERE item_id=?';
		$option_sql  = 'SELECT * FROM item_option WHERE item_id=?';

		//讀取資料
		$order = queryArray($order_sql, $shop_id);
		foreach ($order as &$o) {
			$o['item'] = queryArray($item_sql, $o['order_id']);
			foreach ($o['item'] as &$i) {
				$o['toggle'] = queryArray($toggle_sql, $i['item_id']);
				$o['option'] = queryArray($option_sql, $i['item_id']);
			}
		}

		return array('order' => $order);
	}

	/**
	 * 取得菜單
	 *
	 * 這是一個 public function, 可直接當成API呼叫, 例如: http://api.febric.me/vip/getMenu?shop_id=1
	 * @return object error, message, menu[]
	 */
	public function getMenu() {
		//TODO: 實作cache
		$shop_id = _REQ('shop_id', RE_NUM);
		$shop = getRow('shop', array('shop_id' => $shop_id));
		$merchant_id = $shop['merchant_id'];

		//設定SQL
		$menu_sql    = 'SELECT menu_id, menu_name FROM menu WHERE merchant_id=? AND enable=1 ORDER BY sort';
		$product_sql = 'SELECT p.product_id, p.product_name, m.price
						FROM   product p, menu_product mp, model m
						WHERE  p.product_id=mp.product_id AND mp.menu_id=? AND p.enable=1 AND p.model_id=m.model_id
						ORDER BY p.sort';
		$model_sql   = 'SELECT model_id, model_name, price FROM model WHERE product_id=? ORDER BY sort';
		$toggle_sql  = 'SELECT t.toggle_id, t.toggle_name, pt.plus
						FROM   product_toggle pt, toggle t
						WHERE  pt.product_id=? AND pt.toggle_id=t.toggle_id AND t.enable=1
						ORDER BY t.sort';
		$select_sql  = 'SELECT s.select_id, s.select_name
						FROM   product_select ps, `select` s
						WHERE  ps.product_id=? AND ps.select_id=s.select_id AND s.enable=1
						ORDER BY s.sort';
		$option_sql  = 'SELECT o.option_id, o.option_name, po.plus
						FROM   product_option po, `option` o
						WHERE  o.select_id=? AND po.product_id=? AND po.option_id=o.option_id
						ORDER BY o.sort';

		//讀取資料
		$menu = queryArray($menu_sql, $merchant_id);
		foreach ($menu as &$m) {
			$m['product'] = queryArray($product_sql, $m['menu_id']);
			foreach ($m['product'] as &$p) {
				$p['model']  = queryArray($model_sql, $p['product_id']);
				$p['toggle'] = queryArray($toggle_sql, $p['product_id']);
				$p['select'] = queryArray($select_sql, $p['product_id']);
				foreach ($p['select'] as &$s) {
					$s['option'] = queryArray($option_sql, $s['select_id'], $p['product_id']);
				}
			}
		}

		return array('menu' => $menu);
	}

}
