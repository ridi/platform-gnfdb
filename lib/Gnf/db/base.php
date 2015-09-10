<?php


namespace {
	class __sqlAdd
	{
		function __sqlAdd($in)
		{
			$this->dat = (int)$in;
		}
	}

	function sqlAdd($in)
	{
		return new __sqlAdd($in);
	}

	class __sqlStrcat
	{
		function __sqlStrcat($in)
		{
			$this->dat = $in;
		}
	}

	function sqlStrcat($in)
	{
		return new __sqlStrcat($in);
	}

	class __sqlPassword
	{
		function __sqlPassword($in)
		{
			$this->dat = $in;
		}
	}

	function sqlPassword($in)
	{
		return new __sqlPassword($in);
	}

	class __sqlCompareOperator
	{
	}

	class __sqlLike extends __sqlCompareOperator
	{
		function __sqlLike($in)
		{
			$this->dat = $in;
		}
	}

	function sqlLike($in)
	{
		//__sqlNot을 포함관계에서 최상단으로
		if (is_a($in, '__sqlNot') && is_a($in->dat, '__sqlCompareOperator')) {
			$wrapper = new __sqlLike($in->dat);
			return new __sqlNot($wrapper);
		}
		return new __sqlLike($in);
	}

	class __sqlLikeBegin extends __sqlCompareOperator
	{
		function __sqlLikeBegin($in)
		{
			$this->dat = $in;
		}
	}

	function sqlLikeBegin($in)
	{
		//__sqlNot을 포함관계에서 최상단으로
		if (is_a($in, '__sqlNot') && is_a($in->dat, '__sqlCompareOperator')) {
			$wrapper = new __sqlLikeBegin($in->dat);
			return new __sqlNot($wrapper);
		}
		return new __sqlLikeBegin($in);
	}

	class __sqlRaw
	{
		function __sqlRaw($in)
		{
			$this->dat = $in;
		}
	}

	function sqlRaw($in)
	{
		return new __sqlRaw($in);
	}

	class __sqlTable
	{
		function __sqlTable($in)
		{
			$this->dat = $in;
		}
	}

	function sqlTable($in)
	{
		if (is_a($in, '__sqlTable')) {
			return $in;
		}
		return new __sqlTable($in);
	}

	class __sqlColumn
	{
		function __sqlColumn($in)
		{
			$this->dat = $in;
		}
	}

	function sqlColumn($in)
	{
		if (is_a($in, '__sqlColumn')) {
			return $in;
		}
		return new __sqlColumn($in);
	}

	class __sqlJoin extends __sqlTable
	{
		function __sqlJoin($in, $type = 'join')
		{
			$this->type = $type;
			$this->dat = $in;
		}
	}

	function sqlJoin($in, $type = 'join')
	{
		if (!is_array($in)) {
			$in = func_get_args();
		}
		return new __sqlJoin($in, $type);
	}

	function sqlLeftJoin($in)
	{
		if (!is_array($in)) {
			$in = func_get_args();
		}
		return new __sqlJoin($in, 'left join');
	}

	function sqlInnerJoin($in)
	{
		if (!is_array($in)) {
			$in = func_get_args();
		}
		return new __sqlJoin($in, 'inner join');
	}

	class __sqlWhere
	{
		function __sqlWhere($in)
		{
			$this->dat = $in;
		}
	}

	function sqlWhere(array $in)
	{
		return new __sqlWhere($in);
	}

	class __sqlWhereWithClause
	{
		function __sqlWhereWithClause($in)
		{
			$this->dat = $in;
		}
	}

	function sqlWhereWithClause(array $in)
	{
		return new __sqlWhereWithClause($in);
	}

	class __sqlOr
	{
		function __sqlOr($in)
		{
			$this->dat = $in;
		}
	}

	function sqlOr()
	{
		return new __sqlOr(func_get_args());
	}

	function sqlOrArray(array $args)
	{
		return new __sqlOr($args);
	}

	class __sqlNot extends __sqlCompareOperator
	{
		function __sqlNot($in)
		{
			$this->dat = $in;
		}
	}

	function sqlNot($in)
	{
		//부정의 부정은 긍정
		if (is_a($in, '__sqlNot') && is_a($in->dat, '__sqlCompareOperator')) {
			return $in->dat;
		}
		return new __sqlNot($in);
	}

