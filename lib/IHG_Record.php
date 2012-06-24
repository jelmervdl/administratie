<?php
abstract class IHG_Record {
	
	const SERIALIZED_FLAG = '%serialized%';
	
	const INSERT_QUERY = 'insert';

	const SELECT_QUERY = 'select';
	
	const UPDATE_QUERY = 'update';
	
	const DELETE_QUERY = 'delete';
	
	private static $_date_indicators = array('_date', 'datum', '_tijd', '_time');
	
	private $_pdo;
	
	private $_is_dirty = true;
	
	private $_property_values = array();
	
	private $_properties; // cache voor IHG_Record::properties() aanroepen
		
	abstract protected function _properties();
	
	protected function _table_name($query_type) {
		return get_class($this);
	}
	
	protected function _record_type_class($record_type) {
		return $record_type;
	}
	
	protected function _validate() {
		throw new LogicException('To make a Record saveable, you need to implement the _validate method.');
	}
	
	private function _generate_selector_atom() {
		$sql_columns = array();
		$sql_values = array();
		
		foreach($this->__properties() as $key => $property) {
			if(is_int($key)) {
				$sql_columns[] = sprintf('%s.%s AS %2$s',
					$this->_table_name(self::SELECT_QUERY),
					$property);
			} /*elseif($property instanceof IHG_SQL_Join_Interface) {
				
				$join_condition_atom = $this->_generate_condition_atom($property->join_conditions(), self::SELECT_QUERY);
				
				$sql_columns[] = sprintf('(SELECT %s FROM %s AS %s WHERE %s) AS %s',
					$property->sql_atom(),
					$property->join_table_name(),
					$property->join_table_alias(),
					$join_condition_atom->sql_atom(),
					$key);
				$sql_values += array_merge($property->bound_values(), $join_condition_atom->bound_values());
			} */elseif($property instanceof IHG_SQL_Atom_Interface) {
				$sql_columns[] = sprintf('%s AS %s',
					$property->sql_atom(),
					$key);
				$sql_values += $property->bound_values();
			} else {
				throw new InvalidArgumentException('Unexpected key->value combination. Value has to be an object that implements the IHG_SQL_Atom_Interface');
			}
		}
		
		return new IHG_SQL_Atom("\t" . implode(", \n\t", $sql_columns), $sql_values);
	}
	
	private function _generate_condition_atom(array $conditions = null, $query_type) {
		
		if(!$conditions) {
			return new IHG_SQL_Atom('1'); // geen voorwaarden <=> altijd waar :)
		}
		
		$sql_atoms = array();
		$sql_values = array();
		
		foreach($conditions as $column => $value) {
			// field => array(value, value, IHG_SQL_ATOM(value), value)
			// => field IN(?, ?, expression(?), ?)
			if(is_array($value)) {
				$sql_value_placeholders = array();
				foreach($value as $possible_value) {
					if($possible_value instanceof IHG_SQL_Atom_Interface) {
						$sql_value_placeholders[] = $possible_value->sql_atom();
						$sql_values = array_merge($sql_values, $possible_value->bound_values());
					} else {
						$sql_value_placeholders[] = '?';
						$sql_values[] = $possible_value;
					}
				}
				
				$sql_atoms[] = sprintf('%s.%s IN(%s)',
					$this->_table_name($query_type),
					$column,
					implode(', ', $sql_value_placeholders)
				);
			}
			// IHG_SQL_Atom(sql expression ?)
			// => expression(?)
			elseif(is_int($column) && $value instanceof IHG_SQL_Atom_Interface) {
				$sql_atoms[] = sprintf('(%s)', $value->sql_atom());
				$sql_values = array_merge($sql_values, $value->bound_values());
			}
			// field = IHG_SQL_ATOM(expression(?))
			// => field IN(expression(?))
			elseif($value instanceof IHG_SQL_Atom_Interface) {
				$sql_atoms[] = sprintf('%s.%s IN(%s)',
					$this->_table_name($query_type),
					$column,
					$value->sql_atom()
				);
				$sql_values = array_merge($sql_values, $value->bound_values());
			}
			// field = IHG_RECORD(value)
			// => field_id = ?(value.id)
			elseif($value instanceof IHG_Record && $this->_property_exists($column . '_id')) {
				$sql_atoms[] = sprintf('%s.%s_id = ?',
					$this->_table_name($query_type),
					$column
				);
				$sql_values[] = $value->_get_property('id');
			}
			// field => null
			// => field IS NULL
			elseif($value === null) {
				$sql_atoms[] = sprintf('%s.%s IS NULL',
					$this->_table_name($query_type),
					$column);
			}
			// field => value
			// => field = ?(value)
			else {
				$sql_atoms[] = sprintf('%s.%s = ?',
					$this->_table_name($query_type),
					$column);
				$sql_values[] = $value;
			}
		}
		
		return new IHG_SQL_Atom("\n\t" . implode("\t AND \n", $sql_atoms), $sql_values);
	}

