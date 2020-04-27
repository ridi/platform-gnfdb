<?php

namespace Gnf\db\Helper;

class GnfSqlAdd
{
    /** @var int */
    public $dat;

    public function __construct($in)
    {
        $this->dat = (int)$in;
    }
}
