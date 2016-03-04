<?php
class onlineAction extends frontendAction {
    
	/*
	* 统计网站访问量*用户活跃度
	* chl
	* 20150127
	*/
    public function online_count() {
		
		//setcookie("var_ip","",time()-3600);//清空cookie
		
		$uid      = $this->visitor->info['id'];
		$origin   = $this->_get('origin','intval');
		$today    = date("Y-m-d");
		$date     = date("Y-m-d",strtotime("+1 day"));//第二天
		$date     = explode('-',$date);
		$date_t   = mktime(0,0,0,$date[1],$date[2],$date[0]);//cookie到第二天失效
		$time     = time();
		$get_ip   = get_client_ip();
		
		setcookie ("var_ip", $get_ip, $date_t);
		
		//判断今天是否访问过
		if($_COOKIE['var_ip']==''){
			$ip_count = D('online')->ip_count($today,$origin,$uid=0,$get_ip);
			if(!$ip_count){
				D('online')->user_online($uid=0,$origin,$get_ip,$time);
			}
		}
		
		
		
		
		
		
		
    }
	
	
    

}