	private function _generate_group_by_atom() {
		
		$properties = $this->__properties();
		
		$column = array_key_exists('id', $properties) ? $properties['id'] : new IHG_SQL_Atom('id');
		
		$column->prepend_table_name($this->_table_name(self::SELECT_QUERY));
		
		return $column;
	}
	
	private function __properties() {
		if(!$this->_properties) {
			$this->_properties = $this->_properties();
		}
		
		return $this->_properties;
	}
	
	private function _property_exists($key) {
		return is_int(array_search($key, $this->__properties())) || array_key_exists($key, $this->__properties());
	}
	
	private function _property_isset($key) {
		return $this->_property_exists($key) && isset($this->_property_values[$key]);
	}
	
	private function _get_property($key) {
		if(!in_array($key, $this->__properties()) && !array_key_exists($key, $this->__properties())) {
			trigger_error("Property $key does not exist", E_USER_NOTICE);
			return null;
		}

		if(!isset($this->_property_values[$key])) {
			return null;
		}

		return $this->_property_values[$key];
	}
	
	private function _set_property($key, $value) {
		if(!is_int(array_search($key, $this->__properties()))) {
			trigger_error("Writable property $key does not exist. Property ignored", E_USER_NOTICE);
			return null;
		}
		
		if(!isset($this->_property_values[$key]) || $this->_property_values[$key] != $value) {
			$this->_is_dirty = true;
		}

		$this->_property_values[$key] = $value;
		
		return $value;
	}
		
	public function __get($key) {
		
		if(method_exists($this, $key)) {
			return $this->$key();
		} elseif(!$this->_property_exists($key) && $this->_property_exists($key . '_id') && class_exists($this->_record_type_class($key))) {
			
			if($this->_property_isset($key . '_id')) {
				$value = self::find_record($this->pdo(), $this->_record_type_class($key), $this->_get_property($key . '_id'));
			} else {
				$value = null;
			}
		} else {
			$value = $this->_get_property($key);
		
			if ($this->_is_datetime_field($key))
			{
				try {
					return $value !== null ? new IHG_DateTime($value) : null;
				} catch(Exception $e) {
					return $value;
				}
			}
			elseif(substr($key, -3, 3) == '_id') {
				$record_name = substr($key, 0, -3);
			
			
			}
			elseif(strpos($value, self::SERIALIZED_FLAG) === 0) {
				$unserialized_value = unserialize(substr($value, strlen(self::SERIALIZED_FLAG)));
	
				if($unserialized_value) {
					$value = $unserialized_value;
				}
	
				unset($unserialized_value);
				
				if($value instanceof IHG_Record) {
					$value->bind_pdo($this->pdo());
				}
			}
		}
		
		return $value;
	}
	
