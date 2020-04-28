<?php
namespace Gnf\db;

use Gnf\db\Superclass\ConnectionInterface;

class PDO implements ConnectionInterface
{
    /**
     * @var \PDO
     */
    protected $db;
    private $select_db;

    /**
     * @param \PDO $pdo
     */
    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function selectDb($db)
    {
        $this->select_db = $db;
    }

    public function query($sql)
    {
        return $this->db->query($sql);
    }

    /**
     * @param \PDOStatement $handle
     *
     * @return null|\stdClass
     */
    public function getError($handle)
    {
        $info = $handle->errorInfo();
        if ($info[1] != 0) {
            $ret = new \stdClass();
            $ret->errno = $info[1];
            $ret->message = $info[2];
            return $ret;
        } else {
            return null;
        }
    }

    /**
     * @param \PDOStatement $handle
     *
     * @return mixed
     */
    public function fetchRow($handle)
    {
        return $handle->fetch(\PDO::FETCH_NUM);
    }

    /**
     * @param \PDOStatement $handle
     *
     * @return mixed
     */
    public function fetchAssoc($handle)
    {
        return $handle->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param \PDOStatement $handle
     *
     * @return mixed
     */
    public function fetchObject($handle)
    {
        return $handle->fetch(\PDO::FETCH_OBJ);
    }

    /**
     * @param \PDOStatement $handle
     *
     * @return mixed
     */
    public function fetchBoth($handle)
    {
        return $handle->fetch(\PDO::FETCH_BOTH);
    }

    public function insertId()
    {
        return $this->db->lastInsertId();
    }

    /**
     * @param \PDOStatement $handle
     *
     * @return mixed
     */
    public function getAffectedRows($handle)
    {
        return $handle->rowCount();
    }

    public function hasConnected()
    {
        return is_resource($this->db);
    }

    public function doConnect()
    {
        $this->afterConnect();
        $this->db->query('USE ' . EscapeHelper::escapeLiteral($this->select_db));
    }

    public function transactionBegin()
    {
        $this->db->beginTransaction();
    }

    public function transactionCommit()
    {
        $this->db->commit();
    }

    public function transactionRollback()
    {
        $this->db->rollBack();
    }

    /**
     * @return bool
     */
    public function configIsSupportNestedTransaction()
    {
        return false;
    }
}
