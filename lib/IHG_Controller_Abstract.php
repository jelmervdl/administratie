<?php

class IHG_Controller_Abstract
{
	public function __construct(IHG_Application_Abstract $application)
	{
		$this->application = $application;
	}
	
	public function __get($key)
	{
		return $this->application->$key;
	}
	
	protected function _is_post_request()
	{
		return $_SERVER['REQUEST_METHOD'] == 'POST';
	}
}