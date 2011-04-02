<?php
interface IHG_SQL_Join_Interface {
	public function join_table_name();
	public function join_table_alias();
	public function join_conditions();
}
