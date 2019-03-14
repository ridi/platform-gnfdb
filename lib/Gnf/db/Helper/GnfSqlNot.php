<?php

namespace Gnf\db\Helper;

class GnfSqlNot extends GnfSqlCompareOperator
{
    public function __construct($in)
    {
        $this->dat = $in;
    }

    public static function isSwitchable($in)
    {
        return
            is_a($in, '\Gnf\db\Helper\GnfSqlNot') &&
            (
                is_a($in->dat, '\Gnf\db\Helper\GnfSqlCompareOperator')
                || is_a($in->dat, '\Gnf\db\Helper\GnfSqlNull')
                || is_scalar($in->dat)
                || is_array($in->dat)
            );
    }
}
