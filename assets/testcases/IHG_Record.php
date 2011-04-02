<?php

//SimpleTestOptions::ignore('IHG_Record_TestCase');

class Uur extends IHG_Record {
	
	protected function _table_name() {
		return 'Uren';
	}
	
	protected function _properties() {
		return array(
			'id',
			'bedrijf_id',
			'factuur_id',
			'start_tijd',
			'eind_tijd',
			'duur' => new IHG_SQL_Atom('eind_tijd - start_tijd'),
			'beschrijving'
		);
	}
}

class Bedrijf extends IHG_Record {
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
	
	public function uren() {
		return IHG_Record::find_records($this->pdo(), 'Uur', array('bedrijf' => $this));
	}
	
	public function __toString() {
		return sprintf('[Bedrijf %s]', $this->naam);
	}
}

class GrootBedrijf extends Bedrijf {
	public function properties() {
		return parent::_properties() + array(
			'aantal_uren' => new IHG_SQL_Count('Uur', array(
				'id' => new IHG_SQL_Atom('bedrijf_id')
			))
		);
	}
}

class ExtraGrootBedrijf extends GrootBedrijf {
	protected function _properties() {
		return parent::_properties() + array(
			'aantal_emails' => new IHG_SQL_Count('Email', array(
					'id' => new IHG_SQL_Atom('bedrijf_id')
				)),
			'aantal_email_ontvangers' => new IHG_SQL_Sum('Email',
				new IHG_SQL_Atom('aantal_ontvangers'),
				array(
					'id' => new IHG_SQL_Atom('bedrijf_id')
				))
		);
	}
}

class Email extends IHG_Record {
	protected function _table_name() {
		return 'Emails';
	}
	
	protected function _properties() {
		return array(
			'id',
			'onderwerp',
			'bedrijf_id',
			'aantal_ontvangers'
		);
	}
}


abstract class IHG_Record_TestCase extends UnitTestCase {
	
	protected $pdo;
	
	//abstract public function setUp();
	
	public function testRecordIsReadable() {
		$bedrijf_a = new Bedrijf();
		$bedrijf_a->naam = 'Bedrijf A';
		
		$this->assertIdentical($bedrijf_a->naam, 'Bedrijf A');
	}
	
	public function testDateTimePropertyIsReadable() {
		$uur = new Uur();

		try {
			$uur->start_tijd = new DateTime('1 Oct 2008');
		} catch(Exception $e) {
			$this->fail();
		}
		
		$this->assertIsA($uur->start_tijd, 'DateTime');
		$this->assertEqual($uur->start_tijd->format('j M Y'), '1 Oct 2008');
	}
	
	public function testObjectPropertyIsReadable() {
		
		$x = new stdClass();
		$x->random = "pindakaas";
		
		$bedrijf = new Bedrijf();
		$bedrijf->naam = $x;
		
		$this->assertIdentical($bedrijf->naam, $x);
	}
	
	public function testRecordIsCreated() {
		
		$bedrijf_a = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_a->naam = 'Bedrijf A';
		$bedrijf_a->url = 'http://bedrijfa.nl/';
		$this->assertTrue($bedrijf_a->save(), true);
		
		list($new_record_count) = $this->pdo->query('SELECT COUNT(*) FROM Bedrijven')->fetch(PDO::FETCH_NUM);
		
		$this->assertIdentical((int) $new_record_count, 1);
	}
	
	public function testNewRecordGetsIDAssigned() {
		
		$bedrijf_naam = 'bedrijf ' . uniqid();
		
		$bedrijf_a = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_a->naam = $bedrijf_naam;
		$bedrijf_a->url = 'http://bedrijfa.nl/';
		
		$this->assertNull($bedrijf_a->id);
		
		$bedrijf_a->save();
		
		list($bedrijf_id) = $this->pdo->query('SELECT id FROM Bedrijven WHERE naam = \'' . $bedrijf_naam . '\'')->fetch(PDO::FETCH_NUM);
		
		$this->assertNotNull($bedrijf_id);
		
		$this->assertEqual($bedrijf_a->id, $bedrijf_id);
	}
	
