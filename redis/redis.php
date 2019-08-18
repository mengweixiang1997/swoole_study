<?php

$redisClinet = new swoole_redis;
$redisClinet->connect("127.0.0.1", 6379, function(swoole_redis $redisClinet, $result){
	echo "connect" . PHP_EOL;
	var_dump($result);

	//同步 redis (new Redis()->set('key',2));
	// $redisClinet->set('test_1', time(), function(swoole_redis $redisClinet, $result){
	// 	var_dump($result);
	// });

	// $redisClinet->get('test_1', function(swoole_redis $redisClinet, $result){
	// 	var_dump($result);
	// 	$redisClinet->close();
	// });

	// $redisClinet->keys("*", function(swoole_redis $redisClinet, $result){
	// 	var_dump($result);
	// 	$redisClinet->close();
	// });

	$redisClinet->keys("*1*", function(swoole_redis $redisClinet, $result){
		var_dump($result);
		$redisClinet->close();
	});


});


echo "start" . PHP_EOL;