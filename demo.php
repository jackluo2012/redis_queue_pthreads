<?php
	require "rediscluster.php";
	require "threads.php";

$redis = new RedisCluster();
$redis->connect(array('host'=>'127.0.0.1','port'=>6379));


//*
$cron_id = 10001;// cron 
$CRON_KEY = 'CRON_LIST'; //
$PHONE_KEY = 'PHONE_LIST:'.$cron_id;//

//cron info
$cron = $redis->hget($CRON_KEY,$cron_id);
if(empty($cron)){
	$cron = array('id'=>10,'name'=>'jackluo');//mysql data
	$redis->hset($CRON_KEY,$cron_id,$cron); // set redis	
}
//phone list
$phone_list = $redis->lrange($PHONE_KEY,0,-1);

if(empty($phone_list)){
	$phone_list =explode(',','13228191831,18608041585');	//mysql data
	//join  list
	if($phone_list){
		$redis->multi();
		foreach ($phone_list as $phone) {
			$redis->lpush($PHONE_KEY,$phone);			
		}
		$redis->exec();
	}
}

$CRON_COMP = 'CRON_COMP_'.$cron_id;

$redis->set($CRON_COMP,'On');
try{
	$pool = array();
	$PHONE_NUMBER = 1000; // phone count

	$MAX_THREAD = 8;

	// Off On 


	for($i=$PHONE_NUMBER;$i>0;$i-=$MAX_THREAD)
	{
		$switch = $redis->get($CRON_COMP);
		if($switch == 'Off'){
			unset($pool);
			break;
		}
		while(true){
			if(count($pool) < $MAX_THREAD){
				$pool[$i] = new Threads();
				$pool[$i]->start();
				break;
			}else{
				foreach ($pool as $name => $worker) {
					if(! $worker->isRunning()){
						unset($pool[$name]);
					}
				}
			}
		}
	}
} catch (Exception $e){
	//log
	$thread_err = "THREAD_ERR_LOG:".$cron_id;
	$redis->lpush($thread_err,$e->getMessage,false);
}