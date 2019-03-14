<?php

use Gnf\db\Helper\GnfSqlAdd;
use Gnf\db\Helper\GnfSqlAnd;
use Gnf\db\Helper\GnfSqlBetween;
use Gnf\db\Helper\GnfSqlColumn;
use Gnf\db\Helper\GnfSqlGreater;
use Gnf\db\Helper\GnfSqlGreaterEqual;
use Gnf\db\Helper\GnfSqlJoin;
use Gnf\db\Helper\GnfSqlLesser;
use Gnf\db\Helper\GnfSqlLesserEqual;
use Gnf\db\Helper\GnfSqlLike;
use Gnf\db\Helper\GnfSqlLikeBegin;
use Gnf\db\Helper\GnfSqlLimit;
use Gnf\db\Helper\GnfSqlNot;
use Gnf\db\Helper\GnfSqlNow;
use Gnf\db\Helper\GnfSqlNull;
use Gnf\db\Helper\GnfSqlOr;
use Gnf\db\Helper\GnfSqlPassword;
use Gnf\db\Helper\GnfSqlRange;
use Gnf\db\Helper\GnfSqlRaw;
use Gnf\db\Helper\GnfSqlStrcat;
use Gnf\db\Helper\GnfSqlTable;
use Gnf\db\Helper\GnfSqlWhere;

if (!function_exists('sqlAdd')) {
    function sqlAdd($in)
    {
        return new GnfSqlAdd($in);
    }
}