	public function __set($key, $value)
	{
		if ($this->_is_readonly_field($key))
			throw new LogicException('Sorry, but the property is read-only');

		elseif ($value instanceof DateTime && $this->_is_datetime_field($key))
			$value = $value->format('Y-m-d H:i:s');
		
		elseif ($this->_property_exists($key . '_id') &&
		  ($value instanceof IHG_Record || $value === null))
		{
			if ($value && !$value->_property_isset('id'))
				throw new InvalidArgumentException('Only a previously saved IHG_Record instances can start a relationship');
			
			$key .= '_id';
			$value = $value === null ? null : $value->_get_property('id');
		}

		/*
		// This functionality isn't used, is it? It should not be I think..
		elseif(is_object($value)) {
			
			// Omdat IHG_Record een gebonden PDO heeft,
			// wil dat nog wel eens mis gaan met serializen.
			if($value instanceof IHG_Record) {
				$value = clone $value;
				$value->_pdo = null;
			}
			
			$value = self::SERIALIZED_FLAG . serialize($value);
		}
		*/
		
		$this->_set_property($key, $value);
	}
	
	public function __isset($key) {
		return $this->_property_isset($key);
	}
	
	public function __unset($key) {
		if($key == 'id') {
			throw new LogicException('Sorry, but the "id" property is read-only');
		}
		
		$this->_set_property($key, null);
	}
	
	protected function _is_readonly_field($key)
	{
		return $key == 'id';
	}

	protected function _is_datetime_field($key)
	{
		return $this->_contains_date_indicator($key);
	}

	private function _contains_date_indicator($key) {
		foreach(self::$_date_indicators as $indicator) {
			$pos = strrpos($key, $indicator);
			if($pos !== false && strlen($key) - $pos == strlen($indicator)) {
				return true;
			}
		}
		
		return false;
	}
	
	public function is_new() {
		return !$this->id;
	}
	
	public function is_dirty() {
		return (bool) $this->_is_dirty;
	}
	
	public function is_valid() {
		return count($this->_validate()) === 0;
	}
	
	public function is_equal_to($record) {
		return get_class($record) == get_class($this) && $record->id == $this->id;
	}
	
	public function save() {
		if(!$this->is_valid()) {
			throw new IHG_Record_Exception('Could not save the object: Some data in the object is invalid', $this->_validate());
		}
		
		if($this->is_dirty()) {
			if(empty($this->_property_values['id'])) {
				$guid = $this->_insert_record();
				$this->_set_property('id', $guid);
			} else {
				$this->_update_record();
			}
			
			$this->_is_dirty = false;
		}
		
		return true;
	}
	
	public function delete()
	{
		if (empty($this->_property_values['id']))
			throw new IHG_Record_Exception('Cannot delete an unsaved object');
		
		$stmt = $this->pdo()->prepare(
			sprintf("UPDATE %s SET deleted = NOW() WHERE id = ?",
				$this->_table_name(self::UPDATE_QUERY)));
		
		$stmt->execute(array($this->_get_property('id')));
		
		return true;
	}
	
	private function _insert_record() {
		
		$sql_values = array();
		$sql_assignments = array();
		
		foreach($this->__properties() as $id => $property) {
			if(is_int($id) && $property != 'id' && $this->_get_property($property) !== null) {
				var_dump($property, $this->_get_property($property));
				$sql_assignments[] = $property;
				$sql_values[] = $this->_get_property($property);
			}
		}
		
		$stmt = $this->pdo()->prepare(sprintf('INSERT INTO %s (%s) VALUES (%s)',
			$this->_table_name(self::INSERT_QUERY),
			implode(', ', $sql_assignments),
			str_repeat('?, ', count($sql_assignments) - 1) . '?'
		));
		
		$stmt->execute($sql_values);
		
		return (int) $this->pdo()->lastInsertId();
	}
	
	private function _update_record() {
		
		$sql_values = array();
		$sql_assignments = array();
		
		foreach($this->__properties() as $id => $property) {
			if(is_int($id) && $property != 'id') {
				$sql_assignments[] = $property . ' = ?';
				$sql_values[] = $this->_get_property($property);
			}
		}
		
		$stmt = $this->pdo()->prepare(sprintf("UPDATE %s SET %s WHERE id = ? AND deleted IS NULL",
			$this->_table_name(self::UPDATE_QUERY),
			implode(', ', $sql_assignments)
		));
		
		array_push($sql_values, $this->_get_property('id'));
				
		return $stmt->execute($sql_values);
	}
	
