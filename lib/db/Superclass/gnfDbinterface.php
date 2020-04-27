<?php
namespace Gnf\db\Superclass;

interface gnfDbinterface
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

    public function sqlInsertBulk($table, $dat_keys, $dat_valuess);

    public function sqlInsertOrUpdate($table, $dats, $update = null);

    public function sqlInsertOrUpdateBulk($table, $dat_keys, $dat_valuess);

    public function sqlUpdate($table, $dats, $where);

    public function sqlDelete($table, $where);

    public function insertId();
}
