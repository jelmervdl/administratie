<?php

class IHG_Record_Set_Bedrijf extends IHG_Record {
	protected function _table_name() {
		return 'Bedrijven';
	}
	
	protected function _properties() {
		return array('id', 'naam', 'url');
	}
}

class IHG_Record_Set_TestCase extends UnitTestCase {
	
	const RECORD_TYPE = 'IHG_Record_Set_Bedrijf';
	
	protected $pdo;
	
	public function setUp() {
		$this->pdo = new PDO('mysql:host=localhost;dbname=Experimenteel_IHG_Record', 'root', '');
		
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$this->pdo->query("DROP TABLE IF EXISTS Bedrijven");
	
		$this->pdo->query("
			CREATE TABLE Bedrijven (
				id 		integer 	NOT NULL AUTO_INCREMENT,
				naam 	varchar(50) NOT NULL,
				url 	text,
				PRIMARY KEY (id)
			)
		");
	
		$stmt = $this->pdo->prepare('INSERT INTO Bedrijven (naam, url) VALUES(?, ?)');
		
		for($i = 0; $i < 100; $i++) {
			$stmt->execute(array(
				sprintf('Bedrijf #%02d', $i),
				sprintf('http://www.bedrijf%02d.com', $i)));
		}
	}
	
	public function test_iterator_interface() {
		$query = new IHG_SQL_Atom('SELECT * FROM Bedrijven WHERE id < ? ORDER BY Bedrijven.naam', array(1000));
		
		$record_set = new IHG_Record_Set(
			$this->pdo,
			self::RECORD_TYPE,
			$query);
		
		$i = 0;	
		
		foreach($record_set as $index => $record) {
			if($index !== $i || $record->naam !== sprintf('Bedrijf #%02d', $i)) {
				$this->fail();
			}
			
			$i++;
		}
		
		$this->assertEqual($i, 100);
	}
	
	public function test_rewind() {
		$query = new IHG_SQL_Atom('SELECT * FROM Bedrijven WHERE id <= ? ORDER BY Bedrijven.naam', array(10));
		
		$record_set = new IHG_Record_Set(
			$this->pdo,
			self::RECORD_TYPE,
			$query);
		
		$i = 0;
		
		foreach($record_set as $index => $record) {
			if($index !== $i || $record->naam !== sprintf('Bedrijf #%02d', $i)) {
				$this->fail();
			}
			
			$i++;
		}
		
		$this->assertEqual($i, 10);
		
		$i = 0;
		
		foreach($record_set as $index => $record) {
			if($index !== $i || $record->naam !== sprintf('Bedrijf #%02d', $i)) {
				$this->fail();
			}
			
			$i++;
		}
		
		$this->assertEqual($i, 10);
	}
	
	public function test_seek_and_limit() {
		$query = new IHG_SQL_Atom('SELECT id, naam, url FROM Bedrijven WHERE id <= ? ORDER BY Bedrijven.naam', array(50));
		
		$stmt = $this->pdo->prepare('SELECT COUNT(*) FROM Bedrijven WHERE id <= ?');
		
		$stmt->execute(array(50));
		
		$result = $stmt->fetch(PDO::FETCH_NUM);
		
		$this->assertEqual($result[0], 50);
		
		$record_set = new IHG_Record_Set(
			$this->pdo,
			self::RECORD_TYPE,
			$query);
			
		$this->assertIdentical($record_set->count(), 50);
		
		$new_record_set = $record_set->slice(10, 10);
		
		$this->assertIdentical($new_record_set->count(), 10);
		
		$i = 10;
		
		foreach($new_record_set as $index => $record) {
			if($index !== $i || $record->naam !== sprintf('Bedrijf #%02d', $i)) {
				$this->fail();
			}
			
			$i++;
		}
		
		$this->assertIdentical($i, 20);
		
	}
}

?>