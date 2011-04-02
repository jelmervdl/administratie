<?php

define('ORDER_DESC', 'DESC');

define('ORDER_ASC', 'ASC');

class IHG_Record_Set implements ArrayAccess, Iterator, Countable {
	
	protected $_index;
	
	protected $_last_fetch_index = -1;
	
	protected $_current = false;
	
	protected $_fetched_records = array();
	
	protected $_pdo;
	
	protected $_pdo_stmt;
	
	protected $_query;
	
	protected $_record_type;
	
	protected $_limit = self::INFINITE;
	
	protected $_offset = 0;
	
	const INFINITE = '18446744073709551610';
	
	public function __construct(PDO $pdo, $record_type, IHG_SQL_Atom $query) {
		
		$this->_pdo = $pdo;
		
		$this->_record_type = $record_type;
		
		$this->_query = $query;
		
		$this->rewind();
	}
	
	public function current() {
		return $this->_current;
	}
	
	public function key() {
		return $this->_index + $this->_offset;
	}
	
	public function next() {
		$this->_index++;
	}
	
	public function rewind() {
		$this->_index = 0;
	}
	
	public function valid() {
		return ($this->_current = $this->_fetch()) !== false;
	}
	
	
	public function offsetExists($index) {
		return $this->offsetGet($index) !== false;
	}
	
	public function offsetGet($index) {
		$old_index = $this->_index;
		
		$this->_index = $index + $this->_offset;
		
		$this->valid();
		
		$current = $this->current();
		
		$this->_index = $old_index;
		
		return $current;
	}
	
	public function offsetSet($index, $value) {
		throw new BadMethodCallException('IHG_Record_Set is read-only');
	}
	
	public function offsetUnset($index) {
		throw new BadMethodCallException('IHG_Record_Set is read-only');
	}
	
	
	public function getIterator() {
		return $this;
	}
	
	
	public function count() {
		$sql = $this->_query->sql_atom();
		
		$sql = preg_replace('{LIMIT\s+[0-9]+(?:\sOFFSET\s+[0-9]+)*\s*$}i', '', $sql);
		
		$sql = preg_replace_callback('{SELECT\s.*\sFROM}i', array($this, '_count_placeholder_callback'), $sql);
		$bound_values = array_slice($this->_query->bound_values(), $this->_placeholder_count);
		
		$sql = preg_replace('{ORDER\sBY\s(.+?)\s*$}i', '', $sql);
		
		$stmt = $this->_pdo->prepare($sql);
		
		$stmt->execute($bound_values);
		
		$result = $stmt->fetch(PDO::FETCH_NUM);
		
		if($result === false) {
			return false;
		}
		
		$row_count = $result[0] - $this->_offset;
		
		return $this->_limit !== null ? min($row_count, $this->_limit) : $row_count;
	}
	
	public function sum($property_path) {
		$total = 0;
		
		foreach($this as $object) {
			$total += self::_keypath($object, $property_path);
		}
		
		return $total;
	}
	
	public function _count_placeholder_callback($matches) {
		$this->_placeholder_count = substr_count($matches[0], '?');
		
		return 'SELECT COUNT(*) FROM';
	}
	
	
	public function slice($offset, $limit = null) {
		$set = new self($this->_pdo, $this->_record_type, $this->_query);
		
		$set->_limit = $limit === null ? null : (int) $limit;
		
		$set->_offset = $offset === null ? null : (int) $offset;
		
		return $set;
	}
	
	public function sort($keypath, $order = ORDER_ASC) {
		
		/* Not implemented. This really requires to re-implement the whole 
		 * SQL_Atom idea since it is no use mixing queries and subqueries
		 * and hoping a preg_replace will do the job right.
		 */
		
		$values = array();
		
		foreach ($this as $record)
			$values[] = self::_keypath($record, $keypath);
		
		$this->rewind();
		
		// remove terminating 'false'
		unset($this->_fetched_records[count($this->_fetched_records) - 1]);
		
		array_multisort(
			$values,
			$order == ORDER_ASC ? SORT_ASC : SORT_DESC,
			$this->_fetched_records);
		
		// put terminating 'false' back at the back.
		$this->_fetched_records[count($this->_fetched_records)] = false;
		
		return $this;
	}
	
	protected function _run_query() {
		
		$sql = preg_replace('{LIMIT\s+[0-9]+(?:\sOFFSET\s+[0-9]+)*\s*$}i', '', $this->_query->sql_atom());
		
		if($this->_limit !== null || $this->_offset !== null) {
			$sql = sprintf('%s LIMIT %s OFFSET %d', $sql, 
				$this->_limit === null ? self::INFINITE : (int) $this->_limit,
				$this->_offset);
		}
		
		$this->_pdo_stmt = $this->_pdo->prepare($sql);
		
		$this->_pdo_stmt->execute($this->_query->bound_values());
	}
	
	protected function _fetch() {
		if(!$this->_pdo_stmt) {
			$this->_run_query();
		}
		
		while($this->_index - $this->_last_fetch_index > 0) {
			
			$index = $this->_last_fetch_index + 1;
			
			$data = $this->_pdo_stmt->fetch(PDO::FETCH_ASSOC);
			
			$this->_last_fetch_index = $index;
			
			if(!$data) {
				$this->_fetched_records[$index] = false;
				break;
			} else {
				$this->_fetched_records[$index] = call_user_func_array(
					array(
						$this->_record_type,
						'create_record'),
					array(
						$this->_pdo,
						$this->_record_type,
						$data,
						false));
			}
		}
		
		return ifsetor($this->_fetched_records[$this->_index], false);
	}
	
	static protected function _keypath($object, $path) {
		if(!is_array($path)) {
			$path = preg_split('/[\s\.]*\[([^\]]+)\]\s*|\.+/', $path, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		}

		while(($step = array_shift($path)) !== null) {
			if(!isset($object->$step)) {
				return null;
			}

			$object = $object->$step;
		}

		return $object;
	}
}

?>