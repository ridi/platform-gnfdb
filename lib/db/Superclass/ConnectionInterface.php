<?php
declare(strict_types=1);

namespace Gnf\db\Superclass;

interface ConnectionInterface
{
    public function doConnect();

    public function hasConnected();

    public function selectDb($db);

    public function transactionBegin();

    public function transactionCommit();

    public function transactionRollback();

    /**
     * @return bool
     */
    public function configIsSupportNestedTransaction();

    public function escapeLiteral($value);

    public function query($sql);

    public function getError($handle);

    public function fetchRow($handle);

    public function fetchAssoc($handle);

    public function fetchObject($handle);

    public function fetchBoth($handle);

    /**
     * @param $handle
     *
     * @return int
     */
    public function getAffectedRows($handle);
}
