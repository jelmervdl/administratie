<?php

class Administratie_Bedrijf extends Administratie_Record
{
	protected function _table_name() {
		return 'Bedrijven';
	}
		
	protected function _properties() {
		return array(
			'id',
			'naam',
			'url'
			);
	}
	
	protected function _validate() {
		$errors = array();
		
		if(empty($this->naam))
			$errors['naam'] = 'Een naam is verplicht';
		
		return $errors;
	}
	
	public function uren() {
		return self::find_records($this->pdo(), 'Administratie_Uur', array('bedrijf_id' => $this->id));
	}
	
	public function contactpersonen() {
		return self::find_records($this->pdo(), 'Administratie_Contactpersoon', array(
			'bedrijf_id' => $this->id,
			'vervangen_door' => null
			));
	}
}