<?php
class userAction extends m_userbaseAction {
    
	//登录*admin表登录
	public function login(){
		
		if (IS_POST) {
            $mobile   = $this->_post('mobile', 'trim');
            $password = $this->_post('password', 'trim');
            $admin = M('admin')->field('id,username,password,mobile,role_id')->where(array('mobile'=>$mobile, 'status'=>1))->find();
			//$admin = M('user')->field('id,username,password,mobile')->where(array('mobile'=>$mobile, 'status'=>1))->find();
            if (!$admin) {
                $this->ajaxReturn(0,L('admin_not_exist'));
            }
            if ($admin['password'] != md5($password)) {
                $this->ajaxReturn(0,L('password_error'));
            }
            if(false !== M('admin')->where(array('id'=>$admin['id']))->save(array('last_time'=>time(), 'last_ip'=>get_client_ip()))){
			//if(false !== M('user')->where(array('id'=>$admin['id']))->save(array('last_time'=>time(), 'last_ip'=>get_client_ip()))){
				$time = 3600 * 24 * 28; //四周
				cookie('m_user_info', array('id'=>$admin['id'], 'username'=>$admin['username'], 'mobile'=>$admin['mobile'], 'role_id'=>$admin['role_id']), $time);
				//cookie('m_user_info', array('id'=>$admin['id'], 'username'=>$admin['username'], 'mobile'=>$admin['mobile']), $time);
				$this->ajaxReturn(1,L('login_successe'));
			}else{
				$this->ajaxReturn(0,L('login_error'));
			}
        }else{
			$this->m_user_cookie['id'] && $this->redirect('user/index');
            //来路
			$url =  urlencode($this->_get('url','trim'));
			if(!$url){
				$url = urlencode(__ROOT__.'/index.php?g=m&m=user&a=index');
			}
			$this->assign('url', $url);
			$this->assign('setTitle', '内部员工登录');
			$this->_config_seo();
        	$this->display();
        }
	}
	
	
    public function index() {
		$fph   = C('DB_PREFIX');
		
		//var_dump(cookie('m_user_info'));
		
		//echo $this->m_user_cookie['id'];
		$this->assign('setTitle', '工作平台');
        $this->_config_seo();
        $this->display();
    }
		
   
	
	/**
	 * 个人中心退出
	 * @data 2014/8/11 
	 * @author H.J.H
	 */
	public function logout() {
		cookie('m_user_info', null);
		$this->ajaxReturn(1, L('logout_successe'));
		//redirect ( U ( 'm/user/login' ) );
	}
	
	//修改密码
	public function password(){
		if(IS_POST){
			$password     = $this->_post('password','trim');
			$newpassword  = $this->_post('newpassword','trim');
			$newpassword2 = $this->_post('newpassword2','trim');
			$uid          = $this->m_user_cookie['id'];
			
			!$password && $this->ajaxReturn(0, '请输入登录密码');
			!$newpassword && $this->ajaxReturn(0, '请输入新密码');
			!$newpassword2 && $this->ajaxReturn(0, '请输入确认新密码');
			if($newpassword!=$newpassword2){
				$this->ajaxReturn(0, '两次密码输入的不一致');
			}
			
			$mid = M('admin')->where("id=".$uid." AND password='".md5($password)."'")->getfield('id');
			!$mid && $this->ajaxReturn(0, '登录密码输入错误');
			
			$data['password'] = md5($newpassword);
			if(false !== M('admin')->where(array('id'=>$uid))->save($data)){
				$this->ajaxReturn(1, '密码修改成功');
			}else{
				$this->ajaxReturn(0, '密码修改失败');
			}
			
		}
		
		$this->assign('setTitle', '修改密码');
		$this->_config_seo();
        $this->display();
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}