<?php

namespace Resource\Model;
use Think\Model;

class CommonModel extends Model {
	/*
	构造函数
	*/
	public function __construct($name='',$tablePrefix='',$connection='') {
		$this->connection = C('DB_OA');
		$this->tablePrefix = C('DB_OA.DB_PREFIX');
		parent::__construct($name, $tablePrefix, $connection);
   }
}
?>