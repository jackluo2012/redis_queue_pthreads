<?php
	$redis = new redis();
	$redis->connect('127.0.0.1',6379);
/*
	$redis->lpush('test','1111');
	$redis->lpush('test','2222');
	$redis->lpush('test','3333');
*/
	$lpop = $redis->lpop('test');
	print_r($lpop);
	$lpop = $redis->lpop('test');
	print_r($lpop);
	$lpop = $redis->lpop('test');
	print_r($lpop);

	$list = $redis->lrange('test',0,-1);

print_r($list);