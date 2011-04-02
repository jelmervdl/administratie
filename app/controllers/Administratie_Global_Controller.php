<?php

class Administratie_Global_Controller extends IHG_Controller_Abstract
{
	public function index($child_view)
	{
		if (isset($_SERVER['HTTP_X_LOAD_SHEET']))
			return $child_view;
		
		$view = $this->views->from_file('administratie_layout.phtml');
		$view->breadcrumbs = $this->breadcrumbs->view();
		$view->child_view = $child_view;
		return $view;
	}
}

?>