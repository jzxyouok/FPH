<?php

class userAction extends userbaseAction {

    /**
     * 用户登陆
     */
    public function login() {
        $this->visitor->is_login && $this->redirect('user/index');
        if (IS_POST) {
            $mobile   = $this->_post('mobile', 'trim');
            $password = $this->_post('password', 'trim');
            $remember = $this->_post('remember','intval',1);
            if (empty($mobile)) {
                IS_AJAX && $this->ajaxReturn(0, L('mobile_empty'));
                $this->error(L('mobile_empty'));
            }
            if (empty($password)) {
                IS_AJAX && $this->ajaxReturn(0, L('passwd_empty'));
                $this->error(L('passwd_empty'));
            }
            //连接用户中心
            $passport = $this->_user_server();
            $uid = $passport->auth($mobile, $password);
            if (!$uid) {
                IS_AJAX && $this->ajaxReturn(0, $passport->get_error());
                $this->error($passport->get_error());
            }
            //登陆
            $this->visitor->login($uid, $remember);

            //同步登陆
            $synlogin = $passport->synlogin($uid);
            if (IS_AJAX) {
                $this->ajaxReturn(1, L('login_successe'));
            } else {
                //跳转到登陆前页面（执行同步操作）
                $ret_url = $this->_post('ret_url', 'trim');
                $this->success(L('login_successe').$synlogin, $ret_url);
            }
        } else {
            /* 同步退出外部系统 */
            if (!empty($_GET['synlogout'])) {
                $passport = $this->_user_server();
                $synlogout = $passport->synlogout();
            }
            if (IS_AJAX) {
                $resp = $this->fetch('dialog:login');
                $this->ajaxReturn(1, '', $resp);
            } else {
                //来路
                $ret_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __APP__;
                $this->assign('ret_url', $ret_url);
                $this->assign('synlogout', $synlogout);
                $this->_config_seo();
                $this->display();
            }
        }
    }

    /**
     * 用户退出
     */
    public function logout() {
        $this->visitor->logout();
        //同步退出
        $passport = $this->_user_server();
        $synlogout = $passport->synlogout();
        //跳转到退出前页面（执行同步操作）
        //$this->success(L('logout_successe').$synlogout, U('user/index'));
		$this->ajaxReturn(1, L('logout_successe'));
    }

