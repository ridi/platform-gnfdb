<?php
namespace Gnf\db\Helper;

class GnfSqlLimit
{
    public function __construct($from, $count)
    {
        $this->from = (int)$from;
        $this->count = (int)$count;
    }
}
