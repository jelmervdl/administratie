<?php

class IHG_Record_Provider extends IHG_Component_Abstract implements IteratorAggregate {
	
	private $_pdo;
	
	private $_record_name;
	
	public function __construct(PDO $pdo, $record_name) {
		$this->_pdo = $pdo;
		$this->_record_name = $record_name;
	}
	
	public function record_type() {
		return $this->_record_name;
	}
	
	public function find($conditions = array()) {
		return IHG_Record::find_record($this->_pdo, $this->_record_name, $conditions);
	}
	
	public function find_all($conditions = array()) {
		return IHG_Record::find_records($this->_pdo, $this->_record_name, $conditions);
	}
	
	public function create() {
		return IHG_Record::create_record($this->_pdo, $this->_record_name);
	}
	
	public function getIterator() {
		return $this->find_all();
	}
}