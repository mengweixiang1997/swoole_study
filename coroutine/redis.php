<?php

$http = new swoole_http_server('0.0.0.0', 8081);


$http->on('request', function($request, $response){
	$redis = new Swoole\Coroutine\Redis();
	$redis->connect('127.0.0.1', 6379);
	$redis->set('test_1', time());
	// $value = $redis->get($request->get['key']);

	$value = $redis->get($request->get['key']);

	$response->header('Content-Type', 'text/plain');
	$response->end($value);

});


$http->start();