<?php

class IHG_Formatter_Text
{
	public function __invoke($text)
	{
		$sanitized = htmlspecialchars($text, ENT_COMPAT, 'utf-8');
		
		return nl2br($sanitized);
	}
}