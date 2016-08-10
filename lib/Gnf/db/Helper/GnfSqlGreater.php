<?php
namespace Gnf\db\Helper;

class GnfSqlGreater extends GnfSqlCompareOperator
{
	public function __construct($in)
	{
		$this->dat = $in;
	}
}