	class __sqlGreaterEqual extends __sqlCompareOperator
	{
		function __sqlGreaterEqual($in)
		{
			$this->dat = $in;
		}
	}

	function sqlGreaterEqual($in)
	{
		//__sqlNot을 포함관계에서 최상단으로
		if (is_a($in, '__sqlNot') && is_a($in->dat, '__sqlCompareOperator')) {
			$wrapper = new __sqlGreaterEqual($in->dat);
			return new __sqlNot($wrapper);
		}
		return new __sqlGreaterEqual($in);
	}

	class __sqlGreater extends __sqlCompareOperator
	{
		function __sqlGreater($in)
		{
			$this->dat = $in;
		}
	}

	function sqlGreater($in)
	{
		//__sqlNot을 포함관계에서 최상단으로
		if (is_a($in, '__sqlNot') && is_a($in->dat, '__sqlCompareOperator')) {
			$wrapper = new __sqlGreater($in->dat);
			return new __sqlNot($wrapper);
		}
		return new __sqlGreater($in);
	}

	class __sqlLesserEqual extends __sqlCompareOperator
	{
		function __sqlLesserEqual($in)
		{
			$this->dat = $in;
		}
	}

	function sqlLesserEqual($in)
	{
		//__sqlNot을 포함관계에서 최상단으로
		if (is_a($in, '__sqlNot') && is_a($in->dat, '__sqlCompareOperator')) {
			$wrapper = new __sqlLesserEqual($in->dat);
			return new __sqlNot($wrapper);
		}
		return new __sqlLesserEqual($in);
	}

	class __sqlLesser extends __sqlCompareOperator
	{
		function __sqlLesser($in)
		{
			$this->dat = $in;
		}
	}

	function sqlLesser($in)
	{
		//__sqlNot을 포함관계에서 최상단으로
		if (is_a($in, '__sqlNot') && is_a($in->dat, '__sqlCompareOperator')) {
			$wrapper = new __sqlLike($in->dat);
			return new __sqlNot($wrapper);
		}
		return new __sqlLesser($in);
	}

	class __sqlBetween
	{
		function __sqlBetween($in, $in2)
		{
			$this->dat = $in;
			$this->dat2 = $in2;
		}
	}

	function sqlBetween($in, $in2)
	{
		return new __sqlBetween($in, $in2);
	}

	class __sqlRange
	{
		function __sqlRange($in, $in2)
		{
			$this->dat = $in;
			$this->dat2 = $in2;
		}
	}

	function sqlRange($in, $in2)
	{
		return new __sqlRange($in, $in2);
	}

	class __sqlLimit
	{
		function __sqlLimit($from, $count)
		{
			$this->from = (int)$from;
			$this->count = (int)$count;
		}
	}

	function sqlLimit()
	{
		$in = func_get_args();
		if (count($in) == 1) {
			return new __sqlLimit(0, $in[0]);
		}
		return new __sqlLimit($in[0], $in[1]);
	}

	class __sqlNow
	{
	}

	function sqlNow()
	{
		return new __sqlNow();
	}

	class __sqlNull
	{
	}

	function sqlNull()
	{
		return new __sqlNull();
	}
}
namespace Gnf\db {
	interface gnfDBinterface
	{
		public function sqlBegin();

		public function sqlEnd();

		public function sqlCommit();

		public function sqlRollback();

		public function sqlDo($sql);

		public function sqlData($sql);

		public function sqlDatas($sql);

		public function sqlDict($sql);

		public function sqlDicts($sql);

		public function sqlObject($sql);

		public function sqlObjects($sql);

		public function sqlLine($sql);

		public function sqlLines($sql);

		public function sqlCount($table, $where);

		public function sqlInsert($table, $dats);

		public function sqlInsertOrUpdate($table, $dats, $update = null);

		public function sqlUpdate($table, $dats, $where);

		public function sqlDelete($table, $where);

		public function insert_id();
	}


	abstract class base implements gnfDBinterface
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

