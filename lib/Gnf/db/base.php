<?php

namespace Gnf\db;

use Gnf\db\Helper\GnfSqlNow;
use Gnf\db\Superclass\gnfDbinterface;
use InvalidArgumentException;

abstract class base implements gnfDbinterface
{
	private $dump;
	protected $db;
	private $transactionDepth = 0;
	private $transactionError = false;

	public function getDb()
	{
		return $this->db;
	}

	public function __construct()
	{

	}

	protected function afterConnect()
	{
		$this->sqlDo("SET NAMES 'utf8'");
	}

	public function sqlBegin()
	{
		if ($this->transactionDepth == 0) {
			$this->transactionBegin();
			$this->transactionError = false;
		} else {
			if ($this->configIsSupportNestedTransaction()) {
				$this->transactionBegin();
			}
		}
		$this->transactionDepth++;
	}

	public function sqlEnd()
	{
		if ($this->transactionError) {
			$this->sqlRollback();
			return false;
		} else {
			$this->sqlCommit();
			return true;
		}
	}

	public function sqlCommit()
	{
		$this->transactionDepth--;
		if ($this->transactionDepth == 0) {
			$this->transactionCommit();
			$this->transactionError = false;
		} else {
			if ($this->configIsSupportNestedTransaction()) {
				$this->transactionCommit();
			}
			if ($this->transactionDepth < 0) {
				throw new \Exception('[mysql] transaction underflow');
			}
		}
	}

	public function sqlRollback()
	{
		$this->transactionDepth--;
		if ($this->transactionDepth == 0) {
			$this->transactionRollback();
			$this->transactionError = false;
		} else {
			if ($this->configIsSupportNestedTransaction()) {
				$this->transactionRollback();
			}
			if ($this->transactionDepth < 0) {
				throw new \Exception('[mysql] transaction underflow');
			}
		}
	}

	public function isTransactionActive()
	{
		return $this->transactionDepth > 0;
	}

