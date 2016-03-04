<?php
class teamAction extends m_userbaseAction {

	public function _initialize() {
		parent::_initialize();
		$this->AppID     = C('AppID');
		$this->AppSecret = C('AppSecret');
	}

	public function index(){
		$uid          = $this->m_user_cookie['id'];
		$keyword      = $this->_get('keyword','trim');
		$chengjiao    = $this->_get('chengjiao','trim');
		$city_id_post = $this->_get('city','intval');
		$area_id_post = $this->_get('area','intval');

		if($area_id_post){
			$city_area_id = $area_id_post;
		}else{
			$city_area_id = $city_id_post;
		}
		
		$where = 'admin_id = '.$uid.' AND status=1';
		if($keyword){
			$where .= " AND (username like '%".$keyword."%' OR mobile like '%".$keyword."%')";
		}
		if($city_area_id){
            $where .=' AND city_id in(select id from fph_city where id = '.$city_area_id.' or spid RLIKE "[[:<:]]'.$city_area_id.'[[:>:]]")';
        }
		$list = M('user')->field('id,username,mobile,stat_property,stores_id')->where($where)->order('reg_time ASC')->select();
		//echo M('user')->getlastsql();
		//print_r($list);
		foreach($list as $k=>$v){
			//驻守
            if($v['stat_property']){
                $stat_property = explode(',',$v['stat_property']);
                foreach($stat_property as $v2){
                    $list[$k]['property_title'] .= M('property')->where(array('id'=>$v2))->getfield('title').',';
                }               
            }elseif(!$v['stat_property'] && $v['stores_id']){
                $list[$k]['property_title'] .= M('stores')->where(array('id'=>$v['stores_id']))->getfield('name');
            }
            //成交套数
            $list[$k]['chengjiao'] = M('myclient_property')->where('status = 7 AND uid='.$v['id'])->count('id');
		}		
		$list = $this->ARRAY_sort_by_field($list,'chengjiao',true);

		//是否有成交
		$i=0;
		if($chengjiao==1){
			foreach ($list as $key => $value){	
				if($value['chengjiao']){	
					$list[$i] = $list[$key];
				}else{
					unset($list[$key]);
				}
				$i++;
			}
		}elseif($chengjiao==2){
			foreach ($list as $key => $value){	
				if(!$value['chengjiao']){	
					$list[$i] = $list[$key];
				}else{
					unset($list[$key]);
				}
				$i++;
			}
		}

		//重新构造数组$key值
		$newlist = array();
		$i=0;
		foreach ($list as $key => $value){			
			$newlist[$i] = $list[$key];
			$i++;
		}
		//print_r($newlist);
		//缓存*排序*重构之后的数据
		//S($uid,$newlist,1800);

        $redis = new CacheRedis(1);
        $newlist_arr = serialize($newlist);
        $redis->set($uid,$newlist_arr,1800);

		//获取前十个
		$list_count = count($list);
		if($list_count > 9){
			$list_count = 10;
		}
		for ($x=0; $x<=$list_count-1; $x++){
			$list_arr[]=$newlist[$x];
		}
		//$S_uid = S($uid);
        //$redis = new CacheRedis(1);
        $newlist_arr = $redis->get($uid);
        $listss = unserialize($newlist_arr);

		
		$this->assign('list', $list_arr);
		$this->assign('list_count', count($list_arr));

		//统计该用户下有多少经纪人
		$uid_user_count =  M('user')->where(array('admin_id'=>$uid,'status'=>1))->count('id');
		$this->assign('uid_user_count', $uid_user_count);

		//城市显示
		$city = M('user')->field('city_id')->where('status = 1 AND admin_id = '.$uid.'')->group('city_id')->select();
		$city_id = '';
		foreach($city as $k=>$v){
			$city_id .= $this->city_one($v['city_id']).',';
		}		
		$city_id = substr($city_id,0,-1);
		$city_id = array_unique(explode(',', $city_id));
		
		if($city_id[0]){
			$citylist = M('city')->field('id,name')->where('id in('.implode(',',$city_id).')')->order('id ASC ')->select();
			$this->assign('citylist', $citylist);
		}

		//区域
		if($city_id_post){
			$area_list = M('city')->field('id,name')->where(array('pid'=>$city_id_post))->order('id ASC')->select();
			$this->assign('area_list', $area_list);
		}

		//邀请码
		$admin_code_id = M('admin')->where(array('id'=>$uid))->getfield('code_id');
		$this->assign('admin_code_id', $admin_code_id);

		$this->assign('keyword', $keyword);
		$this->assign('chengjiao', $chengjiao);
		$this->assign('city_id_post', $city_id_post);
		$this->assign('area_id_post', $area_id_post);
		$this->assign('setTitle', '渠道自有经纪人');
		$this->_config_seo();
		$this->display();
	}

