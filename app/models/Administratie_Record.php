<?php

abstract class Administratie_Record extends IHG_Record
{
	protected function _record_type_class($record_type) {
		return sprintf('Administratie_%s', ucfirst($record_type));
	}
}