	/**
	 * @param $func callable
	 * @return bool transaction success
	 * @throws \Exception
	 */
	public function transactional($func)
	{
		if (!is_callable($func)) {
			throw new InvalidArgumentException(
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
	 * @return string
	 *
	 * return ''(zero length string) if not available
	 * return with '(' . xxx . ')' if has two or more clause
	 */
	private function callbackSerializeWhere($key, $value)
	{
		if (is_a($value, '\Gnf\db\Helper\GnfSqlNull') || is_null($value)) {
			return $this->escapeColumnName($key) . ' is null';
		}
		if (is_a($value, '\Gnf\db\Helper\GnfSqlNot') &&
			(
				is_a($value->dat, '\Gnf\db\Helper\GnfSqlNull') ||
				is_null($value->dat)
			)
		) {
			return $this->escapeColumnName($key) . ' is not null';
		}
		if (is_a($value, '\Gnf\db\Helper\GnfSqlNot')) {
			$ret = $this->callbackSerializeWhere($key, $value->dat);
			if (strlen($ret)) {
				return '( !( ' . $ret . ' ) )';
			}
			return '';
		}
		if (is_a($value, '\Gnf\db\Helper\GnfSqlLike')) {
			return $this->escapeColumnName($key) . ' like "%' . $this->escapeLiteral($value->dat) . '%"';
		}
		if (is_a($value, '\Gnf\db\Helper\GnfSqlLikeBegin')) {
			return $this->escapeColumnName($key) . ' like "' . $this->escapeLiteral($value->dat) . '%"';
		}
		if (is_a($value, '\Gnf\db\Helper\GnfSqlGreater')) {
			return $this->escapeColumnName($key) . ' > ' . $this->escapeItem($value->dat, $key);
		}
		if (is_a($value, '\Gnf\db\Helper\GnfSqlLesser')) {
			return $this->escapeColumnName($key) . ' < ' . $this->escapeItem($value->dat, $key);
		}
		if (is_a($value, '\Gnf\db\Helper\GnfSqlGreaterEqual')) {
			return $this->escapeColumnName($key) . ' >= ' . $this->escapeItem($value->dat, $key);
		}
		if (is_a($value, '\Gnf\db\Helper\GnfSqlLesserEqual')) {
			return $this->escapeColumnName($key) . ' <= ' . $this->escapeItem($value->dat, $key);
		}
		if (is_a($value, '\Gnf\db\Helper\GnfSqlBetween')) {
			return $this->escapeColumnName($key) . ' between ' . $this->escapeItem(
				$value->dat,
				$key
			) . ' and ' . $this->escapeItem($value->dat2, $key);
		}
		if (is_a($value, '\Gnf\db\Helper\GnfSqlRange')) {
			return '(' . $this->escapeItem($value->dat, $key) . ' <= ' . $this->escapeColumnName(
				$key
			) . ' and ' . $this->escapeColumnName($key) . ' < ' . $this->escapeItem($value->dat2, $key) . ')';
		}
		if (is_a($value, '\Gnf\db\Helper\GnfSqlOr')) {
			$ret = [];
			foreach ($value->dat as $dat) {
				if (is_array($dat)) {
					$ret[] = '( ' . $this->serializeWhere($dat) . ' )';
				}
			}
			if (count($ret)) {
				return '( ' . implode(' or ', $ret) . ' )';
			}
			return '';
		}
		if (is_array($value)) {
			//divide
			$scalars = [];
			$objects = [];
			if (count($value) == 0) {
				throw new InvalidArgumentException('zero size array, key : ' . $key);
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
				$scalars_query = $this->escapeColumnName($key) . ' in ' . $this->escapeItem($scalars, $key);
			} else {
				$scalars_query = '';
			}

			//merge
			if (strlen($objects_query) && strlen($scalars_query)) {
				return '( ' . $objects_query . ' or ' . $scalars_query . ' )';
			}
			return $objects_query . $scalars_query;
		}

		return $this->escapeColumnName($key) . ' = ' . $this->escapeItem($value, $key);
	}

	private function serializeWhere($array)
	{
		if (count($array) == 0) {
			throw new InvalidArgumentException('zero size array can not serialize : ' . $array);
		}
		$wheres = array_map([&$this, 'callbackSerializeWhere'], array_keys($array), $array);
		$wheres = array_filter($wheres, 'strlen');
		return implode(' and ', $wheres);
	}

	private function callbackSerializeUpdate($key, $value)
	{
		if (is_a($value, '\Gnf\db\Helper\GnfSqlNull') || is_null($value)) {
			return $this->escapeColumnName($key) . ' = null';
		}
		return $this->escapeColumnName($key) . ' = ' . $this->escapeItem($value, $key);
	}

	private function serializeUpdate($arr)
	{
		return implode(', ', array_map([&$this, 'callbackSerializeUpdate'], array_keys($arr), $arr));
	}

	private function escapeTableName($a)
	{
		if (is_a($a, '\Gnf\db\Helper\GnfSqlJoin')) {
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
						if (strlen($ret) == 0) {
							$ret .= $this->escapeTableName($column);
						} else {
							$ret .=
								"\n\t" . $a->type . ' ' . $this->escapeTableName($column) .
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
							$table_name = $this->escapeTableName($key_of_column);
							$joinable_where_clause[$table_name][$key_of_column] = $column;
						}
					}

					foreach ($columns as $key_of_column => $column) {
						$has_more_joinable_where_clause = !is_int($key_of_column);
						if (!$has_more_joinable_where_clause) {
							$join_left_column = $k;
							$join_right_column = $column;

							if (strlen($ret) == 0) {
								$ret .= $this->escapeTableName($join_left_column) . ' ' .
									"\n\t" . $a->type . ' ' .
									$this->escapeTableName($join_right_column) .
									"\n\t\t" . 'on ' .
									$this->escapeColumnName($join_left_column) .
									' = ' .
									$this->escapeColumnName($join_right_column);
							} else {
								$ret .= ' ' .
									"\n\t" . $a->type .
									' ' .
									$this->escapeTableName($join_right_column) .
									"\n\t\t" . 'on ' .
									$this->escapeColumnName($join_left_column) .
									' = ' .
									$this->escapeColumnName($join_right_column);
							}
							$join_right_table_name = $this->escapeTableName($join_right_column);
							if ($joinable_where_clause[$join_right_table_name]) {
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
		if (is_a($a, '\Gnf\db\Helper\GnfSqlTable')) {
			$a = $a->dat;
		}
		$a = str_replace('`', '', $a);
		$a = preg_replace("/\..+/", "", $a);
		return '`' . $a . '`';
	}

	private function escapeColumnName($k)
	{
		$k = str_replace('`', '', $k);
		$k = str_replace('.', '`.`', $k);
		return '`' . $k . '`';
	}

	//referenced yutarbbs(http://code.google.com/p/yutarbbs) by holies
	/**
	 * @param $value
	 * @return string
	 */
	private function escapeItemWithNull($value)
	{
		if (is_a($value, '\Gnf\db\Helper\GnfSqlNull') || is_null($value)) {
			return 'null';
		}
		return self::escapeItem($value);
	}

	/**
	 * @param $value
	 * @param $column null|string // is string if update
	 * @return string
	 */
	private function escapeItem($value, $column = null)
	{
		if (is_scalar($value)) {
			if (is_bool($value)) {
				if ($value) {
					return 'true';
				} else {
					return 'false';
				}
			}
			return '"' . $this->escapeLiteral($value) . '"';
		} elseif (is_array($value)) {
			if (count($value) == 0) {
				throw new InvalidArgumentException('zero size array, key : ' . $value);
			}
			return '(' . implode(', ', array_map([&$this, 'escapeItem'], $value)) . ')';
		} elseif (is_object($value)) {
			if (is_a($value, GnfSqlNow::class)) {
				return 'now()';
			} elseif (is_a($value, '\Gnf\db\Helper\GnfSqlPassword')) {
				return 'password(' . $this->escapeItem($value->dat) . ')';
			} elseif (is_a($value, '\Gnf\db\Helper\GnfSqlLike')) {
				return '"%' . $this->escapeLiteral($value->dat) . '%"';
			} elseif (is_a($value, '\Gnf\db\Helper\GnfSqlLikeBegin')) {
				return '"' . $this->escapeLiteral($value->dat) . '%"';
			} elseif (is_a($value, '\Gnf\db\Helper\GnfSqlRaw')) {
				return $value->dat;
			} elseif (is_a($value, '\Gnf\db\Helper\GnfSqlTable')) {
				return $this->escapeTableName($value);
			} elseif (is_a($value, '\Gnf\db\Helper\GnfSqlColumn')) {
				return $this->escapeColumnName($value->dat);
			} elseif (is_a($value, '\Gnf\db\Helper\GnfSqlWhere')) {
				return $this->serializeWhere($value->dat);
			} elseif (is_a($value, '\Gnf\db\Helper\GnfSqlLimit')) {
				return 'limit ' . $value->from . ', ' . $value->count;
			} elseif (is_a($value, '\Gnf\db\Helper\GnfSqlAdd') && is_string($column)) {//only for update
				if ($value->dat > 0) {
					return $this->escapeColumnName($column) . ' + ' . ($value->dat);
				} elseif ($value->dat < 0) {
					return $this->escapeColumnName($column) . ' ' . ($value->dat);
				}
				return $this->escapeColumnName($column);
			} elseif (is_a($value, '\Gnf\db\Helper\GnfSqlStrcat') && is_string($column)) {//only for update
				return 'concat(ifnull(' . $this->escapeColumnName($column) . ', ""), ' . $this->escapeItem(
					$value->dat
				) . ')';
			}
			return $this->escapeItem($value->dat);
		}
		throw new InvalidArgumentException('invalid escape item');
	}

	private function parseQuery($args)
	{
		if (count($args) >= 1) {
			$sql = array_shift($args);
			$escaped_items = array_map([&$this, 'escapeItem'], $args);

			$breaked_sql_blocks = explode('?', $sql);
			foreach ($breaked_sql_blocks as $index => $breaked_sql_block) {
				if ($index == 0) {
					continue;
				}
				if (count($escaped_items) == 0) {
					throw new InvalidArgumentException('unmatched "? count" with "argument count"');
				}
				$escaped_item = array_shift($escaped_items);
				$breaked_sql_blocks[$index] = $escaped_item . $breaked_sql_block;
			}
			if (count($escaped_items) != 0) {
				throw new InvalidArgumentException('unmatched "? count" with "argument count"');
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
		array_push($this->dump, []);
	}

	public function sqlDumpEnd()
	{
		if (count($this->dump)) {
			return array_pop($this->dump);
		}
		return null;
	}

	public function sqlDo($sql)
	{
		$sql = $this->parseQuery(func_get_args());
		$ret = $this->sqlDoWithoutParsing($sql);
		return $ret;
	}

	/**
	 * @param $sql
	 * @return mixed
	 * @throws \Exception
	 */
	private function sqlDoWithoutParsing($sql)
	{
		if (count($this->dump)) {
			foreach ($this->dump as $k => $v) {
				array_push($this->dump[$k], $sql);
			}
		}
		$ret = $this->query($sql);
		$err = $this->getError($ret);
		if ($err !== null) {
			$this->transactionError = true;
			throw new \Exception('[sql error] ' . $err->message . ' : ' . $sql);
		}
		return $ret;
	}

	public function sqlDump($sql)
	{
		return $this->parseQuery(func_get_args());
	}

	public function sqlData($sql)
	{
		$sql = $this->parseQuery(func_get_args());
		$res = $this->sqlDo($sql);
		if ($res) {
			$arr = $this->fetchRow($res);
			if (isset($arr[0])) {
				return $arr[0];
			}
		}
		return null;
	}

	public function sqlDatas($sql)
	{
		$sql = $this->parseQuery(func_get_args());
		$res = $this->sqlDo($sql);
		$ret = [];
		if ($res) {
			while ($arr = $this->fetchRow($res)) {
				$ret[] = $arr[0];
			}
		}
		return $ret;
	}

	public function sqlArray($sql)
	{
		$sql = $this->parseQuery(func_get_args());
		$res = $this->sqlDo($sql);
		if ($res) {
			$arr = $this->fetchRow($res);
			if ($arr) {
				return $arr;
			}
		}
		return null;
	}

	public function sqlArrays($sql)
	{
		$sql = $this->parseQuery(func_get_args());
		$res = $this->sqlDo($sql);
		$ret = [];
		if ($res) {
			while ($arr = $this->fetchRow($res)) {
				$ret[] = $arr;
			}
		}
		return $ret;
	}

	public function sqlDict($sql)
	{
		$sql = $this->parseQuery(func_get_args());
		$res = $this->sqlDo($sql);
		if ($res) {
			$arr = $this->fetchAssoc($res);
			if ($arr !== false) {
				return $arr;
			}
		}
		return null;
	}

	public function sqlDicts($sql)
	{
		$sql = $this->parseQuery(func_get_args());
		$res = $this->sqlDo($sql);
		$ret = [];
		if ($res) {
			while ($arr = $this->fetchAssoc($res)) {
				$ret[] = $arr;
			}
		}
		return $ret;
	}

	public function sqlObject($sql)
	{
		$sql = $this->parseQuery(func_get_args());
		$res = $this->sqlDo($sql);
		if ($res) {
			$arr = $this->fetchObject($res);
			if ($arr !== false) {
				return $arr;
			}
		}
		return null;
	}

	public function sqlObjects($sql)
	{
		$sql = $this->parseQuery(func_get_args());
		$res = $this->sqlDo($sql);
		$ret = [];
		if ($res) {
			while ($arr = $this->fetchObject($res)) {
				$ret[] = $arr;
			}
		}
		return $ret;
	}

	public function sqlLine($sql)
	{
		$sql = $this->parseQuery(func_get_args());
		$res = $this->sqlDo($sql);
		if ($res) {
			$arr = $this->fetchRow($res);
			if ($arr !== false) {
				return $arr;
			}
		}
		return null;
	}

	public function sqlLines($sql)
	{
		$sql = $this->parseQuery(func_get_args());
		$res = $this->sqlDo($sql);
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
		$table = $this->escapeItem(sqlTable($table));
		$dats_keys = array_keys($dats);
		$keys = implode(', ', array_map([&$this, 'escapeColumnName'], $dats_keys));
		$values = implode(', ', array_map([&$this, 'escapeItemWithNull'], $dats, $dats_keys));
		$sql = "INSERT INTO " . $table . " (" . $keys . ") VALUES (" . $values . ")";
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
		if ($update == null) {
			$update = $dats;
		}

		$table = $this->escapeItem(sqlTable($table));
		$dats_keys = array_keys($dats);
		$keys = implode(', ', array_map([&$this, 'escapeColumnName'], $dats_keys));
		$values = implode(', ', array_map([&$this, 'escapeItemWithNull'], $dats, $dats_keys));
		$update = $this->serializeUpdate($update);
		$sql = "INSERT INTO " . $table . " (" . $keys . ") VALUES (" . $values . ") ON DUPLICATE KEY UPDATE " . $update;
		$stmt = $this->sqlDoWithoutParsing($sql);
		return min(1, $this->getAffectedRows($stmt));
	}

	public function sqlUpdate($table, $dats, $where)
	{
		$table = $this->escapeItem(sqlTable($table));
		$update = $this->serializeUpdate($dats);
		$where = $this->serializeWhere($where);
		$sql = "UPDATE " . $table . " SET " . $update . " WHERE " . $where;
		$stmt = $this->sqlDoWithoutParsing($sql);
		return $this->getAffectedRows($stmt);
	}

	public function sqlDelete($table, $where)
	{
		$table = $this->escapeItem(sqlTable($table));
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

	abstract protected function doConnect();

	abstract protected function hasConnected();

	abstract public function select_db($db);

	abstract protected function transactionBegin();

	abstract protected function transactionCommit();

	abstract protected function transactionRollback();

	/**
	 * @return bool
	 */
	abstract protected function configIsSupportNestedTransaction();

	abstract protected function escapeLiteral($value);

	abstract protected function query($sql);

	abstract protected function getError($handle);

	abstract protected function fetchRow($handle);

	abstract protected function fetchAssoc($handle);

	abstract protected function fetchObject($handle);

	abstract protected function fetchBoth($handle);

	/**
	 * @param $handle
	 * @return int
	 */
	abstract protected function getAffectedRows($handle);
}
