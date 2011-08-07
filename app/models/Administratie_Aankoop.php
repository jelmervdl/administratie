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
	
	public function details()
	{
		if (substr($this->id, 0, 3) == 'uur')
			return self::find_records($this->pdo(),
				'Administratie_Uur',
				array(
					'aankopenoverzicht_id' => $this->id
				));
		else
			return new EmptyIterator();
	}
}