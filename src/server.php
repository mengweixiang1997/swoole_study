<?php
// 锁
$lock = new swoole_lock(SWOOLE_MUTEX);
echo "创建互斥锁".PHP_EOL;
$lock->lock();
if(pcntl_fork() > 0){
	sleep(1);
  	$lock->unlock();
}else{
	echo "子进程 等待锁" . PHP_EOL;
	$lock->lock();
  	echo "子进程 获取锁" . PHP_EOL;
  	$lock->unlock();  //释放锁
  	exit("子进程退出");
}
echo "主进程 释放锁";
unset($lock);
sleep(1);
echo "子进程退出"; 

  

  
// TCP
$host = '0.0.0.0';
$port = 9501;
$serv = new swoole_server($host, $port);
$serv->on('connect', function($serv, $fd){
	echo "建立连接";
});

/** $server，Server对象
* $fd，TCP客户端连接的唯一标识符
* $reactor_id，TCP连接所在的Reactor线程ID
* $data，收到的数据内容，可能是文本或者二进制内容
*/
$serv->on('receive', function($serv, $fd, $from_id, $data){
	echo "收到数据";
  	var_dump($serv . PHP_EOL . $fd . PHP_EOL . $from_id . PHP_EOL . $data);
});
$serv->on('close', function(){
	echo "连接关闭";
});
$serv->start();
  
  
  
// async TCP
$serv = new swoole_server("0.0.0.0",9501);  
$serv->set([
  	'task_worker_num' => 4,
	]);  
$serv->on('receive',function($serv, $fd, $from_id, $data){
	$task_id = $serv->task($data);
  	echo "异步ID：" . $task_id . PHP_EOL; 
});

$serv->on('task', function($serv, $task_id, $from_id, $data){
	echo "执行异步ID：" . $task_id . PHP_EOL;
	$serv->finish($data . " =>  OK");
});

$serv->on('finish', function($serv, $task_id, $data){
	echo "执行完成";
});
$serv->start();

  
  
  

  
// async TCP client
$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
$client->on('connect', function($cli){
	$cli->send('Hello' . PHP_EOL);
});  

$client->on('receive', function($cli, $data){
  	echo "Data: " . $data;
});
  
$client->on('error', function($cli){
	echo "失败" . PHP_EOL;
});
  
$client->on('close', function($cli){
	echo "关闭" . PHP_EOL;
});
  
$client->connect("192.168.0.101",8080,10);
  
  
  
  
  
// UDP
$serv = new swoole_server('0.0.0.0', 9502, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
//监听数据接收的事件
/*
*  $serv: 服务器信息
*  $data: 数据,接收到的数据
*  $fd:   客户端信息
**/
$serv->on('packet', function($serv, $data, $fd){
	$serv->sendto($fd['address'], $fd['port'],"Server: $data");
  	var_dump($fd);
});
$serv->start();
  

  
  

// HTTP
$serv = new swoole_http_server('0.0.0.0',9501);
$serv->on('request',function($request, $response){
	var_dump($request);
  	$response->header("Content-Type","text/html; charset=utf-8");
  	$response->end("hello world".rand(100,999));
});
$serv->start();
  
  
  
  
  
// WEBSOKET
$ws = new swoole_websocket_server('0.0.0.0',9501);
$fd;
// $ws服务器信息 $request客户端信息
$ws->on('open', function($ws, $request){
	// var_dump($request);
  	$fd = $request->fd;
  	$ws->push($fd, "Welcome \n");
});
/**
 * $frame 客户端发送的信息
 * $frame->fd 客户端的唯一编号
 * $frame->data 客户端发送的信息
 * */
$ws->on('message', function($ws, $frame){
	// echo "Message:" . $request->data;
	$ws->push($frame->fd, "测试");
});
$ws->on('close', function(){
	echo "close\n";
});

$ws->on('WorkerStart', function($ws){
	if($ws->worker_id === 0){
        swoole_timer_tick(1000, function($timer_id) use ($ws){
            foreach($ws->connections as $fd){
                $ws->push($fd, "测试". date("Y-m-d H:i:s"));
            }
        });    
    }
});

$ws->start();




// TIMER
swoole_timer_tick(2000, function($timer_id){
	echo "执行" . $timer_id . PHP_EOL;
});
swoole_timer_after(3000, function(){
	echo "3000 ms 后执行" . PHP_EOL;
});
  
  
  
  

// PROCESS  
function doProcess(swoole_process $worker){
	echo var_dump($worker);
  	sleep(3);
}

$process = new swoole_process("doProcess");
$pid = $process->start();

// 等待结束
swoole_process::wait();

  
  
  
// PROCESS events
$workers = [];
$worker_num = 3;
// 创建启动进程
for($i = 0; $i < $worker_num; $i++){
	$process = new swoole_process('doProcess'); //创建单独的新进程
  	$pid = $process->start();  //启动进程，并获取进程ID
  	$workers[$pid] = $process; //存入数组
}
function doProcess(swoole_process $process){
	$process->write("PID: " . $process->pid); //子进程写入信息
  	echo "写入信息：";
    echo $process->id; 
    echo $process->callback . PHP_EOL;
}
//添加进程时间，向每个子进程添加一个执行动作
foreach($workers as $process){
	swoole_event_add($process->pipe, function($pipe) use ($process){
    	$data = $process->read();
      	echo "接收到：" . $data;
    });
}
  
  
// PROCESS QUEUE
$workers = [];  
$worker_num = 2;
for ($i = 0; $i < $worker_num; $i++){
	$process = new swoole_process('doProcess', false, false); //创建子进程
	$process->useQueue(); //开启队列，类似于全局函数
  	$pid = $process->start();
  	$workers[$pid] = $process;
}
function doProcess(swoole_process $process){
	$recv = $process->pop(); //8192
  	echo "从主进程获取到的数据：". $recv . PHP_EOL;
  	sleep(5);
  	$process->exit(0);
}

foreach($workers as $pid => $process){
	$process->push("Hello: 子进程" . $pid . PHP_EOL);
}

for($i = 0; $i < $worker_num; $i++){
	$ret = swoole_process::wait();
	$pid = $ret['pid'];
  	unset($workers[$pid]);
  	echo "子进程退出" . $pid . PHP_EOL;
}


// 信号
use Swoole\Process;
Process::signal(SIGALRM, function () {
    static $i = 0;
    echo "#{$i}\talarm\n";
    $i++;
    if ($i > 20) {
        Process::alarm(-1);
    }
});
//100ms
Process::alarm(100 * 1000);
