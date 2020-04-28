<?php

namespace Gnf\db;

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
use Gnf\db\Superclass\StatementInterface;

abstract class base implements StatementInterface
{
    /** @var array */
    private $dump = [];
    protected $db;
    /** @var int */
    private $transaction_depth = 0;
    /** @var bool */
    private $is_transaction_error = false;

    // needed for `parent::__construct()`
    public function __construct()
    {
    }

    public function getDb()
    {
        return $this->db;
    }

    protected function afterConnect()
    {
        $this->sqlDo("SET NAMES 'utf8'");
    }

    public function sqlBegin()
    {
        if ($this->transaction_depth === 0) {
            $this->transactionBegin();
            $this->is_transaction_error = false;
        } else {
            if ($this->configIsSupportNestedTransaction()) {
                $this->transactionBegin();
            }
        }
        $this->transaction_depth++;
    }

    public function sqlEnd()
    {
        if ($this->is_transaction_error) {
            $this->sqlRollback();

            return false;
        }

        $this->sqlCommit();

        return true;
    }

    public function sqlCommit()
    {
        $this->transaction_depth--;
        if ($this->transaction_depth === 0) {
            $this->transactionCommit();
            $this->is_transaction_error = false;
        } else {
            if ($this->configIsSupportNestedTransaction()) {
                $this->transactionCommit();
            }
            if ($this->transaction_depth < 0) {
                throw new \Exception('[mysql] transaction underflow');
            }
        }
    }

    public function sqlRollback()
    {
        $this->transaction_depth--;
        if ($this->transaction_depth === 0) {
            $this->transactionRollback();
            $this->is_transaction_error = false;
        } else {
            if ($this->configIsSupportNestedTransaction()) {
                $this->transactionRollback();
            }
            if ($this->transaction_depth < 0) {
                throw new \Exception('[mysql] transaction underflow');
            }
        }
    }

    public function isTransactionActive()
    {
        return $this->transaction_depth > 0;
    }

    /**
     * @param $func callable
     *
     * @return bool transaction success
     * @throws \Exception
     */
    public function transactional($func)
    {
        if (!is_callable($func)) {
            throw new \InvalidArgumentException(
                'Expected argument of type "callable", got "' . gettype($func) . '"'
            );
        }

        $this->sqlBegin();

        try {
            $func($this);

            return $this->sqlEnd();
        } catch (\Exception $e) {
            $this->sqlRollback();
            throw $e;
        }
    }

