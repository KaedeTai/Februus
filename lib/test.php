<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'util.php';
require_once 'mysql.php';

chdir('../html/' . PAGE);
$a = explode('/', PAGE);
define('_CLASS_NAME_', $a[count($a) - 1]);
require_once _CLASS_NAME_ . '.php';

class PageTest extends PHPUnit_Framework_TestCase {
	protected $page;

	protected function setUp() {
		$this->page = eval('return new ' . _CLASS_NAME_ . ';');
	}

	protected function tearDown() {
		
	}
}
