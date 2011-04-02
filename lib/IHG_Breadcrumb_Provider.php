<?php

class IHG_Breadcrumb_Provider extends IHG_Component_Abstract {
	
	private $_crumbs = array();
	
	public function add_crumb($title, $link = null) {
		$this->_crumbs[] = array($title, $link);
	}
	
	public function view() {
		$writer = new IHG_HTML_Writer();
		$writer->start_list();
		foreach($this->_crumbs as $crumb) {
			$writer->item();
			if($crumb[1]) $writer->link($crumb[1]);
			$writer->put($crumb[0]);
		}
		$writer->end_list();
		return $writer;
	}
}

?>