    /**
     * @param $key
     * @param $value
     *
     * @return string
     *
     * return ''(zero length string) if not available
     * return with '(' . xxx . ')' if has two or more clause
     */
    private function callbackSerializeWhere($key, $value)
    {
        if ($value instanceof GnfSqlNull || is_null($value)) {
            return $this->escapeColumnName($key) . ' is NULL';
        }
        if (
            $value instanceof GnfSqlNot
            && ($value->dat instanceof GnfSqlNull || is_null($value->dat))
        ) {
            return $this->escapeColumnName($key) . ' is not NULL';
        }
        if ($value instanceof GnfSqlNot) {
            $ret = $this->callbackSerializeWhere($key, $value->dat);
            if ($ret !== '') {
                return '( !( ' . $ret . ' ) )';
            }

            return '';
        }
        if ($value instanceof GnfSqlLike) {
            return $this->escapeColumnName($key) . ' like "%' . $this->escapeLiteral($value->dat) . '%"';
        }
        if ($value instanceof GnfSqlLikeBegin) {
            return $this->escapeColumnName($key) . ' like "' . $this->escapeLiteral($value->dat) . '%"';
        }
        if ($value instanceof GnfSqlGreater) {
            return $this->escapeColumnName($key) . ' > ' . $this->escapeItemExceptNull($value->dat, $key);
        }
        if ($value instanceof GnfSqlLesser) {
            return $this->escapeColumnName($key) . ' < ' . $this->escapeItemExceptNull($value->dat, $key);
        }
        if ($value instanceof GnfSqlGreaterEqual) {
            return $this->escapeColumnName($key) . ' >= ' . $this->escapeItemExceptNull($value->dat, $key);
        }
        if ($value instanceof GnfSqlLesserEqual) {
            return $this->escapeColumnName($key) . ' <= ' . $this->escapeItemExceptNull($value->dat, $key);
        }
        if ($value instanceof GnfSqlBetween) {
            return $this->escapeColumnName($key) . ' between ' . $this->escapeItemExceptNull($value->dat, $key) . ' and ' .
                $this->escapeItemExceptNull(
                    $value->dat2,
                    $key
                );
        }
        if ($value instanceof GnfSqlRange) {
            return '(' . $this->escapeItemExceptNull($value->dat, $key) . ' <= ' .
                $this->escapeColumnName(
                    $key
                ) . ' and ' . $this->escapeColumnName($key) . ' < ' . $this->escapeItemExceptNull($value->dat2, $key) . ')';
        }
        if ($value instanceof GnfSqlAnd) {
            $ret = [];
            foreach ($value->dat as $dat) {
                if (is_array($dat)) {
                    $ret[] = '( ' . $this->serializeWhere($dat) . ' )';
                } elseif ($dat instanceof GnfSqlNot && is_array($dat->dat)) {
                    $ret[] = '( ! ( ' . $this->serializeWhere($dat->dat) . ' ) )';
                } else {
                    throw new \InvalidArgumentException('process sqlAnd needs where(key, value pair)');
                }
            }
            if (count($ret)) {
                return '( ' . implode(' and ', $ret) . ' )';
            }

            return '';
        }
        if ($value instanceof GnfSqlOr) {
            $ret = [];
            foreach ($value->dat as $dat) {
                if (is_array($dat)) {
                    $ret[] = '( ' . $this->serializeWhere($dat) . ' )';
                } elseif ($dat instanceof GnfSqlNot && is_array($dat->dat)) {
                    $ret[] = '( ! ( ' . $this->serializeWhere($dat->dat) . ' ) )';
                } else {
                    throw new \InvalidArgumentException('process sqlOr needs where(key, value pair)');
                }
            }
            if (count($ret)) {
                return '( ' . implode(' or ', $ret) . ' )';
            }

            return '';
        }
        if (is_int($key)) {
            throw new \InvalidArgumentException('cannot implict int key as column : ' . $key);
        }
        if (is_array($value)) {
            //divide
            $scalars = [];
            $objects = [];
            if (count($value) == 0) {
                throw new \InvalidArgumentException('zero size array, key : ' . $key);
            }
            foreach ($value as $operand) {
                if (is_scalar($operand)) {
                    $scalars[] = $operand;
                } else {
                    $objects[] = $operand;
                }
            }

            //process
            if (count($objects)) {
                foreach ($objects as $k => $object) {
                    $objects[$k] = $this->callbackSerializeWhere($key, $object);
                }
                $objects_query = '( ' . implode(' or ', array_filter($objects, 'strlen')) . ' )';
            } else {
                $objects_query = '';
            }
            if (count($scalars)) {
                $scalars_query = $this->escapeColumnName($key) . ' in ' . $this->escapeItemExceptNull($scalars, $key);
            } else {
                $scalars_query = '';
            }

            //merge
            if (strlen($objects_query) && strlen($scalars_query)) {
                return '( ' . $objects_query . ' or ' . $scalars_query . ' )';
            }

            return $objects_query . $scalars_query;
        }

        return $this->escapeColumnName($key) . ' = ' . $this->escapeItemExceptNull($value, $key);
    }

    private function serializeWhere($array)
    {
        if (count($array) === 0) {
            throw new \InvalidArgumentException('zero size array can not serialize');
        }
        $wheres = array_map([&$this, 'callbackSerializeWhere'], array_keys($array), $array);
        $wheres = array_filter($wheres, 'strlen');

        return implode(' and ', $wheres);
    }

    private function callbackSerializeUpdate($key, $value)
    {
        if ($value instanceof GnfSqlNull || is_null($value)) {
            return $this->escapeColumnName($key) . ' = NULL';
        }

        return $this->escapeColumnName($key) . ' = ' . $this->escapeItemExceptNull($value, $key);
    }

    private function serializeUpdate($arr)
    {
        return implode(', ', array_map([&$this, 'callbackSerializeUpdate'], array_keys($arr), $arr));
    }

