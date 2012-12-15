<?php

abstract class Administratie_Record extends IHG_Record
{
	protected function _record_type_class($record_type) {

		$parts = explode('_', $record_type);

		foreach ($parts as &$part)
		{
			// Things like "BTW" should be all upper case
			if (strlen($part) <= 3)
				$part = strtoupper($part);
			
			// Everything else just starts with a capital
			else
				$part = ucfirst($part);
		}

		return sprintf('Administratie_%s', implode('_', $parts));
	}
}