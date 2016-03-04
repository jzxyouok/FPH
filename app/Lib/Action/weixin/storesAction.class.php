<?php
class storesAction extends weixin_userbaseAction {
    public function _initialize() {
		parent::_initialize();
		$this->_mod_c = D('company');//公司
		$this->_mod_s = D('stores');//门店
	}
	public function store_list(){
		$uid = $this->visitor->info['id'];
		$search = array();
		$where = ' status = 1 AND type = 2 ';
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
		//公司列表
		$company_list = M('company')->where(array('teamwork'=>1))->select();
		$search['internal'] = M('user')->where(array('id'=>$uid, 'status'=>1))->getField('internal');

		//筛选数据
		//公司
		$search_company_city = $this->_get('search_company_city','intval');
		if($search_company_city){
			$where .= " AND pid='".$search_company_city."'";
		    $search_company_city = $search_company_city;
		    $search['search_company_city'] = $search_company_city;
		}
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
		//门店类别
        $search_status_name =  $this->_get('search_status_name','intval');
        if($search_status_name){
        	//1 全部 2 我的门店
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
		$this->assign('company_list', $company_list);
		$this->assign('count', $count);
		$this->assign('store_count', $store_count);
		$this->assign('list', $list);
		$this->assign('countlp', count($list));
		$this->assign('citylist', $citylist);
		$this->assign('setTitle', '二手房门店');
		$this->_config_seo();
		$this->display();
	}

	//门店详情
	public function store_show(){
		$uid = $this->visitor->info['id'];
		$id = $this->_get('id','intval');
		$stores_info = array();
		$user_stores_id = $id;
		$stores_info = M('stores')->field('id,name,code_id,address,status,contact,contact_tel,service,img,uid')->where('id='.$user_stores_id)->find();
		$stores_info['user_info'] = M('user')->field('username,mobile')->where(array('id'=>$uid))->find();

		if($stores_info['service']!=0){
			$stores_info['user_info'] = M('user')->field('username,mobile')->where(array('id'=>$stores_info['service']))->find();
		}else{
			$stores_info['user_info'] = M('user')->field('username,mobile')->where(array('id'=>$stores_info['uid']))->find();
		}
		$stores_info['storesid'] = $user_stores_id;
		$stores_log_list = M('stores_log')->where('pid='.$id)->field('id,uid,info,add_time')->order('id DESC')->limit(0,5)->select();
		foreach($stores_log_list as $k=>$v){
			$stores_log_list[$k]['name'] =  M('user')->where(array('id'=>$v['uid']))->getField('username');
			$stores_log_list[$k]['mobile'] =  M('user')->where(array('id'=>$v['uid']))->getField('mobile');
		}
		$fph  = C('DB_PREFIX');
		$this->assign('stores_log_list', $stores_log_list);
		$this->assign('stores_info', $stores_info);
		$this->assign('http_host', 'http://'.$_SERVER['HTTP_HOST']);
		$this->assign('id', $id);
		$this->assign('count_log_list', count($stores_log_list));
		$this->assign('user_stores_id', $user_stores_id);
		$this->assign('setTitle', '门店详情');
		$this->_config_seo();
		$this->display();
	}
	public function ajax_store_log_list(){
		$uid = $this->visitor->info['id'];
		$id = $this->_post('id','intval');
		$page = $this->_post('page','intval');
		$start = $page*5; 
		$stores_log_list = M('stores_log')->where('pid='.$id)->field('id,uid,info,add_time')->order('id DESC')->limit($start,5)->select();
		$str = '';
		foreach($stores_log_list as $k=>$v){
			$stores_log_list[$k]['name'] =  M('user')->where(array('id'=>$v['uid']))->getField('username');
			$stores_log_list[$k]['mobile'] =  M('user')->where(array('id'=>$v['uid']))->getField('mobile');
			$str .='<li><div class="records_list">';
            $str .= '<section class="top"> <span class="contactor">'.$stores_log_list[$k]['name'].' '.$stores_log_list[$k]['mobile'].' </span><span class="date">'.date('Y-m-d',$v['add_time']).'</span> </section>
            <p>'.$v['info'].'</p>';
			$str .='</div></li>';
		}
		if($str){
		    $this->ajaxReturn(1,'',$str);
	    }else{
		    $this->ajaxReturn(0,'别滑动了，已经到底了...');
	    }
	}
	//门店下滑
	public function ajax_company_list(){
		$uid = $this->visitor->info['id'];
		$page = $this->_post('page','intval');
	    $search_shai_city = $this->_post('search_shai_city','intval');
	    $select_city = $this->_post('select_city','intval');
	    $search_title = $this->_post('search_title','trim');
	    $select_name = $this->_post('select_name','trim');
	    $search_ban_city = $this->_post('search_ban_city','intval');
	    $search_ban_city_name = $this->_post('search_ban_city_name','trim');
	    $search_company_city = $this->_post('search_company_city','intval');
	    $search_status_name =  $this->_post('search_status_name','intval');
	    $start = $page*6; 
		$search = array();
		$where = ' status = 1 AND type = 2 ';
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
		    $search['select_name'] = '上海市';
		    $search['select_city'] = 803;
		    $select_city_id = 803;
		    $str_pro = ' OR id=802 ';
		}
		if($search_title){
		    $where .= ' AND name like "%'.$search_title.'%"';
		    $search['title'] = $search_title;
		}
		//公司列表
		$company_list = M('company')->where(array('teamwork'=>1))->select();
		$search['stores_id'] = M('user')->where(array('id'=>$uid, 'status'=>1))->getField('stores_id');
		//筛选数据
		//公司
		if($search_company_city){
			$where .= " AND pid='".$search_company_city."'";
		    $search_company_city = $search_company_city;
		    $search['search_company_city'] = $search_company_city;
		}
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
		    $search['search_ban_city_name'] =  $search_ban_city_name;
		}
		//门店类别
        if($search_status_name){
        	//1 全部 2 我的门店
            if($search_status_name==2){
               $where .= " AND uid= $uid";
            }
            $search['search_status_name'] = $search_status_name;
        }
		$where .=' AND city_id in(select id from fph_city where id = '.$select_city_id.$str_pro.' or spid RLIKE "[[:<:]]'.$select_city_id.'[[:>:]]")';
		$list = $this->_mod_s->where($where)->order(' id DESC ')->limit($start,6)->select();
		// foreach($list as $key=>$value){
		// 	if($value['service']!=0){
		// 		$user_info = M('user')->field('username,mobile')->where('id='.$value['service'])->find();
		// 		$list[$key]['username'] = $user_info['username'];
		// 		$list[$key]['mobile'] = $user_info['mobile'];
		// 	}
		// 	$list[$key]['user_count'] = M('user')->where('stores_id='.$value['id'])->count('id');
		// }
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

