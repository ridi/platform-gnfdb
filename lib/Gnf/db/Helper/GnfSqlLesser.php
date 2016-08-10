<?php

namespace Gnf\db\Helper;

class GnfSqlLesser extends GnfSqlCompareOperator
{
	public function __construct($in)
	{
		$this->dat = $in;
	}
}
