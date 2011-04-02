<?php


class IHG_Annotated_Record extends IHG_Record {
	
	const PROP_GETTER = 0;
	
	const PROP_SETTER = 1;
	
	const PROP_VALUE = 2;
	
	static private $record_definitions = array();
	
	static protected function get_overridden_properties($record_name) {
		if(!array_key_exists($record_name, self::$record_definitions)) {
			$class = new ReflectionClass($record_name);
			
			$overridden_properties = array();
			
			foreach($class->getProperties() as $property) {
				$overridden_property = &$overriden_properties[$property->getName()];
				
				$annotations = self::parse_docblock($property->getDocComment());
				
				if(isset($annotations['get'])) {
					$overridden_property[self::PROP_GETTER] = $annotations['get'];
				}
				
				if(isset($annotations['set'])) {
					$overridden_property[self::PROP_SETTER] = $annotations['set'];
				}
			}
		}
	}
	
	private $_properties = array();
	
	private function initialize_public_properties() {
		$record_name = get_class($this);
		
		$properties = self::get_overridden_properties($record_name);
		
		$default_settings = array(
			self::PROP_GETTER => '__generic_get',
			self::PROP_SETTER => '__generic_set'
		);
		
		foreach($properties as $property => $settings) {
			$default_value = $this->$property;
			
			unset($this->$property);
			
			$this->__properties[$property] = array_merge($settings, $default_settings);
			
			$this->__properties[$property][self::PROP_VALUE] = $default_value;
		}
	}
	
	public function __construct() {
		$this->initialize_public_properties();
	}
	
	public function __get($property) {
		if(!array_key_exists($property, $this->__properties)) {
			throw new LogicException("Property $property does not exist");
		}
		
		return call_user_func(array($this, $this->__properties[$property][self::PROP_GETTER]), $property);
	}
	
	public function __set($property, $value) {
		if(!array_key_exists($property, $this->__properties)) {
			throw new LogicException("Property $property does not exist");
		}
		
		call_user_func(array($this, $this->__properties[$property][self::PROP_SETTER]), $value, $property);
	}
	
	private function __generic_get($property) {
		return $this->__properties[$property][self::PROP_VALUE];
	}
	
	private function __generic_set($value, $property) {
		$this->__properties[$property][self::PROP_VALUE] = $value;
	}
}

$y = new ReflectionProperty('Test', 'uren');
echo $y->getDocComment();