    /**
    * 用户注册
    */
    public function register() {
        $this->visitor->is_login && $this->redirect('user/index');
        if (IS_POST) {
            //来源
            $type = $this->_post('type', 'trim');
            if ($type == 'pc') {
                //验证
                $agreement = $this->_post('agreement');
                !$agreement && $this->error(L('agreement_failed'));

                $captcha = $this->_post('captcha', 'trim');
                if(session('captcha') != md5($captcha)){
					IS_AJAX && $this->ajaxReturn(0, L('captcha_failed'));
                    $this->error(L('captcha_failed'));
                }
            }
			
			//$username = $this->_post('username', 'trim');
			$mobile      = $this->_post('mobile','trim');
			$mobile_code = $this->_post('mobile_code','trim');
			$password    = $this->_post('password','trim');
			$repassword  = $this->_post('password2','trim');
			$share_uid   = $this->_post('share_uid','intval');
			$origin      = $this->_post('origin','intval');
			//$company     = $this->_post('company','intval');
			//$stores_id   = $this->_post('stores_id','intval');
			$code_id     = $this->_post('code_id','intval');

			if(!$mobile) {
                IS_AJAX && $this->ajaxReturn(0, L('mobile_empty'));
                $this->error(L('mobile_empty'));
            }
			if(!checkMobile($mobile)){
				IS_AJAX && $this->ajaxReturn(0, L('mobile_regx_error'));
                $this->error(L('mobile_regx_error'));
			}
			
			if($mobile != $_SESSION['mobile']){
				IS_AJAX && $this->ajaxReturn(0, L('mobile_session_error'));
                $this->error(L('mobile_session_error'));
			}
			
			$mobile_con = M('user')->where("mobile='".$mobile."'")->count('id');
			if($mobile_con){
				IS_AJAX && $this->ajaxReturn(0, L('mobile_count'));
                $this->error(L('mobile_count'));
			}
			if(!$mobile_code) {
                IS_AJAX && $this->ajaxReturn(0, L('mobile_code_error'));
                $this->error(L('mobile_code_error'));
            }
			
			if($mobile_code != $_SESSION['mobile_code']){
				IS_AJAX && $this->ajaxReturn(0, L('mobile_code_input_error'));
				$this->error(L('mobile_code_input_error'));
			}
			
			if(!$password || strlen($password)<6 || strlen($password)>20){
				IS_AJAX && $this->ajaxReturn(0,L('passwd_input_error'));
				$this->error(L('passwd_input_error'));
			}
            if ($password != $repassword) {
				IS_AJAX && $this->ajaxReturn(0,L('inconsistent_password'));
				$this->error(L('inconsistent_password'));
            }
			/*
			if(!$company && !$code_id){
				IS_AJAX && $this->ajaxReturn(0,L('select_company'));
				$this->error(L('select_company'));
		 	}
			if($company && !$stores_id){
				IS_AJAX && $this->ajaxReturn(0,L('select_stores'));
				$this->error(L('select_stores'));
			}
			if($code_id && !is_numeric($code_id)){
				IS_AJAX && $this->ajaxReturn(0,L('stores_id_error'));
				$this->error(L('stores_id_error'));
			}
			*/
			if(!$origin) {
                IS_AJAX && $this->ajaxReturn(0, L('origin_error'));
                $this->error(L('origin_error'));
            }
            //连接用户中心
            $passport = $this->_user_server();
			/*
			//检测公司是否为合作状态
			if($company){
				$result = D('company')->company_teamwork($company);
				if(!$result){
					IS_AJAX && $this->ajaxReturn(0,L('company_teamwork_no'));
					$this->error(L('company_teamwork_no'));
				}
			}
			*/
			//验证门店代码
			if($code_id){
				$stores_info = D('stores')->stores_id($code_id);
				if(!$stores_info){
					IS_AJAX && $this->ajaxReturn(0, L('stores_id_error'));
					$this->error(L('stores_id_error'));
				}
				$stores_id = $stores_info['id'];
				//检测公司是否为合作状态
				$result = D('company')->company_teamwork($stores_info['pid']);
				if(!$result){
					IS_AJAX && $this->ajaxReturn(0,L('company_teamwork_no'));
					$this->error(L('company_teamwork_no'));
				}
			}else{
				$stores_id = 0;
			}
			
            //手机号码归属地
            $city_name = get_city($mobile);
            $city_id = M('city')->where("name='".$city_name."'")->getfield('id');
            if(!$city_id) $city_id=0;
			
            //注册
            $uid = $passport->register($username, $password, $mobile, $stores_id, $origin, $city_id);
            !$uid && $this->error($passport->get_error());
            //登陆
            $this->visitor->login($uid);
            //同步登陆
            $synlogin = $passport->synlogin($uid);
			
			//发送短信提醒****
			//是否请求接口
			$send_sms = D('send_sms');
			$gets   = $send_sms->Messages($mobile,$agent_mobile,$agent_name,$client_name,$client_mobile,$title,$str,$mobile_code,'4',false,1,$mobile_code_origin);
			//注册成功
			IS_AJAX && $this->ajaxReturn(1, L('register_successe'));
            $this->success(L('register_successe').$synlogin, U('user/index'));
        } else {
            //关闭注册
            if (!C('pin_reg_status')) {
                $this->error(C('pin_reg_closed_reason'));
            }
            $this->_config_seo();
            $this->display();
        }
    }
	
	/**
     * 修改密码
     */
    public function password() {
        if( IS_POST ){
            $oldpassword = $this->_post('oldpassword','trim');
            $password   = $this->_post('password','trim');
            $repassword = $this->_post('repassword','trim');
            !$password && $this->error(L('no_new_password'));
            $password != $repassword && $this->error(L('inconsistent_password'));
            $passlen = strlen($password);
            if ($passlen < 6 || $passlen > 20) {
                $this->error('password_length_error');
            }
            //连接用户中心
            $passport = $this->_user_server();
            $result = $passport->edit($this->visitor->info['id'], $oldpassword, array('password'=>$password));
            if ($result) {
                $msg = array('status'=>1, 'info'=>L('edit_password_success'));
            } else {
                $msg = array('status'=>0, 'info'=>$passport->get_error());
            }
            $this->assign('msg', $msg);
        }
        $this->_config_seo();
        $this->display();
    }

    
}