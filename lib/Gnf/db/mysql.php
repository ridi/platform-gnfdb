<?php
namespace Gnf\db;



/**
 * @property mixed select_db
 */
class mysql extends base
{
	private $host;
	private $id;
	private $pass;

	public function __construct($host = null, $id = null, $pass = null)
	{
		parent::__construct();
		$this->host = $host;
		$this->id = $id;
		$this->pass = $pass;
	}

	public function select_db($db)
	{
		$this->select_db = $db;
	}

	protected function escapeLiteral($value)
	{
		$this->checkConnectionOrTry();

		return mysql_real_escape_string($value, $this->db);
	}

	protected function query($sql)
	{
		$this->checkConnectionOrTry();
		return mysql_query($sql, $this->db);
	}

	protected function getError($handle)
	{
		$this->checkConnectionOrTry();
		$errno = mysql_errno($this->db);
		if ($errno != 0) {
			$ret = new \stdClass();
			$ret->errno = $errno;
			$ret->message = mysql_error($this->db);
			return $ret;
		} else {
			return null;
		}
	}

	protected function fetchRow($handle)
	{
		return mysql_fetch_row($handle);
	}

	protected function fetchAssoc($handle)
	{
		return mysql_fetch_assoc($handle);
	}

	protected function fetchObject($handle)
	{
		return mysql_fetch_object($handle);
	}

	protected function fetchBoth($handle)
	{
		return mysql_fetch_array($handle);
	}

	public function insert_id()
	{
		$this->checkConnectionOrTry();
		return mysql_insert_id($this->db);
	}

	protected function getAffectedRows($handle)
	{
		$this->checkConnectionOrTry();
		return mysql_affected_rows($this->db);
	}

	protected function hasConnected()
	{
		return is_resource($this->db);
	}

	protected function doConnect()
	{
		$this->db = mysql_connect($this->host, $this->id, $this->pass, true);
		if (!$this->db) {
			throw new \Exception('[sql error] Mysql Connection error!');
		}
		$this->afterConnect();
		mysql_select_db($this->select_db, $this->db);
	}
}
