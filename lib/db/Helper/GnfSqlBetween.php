<?php

namespace Gnf\db\Helper;

class GnfSqlBetween
{
    public $dat;
    public $dat2;

    public function __construct($in, $in2)
    {
        $this->dat = $in;
        $this->dat2 = $in2;
    }
}
