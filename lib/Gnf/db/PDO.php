<?php
namespace Gnf\db;

class PDO extends base
{
	/**
	 * @var \Doctrine\DBAL\Driver\Connection
	 */
	private $db;
	private $select_db;

	public function __construct(\Doctrine\DBAL\Driver\Connection $pdo)
	{
		parent::__construct();
		$this->db = $pdo;
	}

	public function select_db($db)
	{
		$this->select_db = $db;
	}

	/*
	 * addslashes is not safe in multibyte
	 * str_replace is safe in multibyte but only utf-8
	 */
	protected function escapeLiteral($value)
	{
		if (!is_string($value)) {
			$value = strval($value);
		}

		return str_replace(
			array('\\', "\0", "\n", "\r", "'", '"', "\x1a"),
			array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'),
			$value
		);
	}

	protected function query($sql)
	{
		return $this->db->query($sql);
	}

	/**
	 * @param \Doctrine\DBAL\Driver\Statement $handle
	 * @return null|\stdClass
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
	 * @param \Doctrine\DBAL\Driver\Statement $handle
	 * @return mixed
	 */
	protected function fetchRow($handle)
	{
		return $handle->fetch(\PDO::FETCH_NUM);
	}

	/**
	 * @param \Doctrine\DBAL\Driver\Statement $handle
	 * @return mixed
	 */
	protected function fetchAssoc($handle)
	{
		return $handle->fetch(\PDO::FETCH_ASSOC);
	}

	/**
	 * @param \Doctrine\DBAL\Driver\Statement $handle
	 * @return mixed
	 */
	protected function fetchObject($handle)
	{
		return $handle->fetch(\PDO::FETCH_OBJ);
	}

	/**
	 * @param \Doctrine\DBAL\Driver\Statement $handle
	 * @return mixed
	 */
	protected function fetchBoth($handle)
	{
		return $handle->fetch(\PDO::FETCH_BOTH);
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
