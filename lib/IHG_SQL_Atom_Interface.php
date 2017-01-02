<?php
interface IHG_SQL_Atom_Interface {
	public function sql_atom();
	public function bound_values();
	public function columns();
}
?>