<?php

namespace Gnf\db\Helper;

class GnfSqlLike extends GnfSqlCompareOperator
{
	public function __construct($in)
	{
		$this->dat = $in;
	}
}
