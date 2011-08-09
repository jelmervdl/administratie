<?php

interface IHG_HTML_Table_Grouper
{
	public function has_header($group);
	
	public function format_header($group);
	
	public function classify($object);
}