	public function testFindRecord() {
		$bedrijf_naam = 'bedrijf ' . uniqid();
		
		$bedrijf_a = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_a->naam = $bedrijf_naam;
		$bedrijf_a->url = 'http://bedrijfa.nl/';
		$bedrijf_a->save();
		
		$bedrijf_dull = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_dull->naam = 'Stom testbedrijf dat in de weg zit';
		$bedrijf_dull->url = 'http://bedrijfa.nl/';
		$bedrijf_dull->save();
		
		$bedrijf_b = IHG_Record::find_record($this->pdo, 'Bedrijf', array('naam' => $bedrijf_naam));
		
		$this->assertNotNull($bedrijf_b);
		$this->assertIsA($bedrijf_b, 'Bedrijf');
		$this->assertEqual($bedrijf_a->id, $bedrijf_b->id);
		$this->assertEqual($bedrijf_a, $bedrijf_b);
		$this->assertIdentical($bedrijf_a, $bedrijf_b);
	}
	
	public function testFindRecordBySQLAtom() {
		$bedrijf_naam = 'bedrijf' . uniqid();
		
		$bedrijf = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf->naam = $bedrijf_naam;
		$bedrijf->save();
		
		$bedrijf_dull = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_dull->naam = $bedrijf_naam . ' dat in de weg zit';
		$bedrijf_dull->save();
		
		$sql_atom = new IHG_SQL_Atom('naam = ?');
		$sql_atom->bind_value($bedrijf_naam);
		
		$gevonden_bedrijf = IHG_Record::find_record($this->pdo, 'Bedrijf', $sql_atom);
		
		$this->assertIdentical($bedrijf, $gevonden_bedrijf);
	}
	
	public function testFindRecordBySQLAtomValue() {
		$bedrijf_naam = 'bedrijf' . uniqid();
		
		$suffix = 'suffix';
		
		$bedrijf = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf->naam = $bedrijf_naam . $suffix;
		$bedrijf->save();
		
		$bedrijf_dull = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_dull->naam = $bedrijf_naam . ' dat in de weg zit';
		$bedrijf_dull->save();
		
		$sql_atom = new IHG_SQL_Atom('? || ?'); // concat
		$sql_atom->bind_values(array($bedrijf_naam, $suffix));
		
		$gevonden_bedrijf = IHG_Record::find_record($this->pdo, 'Bedrijf', array('naam' => $sql_atom));
		
		$this->assertIdentical($bedrijf, $gevonden_bedrijf);
	}

	public function testFindRecordByMultipleConditions() {
		$website = 'website ' . uniqid();
		
		$bedrijf_a = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_a->naam = 'bedrijf a';
		$bedrijf_a->url = $website;
		$bedrijf_a->save();
		
		$bedrijf_b = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_b->naam = 'bedrijf b';
		$bedrijf_b->url = $website;
		$bedrijf_b->save();
		
		$bedrijf_c = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_c->naam = 'bedrijf c';
		$bedrijf_c->url = $website;
		$bedrijf_c->save();
		
		$bedrijf_d = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_d->naam = 'bedrijf b';
		$bedrijf_d->url = 'anders';
		$bedrijf_d->save();
		
		$bedrijven = IHG_Record::find_records($this->pdo, 'Bedrijf', array(
			'url' => $website,
			'naam' => array('bedrijf a', 'bedrijf b')
		));
		
		$bedrijven = iterator_to_array($bedrijven);
		
		$this->assertNotNull($bedrijven);
		$this->assertTrue(count($bedrijven) === 2);
		
		$this->assertTrue(ihg_in_array($bedrijf_a, $bedrijven));
		$this->assertTrue(ihg_in_array($bedrijf_b, $bedrijven));
	}
	
	public function testFindRecordByMultipleConditionsIncludingASQLAtom() {
		$website = 'website ' . uniqid();
		
		$bedrijf_a = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_a->naam = 'bedrijf a';
		$bedrijf_a->url = $website;
		$bedrijf_a->save();
		
		$bedrijf_b = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_b->naam = 'bedrijf b';
		$bedrijf_b->url = $website;
		$bedrijf_b->save();
		
		$bedrijf_c = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_c->naam = 'bedrijf c';
		$bedrijf_c->url = $website;
		$bedrijf_c->save();
		
		$bedrijf_d = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_d->naam = 'bedrijf b';
		$bedrijf_d->url = 'anders';
		$bedrijf_d->save();
		
		$bedrijven = IHG_Record::find_records($this->pdo, 'Bedrijf', array(
			'url' => $website,
			'naam' => array('bedrijf a', new IHG_SQL_Atom("'bedrijf b'"))
		));
		
		$bedrijven = iterator_to_array($bedrijven);
		
		$this->assertNotNull($bedrijven);
		$this->assertTrue(count($bedrijven) === 2);
		
		$this->assertTrue(ihg_in_array($bedrijf_a, $bedrijven));
		$this->assertTrue(ihg_in_array($bedrijf_b, $bedrijven));
	}
	
