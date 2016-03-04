<?php
/**
 * @author CH - L
 */
class Packet{
	private $wxapi;
    function _route($fun,$param = ''){
		@require_once "oauth2.php";
		$this->wxapi = new Wxapi();
		switch ($fun)
		{
			case 'userinfo':
				return $this->userinfo($param);
				break;
			case 'wxpacket':
				return $this->wxpacket($param);
				break;			
			default:
				exit("Error_fun");
		}		
    }	
    /**
     * 用户信息
     * 
     */	
	private function userinfo($param){ 
		$get = $param['param'];
		$code = $param['code'];	
		//echo $code;
		if($get=='access_token' && !empty($code)){
			$json = $this->wxapi->get_access_token($code);
			return $json;
			/*if(!empty($json)){
				$userinfo = $this->wxapi->get_user_info($json['access_token'],$json['openid']);
				return $userinfo;
			}
			print_r($userinfo);*/
		}else{
			$this->wxapi->get_authorize_url('http://www.fangpinhui.com/?g=weixin&m=lottery&a=lottery_share&param=access_token','STATE');
			//http://www.fangpinhui.com/weixin/weixinapi/hongbao
		}
	}
    /**
     * 微信红包
     * 
     */		
	private function wxpacket($param){
		//return $this->Wxapi->pay('opxklt1cXqYFy9NXKcNg5pV1HUnc');
		return $this->wxapi->pay($param['openid'],$param['amount']);
	}
	
	
}
?>