		private function callback_serializeWhere($key, $value)
		{
			if (is_a($value, '__sqlNot')) {
				$ret = $this->callback_serializeWhere($key, $value->dat);
				if (strlen($ret)) {
					return '(!(' . $ret . '))';
				}
				return '';
			}
			if (is_a($value, '__sqlOr')) {
				$ret = array();
				foreach ($value->dat as $dat) {
					if (is_array($dat)) {
						$ret[] = '(' . $this->serializeWhere($dat) . ')';
					}
				}
				return '(' . implode(' or ', $ret) . ')';
			}
			if (is_a($value, '__sqlLike')) {
				return $this->escapeColumnName($key) . ' like "%' . $this->escapeLiteral($value->dat) . '%"';
			}
			if (is_a($value, '__sqlLikeBegin')) {
				return $this->escapeColumnName($key) . ' like "' . $this->escapeLiteral($value->dat) . '%"';
			}
			if (is_a($value, '__sqlGreater')) {
				return $this->escapeColumnName($key) . ' > ' . $this->escapeItem($value->dat, $key);
			}
			if (is_a($value, '__sqlLesser')) {
				return $this->escapeColumnName($key) . ' < ' . $this->escapeItem($value->dat, $key);
			}
			if (is_a($value, '__sqlGreaterEqual')) {
				return $this->escapeColumnName($key) . ' >= ' . $this->escapeItem($value->dat, $key);
			}
			if (is_a($value, '__sqlLesserEqual')) {
				return $this->escapeColumnName($key) . ' <= ' . $this->escapeItem($value->dat, $key);
			}
			if (is_a($value, '__sqlBetween')) {
				return $this->escapeColumnName($key) . ' between ' . $this->escapeItem(
					$value->dat,
					$key
				) . ' and ' . $this->escapeItem($value->dat2, $key);
			}
			if (is_a($value, '__sqlRange')) {
				return '(' . $this->escapeItem($value->dat, $key) . ' <= ' . $this->escapeColumnName(
					$key
				) . ' and ' . $this->escapeColumnName($key) . ' < ' . $this->escapeItem($value->dat2, $key) . ')';
			}
			if (is_a($value, '__sqlNull') || is_null($value)) {
				return $this->escapeColumnName($key) . ' is null';
			}
			if (is_array($value)) {
				//divide
				{
					$scalars = array();
					$objects = array();
					foreach ($value as $operand) {
						if (is_scalar($operand)) {
							$scalars[] = $operand;
						} else {
							$objects[] = $operand;
						}
					}
				}
				//process
				{
					foreach ($objects as $k => $object) {
						$objects[$k] = $this->callback_serializeWhere($key, $object);
					}
					$objectsQuery = implode(' or ', array_filter($objects, 'strlen'));
					if (count($scalars)) {
						$scalarsQuery = $this->escapeColumnName($key) . ' in ' . $this->escapeItem($scalars, $key);
					} else {
						$scalarsQuery = '';
					}
				}
				//merge
				{
					if (strlen($objectsQuery) && strlen($scalarsQuery)) {
						return ' ( ' . $objectsQuery . ' or ' . $scalarsQuery . ' ) ';
					}
					return $objectsQuery . $scalarsQuery;
				}
			}

			return $this->escapeColumnName($key) . ' = ' . $this->escapeItem($value, $key);
		}

		private function serializeWhere($arr)
		{
			$wheres = array_map(array(&$this, 'callback_serializeWhere'), array_keys($arr), $arr);
			$wheres = array_filter($wheres, 'strlen');
			return implode(' and ', $wheres);
		}

		private function callback_serializeUpdate($key, $value)
		{
			if (is_a($value, '__sqlNull') || is_null($value)) {
				return $this->escapeColumnName($key) . ' = null';
			}
			return $this->escapeColumnName($key) . ' = ' . $this->escapeItem($value, $key);
		}

		private function serializeUpdate($arr)
		{
			return implode(', ', array_map(array(&$this, 'callback_serializeUpdate'), array_keys($arr), $arr));
		}

