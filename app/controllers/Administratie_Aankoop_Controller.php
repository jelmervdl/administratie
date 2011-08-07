<?php

class Administratie_Aankoop_Controller extends IHG_Controller_Abstract
{
	public function factuur($factuur_id)
	{
		$data = $this->aankopen->find_all(array('factuur_id' => $factuur_id));
		
		$walker = function($aankoop) {
			if ($aankoop instanceof Administratie_Aankoop)
				return $aankoop->details();
			else
				return new EmptyIterator();
		};
		
		return $this->views->from_record($this->aankopen)
			->set_data($data)
			->set_walker($walker)
			//->add_column('bedrijf', 'Bedrijf', array($this, '_format_bedrijf'))
			->add_column('beschrijving', 'Beschrijving')
			->add_column('aantal', 'Aantal', function($aantal) { return number_format($aantal, 1); })
			->add_column('prijs', 'Prijs', function($prijs) { return '&euro;' . number_format($prijs, 2, ',', '.'); });
	}
}