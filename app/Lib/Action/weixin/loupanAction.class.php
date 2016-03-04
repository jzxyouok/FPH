<?php
class loupanAction extends frontendAction {

    public function index() {

		$fph     = C('DB_PREFIX');
		$where = 'A.status=1';
		$time = time();
		$search = array();
		$select_city = $this->_get('select_city','intval');
		$select_name = $this->_get('select_name','trim');
		$search_title = $this->_get('search_title','trim');
		if($select_city){
			$search['select_city'] = $select_city;
			$search['select_name'] = $select_name;
			$select_city_id = $select_city;
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
			//print_r($city_info);
			
			if($city_info['id']!=803 && $city_info['id']!=22 && $city_info['id']!=859 && $city_info['id']!=867 && $city_info['id']!=982 && $city_info['id']!=1533){
				$search['select_name'] = '上海市';
				$search['select_city'] = 803;
				$select_city_id = 803;
			}else{
				$search['select_city'] = $city_info['id'];
				$search['select_name'] = $city_info['name'];
				$select_city_id = $city_info['id'];
			}
		}
		
		if($search_title){
			$where .= ' AND A.title like "%'.$search_title.'%"';
			$search['title'] = $search_title;
		}
		
		//获取合作楼盘 id 进行排序
		$cooperation = M('property_cooperation')->field('pid')->where('term_start < '.$time.' AND term_end > '.$time.'')->order('pid ASC')->select();
		
		$field =  '';
		foreach($cooperation as $k=>$v){
			$field .= $v['pid'].',';
		}
		
		//判断是否有合作楼盘 如果没有 将不进行 根据 合作楼盘排序
		if(empty($cooperation)){
			$where_field ='A.id DESC';
		}else{
			$field = substr($field,0,-1);
			$where_field ='FIELD(A.id ,'.$field.') DESC';
		}
		
		//筛选
		//区域
		$search_shai_city = $this->_get('search_shai_city','intval',0);
		if($search_shai_city){
			$select_city_id = $search_shai_city;
			$search['search_shai_city'] = $search_shai_city;
		}
		//板块
		$search_ban_city = $this->_get('search_ban_city','intval',0);
		if($search_ban_city){
			$select_city_id = $search_ban_city;
			$search['search_ban_city'] = $search_ban_city;
			$search['search_ban_city_name'] =  $this->_get('search_ban_city_name','trim');
		}
		
		//路线
		$search_shai_metro = $this->_get('search_shai_metro','intval');
		if($search_shai_metro){
			$search['search_shai_metro'] = $search_shai_metro;
			$where .= ' AND A.metro RLIKE "[[:<:]]'.$search_shai_metro.'[[:>:]]" ';
		}
		
		$search_shai_metro_pid = $this->_get('search_shai_metro_pid','trim');
		if($search_shai_metro_pid){
			$where .= ' AND A.metro RLIKE "[[:<:]]'.$search_shai_metro_pid.'[[:>:]]" ';
			$search['search_shai_metro_pid'] = $search_shai_metro_pid;
			$search['search_shai_metro_pid_name'] =  $this->_get('search_shai_metro_pid_name','trim');
		}
		
		//均价
		$search_shai_item_price = $this->_get('search_shai_item_price','intval');
		if($search_shai_item_price){
			$where_field =' -A.item_price ASC';
			if($search_shai_item_price ==1)
			$where_field =' -A.item_price DESC';
			$search['search_shai_item_price'] = $search_shai_item_price;
		}
		
		//户型
		$search_shai_room =  $this->_get('search_shai_room','intval');
		if($search_shai_room){
			$whereromm = 'house_room = '.$search_shai_room;
			if($search_shai_room == 5)
			$whereromm = 'house_room > '.$search_shai_room;
			$romm_pid = '';
			$romm_arr = M('property_housetype')->where($whereromm)->select();
			if(!empty($romm_arr)){
				foreach($romm_arr as $v){
					$romm_pid .= $v['pid'].',';
				}
			}else{
				$romm_pid .= '0,';
			}
			$where .= ' AND A.id in('.substr($romm_pid,0,-1).')';
			$search['search_shai_room'] = $search_shai_room;
		}
		
		//物业类型
		$search_shai_property_type =  $this->_get('search_shai_property_type','intval');
		if($search_shai_property_type){
			$where .= ' AND A.property_type RLIKE "[[:<:]]'.$search_shai_property_type.'[[:>:]]"';
			$search['search_shai_property_type'] = $search_shai_property_type;
		}
		
		
		//楼盘特点
		$search_shai_property_feature =  $this->_get('search_shai_property_feature','intval');
		if($search_shai_property_feature){
			$where .= ' AND A.property_feature RLIKE "[[:<:]]'.$search_shai_property_feature.'[[:>:]]" ';
			 $search['search_shai_property_feature'] = $search_shai_property_feature;
		}
		$where_field .= ' ,A.add_time DESC';
		$where .=' AND A.city_id in(select id from fph_city where id = '.$select_city_id.' or spid RLIKE "[[:<:]]'.$select_city_id.'[[:>:]]")';
		$list =  M('property')->field('A.id,A.title,A.city_id,A.img_thumb,A.item_price,A.list_price')
				->table("{$fph}property AS A
					LEFT JOIN {$fph}property_cooperation AS B ON A.id = B.pid")
				->where($where)->order($where_field)
				->limit(0,15)
				->select();
	
		if(isset($_GET) AND $_GET){
			$search['get'] = 1;
			$search['getcount'] = M('property')
				->table("{$fph}property AS A
					LEFT JOIN {$fph}property_cooperation AS B ON A.id = B.pid")
				->where($where)->order($where_field)
				->count('A.id');
		}
		$propertyimg = C('propertyimg');
		foreach($list as $k=>$v){ 
			//标签 pid 1 为合作
			$bool = M('property_cooperation')->where('term_start < "'.$time.'" AND term_end > "'.$time.'" AND pid ='.$v['id'])->find();
			$list[$k]['pid'] =1;
			if(empty($bool))
			$list[$k]['pid'] = 0;
			
			
			//区域
			$listspid = M('city')->where('id ='.$v['city_id'])->find();
			$count_spid = explode('|',$listspid['spid']);
			if(count($count_spid) == 3){
				$list[$k]['area'] = M('city')->where('id ='.$listspid['pid'])->getField('name');
				$list[$k]['area'] .= '  '.$listspid['name'];
			}else if(count($count_spid) == 4){
				$list[$k]['area'] = M('city')->where('id ='.$listspid['pid'])->getField('name');
				$list[$k]['area'] .= '  '.M('city')->where('id ='.$listspid['id'])->getField('name');
			}else{
				$list[$k]['area'] = M('city')->where('id ='.$listspid['id'])->getField('name');
			}
		}
	
		//区域显示
		$city = M('property')->field('city_id')->where('status = 1')->group('city_id')->select();
		$city_id = '';
		foreach($city as $k=>$v){
			if($v['city_id']) $city_id .= $this->city_one($v['city_id']).',';
		}
		
		$city_id = substr($city_id,0,-1);
		$city_id = array_unique(explode(',', $city_id));
		$citylist = M('city')->where('id in('.implode(',',$city_id).')')->order('FIELD(id ,'.$search['select_city'].') DESC ')->select();
		
		
		//筛选数据
		//区域
		$shailist_city = M('city')->where('pid ='.$search['select_city'])->select();
		//路线
		$shaimetrolist = M('metro')->where('pid = 0 AND city_id ='.$search['select_city'])->order('id ASC')->select();
		//物业类型 	楼盘特点
		$cate = array('property_type'=>1,'property_feature'=>12);
			$catelist = $this->property_cate($cate);
		
		//print_r($search);
		$this->assign('shaimetrolist', $shaimetrolist);
		$this->assign('shailist_city', $shailist_city);
		$this->assign('search', $search);
		$this->assign('catelist', $catelist);
		$this->assign('list', $list);
		$this->assign('citylist', $citylist);
        $this->assign('countlp', count($list));
        $this->assign('setTitle', '热销楼盘');
        $this->_config_seo();
        $this->display();
    }
    
    //获取分类
    public function property_cate($cate){
        if(!is_array($cate))
            return false;

        $array = array();
        foreach ($cate as $key => $value) {
             $array[$key] = M('property_cate')->field('id,name,pid')->where('pid ='.$value)->select();
        }
        return $array;
    }
    
    //获取区域 市级别
    public function city_one($id)
    {
	$city = M('city')->where('id ='.$id)->find();
	$count = explode('|',$city['spid']);
	if(count($count) == 2)
	{
	    return $id;
	}
	if($city['pid'] == 0)
	{
	    return M('city')->where('id ='.$id)->getField('pid');
	}
	return $this->city_one($city['pid']);
    }
    
    //根据区域获取 板块
    public function shai_city()
    {
	$shai_city = $this->_post('shai_city','intval');
	$city = M('city')->where('pid ='.$shai_city)->select();
	$str = '<option class="strnull" value="">不限</option>';
	foreach($city as $k=>$v)
	{
	    $str .= '<option id="shai_ban_id'.$v['id'].'" value="'.$v['id'].'">'.$v['name'].'</option>';
	}
	if($str){
		    $this->ajaxReturn(1,'',$str);
	}else{
		$this->ajaxReturn(0,'暂无数据');
	}
    }
    
    //根据 地铁线路 获取 详细站名
    public function shai_metro()
    {
	$shai_metro = $this->_post('shai_metro','intval');
	$metro = M('metro')->where('pid ='.$shai_metro)->select();
	$str = '';
	foreach($metro as $k=>$v)
	{
	    $str .= '<option id="shai_metro_pid'.$v['id'].'" value="'.$v['id'].'">'.$v['name'].'</option>';
	}
	if($str){
		    $this->ajaxReturn(1,'',$str);
	}else{
		$this->ajaxReturn(0,'暂无数据');
	}
    }
    
    //向下滑动加载
    public function ajax_list(){
		$fph     = C('DB_PREFIX');
		$where = 'A.status=1';
		$time = time();
		$page = $this->_post('page','intval');//获取请求的页数	
		$start = $page*15;
		$select_city = $this->_post('select_city','intval');
		$search_title = $this->_post('search_title','trim');
		if($select_city){
			$select_city_id = $select_city;
		}else{
			$select_city_id = 803;
		}
	
		$select = array();
		$select['select_city'] = $select_city_id;
		$select['select_name'] = M('city')->where('id ='.$select_city_id)->getField('name');
		
		//获取合作楼盘 id 进行排序
		$cooperation = M('property_cooperation')->field('pid')->where('term_start < '.$time.' AND term_end > '.$time.'')->order('pid ASC')->select();
		
		$field =  '';
		foreach($cooperation as $k=>$v){
			$field .= $v['pid'].',';
		}
	
		//判断是否有合作楼盘 如果没有 将不进行 根据 合作楼盘排序
		if(empty($cooperation)){
			$where_field ='A.id';
		}else{
			$field = substr($field,0,-1);
			$where_field ='FIELD(A.id ,'.$field.') DESC';
		}
	
		if($search_title){
			$where .= ' AND A.title like "%'.$search_title.'%"';
		}
	
		//筛选
		//区域
		$search_shai_city = $this->_post('search_shai_city','intval');
		if($search_shai_city){
			$select_city_id = $search_shai_city;
		}
		//板块
		$search_ban_city = $this->_post('search_ban_city','intval');
		if($search_ban_city){
			$select_city_id = $search_ban_city;
		}
	
		//路线
		$search_shai_metro = $this->_post('search_shai_metro','intval');
		if($search_shai_metro){
			$where .= ' AND A.metro RLIKE "[[:<:]]'.$search_shai_metro.'[[:>:]]" ';
		}
		
		$search_shai_metro_pid = $this->_post('search_shai_metro_pid','trim');
		if($search_shai_metro_pid){
			$metro_arr = explode(',',substr($search_shai_metro_pid,0,-1));
			foreach($metro_arr as $k=>$v)
			{
			$where .= ' AND A.metro RLIKE "[[:<:]]'.$v.'[[:>:]]") ';
			}
		}
	
		//均价
		$search_shai_item_price = $this->_post('search_shai_item_price','intval');
		if($search_shai_item_price){
			$where_field =' -A.item_price ASC';
			if($search_shai_item_price ==1)
			$where_field =' -A.item_price DESC';
		}

		//户型
		$search_shai_room =  $this->_post('search_shai_room','intval');
		if($search_shai_room){
			$whereromm = 'house_room = '.$search_shai_room;
			if($search_shai_room == 5)
				$whereromm = 'house_room >= '.$search_shai_room;
				$romm_pid = '';
				$romm_arr = M('property_housetype')->where($whereromm)->select();
				foreach($romm_arr as $v) {
					$romm_pid .= $v['pid'].',';
				}
			$where .= ' AND A.id in('.substr($romm_pid,0,-1).')';
		}
	
		//物业类型
		$search_shai_property_type =  $this->_post('search_shai_property_type','intval');
		if($search_shai_property_type){
			$where .= ' AND A.property_type RLIKE "[[:<:]]'.$search_shai_property_type.'[[:>:]]"';
		}
	
		//楼盘特点
		$search_shai_property_feature =  $this->_post('search_shai_property_feature','intval');
		if($search_shai_property_feature){
			$where .= ' AND A.property_feature RLIKE "[[:<:]]'.$search_shai_property_feature.'[[:>:]]"';
		}
		
		$where .=' AND A.city_id in(select id from fph_city where id = '.$select_city_id.' or spid RLIKE "[[:<:]]'.$select_city_id.'[[:>:]]")';
		$where_field .= ' ,A.add_time DESC';
		$list =  M('property')->field('A.id,A.title,A.city_id,A.img_thumb,A.item_price,A.list_price')
				->table("{$fph}property AS A
					LEFT JOIN {$fph}property_cooperation AS B ON A.id = B.pid")
				->where($where)
				->order($where_field)
				->limit($start,15)
				->select();
		
		$str = '';
	
		foreach($list as $k=>$v){ 
			//标签 pid 1 为合作
			$bool = M('property_cooperation')->where('term_start < "'.$time.'" AND term_end > "'.$time.'" AND pid ='.$v['id'])->find();
			$list[$k]['pid'] =1;
			if(empty($bool))
			$list[$k]['pid'] = 0;
			
			$list[$k]['img'] = M('property_img')->where('status = 1 AND pid = '.$v['id'].' AND img like "%/'.$v['img_thumb'].'%"')->getField('img');
		
			//区域
			$listspid = M('city')->where('id ='.$v['city_id'])->find();
			$count_spid = explode('|',$listspid['spid']);
			if(count($count_spid) == 3){
			$list[$k]['area'] = M('city')->where('id ='.$listspid['pid'])->getField('name');
			$list[$k]['area'] .= '  '.$listspid['name'];
			}else if(count($count_spid) == 4){
			$list[$k]['area'] = M('city')->where('id ='.$listspid['pid'])->getField('name');
			$list[$k]['area'] .= '  '.M('city')->where('id ='.$listspid['id'])->getField('name');
			}else{
			$list[$k]['area'] = M('city')->where('id ='.$listspid['id'])->getField('name');
			}
			//html 显示
			$str .= '<li><a href="'.U('weixin/loupan/detail',array('id'=>$list[$k]['id'],'select_city'=>$select['select_city'],'select_name'=>$select['select_name'] )).'"><figure>';
			
			if($list[$k]['pid'] == 1)
			$str .= '<i></i> ';
			$str .= '<img src="'.get_fdfs_image($list[$k]['img_thumb'], '_weixin_thumb').'" width="120" height="90"> </figure><section class="detail">';
			$str .= '<h3>'.$list[$k]['title'].'</h3>';
			$str .= '<span class="region">'.$list[$k]['area'].'</span> <span class="price">';
			if($list[$k]['item_price'] != '')
			{
			$str .= $list[$k]['item_price'].'元/㎡';
			}
			else
			{
			$str .= '价格待定';
			}
			$str .= '</span> </section>';
			$str .= '<section class="status">';
			
			if($list[$k]['list_price'] == '')
			{
			$str .= '<span class="no_brokerage">暂无佣金</span>';
			}
			else
			{
			$str .= '<i class="PREMISES_STATUS">佣</i> <span class="deposit">'.$list[$k]['list_price'].'元起</span> <span class="unit">每套</span>';
			}
			$str .= '</section></a></li>';
			
		}
	
		if($str){
			$this->ajaxReturn(1,'',$str);
		}else{
			$this->ajaxReturn(0,'别滑动了，已经到底了...');
		}
    }
    
    
    //楼盘详细页面
    public function detail() {
		$fph     = C('DB_PREFIX');
		$time = time();
		$search = array();
		$search['select_city'] = $this->_get('select_city','intval');
		$search['select_name'] = $this->_get('select_name','trim');
		$uid = $this->visitor->info['id'];
		//分享终端
		$origin = $this->_get('origin','trim');
	    $id = $this->_get('id','intval');
	    if(!$id){
	    	$this->error('参数出错！');
	    	exit;
	    }
		$list = M('property')->where('id = '.$id)->find();
		$propertyimg = C('propertyimg');
		$statusimg = M('property_img')->field('img')->where('pid = '.$list['id'].' AND focus_img=1')->find();
		if(!$statusimg){
			$statusimg = M('property_img')->field('img')->where('pid = '.$list['id'].'')->find();
		}
		$list['img'] = $statusimg['img'];

		$bool = M('property_cooperation')->where('term_start < "'.$time.'" AND term_end > "'.$time.'" AND pid ='.$list['id'])->find();
		$list['pid'] =1;
		if(empty($bool))
		   $list['pid'] = 0;
	
		//是否有带看奖
		$prize = M('property_prize')->field('id,pid,prize,stores_id')->where('pid='.$list['id'].' AND stores_id="" AND time_start<'.$time.' AND time_end > '.$time)->order('id DESC')->find();
		$list['prize'] = $prize['prize'];
		 if($uid){
		 	$prize = M('property_prize')->field('id,pid,prize,stores_id')->where('stores_id != "" AND pid='.$list['id'].' AND time_start<'.$time.' AND time_end > '.$time)->order('id DESC')->find();
		 	if($prize['id']){
		 		$stores_id = M('user')->where('id='.$uid.' AND stores_id in('.$prize['stores_id'].')')->getField('stores_id');
		 		if($stores_id){
		 			$list['prize'] = $prize['prize'];
		 		}
		 	}
		 }


		//楼盘特点
	    $list['property_feature_name'] = '';
		if(!empty($list['property_feature']))
		{
		    $property_type = M('property_cate')->where('id in('.$list['property_feature'].')')->select();
		    foreach($property_type as $k=>$v)
		    {
			$list['property_feature_name'] .= $v['name'].'、';
		    }
		    $list['property_feature_name'] = substr($list['property_feature_name'],0,-3);
		}
	
		//物业类型 	
	    	$list['property_type_name'] = '';
		if(!empty($list['property_type']))
		{
		    $property_type = M('property_cate')->where('id in('.$list['property_type'].')')->select();
		    foreach($property_type as $k=>$v)
		    {
			$list['property_type_name'] .= $v['name'].'、';
		    }
		    $list['property_type_name'] = substr($list['property_type_name'],0,-3);
		}
	
		//建筑类型 	
	    $list['building_type_name'] = '';
		if(!empty($list['building_type']))
		{
		    $property_type = M('property_cate')->where('id in('.$list['building_type'].')')->select();
		    foreach($property_type as $k=>$v)
		    {
			$list['building_type_name'] .= $v['name'].'、';
		    }
		    $list['building_type_name'] = substr($list['building_type_name'],0,-3);
		}
	
		//装修状况 
		$list['decoration_name'] = '';
		if(!empty($list['decoration']))
		{
		    $property_type = M('property_cate')->where('id in('.$list['decoration'].')')->select();
		    foreach($property_type as $k=>$v)
		    {
			$list['decoration_name'] .= $v['name'].'、';
		    }
		    $list['decoration_name'] = substr($list['decoration_name'],0,-3);
		}
	
		//楼盘
		$list['article'] = M('article')->where('pid = '.$id)->select();
		foreach($list['article'] as $k=>$v){
		    $list['article'] [$k] ['article_name'] = M('article_cate')->where('id ='.$v['cate_id'])->getField('name');
		}
		//渠道佣金
		$yj_where = 'pid ='.$id;
		$l_stores_id = M('property_commission')->where("stores_id !='' AND pid =".$id)->order('id DESC')->getField('stores_id');
		if($uid && $l_stores_id){
			$u_count = M('user')->where("id=".$uid." AND stores_id in(".$l_stores_id.")")->count('id');
		}
		if(!$u_count){
			$yj_where .= ' AND stores_id=""';
		}
		$list['commission'] = M('property_commission')->where($yj_where)->order('add_time DESC')->select();
	    foreach ($list['commission'] as $key => $value) {
	       $catearr = M('property_cate')->where('id in('.$value['property_type'].')')->select();
	       $list['commission'][$key]['cate'] = '';
	       foreach ($catearr as $k => $v) {
	          $list['commission'][$key]['cate'][$v['id']]['name'] .= $v['name'];
	      	  $list['commission'][$key]['cate'][$v['id']]['id'] .= $v['id'];
	       }
	    }

		//地铁路线
		if(!empty($list['metro']))
		$list['metro'] = $this->metro($list['metro']);
		
		//在售户型
		$list['housetype'] = M('property_housetype')->where(array('pid'=>$id))->select();
		
		//区域
		if(!$search['select_city'])
		{
		    $search['select_name'] = '上海市';
		    $search['select_city'] = 803;
		}
		$city = M('property')->field('city_id')->where('status = 1')->group('city_id')->select();
		$city_id = '';
		foreach($city as $k=>$v)
		{
			if($v['city_id']) $city_id .= $this->city_one($v['city_id']).',';
		}
		$city_id = substr($city_id,0,-1);
		$city_id = array_unique(explode(',', $city_id));
		$citylist = M('city')->where('id in('.implode(',',$city_id).')')->order('FIELD(id ,'.$search['select_city'].') DESC ')->select();
		$this->assign('citylist', $citylist);
		if($list['pid']==0){
			$list['commission_info'] ='';
			$list['report_info'] = '';
		}
		//print_r($list);
		$this->assign('id', $id);
		$this->assign('list', $list);
		$this->assign('search', $search);
		$this->assign('origin', $origin);
		
	    $uid = $this->visitor->info['id'];  
	    $this->assign('uid', $uid);
	    $return_url = urlencode($_SERVER['REQUEST_URI']);
	    $this->assign('return_url', $return_url);
	   
	    //判断是否收藏       
	    if($uid){
	   		$favorites_count = M('favorites')->where(array('pid'=>$id,'uid'=>$uid))->count('id');
	   		$this->assign('favorites_count', $favorites_count);
	    }

	    $this->_config_seo();
	    $this->display();
    }
    
    /*
    *@Descriptions：地铁关联，字符串转换数组
    *@param string $str
    *@return array
    *@Date:2014-11-19
    *@Author: wsj
    */
    public function metro($str)
    {
        if(empty($str))
            return false;
        
        $array = array();
        $strarray = explode('|', $str);
        foreach ($strarray as $k1 => $v) {
            $arr = explode('&', $v);
            $metro = M('metro')->field('id,pid,name')->where('id ='.reset($arr))->find();
            $array[$k1]['metro_id'] = $metro['id'];
            $array[$k1]['metro_pid'] = $metro['pid'];
            $array[$k1]['metro_name'] = $metro['name'];
            $endarr = explode(',', end($arr));
            foreach ($endarr as $k2 => $n) {
                $array[$k1]['metro_end'][$k2] = M('metro')->field('id,pid,name')->where('id ='.$n)->find();
            }
        }
        return $array;
    }
    
     //在售户型详细页面
    public function hx_detail() {
        $id = $this->_get('id','intval');
	$search = array();
	$search['select_city'] = $this->_get('select_city','intval');
	$search['select_name'] = $this->_get('select_name','trim');
	if(!$id){
        	$this->error('参数出错！');
        	exit;
        }
        $list = M('property_housetype')->where(array('id'=>$id))->find();
	
	//装修状况 
	$list['property_label_name'] = '';
	if(!empty($list['property_label']))
	{
	    $property_type = M('property_cate')->where('id in('.$list['property_label'].')')->select();
	    foreach($property_type as $k=>$v)
	    {
		$list['property_label_name'] .= $v['name'].'、';
	    }
	    $list['property_label_name'] = substr($list['property_label_name'],0,-3);
	}
	
	//区域
	if(!$search['select_city'])
	{
	    $search['select_name'] = '上海市';
	    $search['select_city'] = 803;
	}
	$city = M('property')->field('city_id')->where('status = 1')->group('city_id')->select();
	$city_id = '';
	foreach($city as $k=>$v)
	{
	    $city_id .= $this->city_one($v['city_id']).',';
	}
	$city_id = substr($city_id,0,-1);
	$city_id = array_unique(explode(',', $city_id));
	$citylist = M('city')->where('id in('.implode(',',$city_id).')')->order('FIELD(id ,'.$search['select_city'].') DESC ')->select();
	$this->assign('citylist', $citylist);
	
	
	$uid = $this->visitor->info['id'];  
        $this->assign('uid', $uid);
        $return_url = urlencode($_SERVER['REQUEST_URI']);
        $this->assign('return_url', $return_url);
       
        //判断是否收藏       
        if($uid){
       		$favorites_count = M('favorites')->where(array('pid'=>$list['pid'],'uid'=>$uid))->count('id');
       		$this->assign('favorites_count', $favorites_count);
        }
	
        $this->assign('list', $list);
	$this->assign('search', $search);
	$this->assign('setTitle', $list['title']);
        $this->_config_seo();
        $this->display();
    }
	
    public function huodong() {
        $fph = C('DB_PREFIX');
        $where = ' 1=1 ';
        $search = array();
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
		    $search['select_name'] = '上海市';
		    $search['select_city'] = 803;
		    $select_city_id = 803;
		    $str_pro = ' OR id=802 ';
		}
		
		if($search_title){
		    $where .= ' AND A.title like "%'.$search_title.'%"';
		    $search['title'] = $search_title;
		}
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
		 //楼盘筛选
        $search_property_name =  $this->_get('search_property_name','intval');
        $where .=' AND A.city_id in(select id from fph_city where id = '.$select_city_id.$str_pro.' or spid RLIKE "[[:<:]]'.$select_city_id.'[[:>:]]")';
        $feilds = 'A.*';
        $tablejoin = "{$fph}article AS A";
        if($search_property_name){
        	$tablejoin = "{$fph}article AS A LEFT JOIN {$fph}property AS B ON A.pid=B.id LEFT JOIN {$fph}property_cooperation as C on B.id=C.pid ";
        	$feilds = 'A.*,B.id as bid';
            if($search_property_name==2){//合作楼盘
                $where .= " AND (C.term_start<=".time()." AND C.term_end>=".time().")";
            }elseif($search_property_name==3){//非合作楼盘
                $where2 = " (C.term_start<=".time()." AND C.term_end>=".time().")";
                $hezuo = M('article')->field('A.id')->table($tablejoin)->where($where2)->select();
                $arr_id = '';
                foreach($hezuo as $key=>$value){
               		$arr_id .= $value['id'].',';
                }
                $arr_id = substr($arr_id,0,-1);
                $where .=" AND A.id not in($arr_id)";
            }
            $search['search_property_name'] = $search_property_name;
        }

        //报名筛选
        $search_status_name =  $this->_get('search_status_name','intval');
        if($search_status_name){
           if($search_status_name==1){//可报名
               $where .= " AND A.status=1";
            }elseif($search_status_name==2){//无报名
               $where .= " AND A.status=0";
            }
            $search['search_status_name'] = $search_status_name;
        }
        $list = M('article')->field($feilds)->table($tablejoin)->where($where)->order('A.ordid ASC,A.id DESC')->limit(0,6)->select();
        foreach($list as $k=>$v){
        	$list[$k]['cate_name'] = M('article_cate')->where('id='.$v['cate_id'])->getField('name');
        }
        $this->assign('countlp', count($list));
        $this->assign('list', $list);

		//区域
		$shailist_city = M('city')->where('pid ='.$search['select_city'])->select();
        //区域显示
		$city = M('article')->field('city_id')->where('city_id !=0 ')->group('city_id')->select();
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
        $this->assign('search', $search);
        $this->assign('citylist', $citylist);
        $this->assign('shailist_city', $shailist_city);
        $this->assign('setTitle', '热门活动');
        $this->_config_seo();
        $this->display();
    }

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

    
    //向下滑动加载 活动类别
    public function ajax_huodong_list(){
	    $page = $this->_post('page','intval');//获取请求的页数	
	    $select_city = $this->_post('select_city','intval');//城市ID
	    $search_title = $this->_post('search_title','trim');//搜索关键字
	    $select_name = $this->_post('select_name','trim');//城市名称
	    $search_shai_city = $this->_post('search_shai_city','intval');//区域
	    $search_ban_city = $this->_post('search_ban_city','intval');//板块
	    $search_ban_city_name = $this->_post('search_ban_city_name','trim');//板块名称
	    $search_property_name = $this->_post('search_property_name','intval');//楼盘筛选
	    $search_status_name = $this->_post('search_status_name','intval');//报名筛选
	    $start = $page*6; 
        $fph = C('DB_PREFIX');
        $where = ' 1=1 ';
        $search = array();
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
		    $where .= ' AND A.title like "%'.$search_title.'%"';
		    $search['title'] = $search_title;
		}
		//筛选数据
		//区域
		if($search_shai_city){
		    $select_city_id = $search_shai_city;
		    $search['search_shai_city'] = $search_shai_city;
		}
		//板块
		$search['search_ban_city_name'] = '';
		if($search_ban_city){
		    $select_city_id = $search_ban_city;
		    $search['search_ban_city'] = $search_ban_city;
		    $search['search_ban_city_name'] = $search_ban_city_name;
		}
//
 		//楼盘筛选
        $where .=' AND A.city_id in(select id from fph_city where id = '.$select_city_id.$str_pro.' or spid RLIKE "[[:<:]]'.$select_city_id.'[[:>:]]")';
        $feilds = 'A.*';
        $tablejoin = "{$fph}article AS A";
        if($search_property_name){
        	$tablejoin = "{$fph}article AS A LEFT JOIN {$fph}property AS B ON A.pid=B.id LEFT JOIN {$fph}property_cooperation as C on B.id=C.pid ";
        	$feilds = 'A.*,B.id as bid';
            if($search_property_name==2){//合作楼盘
                $where .= " AND (C.term_start<=".time()." AND C.term_end>=".time().")";
            }elseif($search_property_name==3){//非合作楼盘
                $where2 = " (C.term_start<=".time()." AND C.term_end>=".time().")";
                $hezuo = M('article')->field('A.id')->table($tablejoin)->where($where2)->select();
                $arr_id = '';
                foreach($hezuo as $key=>$value){
               		$arr_id .= $value['id'].',';
                }
                $arr_id = substr($arr_id,0,-1);
                $where .=" AND A.id not in($arr_id)";
            }
            $search['search_property_name'] = $search_property_name;
        }
        //报名筛选
        if($search_status_name){
           if($search_status_name==1){//可报名
               $where .= " AND A.status=1";
            }elseif($search_status_name==2){//无报名
               $where .= " AND A.status=0";
            }
            $search['search_status_name'] = $search_status_name;
        }
        $list = M('article')->field($feilds)->table($tablejoin)->where($where)->order('A.ordid ASC,A.id DESC')->limit($start,6)->select();
        $this->assign('countlp', count($list));
        $str = '';
        foreach($list as $k=>$v){
        	$list[$k]['cate_name'] = M('article_cate')->where('id='.$v['cate_id'])->getField('name');
        	$str .= '<li><a href="'.U('weixin/loupan/hd_show',array('id'=>$v['id'],'select_city'=>$select_city)).'"><span class="tips">'.$list[$k]['cate_name'].'</span>';
	        $str .= '<h2>'.$v['title'].'</h2>';
	        $str .= '<figure>';
	        if($v['img']){
	        	 $str .='<img src="'.get_fdfs_image($v['img'], '_720x540').'" />';
	        }
	           
	        $str .= '</figure>';
	        $str .= '<time>活动日期：'.date('Y.m.d',$v['time_start']).'-'.date('Y.m.d',$v['time_end']).'</time>';
	        $str .= '<p>'.msubstr(strip_tags($v['info']),0,80,'utf-8',true).'</p>';
	        $str .= '</a></li>';
        }
	    if($str){
		    $this->ajaxReturn(1,'',$str);
	    }else{
		    $this->ajaxReturn(0,'别滑动了，已经到底了...');
	    }
	    
    }
	
	//活动详细页面
	public function hd_show() {
        $id = $this->_get('id','intval');
        $info = M('article')->field('id,title,img,time_start,time_end,info,pid,city_id')->where(array('id'=>$id))->find();
        $get_time_end = '';
        if($info['time_end']<time()){
        	$get_time_end = 1;
        }
        $info['get_time_end']=$get_time_end;
	    $select_city = $this->_get('select_city','intval');
		if($select_city){
		    $info['select_city'] = $select_city;
		}else{
		    $info['select_city'] = 803;
		}
		$info['select_name'] = M('city')->where('id='.$info['select_city'])->getField('name');
 		
	   
        $protitle = M('property')->where(array('status'=>1,'id'=>$info['pid']))->getField('title');
        $this->assign('protitle', $protitle);
      	$fph=C('DB_PREFIX');
        $data['B.term_end'] = array('EGT',time());
		$data['B.term_start'] = array('ELT',time());
		$data['A.id'] = array('eq',$info['pid']);
		$info['cooperation'] = M('property')->field('A.id,A.tel')->table("{$fph}property AS A LEFT JOIN {$fph}property_cooperation AS B ON A.id=B.pid")->where($data)->find();
		$this->assign('info', $info);
        $this->assign('setTitle', '热门活动');
        $this->_config_seo();
        $this->display();
    }
    
    //收藏楼盘
    public function favorite (){
    	if(IS_POST){
            $uid = $this->visitor->info['id'];  
    		$pid = $this->_post('pid','trim');
    		if(!$uid){
    			$this->ajaxReturn(0,'登录之后才能收藏');
    		}  		
    		if(!$pid){
    			$this->ajaxReturn(0,'无楼盘信息');
    		}
    	   	if(M('favorites')->where(array('pid'=>$pid,'uid'=>$uid))->count('id')){
    	   		$this->ajaxReturn(0,'已经收藏过了');
    	   	}
    	    $data['uid']  = $uid;
    	    $data['pid']  = $pid;
    	    $data['add_time']=time();
        	if($return !== M('favorites')->add($data)){
        		$this->ajaxReturn(1,L('favorite_successe'));
        	}else{
        		$this->ajaxReturn(0,L('favorite_error'));
        	}
        }
     
    }
    //取消收藏楼盘
    public function cancel_favorite (){
    	if(IS_POST){
            $uid = $this->visitor->info['id'];  
    		$pid = $this->_post('pid','trim');
    		if(!$uid){
    			$this->ajaxReturn(0,'登录之后才能取消收藏');
    		}
    		if(!$pid){
    			$this->ajaxReturn(0,'无楼盘信息');
    		}
    		if($return !== M('favorites')->where(array('pid'=>$pid,'uid'=>$uid))->delete()){
    			$this->ajaxReturn(1,'已经取消收藏了');
    		}else{
    			$this->ajaxReturn(0,'参数有误');
    		}
    	}
    
    }
	
	
	//活动报名
	public function huodong_baoming(){
		if(IS_POST){
			$uid    = $this->visitor->info['id'];
			$name   = $this->_post('name','trim');
			$mobile = $this->_post('mobile','trim');
			$pid    = $this->_post('pid','intval');
			if(!$uid){
				$uid = 0;
			}
			!$name   && $this->ajaxReturn(0,'请输入您的姓名');
			!$mobile && $this->ajaxReturn(0,'请输入您的电话');
			!$pid    && $this->ajaxReturn(0,'请选择所要报名的活动');
			$get_time_end = M('article')->where('id='.$pid)->getField('time_end');
			if($get_time_end<time()){
	        	$this->ajaxReturn(0,'报名时间已截止');exit;
	        }
			$data['pid']      = $pid;
			$data['uid']      = $uid;
			$data['name']     = $name;
			$data['mobile']   = $mobile;
			$data['add_time'] = time();

			if(false !== M('article_baoming')->where(array('pid'=>$pid,'uid'=>$uid))->add($data)){
				$user_id = M('user')->where("username = '' AND mobile = '".$mobile."'")->getfield('id');
				if($user_id){
					M('user')->where(array('id'=>$user_id))->save(array('username'=>$name));
				}
				$this->ajaxReturn(1,'报名成功');
			}else{
				$this->ajaxReturn(0,'报名失败');
			}
		}
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
    
}