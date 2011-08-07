<?php

class IHG_Formatter_Rich
{
	public function __invoke($text)
	{
		$text_formatter = new IHG_Formatter_Text();
		
		$list_formatter = new IHG_Formatter_List($text_formatter);
		
		return $list_formatter($text);
	}
}
