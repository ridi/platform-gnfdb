<?php

namespace Gnf\db\Helper;

class GnfSqlLikeBegin extends GnfSqlCompareOperator
{
	public function __construct($in)
	{
		$this->dat = $in;
	}
}
