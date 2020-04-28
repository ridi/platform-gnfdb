<?php
namespace Gnf\db\Superclass;

interface StatementInterface
{
    public function sqlBegin();

    public function sqlEnd();

    public function sqlCommit();

    public function sqlRollback();

    public function sqlDo(...$args);

    public function sqlData(...$args);

    public function sqlDatas(...$args);

    public function sqlDict(...$args);

    public function sqlDicts(...$args);

    public function sqlObject(...$args);

    public function sqlObjects(...$args);

    public function sqlLine(...$args);

    public function sqlLines(...$args);

    public function sqlCount($table, $where);

    public function sqlInsert($table, $dats);

    public function sqlInsertBulk($table, $dat_keys, $dat_valuess);

    public function sqlInsertOrUpdate($table, $dats, $update = null);

    public function sqlInsertOrUpdateBulk($table, $dat_keys, $dat_valuess);

    public function sqlUpdate($table, $dats, $where);

    public function sqlDelete($table, $where);

    public function insertId();
}
