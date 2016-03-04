<?php
class caseAction extends weixin_userbaseAction {
	public function _initialize() {
		parent::_initialize();
		$this->_mod_c = D('company');//公司
		$this->_mod_s = D('stores');//门店
	}
	public function index(){
		$uid = $this->visitor->info['id'];
		$search = array();
		$where = ' status = 1 AND type = 1 ';
		$fph = C('DB_PREFIX');
		$select_city = $this->_get('select_city','intval');
		$select_name = $this->_get('select_name','trim');
		$search_title = $this->_get('search_title','trim');
		$str_pro = '';
		if($select_city){
		    $search['select_city'] = $select_city;
		    $search['select_name'] = $select_name;
		    $select_city_id = $select_city;
		    if($select_city==803){
		    	$str_pro = ' OR id=802 ';
		    }
		}else{
			//获取当前所在城市 
			vendor('Ip.IpLocation', '', '.class.php');
			$Ip = new IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
			$ip_addr = get_client_ip();
		    //F($ip_addr,null);
		    //获取缓存数据
		    $location=F($ip_addr);
		    if(!$location){
		    	F($ip_addr,$Ip->getlocation($ip_addr));
		    	$location=F($ip_addr);
		    }
			$city_name = preg_replace("/^[^省]*省/",'',$location['country']);
			$city_info = M('city')->field('id,name')->where("name like '%".$city_name."%'")->find();
			if($city_info['id']!=803 && $city_info['id']!=22 && $city_info['id']!=859 && $city_info['id']!=867 && $city_info['id']!=982 && $city_info['id']!=1533 && $city_info['id']!=824){
				$search['select_name'] = '上海市';
			    $search['select_city'] = 803;
			    $select_city_id = 803;
			    $str_pro = ' OR id=802 ';
			}else{
				$search['select_city'] = $city_info['id'];
				$search['select_name'] = $city_info['name'];
				$select_city_id = $city_info['id'];
			}
		}
		if($search_title){
		    $where .= ' AND name like "%'.$search_title.'%"';
		    $search['title'] = $search_title;
		}
		//是否为内部
		$search['internal'] = M('user')->where(array('id'=>$uid, 'status'=>1))->getField('internal');
		//筛选数据
		//区域
		$search_shai_city = $this->_get('search_shai_city','intval');
		if($search_shai_city){
		    $select_city_id = $search_shai_city;
		    $search['search_shai_city'] = $search_shai_city;
		}
		//板块
		$search_ban_city = $this->_get('search_ban_city','intval');
		$search['search_ban_city_name'] ='';
		if($search_ban_city){
		    $select_city_id = $search_ban_city;
		    $search['search_ban_city'] = $search_ban_city;
		    $search['search_ban_city_name'] =  $this->_get('search_ban_city_name','trim');
		}
		//案场渠道
        $search_status_name =  $this->_get('search_status_name','intval');
        if($search_status_name){
        	//1 全部 2 我创建案场渠道
            if($search_status_name==2){
               $where .= " AND uid= $uid";
            }
            $search['search_status_name'] = $search_status_name;
        }
		$where .=' AND city_id in(select id from fph_city where id = '.$select_city_id.$str_pro.' or spid RLIKE "[[:<:]]'.$select_city_id.'[[:>:]]")';

		$list = $this->_mod_s->where($where)->order(' id DESC ')->limit(0,6)->select();
		foreach($list as $key=>$value){
			if($value['service']!=0){
				$user_info = M('user')->field('username,mobile')->where('id='.$value['service'])->find();
				$list[$key]['username'] = $user_info['username'];
				$list[$key]['mobile'] = $user_info['mobile'];
			}
			$list[$key]['user_count'] = M('user')->where('stores_id='.$value['id'])->count('id');
			//$store_count += $list[$key]['user_count'];
		}
		$count = count($this->_mod_s->where($where)->select());
		//所在城市 门店所有成员数
		$store_count = 0;
		$list_tot = $this->_mod_s->field('id')->where($where)->select();
		foreach($list_tot as $k_t=>$v_t){
			$store_count += M('user')->where('stores_id='.$v_t['id'])->count('id');
		}
		//区域
		$shailist_city = M('city')->where('pid ='.$search['select_city'])->select();
		//区域显示
		$city = $this->_mod_s->field('city_id')->where('city_id !=0 ')->group('city_id')->select();
		$city_id = '';
		$id_str =array();
		$str = '';
		foreach($city as $k=>$v){
		   $str  = $this->get_city($v['city_id']);
           if($str){
              $id_str[]=$str;
           }
		}
		$city_id = array_unique($id_str);
		$citylist = M('city')->where('id in('.implode(',',$city_id).')')->order('FIELD(id ,'.$search['select_city'].') DESC ')->select();
		$this->assign('shailist_city', $shailist_city);
		$this->assign('search', $search);
		$this->assign('count', $count);
		$this->assign('store_count', $store_count);
		$this->assign('list', $list);
		$this->assign('countlp', count($list));
		$this->assign('citylist', $citylist);
		$this->assign('setTitle', '一手房案场');
		$this->_config_seo();
		$this->display();
	}

