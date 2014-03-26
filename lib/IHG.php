<?php

error_reporting(E_ALL & ~E_STRICT); 
ini_set("display_errors", 1);

set_include_path(implode(PATH_SEPARATOR, array(
	'./app/models',
	'./app/controllers',
	'./lib',
	dirname(__FILE__),
	get_include_path()
	)));

function __autoload($classname) {
	include sprintf('%s.php', $classname);
}

function ifsetor(&$a, $b = null) {
	return isset($a) ? $a : $b;
}

function ihg_is_equal($a, $b) {

	if(false && $a instanceof IHG_Record && $b instanceof IHG_Record) {
		$r = $a->is_equal_to($b);
	} else {
		$r = $a == $b;
	}
	
	return $r;
}

function ihg_in_array($needle, $haystack) {	
	if(!method_exists($needle, 'is_equal_to')) {
		return in_array($needle, $haystack);
	} else {
		foreach($haystack as $item) {
			if($needle->is_equal_to($item)) return true;
		}
		
		return false;
	}
}

function array_trim(&$array)
{
	// while the array still has elements and the last element evaluates to false
	while (count($array) && !end($array))
		array_pop($array); // pop it off
}

