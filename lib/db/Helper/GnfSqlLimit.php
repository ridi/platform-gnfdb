<?php
namespace Gnf\db\Helper;

class GnfSqlLimit
{
    /** @var int */
    public $from;
    /** @var int */
    public $count;

    public function __construct($from, $count)
    {
        $this->from = (int)$from;
        $this->count = (int)$count;
    }
}
