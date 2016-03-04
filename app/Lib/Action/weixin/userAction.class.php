<?php
class userAction extends weixin_userbaseAction {
    
    public function _initialize() {
		parent::_initialize();
		$this->AppID     = C('AppID');
		$this->AppSecret = C('AppSecret');
	}

    public function index() {
		$uid   = $this->visitor->info['id'];
		$fph   = C('DB_PREFIX');
		load("@.wechat_functions");//加载微信函数 位置common文件夹下面

		//会员信息
		if($uid){
			$info  = M('user')->field('id,admin_id,username,avatar,city_id,mobile,address,stores_id,openid')->where(array('id'=>$uid, 'status'=>1))->find();
		}
		//$info['openid'] = 'opxklt1cXqYFy9NXKcNg5pV1HUnc';//测试
		if($info['openid']){
			//获取微信用户详细信息
			$user_info = getwechatuser($this->AppID,$this->AppSecret,$info['openid']);
		}else{
			//获取微信用户openid
			$code = $this->_get('code','trim');
			if(!$code){
				$url='http://www.fangpinhui.com/weixin/user/index';
				redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->AppID.'&redirect_uri='.urlencode($url).'&response_type=code&scope=snsapi_base&state=123#wechat_redirect');
				exit;
			}
			$get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->AppID.'&secret='.$this->AppSecret.'&code='.$code.'&grant_type=authorization_code';
			$json_obj = json_decode(httpGet($get_token_url),true);
			$openid = $json_obj['openid'];
			//获取微信用户详细信息
			$user_info=getwechatuser($this->AppID,$this->AppSecret,$openid);
		}
		$info['headimgurl'] = $user_info['headimgurl'];
		$info['nickname']   = $user_info['nickname'];
		$info['province']   = $user_info['province'];
		$info['city']       = $user_info['city'];
		//print_r($user_info).'<br>-------';
		//print_r($info);
		//exit;

		if($uid){
			//更新openid
			if(!$info['openid']){
				M('user')->where(array('id'=>$uid))->save(array('openid'=>$user_info['openid']));
			}

			/*//显示市 区
			if($info['city_id']){
				$area = M('city')->field('name,pid')->where('id ='.$info['city_id'])->find();
				$city = M('city')->field('name')->where('id ='.$area['pid'])->find();
				$info['user_address'] = $city['name'].'&nbsp;&nbsp;'.$area['name'].'&nbsp;&nbsp;'.$info['address'];
			}*/

			//是否为内部管理员
			$info['admin_mobile'] = M('admin')->where("mobile='".$info['mobile']."'")->count('id');

			$count = array();
			$count['kehu'] = $count['favorites'] = $count['share_id'];
			if(!empty($info['id'])){
				//我的客户
				//$count['kehu'] = count(M('myclient_property')->where('uid ='.$info['id'])->group('pid')->select());
				//我的收藏
				$count['favorites'] = M('favorites')->where('uid ='.$info['id'])->count('id');
				//我的团队
				//$count['share_id'] = M('user')->where('share_id ='.$info['id'])->count('id');
			}
						
			//计算佣金 已结佣
			$money_user = M('myclient')->field('A.name,A.mobile,B.property,B.status,B.buy_product,B.with_look,C.id,E.expenditure')
									   ->table("{$fph}myclient AS A
												INNER JOIN {$fph}myclient_property AS B ON A.id=B.pid
												INNER JOIN {$fph}myclient_status AS C ON C.mpid=B.id
												INNER JOIN {$fph}commission AS E ON E.pid=C.id")
									   ->where('B.uid='.$uid.' AND B.status =5')
									   ->order('B.add_time DESC')
									   ->select();
			$yijie  = 0;
			foreach ($money_user as $k => $v) {
				$yijie = M('expenditure')->where('pid='.$v['id'])->sum('price');//已结佣
			}
			$count['yijie'] = $yijie;
			
			//判断门店OR驻守
			if($info['stores_id']){
				$stores_info = M('stores')->field('type,name')->where("id=".$info['stores_id']."")->find();
			}
	        $this->assign('stores_info', $stores_info);
			
			//检测是否加入门店*或是否审核
			//$user_stores_stauts = M('user')->where("id=".$uid." AND (stores_id=0 OR store_status=0)")->count('id');
	        //$this->assign('user_stores_stauts', $user_stores_stauts);

	        //查找服务专员	        
	        if($info['admin_id']){
	        	$service_info = M('admin')->field('username,mobile')->where(array('id'=>$info['admin_id']))->find();
	    	}elseif(!$info['admin_id'] && $info['stores_id']){
	    		$stores_info = M('stores')->field('type,sid')->where(array('id'=>$info['stores_id']))->find();
	    		if($stores_info['type']==1){
	    			$service_info = M('admin')->field('username,mobile')->where(array('id'=>$stores_info['sid']))->find();
	    		}else{
	    			$service_info = M('user')->field('username,mobile,avatar')->where(array('id'=>$stores_info['sid']))->find();
	    		}
	    	}
	    	$this->assign('service_info', $service_info);
	    }
		
		//统计驻守楼盘
		if($uid){
			$stat_property = M('user')->where(array('id'=>$uid))->getfield('stat_property');
			if($stat_property){
				$this->assign('stat_property', count(explode(',',$stat_property)));
			}else{
				$this->assign('stat_property', 0);
			}
		}
		$this->assign('count', $count);
		$this->assign('info', $info);
		$this->assign('setTitle', '会员中心');
        $this->_config_seo();
        $this->display();
    }
	
