<?php

namespace Gnf\db\Helper;

class GnfSqlJoin extends GnfSqlTable
{
	public $type;

	public function __construct($in, $type = 'join')
	{
		parent::__construct($in);
		$this->type = $type;
	}
}
