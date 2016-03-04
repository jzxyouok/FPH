<?php
class lotteryAction extends frontendAction {
	
	/*
	* 抽奖活动
	* lishun
	*/
    public function index(){
		//echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0,user-scalable=no" />';
		//echo '本阶段红包完毕,下午三点开始第二波';exit;
		//echo 'qqqq';exit; !isset($_SESSION['lottery'])
		//cookie('hongbao', null);
		$time_start = 1431050400;
		$time_end   = 1431051000;
		$time       = time();
		if($time < $time_start || $time > $time_end){
			$this->redirect('weixin/lottery/defaults');
			exit;
		}
		
		$user=cookie('hongbao');
        $openid_cook=$user->openid;
		if(!$openid_cook || empty($openid_cook) || !$_SESSION['lottery']['subscribe']){
			load("@.wechat_functions");	 //加载微信函数 位置common文件夹下面
			$appid = C('AppID');
			$appsecret = C('AppSecret');
			$code = $_GET["code"];
			if(!$code){
				$url='http://www.fangpinhui.com/weixin/lottery/index';
				redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.urlencode($url).'&response_type=code&scope=snsapi_base&state=123#wechat_redirect');
				exit;
			}
			$get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$appsecret.'&code='.$code.'&grant_type=authorization_code';
			$json_obj = json_decode(httpGet($get_token_url),true);
			$openid = $json_obj['openid'];//用户的openid
			//$openid = 'opxklt1cXqYFy9NXKcNg5pV1HUnc';
			$user_info=getwechatuser($appid,$appsecret,$openid);
			$_SESSION['lottery']['subscribe'] = $user_info['subscribe'];
			$_SESSION['lottery']['openid']    = $user_info['openid'];
		}
		//echo $user_info['subscribe'].'--'.$user_info['openid'].'<br>';
		//echo $_SESSION['lottery']['subscribe'];
		print_r($user_info);//这个里面也有用户的openid
		exit;
		
     	$uid = $this->visitor->info['id'];
		if(!$openid_cook){
			if($uid && $_SESSION['lottery']['openid']){
				$lottery_count=M('weixin_lottery')->where("(uid=".$uid." OR open_id='".$_SESSION['lottery']['openid']."') AND type=3")->count('id');
			}
        }else{
			$lottery_count = 1;
		}
		$this->assign('lottery_count', $lottery_count);
		$this->assign('subscribe', $_SESSION['lottery']['subscribe']);
		$this->assign('openid', $_SESSION['lottery']['openid']);
		$this->assign('uid', $uid);
		$send_code = session('send_code',random(6,1)); 
        $send_code = session('send_code');
		$this->assign('send_code', $send_code);
		
		//分享
		$time = time();
		$this->assign('jssdk', A('weixin/jssdk')->getSignPackage());
		$this->assign('time', $time);
		$url = 'http://www.fangpinhui.com';
		$this->assign('url', $url);
		
