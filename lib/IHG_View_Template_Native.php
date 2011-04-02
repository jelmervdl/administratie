<?php

class IHG_View_Template_Native implements IHG_View_Interface
{
	private $_data = array();
	
	private $_application;
	
	private $_template_file;
	
	private $_embedded = true;
	
	public function __construct(IHG_Application_Abstract $application, $template_file)
	{
		$this->_application = $application;
		
		$this->_template_file = $template_file;
	}
	
	public function __set($key, $value)
	{
		$this->_data[$key] = $value;
	}
	
	public function __get($key)
	{
		return $this->_application->$key;
	}
	
	public function dont_embed()
	{
		$this->_embedded = false;
	}
	
	public function is_embedded()
	{
		return $this->_embedded;
	}
	
	public function draw()
	{
		extract($this->_data);		
		include $this->_template_file;
	}
	
	protected function _attr($x)
	{
		return htmlentities((string) $x, ENT_QUOTES, 'UTF-8');
	}
	
	protected function _html($x)
	{
		return htmlentities((string) $x, ENT_QUOTES, 'UTF-8');
	}
	
	protected function _link_sheet($label, $controller, $method, $arg0 = null)
	{
		$arguments = array_slice(func_get_args(), 1);
		
		$link = call_user_func_array(array($this->_application->router, 'link'), $arguments);
		
		return sprintf('<a href="%s" class="open-in-sheet">%s</a>',
			$link, $this->_html($label));
	}
	
	protected function _partial($controller, $method, $arg0 = null)
	{
		$arguments = array_slice(func_get_args(), 2);
		
		return $this->_application->run_controller($controller, $method, $arguments)->draw();
	}
	
	protected function _inline_edit($postback_url, $value)
	{
		$id = uniqid('inline_edit');
		
		return sprintf('<span id="%s" class="inline_edit" rel="%s">%s</span>',
			$id, $this->_attr($postback_url), $this->_html($value));
	}
	
	static public function from_file(IHG_Application_Abstract $application, $file)
	{
		return new self($application, $file);
	}
}