<?php

class IHG_Formatter_Price
{
	public function __invoke($price, $model = null)
	{
		return htmlspecialchars($model ? $model->valuta_symbool : '€') . ' ' . number_format($price, 2, '.', ',');
	}
}