		$this->assign('setTitle', '房品会--五月花钱季,放纵抢红包');
        $this->display();
    }
   
   //发送验证码
	public function send_sms(){ 
		if(IS_POST){
			$uid         = $this->visitor->info['id'];
			$uid && $this->ajaxReturn(0, '你已经是登录状态');
			
			$user      = cookie('hongbao');
			$openid_cook = $user->openid;
			$openid_cook && $this->ajaxReturn(0, '你已经抢过红包');
			
			$mobile      = $this->_post('mobile','trim');
			$send_code   = $this->_post('send_code','trim');
			$mobile_code = random(6,1);
			$send_sms    = D('send_sms');
			
			if(empty($mobile)){
				$this->ajaxReturn(0, '手机号码不能为空');
			}
			if(!checkMobile($mobile)){
				$this->ajaxReturn(0, '手机号码格式不正确');
			}
			if(empty($send_code)){
				$this->ajaxReturn(0, '系统验证码为空');
			}
			$_SESSION['mobile']      = $mobile;
			$_SESSION['mobile_code'] = $mobile_code;

			if($mobile_code){
				$send_sms->Messages($mobile,$agent_mobile,$agent_name,$client_name,$client_mobile,$title,$str,$mobile_code,'3',true,1,$mobile_code_origin);
				$this->ajaxReturn(1, '' ,$mobile_code);
			}else{
				$this->ajaxReturn(0, '发送失败');
			}
		}
	}
	
	//登录*注册验证
	public function login_submit(){
		$mobile      = $this->_post('mobile','trim');
		$mobile_code = $this->_post('mobile_code','trim');
		$send_sms    = D('send_sms');
		
		$user      = cookie('hongbao');
        $openid_cook = $user->openid;
		$openid_cook && $this->ajaxReturn(0, '你已经抢过红包');

		!$mobile && $this->ajaxReturn(0,'请输入手机号码');
		!$mobile_code && $this->ajaxReturn(0,'请输入验证号');
		
		if($mobile != $_SESSION['mobile']){
			$this->ajaxReturn(0,'手机号码输入错误');
		}
		if($mobile_code != $_SESSION['mobile_code']){
			$this->ajaxReturn(0,'验证码输入错误');
		}
		
		$uid = M('user')->where("mobile='".$mobile."' ")->getField('id');
		//如果不存在，写入数据库
		if(!$uid){
			$password = '123456';
			$data['password']  = md5($password);
			$data['mobile']    = $mobile;
			$data['origin']    = 1;//微信端
			$data['reg_ip']    = get_client_ip();
			$data['reg_time']  = time();
			$data['status']    = 1;
			$send_sms->Messages($mobile,$agent_mobile,$agent_name,$client_name,$client_mobile,$title,$str,$password,'5',false,1,1);
			$uid = M('user')->add($data);
		}
		if($uid){
			$this->visitor->login($uid,1);
			$this->ajaxReturn(1, '');
		}else{
			$this->ajaxReturn(0, '验证失败');
		}
	}
	
	//点击抢红包
	public function click_button_qq(){
		if(IS_POST){
			$time_start = 1431050400;
			$time_end   = 1431051180;
			$time       = time();
			if($time < $time_start || $time > $time_end){
				$this->ajaxReturn(0, '本阶段红包派发完毕');
			}
			$open_id   = $_SESSION['lottery']['openid'];
			$subscribe = $_SESSION['lottery']['subscribe'];
			$uid       = $this->visitor->info['id'];
			$user      = cookie('hongbao');
			$openid_cook = $user->openid;
			
			!$uid && $this->ajaxReturn(0, '不登录就想抢钱么?');
			!$open_id && $this->ajaxReturn(0, '个人信息读取失败');
			!$subscribe && $this->ajaxReturn(0, '个人信息读取失败,你可能没关注');
			$openid_cook && $this->ajaxReturn(0, '你已经抢过红包');
			if(!$openid_cook){
				if($uid && $open_id){
					$lottery_count = M('weixin_lottery')->where("(uid=".$uid." OR open_id='".$open_id."') AND type=3")->count('id');
					$lottery_count && $this->ajaxReturn(0, '你已经抢过红包');
				}
			}else{
				$this->ajaxReturn(0, '你已经抢过红包');
			}
			$rand_num = mt_rand(1, 3);
			$i = M('weixin_guaguaka')->field('min,max')->where(array('id'=>$rand_num,'status'=>1))->find();
			$amount=mt_rand($i['min'],$i['max']);
			
			if($rand_info < 100){
				Vendor("hongbao.pay");
				$packet = new Packet();
				$msg = $packet->_route('wxpacket',array('openid'=>$open_id,'amount'=>$amount));
				
				$data['uid']      = $uid;
				$data['amount']   = $amount/100;
				$data['add_time'] = time();
				$data['type']     = 3;
				$data['intro']    = $msg;
				$data['open_id']  = $open_id;
				$data['ip']       = get_client_ip();
				
				if(strstr($msg,'SUCCESS')){
					$data['status']   = 1;
					M('weixin_lottery')->add($data);
					cookie('hongbao',array('uid' => $uid,'openid' => $open_id),86400*2);
					$this->ajaxReturn(1, '成功', $amount/100);
				}else{
					$data['status']   = 0;
					M('weixin_lottery')->add($data);
					$this->ajaxReturn(0, $msg.',再来一次');
				}
			}else{
				$this->ajaxReturn(0, '该红包小于一元,再来一次！');
			}
		}
	}
	
	//默认页面
	public function defaults(){
		$this->assign('setTitle', '房品会--五月花钱季,放纵抢红包');
        $this->display();
	}
	
	
	
	
	
	
}