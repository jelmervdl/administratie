<?php

class Administratie_Aankoop extends Administratie_Record {
	protected function _table_name() {
		return 'AankopenOverzicht';
	}
	
	protected function _properties() {
		return array(
			'id',
			'beschrijving',
			'aantal',
			'prijs',
			'btw',
			'bedrijf_id',
			'factuur_id',
			'prijs_incl' => new IHG_SQL_Atom('prijs + btw')
			);
	}
}