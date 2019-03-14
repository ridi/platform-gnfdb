<?php

namespace Gnf\db\Helper;

class GnfSqlGreaterEqual extends GnfSqlCompareOperator
{
    public function __construct($in)
    {
        $this->dat = $in;
    }
}