    private function escapeTable($a)
    {
        if ($a instanceof GnfSqlJoin) {
            $ret = '';
            foreach ($a->dat as $k => $columns) {
                /** @var $has_join_only_one_column
                 * if $has_join_only_one_column = true
                 * => sqlJoin(array('tb_pay_info.t_id', 'tb_cash.t_id', 'tb_point.t_id'))
                 * if $has_join_only_one_column = false
                 * => sqljoin(array('tb_pay_info.t_id' => array('tb_cash.t_id', 'tb_point.t_id')))
                 */

                $has_join_only_one_column = is_int($k);

                if (!is_array($columns)) {
                    $columns = [$columns];
                }
                if ($has_join_only_one_column) {
                    $last_column = '';
                    foreach ($columns as $key_of_column => $column) {
                        if (strlen($ret) === 0) {
                            $ret .= $this->escapeTableNameFromFullColumnElement($column);
                        } else {
                            $ret .=
                                "\n\t" . $a->type . ' ' . $this->escapeTableNameFromFullColumnElement($column) .
                                "\n\t\t" . 'on ' . $this->escapeColumnName($last_column) .
                                ' = ' . $this->escapeColumnName($column);
                        }
                        $last_column = $column;
                    }
                } else {
                    /** @var $has_more_joinable_where_clause
                     * if $has_more_joinable_where_clause = true
                     *  => sqljoin(array('tb_pay_info.t_id' => array('tb_cash.t_id', 'tb_cash.type' => 'event')))
                     * if $has_more_joinable_where_clause = false
                     *  => sqljoin(array('tb_pay_info.t_id' => array('tb_cash.t_id')))
                     */

                    $joinable_where_clause = [];
                    foreach ($columns as $key_of_column => $column) {
                        $has_more_joinable_where_clause = !is_int($key_of_column);
                        if ($has_more_joinable_where_clause) {
                            $table_name = $this->escapeTableNameFromFullColumnElement($key_of_column);
                            $joinable_where_clause[$table_name][$key_of_column] = $column;
                        }
                    }

                    foreach ($columns as $key_of_column => $column) {
                        $has_more_joinable_where_clause = !is_int($key_of_column);
                        if (!$has_more_joinable_where_clause) {
                            $join_left_column = $k;
                            $join_right_column = $column;

                            if (strlen($ret) == 0) {
                                $ret .= $this->escapeTableNameFromFullColumnElement($join_left_column) . ' ' .
                                    "\n\t" . $a->type . ' ' .
                                    $this->escapeTableNameFromFullColumnElement($join_right_column) .
                                    "\n\t\t" . 'on ' .
                                    $this->escapeColumnName($join_left_column) .
                                    ' = ' .
                                    $this->escapeColumnName($join_right_column);
                            } else {
                                $ret .= ' ' .
                                    "\n\t" . $a->type .
                                    ' ' .
                                    $this->escapeTableNameFromFullColumnElement($join_right_column) .
                                    "\n\t\t" . 'on ' .
                                    $this->escapeColumnName($join_left_column) .
                                    ' = ' .
                                    $this->escapeColumnName($join_right_column);
                            }
                            $join_right_table_name = $this->escapeTableNameFromFullColumnElement($join_right_column);
                            if (isset($joinable_where_clause[$join_right_table_name])) {
                                $ret .= ' and '
                                    . $this->serializeWhere($joinable_where_clause[$join_right_table_name]);
                                unset($joinable_where_clause[$join_right_table_name]);
                            }
                        }
                    }
                    foreach ($joinable_where_clause as $table_name => $where) {
                        $ret .= ' and ' . $this->serializeWhere($where);
                    }
                }
            }

            return $ret;
        }
        if ($a instanceof GnfSqlTable) {
            $a = $a->dat;
        }

        return $this->escapeTableNameFromTableElement($a);
    }

    private function escapeTableNameFromTableElement($tablename)
    {
        return $this->escapeFullColumnElement($tablename);
    }

    private function escapeFullColumnElement($table_column_element)
    {
        $table_column_element = preg_replace("/\..+/", "", $table_column_element);
        $table_column_element = str_replace('`', '', $table_column_element);

        return '`' . $table_column_element . '`';
    }

    private function escapeTableNameFromFullColumnElement($fullsized_column)
    {
        $dot_count = substr_count($fullsized_column, '.');
        if ($dot_count !== 1 && $dot_count !== 2) {
            throw new \Exception('invalid column name (' . $fullsized_column . ') to extract table name');
        }
        $fullsized_column_items = explode('.', $fullsized_column);
        array_pop($fullsized_column_items);
        $fullsized_column_items = array_map(
            function ($item) {
                return $this->escapeFullColumnElement($item);
            },
            $fullsized_column_items
        );

        return implode('.', $fullsized_column_items);
    }

