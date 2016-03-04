<?php
class onlineAction extends frontendAction {
    
	/*
	* 统计网站访问量*用户活跃度
	* chl
	* 20150127
	*/
    public function online_count() {
		
		//setcookie("user_online","",time()-1);//清空cookie
		
		$uid      = $this->visitor->info['id'];
		$origin   = $this->_get('origin','intval');
		$date     = date("Y-m-d",strtotime("+1 day"));//第二天
		$date     = explode('-',$date);
		$date_t   = mktime(0,0,0,$date[1],$date[2],$date[0]);//cookie到第二天失效
		$time     = time();
		
		//微信中有过消息通讯的注册用户数
		if($uid){
			//记录cookie//一维数组保存cookie
			$cookies_data = unserialize($_COOKIE['user_online']);
			$array=array($uid);
			if($_COOKIE['user_online']){
				if(!in_array($uid,$cookies_data)){
					$cookies_data[]= $uid;
					$arr_str = serialize($cookies_data);
					setcookie('user_online',$arr_str, $date_t); 
					//判断是否访问过*未访问的情况
					D('online')->user_online($uid,$origin,$get_ip,$time);
				}
			}else{
				$arr_str = serialize($array);
				setcookie('user_online',$arr_str, $date_t); 
				//第一次访问
				D('online')->user_online($uid,$origin,$get_ip,$time);
			}
		}
		
		
		
		
    }
	
	
    

}