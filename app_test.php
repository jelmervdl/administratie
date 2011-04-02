<?php

require_once 'lib/IHG.php';

error_reporting(E_ALL);
ini_set('display_errors', true);

class My_Application extends IHG_Application_Abstract {

	private $_pdo;

	private function _connect() {
		$this->_pdo = new PDO('mysql:host=localhost;dbname=Experimenteel_IHG_Record', 'root', '');
		$this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	private function _install() {

		$this->_uninstall();

		$this->_pdo->query("
			CREATE TABLE Gebruikers (
				id				integer	NOT NULL AUTO_INCREMENT,
				email			varchar(255)	NOT NULL,
				password		varchar(40) NOT NULL,
				PRIMARY KEY (id)
			)
		");

		$this->_pdo->query("
			CREATE TABLE Bedrijven (
				id 		integer 	NOT NULL AUTO_INCREMENT,
				naam 	varchar(50) NOT NULL,
				url 	text,
				PRIMARY KEY (id)
			)
		");

		$this->_pdo->query("
			CREATE TABLE Emails (
				id				integer	NOT NULL AUTO_INCREMENT,
				bedrijf_id		int		NOT NULL,
				titel			varchar(50) NOT NULL,
				inhoud			text,
				PRIMARY KEY (id)
			)
		");

		$gebruiker_stmt = $this->_pdo->prepare("INSERT INTO Gebruikers (email, password) VALUES(?, SHA1(?))");

		$gebruiker_stmt->execute(array('jelmer@ikhoefgeen.nl', 'test'));

		$bedrijf_stmt = $this->_pdo->prepare("INSERT INTO Bedrijven (naam, url) VALUES(?, ?)");

		$email_stmt = $this->_pdo->prepare("INSERT INTO Emails (bedrijf_id, titel, inhoud) VALUES(?, ?, ?)");

		for($i = 0; $i < 4; $i++) {
			$bedrijf_stmt->execute(array(
				sprintf('Bedrijf %d', $i),
				sprintf('http://www.bedrijf%d.nl', $i)
				));

			$bedrijf_id = $this->_pdo->lastInsertId();

			for($j = 0; $j < 25; $j++) {
				$email_stmt->execute(array(
					$bedrijf_id,
					sprintf('Email %d', $j),
					sprintf('Lorem ipsum dolores est')
					));
			}
		}
	}

	private function _uninstall() {
		$this->_pdo->query("DROP TABLE IF EXISTS Gebruikers");
		$this->_pdo->query("DROP TABLE IF EXISTS Bedrijven");
		$this->_pdo->query("DROP TABLE IF EXISTS Emails");
	}

	public function set_up() {

		parent::set_up();

		$this->router
			->register_route('/', 'My_Application_Index_Controller::index')
			->register_route('/login', 'My_Application_Identity_Controller::login')
			->register_route('/logout', 'My_Application_Identity_Controller::logout')
			->register_route('/%s.html', 'My_Application_Index_Controller::bedrijf')
			->register_route('/%s/email-%d.html', 'My_Application_Index_Controller::email');


		$this->configuration->load(array(
			'global_controller' => 'My_Application_Controller::main',
			'exception_controller' => 'My_Application_Controller::exception'));


		$this->register_component('gebruikers',
			new IHG_Record_Provider($this->_pdo, 'My_Application_Gebruiker'));

		$this->register_component('bedrijven',
			new IHG_Record_Provider($this->_pdo, 'My_Application_Bedrijf'));

		$this->register_component('emails',
			new IHG_Record_Provider($this->_pdo, 'My_Application_Email'));


		$this->register_component('identity',
			new IHG_Identity_Provider($this->gebruikers));
	}

	public function run() {
		$this->_connect();

		$this->_install();

		parent::run();

		$this->_uninstall();
	}
}

class My_Application_Gebruiker extends IHG_Record {
	protected function _table_name() {
		return 'Gebruikers';
	}

	protected function _properties() {
		return array(
			'id',
			'email',
			'password'
		);
	}
}

class My_Application_Bedrijf extends IHG_Record {

	protected function _table_name() {
		return 'Bedrijven';
	}

	protected function _record_type_class($record_type) {
		return sprintf('My_Application_%s', $record_type);
	}

	protected function _properties() {
		return array(
			'id',
			'naam',
			'url',
			'aantal_emails' => new IHG_SQL_Count('My_Application_Email', array(
				'id' => new IHG_SQL_Atom('bedrijf_id')
				))
		);
	}

	public function __toString() {
		return $this->naam;
	}

	public function emails() {
		return self::find_records($this->pdo(), 'My_Application_Email', array(
			'bedrijf_id' => $this->id
		));
	}

}

class My_Application_Email extends IHG_Record {
	public function _table_name() {
		return 'Emails';
	}

	protected function _record_type_class($record_type) {
		return sprintf('My_Application_%s', $record_type);
	}

	public function _properties() {
		return array(
			'id',
			'bedrijf_id',
			'titel',
			'inhoud'
		);
	}
}

class My_Application_Controller extends IHG_Controller_Abstract {

	public function main($child_view) {

		$identity_view = $this->_identity();

		$menu_view = $this->_menu();

		$combi_view = new IHG_View_Collection();
		$combi_view->append_view($identity_view);
		$combi_view->append_view($menu_view);
		$combi_view->append_view($child_view);

		return $combi_view;
	}

	public function exception($e) {
		return new IHG_View_String($e->getMessage());
	}

	private function _menu() {
		$html_writer = new IHG_HTML_Writer();

		$html_writer
			->section()->put('Menu')->end()
			->start_list(IHG_HTML_Writer::ORDERED_LIST)
			->item()->link($this->router->link('My_Application_Index_Controller', 'index'))->put("Bedrijven")
			->end_list();

		return $html_writer;
	}

	private function _identity() {
		if($this->identity->i_has_it()) {
			return $this->views->writer()->put('Hallo ')->put($this->identity->identity()->email)
				->link($this->router->link('My_Application_Identity_Controller', 'logout'))->put('Log uit')->end();
		} else {
			return $this->views->writer()->link($this->router->link('My_Application_Identity_Controller', 'login'))->put('Inloggen')->end();
		}
	}

}

class My_Application_Index_Controller extends IHG_Controller_Abstract {

	public function index() {

		$bedrijven = $this->bedrijven->find_all();

		$html_writer = new IHG_HTML_Writer();

		$html_writer->start_list();

		foreach($bedrijven as $bedrijf) {
			$html_writer->item()
				->link($this->router->link(__CLASS__, 'bedrijf', $bedrijf->naam))
				->put($bedrijf);
		}

		$html_writer->end_list();

		return $html_writer;
	}

	public function bedrijf($bedrijf_naam) {

		$bedrijf = $this->bedrijven->find(array(
			'naam' => $bedrijf_naam
		));

		if(!$bedrijf) {
			throw new Exception("Bedrijf niet gevonden");
		}

		$bedrijf_view = $this->views->from_file('bedrijf');

		$bedrijf_view->bedrijf = $bedrijf;

		$html_writer = new IHG_HTML_Writer();

		$html_writer
			->section()->put('Emails')->end()
			->start_list();

		foreach($bedrijf->emails() as $email) {
			$html_writer
				->item()
					->link($this->router->link(__CLASS__, 'email', $bedrijf->naam, $email->id))
					->put($email->titel);
		}

		$html_writer
			->end_list();

		return $this->views->combine($bedrijf_view, $html_writer);
	}

	public function email($bedrijf_naam, $email_id) {
		$email = $this->emails->find((int) $email_id);

		if(!$email || $email->bedrijf->naam != $bedrijf_naam) {
			throw new Exception('Email niet gevonden');
		}

		$bedrijf = $email->bedrijf;

		$bedrijf_view = $this->views->from_file('bedrijf');

		$bedrijf_view->bedrijf = $bedrijf;


		$email_view = $this->views->from_file('email');

		$email_view->email = $email;

		$email_view->bedrijf = $bedrijf;

		return $this->views->combine($bedrijf_view, $email_view);
	}

}

class My_Application_Identity_Controller extends IHG_Controller_Abstract {

	public function login() {
		if($this->_is_post_request()) {
			try {
				$this->identity->authenticate(array(
					'email' 	=> $_POST['email'],
					'password' 	=> sha1($_POST['password'])
				));

				$resulting_view = $this->views->redirect($_POST['referer']);
			} catch(IHG_Identity_Exception $e) {
				$resulting_view = new IHG_View_String('FAIL');
			}
		}

		$login_form = $this->views->from_file('login');
		$login_form->referer = ifsetor($_POST['referer'], $_SERVER['HTTP_REFERER']);
		$login_form->email   = ifsetor($_POST['email'], '');

		if(isset($resulting_view)) {
			return $this->views->combine($resulting_view, $login_form);
		} else {
			return $login_form;
		}
	}

	public function logout() {
		$this->identity->clear_identity();

		return $this->views->redirect(ifsetor(
			$_SERVER['HTTP_REFERER'],
			$this->router->link('My_Application_Index_Controller', 'index')));
	}
}

IHG_Application_Abstract::runApplication('My_Application');