	public function ajax_store_index(){
		$uid = $this->visitor->info['id'];
		$page = $this->_post('page','intval');
	    $search_shai_city = $this->_post('search_shai_city','intval');
	    $select_city = $this->_post('select_city','intval');
	    $search_title = $this->_post('search_title','trim');
	    $select_name = $this->_post('select_name','trim');
	    $search_ban_city = $this->_post('search_ban_city','intval');
	    $search_ban_city_name = $this->_post('search_ban_city_name','trim');
	    $search_status_name =  $this->_post('search_status_name','intval');
	    $start = $page*6;
		$search = array();
		$where = ' status = 1 AND type = 1 ';
		$fph = C('DB_PREFIX');
		$str_pro = '';
		if($select_city){
		    $search['select_city'] = $select_city;
		    $search['select_name'] = $select_name;
		    $select_city_id = $select_city;
		    if($select_city==803){
		    	$str_pro = ' OR id=802 ';
		    }
		}else{
			//获取当前所在城市 
			vendor('Ip.IpLocation', '', '.class.php');
			$Ip = new IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
			$ip_addr = get_client_ip();
		    //获取缓存数据
		    $location=F($ip_addr);
		    if(!$location){
		    	F($ip_addr,$Ip->getlocation($ip_addr));
		    	$location=F($ip_addr);
		    }
			$city_name = preg_replace("/^[^省]*省/",'',$location['country']);
			$city_info = M('city')->field('id,name')->where("name like '%".$city_name."%'")->find();
			if($city_info['id']!=803 && $city_info['id']!=22 && $city_info['id']!=859 && $city_info['id']!=867 && $city_info['id']!=982 && $city_info['id']!=1533 && $city_info['id']!=824){
				$search['select_name'] = '上海市';
			    $search['select_city'] = 803;
			    $select_city_id = 803;
			    $str_pro = ' OR id=802 ';
			}else{
				$search['select_city'] = $city_info['id'];
				$search['select_name'] = $city_info['name'];
				$select_city_id = $city_info['id'];
			}

		}
		if($search_title){
		    $where .= ' AND name like "%'.$search_title.'%"';
		    $search['title'] = $search_title;
		}
		//是否为内部
		$search['internal'] = M('user')->where(array('id'=>$uid, 'status'=>1))->getField('internal');
		//筛选数据
		//区域
		if($search_shai_city){
		    $select_city_id = $search_shai_city;
		    $search['search_shai_city'] = $search_shai_city;
		}
		//板块
		$search['search_ban_city_name'] ='';
		if($search_ban_city){
		    $select_city_id = $search_ban_city;
		    $search['search_ban_city'] = $search_ban_city;
		    $search['search_ban_city_name'] = $search_ban_city_name;
		}
		//案场渠道
        if($search_status_name){
        	//1 全部 2 我的门店
            if($search_status_name==2){
               $where .= " AND uid= $uid";
            }
            $search['search_status_name'] = $search_status_name;
        }
		$where .=' AND city_id in(select id from fph_city where id = '.$select_city_id.$str_pro.' or spid RLIKE "[[:<:]]'.$select_city_id.'[[:>:]]")';

		$list = $this->_mod_s->where($where)->order(' id DESC ')->limit($start,6)->select();
		$count = count($this->_mod_s->where($where)->select());
		//区域
		$shailist_city = M('city')->where('pid ='.$search['select_city'])->select();
		//区域显示
		$city = $this->_mod_s->field('city_id')->where('city_id !=0 ')->group('city_id')->select();
		$city_id = '';
		$id_str =array();
		$str = '';
		foreach($city as $k=>$v){
		   $str  = $this->get_city($v['city_id']);
           if($str){
              $id_str[]=$str;
           }
		}
		$city_id = array_unique($id_str);
		$citylist = M('city')->where('id in('.implode(',',$city_id).')')->order('FIELD(id ,'.$search['select_city'].') DESC ')->select();
		$str = '';

        foreach($list as $k=>$v){
        	if($v['service']!=0){
				$user_info = M('user')->field('username,mobile')->where('id='.$v['service'])->find();
				$list[$k]['username'] = $user_info['username'];
				$list[$k]['mobile'] = $user_info['mobile'];
			}
			$list[$k]['user_count'] = M('user')->where('stores_id='.$v['id'])->count('id');
        	$str .='<li><a href="'.U('weixin/case/detail',array('id'=>$v['id'])).'"><h2>'.$v['name'].'<b class="LBL_STATUS sc_11">邀请码:'.$v['code_id'].'</b></h2><span class="address">'.$v['address'].'</span><span class="store_code">服务专员:'.$list[$k]['username'].' '.$list[$k]['mobile'].'</span><span class="store_code_r">成员'.$list[$k]['user_count'].'人</span><i></i></a></li>';
        }
        if($str){
		    $this->ajaxReturn(1,'',$str);
	    }else{
		    $this->ajaxReturn(0,'别滑动了，已经到底了...');
	    }
	}
	public function add(){
		$uid = $this->visitor->info['id'];
		if(IS_POST){
			$name    = $this->_post('name','trim');
			$city_id = $this->_post('city_id','intval');
			$address = $this->_post('address','trim');
			!$name && $this->ajaxReturn(0,'请输入案场名称');
			!$city_id && $this->ajaxReturn(0,'请选择区域板块');
			!$address && $this->ajaxReturn(0,'请输入项目地址');
			
			//用户信息
			$user_info = D('user')->user_info($uid);
			
			$data['uid']         = $uid;
			$data['pid']         = 420;
			$data['type']        = 1;
			$data['name']        = $name;
			$data['city_id']     = $city_id;
			$data['address']     = $address;
			$data['contact']     = $user_info['username'];
			$data['contact_tel'] = $user_info['mobile'];
			$data['service']     = $uid;
			$data['add_time']    = time();
			$data['status']      = 1;
			
			if($id = M('stores')->add($data)){
				$this->ajaxReturn(1,'案场添加成功',$id);
			}else{
				$this->ajaxReturn(0,'案场添加失败');
			}
		}
		
		
		//读取城市
		$city = M('property')->field('city_id')->where('status = 1 and city_id !=0')->group('city_id')->select();
		$city_id = '';
		$id_str =array();
		$str = '';
		foreach($city as $k=>$v){
		   $str  = $this->get_city($v['city_id']);
           if($str){
               $id_str[]=$str;
           }
		}
		$city_id = array_unique($id_str);
		$citylist = M('city')->where('id in('.implode(',',$city_id).')')->order('id DESC')->select();
        $this->assign('citylist', $citylist);
		
		$this->assign('setTitle', '新增案场');
		$this->_config_seo();
		$this->display();
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
	
	//案场详情
	public function detail(){
		
		if(IS_POST){
			$username  = $this->_post('username','trim');
			$mobile    = $this->_post('mobile','trim');
			$stores_id = $this->_post('stores_id','intval');
			
			!$stores_id && $this->ajaxReturn(0, '系统参数出差');
			!$username && $this->ajaxReturn(0, '请输入姓名');
			!$mobile && $this->ajaxReturn(0, '请输入手机号码');
			if(!checkMobile($mobile)){
				$this->ajaxReturn(0, L('mobile_regx_error'));
			}
			$user_id = M('user')->where("mobile='".$mobile."'")->getfield('id');
			
			if($user_id){
				//用户信息
				$user_info = D('user')->user_info($user_id);
				if($user_info['stores_id']){
					//所属案场名称
					$stores_info = D('stores')->stores_info($user_info['stores_id']);
					$this->ajaxReturn(0, '该用户已经所属"'.$stores_info['name'].'"案场');
				}elseif($user_info['stores_id']==0){
					if(false !== M('user')->where("id=".$user_id."")->save(array('stores_id'=>$stores_id,'username'=>$username))){
						$this->ajaxReturn(1,'案场驻守人员添加成功');
					}else{
						$this->ajaxReturn(0,'案场驻守人员添加失败');
					}
				}
			}else{
				//手机号码归属地
	            $city_name = get_city($mobile);
	            $city_id = M('city')->where("name='".$city_name."'")->getfield('id');
	            if(!$city_id) $city_id=0;
				$rand_code         = random(6,1);
				$data['username']  = $username;
				$data['mobile']    = $mobile;
				$data['city_id']   = $city_id;
				$data['password']  = md5($rand_code);
				$data['stores_id'] = $stores_id;
				$data['reg_time']  = time();
				$data['reg_ip']    = get_client_ip();
				
				$last_id = M('user')->add($data);
				if($last_id){
					//是否请求接口
					$send_sms = D('send_sms');
					$result = $send_sms->Messages($mobile,$agent_mobile,$agent_name,$client_name,'',$title,$str,$rand_code,'5',false,1,$mobile_code_origin);
					$this->ajaxReturn(1,'案场驻守人员添加成功');
				}else{
					$this->ajaxReturn(0,'案场驻守人员添加失败');
				}
			}
		}
		$uid = $this->visitor->info['id'];
		$id  = $this->_get('id','intval');
		
		$stores_info = M('stores')->field('id,name,address,status,img,uid,service,code_id')->where('id='.$id)->find();
		$stores_info['user_info'] = M('user')->field('username,mobile')->where(array('id'=>$stores_info['service']))->find();
		$this->assign('stores_info', $stores_info);
		
		//案场成员
		$user_list = M('user')->field('id,username,mobile')->where(array('stores_id'=>$id))->order('reg_time DESC')->select();
		//成交套数
		foreach($user_list as $k=>$v){
			$user_list[$k]['count'] = count(M('myclient_property')->field('id')->where("status_cid=0 AND status = 7 AND uid = ".$v['id'])->select());
		}
		$this->assign('user_list', $user_list);
		
		
		
		$this->assign('setTitle', '案场详情');
		$this->_config_seo();
		$this->display();
	}
	
	
	
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}