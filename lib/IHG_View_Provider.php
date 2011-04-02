<?php

class IHG_View_Provider extends IHG_Component_Abstract {
	
	private $_view_path;
	
	private $_application;
	
	public function __construct($path_to_views) {
		$this->_view_path = rtrim($path_to_views, '/');
		
		$this->observe('registered', array($this, '_registered'));
	}
	
	public function writer() {
		return new IHG_HTML_Writer();
	}
	
	public function redirect($url) {
		return new IHG_View_Redirect($url);
	}
	
	public function reload() {
		return new IHG_View_Reload();
	}
	
	public function combine($arg0, $arg1 = null) {
		$views = func_get_args();
		
		$combined_view = new IHG_View_Collection();
		
		foreach($views as $index => $view) {
			$combined_view->append_view($view);
		}
		
		return $combined_view;
	}
	
	public function from_file($file) {
		if(file_exists($this->_view_path . '/' . $file)) {
			return $this->_load_file($this->_view_path . '/' . $file);
		}
		
		$candidates = glob($this->_view_path . '/' . $file . '.*');
		
		if(count($candidates) > 0) {
			foreach($candidates as $candidate) {
				try {
					return $this->_load_file($candidate);
				} catch(Exception $e) {}
			}
		}
		
		throw new InvalidArgumentException('Template file not found: ' . $file);
	}
	
	public function from_string($content) {
		return new IHG_View_String($content);
	}
	
	public function from_record($record_type) {
		return new IHG_HTML_Table($record_type);
	}
	
	public function true() {
		return new IHG_View_JSON(true);
	}
	
	public function false() {
		return new IHG_View_JSON(false);
	}
	
	private function _load_file($file) {
		if(!is_file($file)) {
			throw new InvalidArgumentException($file . ' is not a file');
		}
		
		switch($this->_extension($file)) {
			case 'txt':
				return IHG_View_String::from_file($this->_application, $file);
			case 'php':
			case 'phtml':
				return IHG_View_Template_Native::from_file($this->_application, $file);
			default:
				throw new InvalidArgumentException('Filetype not supported');
		}
	}
	
	private function _extension($file) {
		return substr($file, strrpos($file, '.') + 1);
	}
	
	public function _registered(IHG_Application_Abstract $application, $component_name) {
		$this->_application = $application;
	}
}