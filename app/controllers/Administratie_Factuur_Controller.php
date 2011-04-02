<?php

class Administratie_Factuur_Controller extends IHG_Controller_Abstract {
	
	public function index() {
		$this->breadcrumbs->add_crumb('Facturen');
		
		return $this->views->from_file('administratie_factuur_index');
	}
	
	public function belasting_overzicht() {
		$this->breadcrumbs->add_crumb('Belasting');
		
		$facturen = $this->facturen->find_all(array(
			'aangegeven' => null));
		
		if($this->_is_post_request()) {
			foreach($facturen as $factuur) {
				if(in_array($factuur->nummer, $_POST['factuur_nummers'])) {
					$factuur->aangegeven = IHG_DateTime::from_string('now');
					$factuur->save();
				}
			}
		}
		
		$factuur_view = $this->views->from_record($this->facturen)
			->set_data($facturen)
			->add_column('nummer', '', IHG_HTML_Table::checkbox_decorator('factuur_nummers'))
			->add_column('nummer', '#', array($this, '_link_factuur'))
			->add_column('bedrijf', 'Bedrijf', create_function('$bedrijf', 'return $bedrijf->naam;'))
			->add_column('verzend_datum', 'Verzonden op')
			->add_column('btw', 'BTW', create_function('$btw', 'return sprintf("&euro; %0.2f", $btw);'));
		
		$prijs_totaal_view=$this->views->from_string(sprintf('Totaal prijs: &euro; %0.2f', $facturen->sum('prijs')));
		$btw_totaal_view = $this->views->from_string(sprintf('Totaal BTW: &euro; %0.2f', $facturen->sum('btw')));
		
		$view = $this->views->from_file('administratie_belasting');
		$view->facturen = $factuur_view;
		$view->prijs_totaal = $prijs_totaal_view;
		$view->btw_totaal = $btw_totaal_view;
		
		return $view;
	}
	
	public function openstaande_facturen($bedrijf_id = null) {
		$openstaande_facturen = $this->facturen->find_all(array(
			'voldaan' => null
			)
			+ ($bedrijf_id ? array(
				'bedrijf_id' => $bedrijf_id
			) : array()));
		
		return $this->views->from_record($this->facturen)
			->set_data($openstaande_facturen)
			->add_column('nummer', '#', array($this, '_link_factuur'))
			->add_column('bedrijf', 'Bedrijf', create_function('$bedrijf', 'return $bedrijf->naam;'))
			->add_column('termijn_resterend', 'Termijn', create_function('$x', 'return $x . " dagen";'))
			->add_column('verzend_datum', 'Verzonden op')
			->add_column('uiterste_betaal_datum', 'Uiterlijk betaald op');
	}
	
	public function list_facturen($bedrijf_id = null) {
		$facturen = $this->facturen->find_all($bedrijf_id !== null ? array('bedrijf_id' => $bedrijf_id) : array());
		
		return $this->views->from_record($this->facturen)
			->set_data($facturen)
			->add_column('nummer', '#', array($this, '_link_factuur'))
			->add_column('verzend_datum', 'Verzonden op')
			->add_column('uiterste_betaal_datum', 'Uiterlijk betaald op')
			->add_column('voldaan', 'Voldaan', array($this, '_format_voldaan'))
			->add_column('id', 'Download', array($this, '_link_factuur_pdf'));
	}
	
	public function factuur($factuur_id) {
		$factuur = $this->facturen->find($factuur_id);
		
		$bedrijf = $factuur->bedrijf;
		
		$this->breadcrumbs->add_crumb($bedrijf->naam,
			$this->router->link('Administratie_Bedrijf_Controller', 'bedrijf', $bedrijf->id));
		
		$this->breadcrumbs->add_crumb('Factuur ' . $factuur->nummer);
		
		$view = $this->views->from_file('administratie_factuur');
		$view->factuur = $factuur;
		return $view;
	}
	
