<?php

require_once 'lib/IHG.php';

date_default_timezone_set('Europe/Amsterdam');

function summary($text) {
	$length = 100;
	
	if(strlen($text) < $length) return $text;
	
	$tmp = substr($text, 0, $length);
	$pos = strrpos($tmp, ' ');
	$summary = substr($tmp, 0, $pos);

	//return $text . '...';

        return sprintf('<span title="%s">%s...</span>',
            htmlentities($text, ENT_QUOTES, 'utf-8'),
            htmlentities($summary, ENT_COMPAT, 'utf-8'));
}

class Administratie_Application extends IHG_Application_Abstract {
	public function set_up() {
		
		parent::set_up();
		
		global $pdo;
		
		$pdo = include 'etc/config.php';
		//$pdo = new Debug_PDO('mysql:host=localhost;dbname=Werk', 'root', '');
		//$pdo->add_filter(array($this, 'snapshot_filter')); // alleen INSERT queries
		//$pdo->add_listener(array($this, 'snapshot_callback')); // alleen INSERT over de log_socket
		//$pdo->add_decorator(array($this, 'snapshot_argument_decorator')); // kleurtjes voor de values
			
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo->query('SET NAMES UTF8');
		
		$this->configuration->load(array(
			'global_controller' => 'Administratie_Global_Controller::index'
		));
		
		$this->router
			->register_route('/', 'Administratie_Index_Controller::index')
			->register_route('/facturen/',				'Administratie_Factuur_Controller::index')
			->register_route('/facturen/%d.html',		'Administratie_Factuur_Controller::factuur')
			->register_route('/facturen/%d/project_naam.js', 'Administratie_Factuur_Controller::post_project_naam')
			->register_route('/facturen/%d/project_beschrijving.js', 'Administratie_Factuur_Controller::post_project_beschrijving')
			->register_route('/facturen/%d.pdf',		'Administratie_Factuur_Controller::factuur_pdf')
			->register_route('/belasting/',				'Administratie_Factuur_Controller::belasting_overzicht')
			->register_route('/bedrijven/', 			'Administratie_Bedrijf_Controller::bedrijven')
			->register_route('/bedrijven/nieuw.html', 'Administratie_Bedrijf_Controller::bedrijf_toevoegen')
			->register_route('/bedrijven/%d.html', 		'Administratie_Bedrijf_Controller::bedrijf')
			->register_route('/bedrijven/%d/contacten/%d.html', 'Administratie_Contactpersoon_Controller::contactpersoon_toevoegen')
			->register_route('/bedrijven/%d/contacten/nieuw.html', 'Administratie_Contactpersoon_Controller::contactpersoon_toevoegen')
			->register_route('/uren/nieuw.html',		'Administratie_Uur_Controller::uur_toevoegen')
			->register_route('/uren/%d.html', 			'Administratie_Uur_Controller::uur_toevoegen')
			->register_route('/tarieven/nieuw.html', 	'Administratie_Tarief_Controller::tarief_toevoegen')
			->register_route('/tarieven/%d.html', 		'Administratie_Tarief_Controller::tarief_toevoegen');
		
		$this->breadcrumbs->add_crumb('<span id="home-breadcrumb">Home</span>',
			$this->router->link('Administratie_Index_Controller', 'index'));
		
		$this->register_component('bedrijven',
			new IHG_Record_Provider($pdo, 'Administratie_Bedrijf'));
		
		$this->register_component('facturen',
			new IHG_Record_Provider($pdo, 'Administratie_Factuur'));
			
		$this->register_component('uren',
			new IHG_Record_Provider($pdo, 'Administratie_Uur'));
		
		$this->register_component('tarieven',
			new IHG_Record_Provider($pdo, 'Administratie_Tarief'));
			
		$this->register_component('contactpersonen',
			new Administratie_Contactpersoon_Provider($pdo));
			
		$this->register_component('aankopen',
			new IHG_Record_Provider($pdo, 'Administratie_Aankoop'));
			
		$this->register_component('producten',
			new IHG_Record_Provider($pdo, 'Administratie_Product'));
	}
	
	public function snapshot_callback($snapshot) {
		if(stristr($snapshot->query, 'INSERT INTO') || stristr($snapshot->query, 'UPDATE ')) {
			log_send((string) $snapshot);
		}
	}
	
	public function snapshot_argument_decorator($value) {
		if($value === null) {
			return chr(0x1B) . "[0;37;45mNULL" . chr(0x1B). "[0;32;40m";
		} else {
			return sprintf(chr(0x1B) . "[0;37;41m%s" . chr(0x1B). "[0;32;40m", $value);
		}
	}
	
	public function snapshot_filter($snapshot, &$success) {
		if(stristr($snapshot->query, 'INSERT INTO') || stristr($snapshot->query, 'UPDATE ')) {
			$snapshot->insert_id = 223;
			$success = true;
			return false;
		} 
		
		return true;
	}
}

IHG_Application_Abstract::runApplication('Administratie_Application');

?>
