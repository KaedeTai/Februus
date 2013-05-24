<?php

define('PAGE', 'example1');

require_once 'test.php';

class testExample1 extends PageTest {
	public function testInit() {
		$this->assertRegExp('/^<!DOCTYPE html>.*屈臣氏/s', $this->page->init());
	}

	public function testInit2() {
		$this->assertRegExp('/^<!DOCTYPE html>.*OK/s', $this->page->init());
	}
}
