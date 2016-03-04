<?php

class onlineModel extends Model
{
	/*
	* 微信中有过消息通讯的注册用户数
	* @param $uid     用户id
	* @param $origin  终端 终端 0:未知设备 1:微信 2:IOS 3:Android 4:PC 5:windowsPhone
	* @param $get_ip  客户端IP
	* @param $time    时间 time()
	* chl
	*/
	public function user_online($uid=0,$origin,$get_ip=0,$time){
		$list = M('online')->add(array('uid'=>$uid,'origin'=>$origin,'get_ip'=>$get_ip,'add_time'=>$time));	
		return $data;
	}
	
	//
	/*
	* 判断同一天是否有过访问 UV * 一天只记录一次
	* @param $today   今天
	* @param $origin  终端 终端 0:未知设备 1:微信 2:IOS 3:Android 4:PC 5:windowsPhone
	* @param  $uid    用户id
	* @param $get_ip  客户端IP
	* chl
	*/
	public function ip_count($today,$origin,$uid,$get_ip){
		$data = M('online')->where("FROM_UNIXTIME(add_time,'%Y-%m-%d') = '".$today."' AND origin=".$origin." AND uid=".$uid." AND get_ip='".$get_ip."'")->count('id');
		return $data;
	}
	
	
	
	
	
	
	
	
	
	
	
	
}