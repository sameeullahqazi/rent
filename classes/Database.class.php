<?php

class Database
{
	
	private $mysqli;
	
	public function __construct()
	{
		// error_log("MySQLi Constructor called!");		
		$db_host = 'localhost';
		$user = 'root';
		$pass = '';
		$db = 'wordpress';
		
		/*
		// FOR PRODUCTION
		$db_host = 'localhost';
		$user = 'ridgepvt_samee';
		$pass = 'Ridge143!';
		$db = 'ridgepvt_payroll'; // 'payroll';
		*/
		
		$this->mysqli = new mysqli($db_host, $user, $pass, $db);
		/* check connection */
		if ($this->mysqli->connect_errno) {
		error_log("Connect failed: " . $this->mysqli->connect_error);
		throw new Exception("Connect failed: " . $this->mysqli->connect_error);
		}
		else
		{
		// error_log("Successfully connected to database...");
		}
		// error_log("mysqli in constructor: " . var_export($this->mysqli, true));
	}
	
	public function __destruct()
	{
		// error_log("MySQLi Desctructor called!");
		if($this->mysqli->close())
		{
			// error_log("mysqli->close() closed successfully.");
		}
		else
		{
			error_log("mysqli->close() could not be closed successfully.");
		}
	}

	public function getMySQLiObj()
	{
		return $this->mysqli;
	}

	/**********************************************************************		WRAPPER FUNCTIONS FOR THE NEW MYSQLI INTERFACE	***********************************************************************/
	public static function real_escape_string($str)
	{
		global $mysqli;
		return $mysqli->real_escape_string($str);
	}

	public static function query($sql)
	{
		global $mysqli;
		$result = $mysqli->query($sql);
		if(is_bool($result) && !$result)
			throw new Exception($mysqli->error . "\nSQL: " . $sql);

		return $result;
	}

	public static function autocommit($auto_commit = true)
	{
		global $mysqli;
		$mysqli->autocommit($auto_commit);
	}

	public static function begin_transaction()
	{
		global $mysqli;
		return $mysqli->begin_transaction();
	}

	public static function commit()
	{
		global $mysqli;
		return $mysqli->commit();
	}

	public static function rollback()
	{
		global $mysqli;
		return $mysqli->rollback();
	}
}


?>
