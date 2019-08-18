<?php 

/**
 * 
 */
class DB{
	public $dbSource = "";
	public $dbConfig = [];
	function __construct()
	{
		$this->dbSource = new Swoole\Mysql;
		$this->dbConfig = [
			'host'     => '127.0.0.1',
			'port'     => 3306,
			'user'     => 'root',
			'password' => 123456,
			'database' => 'sys',
			'charset'  => 'utf8',
		];
	}


	public function update()
	{


	}



	public function add()
	{


	}



	public function execute()
	{
		//connect 
		$this->dbSource->connect($this->dbConfig, function($db, $result){
			if ($result === false) {
				var_dump($db->connect_error);
			}

			$sql = "select * from session";
			$db->query($sql, function($db, $result){
				// select => result 查询结果集
				// add update delete => bool
				if ($result === false) {
					return false;
				}else if ($result === false) {
					return true;
				}else{
					var_dump($result);
				}
				
			});
			
		});
		return true;
	}

}

$obj = new DB();
$obj->execute();