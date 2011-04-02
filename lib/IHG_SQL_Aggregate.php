<?php

abstract class IHG_SQL_Aggregate implements IHG_SQL_Atom_Interface, IHG_SQL_Join_Interface {
	
	protected $table_name;
	
	protected $table_alias;
	
	protected $binding_conditions;
	
	public function __construct($record_name, array $binding_conditions) {
		$this->table_name = IHG_Record::table_name_for_record($record_name);
		$this->table_alias = uniqid($this->table_name);
		
		foreach($binding_conditions as $key => $value) {
			if(is_array($value)) {
				foreach($value as $sub_value) {
					if($sub_value instanceof IHG_SQL_Atom) {
						$sub_value->prepend_table_name($this->table_alias);
					}
				}
			} else {
				if($value instanceof IHG_SQL_Atom) {
					$value->prepend_table_name($this->table_alias);
				}
			}
		}
		
		$this->binding_conditions = $binding_conditions;
	}
	
	//abstract public function sql_atom();
	
	public function bound_values() {
		return array();
	}
	
	public function join_table_name() {
		return $this->table_name;
	}
	
	public function join_table_alias() {
		return $this->table_alias;
	}
	
	public function join_conditions() {
		return $this->binding_conditions;
	}
}
