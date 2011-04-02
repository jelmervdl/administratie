<?php

class IHG_Record_Set_Page {
	
	protected $_set;
	
	protected $_page_id;
	
	protected $_page_size = 10;
	
		
	public function __construct(IHG_Record_Set $record_set, $page_id = 'page') {
		$this->_record_set = $record_set;
		$this->_page_id = $page_id;
	}
	
	
	public function set_page_size($record_count) {
		$this->_page_size = (int) $record_count;
	}
	
	public function page_size() {
		return $this->_page_size;
	}
	
	
	public function control_view() {
		
	}
	
	public function record_set() {
		return $this->_record_set->slice(
			$this->_calculate_offset(),
			$this->page_size());
	}
	
	
	protected function _calculate_offset() {
		$get_id = $this->_page_id . '_offset';
		
		if(isset($_GET[$get_id]) && filter_var($_GET[$get_id], FILTER_VALIDATE_INT, array('min_range' => 0))) {
			return (int) $_GET[$get_id];
		} else {
			return 0;
		}
	}
}

?>