    private function escapeColumnName($k)
    {
        if (is_int($k)) {
            throw new \InvalidArgumentException('cannot implict int key as column : ' . $k);
        }
        $k = str_replace(['`', '.'], ['', '`.`'], $k);

        return '`' . $k . '`';
    }

    //referenced yutarbbs(http://code.google.com/p/yutarbbs) by holies
    /**
     * @param $value
     *
     * @return string
     */
    private function escapeItem($value)
    {
        if ($value instanceof GnfSqlNull || is_null($value)) {
            return 'NULL';
        }

        return $this->escapeItemExceptNull($value);
    }

    /**
     * @param $value
     * @param $column null|string // is string if update
     *
     * @return string
     */
    private function escapeItemExceptNull($value, $column = null)
    {
        if (is_scalar($value)) {
            if (is_bool($value)) {
                if ($value) {
                    return 'true';
                }

                return 'false';
            }

            return '"' . $this->escapeLiteral($value) . '"';
        }

        if (is_array($value)) {
            if (count($value) === 0) {
                throw new \InvalidArgumentException('zero size array, key : ' . (string)$column);
            }

            return '(' . implode(', ', array_map([&$this, 'escapeItemExceptNull'], $value)) . ')';
        }

        if (is_object($value)) {
            if ($value instanceof GnfSqlNow) {
                return 'now()';
            }
            if ($value instanceof GnfSqlPassword) {
                return 'password(' . $this->escapeItemExceptNull($value->dat) . ')';
            }
            if ($value instanceof GnfSqlLike) {
                return '"%' . $this->escapeLiteral($value->dat) . '%"';
            }
            if ($value instanceof GnfSqlLikeBegin) {
                return '"' . $this->escapeLiteral($value->dat) . '%"';
            }
            if ($value instanceof GnfSqlRaw) {
                return $value->dat;
            }
            if ($value instanceof GnfSqlTable) {
                return $this->escapeTable($value);
            }
            if ($value instanceof GnfSqlColumn) {
                return $this->escapeColumnName($value->dat);
            }
            if ($value instanceof GnfSqlWhere) {
                return $this->serializeWhere($value->dat);
            }
            if ($value instanceof GnfSqlLimit) {
                return 'limit ' . $value->from . ', ' . $value->count;
            }
            if ($value instanceof GnfSqlAdd && is_string($column)) {//only for update
                if ($value->dat > 0) {
                    return $this->escapeColumnName($column) . ' + ' . ($value->dat);
                }
                if ($value->dat < 0) {
                    return $this->escapeColumnName($column) . ' ' . ($value->dat);
                }

                return $this->escapeColumnName($column);
            }
            if ($value instanceof GnfSqlStrcat && is_string($column)) {//only for update
                return 'concat(ifnull(' . $this->escapeColumnName($column) . ', ""), ' . $this->escapeItemExceptNull(
                        $value->dat
                    ) . ')';
            }

            return $this->escapeItemExceptNull($value->dat);
        }

        throw new \InvalidArgumentException('invalid escape item');
    }

    private function parseQuery($args)
    {
        if (count($args) >= 1) {
            $sql = array_shift($args);
            $escaped_items = array_map([&$this, 'escapeItemExceptNull'], $args);

            $breaked_sql_blocks = explode('?', $sql);
            foreach ($breaked_sql_blocks as $index => $breaked_sql_block) {
                if ($index === 0) {
                    continue;
                }
                if (count($escaped_items) === 0) {
                    throw new \InvalidArgumentException('unmatched "? count" with "argument count"');
                }
                $escaped_item = array_shift($escaped_items);
                $breaked_sql_blocks[$index] = $escaped_item . $breaked_sql_block;
            }
            if (count($escaped_items) !== 0) {
                throw new \InvalidArgumentException('unmatched "? count" with "argument count"');
            }

            return implode('', $breaked_sql_blocks);
        }

        return "";
    }

    public function sqlDumpBegin()
    {
        if (!is_array($this->dump)) {
            $this->dump = [];
        }
        $this->dump[] = [];
    }

    public function sqlDumpEnd()
    {
        if (count($this->dump)) {
            return array_pop($this->dump);
        }

        return null;
    }

    public function sqlDo(...$args)
    {
        $sql = $this->parseQuery($args);

        return $this->sqlDoWithoutParsing($sql);
    }