	public function bind_pdo(PDO $pdo) {
		$this->_pdo = $pdo;
	}
	
	protected function pdo() {
		if(isset($this->_pdo)) {
			return $this->_pdo;
		} else {
			throw new LogicException('No PDO bound to this object');
		}
	}
		
	static public function create_record(PDO $pdo, $record_type, array $data = array(), $is_dirty = true) {
		$record = new $record_type();
		
		$record->_property_values = $data;
		
		$record->_is_dirty = (bool) $is_dirty;
		
		$record->bind_pdo($pdo);
		
		return $record;
	}
	
	static public function find_record(PDO $pdo, $record_type, $conditions) {
		$dummy_record = new $record_type();
		
		$selector_atom = $dummy_record->_generate_selector_atom();
		$group_by_atom = $dummy_record->_generate_group_by_atom();
		
		if($conditions instanceof IHG_SQL_Atom_Interface) {
			$condition_atom = $conditions;
		} else {
			if(!is_array($conditions)) {
				$conditions = array('id' => $conditions);
			}
			
			$condition_atom = $dummy_record->_generate_condition_atom($conditions, self::SELECT_QUERY);
		}
		
		$stmt = $pdo->prepare(sprintf("SELECT %s \nFROM \n\t%s \nWHERE %s AND deleted IS NULL \nGROUP BY %s \nLIMIT 1",
			$selector_atom->sql_atom(),
			$dummy_record->_table_name(self::SELECT_QUERY),
			$condition_atom->sql_atom(),
			$group_by_atom->sql_atom()));
		
		$stmt->execute($selector_atom->bound_values()
			+ $condition_atom->bound_values()
			+ $group_by_atom->bound_values());
		
		$results = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if(!$results) return null;
		
		$dummy_record->_property_values = $results;
		
		$dummy_record->_is_dirty = false;
		
		$dummy_record->bind_pdo($pdo);
		
		return $dummy_record;
	}
	
	static public function find_records(PDO $pdo, $record_type, array $conditions = null) {
		
		$dummy_record = new $record_type();
		
		$selector_atom  = $dummy_record->_generate_selector_atom();
		$condition_atom = $dummy_record->_generate_condition_atom($conditions, self::SELECT_QUERY);
		$group_by_atom = $dummy_record->_generate_group_by_atom();
		
		$stmt = new IHG_SQL_Atom(sprintf("SELECT %s \nFROM \n\t%s \nWHERE %s AND deleted IS NULL \nGROUP BY %s",
			$selector_atom->sql_atom(),
			$dummy_record->_table_name(self::SELECT_QUERY),
			$condition_atom->sql_atom(),
			$group_by_atom->sql_atom()),
			array_merge(
				$selector_atom->bound_values(),
				$condition_atom->bound_values(),
				$group_by_atom->bound_values()
			));
			
		return new IHG_Record_Set($pdo, $record_type, $stmt);
		
		/*
		$stmt = $pdo->prepare(sprintf('SELECT %s FROM %s WHERE %s GROUP BY %s',
			$selector_atom->sql_atom(),
			$dummy_record->_table_name(),
			$condition_atom->sql_atom(),
			$group_by_atom->sql_atom()));
		
		$stmt->execute($selector_atom->bound_values()
			+ $condition_atom->bound_values()
			+ $group_by_atom->bound_values());
		
		$records = new ArrayObject();
		
		while($result_set = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$record = clone $dummy_record;
			$record->_property_values = $result_set;
			$record->_is_dirty = false;
			$record->bind_pdo($pdo);
			$records->append($record);
		}
		
		return $records;
		*/
	}

	static public function table_name_for_record($record_type, $query_type) {
		$dummy_record = new $record_type();
		return $dummy_record->_table_name($query_type);
	}
	
	static public function properties_for_record($record_type) {
		$dummy_record = new $record_type();
		return $dummy_record->_properties();
	}
}

class IHG_Record_Exception extends Exception {
	
	public $errors;
	
	public function __construct($message, array $errors = null) {
		$this->errors = $errors;
		
		parent::__construct($message);
	}
	
}

?>