	//选择城市
	public function ajax_city_list(){
		$id = $this->_post('id','intval');
		$list = M('city')->field('id,name')->where(array('pid'=>$id))->order('id ASC')->select();
		if($list){
			$this->ajaxReturn(1, '' ,$list);
		}else{
			$this->ajaxReturn(0, '操作失败');
		}
	}

	//向下滑动加载
	public function ajax_index_list(){
		$uid     = $this->m_user_cookie['id'];
		$page    = $this->_post('page','intval');
		$start   = $page * 10;

		//从缓存中读取数据*排序重构之后的数据
		//$list = S($uid);

        $redis = new CacheRedis(1);
        $newlist_arr = $redis->get($uid);
        $list = unserialize($newlist_arr);

		if(9+$start > count($list)){
			//$this->ajaxReturn(0,'别滑动了，已经到底了...');
		}

		//获取十个
		for ($x=$start; $x<=9+$start; $x++){
			$list_arr[]=$list[$x];
		}
		
		$str = '';		
		foreach($list_arr as $key=>$val){
			if($val['id']){
				$str .= '<li>';
		            $str .= '<a href="'.U('m/team/team_detail',array('id'=>$val['id'])).'">';
			            $str .= '<h2>'.$val['username'].' '.$val['mobile'].'</h2>';
			            $str .= '<span class="address">'.$val['property_title'].'</span><span class="store_code_r">成交'.$val['chengjiao'].'套</span><i></i>';
		            $str .= '</a>';
		        $str .= '</li>';
	        }
		}
		
		if($str){
		    $this->ajaxReturn(1,'Success',$str);
	    }else{
		    $this->ajaxReturn(0,'别滑动了，已经到底了...');
	    }
	}

