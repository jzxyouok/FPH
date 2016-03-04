<?php
class propertyAction extends frontendAction {
    
    public function _initialize(){
		parent::_initialize();
		$this->_mod = D('property');
    }
    
     /*
    *@Descriptions：楼盘显示
    *@Date:2015-1-19
    *@Author: wsj
    */
    public function index() {

		$fph        = C('DB_PREFIX');
    	$area       = $this->_get('area', 'intval',0);
    	$house_type = $this->_get('house_type', 'intval',0);
    	$tenement   = $this->_get('tenement', 'intval',0);
    	$search     = $this->_get('search','trim');
		$time       = time();

		//显示楼盘列表
		$where = 'A.status = 1';
		if($search){
			$where .= ' AND A.title like "%'.$search.'%"';
		}
		if($area){
			$where .=' AND A.city_id in(select id from fph_city where id = '.$area.' or spid LIKE "%'.$area.'%")';
		}else{
			if(!empty($_COOKIE['head_city'])){
				$where .=' AND A.city_id in(select id from fph_city where id = '.$_COOKIE['head_city'].' or spid RLIKE "[[:<:]]'.$_COOKIE['head_city'].'[[:>:]]")';
			}
		}
		if($tenement){
			$where .= ' AND A.property_type like "%'.$tenement.'%"';
		}


		if($house_type){
			$house_type_arr = M('property_housetype')->field('pid')->where('house_room ='.$house_type)->select();
			foreach($house_type_arr as $k=>$v){
				$house_type_id .= $v['pid'].',';
			}
			$house_type_id = substr($house_type_id,0,-1);
			$where .= ' AND A.id in('.$house_type_id.')';
		}

		//过滤吧合作放到最前面
		//获取合作楼盘 id 进行排序
		$cooperation = M('property_cooperation')->field('pid')->where('term_start < '.$time.' AND term_end > '.$time.'')->order('pid ASC')->select();

		$field =  '';
		foreach($cooperation as $k=>$v){
			$field .= $v['pid'].',';
		}

		//判断是否有合作楼盘 如果没有 将不进行 根据 合作楼盘排序
		if(empty($cooperation)){
			$where_field ='A.add_time DESC';
		}else{
			$field = substr($field,0,-1);
			$where_field ='FIELD(A.id ,'.$field.') DESC,A.add_time DESC';
		}

		$count = M('property')->table("{$fph}property AS A
										INNER JOIN {$fph}city AS C ON  A.city_id = C.id")->where($where)->count('A.id');
		$p = new Page($count, 9);
		$page = $p->show();
		$list = M('property')->field('A.id,A.title,A.city_id,A.img_thumb,A.item_price,A.item_price,A.prefer,A.commission_info,A.list_price')->table("{$fph}property AS A INNER JOIN {$fph}city AS C ON  A.city_id = C.id")
								->where($where)
								->limit($p->firstRow.','.$p->listRows)
								->order($where_field)
								->select();
		$citylist_id = $room_id = '';
		foreach($list  as $k=>$v){

			//标签 pid 1 为合作
			$bool = M('property_cooperation')->where('term_start < "'.$time.'" AND term_end > "'.$time.'" AND pid ='.$v['id'])->find();
			$list[$k]['pid'] =1;
			if(empty($bool))
				$list[$k]['pid'] = 0;

			//设置 只显示区域
			$listspid = M('city')->where('id ='.$v['city_id'])->find();
			$count_spid = explode('|',$listspid['spid']);
			if(count($count_spid) == 3){
				$list[$k]['city'] = $listspid['name'];
			}else if(count($count_spid) == 4){
				$list[$k]['city'] = M('city')->where('id ='.$listspid['pid'])->getField('name');
			}else{
				$list[$k]['city'] = M('city')->where('id ='.$listspid['id'])->getField('name');
			}
		}

		//获取区域 只显示 已有楼盘
		$city_arr = M('property')->where('status = 1')->group('city_id')->select();
		foreach($city_arr as $k=>$v){
			$cityspid = M('city')->where('id ='.$v['city_id'])->find();
			$spid = explode('|',$cityspid['spid']);
			if(count($spid) == 4){
				$citylist_id .= $cityspid['pid'].',';
			}else{
				$citylist_id .= $cityspid['id'].',';
			}
		}
		$city_list_id = substr($citylist_id,0,-1);
		$citylist_id = explode(',',$city_list_id);
		if($citylist_id){
			foreach($citylist_id as $key => $val){
				if(!$val) unset($citylist_id[$key]);
			}
		}
		$citylist_ids = implode(',',$citylist_id);

		$citylist =M('city')->field('id,name,pid')->where('id in('.$citylist_ids.') AND pid = '.$_COOKIE['head_city'].'')->select();

		//获取户型 只显示 已有楼盘
		$room_arr = M('property_housetype')->field('house_room')->group('house_room')->select();
		foreach($room_arr as $k=>$v){
			$room[] .= $v['house_room'];
		}

		//获取分类 只显示 已有分类
		$property_cate = M('property_cate')->field('id,name')->where('pid = 1')->select();
		//print_r($list);

		$this->assign('search',$search);
		$this->assign('area',$area);
		$this->assign('house_type',$house_type);
		$this->assign('tenement',$tenement);
		$this->assign('citylist', $citylist);
		$this->assign('room', $room);
		$this->assign('property_cate', $property_cate);
		$this->assign('list', $list);
		$this->assign('page', $page);
    	$this->assign('setTitle', '热销楼盘');
        $this->_config_seo();
        $this->display();
    }

    /*
    *@Descriptions：楼盘详情
    *@Date:2015-1-19
    *@Author: wsj
    */
    public function detail(){
		$fph = C('DB_PREFIX');
		$time = time();
		$id = $this->_get('pid','intval');
		if(!$id)
		$this->error('参数出错');
	
		$where = '1=1';
		$where .= ' AND id = '.$id;
		$list = M('property')->where($where)->find();
		$list['kefu_tel']=C('pin_kefu_tel');//客服
		$list['intentioncount'] = M('myclient_property')->where(array('pid'=>$info['id']))->count('id') * 3 + $list['intentioncount'];//意象客户
		$cate = M('property_cate')->field('id,name')->where('id in('.$list['property_type'].')')->select();
		$list['leixing'] = $cate;
		//楼盘分类
		$list['leixing_i'] = '';
		foreach($cate as $k=>$v){
			$list['leixing_i'] .= $v['name'].',';
		}
		$list['leixing_i'] = substr($list['leixing_i'],0,-1);
	
		//建筑类型
		if($list['building_type']!=''){
			$building_type = M('property_cate')->where('id in('.$list['building_type'].')')->select();
			$list['building_type'] = '';
			foreach($building_type as $k=>$v){
				$list['building_type'] .= $v['name'].',';
			}
			$list['building_type'] = substr($list['building_type'],0,-1);
		}
	
		//装修情况
		$list['get_decoration'] = '';
		if($list['decoration']!=''){
			$decoration = M('property_cate')->where('id in('.$list['decoration'].')')->select();
			foreach($decoration as $k=>$v){
				$list['get_decoration'] .= $v['name'].',';
			}
			$list['get_decoration'] = substr($list['get_decoration'],0,-1);
		}

		//周边地铁
		$metro_info_m = '';
		if($list['metro'] !=''){
            $metro_info_m = $this->metro($list['metro']);
			/*$metro = strpos($list['metro'],'|');
			$metro_info =array();
			if($metro === false){
				//不包含;
				$metro = explode('&', $list['metro']);
				$metro_arr = M('metro')->field('id,city_id,pid,name')->where('id='.$metro[0])->find();
				$metro_info['name'] = $metro_arr['name'];//几号线
				$metro_info['metro_name'] = M('metro')->where('id='.$metro[1])->getField('name');
				$metro_info['city_name'] = M('city')->where('id='.$metro_arr['city_id'])->getField('name');
				$metro_info_m = $metro_info['city_name'].':'.$metro_info['name'].':'.$metro_info['metro_name'];
			}else{
				$metro = explode('|', $list['metro']);
				foreach($metro as $k1=>$v1){
					$metro = explode('&', $v1);
					$metro_arr = M('metro')->field('id,city_id,pid,name')->where('id='.$metro[0])->find();
					$metro_info['city_name'] = M('city')->where('id='.$metro_arr['city_id'])->getField('name');
					$metro_info['name'] = $metro_arr['name'];
                    //$metro_info['metro_name'] = M('metro')->where('id in('.$metro[1].'')->getField('name');
                    foreach($metro[1] as $val){
                        $metro_info['metro_name'] .= M('metro')->where('id ='.$val)->getField('name');
                    }
					$metro_info_m .= $metro_info['city_name'].':'.$metro_info['name'].':'.$metro_info['metro_name'].', ';
				}
				$metro_info_m = substr($metro_info_m,0,-2);
                print_r($metro);
			}*/
            //$metro_info_m = substr($metro_info_m,0,-2);
		}

		$this->assign('metro_info_m',$metro_info_m);
		//标签 pid 1 为合作
		$bool = M('property_cooperation')->where('term_start < "'.$time.'" AND term_end > "'.$time.'" AND pid ='.$list['id'])->find();
		$list['hz_pid'] =1;
		if(empty($bool)){
			$list['hz_pid'] = 0;
		}
		//赚佣金
		$commission = M('property_commission')->field('id,property_type,price,each,money,stores_id')->where('pid ='.$list['id'].' AND (stores_id="" or stores_id is null)')->order('add_time DESC')->select();
		foreach ($commission as $key => $value) {
			$catearr = M('property_cate')->where('id in('.$value['property_type'].')')->select();
			$commission[$key]['cate'] = '';
			foreach ($catearr as $ke => $va) {
			   $commission[$key]['cate'] .= $va['name'].',';
			}
			$commission[$key]['cate'] = substr($commission[$key]['cate'],0,-1);
		}
		$this->assign('commission',$commission);
	
		//楼盘特色
		$list['cate_feature'] = '';
		if($list['property_feature']!=''){
			$cate_feature = M('property_cate')->field('id,name')->where('id in('.$list['property_feature'].')')->select();
			// foreach($cate_feature as $k=>$v){
			// 	$list['cate_feature'] .= $v['name'].',';
			// }
			//楼盘分类
			$list['cate_feature'] = $cate_feature;
			//$list['cate_feature'] = substr($list['cate_feature'],0,-1);
		}
	
		//图片显示
		$pic_img = M('property_img')->where('type = 2 AND status = 1 AND pid ='.$id)->select();
		
		//区域显示
		$move_in = $area = '';
		$listspid = M('city')->where('id ='.$list['city_id'])->find();
		$count_spid = explode('|',$listspid['spid']);
		if(count($count_spid) == 3){
			$list['area'] = M('city')->where('id ='.$listspid['pid'])->getField('name');
			$list['area'] .= '  '.$listspid['name'];
			$area .= $listspid['id'].',';
		}else if(count($count_spid) == 4){
			$list['area'] = M('city')->where('id ='.$listspid['pid'])->getField('name');
			$list['area'] .= '  '.M('city')->where('id ='.$listspid['id'])->getField('name');
			$area .= $listspid['pid'].',';
		}else{
			$area .= $listspid['id'].',';
			$list['area'] = M('city')->where('id ='.$listspid['id'])->getField('name');
		}
	
		$hxlist = M('property_housetype')->where(array('pid'=>$id,'status'=>1))->select();//在售户型
		if(!empty($area)){
			$area = substr($area,0,-1);
			$move_in =' AND A.city_id in(select id from fph_city where id = '.$area.' or spid LIKE "%'.$area.'%")';
		}
		$move_list = M('property')->field('A.id,A.title,A.img_thumb,A.city_id,A.item_price,A.prefer,A.list_price')
				  ->table("{$fph}property AS A
					  INNER JOIN {$fph}property_cooperation AS B ON A.id = B.pid
					  INNER JOIN {$fph}city AS C ON  A.city_id = C.id
					  ")
				  ->where(' B.term_start < "'.$time.'" AND B.term_end > "'.$time.'"  '.$move_in)
				  ->order('A.add_time DESC')
				  ->limit(0,3)
				  ->select();
	
		foreach($move_list  as $k=>$v){
			//设置 只显示区域
			$listspid = M('city')->where('id ='.$v['city_id'])->find();
			$count_spid = explode('|',$listspid['spid']);
			if(count($count_spid) == 3){
				$move_list[$k]['city'] = $listspid['name'];
			}else if(count($count_spid) == 4){
				$move_list[$k]['city'] = M('city')->where('id ='.$listspid['pid'])->getField('name');
			}else{
				$move_list[$k]['city'] = M('city')->where('id ='.$listspid['id'])->getField('name');
			}
		}
		$this->assign('area_id', $area);
		$this->assign('move_list', $move_list);
		$this->assign('hxlist', $hxlist);
    	$this->assign('http_host', $_SERVER['HTTP_HOST']);
		$this->assign('pic_img',$pic_img);
		$this->assign('favorites_sum',  M('favorites')->where(array('pid'=>$id))->count('id'));//统计有多少收藏的
		$this->assign('info',$list);
		$this->assign('setTitle', '楼盘详情');
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
	
    public function apartment(){
		$id  = $this->_get('id','intval');
		$pid = $this->_get('pid','intval');
		if(!$id || !$pid){
			$this->error('参数出错');
			exit;
		}
		$hxlist = M('property_housetype')->field('id,house_img')->where(array('pid'=>$pid))->select();
		$this->assign('hxlist', $hxlist);
		
		$info = M('property_housetype')->where(array('id'=>$id))->find();
		switch($info['status']){
			case 1:
				$hxtitle = '在售';
				break;
			case 2:
				$hxtitle = '售完';
				break;
		}
		$info['hxtitle'] = $hxtitle;
		$this->assign('info', $info);
		$this->assign('setTitle', $this->_mod->where('id='.$pid)->getField('title'));
        $this->_config_seo();
        $this->display();
    }
	
	//ajax读取户型
	public function ajax_apartment(){
		$id  = $this->_post('id','intval');
		
		!$id && $this->ajaxReturn(0,'参数出错');
		
		$info = M('property_housetype')->where(array('id'=>$id))->find();
		switch($info['status']){
			case 1:
				$hxtitle = '在售';
				break;
			case 2:
				$hxtitle = '售完';
				break;
		}
		$info['hxtitle'] = $hxtitle;
		if($info){
			$this->ajaxReturn(1,'',$info);
		}else{
			$this->ajaxReturn(0,'参数出错');
		}
	}
	
}