	public function factuur_toevoegen($factuur_id = null, $bedrijf_id = null) {
		if($factuur_id) {
			$factuur = $this->facturen->find($factuur_id);
		} else {
			$factuur = $this->facturen->create();
		}
		
		$view = $this->views->from_file('administratie_factuur_toevoegen');
		
		if($bedrijf_id) {
			$factuur->bedrijf = $this->bedrijven->find($bedrijf_id);
		}
		
		$uren = $this->uren->find_all(array('factuur_id' => null) 
			+ ($bedrijf_id !== null ? array('bedrijf_id' => $bedrijf_id) : array()));
			
		if($this->_is_post_request()) {
			try {
				$factuur->bedrijf_id = $_POST['bedrijf_id'];
				$factuur->contactpersoon_id = $this->bedrijven->find($_POST['bedrijf_id'])->contactpersonen[0]->id;
				$factuur->project_naam = $_POST['project_naam'];
				$factuur->project_beschrijving = $_POST['project_beschrijving'];
				$factuur->verzend_datum = IHG_DateTime::from_string($_POST['verzend_datum']);
				$factuur->uiterste_betaal_datum = IHG_DateTime::from_string($_POST['uiterste_betaal_datum']);
				
				if(empty($_POST['uur'])) {
					$e = Exception('Geen uren geselecteerd');
					$e->errors = 'uren';
					throw $e;
				}
				
				if($factuur->save()) {
					foreach($_POST['uur'] as $uur_id) {
						$uur = $this->uren->find($uur_id);
						$uur->factuur = $factuur;
						$uur->save();
					}
				}
				
				$factuur = $this->facturen->create();
				$view = $this->views->reload();
			} catch(Exception $e) {
				$view->errors = $e->errors;
			}
		} else {
			if(empty($factuur->verzend_datum)) {
				$factuur->verzend_datum = IHG_DateTime::from_string('now');
			}
			
			if(empty($factuur->uiterste_betaal_datum)) {
				$factuur->uiterste_betaal_datum = IHG_DateTime::from_string('+4 weeks');
			}
			
			if(empty($factuur->project_naam) && $bedrijf_id !== null) {
				$oude_facturen = $this->facturen->find_all(array('bedrijf_id' => $bedrijf_id))->sort('id', ORDER_DESC);
				
				if($oude_facturen->valid()) {
					$factuur->project_naam = $oude_facturen->current()->project_naam;
					$factuur->project_beschrijving = $oude_facturen->current()->project_beschrijving;
				}
			}
		}
		
		$view->uren = $uren;
		$view->factuur = $factuur;
		
		return $view;
	}
	
	public function factuur_pdf($factuur_id) {
		$factuur = $this->facturen->find($factuur_id);
		
		$bedrijf = $factuur->bedrijf;
		
		$aankopen = $factuur->aankopen;
		
		$view = $this->views->from_file('administratie_factuur_pdf');
		$view->dont_embed();
		$view->factuur = $factuur;
		$view->bedrijf = $bedrijf;
		$view->contactpersoon = $factuur->contactpersoon;
		$view->aankopen = $aankopen;
		return $view;
	}
	
	public function factuur_mailen($factuur_id) {
		$factuur = $this->facturen->find($factuur_id);
		
		$view = $this->views->from_file('administratie_factuur_mailen');
		$view->factuur = $factuur;
		$view->contactpersoon = $factuur->contactpersoon;
		
		if ($this->_is_post_request())
		{
			
		}
		
		return $view;
	}
	
	public function _link_factuur($factuur_nr, $factuur) {
		return sprintf('<a href="%s">%s</a>',
			$this->router->link(__CLASS__, 'factuur', $factuur->id),
			$factuur_nr);
	}
	
	public function _link_factuur_pdf($factuur_id, $factuur) {
		return sprintf('<a href="%s">pdf</a>',
			$this->router->link(__CLASS__, 'factuur_pdf', $factuur_id));
	}
	
	public function _format_voldaan($voldaan, $factuur) {
		return sprintf($voldaan ? 'Ja' : 'Nee (%d dagen)', $factuur->termijn_resterend);
	}
	
	public function post_project_naam($factuur_id) {
		try {
			$factuur = $this->facturen->find($factuur_id);
			
			$factuur->project_naam = trim($_POST['data']);
		
			$factuur->save();
		
			return $this->views->true();
		} catch(Exception $e) {
			return $this->views->false();
		}
	}
	
	public function post_project_beschrijving($factuur_id) {
		try {
			$factuur = $this->facturen->find($factuur_id);
			
			$factuur->project_beschrijving = trim($_POST['data']);
		
			$factuur->save();
		
			return $this->views->true();
		} catch(Exception $e) {
			return $this->views->false();
		}
	}
}