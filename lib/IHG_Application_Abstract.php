<?php

abstract class IHG_Application_Abstract {
	
	protected $components = array();
	
	public function __construct() {
		
	}
	
	public function __get($key) {
		return $this->get_component($key);
	}
	
	protected function register_component($name, IHG_Component_Interface $component, $override = false) {
		if(array_key_exists($name, $this->components) && !$override) {
			throw new InvalidArgumentException("There is already a component with the name $name registered with this application");
		}
		
		$this->components[$name] = $component;
		
		$component->notify('registered', array($this, $name));
		
		return $this;
	}
	
	protected function get_component($name) {
		if(!array_key_exists($name, $this->components)) {
			throw new InvalidArgumentException($name . ' no such component registered');
		}
		
		return $this->components[$name];
	}
	
	protected function set_up() {
		$this->register_component('router',
			new IHG_Router_Provider());
		
		$this->register_component('configuration', 
			new IHG_Configuration_Provider(array(
				'global_controller' 	=> 'IHG_Global_Controller::index',
				'exception_controller'	=> 'IHG_Exception_Controller::index'
			)));
		
		$this->register_component('views',
			new IHG_View_Provider('app/views'));
			
		$this->register_component('breadcrumbs',
			new IHG_Breadcrumb_Provider());
			
		$this->register_component('form',
			new IHG_Form_Helper());
	}
	
	public function run() {
		
		$this->set_up();
		
		if(isset($_GET['__path']))
			$path = $_GET['__path'];
		else
			$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';
		
		list($global_controller, $global_action) = explode('::', $this->configuration->global_controller);
		
		list($specific_controller, $specific_action, $arguments) = $this->router->find_controller($path);
		
		$specific_view = $this->run_controller($specific_controller, $specific_action, $arguments);
		
		if(!$specific_view->is_embedded()) {
			$global_view = $specific_view;
		} else {
			$global_view = $this->run_controller($global_controller, $global_action, array($specific_view));
		}
		
		$global_view->draw();
		
		$this->tear_down();
	}
	
	public function run_controller($controller_class, $controller_action, $arguments, $dont_catch = false) {
		try {
			
			if(!class_exists($controller_class)) {
				throw new LogicException(sprintf('Controller "%s" does not exist', $controller_class));
			}
			
			$controller = new $controller_class($this);
		
			if(!is_callable(array($controller, $controller_action))) {
				throw new LogicException(sprintf('Controller "%s" has no action named "%s"', $controller_class, $controller_action));
			}
		
			return call_user_func_array(array($controller, $controller_action), $arguments);
		} catch(Exception $e) {
			if(!$dont_catch) {
				return $this->run_exception($e);
			} else {
				throw $e;
			}
		}
	}
	
	public function run_exception(Exception $e) {
		list($controller, $action) = explode('::', $this->configuration->exception_controller);
		
		return $this->run_controller($controller, $action, array($e), true);
	}
	
	public function tear_down() {
		
	}
	
	static public function runApplication($application_class) {
		$application = new $application_class;
		$application->run();
	}
}