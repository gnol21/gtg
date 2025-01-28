<?php

class Connect extends PDO{
	
	public function __construct(){
		
		parent::__construct("mysql:host=sql107.infinityfree.com;dbname=if0_37839690_mydata",'if0_37839690','empasis52186',array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		
		$this->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		$this->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
	}
	
}

?>