	public function testFindRecordByMultipleConditionsIncludingASQLAtomCondition() {
		$website = 'website ' . uniqid();
		
		$bedrijf_a = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_a->naam = 'bedrijf a';
		$bedrijf_a->url = $website;
		$bedrijf_a->save();
		
		$bedrijf_b = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_b->naam = 'bedrijf b';
		$bedrijf_b->url = $website;
		$bedrijf_b->save();
		
		$bedrijf_c = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_c->naam = 'bedrijf c';
		$bedrijf_c->url = $website;
		$bedrijf_c->save();
		
		$bedrijf_d = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_d->naam = 'bedrijf b';
		$bedrijf_d->url = 'anders';
		$bedrijf_d->save();
		
		$sql_condition_atom = new IHG_SQL_Atom('naam = ? OR naam = ?');
		$sql_condition_atom->bind_values(array('bedrijf a', 'bedrijf b'));
		
		$bedrijven = IHG_Record::find_records($this->pdo, 'Bedrijf', array(
			'url' => $website,
			$sql_condition_atom
		));
		
		$bedrijven = iterator_to_array($bedrijven);
		
		$this->assertNotNull($bedrijven);
		$this->assertTrue(count($bedrijven) === 2);
		
		$this->assertTrue(ihg_in_array($bedrijf_a, $bedrijven));
		$this->assertTrue(ihg_in_array($bedrijf_b, $bedrijven));
	}
	
	public function testDirtyRecords() {
		$bedrijf_naam = 'bedrijf ' . uniqid();
		
		$bedrijf = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$this->assertTrue($bedrijf->is_dirty());
		
		$bedrijf->naam = $bedrijf_naam;
		$this->assertTrue($bedrijf->is_dirty());
		
		$bedrijf->save();
		$this->assertFalse($bedrijf->is_dirty());
		
		$bedrijf = IHG_Record::find_record($this->pdo, 'Bedrijf', array('naam' => $bedrijf_naam));
		
		$this->assertNotNull($bedrijf);
		$this->assertFalse($bedrijf->is_dirty());
		
		$bedrijf->url = 'http://ikhoefgeen.nl';
		$this->assertTrue($bedrijf->is_dirty());
		
		$bedrijf->save();
		$this->assertFalse($bedrijf->is_dirty());
		
		$bedrijven = IHG_Record::find_records($this->pdo, 'Bedrijf', array('naam' => $bedrijf_naam));
		$this->assertTrue($bedrijven[0] !== false && $bedrijven[0]->is_dirty() === false);
	}
	
	public function testFindMultipleRecords() {
		$website = 'website ' . uniqid();
		
		$bedrijf_a = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_a->naam = 'bedrijf a';
		$bedrijf_a->url = $website;
		$bedrijf_a->save();
		
		$bedrijf_b = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_b->naam = 'bedrijf b';
		$bedrijf_b->url = $website;
		$bedrijf_b->save();
		
		$bedrijf_c = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_c->naam = 'bedrijf c';
		$bedrijf_c->url = $website;
		$bedrijf_c->save();
		
		$bedrijf_dull = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_dull->naam = 'bedrijf dat in de weg zit';
		$bedrijf_dull->save();
		
		$bedrijven = IHG_Record::find_records($this->pdo, 'Bedrijf', array('url' => $website));
		
		$bedrijven = iterator_to_array($bedrijven);
		
		$this->assertNotNull($bedrijven);
		$this->assertTrue(count($bedrijven) === 3);
		
		$this->assertTrue(ihg_in_array($bedrijf_a, $bedrijven));
		$this->assertTrue(ihg_in_array($bedrijf_b, $bedrijven));
		$this->assertTrue(ihg_in_array($bedrijf_c, $bedrijven));
	}
	
