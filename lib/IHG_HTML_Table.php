<?php

interface IHG_HTML_Table_Grouper
{
	public function has_header($group);
	
	public function format_header($group);
	
	public function classify($object);
}

class IHG_HTML_Table_DefaultGrouper implements IHG_HTML_Table_Grouper
{
	public function has_header($group)
	{
		return false;
	}
	
	public function format_header($group)
	{
		return "";
	}
	
	public function classify($object)
	{
		return "default";
	}
}

class IHG_HTML_Table implements IHG_View_Interface {
	
	protected $_available_columns;
	
	protected $_preferred_columns = array();
	
	protected $_data;
	
	protected $_walker;
	
	protected $_grouper;
	
	const PROPERTY_NAME = 0;
	
	const LABEL = 1;
	
	const FORMATTER = 2;
	
	const SUMMARIZER = 3;
	
	public function __construct($record_type) {
		if($record_type instanceof IHG_Record_Provider) {
			$record_type = $record_type->record_type();
		}
		
		if(is_string($record_type)) {
			$this->_available_columns = array();
			
			foreach(IHG_Record::properties_for_record($record_type) as $key => $value) {
				$this->_available_columns[] = array(
					self::PROPERTY_NAME => is_int($key) ? $value : $key,
					self::LABEL 		=> is_int($key) ? $value : $key,
					self::FORMATTER		=> array($this, '_default_formatter'),
					self::SUMMARIZER	=> null);
			}
		}
		
		if(!$this->_available_columns) {
			throw new InvalidArgumentException('IHG_HTML_Table::__construct requires the first argument to be the name or the provider of a record type. ' . gettype($record_type) . ' was given');
		}
		
		$this->_grouper = new IHG_HTML_Table_DefaultGrouper();
	}
	
	public function add_column($property_name, $label = null, $formatter = null, $summary = null) {
		$this->_preferred_columns[] = array(
			self::PROPERTY_NAME => $property_name,
			self::LABEL 		=> $label !== null ? $label : $property_name,
			self::FORMATTER		=> $formatter ? $formatter : array($this, '_default_formatter'),
			self::SUMMARIZER	=> $summary);
		
		return $this;
	}
	
	public function set_data($data) {
		$this->_data = $data;
		
		return $this;
	}
	
	public function set_walker($walker) {
		if (!is_callable($walker))
			throw new InvalidArgumentException("Walker function is not callable");
		
		$this->_walker = $walker;
		
		return $this;
	}
	
	public function set_grouper(IHG_HTML_Table_Grouper $grouper)
	{
		$this->_grouper = $grouper;
		
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
	
	public function draw()
	{
		$columns = count($this->_preferred_columns)
			? $this->_preferred_columns
			: $this->_available_columns;
		
		$has_summaries = false;
		
		foreach ($columns as $column)
		{
			if ($column[self::SUMMARIZER])
			{
				$has_summaries = true;
				break;
			}
		}
		
		$data = array();
		
		foreach ($this->_data as $object)
		{
			$group = $this->_grouper->classify($object);
			$data[$group][] = $this->_format_row_recursive($object, $columns, 0);
		}
		
		$n = "\n";
		
		echo '<table>', $n;
		
		echo '	<thead>', $n;
		echo '		<tr>', $n;
		foreach($columns as $column):
		echo '			<th class="' .$column[self::PROPERTY_NAME]. '">' . $column[self::LABEL] . '</th>', $n;
		endforeach;
		echo '		</tr>', $n;
		echo '	</thead>', $n;
		
		foreach ($data as $group => $rows):
		echo '	<tbody' . ($this->_grouper->has_header($group) ?  ' class="group"' : '') . '>', $n;
		if ($this->_grouper->has_header($group)):
		echo '		<tr class="group-header">', $n;
		echo '			<th colspan="' . count($columns) . '">' . $this->_grouper->format_header($group) . '</th>', $n;
		echo '		</tr>', $n;
		endif;
		foreach ($rows as $row)
			echo $row;
		echo '	</tbody>', $n;
		endforeach;
		
		if ($has_summaries && count($data)):
		echo '	<tfoot>', $n;
		echo '		<tr>', $n;
		foreach ($columns as $column):
		if ($column[3]):
			echo '			<td class="' .$column[self::PROPERTY_NAME]. '">' . call_user_func($column[self::FORMATTER], call_user_func($column[self::SUMMARIZER], $this->_pluck_data($column[self::PROPERTY_NAME]))) . '</td>', $n;
		else:
			echo '			<td></td>', $n;
		endif;
		endforeach;
		echo '		</tr>', $n;
		echo '	</tfoot>', $n;
		endif;
		echo '</table>', $n;
	}
	
	protected function _format_row_recursive($object, $columns, $level)
	{
		$n = "\n";
		
		$data = "\t\t<tr class=\"level-{$level}\">\n";

		foreach($columns as $column)
			$data .= "\t\t\t<td class=\"{$column[self::PROPERTY_NAME]}\">"
				.  call_user_func($column[self::FORMATTER], $object->{$column[self::PROPERTY_NAME]}, $object)
				. "</td>\n";

		$data .= "\t\t</tr>\n";
		
		if ($this->_walker)
		{
			$children = call_user_func($this->_walker, $object);
		
			foreach ($children as $child)
				$data .= $this->_draw_row_recursive($child, $columns, $level + 1);
		}
		
		return $data;
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