	//经纪人详情
	public function team_detail(){
		$id  = $this->_get('id','intval');
		$uid = $this->m_user_cookie['id'];
		$fph = C('DB_PREFIX');
		!$id && $this->error('参数错误');

		$user_info = M('user')->field('id,username,mobile,admin_id,stat_property,stores_id,reg_time')->where(array('id'=>$id))->find();

		if($user_info['admin_id'] != $uid || $user_info == ''){
			$this->error('参数错误');
		}

		//驻守
        if($user_info['stat_property']){
            $stat_property = explode(',',$user_info['stat_property']);
            foreach($stat_property as $val){
                $user_info['property_title'] .= M('property')->where(array('id'=>$val))->getfield('title').',';
            }               
        }elseif(!$user_info['stat_property'] && $user_info['stores_id']){
            $user_info['property_title'] .= M('stores')->where(array('id'=>$user_info['stores_id']))->getfield('name');
        }
        //成交套数
        $user_info['chengjiao'] = M('myclient_property')->where('status = 7 AND uid='.$user_info['id'])->count('id');
        //带看
        $user_info['daikan'] = M('myclient_property')->where('with_look = 1 AND uid='.$user_info['id'])->count('id');
        //佣金
        $my = M('myclient_status')->field('A.id,C.expenditure')
                                  ->table("{$fph}myclient_status AS A
                                           INNER JOIN {$fph}myclient_property AS B ON A.mpid = B.id
                                           INNER JOIN {$fph}commission as C ON C.pid=A.id")
                                  ->where('A.status = 7 AND B.uid = '.$user_info['id'])->select();
        $user_info['yongjin'] = 0;
        foreach($my as $k=>$v){
            $user_info['yongjin'] = $user_info['yongjin'] + M('expenditure')->where('pid='.$v['id'])->sum('price');
        }

		$this->assign('user_info', $user_info);

		$admin_info = M('admin')->field('username')->where(array('id'=>$user_info['admin_id']))->find();
		$this->assign('admin_info', $admin_info);

		$this->assign('setTitle', '经纪人详情');
		$this->_config_seo();
		$this->display();
	}

	//新增经纪人
	public function team_add(){

		if(IS_POST){

			$username = $this->_post('username','trim');
			$mobile   = $this->_post('mobile','trim');
			$uid      = $this->m_user_cookie['id'];
			!$username && $this->ajaxReturn(0, '请填写经纪人姓名');
			!$mobile && $this->ajaxReturn(0, '请填写经纪人手机号码');
			if(!checkMobile($mobile)){
				$this->ajaxReturn(0, L('mobile_regx_error'));
			}
			$user_info = M('user')->field('id,stores_id,admin_id')->where("mobile='".$mobile."'")->find();
			
			//已经加入团队
			if($user_info['admin_id']){
				$admin_username = M('admin')->where('id='.$user_info['admin_id'].'')->getfield('username');
				$this->ajaxReturn(0, '添加失败！用户已加入了'.$admin_username.'团队');
			}

			//已经加入门店
			if($user_info['stores_id']){
				$stores_name = M('stores')->where('id='.$user_info['stores_id'].'')->getfield('name');
				$this->ajaxReturn(0, '添加失败！用户已加入了'.$stores_name.'门店');
			}
			
			//没有查到该手机号码
			if(!$user_info){
				//手机号码归属地
				$city_name = get_city($mobile);
				$city_id = M('city')->where("name='".$city_name."'")->getfield('id');
				if(!$city_id) $city_id=0;

				$password          = '123456';
				$data['password']  = md5($password);
				$data['username']  = $username;
				$data['admin_id']  = $uid;
				$data['mobile']    = $mobile;
				$data['city_id']   = $city_id;
				$data['origin']    = 1;//微信端
				$data['reg_ip']    = get_client_ip();
				$data['reg_time']  = time();
				$data['status']    = 1;

				if(false !== M('user')->add($data)){
					$this->ajaxReturn(1, '添加成功');
				}else{
					$this->ajaxReturn(0, '添加失败');
				}
			//查找到数据*无门店，无团队
			}elseif(!$user_info['stores_id'] && !$user_info['admin_id']){
				if(false !== M('user')->where(array('id'=>$user_info['id']))->save(array('admin_id'=>$uid))){
					$this->ajaxReturn(1, '添加成功');
				}else{
					$this->ajaxReturn(0, '添加失败');
				}
			}
		}

		$this->assign('setTitle', '新增经纪人');
		$this->_config_seo();
		$this->display();
	}

	//邀请加入
	public function team_invite(){
		$uid = $this->m_user_cookie['id'];
		if(!$uid){
			$this->error('参数出错');
		}
		

		//邀请码
		$admin_info = M('admin')->field('username,code_id,ticket')->where(array('id'=>$uid))->find();
		

		if(!$admin_info['ticket']){
			load("@.wechat_functions");
			$access_token=getAccessToken($this->AppID,$this->AppSecret) ;  //获取access_token
	    	$json_url='https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;
	         
	        $action_name='QR_LIMIT_SCENE';   //生成类型(永久)
	        //$action_name='QR_SCENE';   //生成类型(临时，1800秒)
	        
	        //临时 post的json数据
	        if($action_name=='QR_SCENE'){
	            $curl_data='{"expire_seconds": 1800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": "123"}}';
	        }
	        
	        //永久 post的json数据
	        if($action_name=='QR_LIMIT_SCENE'){
	            $curl_data='{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": '.$uid.'}}}';
	        } 
	        $json_info=json_decode($this->api_notice_increment($json_url,$curl_data),true);
	        M('admin')->where(array('id'=>$uid))->save(array('ticket'=>$json_info['ticket']));
	        $admin_info['ticket'] = $json_info['ticket'];
        }
        $this->assign('admin_info', $admin_info);

		$this->assign('setTitle', '邀请加入');
		$this->_config_seo();
		$this->display();
	}

	//curl提交
	public function api_notice_increment($url, $data){
        $ch = curl_init();
        $header = "Accept-Charset: utf-8";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        if (curl_errno($ch)) {
            //curl_close( $ch )
            return $ch;
        }else{
            //curl_close( $ch ) 
            return $tmpInfo;
        }
        curl_close( $ch ) ;
    }

		
	//根据某字段按倒序/顺序排列
	function ARRAY_sort_by_field($arr_data, $field, $descending = false){  
        $arrSort = array();  
        foreach ($arr_data as $key => $value){  
           $arrSort[$key] = $value[$field];  
        }  
        if($descending){
           arsort($arrSort);  
        }else{  
           asort($arrSort);  
        }  
        $resultArr = array();  
        foreach ($arrSort as $key => $value){  
           $resultArr[$key] = $arr_data[$key];  
        }  
        return $resultArr;
    }

    //获取区域 市级别
    public function city_one($id){
		$city = M('city')->where('id ='.$id)->find();
		$count = explode('|',$city['spid']);
		if(count($count) == 2){
		    return $id;
		}
		if($city['pid'] == 0){
		    return M('city')->where('id ='.$id)->getField('pid');
		}
		return $this->city_one($city['pid']);
    }
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}