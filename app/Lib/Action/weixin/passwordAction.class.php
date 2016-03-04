<?php
class passwordAction extends frontendAction {
           
    //忘记密码
    public function index() {

		if(IS_POST){
			$mobile      = $this->_post('mobile','trim');
			$mobile_code = $this->_post('mobile_code','trim');
                        
			!$mobile && $this->ajaxReturn(0,'请输入手机号码');
			if(!checkMobile($mobile)){
				$this->ajaxReturn(0,'手机号码格式不正确');
			}	
			if($mobile != $_SESSION['mobile']){
				$this->ajaxReturn(0,'手机号码输入错误');
			}
			$mobile_info = M('user')->field('id,mobile')->where("mobile='".$mobile."'")->find();
			!$mobile_info && $this->ajaxReturn(0,'手机号码输入错误');
			
			!$mobile_code && $this->ajaxReturn(0,'请输入验证码');
			if($mobile_code != $_SESSION['mobile_code']){
				$this->ajaxReturn(0,'验证码输入错误');
			}
            $this->ajaxReturn(1,'提交成功',$mobile_info);
		}
		
		$send_code = session('send_code',random(6,1)); 
		$send_code = session('send_code');
		$this->assign('send_code', $send_code);
		
		$this->assign('setTitle', '修改密码');
        $this->_config_seo();
        $this->display();
    }
	
    public function password() {
        if(IS_POST){
			$mobile    = $this->_post('mobile','trim');
			$id        = $this->_post('id','intval');
			$password  = $this->_post('password','trim');
			$password2 = $this->_post('password2','trim');
			
		   !$mobile && $this->ajaxReturn(0,L('operation_failure'));
		   !$id && $this->ajaxReturn(0,L('operation_failure'));
		   if(!checkMobile($mobile)){
				$this->ajaxReturn(0,'手机号码格式不正确');
			}
		   !$password && $this->ajaxReturn(0,'密码不能为空');
		   if(!$password || strlen($password)<6){
				$this->ajaxReturn(0,'请输入不能小于6位的密码');
		   }
		   !$password2 && $this->ajaxReturn(0,'确认密码不能为空');
		   if($password != $password2){
			$this->ajaxReturn(0,'两次输入的密码不一致');
		   }
		   
		   $date['password'] = md5($password);
		   if(false !== M('user')->where("id=$id AND mobile='".$mobile."'")->save($date)){
			   $this->ajaxReturn(1,'修改成功,请登录');
		   }else{
			   $this->ajaxReturn(0,'修改失败');
		   }
        }
        $mobile = $this->_get('mobile','trim');
		$id     = $this->_get('id','trim');
		if(!$mobile && !$id){
			$this->error('非法参数');
		}
		if($mobile != $_SESSION['mobile']){
			$this->error('手机号码输入错误');
		}
		
        $this->assign('mobile',$mobile);
		$this->assign('id',$id);
		
        $this->assign('setTitle', '修改密码');
        $this->_config_seo();
        $this->display();
    }
    
    //发送验证码
    public function send_sms(){
		if(IS_POST){
			$mobile    = $this->_post('mobile','trim');
			$send_code = $this->_post('send_code','trim');
			
			$mobile_code = random(6,1);
			
			if(empty($mobile)){
				exit('手机号码不能为空');
			}
			if(!checkMobile($mobile)){
				exit('手机号码格式不正确');
			}
	
			$mobile_yes = M('user')->where("mobile='".$mobile."'")->count('id');
			if(!$mobile_yes){
					exit('没有查找到您的手机号码');
			}
				
			if(empty($_SESSION['send_code']) or $send_code!=$_SESSION['send_code']){
				//防短信轰炸机
				exit('非法请求');
			}

			//是否请求接口
			$send_sms = D('send_sms');
			$gets   = $send_sms->Messages($mobile,$agent_mobile,$agent_name,$client_name,$client_mobile,$title,$str,$mobile_code,'3',true,1,2);

			if($gets['SubmitResult']['code']==2){
				$_SESSION['mobile'] = $mobile;
				$_SESSION['mobile_code'] = $mobile_code;
			}
			echo $gets['SubmitResult']['msg'];
		}
	}
}