    /**
     * @param $sql
     *
     * @return mixed
     * @throws \Exception
     */
    private function sqlDoWithoutParsing($sql)
    {
        if (count($this->dump)) {
            foreach ($this->dump as $k => $v) {
                $this->dump[$k][] = $sql;
            }
        }
        $ret = $this->query($sql);
        $err = $this->getError($ret);
        if ($err !== null) {
            $this->is_transaction_error = true;
            throw new \Exception('[sql error] ' . $err->message . ' : ' . $sql);
        }

        return $ret;
    }

    public function sqlDump(...$args)
    {
        return $this->parseQuery($args);
    }

    public function sqlData(...$args)
    {
        $sql = $this->parseQuery($args);
        $res = $this->sqlDoWithoutParsing($sql);
        if ($res) {
            $arr = $this->fetchRow($res);
            if (isset($arr[0])) {
                return $arr[0];
            }
        }

        return null;
    }

    public function sqlDatas(...$args)
    {
        $sql = $this->parseQuery($args);
        $res = $this->sqlDoWithoutParsing($sql);
        $ret = [];
        if ($res) {
            while ($arr = $this->fetchRow($res)) {
                $ret[] = $arr[0];
            }
        }

        return $ret;
    }

    public function sqlArray(...$args)
    {
        $sql = $this->parseQuery($args);
        $res = $this->sqlDoWithoutParsing($sql);
        if ($res) {
            $arr = $this->fetchRow($res);
            if ($arr) {
                return $arr;
            }
        }

        return null;
    }

    public function sqlArrays(...$args)
    {
        $sql = $this->parseQuery($args);
        $res = $this->sqlDoWithoutParsing($sql);
        $ret = [];
        if ($res) {
            while ($arr = $this->fetchRow($res)) {
                $ret[] = $arr;
            }
        }

        return $ret;
    }

    public function sqlDict(...$args)
    {
        $sql = $this->parseQuery($args);
        $res = $this->sqlDoWithoutParsing($sql);
        if ($res) {
            $arr = $this->fetchAssoc($res);
            if ($arr !== false) {
                return $arr;
            }
        }

        return null;
    }

    public function sqlDicts(...$args)
    {
        $sql = $this->parseQuery($args);
        $res = $this->sqlDoWithoutParsing($sql);
        $ret = [];
        if ($res) {
            while ($arr = $this->fetchAssoc($res)) {
                $ret[] = $arr;
            }
        }

        return $ret;
    }

    public function sqlObject(...$args)
    {
        $sql = $this->parseQuery($args);
        $res = $this->sqlDoWithoutParsing($sql);
        if ($res) {
            $arr = $this->fetchObject($res);
            if ($arr !== false) {
                return $arr;
            }
        }

        return null;
    }

    public function sqlObjects(...$args)
    {
        $sql = $this->parseQuery($args);
        $res = $this->sqlDoWithoutParsing($sql);
        $ret = [];
        if ($res) {
            while ($arr = $this->fetchObject($res)) {
                $ret[] = $arr;
            }
        }

        return $ret;
    }

    public function sqlLine(...$args)
    {
        $sql = $this->parseQuery($args);
        $res = $this->sqlDoWithoutParsing($sql);
        if ($res) {
            $arr = $this->fetchRow($res);
            if ($arr !== false) {
                return $arr;
            }
        }

        return null;
    }

    public function sqlLines(...$args)
    {
        $sql = $this->parseQuery($args);
        $res = $this->sqlDoWithoutParsing($sql);
        $ret = [];
        if ($res) {
            while ($arr = $this->fetchRow($res)) {
                $ret[] = $arr;
            }
        }

        return $ret;
    }

    public function sqlDictsArgs()
    {
        $args = func_get_args();
        if (!is_array($args[1])) {
            trigger_error("sqlDictsArgs's second argument must be an array");
            die;
        }
        array_unshift($args[1], $args[0]);
        $args = $args[1];

        return @call_user_func_array([&$this, 'sqlDicts'], $args);
    }

    public function sqlCount($table, $where)
    {
        $sql = "SELECT count(*) FROM ? WHERE ?";

        return $this->sqlData($sql, sqlTable($table), sqlWhere($where));
    }

