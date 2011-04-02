<?php

class Administratie_Tarief_Controller extends IHG_Controller_Abstract {
	
	public function tarief_toevoegen($tarief_id = null)
	{
		if ($tarief_id)
			$tarief = $this->tarieven->find($tarief_id);
		else
			$tarief = $this->tarieven->create();
		
		$view = $this->views->from_file('administratie_tarief_toevoegen');
		$view->tarief = $tarief;
		
		if($this->_is_post_request())
		{
			try
			{
				$tarief->naam = $_POST['naam'];
				$tarief->prijs_per_uur = $_POST['prijs_per_uur'];
			
				if ( (isset($_POST['delete']) && $tarief->delete()) || $tarief->save() )
					return $this->views->redirect($_POST['_origin']);
			}
			catch(IHG_Record_Exception $e)
			{
				$view->errors = $e->errors;
			}
		}
		
		return $view;
	}
	
}