<?php

namespace Gnf\db\Helper;

class GnfSqlLesserEqual extends GnfSqlCompareOperator
{
    public function __construct($in)
    {
        $this->dat = $in;
    }
}
