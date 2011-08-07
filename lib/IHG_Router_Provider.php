<?php

class IHG_Router_Provider extends IHG_Component_Abstract
{
	const PATTERN = 0;
	
	const CONTROLLER = 1;
	
	const ACTION = 2;
	
	const ROUTE = 3;
	
	const HASH_PATTERN = '%s::%s(%d)';
	
	private $_routes = array();
	
	private function _compile_hash($route, $controller, $action)
	{
		$argument_count = substr_count($route, '%d')
			+ substr_count($route, '%s');
			
		return sprintf(self::HASH_PATTERN, $controller, $action, $argument_count);
	}
	
	private function _compile_pattern($route, $controller, $action)
	{
		return sprintf('{^%s$}i',
			str_replace(
				array('%d', '%s'),
				array('([0-9]+)', '([a-z0-9_\-\+%]+)'),
				preg_quote($route)));
	}
	
	public function root()
	{
		// Als mod_rewrite via __path variabele gebruikt wordt, dan is de
		// mapnaam de root. Anders vallen we terug op Apache's multiviews
		// en wordt de scriptnaam zonder extensie de root
		
		if(isset($_GET['__path']))
			return dirname($_SERVER['SCRIPT_NAME']);
		else
			return dirname($_SERVER['SCRIPT_NAME']) . '/' . basename($_SERVER['SCRIPT_NAME'], '.php');
	}
	
	public function register_route($route, $controller_name)
	{
		if (!is_array($controller_name))
			$controller_name = explode('::', $controller_name);
		
		list($controller, $action) = $controller_name;
		
		$hash = $this->_compile_hash($route, $controller, $action);
		
		$this->_routes[$hash] = array(
			self::PATTERN => $this->_compile_pattern($route, $controller, $action),
			self::ROUTE => $route,
			self::CONTROLLER => $controller,
			self::ACTION => $action);
		
		return $this;
	}
	
	public function find_controller($url)
	{
		foreach($this->_routes as $hash => $route)
		{
			if(preg_match($route[self::PATTERN], $url, $arguments))
			{
				array_shift($arguments);
				
				$arguments = array_map('urldecode', $arguments);
				
				return array($route[self::CONTROLLER], $route[self::ACTION], $arguments);
			}
		}
	
		return null;
	}
	
	public function link($controller, $action, $arg0 = null)
	{
		try
		{
			$arguments = array_slice(func_get_args(), 2);
			$root = $this->root();
			return $root . $this->link_controller($controller, $action, $arguments);
		}
		catch(Exception $e)
		{
			return '#';
		}
	}

	
	public function link_controller($controller, $action, array $arguments = array())
	{
		//$arguments = array_filter($arguments, function($arg) { return !empty($arg); });
		
		$arguments = array_map('urlencode', $arguments);
		
		$hash = sprintf(self::HASH_PATTERN,
			$controller,
			$action,
			count($arguments));
		
		if (!array_key_exists($hash, $this->_routes))
			throw new LogicException('Route not found: ' . $hash);
		
		return vsprintf($this->_routes[$hash][self::ROUTE], $arguments);
	}
}