    public function register() {
		
	    $uid = $this->visitor->info['id'];
	    if($uid){
			$user_url = __ROOT__.'/index.php?g=weixin&m=user&a=index';
			header("Location: $user_url");
		}
	
		$share_id = $this->_get('share_id','trim');
		$share_uid = M('user')->where("md5(id)='".$share_id."'")->getfield('id');
		$this->assign('share_uid', $share_uid); 
		
		$send_code = session('send_code',random(6,1)); 
		$send_code = session('send_code');
		$this->assign('send_code', $send_code);
		
		//公司
		$company_list = D('company')->company_list();
		$this->assign('company_list', $company_list);
		
		$url = urlencode($this->_get('url','trim'));
		if(!$url){
			$url = urlencode(__ROOT__.'/index.php?g=weixin&m=user&a=index');
		}
		$this->assign('url', $url);
	
		$this->assign('setTitle', '会员注册');
        $this->_config_seo();
        $this->display();
    }
	
	//选择公司*对应门店数据
	public function ajax_stores(){
		$id = $this->_post('id','intval');
		!$id && $this->ajaxReturn(0,'非法提交');
		$list = D('stores')->stores_list($id);
		$this->ajaxReturn(1, '' ,$list);
		//echo json_encode($list);
	}
	
    public function login() {
    	
		$this->visitor->is_login && $this->redirect('weixin/user/index');
		if(IS_POST){
			$mobile      = $this->_post('mobile','trim');
			$mobile_code = $this->_post('mobile_code','trim');
			$admin_id    = $this->_post('admin_id','intval');

			//判断用户是否存在
			if($admin_id){
				$admin_count = M('admin')->where(array('id'=>$admin_id,'status'=>1))->count('id');
				!$admin_count && $this->ajaxReturn(0, '该服务专员不存在或离职');
			}

			!$mobile && $this->ajaxReturn(0, L('mobile_empty'));
			if(!checkMobile($mobile)){
				$this->ajaxReturn(0, L('mobile_regx_error'));
			}
			if($mobile != $_SESSION['mobile']){
				$this->ajaxReturn(0, L('mobile_session_error'));
			}
			!$mobile_code && $this->ajaxReturn(0, L('mobile_code_error'));
			if($mobile_code != $_SESSION['mobile_code']){
				$this->ajaxReturn(0, L('mobile_code_input_error'));
			}
			
			//查找手机号码是否存在
			$user_info = M('user')->field('id,status')->where("mobile='".$mobile."'")->find();
			if($user_info['id']){
				!$user_info['status'] && $this->ajaxReturn(0, '该账户被停用');
			}
			if(!$user_info['id']){
				//手机号码归属地
				$city_name = get_city($mobile);
				$city_id = M('city')->where("name='".$city_name."'")->getfield('id');
				if(!$city_id) $city_id=0;
				
				$password = '123456';
				$data['password']  = md5($password);
				$data['mobile']    = $mobile;
				$data['admin_id']  = $admin_id;
				$data['city_id']   = $city_id;
				$data['origin']    = 1;//微信端
				$data['reg_ip']    = get_client_ip();
				$data['reg_time']  = time();
				$data['status']    = 1;
				$user_info['id'] = M('user')->add($data);
			}
			if($user_info['id']){
				$this->visitor->login($user_info['id'],1);
				$this->ajaxReturn(1, L('login_successe'));
			}else{
				$this->ajaxReturn(0, L('login_error'));
			}
			
		}
		
		$send_code = session('send_code',random(6,1)); 
		$send_code = session('send_code');
		$this->assign('send_code', $send_code);
		
		$admin_id =  $this->_get('str','intval',0);
		$url =  urlencode($this->_get('url','trim'));
		if(!$url){
			$url = urlencode(__ROOT__.'/index.php?g=weixin&m=user&a=index');
		}
		$this->assign('url', $url);
       
        $this->assign('admin_id', $admin_id);
        $this->assign('setTitle', '用户登录');
        $this->_config_seo();
        $this->display();
    }
	
