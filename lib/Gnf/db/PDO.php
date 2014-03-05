<?php
namespace Gnf\db;

class PDO extends base
{
	private $select_db;

	public function gnfDB_PDO(\Doctrine\DBAL\Driver\Connection $pdo)
	{
		parent::__construct();
		$this->db = $pdo;
	}

	public function select_db($db)
	{
		$this->select_db = $db;
	}

	protected function escapeLiteral($value)
	{
		return addslashes($value);
	}

	protected function query($sql)
	{
		return $this->db->query($sql);
	}

	/**
	 * @param Doctrine\DBAL\Driver\Statement $handle
	 * @return null|stdClass
	 */
	protected function getError($handle)
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
	 * @param Doctrine\DBAL\Driver\Statement $handle
	 * @return mixed
	 */
	protected function fetchRow($handle)
	{
		return $handle->fetch(PDO::FETCH_NUM);
	}

	/**
	 * @param Doctrine\DBAL\Driver\Statement $handle
	 * @return mixed
	 */
	protected function fetchAssoc($handle)
	{
		return $handle->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * @param Doctrine\DBAL\Driver\Statement $handle
	 * @return mixed
	 */
	protected function fetchObject($handle)
	{
		return $handle->fetch(PDO::FETCH_OBJ);
	}

	/**
	 * @param Doctrine\DBAL\Driver\Statement $handle
	 * @return mixed
	 */
	protected function fetchBoth($handle)
	{
		return $handle->fetch(PDO::FETCH_BOTH);
	}

	public function insert_id()
	{
		return $this->db->lastInsertId();
	}

	/**
	 * @param Doctrine\DBAL\Driver\Statement $handle
	 * @return mixed
	 */
	protected function getAffectedRows($handle)
	{
		return $handle->rowCount();
	}

	protected function hasConnected()
	{
		return is_resource($this->db);
	}

	protected function doConnect()
	{
		$this->afterConnect();
		$this->db->query('USE ' . $this->escapeLiteral($this->select_db));
	}
}
