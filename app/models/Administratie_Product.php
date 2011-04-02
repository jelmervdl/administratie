<?php

class Administratie_Product extends Administratie_Record {
	protected function _table_name() {
		return 'Producten';
	}
	
	protected function _properties() {
		return array(
			'id',
			'naam',
			'beschrijving',
			'prijs'
			);
	}
}