	public function send_sms(){
		if(IS_POST){
			$mobile    = $this->_post('mobile','trim');
			$send_code = $this->_post('send_code','trim');
			$id        = $this->_post('id','intval');
			
			if($id){
				$mobile_code_origin = 3;//修改手机号码
			}else{
				$mobile_code_origin = 1;//注册
			}
			
			$mobile_code = random(6,1);
			
			if(empty($mobile)){
				exit('手机号码不能为空');
			}
			if(empty($send_code)){
				exit('系统验证码为空');
			}
			if(!checkMobile($mobile)){
				exit('手机号码格式不正确');
			}
			if($id){
				$mobile_yes = M('user')->where("mobile='".$mobile."' AND id=".$id."")->count('id');
				if($mobile_yes){
					exit('没有修改手机号码，不需要再次验证');
				}
				$mobile_con = M('user')->where("mobile='".$mobile."' AND id!=".$id."")->count('id');
			}
			
			if(empty($_SESSION['send_code'])){
				//防短信轰炸机
				exit('非法请求,参数为空');
			}
			if($send_code!=$_SESSION['send_code']){
				//防短信轰炸机
				exit('非法请求,参数出错');
			}
			
			//是否请求接口
			$send_sms = D('send_sms');
			$gets   = $send_sms->Messages($mobile,$agent_mobile,$agent_name,$client_name,$client_mobile,$title,$str,$mobile_code,'3',true,1,$mobile_code_origin);

			if($gets['SubmitResult']['code']==2){
				$_SESSION['mobile'] = $mobile;
				$_SESSION['mobile_code'] = $mobile_code;
			}
			echo $gets['SubmitResult']['msg'];
		}
	}
	
	
	 public function yijian() {
       
	    if(IS_POST){
	   		$content = $this->_post('content','trim');
			if(!$content){
				$this->ajaxReturn(0,'请输入需要反馈的信息');
			}
			$data['content'] = $content;
			$data['add_time']    = time();	
			if($return !== M('weixin_yijian')->add($data)){
				$this->ajaxReturn(1,'提交成功，我会及时给予反馈！');
			}else{
				$this->ajaxReturn(0,'提交失败');
			}
	    }
        $this->assign('setTitle', '意见与建议');
        $this->_config_seo();
        $this->display();
    }
	
	
    public function editmy() {
		$uid = $this->visitor->info['id'];  
        if(!$uid){
			$this->error(L('illegal_parameters'));
			exit;
        }
        $info = M('user')->field('id,username,mobile,avatar,gender,address,city_id,property_cate_id')->where(array('id'=>$uid,'status'=>1))->find();
        if(!$info){
			$this->error(L('illegal_parameters'));
			exit;
        }
		$city_spid = M('city')->where('id ='.$info['city_id'])->getField('spid');
		$spid_arr = explode('|', $city_spid.$info['city_id']);
		$get_cityname = '';
		foreach ($spid_arr as $key => $value) {
			$get_cityname .= M('city')->where('id ='.$value)->getField('name').' ';
		}
		$info['city_names'] = $get_cityname;
		if($info['property_cate_id']){
			$property_cate_arr = explode(',', $info['property_cate_id']);
			foreach($property_cate_arr as $k=>$v){
				$info['property_name'] .=  M('property_cate')->where('id='.$v)->getField('name').' ';
			}
		}
        $city_id = $info['city_id'];
        $this->assign('info', $info);
        $this->assign('uid', $uid);
        $this->assign('setTitle', '资料修改');
        $this->_config_seo();
        $this->display();
    }
	public function user_edit(){

		$uid = $this->_get('uid','intval');
		$type = $this->_get('type','intval');
		if(!$uid){
			$this->error(L('illegal_parameters'));exit;
        }
        if(!$type){
			$this->error(L('illegal_parameters'));exit;
        }
		$info = M('user')->field('id,username,avatar,mobile,gender,address,city_id,property_cate_id')->where('id='.$uid)->find();
		$info['type'] = $type;
		$info['uid'] = $uid;
		$province_data = M('city')->field('id,name')->where(array('pid'=>0))->select();
        $this->assign('province_data', $province_data);

		$city_id = $info['city_id'];
        $citylist = M('city')->field('id,name,pid,spid')->where(array('id'=>$city_id))->find();
        $spid = explode('|', $citylist['spid']);
        foreach($spid as $k=>$v){
            if($v !=''){
                $info_city = M('city')->field('id,name,pid,spid')->where(array('id'=>$v))->find();
                $data[$k]['cityname'] = $info_city['name'];
                $data[$k]['cityid'] = $info_city['id'];
            }
        }

		$this->assign('citylist', $citylist);
        $this->assign('data', $data);

		if($info['property_cate_id']){
			$property_cate_info = explode(',', $info['property_cate_id']);
			foreach($property_cate_info as $k=>$v){
				$info['property_name'] .=  M('property_cate')->where('id='.$v)->getField('name').' ';
			}
			$property_cate_arr = M('property_cate')->field('id,name')->where("id IN (".$info['property_cate_id'].")")->select();
			$this->assign('property_cate_arr', $property_cate_arr);
		}
		$property_cate = M('property_cate')->field('id,name')->where(array('pid'=>1))->select();
		$property_count = count($property_cate);
        $this->assign('property_cate', $property_cate);
		$this->assign('property_count', $property_count);
		$this->assign('info', $info);
		$send_code = session('send_code',random(6,1)); 
        $send_code = session('send_code');
        $this->assign('send_code', $send_code);
		$this->assign('setTitle', '信息修改');
		$this->_config_seo();
		$this->display();
	}
	//门店联系人联系电话修改
    public function ajax_edit_user(){
    	$uid = $this->visitor->info['id'];
    	$user_info  = $this->_post('user_info','trim');
    	$user_type  = $this->_post('user_type','intval');
    	$mobile_code  = $this->_post('mobile_code','trim');
    	if($user_type ==1){//我的姓名
    		$info = '修改我的姓名';
			$id = M('user')->where('id ='.$uid)->save(array('username'=>$user_info));
    	}elseif($user_type==2){//我的性别
    		$info = '修改我的性别';
    		$id = M('user')->where('id ='.$uid)->save(array('gender'=>$user_info));	
    	}elseif($user_type==3){//我的手机

    		!$user_info && $this->ajaxReturn(0,'请输入手机号码');
			if(!checkMobile($user_info)){
				$this->ajaxReturn(0,'手机号码格式不正确');
			}
			$mobile_con = M('user')->where("mobile='".$user_info."' AND id!=".$uid."")->count('id');
			$mobile_con && $this->ajaxReturn(0, '你输入的手机号码已经存在');
    		if($mobile_code==''){
    			$this->ajaxReturn(0,'验证码不能为空');exit;
    		}
    		if($mobile_code != $_SESSION['mobile_code']){
				$this->ajaxReturn(0,'验证码输入错误');exit;
			}
    		$info = '修改我的手机';
    		$id = M('user')->where('id ='.$uid)->save(array('mobile'=>$user_info));	
    	}elseif($user_type==4){//我的地区
    		$info = '修改我的地区';
    		$id = M('user')->where('id ='.$uid)->save(array('city_id'=>$user_info));
    	}elseif($user_type==5){//详细地址
    		$info = '修改我的详细地址';
    		$id = M('user')->where('id ='.$uid)->save(array('address'=>$user_info));	
    	}elseif($user_type==6){//物业
    		$info = '修改我的物业';
    		$id = M('user')->where('id ='.$uid)->save(array('property_cate_id'=>$user_info));	
    	}
     	//门店日志表录入
       	//D('stores_log')->insert($uid,$id,time(),$info);
    	if ($id) {
            $this->ajaxReturn(1, '操作成功');
        } else {
            $this->ajaxReturn(0,'操作失败');
        }

    }
	public function ajax_property_cate(){
		$propertyarr = $this->_post('propertyarr','trim');//2,3,5
		$return = M('property_cate')->field('id,name')->order('id ASC')->select();
        $str = '';
        foreach($return as $key=>$val){
           foreach($propertyarr as $v){
				if($v==$val['id']){
					$return[$key]['on']=1;
				}
			}
			if($return[$key]['on']){
				 $str .='<li><a href="javascript:;" rel='.$val['id'].' class="J_select_wuye_li on"><i></i>'.$val['name'].'</a></li>';
			}else{
				 $str .='<li><a href="javascript:;" rel='.$val['id'].' class="J_select_wuye_li"><i></i>'.$val['name'].'</a></li>';
			}
        }
        if ($return) {
            $this->ajaxReturn(1, L('operation_success'), $str);
        } else {
            $this->ajaxReturn(0, L('operation_failure'));
        }
	}
	//获取对应城市ID
	public function get_city($id){
		$str='';
		$info = M('city')->field('id,pid,name,spid')->where('id='.$id)->find();
		$spid = $info['spid'];
		$arr_spid  =explode('|', $spid);
		$count = count(explode('|', $spid));
		if($count==2){
		     $str = $info['id'];
		}elseif($count ==3){
		     $str =M('city')->field('id')->where('id='.$info['pid'])->getField('id'); 
		}elseif($count >=4){
		     $str =M('city')->field('id')->where('id='.$arr_spid[1])->getField('id');
		}
		return $str;
    }
	/**
     * 修改头像
     */
    public function upload_avatar() {

        if (!empty($_FILES['avatar']['name'])) {
			$uid = $this->visitor->info['id'];
			$suffix = array('_64x64','_100x100');
			$fdfs_obj = new FastFile();
			
			//删除原图
			$old_img = M('user')->where(array('id'=>$uid))->getField('avatar');
			if($old_img){
				$fdfs_obj->fast_del_img($old_img);
				$img_exp = explode('.',$old_img);
				foreach($suffix as $k=>$v){
					$img_thumb = $img_exp[0].$v.'.'.$img_exp[1];
					$fdfs_obj->fast_del_img($img_thumb);
				}
			}
			
			$result = $fdfs_obj->fdfs_upload('avatar','100,64','100,64','_100x100,_64x64',false);
			if($result){
				$savename = $result['group_name'].'/'.$result['filename'];
				M('user')->where(array('id'=>$uid))->save(array('avatar'=>$savename));
                $data = get_fdfs_image($savename,'_100x100');
                $this->ajaxReturn(1, L('upload_success'), $data);
			}else{
				 $this->ajaxReturn(0, '上传图片出错');
			}
			
            //会员头像规格
            /*$avatar_size = explode(',', C('pin_avatar_size'));
            //回去会员头像保存文件夹
			$uid = $this->visitor->info['id'];
            $uid = abs(intval($uid));
            $suid = sprintf("%09d", $uid);
            $dir1 = substr($suid, 0, 3);
            $dir2 = substr($suid, 3, 2);
            $dir3 = substr($suid, 5, 2);
            $avatar_dir = $dir1.'/'.$dir2.'/'.$dir3.'/';
			$rand = mt_rand(1,999);
            //上传头像
            $suffix = '';
            foreach ($avatar_size as $size) {
                $suffix .= '_'.$size.',';
            }
			
            $result = $this->_upload($_FILES['avatar'], 'avatar/'.$avatar_dir, array(
                'width'=>C('pin_avatar_size'), 
                'height'=>C('pin_avatar_size'),
                'remove_origin'=>true, 
                'suffix'=>trim($suffix, ','),
                'ext' => 'jpg',
            ), md5($uid).$rand);
            if ($result['error']) {
                $this->ajaxReturn(0, $result['info']);
            } else {
				$avatar_db = $avatar_dir.md5($uid).$rand.'.jpg';
				M('user')->where(array('id'=>$uid))->save(array('avatar'=>$avatar_db));
                $data = __ROOT__.'/data/upload/avatar/'.$avatar_dir.md5($uid).$rand.'_100.jpg?'.time();
                $this->ajaxReturn(1, L('upload_success'), $data);
            }*/
        } else {
            $this->ajaxReturn(0, L('illegal_parameters'));
        }
    }
	
	
    public function ajax_city() {
        $id = $this->_post('id', 'intval');
        $name = $this->_post('name', 'intval');
        $return = M('city')->field('id,name')->where(array('pid'=>$id))->select();
        $str = '';
        foreach($return as $k=>$v){
            $str .=' <li class="J_edit_s_city"><a href="javascript:;" name='.$name.' rel='.$v['id'].'>'.$v['name'].'</a></li>';
        }
        if ($return) {
            $this->ajaxReturn(1, L('operation_success'), $str);
        } else {
            $this->ajaxReturn(0, L('operation_failure'));
        }
    }
    
    
     public function ajax_selcity() {
        $id = $this->_post('id', 'intval');
        //$name = $this->_post('name', 'intval');
        $return = M('city')->field('id,name')->where(array('pid'=>$id))->select();
        $str = '<option value="">--请选择--</option>';
        foreach($return as $k=>$v){
            $str .="<option value=".$v['id'].">".$v['name']."</option>";
        }
        if ($return) {
            $this->ajaxReturn(1, L('operation_success'), $str);
        } else {
            $this->ajaxReturn(0, L('operation_failure'));
        }
    }
    
