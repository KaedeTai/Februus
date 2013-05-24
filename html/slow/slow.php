<?php

class slow {
	public function init() {
		$a = 0;
		$start = time();
		for ($i = 0; $i < 100000000; $i ++) {
			$a ++;
		}
		return 'This is a slow page. $a = ' . $a . '. It tooks ' . (time() - $start) . ' seconds.';
	}
}
