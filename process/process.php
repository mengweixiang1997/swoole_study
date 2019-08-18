<?php

$process = new swoole_process(function(swoole_process $pro){

	// echo "123"; //通讯模式下 不会打印 

	//
	$pro->exec("/usr/local/php/bin/php", [__dir__ . "/ws.php"]);



}, false);

$pid = $process->start();

echo $pid . PHP_EOL;


swoole_process::wait();