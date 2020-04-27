<?php
namespace Gnf\Tests\db;

use Gnf\db\base;

class BaseTestTarget extends base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function insertId()
    {
    }

    public function insert_id()
    {
    }

    protected function doConnect()
    {
    }

    protected function hasConnected()
    {
    }

    public function selectDb($db)
    {
    }

    public function select_db($db)
    {
    }

    protected function transactionBegin()
    {
    }

    protected function transactionCommit()
    {
    }

    protected function transactionRollback()
    {
    }

    /**
     * @return bool
     */
    protected function configIsSupportNestedTransaction()
    {
        return false;
    }

    protected function escapeLiteral($value)
    {
        if (!is_string($value)) {
            $value = strval($value);
        }

        return str_replace(
            ['\\', "\0", "\n", "\r", "'", '"', "\x1a"],
            ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'],
            $value
        );
    }

    protected function query($sql)
    {
    }

    protected function getError($handle)
    {
    }

    protected function fetchRow($handle)
    {
    }

    protected function fetchAssoc($handle)
    {
    }

    protected function fetchObject($handle)
    {
    }

    protected function fetchBoth($handle)
    {
    }

    /**
     * @param $handle
     *
     * @return int
     */
    protected function getAffectedRows($handle)
    {
    }
}