		function escapeTableName($a)
		{
			if (is_a($a, '__sqlJoin')) {
				$ret = '';
				foreach ($a->dat as $k => $columns) {

					/** @var $joinOnlyOneColumn
					 * if $joinOnlyOneColumn = [true]
					 * => sqlJoin(array('tb_pay_info.t_id', 'tb_cash.t_id', 'tb_point.t_id'))
					 * if $joinOnlyOneColumn = [false]
					 * => sqljoin(array('tb_pay_info.t_id' => array('tb_cash.t_id', 'tb_point.t_id')))
					 */

					$joinOnlyOneColumn = is_int($k);

					if (!is_array($columns)) {
						$columns = array($columns);
					}
					if ($joinOnlyOneColumn) {
						$lastColumn = '';
						foreach ($columns as $keyOfColumn => $column) {
							if (strlen($ret) == 0) {
								$ret .= $this->escapeTableName($column);
							} else {
								$ret .= ' ' . $a->type . ' ' . $this->escapeTableName($column);
								$ret .= ' on ' . $this->escapeColumnName(
										$lastColumn
									) . ' = ' . $this->escapeColumnName(
										$column
									);
							}
							$lastColumn = $column;
						}
					} else {
						/** @var $isMoreJoinWhereClause
						 * if $isMoreJoinWhereClause = [true]
						 *  => sqljoin(array('tb_pay_info.t_id' => array('tb_cash.t_id', 'tb_cash.type' => 'event')))
						 * if $isMoreJoinWhereClause = [false]
						 *  => sqljoin(array('tb_pay_info.t_id' => array('tb_cash.t_id')))
						 */

						//filter $moreJoinWhereClause
						$moreJoinWhereClause = array();
						foreach ($columns as $keyOfColumn => $column) {
							$isMoreJoinWhereClause = !is_int($keyOfColumn);
							if ($isMoreJoinWhereClause) {
								$tableName = $this->escapeTableName($keyOfColumn);
								$moreJoinWhereClause[$tableName][$keyOfColumn] = $column;
							}
						}

						foreach ($columns as $keyOfColumn => $column) {
							$isMoreJoinWhereClause = !is_int($keyOfColumn);
							if (!$isMoreJoinWhereClause) {
								$joinLeftColumn = $k;
								$joinRightColumn = $column;

								if (strlen($ret) == 0) {
									$ret .= $this->escapeTableName($joinLeftColumn) . ' ' .
										$a->type . ' ' .
										$this->escapeTableName($joinRightColumn) .
										' on ' .
										$this->escapeColumnName($joinLeftColumn) .
										' = ' .
										$this->escapeColumnName($joinRightColumn);
								} else {
									$ret .= ' ' .
										$a->type .
										' ' .
										$this->escapeTableName($joinRightColumn) .
										' on ' .
										$this->escapeColumnName($joinLeftColumn) .
										' = ' .
										$this->escapeColumnName($joinRightColumn);
								}
								$joinRightTableName = $this->escapeTableName($joinRightColumn);
								if ($moreJoinWhereClause[$joinRightTableName]) {
									$ret .= ' and ' . $this->serializeWhere($moreJoinWhereClause[$joinRightTableName]);
									unset($moreJoinWhereClause[$joinRightTableName]);
								}
							}
						}
					}
				}
				return $ret;
			}
			if (is_a($a, '__sqlTable')) {
				$a = $a->dat;
			}
			$a = str_replace('`', '', $a);
			$a = preg_replace("/\..+/", "", $a);
			return '`' . $a . '`';
		}

		function escapeColumnName($k)
		{
			$k = str_replace('`', '', $k);
			$k = str_replace('.', '`.`', $k);
			return '`' . $k . '`';
		}

		//referenced yutarbbs(http://code.google.com/p/yutarbbs) by holies
		function escapeItem($a, $k = null)
		{
			if (is_scalar($a)) {
				return '"' . $this->escapeLiteral($a) . '"';
			} elseif (is_array($a)) {
				return '(' . implode(', ', array_map(array(&$this, 'escapeItem'), $a)) . ')';
			} elseif (is_object($a)) {
				if (is_a($a, '__sqlNow')) {
					return 'now()';
				} elseif (is_a($a, '__sqlPassword')) {
					return 'password(' . $this->escapeItem($a->dat) . ')';
				} elseif (is_a($a, '__sqlLike')) {
					return '"%' . $this->escapeLiteral($a->dat) . '%"';
				} elseif (is_a($a, '__sqlLikeBegin')) {
					return '"' . $this->escapeLiteral($a->dat) . '%"';
				} elseif (is_a($a, '__sqlRaw')) {
					return $a->dat;
				} elseif (is_a($a, '__sqlTable')) {
					return $this->escapeTableName($a);
				} elseif (is_a($a, '__sqlColumn')) {
					return $this->escapeColumnName($a->dat);
				} elseif (is_a($a, '__sqlWhere')) {
					return $this->serializeWhere($a->dat);
				} elseif (is_a($a, '__sqlWhereWithClause')) {
					$where = $this->serializeWhere($a->dat);
					if (strlen($where)) {
						return ' where ' . $where;
					} else {
						return '';
					}
				} elseif (is_a($a, '__sqlLimit')) {
					return 'limit ' . $a->from . ', ' . $a->count;
				} elseif (is_a($a, '__sqlAdd') && is_string($k)) //only for update
				{
					if ($a->dat > 0) {
						return $this->escapeColumnName($k) . ' + ' . ($a->dat);
					} elseif ($a->dat < 0) {
						return $this->escapeColumnName($k) . ' ' . ($a->dat);
					}
					return $this->escapeColumnName($k);
				} elseif (is_a($a, '__sqlStrcat') && is_string($k)) //only for update
				{
					return 'concat(ifnull(' . $this->escapeColumnName($k) . ', ""), ' . $this->escapeItem(
						$a->dat
					) . ')';
				}
				return $this->escapeItem($a->dat);
			}
			return 'NULL';
		}

