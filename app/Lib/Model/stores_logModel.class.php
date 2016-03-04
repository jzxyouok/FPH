<?php

class stores_logModel extends Model
{
	/*
	* 门店日志表录入
	* lishun
	*/
	public function insert($uid,$pid,$tim,$info){
		$data_log['pid']  = $pid;
		$data_log['uid']  = $uid;
    	$data_log['info'] = $info;
    	$data_log['add_time'] = $tim;
       	M('stores_log')->add($data_log);
	}
	/*
	* 门店日志表录入
	* lishun
	*/
	public function insert_m($uid,$pid,$tim,$info){
		$data_log['pid']  = $pid;
		$data_log['mid']  = $uid;
    	$data_log['info'] = $info;
    	$data_log['add_time'] = $tim;
       	M('stores_log')->add($data_log);
	}

}