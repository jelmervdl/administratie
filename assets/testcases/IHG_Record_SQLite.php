<?php

require_once ROOT.'IHG_Record.php';

class IHG_Record_SQLite_TestCase extends IHG_Record_TestCase {
	public function setUp() {
		$this->pdo = new PDO('sqlite::memory:');
		
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$this->pdo->query("DROP TABLE IF EXISTS Bedrijven");
		$this->pdo->query("DROP TABLE IF EXISTS Uren");
		$this->pdo->query("DROP TABLE IF EXISTS Emails");
		
		$this->pdo->query("
			CREATE TABLE Bedrijven (
				id		integer NOT NULL default 0,
				naam 	varchar(50) NOT NULL,
				url 	text,
				PRIMARY KEY (id)
			)
		");

		$this->pdo->query("
			CREATE TABLE Uren (
				id				integer	NOT NULL default 0,
				bedrijf_id		int		NOT NULL,
				factuur_id		int	default NULL,
				start_tijd		datetime NOT NULL,
				eind_tijd		datetime default NULL,
				beschrijving	text,
				PRIMARY KEY (id)
			)
		");
		
		$this->pdo->query("
			CREATE TABLE Emails (
				id				integer	NOT NULL default 0,
				bedrijf_id		int		NOT NULL,
				onderwerp		varchar(50) NOT NULL,
				aantal_ontvangers		int	default NULL,
				PRIMARY KEY (id)
			)
		");		
	}
}
