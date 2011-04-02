<?php

class IHG_Form_Helper extends IHG_Component_Abstract
{
	protected $_helper_element;
	
	public function __construct()
	{
		$this->_helper_element = new IHG_Form_Helper_Element();
	}
	
	public function hidden($name, $value)
	{
		return sprintf('<input type="hidden" name="%s" value="%s">' . "\n",
			self::_escape($name), self::_escape($value));
	}
	
	public function controller($controller, $method)
	{
		return $this->hidden('_controller',
			base64_encode($controller . '::' . $method));
	}
	
	public function origin()
	{
		return $this->hidden('_origin',
			isset($_SERVER['HTTP_X_ORIGIN'])
				? $_SERVER['HTTP_X_ORIGIN']
				: $_SERVER['REQUEST_URI']);
	}
	
	public function textfield($name, $value, &$errors = null)
	{
		return $this->_helper_element->reset_with(
			'<input type="text" %s value="%s">%s',
			$name, $value, $errors)->add_class('textfield');
	}
	
	public function textarea($name, $value, &$errors = null)
	{
		return $this->_helper_element->reset_with(
			'<textarea %s>%s</textarea>%s',
			$name, $value, $errors);
	}
	
	public function checkbox($name, $value, &$errors = null)
	{
		$field = sprintf('<input type="checkbox" name="%s" id="%1$s" value="true" %s>',
			$name, $value ? 'checked="checked"' : '');
		
		$field .= self::_error_decorator($name, $errors);
		
		return $field;
	}
	
	public function datepicker($name, $value, &$errors = null)
	{
		return $this->_helper_element->reset_with(
			'<input type="text" %s value="%s">%s',
			$name, $value->format('d-m-Y H:i'), $errors)->add_class('datepicker');
		
		return $field;
	}
	
	public function popupbutton($name, $options, $selected_option, &$errors = null, $decorator = null)
	{
		if(!$decorator) $decorator = array(get_class($this), '_escape');
		
		$field = sprintf('<select id="%s" name="%1$s">', $name);
		
		foreach($options as $option) {
			$field .= sprintf('<option value="%s"%s>%s</option>',
				$option->id,
				ihg_is_equal($option, $selected_option) ? ' selected="selected"' : '',
				call_user_func($decorator, $option));
		}
		
		$field .= '</select>';
		
		$field .= self::_error_decorator($name, $errors);
		
		return $field;
	}
	
	static protected function _error_decorator($name, &$errors)
	{
		if($errors && isset($errors[$name])) {
		 	return sprintf('<span class="invalid tip">%s</span>', self::_escape($errors[$name]));
		}
	}
	
	static protected function _escape($raw)
	{
		return htmlentities($raw, ENT_QUOTES,'UTF-8');
	}
}

class IHG_Form_Helper_Element
{
	protected $_name;
	
	protected $_value;
	
	protected $_errors;
	
	protected $_size;
	
	protected $_classnames;
	
	protected $_placeholder;
	
	public function reset_with($format, $name, $value, &$errors)
	{
		$this->_format = $format;
		$this->_name = $name;
		$this->_value = $value;
		$this->_errors = $errors;
		$this->_size = null;
		$this->_placeholder = null;
		$this->_classnames = array();
		
		return $this;
	}
	
	public function add_class($classname)
	{
		$this->_classnames[] = $classname;
		
		return $this;
	}
	
	public function set_size($size)
	{
		$this->_size = intval($size);
		
		return $this;
	}
	
	public function set_placeholder($placeholder)
	{
		$this->_placeholder = $placeholder;
		
		return $this;
	}
	
	public function __toString()
	{
		return $this->render();
	}
	
	public function render()
	{
		return sprintf($this->_format,
			$this->_render_attributes(),
			$this->_value,
			$this->_render_error());
	}
	
	protected function _render_attributes()
	{
		$attributes = array();
		
		$attributes[] = sprintf('id="%s" name="%1$s"', self::_escape($this->_name));
		
		if($this->_size) $attributes[] = sprintf('size="%d"', $this->_size);
		
		if($this->_classnames) $attributes[] = sprintf('class="%s"', implode(' ', $this->_classnames));
		
		if($this->_placeholder) $attributes[] = sprintf('data-placeholder="%s"', self::_escape($this->_placeholder));
		
		return implode(' ', $attributes);
	}
	
	protected function _render_error()
	{
		return $this->_errors && isset($this->_errors[$this->_name])
			? sprintf('<span class="invalid tip">%s</span>', self::_escape($this->_errors[$this->_name]))
			: '';
	}
	
	static protected function _escape($raw)
	{
		return htmlentities($raw, ENT_QUOTES,'UTF-8');
	}
}