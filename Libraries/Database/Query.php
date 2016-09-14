<?php
namespace Libraries\Database;

class Query{
	private $_PDO;
	private $_stmt;
	function __construct($_PDO, $query){
		$this->_PDO = $_PDO;
		$this->_stmt = $this->_PDO->prepare($query);
		$this->_stmt->execute();
	}

	public function result(){
		$this->_stmt->setFetchMode(\PDO::FETCH_OBJ);
		$fetchAll = $this->_stmt->fetchAll();
		return $fetchAll;
	}
	public function resultAssoc() {
		$this->_stmt->setFetchMode(\PDO::FETCH_ASSOC);
		$fetchAll = $this->_stmt->fetchAll();
		return $fetchAll;
	}

	public function rowCount(){
		return $this->_stmt->rowCount();
	}


	public function __destruct(){
		$this->_stmt = null;
		$this->_PDO = null;
	}
}