	/* @TODO fix the joins! */
	public function __testJoins() {
		
		$bedrijf_a = IHG_Record::create_record($this->pdo, 'GrootBedrijf');
		$bedrijf_a->naam = 'bedrijf a';
		$bedrijf_a->save();
		
		$bedrijf_b = IHG_Record::create_record($this->pdo, 'GrootBedrijf');
		$bedrijf_b->naam = 'bedrijf b';
		$bedrijf_b->save();
		
		for($i = 1; $i <= 25; $i++) {
			$email = IHG_Record::create_record($this->pdo, 'Email');
			$email->bedrijf = $bedrijf_b;
			$email->onderwerp = 'Email ' . $i;
			$email->aantal_ontvangers = 3;
			$email->save();
		}
		
		for($i = 1; $i <= 12; $i++) {
			$uur = IHG_Record::create_record($this->pdo, 'Uur');
			$uur->start_tijd = new DateTime($i . ' Oct 2007');
			$uur->eind_tijd  = new DateTime($i + 1 . ' Oct 2007');
			$uur->beschrijving = 'Uur ' . $i;
			$uur->bedrijf = $bedrijf_b;
			$uur->save();
		}
		
		$uur->eind_tijd = null;
		$uur->save();
		
		$bedrijven = IHG_Record::find_records($this->pdo, 'GrootBedrijf');
		
		foreach($bedrijven as $bedrijf) {
			
			if($bedrijf->naam == 'bedrijf a')
				$this->assertEqual($bedrijf->aantal_uren, 0);
			elseif($bedrijf->naam == 'bedrijf b')
				$this->assertEqual($bedrijf->aantal_uren, 12);
			else 
				$this->fail();
		}
		
		$bedrijf = IHG_Record::find_record($this->pdo, 'ExtraGrootBedrijf', array(
			'naam' => 'bedrijf b'
		));
	
		$this->assertEqual($bedrijf->aantal_emails, 25);
		$this->assertEqual($bedrijf->aantal_email_ontvangers, 75);
	}
	
	public function testObjectPropertyIsSaved() {
		$bedrijf_naam = 'bedrijf ' . uniqid();
		
		$uur = IHG_Record::create_record($this->pdo, 'Uur');
		$uur->start_tijd = new DateTime('1 Oct 2007');
		
		$bedrijf = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf->naam = $bedrijf_naam;
		$bedrijf->url = $uur;
		
		$this->assertIdentical($bedrijf->url, $uur);
		
		$bedrijf->save();
		
		$bedrijf_dull = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf_dull->naam = $bedrijf_naam . ' dat in de weg zit';
		$bedrijf_dull->save();
		
		$bedrijf = IHG_Record::find_record($this->pdo, 'Bedrijf', array('naam' => $bedrijf_naam));
		$this->assertIdentical($bedrijf->url, $uur);
	}

	public function testRelationIsSaved() {
		$beschrijving = 'beschrijving ' . uniqid();
		
		$bedrijf = IHG_Record::create_record($this->pdo, 'Bedrijf');
		$bedrijf->naam = 'Bedrijf X';
		
		$uur = IHG_Record::create_record($this->pdo, 'Uur');
		$uur->start_tijd = new DateTime('1 Oct 2007');
		$uur->beschrijving = $beschrijving;
		
		try {
			$uur->bedrijf = $bedrijf;
			$this->fail();
			return;
		} catch(Exception $e) {}
		
		$bedrijf->save();
		
		try {
			$uur->bedrijf = $bedrijf;
		} catch(Exception $e) {
			$this->fail();
			return;
		}
		
		$this->assertIdentical($uur->bedrijf, $bedrijf);
		
		$uur->save();
		
		$uur = IHG_Record::find_record($this->pdo, 'Uur', array('beschrijving' => $beschrijving));
		
		$this->assertIdentical($uur->bedrijf, $bedrijf);
		
		$this->assertIdentical($bedrijf->uren()->offsetGet(0), $uur);
	}

	public function tearDown() {
		$this->pdo->query("DROP TABLE IF EXISTS Bedrijven");
		$this->pdo->query("DROP TABLE IF EXISTS Uren");
	}
}