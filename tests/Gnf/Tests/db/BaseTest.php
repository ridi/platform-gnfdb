<?php

namespace Gnf\Tests\db;

use InvalidArgumentException;

class BaseTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @param $sql
	 * @param $where
	 *
	 * @dataProvider providerWhere
	 */
	public function testWhere($sql, $where)
	{
		$base = new BaseTestTarget;

		$this->assertEquals($sql, $base->sqlDump('?', sqlWhere($where)));
	}

	public function providerWhere()
	{
		return [
			[
				'`a` = "1" and `b` = "2" and `c` is NULL',
				['a' => '1', 'b' => '2', 'c' => sqlNull()]
			],
			[
				'`a` in ("1", "2", "3")',
				['a' => [1, 2, 3]]
			],
			[
				'`a` = concat(ifnull(`a`, ""), "more string")',
				['a' => sqlStrcat('more string')]
			],
			[
				'`a` = password("any password")',
				['a' => sqlPassword('any password')]
			],
			[
				'`a` like "%any string%"',
				['a' => sqlLike('any string')]
			],
			[
				'`a` like "%any string?%"',
				['a' => sqlLike('any string?')]
			],
			[
				'`a` like "any prefix%"',
				['a' => sqlLikeBegin('any prefix')]
			],
			[
				'`a` like "any prefix?%"',
				['a' => sqlLikeBegin('any prefix?')]
			],
			[
				'`a` = __\'\'` + 123 + 5 + `alive`',
				['a' => sqlRaw('__\'\'` + 123 + 5 + `alive`')]
			],
			[
				'`a` = `title column`',
				['a' => sqlColumn('title column')]
			],
			[
				'`a` in ("1", "2")',
				['a' => sqlOr(1, 2)]
			],
			[
				'`c` is NULL',
				['c' => sqlNull()]
			],
			[
				'`d` is not NULL',
				['d' => sqlNot(sqlNull())]
			],
			[
				'`e` is not NULL and `f` = "g"',
				['e' => sqlNot(sqlNull()), 'f' => 'g']
			],
			[
				'`sqlGreater` > "1"',
				['sqlGreater' => sqlGreater(1)]
			],
			[
				'`sqlGreaterEqual` >= "1"',
				['sqlGreaterEqual' => sqlGreaterEqual(1)]
			],
			[
				'`sqlLesser` < "1"',
				['sqlLesser' => sqlLesser(1)]
			],
			[
				'`sqlLesserEqual` <= "1"',
				['sqlLesserEqual' => sqlLesserEqual(1)]
			],
			[
				'`sqlLesserEqual` <= `some column`',
				['sqlLesserEqual' => sqlLesserEqual(sqlColumn('some column'))]
			],
			[
				'`sqlBetween` between "1" and "100"',
				['sqlBetween' => sqlBetween(1, 100)]
			],
			[
				'("1" <= `sqlRange` and `sqlRange` < "100")',
				['sqlRange' => sqlRange(1, 100)]
			],
			[
				'`sqlLimit` = limit 1, 100',
				['sqlLimit' => sqlLimit(1, 100)]
			],
			[
				'`sqlLimit` = limit 0, 1',
				['sqlLimit' => sqlLimit(1)]
			],
			[
				'`sqlNow` = now()',
				['sqlNow' => sqlNow()]
			],
			[
				'( ( `c` = "3" ) or ( `b` = "2" ) )',
				[sqlOr(['c' => '3'], ['b' => 2])]
			],
			[
				'( ( `c` = "4" ) or ( `b` = "5" ) )',
				['d' => sqlOr(['c' => '4'], ['b' => 5])]
			],
			[
				'`a` in ("1", "2")',
				['a' => sqlOrArray([1, 2])]
			],
			[
				'( !( `a` in ("1", "2") ) )',
				['a' => sqlNot([1, 2])]
			],
			[
				'`a` in ("1", "2")',
				['a' => sqlNot(sqlNot([1, 2]))]
			],
			[
				'( ( ( !( `a` = "1" ) ) ) or `a` in ("2") )',
				['a' => [sqlNot(1), 2]]
			],
			[
				'( !( ( ( ( !( `a` = "1" ) ) ) or `a` in ("2") ) ) )',
				['a' => sqlNot([sqlNot(1), 2])]
			],
			[
				'( ( `a` = "1" ) and ( `b` = "2" ) )',
				[sqlAnd(['a' => 1], ['b' => 2])]
			],
			[
				'( ( `a` = "1" ) and ( `b` = "2" ) )',
				[sqlAndArray([['a' => 1], ['b' => 2]])]
			],
			[
				'( ( ! ( `a` = "1" ) ) and ( `b` = "2" ) )',
				[sqlAndArray([sqlNot(['a' => 1]), ['b' => 2]])]
			],
			[
				'( ( `a` = "1" ) or ( `b` = "2" ) )',
				[sqlOr(['a' => 1], ['b' => 2])]
			],
			[
				'( ( `a` = "1" ) or ( `b` = "2" ) )',
				[sqlOrArray([['a' => 1], ['b' => 2]])]
			],
			[
				'( ( ! ( `a` = "1" ) ) or ( `b` = "2" ) )',
				[sqlOrArray([sqlNot(['a' => 1]), ['b' => 2]])]
			],
			[
				'( ( ( ( `a` = "1" ) and ( `b` = "2" ) ) ) or ( ( ( `a` = "3" ) and ( `b` = "4" ) ) ) )',
				[sqlOr([sqlAnd(['a' => 1], ['b' => 2])], [sqlAnd(['a' => 3], ['b' => 4])])]
			],
			[
				'( ( ( ( `a` = "1" ) or ( `b` = "2" ) ) ) and ( ( ( `a` = "3" ) or ( `b` = "4" ) ) ) )',
				[sqlAnd([sqlOr(['a' => 1], ['b' => 2])], [sqlOr(['a' => 3], ['b' => 4])])]
			],
			[
				'( ( ( ( `a` = "1" ) and ( `b` = "2" ) ) ) or ( ( ( `a` = "3" ) and ( `b` = "4" ) ) ) )',
				[sqlOrArray([[sqlAndArray([['a' => 1], ['b' => 2]])], [sqlAndArray([['a' => 3], ['b' => 4]])]])]
			],
			[
				'( ( ( ( `a` = "1" ) or ( `b` = "2" ) ) ) and ( ( ( `a` = "3" ) or ( `b` = "4" ) ) ) )',
				[sqlAndArray([[sqlOrArray([['a' => 1], ['b' => 2]])], [sqlOrArray([['a' => 3], ['b' => 4]])]])]
			],
			[
				'( ( !( `a` in ("1", "2", "3") ) ) or `a` is NULL )',
				['a' => [sqlNot([1, 2, 3]), null]]
			],
			// injection test
			[
				'`a` = "\\"\\\\\\"\\\\\\"\\\'"',
				['a' => '"\"\\"\'']
			],
			[
				'`a` = "0x1233"',
				['a' => '0x1233']
			],
			[
				'`a` = "username=' . chr(0xbf) . '\\' . '"' . '"',
				['a' => "username=" . chr(0xbf) . "\""]
			],
			[
				'`a` = "username=' . chr(0xbf) . '\\' . '\'' . '"',
				['a' => "username=" . chr(0xbf) . "'"]
			],
		];
	}


	/**
	 * @param $where
	 *
	 * @dataProvider providerWhereException
	 * @expectedException InvalidArgumentException
	 */
	public function testWhereException($where)
	{
		$base = new BaseTestTarget;
		$base->sqlDump('?', sqlWhere($where));
	}

	public function providerWhereException()
	{
		return [
			[['a' => sqlOr(1, ['b' => 2])]],
			[[sqlOrArray([sqlNot(['a' => 1]), null])]],
			[[sqlAndArray([sqlNot(['a' => 1]), null])]],
		];
	}

	/**
	 * @param $sql
	 * @param $insert
	 *
	 * @dataProvider providerInsert
	 */
	public function testInsert($sql, $insert)
	{
		$base = new BaseTestTarget;

		$base->sqlDumpBegin();
		$base->sqlInsert('TABLE', $insert);
		$dump = $base->sqlDumpEnd();
		$this->assertEquals($sql, $dump[0]);
	}

	public function providerInsert()
	{
		return [
			[
				'INSERT INTO `TABLE` (`sqlPassword`) VALUES (password("password"))',
				['sqlPassword' => sqlPassword('password')]
			],
			[
				'INSERT INTO `TABLE` (`sqlNow`) VALUES (now())',
				['sqlNow' => sqlNow()]
			],
			[
				'INSERT INTO `TABLE` (`sqlNull`) VALUES (NULL)',
				['sqlNull' => sqlNull()]
			],
			[
				'INSERT INTO `TABLE` (`null_column`) VALUES (NULL)',
				['null_column' => null]
			],
		];
	}

	/**
	 * @param $sql
	 * @param $keys
	 * @param $values
	 *
	 * @dataProvider providerInsertBulk
	 */
	public function testInsertBulk($sql, $keys, $values)
	{
		$base = new BaseTestTarget;

		$base->sqlDumpBegin();
		$base->sqlInsertBulk('TABLE', $keys, $values);
		$dump = $base->sqlDumpEnd();
		$this->assertEquals($sql, $dump[0]);
	}

	public function providerInsertBulk()
	{
		return [
			[
				'INSERT INTO `TABLE` (`key`) VALUES  ( password("password") ), ( now() ), ( NULL ), ( NULL ), ( "123" )',
				['key'],
				[
					[sqlPassword('password')],
					[sqlNow()],
					[sqlNull()],
					[null],
					['123']
				]
			],
		];
	}

	/**
	 * @param $insert
	 *
	 * @dataProvider providerInsertException
	 * @expectedException InvalidArgumentException
	 */
	public function testInsertException($insert)
	{
		$base = new BaseTestTarget;

		$base->sqlDumpBegin();
		$base->sqlInsert('TABLE', $insert);
		$base->sqlInsertOrUpdate('TABLE', $insert);
		$base->sqlDumpEnd();
	}

	public function providerInsertException()
	{
		return [
			[
				[sqlAdd(-1)]
			],
			[
				['asd']
			],
		];
	}

	/**
	 * @param $sql
	 * @param $insert
	 *
	 * @dataProvider providerUpsert
	 */
	public function testUpsert($sql, $insert)
	{
		$base = new BaseTestTarget;

		$base->sqlDumpBegin();
		$base->sqlInsertOrUpdate('TABLE', $insert);
		$dump = $base->sqlDumpEnd();
		$this->assertEquals($sql, $dump[0]);
	}

	public function providerUpsert()
	{
		return [
			[
				'INSERT INTO `TABLE` (`sqlPassword`) VALUES (password("password")) ON DUPLICATE KEY UPDATE `sqlPassword` = password("password")',
				['sqlPassword' => sqlPassword('password')]
			],
			[
				'INSERT INTO `TABLE` (`sqlNow`) VALUES (now()) ON DUPLICATE KEY UPDATE `sqlNow` = now()',
				['sqlNow' => sqlNow()]
			],
			[
				'INSERT INTO `TABLE` (`sqlNull`) VALUES (NULL) ON DUPLICATE KEY UPDATE `sqlNull` = NULL',
				['sqlNull' => sqlNull()]
			],
			[
				'INSERT INTO `TABLE` (`null_column`) VALUES (NULL) ON DUPLICATE KEY UPDATE `null_column` = NULL',
				['null_column' => null]
			],
		];
	}

	/**
	 * @param $sql
	 * @param $update
	 *
	 * @dataProvider providerUpdate
	 */
	public function testUpdate($sql, $update)
	{
		$base = new BaseTestTarget;

		$base->sqlDumpBegin();
		$base->sqlUpdate('TABLE', $update, ['a' => 1]);
		$dump = $base->sqlDumpEnd();
		$this->assertEquals('UPDATE `TABLE` SET ' . $sql . ' WHERE `a` = "1"', $dump[0]);
	}

	public function providerUpdate()
	{
		return [
			[
				'`sqlAdd` = `sqlAdd` -1',
				['sqlAdd' => sqlAdd(-1)]
			],
			[
				'`sqlAdd` = `sqlAdd`',
				['sqlAdd' => sqlAdd(0)]
			],
			[
				'`sqlAdd` = `sqlAdd` + 1',
				['sqlAdd' => sqlAdd(1)]
			],
			[
				'`sqlPassword` = password("password")',
				['sqlPassword' => sqlPassword('password')]
			],
			[
				'`sqlStrcat` = concat(ifnull(`sqlStrcat`, ""), " more string")',
				['sqlStrcat' => sqlStrcat(' more string')]
			],
			[
				'`sqlNow` = now()',
				['sqlNow' => sqlNow()]
			],
			[
				'`sqlNull` = NULL',
				['sqlNull' => sqlNull()]
			],
			[
				'`null_column` = NULL',
				['null_column' => null]
			],
		];
	}

	/**
	 * @param $sql
	 * @param $join
	 *
	 * @dataProvider providerTable
	 */
	public function testTable($sql, $join)
	{
		$base = new BaseTestTarget;

		$this->assertEquals($sql, $base->sqlDump('?', $join));
	}

	public function providerTable()
	{
		return [
			[
				'`sqlTable`',
				sqlTable(sqlTable('sqlTable'))
			],
			[
				'`db1`.`table_1` 
	join `db2`.`table_2`
		on `db1`.`table_1`.`id` = `db2`.`table_2`.`id`',
				sqlTable(sqlJoin(['db1.table_1.id' => 'db2.table_2.id']))
			],
			[
				'`table_1` 
	join `table_2`
		on `table_1`.`id` = `table_2`.`id`',
				sqlTable(sqlJoin(['table_1.id' => 'table_2.id']))
			],
			[
				'`table_3` 
	join `table_4`
		on `table_3`.`id` = `table_4`.`id` 
	join `table_5`
		on `table_3`.`id` = `table_5`.`id` 
	join `table_6`
		on `table_3`.`id` = `table_6`.`id`',
				sqlJoin(['table_3.id' => ['table_4.id', 'table_5.id', 'table_6.id']])
			],
			[
				'`table_7` 
	join `table_8`
		on `table_7`.`id` = `table_8`.`id` and `table_8`.`other_id` = `table_9`.`id` and `table_8`.`other_id2` = `table_9`.`id2` 
	join `table_9`
		on `table_10`.`id` = `table_9`.`id2` and `table_9`.`other_id2` = "must_value_2" 
	join `table_11`
		on `table_10`.`id` = `table_11`.`id2` and `table_10`.`other_id2` = "must_value_1"',
				sqlJoin(
					[
						'table_7.id' => [
							'table_8.id',
							'table_8.other_id' => sqlColumn('table_9.id'),
							'table_8.other_id2' => sqlColumn('table_9.id2'),
						],
						'table_10.id' => [
							'table_9.id2',
							'table_11.id2',
							'table_10.other_id2' => 'must_value_1',
							'table_9.other_id2' => 'must_value_2',
						]
					]
				)
			],
			[
				'`db1`.`table_7` 
	join `db1`.`table_8`
		on `db1`.`table_7`.`id` = `db1`.`table_8`.`id` and `db1`.`table_8`.`other_id` = `db2`.`table_9`.`id` and `db1`.`table_8`.`other_id2` = `db2`.`table_9`.`id2` 
	join `db2`.`table_9`
		on `db2`.`table_10`.`id` = `db2`.`table_9`.`id2` and `db2`.`table_9`.`other_id2` = "must_value_2" 
	join `db3`.`table_11`
		on `db2`.`table_10`.`id` = `db3`.`table_11`.`id2` and `db2`.`table_10`.`other_id2` = "must_value_1"',
				sqlJoin(
					[
						'db1.table_7.id' => [
							'db1.table_8.id',
							'db1.table_8.other_id' => sqlColumn('db2.table_9.id'),
							'db1.table_8.other_id2' => sqlColumn('db2.table_9.id2'),
						],
						'db2.table_10.id' => [
							'db2.table_9.id2',
							'db3.table_11.id2',
							'db2.table_10.other_id2' => 'must_value_1',
							'db2.table_9.other_id2' => 'must_value_2',
						]
					]
				)
			],
		];
	}

	/**
	 * @param $result
	 * @param $sql
	 * @param $item
	 *
	 * @dataProvider providerEscapeItem
	 */
	public function testEscapeItem($result, $sql, $item)
	{
		$base = new BaseTestTarget;

		$this->assertEquals($result, $base->sqlDump($sql, $item));
	}

	public function providerEscapeItem()
	{
		return [
			[
				'("a", "b")',
				'?',
				['a', 'b']
			],
			[
				'"1"',
				'?',
				1
			],
			[
				"false",
				'?',
				false
			],
			[
				"true",
				'?',
				true
			],
			[
				'"-0.001"',
				'?',
				-0.001
			],
			[
				'"0.001"',
				'?',
				+0.001
			],
		];
	}

	/**
	 * @param $sql
	 * @param $item
	 *
	 * @dataProvider providerEscapeItemException
	 * @expectedException InvalidArgumentException
	 */
	public function testEscapeItemException($sql, $item)
	{
		$base = new BaseTestTarget;

		$base->sqlDump($sql, $item);
	}

	public function providerEscapeItemException()
	{
		return [
			[
				'?',
				sqlNull()
			],
			[
				'?',
				['a', null]
			],
			[
				'?',
				['a', sqlNull()]
			],
		];
	}

	/**
	 * @param $sql
	 * @param $where
	 *
	 * @dataProvider providerException
	 * @expectedException InvalidArgumentException
	 */
	public function testException($sql, $where)
	{
		$base = new BaseTestTarget;

		$base->sqlDump($sql, sqlWhere($where));
	}

	public function providerException()
	{
		return [
			[
				'?',
				['column' => []]
			],
			[
				'??',
				['column' => [1]]
			],
			[
				'',
				['column' => [1]]
			],
		];
	}


	/**
	 * @param $update
	 * @param $where
	 *
	 * @dataProvider providerUpdateException
	 * @expectedException InvalidArgumentException
	 */
	public function testUpdateException($update, $where)
	{
		$base = new BaseTestTarget;

		$base->sqlUpdate('TABLE', $update, $where);
	}

	public function providerUpdateException()
	{
		return [
			[
				['column' => []],
				['column' => ['b']]
			],
			[
				['column' => ['a']],
				['column' => []]
			],
			[
				['column' => []],
				['column' => []]
			],
			[
				['column' => 'a'],
				[]
			],
			[
				['sqlNull' => sqlNot(sqlNull())],
				['column' => ['b']]
			],
			[
				['null_column' => sqlNot(null)],
				['column' => ['b']]
			],
		];
	}
}
