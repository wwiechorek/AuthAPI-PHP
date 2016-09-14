<?php
namespace Libraries\Database;

class Connection{
	//O METODO SINGLETON
	private static $instance = null;
	public static function getInstance($named = '') {
		if(empty($named)) return self::$instance;
		if (!isset(self::$instance[$named])) {
			$c = __CLASS__;
			self::$instance[$named] = new $c;
		}
		return self::$instance;
	}

	private $_PDO;
	private function __construct() { }

	public function newConnection($config){
		$driver = (isset($config['driver'])) ? $config['driver'] : "mysql";
		$host = (isset($config['host'])) ? $config['host'] : "localhost";
		$dbname = $config['dbname'];
		$user = (isset($config['user'])) ? $config['user'] : "root";
		$pass = (isset($config['pass'])) ? $config['pass'] : "123456";
		try{
			$this->_PDO = new \PDO($driver.":host=$host;dbname=".$dbname.";charset=utf8", $user, $pass);
			$this->_PDO->setAttribute(\PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES 'utf8'");
			$this->_PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    	$this->_PDO->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
		}catch(PDOException $e) {
			$config['error']($e->getMessage());
		}

	}

	public function query($query){
		$query = new Query($this->_PDO, $query);
		return $query;
	}

	public function insertId(){
		return $this->_PDO->lastInsertId();
	}

	public function escape($str){
		return $this->_PDO->quote($str);
	}

	public function __destruct(){
		$this->_PDO = null;
	}
}
