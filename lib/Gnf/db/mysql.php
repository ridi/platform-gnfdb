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

class __gnfCache
{
	function __gnfCache($dbObject, $timeoutMin)
	{
		$this->obj = $dbObject;
		$this->timeoutMin = $timeoutMin;
	}

	function __call($method, $args)
	{
		$key = $this->getKey($method, $args);

		$result = false;
		$cache = $this->getCache($key, $result);
		if ($result == true) {
			return $cache;
		}

		$dat = call_user_func_array(array($this->obj, $method), $args);

		if ($dat === null
			|| $dat === false
			|| (is_array($dat) && count($dat) == 0)
		) {
			return $dat;
		}
		$this->setCache($key, $dat);
		return $dat;
	}

	function getKey($method, $args)
	{
		$key = sha1($method . serialize($args));
		$dir = '/mnt/tmpfs/gnfCache';
		$file = $dir . '/' . $key;
		return $file;
	}

	function getCache($key, &$resultReturn)
	{
		$dir = dirname($key);
		if (!is_dir($dir)) {
			@mkdir($dir, 0777, true);
		}
		if (!is_file($key)) {
			return null;
		}
		$diff = time() - filemtime($key);
		if ($diff >= $this->timeoutMin * 60) {
			return null;
		}
		$ret = file_get_contents($key);
		if ($ret === false) {
			return null;
		}
		$resultReturn = true;
		return unserialize($ret);
	}

	function setCache($key, $dat)
	{
		//윈도우에서는 안되네
		//if(!is_writable($key))
		//return false;

		//@file_put_contents($key, serialize($dat), LOCK_EX);
		$tmp_file = tempnam(dirname($key), basename($key));
		if (false !== @file_put_contents($tmp_file, serialize($dat))) {
			if (@rename($tmp_file, $key)) {
				@chmod($key, 0666 & ~umask());
				return;
			}
		}
	}
}
