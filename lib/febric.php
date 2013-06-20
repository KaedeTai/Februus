<?php

class Febric {
	function __construct() {
		mysql_pconnect(DB_HOST, DB_USER, DB_PASS);
		mysql_select_db(DB_NAME);
		mysql_query('set names utf8');
		mysql_query("set time_zone = 'Asia/Taipei'");
	}
}