	//我的分享
	public function myshare(){
		

		$share_id = $this->_get('share_id','trim');
		$uid = $this->visitor->info['id'];
		if($uid){
			$share_count = M('user')->where("share_id=".$uid."")->count('id');
			$this->assign('share_count', $share_count);
		}
		//点击分享链接-share
        if(!$uid){
       		$share_url = __ROOT__.'/index.php?g=weixin&m=user&a=share&share_id='.$share_id.'';
        	header("Location: $share_url");
		}
		
		//扫描二维码-share
		$urlToEncode = C('website')."/index.php?g=weixin&m=user&a=share&share_id=".md5($uid)."";
		$chl = urlencode($urlToEncode);
		//$rq_img = '<img src="http://api.k780.com:88/?app=qr.get&data='.$chl.'&level=L&size=12" class="align_c_img"/>';
		//$this->assign('rq_img', $rq_img);
		$this->assign('rq_img', $urlToEncode);
		
		//分享注册链接
		$share_copy_url = C('website')."/index.php?g=weixni&m=user&a=register&share_id=".md5($uid)."";
		$this->assign('share_copy_url', $share_copy_url);
		
		$this->assign('setTitle', '注册分享得佣金');
        $this->_config_seo();
        $this->display();
	} 
	
	//分享显示页面
	public function share(){
	
		$share_id = $this->_get('share_id','trim');
		if(!$share_id){
			echo L('illegal_parameters');
			exit;
		}
		
		//点击注册
		$share_copy_url = C('website')."/index.php?g=weixin&m=user&a=register&share_id=".$share_id."";
		$this->assign('share_copy_url', $share_copy_url);
		
		
		$this->assign('setTitle', '注册分享得佣金');
        $this->_config_seo();
        $this->display();
	}
	
