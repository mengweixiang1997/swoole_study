<?php

class Ws
{
	private static $host = "0.0.0.0";
	private static $port = 9501;
	public $ws = null;

	function __construct()
	{
		$this->ws = new swoole_websocket_server(self::$host, self::$port);

      	$this->ws->set([
        	'worker_num'       => 4,
          	'task_worker_num'  => 2,
        ]);

		$this->ws->on("open", [$this, 'onOpen']);
      	$this->ws->on("task", [$this, 'onTask']);
      	$this->ws->on("finish", [$this, 'onFinish']);		
		$this->ws->on("message", [$this, 'onMessage']);
		$this->ws->on("close", [$this, 'onClose']);

		$this->ws->start();
	}


	/**
	 * 监听ws连接事件
	 * @param  $ws      
	 * @param  $request   
	 */
	public function onOpen($ws, $request)
	{
		var_dump($request->fd);

		if ($request->fd == 1) {
			swoole_timer_tick(2000, function($timer_id){
				echo "2s: timerId:" . $timer_id . PHP_EOL;

			});
		}
	}



	/**
	 * 监听ws的消息事件
	 * @param   $ws    
	 * @param   $frame 
	 * @return         
	 */
	public function onMessage($ws, $frame)
	{
		echo "Server push message:" . $frame->data . PHP_EOL;
		$ws->push($frame->fd, "server push" . date("Y-m-d H:i:s"));
		$data = [
        	'task' => 1,
          	'fd'   => $frame->fd,
        ];
      	// $ws->task($data);
      	
      	swoole_timer_after(5000, function() use ($ws, $frame){  //这个是异步的
      		echo "5s after".PHP_EOL;
      		$ws->push($frame->fd, "Server time after");
      	});
      	
      	$ws->push($frame->fd, 'server push: ' . date("Y-m-d H:i:s"));
	}



	/**
	 * 创建task任务
	 * @param   $ws       
	 * @param   $taskId   
	 * @param   $workerId 
	 * @param   $data     
	 * @return            
	 */
  	public function onTask($ws, $taskId, $workerId, $data)
    {
      	print_r($data);
      	sleep(10);

      	return "on Task finish"; // 告诉Task进程
    }
  


    /**
     * 结束任务
     * @param  $ws    
     * @param  $taskId
     * @param  $data  
     */
  	public function onFinish($ws, $taskId, $data)
  	{
  		echo "Task id" . $taskId . PHP_EOL;
  		echo "finish data sucess:" . $data . PHP_EOL;
  	}




	/**
	 * close
	 * @param   $ws 
	 * @param   $fd 
	 * @return      
	 */
	public function onClose($ws, $fd)
	{
		echo "Client id:" . $fd . PHP_EOL;

	}



}


$obj = new Ws();