		private function parseQuery($args)
		{
			if (count($args) >= 1) {
				$s = array_shift($args);
				$args = array_map(array(&$this, 'escapeItem'), $args);

				return preg_replace_callback(
					'/\?/',
					function ($m) use (&$args) {
						return array_shift($args);
					},
					$s,
					count($args)
				);
			}
			return "";
		}

		public function sqlDumpBegin()
		{
			if (!is_array($this->dump)) {
				$this->dump = array();
			}
			array_push($this->dump, array());
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
			$ret = array();
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
			$ret = array();
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
			$ret = array();
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
			$ret = array();
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
			$ret = array();
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
			return @call_user_func_array(array(&$this, 'sqlDicts'), $args);
		}

		public function sqlCount($table, $where)
		{
			$sql = "SELECT count(*) FROM ? ?";
			return $this->sqlData($sql, sqlTable($table), sqlWhereWithClause($where));
		}

		public function sqlInsert($table, $dats)
		{
			$table = $this->escapeItem(sqlTable($table));
			$dats_keys = array_keys($dats);
			$keys = implode(', ', array_map(array(&$this, 'escapeColumnName'), $dats_keys));
			$values = implode(', ', array_map(array(&$this, 'escapeItem'), $dats, $dats_keys));
			$sql = "INSERT INTO " . $table . " (" . $keys . ") VALUES (" . $values . ")";
			$stmt = $this->sqlDo($sql);
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
			$keys = implode(', ', array_map(array(&$this, 'escapeColumnName'), $dats_keys));
			$values = implode(', ', array_map(array(&$this, 'escapeItem'), $dats, $dats_keys));
			$update = $this->serializeUpdate($update);
			$sql = "INSERT INTO " . $table . " (" . $keys . ") VALUES (" . $values . ") ON DUPLICATE KEY UPDATE " . $update;
			$stmt = $this->sqlDo($sql);
			return min(1, $this->getAffectedRows($stmt));
		}

		public function sqlUpdate($table, $dats, $where)
		{
			$table = $this->escapeItem(sqlTable($table));
			$update = $this->serializeUpdate($dats);
			$where = $this->serializeWhere($where);
			$sql = "UPDATE " . $table . " SET " . $update . " WHERE " . $where;
			$stmt = $this->sqlDo($sql);
			return $this->getAffectedRows($stmt);
		}

		public function sqlDelete($table, $where)
		{
			$table = $this->escapeItem(sqlTable($table));
			$where = $this->serializeWhere($where);
			$sql = "DELETE FROM " . $table . " WHERE " . $where;
			$stmt = $this->sqlDo($sql);
			return $this->getAffectedRows($stmt);
		}

		protected function checkConnectionOrTry()
		{
			if ($this->hasConnected()) {
				return;
			}
			$this->doConnect();
		}

		protected abstract function doConnect();

		protected abstract function hasConnected();

		public abstract function select_db($db);

		protected abstract function transactionBegin();

		protected abstract function transactionCommit();

		protected abstract function transactionRollback();

		/**
		 * @return bool
		 */
		protected abstract function configIsSupportNestedTransaction();

		protected abstract function escapeLiteral($value);

		protected abstract function query($sql);

		protected abstract function getError($handle);

		protected abstract function fetchRow($handle);

		protected abstract function fetchAssoc($handle);

		protected abstract function fetchObject($handle);

		protected abstract function fetchBoth($handle);

		/**
		 * @param $handle
		 * @return int
		 */
		protected abstract function getAffectedRows($handle);
	}
}