        	 $str .='<li><a href="'.U('weixin/stores/store_show',array('id'=>$v['id'])).'"><h2>'.$v['name'].'<b class="LBL_STATUS sc_11">邀请码：'.$v['code_id'].'</b></h2><span class="address">'.$v['address'].'</span><span class="store_code">服务专员:'.$list[$k]['username'].' '.$list[$k]['mobile'].'</span><span class="store_code_r">成员'.$list[$k]['user_count'].'人</span><i></i></a></li>';
        }
        if($str){
		    $this->ajaxReturn(1,'',$str);
	    }else{
		    $this->ajaxReturn(0,'别滑动了，已经到底了...');
	    }
		// $this->assign('shailist_city', $shailist_city);
		// $this->assign('search', $search);
		// $this->assign('company_list', $company_list);
		// $this->assign('count', $count);
		// $this->assign('list', $list);
		// $this->assign('citylist', $citylist);
		// $this->assign('setTitle', '门店列表');

	}
	//门店会员列表
	public function index(){
		$uid = $this->visitor->info['id'];
		$id = $this->_get('id','intval');
		$list = $stores_info = array();
		$user_stores_id = M('user')->where('id='.$uid)->getField('stores_id');
		if($id){
			$user_stores_id = $id;
			$uid = M('stores')->where('id='.$id)->getField('uid');
		}
		if($user_stores_id!=0){
			$stores_info = M('stores')->field('id,name,uid,code_id,type,address,status,contact,contact_tel,service,img')->where('id='.$user_stores_id)->find();
			$stores_info['storesid'] = $user_stores_id;
			$list = M('user')->field('id,username,mobile')->where('stores_id='.$user_stores_id)->select();
		}
		$fph  = C('DB_PREFIX');
		//成交套数
		foreach($list as $k=>$v){
			$list[$k]['count'] = count(M('myclient_property')->field('id')->where("status_cid=0 AND status = 7 AND uid = ".$v['id'])->select());
		}
		if(empty($list)){
		 	$list=array();
		}
		if($stores_info['type']==1){
			$stores_type_info = M('user')->field('username,mobile')->where('id='.$stores_info['service'])->find();
			$this->assign('stores_type_info', $stores_type_info);
		}
		$this->assign('stores_info', $stores_info);
		$this->assign('user_stores_id', $user_stores_id);
		$this->assign('list', $list);
		$this->assign('http_host', 'http://'.$_SERVER['HTTP_HOST']);
		$this->assign('id', $id);
		$this->assign('setTitle', '门店会员列表');
		$this->_config_seo();
		$this->display();
	}
	//门店成员
	public function store_index(){
		$uid = $this->visitor->info['id'];
		$id = $this->_get('id','intval');
		$list = $stores_info = array();
		$user_stores_id = $id;
		$internal = M('user')->where('id='.$uid)->getField('internal');
		if($user_stores_id!=0){
			$stores_info = M('stores')->field('id,name,code_id,address,status,contact,contact_tel,service,img,uid')->where('id='.$user_stores_id)->find();
			$stores_info['storesid'] = $user_stores_id;
			$list = M('user')->field('id,username,mobile')->where('stores_id='.$user_stores_id)->select();
		}
		if($stores_info['service']!=0){
			$stores_info['user_info'] = M('user')->field('username,mobile')->where(array('id'=>$stores_info['service']))->find();
		}else{
			$stores_info['user_info'] = M('user')->field('username,mobile')->where(array('id'=>$stores_info['uid']))->find();
		}
		$fph  = C('DB_PREFIX');
		//成交套数
		foreach($list as $k=>$v){
			$list[$k]['count'] = count(M('myclient_property')->field('id')->where("status_cid=0 AND status = 7 AND uid = ".$v['id'])->select());
		}
		if(empty($list)){
		 	$list=array();
		}
		$this->assign('stores_info', $stores_info);
		$this->assign('internal', $internal);
		$this->assign('user_stores_id', $user_stores_id);
		$this->assign('list', $list);
		$this->assign('http_host', 'http://'.$_SERVER['HTTP_HOST']);
		$this->assign('id', $id);
		$this->assign('setTitle', '门店会员列表');
		$this->_config_seo();
		$this->display();
	}
	//添加会员
	public function user_add(){
		$id = $this->_get('id','intval');
		$this->assign('id', $id);
		$this->assign('setTitle', '添加会员');
		$this->_config_seo();
		$this->display();
	}
	//门店详情
	public function store_info(){
		$uid = $this->visitor->info['id'];
		$id = $this->_get('id','intval');
		$stores_info = M('stores')->field('id,name,code_id,address,status,contact,contact_tel,service,pid,city_id,img')->where('id='.$id)->find();

		$city_spid = M('city')->where('id ='.$stores_info['city_id'])->getField('spid');
		$spid_arr = explode('|', $city_spid.$stores_info['city_id']);
		$get_cityname = '';
		foreach ($spid_arr as $key => $value) {
			$get_cityname .= M('city')->where('id ='.$value)->getField('name').' ';
		}
		$stores_info['city_names'] = $get_cityname;

		$stores_info['company'] = M('company')->where('id='.$stores_info['pid'])->getField('short_name');
		$this->assign('stores_info', $stores_info);
		$this->assign('id', $id);
		$this->assign('setTitle', '门店详情');
		$this->_config_seo();
		$this->display();
	}
	//上传图片
	public function upload_avatar() {
		$id= $this->_get('id','intval');
        if (!empty($_FILES['avatar']['name'])) {
            //会员头像规格
            $avatar_size = explode(',', '100');
            //回去会员头像保存文件夹
			$dir = date('ym/d/');
			 $suffix = '';
            foreach ($avatar_size as $size) {
                $suffix .= '_'.$size.',';
            }
            //上传头像
             $result = $this->_upload($_FILES['avatar'], 'stores_avatar/'. $dir, 
             	array(
             		'width'=>'100', 
             		'height'=>'70',
             		'suffix'=>trim($suffix, ',')
             		));
            if ($result['error']) {
                $this->ajaxReturn(0, $result['info']);
            } else {
				M('stores')->where(array('id'=>$id))->save(array('img'=>$dir.$result['info'][0]['savename']));
				$arr_pic = explode('.', $result['info'][0]['savename']);
                $data = __ROOT__.'/data/upload/stores_avatar/'.$dir.$arr_pic[0].'_100.'.$arr_pic[1];
                $this->ajaxReturn(1, L('upload_success'), $data);
            }
        } else {
            $this->ajaxReturn(0, L('illegal_parameters'));
        }
    }
	public function edit_store(){
		$id = $this->_get('id','intval');
		$type = $this->_get('type','intval');
		$stores_info = M('stores')->field('id,contact,contact_tel')->where('id='.$id)->find();
		$this->assign('stores_info', $stores_info);
		$this->assign('type', $type);
		$this->assign('setTitle', '信息修改');
		$this->_config_seo();
		$this->display();
	}
	//门店联系人联系电话修改
    public function ajax_edit_store(){
    	$uid = $this->visitor->info['id'];
    	$store_info  = $this->_post('store_info','trim');
    	$store_id  = $this->_post('store_id','intval');
    	$store_type  = $this->_post('store_type','intval');
    	if($store_type ==1){//联系人
    		$info = '修改门店联系人';
			$id = M('stores')->where('id ='.$store_id)->save(array('contact'=>$store_info));
    	}else{//联系电话
    		$info = '修改门店电话';
    		$id = M('stores')->where('id ='.$store_id)->save(array('contact_tel'=>$store_info));	
    	}
     	//门店日志表录入
       	D('stores_log')->insert($uid,$id,time(),$info);
    	if ($id) {
            $this->ajaxReturn(1, '操作成功',$id);
        } else {
            $this->ajaxReturn(0,'操作失败',$store_id);
        }

    }
	//门店显示详情页面
	public function complete(){
		$id = $this->_get('id','intval');
		$type_ok = $this->_get('type_ok','intval');
		$info = M('stores')->where('id='.$id)->find();
		if($type_ok==2){
			$info['type_ok'] = 1;
		}
		$this->assign('setTitle', '门店详情页面');
		$this->assign('info',$info);
		$this->assign('http_host', 'http://'.$_SERVER['HTTP_HOST']);
		$this->_config_seo();
		$this->display();
	}
	public function store_required(){
		$id = $this->_get('id','intval');
		$uid = $this->visitor->info['id'];
		$info = M('stores')->where('id='.$id)->find();
		M('user')->where('id ='.$uid)->save(array('stores_id'=>$id));
		$this->assign('info',$info);
		$this->_config_seo();
		$this->display();
	}
	//新建门店
	public function add() {
		$uid = $this->visitor->info['id'];
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
		$this->assign('setTitle', '新建门店');
		$this->_config_seo();
		$this->display();
    }
    //录入数据
    public function insert(){
    	$uid = $this->visitor->info['id'];
    	$get_city_id  = $this->_post('get_city_id','intval');
    	$company_name = $this->_post('company_name','trim');
    	$stores_name  = $this->_post('stores_name','trim');
    	$stores_address  = $this->_post('stores_address','trim');
    	$contact  = $this->_post('contact','trim');
    	$contact_tel  = $this->_post('contact_tel','trim');
    	$type  = $this->_post('type','intval');
    	$tim = time();
    	if($type==1){
    		//所属公司
	    	$data['uid']  = $uid;
	    	$data['name'] = $company_name;
	    	$data['short_name'] = $company_name;
	    	$data['add_time'] = $tim;
	       	$lastid = M('company')->add($data);
    	}else{
    		$lastid  = $this->_post('get_one_company','intval');
    	}
       	$code_id = M('stores')->order('code_id DESC')->getField('code_id');
       	//门店
       	$code_id = M('stores')->order('code_id DESC')->getField('code_id');
       	$internal = M('user')->where('id='.$uid)->getField('internal');
       	if($internal==1){
       		$data_s['service']  = $uid;
       		$data_s['status']  = 1;
       	}
       	$data_s['uid']  = $uid;
		$data_s['type'] = 2;
		$data_s['type_table'] = 2;
       	$data_s['pid']  = $lastid;
       	$data_s['code_id']  = $code_id+1;
       	$data_s['name']  = $stores_name;
       	$data_s['city_id']  = $get_city_id;
       	$data_s['address'] = $stores_address;
       	$data_s['contact'] = $contact;
       	$data_s['contact_tel'] = $contact_tel;
       	$data_s['add_time'] = $tim;
       	$s_name = M('stores')->where('name="'.$stores_name.'"')->getField('name');
       	$lastid_s = '';
       	if(empty($s_name)){
       		$lastid_s = M('stores')->add($data_s);
	       	if($internal!=1){
	       		M('user')->where('id ='.$uid)->save(array('stores_id'=>$lastid_s));
	       	}
	       	//门店日志表录入
	       	$info = '创建了本门店';
	       	D('stores_log')->insert($uid,$lastid_s,$tim,$info);
       	}else{
       		$lastid_s = M('stores')->where('name="'.$s_name.'"')->getField('id');
       	}
       
      
    	if ($lastid_s) {
            $this->ajaxReturn(1, '操作成功',$lastid_s);
        } else {
            $this->ajaxReturn(0,'操作失败');
        }

    }
    /**
     * ajax检测门店是否存在
     */
    public function ajax_check_name() {
        $name = $this->_post('stores_name', 'trim');
        $id = $this->_post('id', 'intval');
        $arr = array();
        $result = $this->_mod_s->name_exists($name,  $id);
        if($result){
        	$pid = M('stores')->where('id='.$result)->getField('pid');
        	$teamwork = M('company')->where('id='.$pid)->getField('teamwork');
        	$arr['pid'] = $pid;
        	$arr['teamwork'] = $teamwork;
        }
       
        $arr['result'] = $result;
        if ($result) {
            $this->ajaxReturn(0, '该门店已经存在',$arr);
        } else {
            $this->ajaxReturn();
        }
    }
    /**
     * ajax根据邀请码检测门店是否存在
     */
    public function ajax_check_code_id() {
        $code_id = $this->_post('code_id', 'trim');
        $result = $this->_mod_s->get_stores_id($code_id);
        if ($result) {
            $this->ajaxReturn(0, '该门店已经存在',$result);
        } else {
            $this->ajaxReturn(1);
        }
    }
    /**
     * ajax检测所属公司是否存在
     */
    public function ajax_check_company() {
        $name = $this->_post('company_name','trim');
        $id = $this->_post('id', 'intval');

        if ($this->_mod_c->company_exists($name,  $id)) {
            $this->ajaxReturn(0, '所属公司已经存在');
        } else {
            $this->ajaxReturn();
        }
    }
    /**
     * ajax查找对应所属公司列表
     */
    public function ajax_check_company_list() {
        $name = $this->_post('company_name','trim');
        $list = $this->_mod_c->company_list_exists($name);
        $str = '';
		$arr=array();
        foreach($list as $k=>$v){
        	$str  .='<li><a href="javascript:;" rel = '.$v['id'].' >'.$v['name'].'</a></li>';
        }
		$arr['str']=$str;
		$arr['count']=count($list);
        if ($list) {
            $this->ajaxReturn(1, '操作成功',$arr);
        } else {
            $this->ajaxReturn(0,'操作失败');
        }
    }

    //根据区域获取 板块
    public function shai_city(){
		$shai_city = $this->_post('shai_city','intval');
		$shai_type = $this->_post('shai_type','intval');
		$city = M('city')->where('pid ='.$shai_city)->select();
		if($shai_type==1){
			$str = '<option value="">请选择区域</option>';
		}elseif($shai_type==2){
			$str = '<option value="">请选择版块</option>';
		}elseif($shai_type==3){
			$str = '<option value="">请选择版块</option>';
		}
		foreach($city as $k=>$v){
		    $str .= '<option value="'.$v['id'].'">'.$v['name'].'</option>';
		}
		if($str && count($city)!=0){
			    $this->ajaxReturn(1,'',$str);
		}else{
			$this->ajaxReturn(0,'暂无数据');
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
	//门店日志信息录入
    public function store_log_insert(){
    	$uid = $this->visitor->info['id'];
    	$store_info  = $this->_post('store_info','trim');
    	$store_id  = $this->_post('store_id','intval');
    	$tim = time();
    	$data_log['pid']  = $store_id;
       	$data_log['uid']  = $uid;
       	$data_log['info']  = $store_info;
       	$data_log['add_time'] = $tim;
       	$lastid_log = M('stores_log')->add($data_log);
    	if ($lastid_log) {
    		$user_info = M('user')->field('username,mobile')->where(array('id'=>$uid))->find();
    		$user_info['info']=$store_info;
    		$user_info['add_time']=date('Y-m-d',$tim);
            $this->ajaxReturn(1, '操作成功',$user_info);
        } else {
            $this->ajaxReturn(0,'操作失败');
        }

    }	
	//ajax 门店添加会员
	public function ajax_stores_username_add(){
		$uid       = $this->visitor->info['id'];
		$username  = $this->_post('username','trim');
		$mobile    = $this->_post('mobile','trim');
		$stores_id = $this->_post('store_id','trim');
		
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
				$stores_info = D('stores')->stores_info($user_info['stores_id']);
				$this->ajaxReturn(0, '该用户已经所属"'.$stores_info['name'].'"门店');
			}elseif($user_info['stores_id']==0){
				if(false !== M('user')->where("id=".$user_id."")->save(array('stores_id'=>$stores_id,'username'=>$username))){
					$this->ajaxReturn(1,'门店人员添加成功',$stores_id);
				}else{
					$this->ajaxReturn(0,'门店人员添加失败');
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
				$this->ajaxReturn(1,'门店人员添加成功',$stores_id);
			}else{
				$this->ajaxReturn(0,'门店人员添加失败');
			}
		}
	}
   
	
	
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}