<?php

include '../../lib/IHG.php';

error_reporting(E_ALL);
ini_set('display_errors', true);

define('ROOT', dirname(__FILE__) . '/');

include 'simpletest/autorun.php';

class IHG_Record_TestSuite extends TestSuite {
	public function __construct() {
		$this->addFile(ROOT.'IHG_Record_MySQL.php');
		$this->addFile(ROOT.'IHG_Record_SQLite.php');
	}
}

class IHG_Record_Set_Testsuite extends TestSuite {
	public function __construct() {
		$this->addFile(ROOT.'IHG_Record_Set.php');
	}
}

class IHG_HTML_TestSuite extends TestSuite {
	public function __construct() {
		$this->addFile(ROOT.'IHG_HTML_Writer.php');
	}
}