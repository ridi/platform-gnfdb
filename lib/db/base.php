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
use Gnf\db\InterpreterProvider;
use Gnf\db\Interpreter\ItemInterpreter;
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

    /** @var InterpreterProvider */
    private $interpreter_provider;

    // needed for `parent::__construct()`
    public function __construct(?InterpreterProvider $interpreter_provider = null)
    {
        if ($interpreter_provider === null) {
            $interpreter_provider = new InterpreterProvider();
        }
        $this->interpreter_provider = $interpreter_provider;
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
        return $this->interpreter_provider->getWhereInterpreter()->process($value, $key);
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
            return EscapeHelper::escapeColumnName($key) . ' = NULL';
        }

        return EscapeHelper::escapeColumnName($key) . ' = ' . $this->escapeItemExceptNull($value, $key);
    }

    private function serializeUpdate($arr)
    {
        return implode(', ', array_map([&$this, 'callbackSerializeUpdate'], array_keys($arr), $arr));
    }

    private function escapeTable($a)
    {
        return $this->interpreter_provider->getTableInterpreter()->process($a);
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
     * @param mixed       $value
     * @param string|null $column  // is string if update
     *
     * @return string
     */
    private function escapeItemExceptNull($value, $column = null)
    {
        return $this->interpreter_provider->getItemInterpreter()->process($value, $column);
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
        $keys = implode(', ', array_map([EscapeHelper::class, 'escapeColumnName'], $dats_keys));
        $values = implode(', ', array_map([&$this, 'escapeItem'], $dats, $dats_keys));
        $sql = "INSERT INTO " . $table . " (" . $keys . ") VALUES (" . $values . ")";
        $stmt = $this->sqlDoWithoutParsing($sql);

        return $this->getAffectedRows($stmt);
    }

    public function sqlInsertBulk($table, $dat_keys, $dat_valuess)
    {
        $table = $this->escapeItemExceptNull(sqlTable($table));
        $keys = implode(', ', array_map([EscapeHelper::class, 'escapeColumnName'], $dat_keys));
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
        $keys = implode(', ', array_map([EscapeHelper::class, 'escapeColumnName'], $dats_keys));
        $values = implode(', ', array_map([&$this, 'escapeItem'], $dats, $dats_keys));
        $update = $this->serializeUpdate($update);
        $sql = "INSERT INTO " . $table . " (" . $keys . ") VALUES (" . $values . ") ON DUPLICATE KEY UPDATE " . $update;
        $stmt = $this->sqlDoWithoutParsing($sql);

        return min(1, $this->getAffectedRows($stmt));
    }

    public function sqlInsertOrUpdateBulk($table, $dat_keys, $dat_valuess)
    {
        $table = $this->escapeItemExceptNull(sqlTable($table));
        $escape_dat_keys = array_map([EscapeHelper::class, 'escapeColumnName'], $dat_keys);

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
