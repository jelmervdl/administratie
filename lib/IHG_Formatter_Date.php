<?php

class IHG_Formatter_Date
{
	public function __invoke(DateTime $date)
	{
		return $date->format('d–m–Y');
	}
}

?>