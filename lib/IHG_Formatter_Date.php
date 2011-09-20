<?php

class IHG_Formatter_Date
{
	public function __invoke(DateTime $date = null)
	{
		return $date !== null
			? $date->format('d–m–Y')
			: '';
	}
}

?>