if (!function_exists('sqlStrcat')) {
    function sqlStrcat($in)
    {
        return new GnfSqlStrcat($in);
    }
}
if (!function_exists('sqlPassword')) {
    function sqlPassword($in)
    {
        return new GnfSqlPassword($in);
    }
}
if (!function_exists('sqlLike')) {
    function sqlLike($in)
    {
        //__sqlNot을 포함관계에서 최상단으로
        if (is_a($in, '\Gnf\db\Helper\GnfSqlNot') && is_a($in->dat, '\Gnf\db\Helper\GnfSqlCompareOperator')) {
            $wrapper = new GnfSqlLike($in->dat);

            return new GnfSqlNot($wrapper);
        }

        return new GnfSqlLike($in);
    }

}
if (!function_exists('sqlLikeBegin')) {
    function sqlLikeBegin($in)
    {
        //__sqlNot을 포함관계에서 최상단으로
        if (is_a($in, '\Gnf\db\Helper\GnfSqlNot') && is_a($in->dat, '\Gnf\db\Helper\GnfSqlCompareOperator')) {
            $wrapper = new GnfSqlLikeBegin($in->dat);

            return new GnfSqlNot($wrapper);
        }

        return new GnfSqlLikeBegin($in);
    }
}
if (!function_exists('sqlRaw')) {
    function sqlRaw($in)
    {
        return new GnfSqlRaw($in);
    }

}
if (!function_exists('sqlTable')) {
    function sqlTable($in)
    {
        if (is_a($in, '\Gnf\db\Helper\GnfSqlTable')) {
            return $in;
        }

        return new GnfSqlTable($in);
    }
}
if (!function_exists('sqlColumn')) {
    function sqlColumn($in)
    {
        if (is_a($in, '\Gnf\db\Helper\GnfSqlColumn')) {
            return $in;
        }

        return new GnfSqlColumn($in);
    }
}
if (!function_exists('sqlJoin')) {
    function sqlJoin($in, $type = 'join')
    {
        if (!is_array($in)) {
            $in = func_get_args();
        }

        return new GnfSqlJoin($in, $type);
    }
}
if (!function_exists('sqlLeftJoin')) {
    function sqlLeftJoin($in)
    {
        if (!is_array($in)) {
            $in = func_get_args();
        }

        return new GnfSqlJoin($in, 'left join');
    }
}
if (!function_exists('sqlInnerJoin')) {
    function sqlInnerJoin($in)
    {
        if (!is_array($in)) {
            $in = func_get_args();
        }

        return new GnfSqlJoin($in, 'inner join');
    }
}
if (!function_exists('sqlWhere')) {
    function sqlWhere(array $in)
    {
        return new GnfSqlWhere($in);
    }
}
if (!function_exists('sqlAnd')) {
    function sqlAnd()
    {
        $input = func_get_args();
        $has_scalar_only = true;
        foreach ($input as $v) {
            $is_could_treat_as_scalar = (is_scalar($v) || $v instanceof GnfSqlNot);
            if (!($is_could_treat_as_scalar)) {
                $has_scalar_only = false;
                break;
            }
        }
        if ($has_scalar_only) {
            return $input;
        }

        return new GnfSqlAnd($input);
    }
}
if (!function_exists('sqlAndArray')) {
    function sqlAndArray(array $args)
    {
        $input = $args;
        $has_scalar_only = true;
        foreach ($input as $v) {
            $is_could_treat_as_scalar = (is_scalar($v) || $v instanceof GnfSqlNot);
            if (!($is_could_treat_as_scalar)) {
                $has_scalar_only = false;
                break;
            }
        }
        if ($has_scalar_only) {
            return $input;
        }

        return new GnfSqlAnd($args);
    }
}
if (!function_exists('sqlOr')) {
    function sqlOr()
    {
        $input = func_get_args();
        $has_scalar_only = true;
        foreach ($input as $v) {
            $is_could_treat_as_scalar = (is_scalar($v) || $v instanceof GnfSqlNot);
            if (!($is_could_treat_as_scalar)) {
                $has_scalar_only = false;
                break;
            }
        }
        if ($has_scalar_only) {
            return $input;
        }

        return new GnfSqlOr($input);
    }
}
if (!function_exists('sqlOrArray')) {
    function sqlOrArray(array $args)
    {
        $input = $args;
        $has_scalar_only = true;
        foreach ($input as $v) {
            $is_could_treat_as_scalar = (is_scalar($v) || $v instanceof GnfSqlNot);
            if (!($is_could_treat_as_scalar)) {
                $has_scalar_only = false;
                break;
            }
        }
        if ($has_scalar_only) {
            return $input;
        }

        return new GnfSqlOr($args);
    }
}
if (!function_exists('sqlNot')) {
    function sqlNot($in)
    {
        //부정의 부정은 긍정
        if (GnfSqlNot::isSwitchable($in)) {
            return $in->dat;
        }

        return new GnfSqlNot($in);
    }
}
if (!function_exists('sqlGreaterEqual')) {
    function sqlGreaterEqual($in)
    {
        //__sqlNot을 포함관계에서 최상단으로
        if (GnfSqlNot::isSwitchable($in)) {
            $wrapper = new GnfSqlGreaterEqual($in->dat);

            return new GnfSqlNot($wrapper);
        }

        return new GnfSqlGreaterEqual($in);
    }
}
if (!function_exists('sqlGreater')) {
    function sqlGreater($in)
    {
        //__sqlNot을 포함관계에서 최상단으로
        if (GnfSqlNot::isSwitchable($in)) {
            $wrapper = new GnfSqlGreater($in->dat);

            return new GnfSqlNot($wrapper);
        }

        return new GnfSqlGreater($in);
    }
}
if (!function_exists('sqlLesserEqual')) {
    function sqlLesserEqual($in)
    {
        //__sqlNot을 포함관계에서 최상단으로
        if (GnfSqlNot::isSwitchable($in)) {
            $wrapper = new GnfSqlLesserEqual($in->dat);

            return new GnfSqlNot($wrapper);
        }

        return new GnfSqlLesserEqual($in);
    }

}
if (!function_exists('sqlLesser')) {
    function sqlLesser($in)
    {
        //__sqlNot을 포함관계에서 최상단으로
        if (GnfSqlNot::isSwitchable($in)) {
            $wrapper = new GnfSqlLike($in->dat);

            return new GnfSqlNot($wrapper);
        }

        return new GnfSqlLesser($in);
    }
}
if (!function_exists('sqlBetween')) {
    function sqlBetween($in, $in2)
    {
        return new GnfSqlBetween($in, $in2);
    }
}
if (!function_exists('sqlRange')) {
    function sqlRange($in, $in2)
    {
        return new GnfSqlRange($in, $in2);
    }
}
if (!function_exists('sqlLimit')) {
    function sqlLimit()
    {
        $in = func_get_args();
        if (count($in) == 1) {
            return new GnfSqlLimit(0, $in[0]);
        }

        return new GnfSqlLimit($in[0], $in[1]);
    }
}
if (!function_exists('sqlNow')) {
    function sqlNow()
    {
        return new GnfSqlNow();
    }
}
if (!function_exists('sqlNull')) {
    function sqlNull()
    {
        return new GnfSqlNull();
    }
}
