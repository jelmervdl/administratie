<?php

class IHG_SQL_Sum extends IHG_SQL_Aggregate {

	protected $sum_atom;

	public function __construct($record_name, $sum_atom, array $binding_conditions) {
		parent::__construct($record_name, $binding_conditions);

		if(!$sum_atom instanceof IHG_SQL_Atom_Interface) {
			$sum_atom = new IHG_SQL_Atom($sum_atom);
		}

		$sum_atom->prepend_table_name($this->join_table_alias());
		
		$this->sum_atom = $sum_atom;
	}
	
	public function sql_atom() {
		return sprintf('SUM(%s)', $this->sum_atom->sql_atom());
	}
	
	public function bound_values() {
		return $this->sum_atom->bound_values();
	}
}
