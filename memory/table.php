<?php 

$table = new swoole_table(1024);

$table->column('id', $table::TYPE_INT, 4);
$table->column('name', $table::TYPE_STRING, 64);
$table->column('age', $table::TYPE_INT, 3);
$table->create();

$table->set('test_1', ['id' => 1, 'name' => 'test', 'age' => 30]);


$table['test_2'] = [
	'id'    => 1,
	'name'  => 'test',
	'age'   => 31,
];

$table->decr('test_2', 'age', 2);  //原子操作  incr decr
$table->del('test_2'); // 删除
print_r($table->get('test_2'));
