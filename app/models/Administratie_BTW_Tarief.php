<?php

class Administratie_BTW_Tarief extends Administratie_Record
{
	protected function _table_name($query_type)
	{
		return 'btw_tarieven';
	}
	
	protected function _properties()
	{
		return array(
			'id',
			'percentage'
		);
	}
	
	public function is_deletable()
	{
		return false;
	}
}
