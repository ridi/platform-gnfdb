# GnfDB

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ridibooks/platform-gnfdb/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ridibooks/platform-gnfdb/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/ridibooks/platform-gnfdb/v/stable)](https://packagist.org/packages/ridibooks/platform-gnfdb)
[![License](https://poser.pugx.org/ridibooks/platform-gnfdb/license)](https://packagist.org/packages/ridibooks/platform-gnfdb)

## 0. 설치

composer를 사용하여 설치 할 수 있다.

ridibooks/platform-gnfdb 또는 gnf/gnfdb를 설치하면 된다.

```
composer require ridibooks/platform-gnfdb
```



## 1. 연결 방법

gnfdb 패키지 내에 Gnf\db\PDO 를 instance화 하면 사용 할 수 있다.

Gnf\db\PDO는 \PDO를 인자로 받는다. 연결을 생성하고 인자로 전달하자.

`````php
<?php
$pdo_dbh = new PDO('mysql:host={host}', $user, $password);
$db = new Gnf\db\PDO($pdo_dbh);
`````

## 2. SELECT 관련

select에 사용하는 method 목록은 다음과 같다.

- sqlDict / sqlDicts - 결과 값을 array(PDO::FETCH_ASSOC)로 받는다.
- sqlObject / sqlObjects - 결과 값을 object(PDO::FETCH_OBJ)로 받는다.

위 method의 형태는 다음과 같다.

- sqlDict($sql, ...)

\PDO의 prepare와 문법이 동일하지만, ":column:" 과 같은 bind는 지원하지 않고, "?" 로 사용하는 bind만 지원한다.

하나만 가져오는 sqlDict / sqlObject는 데이터가 없을 경우 null을 return하고,
여러개를 가져오는 sqlDicts / sqlObjects는 데이터가 없을 경우 empty array를 return한다. 

**사용 예제**

```php
$db->sqlDict('SELECT * FROM tb_book WHERE id = ?', $b_id);
$db->sqlDicts(
    'SELECT *
    FROM tb_book
    INNER JOIN cpdp_books ON (tb_book.id = cpdp_books.b_id AND cpdp_books.approve_status = ?)
    WHERE tb_book.id = ? AND tb_book.pub_id = ?',
    ApproveStatus::OPEN,
    $b_id,
    $pud_ib
);
```





## 3. WHERE 조건의 확장된 사용법

where 조건을 직접적으로 지정하고 ?를 bind 할 수 있지만, 불편하기에 다음과 같은 함수를 지원한다.

- sqlWhere($where)

$where은 column => condition 으로 구성된 배열이다.

### 3.1 조건 작성 법

- 단순 비교는 값을 그대로 사용한다.
- 비교 연산자는 다음 함수를 값으로 사용한다.
  - `sqlLesser($value)`
    - `< $value`
  - `sqlLesserEqual($value)`
    - `<= $value`
  - `sqlGreater($value)`
    - `> $value`
  - `sqlGreaterEqual($value)`
    - `>= $value`
- 범위 연산자
  - `sqlBetween($start, $end)`
    - `$start <= value <= $end`
  - `sqlRange($start, $end)`
    - `$start <= b < $end`
- like 구문은 다음과 같이 사용한다.
  - `sqlLike($keyword)`
    - `like '%{$keyword}%'`
  - `sqlLikeBegin($keyword)`
    - `like '{$keyword}%'`

**조건 작성 예제**

```php
<?php
//단순 비교
$db->sqlDict(
    'SELECT * FROM tb_book WHERE ?',
    sqlWhere(['id' => $b_id])
); // => SELECT * FROM tb_book WHERE id = {$b_id}
 
 
//비교 연산자
$db->sqlDict(
    'SELECT * FROM tb_book WHERE ?',
    sqlWhere(['pub_id' => sqlLesser($pub_id)])
); // => SELECT * FROM tb_book WHERE pub_id < {$pub_id}
 
 
//Between
$db->sqlDict(
    'SELECT * FROM tb_book WHERE ?',
    sqlWhere(['regdate' => sqlBetween('20160101000000', '20161231235959')])
); // => SELECT * FROM tb_book WHERE regdate BETWEEN '20160101000000' AND '20161231235959'
 
 
//like
$db->sqlDict(
    'SELECT * FROM tb_book WHERE ?',
    sqlWhere(['title' => sqlLike('체험판')])
); // => SELECT * FROM tb_book WHERE title like '%체험판%'
```

- 단순 비교 시 비교 대상이 array인 경우 IN 쿼리로 적용된다.

**비교 대상이 array인 쿼리 예제**

```php
<?php
$b_ids = ['101000940', '101000941', '101000945'];
$db->sqlDicts(
    'SELECT * FROM tb_book WHERE ?',
    sqlWhere(['id' => $b_ids])
); // => SELECT * FROM tb_book WHERE id IN ('101000940', '101000941', '101000945')
```

### 3.2 같이 사용 할 수 있는 함수

- sqlRaw($value)
  - 입력 한 값 그대로 사용된다.
- sqlLimit($count)
  - `limit $count`
- sqlLimit($from, $count)
  - `limit $from, $count`
- sqlNull()
  - null 값, 그냥 값으로 null을 사용해도 동일하다.
- sqlNot($value)
  - Not이 적용 된다. sqlGreater 등 다른 연산과 복합적으로 사용 할 수 있다.

### 3.3 AND, OR 복합 사용

앞서 설명했던 sqlWhere은 AND로 작성하는 함수고, sqlOr($where) 함수로 OR문을 작성 할 수 있다.

이 두 함수를 섞어 사용함으로 다양한 where문 작성이 가능하다.

**복합 쿼리**

```php
<?php
$where = [
   'tb_book.pub_id' => sqlNot(101),
   sqlOr(
      [
         'tb_book.category' => sqlNot(
            [
               4001,
               4003
            ]
         )
      ],
      [
         'tb_book.serial_completed' => sqlNot('Y'),
         'tb_book.series_id' => sqlNot('')
      ],
      ['tb_category.genre' => sqlNot('comic')],
      ['tb_book.is_setbook' => 'Y']
   )
];
$db->sqlDicts(
    'SELECT tb_book.id
    FROM tb_book
    LEFT JOIN tb_book_comic ON (tb_book.id = tb_book_comic.b_id)
    LEFT JOIN tb_category ON (tb_book.category = tb_category.id)
    WHERE ?',
    sqlWhere($where)
);
/*
SELECT tb_book.id
FROM tb_book
LEFT JOIN tb_book_comic ON (tb_book.id = tb_book_comic.b_id)
LEFT JOIN tb_category ON (tb_book.category = tb_category.id)
WHERE tb_book.pub_id != 101
    AND (
        tb_book.category NOT IN (4001, 4003)
        OR (
            tb_book.serial_completed != 'Y'
                AND tb_book.series_id != ''
        )
        OR tb_category.genre != 'comic'
        OR tb_book.is_setbook = 'Y'
    )
*/
```

### 3.4 확장된 테이블 구문

테이블명을 직접적으로 쿼리에 작성 할 수 있지만, JOIN의 ON 조건으로 연결되는 컬럼만 작성함으로 간단한 JOIN 쿼리를 만들 수 있다.

지원되는 함수는 다음과 같다.

- sqlTable($table) - 단일 테이블 사용
- sqlJoin($tables) - JOIN 쿼리
- sqlLeftJoin($tables) - LEFT JOIN 쿼리
- sqlInnerJoin($tables) - INNER JOIN 쿼리

작성 방법

- 테이블 목록은 array로 작성하여 사용한다.
- key, value모두 서로 ON으로 JOIN할 컬럼을 작성한다.
- 앞서 선언되어 있는 테이블을 key값으로 사용하면 좋다.
- 첫 선언은 key가 from 절 테이블이고, value가 후에 따라오는 join 테이블이다.
  - value에 단일 테이블을 적어서 하나만 JOIN 할 수도 있고,
    배열로 여러 테이블을 작성하면 순차적으로 JOIN문이 작성된다.

**테이블 구문 작성 예제**

```php
<?php
// 단일 테이블
$db->sqlDict('SELECT * FROM ?', sqlTable('tb_book'));
// => SELECT * FROM tb_book
 
 
// join 테이블 - 1:1 관계만
$tables = [
   'tb_book.id' => 'tb_book_comic.b_id',
   'tb_book.category' => 'tb_category.id'
];
$db->sqlDicts('SELECT * FROM ?', sqlLeftJoin($tables));
/*
SELECT *
FROM tb_book
LEFT JOIN tb_book_comic ON (tb_book.id = tb_book_comic.b_id)
LEFT JOIN tb_category ON (tb_book.category = tb_category.id)
*/
 

// join 테이블 - 첫 테이블에 join이 여러개 걸리는 경우
$tables = [
   'tb_book_production.b_id' => ['tb_book.id', 'platform_withhold.b_id'],
   'tb_book.pub_id' => 'tb_publisher.id',
   'tb_publisher.id' => 'tb_publisher_manager.pub_id',
   'tb_book.id' => 'tb_book_search.b_id'
];
$db->sqlDicts('SELECT * FROM ?', sqlLeftJoin($tables));
/*
SELECT *
FROM tb_book_production
LEFT JOIN tb_book ON (tb_book_production.b_id = tb_book.id)
LEFT JOIN platform_withhold ON (tb_book_production.b_id = production_withhold.b_id)
LEFT JOIN tb_publisher ON (tb_book.pub_id = tb_publisher.id)
LEFT JOIN tb_publisher_manager ON (tb_publisher.id = tb_publisher_manager.pub_id)
LEFT JOIN tb_book_search ON (tb_book.id = tb_book_search.b_id)
*/
```

### 3.5 기타

- sqlAdd($something) - column + $something 쿼리로 변환
- sqlStrcat($something) - concat(colum, $something) 쿼리로 변환
- sqlPassword($something) - sql password() function
- sqlColumn($column) - 컬럼명을 안전하게 사용하고 싶을 때
- sqlWhereWithClause($where) - sqlWhere()과 동일하지만, 'WHERE '이 조건 문 앞에 선행된다.
- sqlRange($A, $B) - $a <= column < $b
- sqlNow() - sql now() function

## 4. CRUD

CRUD 중 다음 method를 지원한다.

- sqlInsert(table,data)
- sqlUpdate(table, data, where);
- sqlDelete(table, where)
- sqlInsertOrUpdate(table, data, update_where = null) - INSERT INTO ~~ ON DUPLICATE KEY UPDATE 구문

- sqlInsertBulk(table, data_keys, data_valuess) - Bulk용 sqlInsert
- sqlInsertOrUpdateBulk(table, data_keys, data_valuess) - Bulk용 sqlInsertOrUpdate

변수는 다음과 같다.

- table - 단일 테이블 명 (sqlTable을 적용하지 않은)
- data - 추가/수정 할 데이터 배열, column => value 로 구성되어야 함
   - data_keys 추가/수정 할 데이터의 키 배열
   - data_valuess 추가/수정 할 데이터 배열 나열, [[column => value], [column => value] ... ] 로 구성되어야 함
- where, update_where - 조건 문 (sqlWhere을 없어도 됨)

## 5. transaction

transaction은 다음 두가지 방법을 지원한다.

- transactional

  - ```php
    $success = $db->transactional(function($db) {
      $db->sqlDo($sql);
    });
    ```

- sqlBegin-sqlEnd

  - ```php
    $db->sqlBegin();
    dbQueries($db);
    $success = $db->sqlEnd();
    ```

  - sqlEnd 대신 sqlCommit, sqlRollback method도 가능.

## 6. 기타 등등

작성 되어 있는 method 목록

- `sqlDo($sql, ...)` - 단순 실행
- `sqlCount($table, $where)` - SELECT count(*) FROM ~~ 의 축약형
- `sqlData($sql, ...)` - Dict/Object와 다르게 SELECT절의 첫번째 컬럼만 추출한다, 값이 없으면 null
- `sqlDatas($sql, ...)` - 위와 동일하며, 값이 없으면 empty array
- `sqlArray($sql, ...)` - Dict와 유사하지만, key 값이 숫자로 입력됨 (PDO::FETCH_NUM)
- `sqlArrays($sql, ...)` - Dicts와 유사하고, 위와 동일
- `sqlLine($sql, ...)` - sqlArray와 동일
- `sqlLines($sql, ...)` - sqlArrays와 동일
- `sqlDump($sql, ...)` - query parse 결과값을 return
- `sqlDumpBegin(); sqlDo($sql, ...); sqlDumpEnd()` - sqlDo 호출 시 실행 된 query를 sqlDumpEnd에서 받을 수 있다.
