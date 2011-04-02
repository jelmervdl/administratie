<?php

class IHG_SQL_Count extends IHG_SQL_Aggregate {
	public function __construct($record_name, array $binding_conditions) {
		parent::__construct($record_name, $binding_conditions);
	}
	
	public function sql_atom() {
		return sprintf('COUNT(*)');
	}
}
