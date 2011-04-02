<?php

class Administratie_Contactpersoon_Provider extends IHG_Record_Provider
{
	public function __construct(PDO $pdo, $record_name = 'Administratie_Contactpersoon')
	{
		parent::__construct($pdo, $record_name);
	}
	
	public function find_all($conditions = array())
	{
		if (!isset($conditions['vervangen_door']))
			$conditions['vervangen_door'] = null;
		
		return parent::find_all($conditions);
	}
}