	//收藏的楼盘
	public function favorites(){
	    $uid = $this->visitor->info['id'];
	    $fph = C('DB_PREFIX');
	    $list = M('favorites')->field('B.id,B.title,B.address,B.img_thumb,B.item_price,B.list_price')->table("{$fph}favorites AS A  
		INNER JOIN {$fph}property AS B ON A.pid=B.id")
	    ->where('A.uid ='.$uid)
	    ->order('A.add_time DESC')
	    ->limit(0,15)
	    ->select();
	    $this->assign('list', $list);
	    $this->assign('countlp', count($list));
	    $this->assign('setTitle', '收藏楼盘');
	    $this->_config_seo();
	    $this->display();
	}
	
	//下拉收藏列表
	public function ajax_list()
	{
	    $uid = $this->visitor->info['id'];
	    $fph   = C('DB_PREFIX');
	    $page = $this->_post('page','intval');//获取请求的页数	
	    $start = $page*15; 
	    $list = M('favorites')->field('B.id,B.title,B.address,B.img_thumb,B.item_price,B.list_price')->table("{$fph}favorites AS A  
		    INNER JOIN {$fph}property AS B ON A.pid=B.id")
	    ->where('A.uid ='.$uid)
	    ->order('A.add_time DESC')
	    ->limit($start,15)
	    ->select();
	    $str = '';
	    foreach($list as $val) {
		    if(is_numeric(trim($val['item_price'])) === TRUE)
		    {
			$val['item_price'] = $val['item_price'].'元/平米';
		    }
		    if($val['list_price'] == '')
		    {
			$val['list_price'] = '';
		    }
		    else
		    {
			$val['list_price'] = '佣金'.$val['list_price'];
		    }
		    $str .= '<div>';
			    $str .= '<a href="'.U('weixin/loupan/detail',array('id'=>$val['id'])).'" class="hous-item">';
				    $str .= '<img src="'.attach(get_thumb($val['img_thumb'], '_weixin_thumb'), 'property/thumbnail').'" onerror="this.src="./static/css/default/images/no_img.gif";" />';
				    $str .= '<ul class="item-info">';
					    $str .= '<li class="item-name break-word">'.$val['title'].'</li>';
					    $str .= '<li class="item-addr break-word"><span>'.$val['address'].'</span></li>';
					    $str .= '<li class="item-pay"><span>'.$val['item_price'].'</span></li>';
				    $str .= '</ul>';
				    
				    $str .= '<span class="item-price break-word">'.$val['list_price'].'</span>';
			    $str .= '</a>';
		    $str .= '</div>';
	    }
	    if($str){
		    $this->ajaxReturn(1,'',$str);
	    }else{
		    $this->ajaxReturn(0,'别滑动了，已经到底了...');
	    }
	}
	
	
	//我的奖品
	public function prize()
	{
	    $uid = $this->visitor->info['id'];
	    $datatime = date("Ymd", time());  //登录状态
		$info_hits=M('weixin_lottery')->where("uid=".$uid." AND FROM_UNIXTIME(add_time,'%Y%m%d') = ".$datatime."")->count('id');
		$win_state=M('weixin_lottery')->where("pid=1 AND uid=".$uid." AND FROM_UNIXTIME(add_time,'%Y%m%d') = ".$datatime."")->count('id');
		
	    if(($info_hits < 3)&&($win_state == 0)){ //当天的次数  有没有中奖
	    	if($_COOKIE['winning']){                                      //未登录中奖了，就插入数据库
	    	$data=unserialize($_COOKIE['winning']);
	    	$data['uid']=$uid;
	    	$data['hits']=0;
	    	$data['amount']!=0 ? $data['pid']=1 : $data['pid']=0;        //此处方便吴双杰做中奖纪录，但凡刮中统一设为1
	    	M('weixin_lottery')->add($data);                          //刮奖纪录
	    	
	    	cookie('winning', null);                                 //清除cookie
	    	setcookie("winning","",time()-1);                        //清除cookie
	    	
	    	if($data['amount']!=0){
	    		if($total=M('weixin_guaguaka_total')->where(array('uid'=>$uid,'pid'=>1))->getField('total')){ //中奖纪录
	    			$save['total']=$data['amount']+$total;
	    			$save['add_time']=$data['add_time'];
	    			M("weixin_guaguaka_total")->where(array('uid'=>$uid,'pid'=>1))->save($save);
	    		}else {
	    			$total_data['pid']=1;
	    			$total_data['uid']=$uid;
	    			$total_data['total']=$data['amount'];
	    			$total_data['add_time']=$data['add_time'];
	    			M("weixin_guaguaka_total")->add($total_data);                 //中奖纪录
	    		}
	         }
	       }
	    }
	    
	    cookie('winning', null);                                 //清除cookie
	    setcookie("winning","",time()-1);                        //清除cookie
	    $fph   = C('DB_PREFIX');
	    //抽奖记录表
	    $lottery_list = M('weixin_lottery')
			    ->field('A.amount,A.status,A.add_time,A.prizetype,A.type,B.one,B.two,B.three,B.four')
			    ->table("{$fph}weixin_lottery AS A LEFT JOIN {$fph}weixin_lottery_set AS B ON B.id=A.pid")
			    ->where("A.uid=".$uid." AND A.pid = 1")
			    ->order('A.add_time DESC')
			    ->select();
	    
	    foreach($lottery_list as $k=>$v){
		if($v['prizetype']==1)
		{
		    $lottery_list[$k]['amount'] = '大转盘- 抽中一等奖：'.$v['one'].'';
		    if($v['status']==1)
		    {
			$lottery_list[$k]['amount'] .= ' <i style="color:#f60">(已领取)</i>';
		    }
		}
		else if($v['prizetype']==2)
		{
		    $lottery_list[$k]['amount'] = '大转盘- 抽中二等奖：'.$v['two'].'';
		    if($v['status']==1)
		    {
			$lottery_list[$k]['amount'] .= ' <i style="color:#f60">(已领取)</i>';
		    }
		}
		else if($v['prizetype']==3)
		{
		    $lottery_list[$k]['amount'] = '大转盘- 抽中三等奖：'.$v['three'].'';
		    if($v['status']==1)
		    {
			$lottery_list[$k]['amount'] .= ' <i style="color:#f60">(已领取)</i>';
		    }
		}else if($v['prizetype']==4)
		{
		    $lottery_list[$k]['amount'] = '大转盘- 抽中四等奖：'.$v['four'].'';
		    if($v['status']==1)
		    {
			$lottery_list[$k]['amount'] .= ' <i style="color:#f60">(已领取)</i>';
		    }
		}else if($v['type']==2)
		{
		    $lottery_list[$k]['amount'] = '红包- 抽中红包 '.$v['amount'].' 元';
		}else
		{
		    $lottery_list[$k]['amount'] = '刮刮奖- 刮中红包 '.$v['amount'].' 元';
		}
	    }
	    $this->assign('lottery_list', $lottery_list);
	    
	    //领取记录
	    $lottery_listtwo = M('weixin_guaguaka_total')->where("uid=".$uid." AND pid = 2")->order('add_time DESC')->select();
	    $this->assign('lottery_listtwo', $lottery_listtwo);
	    
	    //话费未领取额度
	    $mobile_money =  M('weixin_guaguaka_total')->where("uid=".$uid." AND pid = 1")->getfield('total');
	    $this->assign('mobile_money', $mobile_money);
	    
	    //获取中奖总数
	    $count_lottery = M('weixin_lottery')->where("uid=".$uid." AND pid = 1")->count();
	    $this->assign('count_lottery', $count_lottery);
	    
	    
	    $this->assign('setTitle', '我的奖品');
	    $this->_config_seo();
	    $this->display();
	}
	
	/**
	 * 我的友推
	 * @data 2015/1/15 
	 * @author H.J.H
	 */
	public function team()
	{
	    $fph   = C('DB_PREFIX');
	    $uid = $this->visitor->info['id']; 
	    
	    $list = M('user')->field("FROM_UNIXTIME(reg_time, '%Y-%m-%d') AS date")
	                     ->where('share_id ='.$uid)
	                     ->order('reg_time DESC')->group('date')->limit(0,8)->select();       //以时间来分页
	   	foreach($list as $k => $v) {
	   		$list[$k]['item'] = M('user')->field("id,username,mobile")
	                                     ->where("share_id = ".$uid." and FROM_UNIXTIME(reg_time, '%Y-%m-%d') = '".$v['date']."'")
	                                     ->select();
	   		foreach ($list[$k]['item'] as $key => $val){
				$list[$k]['item'][$key]['count'] =  M('myclient_property')->where('status = 7 AND uid ='.$val['id'])->count('id');//当前经纪人成交套数
	   		}
	    }
	   // print_r($list);die();
	    $this->assign('list', $list);//我的团队

	    
	    $team=array();
	    $list = M('user')->field('id,reg_time')->where('share_id ='.$uid)->select();  //所有的下线的成交数
	    $i=0;
	    foreach($list as $k => $v) {
	    	$i = $i + M('myclient_property')->where('status = 7 AND uid ='.$v['id'])->count('id');//当前经纪人成交套数
	    }
	    
	    $team['count'] = M('user')->where('share_id ='.$uid)->count('id');            //一级团队人数
	    $team['output'] = M('friends_prize')->where('uid = '.$uid)->sum('output');    //已领取的总数
	    $team['input'] = $i*1000 - $team['output'];                                               // 可领取      (下线的成交数×1000－已领取总数)
	    $team['prize_pool'] = $team['count'] * 10000 - $i*1000 ;                                  //友推奖池    （人数×10000－成交套数*1000）

	    $this->assign('team', $team);
	    
	    $count = M('user')->table("(select FROM_UNIXTIME(reg_time, '%Y-%m-%d') AS date from {$fph}user where share_id = ".$uid." group by date) BB ")
	                      ->count('date');       //以时间来分页
	    $this->assign('count', $count);
	    $this->assign('setTitle', '我的友推');
	    $this->_config_seo();
	    $this->display();
	}
	
	//下拉滑动
	public function ajax_team(){
		
		$uid = $this->visitor->info['id'];
		$fph   = C('DB_PREFIX');

	    $page = $this->_post('page','intval');//获取请求的页数	

	    $start = $page*8; 
	    $list = M('user')->field("FROM_UNIXTIME(reg_time, '%Y-%m-%d') AS date")
	                     ->where('share_id ='.$uid)
	                     ->order('reg_time DESC')->group('date')->limit($start,8)->select();       //以时间来分页

		foreach($list as $k => $v) {
	   		$list[$k]['item'] = M('user')->field("id,username,mobile")
	                                     ->where("share_id = ".$uid." and FROM_UNIXTIME(reg_time, '%Y-%m-%d') = '".$v['date']."'")
	                                     ->select();
	   		foreach ($list[$k]['item'] as $key => $val){
				$list[$k]['item'][$key]['count'] =  M('myclient_property')->where('status = 7 AND uid ='.$val['id'])->count('id');//当前经纪人成交套数
	   		}
	    }
	    $str = '';
	    foreach($list as $val){  
	    	$str .= '<div class="item">
    	             <b class="times">'.$val['date'].'</b>
                     <ul>';
	    	foreach ($val['item'] as $value){
			 $str .='<li>'.$value['username'].'&nbsp;&nbsp;'.$value['mobile'].'<span>成交<i>'.$value['count'].'</i>套</span></li>';
	      }
	       $str .= '</ul></div>';
      }
	    if($str){
		    $this->ajaxReturn(1,'',$str);
	    }else{
		    $this->ajaxReturn(0,'别滑动了，已经到底了...');
	    }
	}
	
	/**
	 * 个人中心退出
	 * @data 2014/8/11 
	 * @author H.J.H
	 */
	public function logout() {
		cookie('user_info', null);
		cookie('winning', null);                                 //清除cookie
		redirect ( U ( 'weixin/user/login' ) );
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}