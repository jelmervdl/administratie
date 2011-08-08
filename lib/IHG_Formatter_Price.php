<?php

class IHG_Formatter_Price
{
	public function __invoke($price)
	{
		return '&euro; ' . number_format($price, 2, '.', ',');
	}
}