    public function sqlInsert($table, $dats)
    {
        $table = $this->escapeItemExceptNull(sqlTable($table));
        $dats_keys = array_keys($dats);
        $keys = implode(', ', array_map([&$this, 'escapeColumnName'], $dats_keys));
        $values = implode(', ', array_map([&$this, 'escapeItem'], $dats, $dats_keys));
        $sql = "INSERT INTO " . $table . " (" . $keys . ") VALUES (" . $values . ")";
        $stmt = $this->sqlDoWithoutParsing($sql);

        return $this->getAffectedRows($stmt);
    }

    public function sqlInsertBulk($table, $dat_keys, $dat_valuess)
    {
        $table = $this->escapeItemExceptNull(sqlTable($table));
        $keys = implode(', ', array_map([&$this, 'escapeColumnName'], $dat_keys));
        $bulk_values = [];
        foreach ($dat_valuess as $dat_values) {
            $bulk_values[] = implode(', ', array_map([&$this, 'escapeItem'], $dat_values));
        }
        $sql = "INSERT INTO " . $table . " (" . $keys . ") VALUES ";
        foreach ($bulk_values as $values) {
            $sql .= ' ( ' . $values . ' ),';
        }
        $sql = substr($sql, 0, -1);
        $stmt = $this->sqlDoWithoutParsing($sql);

        return $this->getAffectedRows($stmt);
    }

    public function sqlInsertOrUpdate($table, $dats, $update = null)
    {
        /**
         * MySQL 5.1 에서 duplicate key update 구문에 unique 컬럼을 쓰면 퍼포먼스에 문제가 있다.
         * 따라서 update 에 해당하는 것만 따로 받을 수 있도록 수정하였음.
         * 이 후 MySQL 버전에서 이 문제가 해결되면 $update 변수는 삭제될 예정.
         */
        if ($update === null) {
            $update = $dats;
        }

        $table = $this->escapeItemExceptNull(sqlTable($table));
        $dats_keys = array_keys($dats);
        $keys = implode(', ', array_map([&$this, 'escapeColumnName'], $dats_keys));
        $values = implode(', ', array_map([&$this, 'escapeItem'], $dats, $dats_keys));
        $update = $this->serializeUpdate($update);
        $sql = "INSERT INTO " . $table . " (" . $keys . ") VALUES (" . $values . ") ON DUPLICATE KEY UPDATE " . $update;
        $stmt = $this->sqlDoWithoutParsing($sql);

        return min(1, $this->getAffectedRows($stmt));
    }

    public function sqlInsertOrUpdateBulk($table, $dat_keys, $dat_valuess)
    {
        $table = $this->escapeItemExceptNull(sqlTable($table));
        $escape_dat_keys = array_map([&$this, 'escapeColumnName'], $dat_keys);

        $keys = implode(', ', $escape_dat_keys);

        $bulk_values = [];
        foreach ($dat_valuess as $dat_values) {
            $bulk_values[] = implode(', ', array_map([&$this, 'escapeItem'], $dat_values));
        }
        $sql = "INSERT INTO " . $table . " (" . $keys . ") VALUES";
        foreach ($bulk_values as $values) {
            $sql .= ' ( ' . $values . ' ),';
        }
        $sql = substr($sql, 0, -1);
        $sql .= " ON DUPLICATE KEY UPDATE";
        foreach ($escape_dat_keys as $escape_dat_key) {
            $sql .= ' ' . $escape_dat_key . ' = VALUES ( ' . $escape_dat_key . ' ),';
        }
        $sql = substr($sql, 0, -1);

        $stmt = $this->sqlDoWithoutParsing($sql);

        return $this->getAffectedRows($stmt);
    }

    public function sqlUpdate($table, $dats, $where)
    {
        $table = $this->escapeItemExceptNull(sqlTable($table));
        $update = $this->serializeUpdate($dats);
        $where = $this->serializeWhere($where);
        $sql = "UPDATE " . $table . " SET " . $update . " WHERE " . $where;
        $stmt = $this->sqlDoWithoutParsing($sql);

        return $this->getAffectedRows($stmt);
    }

    public function sqlDelete($table, $where)
    {
        $table = $this->escapeItemExceptNull(sqlTable($table));
        $where = $this->serializeWhere($where);
        $sql = "DELETE FROM " . $table . " WHERE " . $where;
        $stmt = $this->sqlDoWithoutParsing($sql);

        return $this->getAffectedRows($stmt);
    }

    protected function checkConnectionOrTry()
    {
        if ($this->hasConnected()) {
            return;
        }
        $this->doConnect();
    }
}
