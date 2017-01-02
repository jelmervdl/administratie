<?php

class Administratie_Aankoop extends Administratie_Record {
	protected function _table_name($query_type) {
		return 'AankopenOverzicht';
	}
	
	protected function _properties($query_type) {
		return array(
			'id',
			'beschrijving',
			'aantal',
			'prijs',
			'bedrijf_id',
			'factuur_id'
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