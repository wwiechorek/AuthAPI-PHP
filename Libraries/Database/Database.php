<?php
namespace Libraries\Database;

class Database {
	function __construct() {
		$this->instance = Connection::getInstance();
		foreach ($this->instance as $key => $value) {
			$this->$key = $value;
		}
	}

	function getInstance($named = 'db') {
		$instance = Connection::getInstance();
		return $instance[$named];
	}

	function newConnection($config, $named = 'db'){
		$connectDB = Connection::getInstance($named);
		$connectDB[$named]->newConnection($config);
	}

}
