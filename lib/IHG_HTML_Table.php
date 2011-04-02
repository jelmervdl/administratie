<?php

class IHG_HTML_Table implements IHG_View_Interface {
	
	protected $_available_columns;
	
	protected $_preferred_columns = array();
	
	protected $_data;
	
	public function __construct($record_type) {
		if($record_type instanceof IHG_Record_Provider) {
			$record_type = $record_type->record_type();
		}
		
		if(is_string($record_type)) {
			$this->_available_columns = array();
			
			foreach(IHG_Record::properties_for_record($record_type) as $key => $value) {
				$this->_available_columns[] = array(
					is_int($key) ? $value : $key,
					is_int($key) ? $value : $key,
					array($this, '_default_formatter'),
					null);
			}
		}
		
		if(!$this->_available_columns) {
			throw new InvalidArgumentException('IHG_HTML_Table::__construct requires the first argument to be the name or the provider of a record type. ' . gettype($record_type) . ' was given');
		}
	}
	
	public function add_column($property_name, $label = null, $formatter = null, $summary = null) {
		$this->_preferred_columns[] = array(
			$property_name,
			$label !== null ? $label : $property_name,
			$formatter ? $formatter : array($this, '_default_formatter'),
			$summary);
		
		return $this;
	}
	
	public function set_data($data) {
		$this->_data = $data;
		
		return $this;
	}
	
	public function _default_formatter($x) {
		if($x instanceof DateTime) {
			$x = $x->format('d/m/Y');
		}
		
		return htmlentities($x, ENT_QUOTES, 'UTF-8');
	}
	
	public function is_embedded() {
		return true;
	}
	
	public function draw() {
		if(count($this->_preferred_columns) === 0) {
			$columns = $this->_available_columns;
		} else {
			$columns = $this->_preferred_columns;
		}
		
		$has_summaries = false;
		foreach ($columns as $column)
		{
			if ($column[3])
			{
				$has_summaries = true;
				break;
			}
		}
		
		$n = "\n";
		
		echo '<table>', $n;
		echo '	<thead>', $n;
		echo '		<tr>', $n;
		foreach($columns as $column):
		echo '			<th>' . $column[1] . '</th>', $n;
		endforeach;
		echo '		</tr>', $n;
		echo '	</thead>', $n;
		echo '	<tbody>', $n;
		foreach($this->_data as $object):
		echo '		<tr>', $n;
		foreach($columns as $column):
		echo '			<td>' . call_user_func($column[2], $object->{$column[0]}, $object) . '</td>', $n;
		endforeach;
		echo '		</tr>', $n;
		endforeach;
		echo '	</tbody>', $n;
		if ($has_summaries):
		echo '	<tfoot>', $n;
		echo '		<tr>', $n;
		foreach ($columns as $column):
		if ($column[3]):
			echo '			<td>' . call_user_func($column[2], call_user_func($column[3], $this->_pluck_data($column[0]))) . '</td>', $n;
		else:
			echo '			<td></td>', $n;
		endif;
		endforeach;
		echo '		</tr>', $n;
		echo '	</tfoot>', $n;
		endif;
		echo '</table>', $n;
	}
	
	protected function _pluck_data($attribute)
	{
		$values = array();
		
		foreach ($this->_data as $object)
			$values[] = $object->{$attribute};
		
		return $values;
	}
	
	static public function default_decorator() {
		return array($this, '_default_formatter');
	}
	
	static public function checkbox_decorator($element_name) {
		return array(new IHG_HTML_Decorator_Checkbox($element_name), 'decorate');
	}
}

?>