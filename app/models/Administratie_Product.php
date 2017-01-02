<?php

class Administratie_Product extends Administratie_Record {
	protected function _table_name($query_mode) {
		return 'Producten';
	}
	
	protected function _properties($query_mode) {
		return array(
			'id',
			'naam',
			'beschrijving',
			'prijs'
			);
	}
}