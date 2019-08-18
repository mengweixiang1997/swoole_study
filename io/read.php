<?php

// 异步文件系统仅限于4.3.0之前的版本，后续版本已经全面使用携程（coroutine）代替原有方案，具体参见：Coroutine模块
swoole_async_readfile(__DIR__ . DIRECTORY_SEPARATOR ."1.txt", function($filename, $fileContent){
	echo "filename:" . $filename . PHP_EOL;
	echo "content:" . $fileContent . PHP_EOL;
 
});
