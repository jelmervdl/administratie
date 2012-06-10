<?php

class Administratie_Werk_Controller extends IHG_Controller_Abstract
{	
	public function list_actieve_werken() {
		return $this->views->from_record($this->werken)
			->set_data($this->werken->find_all()->sort('werk_id', ORDER_ASC))
			->add_column('naam', 'Naam');
			//->add_column('url', 'Website', array($this, '_format_url'));
	}
	
	public function werk_toevoegen($werk_id = null)
	{
		$werk = $werk_id
			? $this->werken->find($werk_id)
			: $this->werken->create();
			
		$view = $this->views->from_file('administratie_werk_toevoegen');
		
		if($this->_is_post_request())
		{
			try {
				$werk->bedrijf = !empty($_POST['bedrijf_id'])
					? $this->bedrijven->find($_POST['bedrijf_id'])
					: null;
				
				$werk->tarief = !empty($_POST['tarief_id'])
					? $this->tarieven->find($_POST['tarief_id'])
					: null;

				$werk->naam = trim($_POST['naam']);
				$werk->taakomschrijving = trim($_POST['taakomschrijving']);

				$werk->budget = $_POST['budget'];
				$werk->deadline = IHG_DateTime::from_string($_POST['deadline']);
			
				if($werk->save())
					return $this->views->redirect($_POST['_origin']);
				
			} catch(IHG_Record_Exception $e) {
				$view->errors = $e->errors;
			}
		}
		
		$view->werk = $werk;
		
		return $view;
	}
}
