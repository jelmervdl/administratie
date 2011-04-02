<?php

class IHG_Identity_Exception extends Exception {}

class IHG_Identity_Provider extends IHG_Component_Abstract {
	
	private $_identity;
	
	private $_record_provider;
	
	private $_session_namespace;
	
	public function __construct(IHG_Record_Provider $provider) {
		
		$this->_record_provider = $provider;
		
		$this->_session_namespace = $provider->record_type() . '_identity';
	}
	
	public function i_has_it() {
		return $this->has_identity();
	}
	
	public function has_identity() {
		if($this->_identity === null) {
			$this->_retrieve_identity();
		}
		
		return (bool) $this->_identity;
	}
	
	public function identity() {
		if(!$this->has_identity()) {
			throw new IHG_Identity_Exception('User is not identified');
		}
		
		return $this->_identity;
	}
	
	public function authenticate(array $conditions) {
		
		$identity = $this->_record_provider->find_all($conditions);
		
		if(iterator_count($identity) !== 1) {
			throw new IHG_Identity_Exception('Identity not found');
		}
		
		$this->_identity = current($identity);
		
		$this->_store_identity();
	}
	
	public function clear_identity() {
		$this->_identity = false;
		
		$this->_store_identity();
	}
	
	private function _retrieve_identity() {
		@session_start();
	
		if(isset($_SESSION[$this->_session_namespace])) {
			$this->_identity = $this->_record_provider->find((int) $_SESSION[$this->_session_namespace]);
		} else {
			$this->_identity = false;
		}
	}
	
	private function _store_identity() {
		@session_start();
		
		if($this->_identity) {
			$_SESSION[$this->_session_namespace] = $this->_identity->id;
		} else {
			unset($_SESSION[$this->_session_namespace]);
		}
	}
}