<?php

class Administratie_Uur_Grouper implements IHG_HTML_Table_Grouper
{
	private $application;
	
	public function __construct($application)
	{
		$this->application = $application;
	}
	
	public function has_header($bedrijf_id)
	{
		return true;
	}
	
	public function format_header($bedrijf_id)
	{
		return sprintf('<a href="%s">%s</a>',
			$this->application->router->link('Administratie_Bedrijf_Controller', 'bedrijf', $bedrijf_id),
			$this->application->bedrijven->find($bedrijf_id)->naam);
	}
	
	public function classify($uur)
	{
		return $uur->bedrijf_id;
	}
}

class Administratie_Uur_Controller extends IHG_Controller_Abstract
{
	public function onbetaalde_uren($bedrijf_id = null)
	{
		$router = $this->router;
		
		$view = $this->views->from_record($this->uren)
			->set_data($this->uren->find_all(array('factuur_id' => null) 
				+ ($bedrijf_id !== null ? array('bedrijf_id' => $bedrijf_id) : array())))
			//->add_column('bedrijf', 'Bedrijf', array($this, '_format_bedrijf'))
			->add_column('id', '#', function($id, $uur) use ($router) { return sprintf('<a href="%s" class="open-in-sheet">%d</a>', $router->link('Administratie_Uur_Controller', 'uur_toevoegen', $uur->bedrijf_id, $id), $id); })
			->add_column('beschrijving', 'Beschrijving', new IHG_Formatter_Rich())
			->add_column('start_tijd', 'Datum', new IHG_Formatter_Date())
			->add_column('duur', 'Duur', create_function('$x', 'return number_format($x, 2);'), 'array_sum')
			->add_column('prijs', 'Prijs', new IHG_Formatter_Price(), 'array_sum');
		
		if ($bedrijf_id === null)
			$view->set_grouper(new Administratie_Uur_Grouper($this->application));
		
		return $view;
	}
	
	public function factuur($factuur_id)
	{
		return $this->views->from_record($this->uren)
			->set_data($this->uren->find_all(array('factuur_id' => $factuur_id)))
			//->add_column('bedrijf', 'Bedrijf', array($this, '_format_bedrijf'))
			->add_column('beschrijving', 'Beschrijving')
			->add_column('start_tijd', 'Datum', new IHG_Formatter_Date())
			->add_column('duur', 'Duur', create_function('$x', 'return number_format($x, 2);'))
			->add_column('prijs', 'Prijs', new IHG_Formatter_Price());
	}
	
	public function uur_toevoegen($bedrijf_id = null, $uur_id = null)
	{	
		if ($uur_id)
			$uur = $this->uren->find($uur_id);
		else
			$uur = $this->_create_new_uur();
		
		if (!$uur_id && $bedrijf_id)
		{
			$bedrijf = $this->bedrijven->find($bedrijf_id);
			$uur->bedrijf = $bedrijf;
			$bedrijf_uren = $bedrijf->uren->sort('id', ORDER_DESC);
			
			$bedrijf_uren->valid();

			if ($last_registered_uur = $bedrijf_uren->current())
				$uur->tarief = $last_registered_uur->tarief;
		}
		
		$view = $this->views->from_file('administratie_uur_toevoegen');
		$view->errors = array();
		
		try
		{
			if ($this->_is_post_request())
			{
				$uur->bedrijf		= $this->bedrijven->find($_POST['bedrijf_id']);
				$uur->werk_id		= empty($_POST['werk_id']) ? null : $_POST['werk_id'];
				$uur->start_tijd	= IHG_DateTime::from_string($_POST['start_tijd']);
				$uur->eind_tijd		= IHG_DateTime::from_string($_POST['eind_tijd']);
				$uur->beschrijving	= $_POST['beschrijving'];
				$uur->tarief		= $this->tarieven->find($_POST['tarief_id']);
			
				if ( (isset($_POST['delete']) && $uur->delete())
					|| ($uur->save() && !empty($_POST['_origin'])) )
				{
					return $this->views->redirect($_POST['_origin']);
				}
			}
		}
		catch (IHG_Record_Exception $e)
		{
			$view->errors = $e->errors;
		}
		
		$view->uur = $uur;
		
		return $view;
	}
	
	private function _create_new_uur()
	{
		$uur = $this->uren->create();
		$uur->start_tijd = new DateTime('now');
		$uur->eind_tijd	= new DateTime('now');
		return $uur;
	}
	
	public function _format_bedrijf($bedrijf)
	{
		return sprintf('<a href="%s">%s</a>',
			$this->router->link('Administratie_Bedrijf_Controller', 'bedrijf', $bedrijf->id),
			$bedrijf->naam);
	}
}

?>