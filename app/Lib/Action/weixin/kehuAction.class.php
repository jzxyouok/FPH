<?php
class kehuAction extends weixin_userbaseAction {
    
    /*
    *我的客户显示
    */
    public function index(){
		//表前缀获取
		$fph = C('DB_PREFIX');
		$uid = $this->visitor->info['id'];
		$search = array();
		$search_title = $this->_get('search_title','trim');
		$status = $this->_get('status','trim');
		$search_status = $this->_get('search_status','intval');
		$where = "with_look = 1 AND uid = ".$uid;
        if($status){
    		$statusarr = explode(',', $status);
    		$where .= ' AND status ="'.$statusarr[0].'"  AND status_cid ="'.$statusarr[1].'"';
    	}
		$search['status'] = $status;
		$search['search_title'] = $search_title;
		$search['search_status'] = $search_status;
		if($search_title){
			//楼盘
			$property = M('property')->field('id')->where('title like "%'.$search_title.'%"')->select();
			if(!empty($property)){
				foreach ($property as $v) {
	    			$propertyid .= $v['id'].',';
	   			}
	   			$where .= " AND (property in(".substr($propertyid,0,-1).")  ";
			}else{
				$where .= " AND (property in(-1)  ";
			}
			//会员
			$myclient = M('myclient')->field('id')->where('name like "%'.$search_title.'%" or mobile="'.$search_title.'"')->select();
			if(!empty($myclient)){
				foreach ($myclient as $v) {
	    			$myclientid .= $v['id'].',';
	   			}
	   			$where .= " OR pid in(".substr($myclientid,0,-1).") ) ";
			}else{
				$where .= "   )";
			}

		}
 	    $list = M('myclient_property')->query("select *, FROM_UNIXTIME(add_time,'%Y%m%d') days from fph_myclient_property where $where group by days order by add_time DESC limit 0,10");
 		$search['count_user'] = M('myclient_property')->where($where.' AND add_time !=""')->count('id');
		foreach ($list as $key => $value) {
			$data = date('Y-m-d',$value['add_time']);
			$start_time = strtotime($data);
			$end_time = strtotime($data.' 23:59:59');
			$list[$key]['my_p'] = M('myclient_property')->where($where.' AND add_time between '.$start_time.' and '.$end_time."")->select();
			foreach ($list[$key]['my_p'] as $k => $v) {
				$list[$key]['my_p'][$k]['title'] = M('property')->where('id='.$v['property'])->getField('title');
				$list[$key]['my_p'][$k]['name'] = M('myclient')->where('id='.$v['pid'])->getField('name');
				if($v['status'] == 6 AND $v['status_cid'] == 0)
				{
					$list[$key]['my_p'][$k]['zhongzhi'] = M('myclient_status')->where('status = 5 AND mpid ='.$v['id'])->getfield('status_cid');
				}
			}
		}

		$this->assign('search',$search);
		$this->assign('list',$list);
		$this->assign('count_list',count($list));
		//累计客户
		$mycount = M('myclient_property')->where('uid ='.$uid)->group('pid')->select();
		$this->assign('mycount',count($mycount));
		//累计成交
		$chengjiao = M('myclient_property')->where("status = 7 AND uid = ".$uid)->group('pid')->select();
		$this->assign('chengjiao',count($chengjiao));
		$this->assign('setTitle', '我的客户');
        $this->_config_seo();
        $this->display();
    }

    //报备客户
    public function ajax_baobei(){
		//表前缀获取
		$fph = C('DB_PREFIX');
		$uid = $this->visitor->info['id'];
		$search = array();
		$page = $this->_post('page','intval');
	    $status = $this->_post('status','trim');
	    $search_title = $this->_post('search_title','trim');
	    $search_status = $this->_post('search_status','intval');
	    $start = $page*10;

		$where = "with_look = 1 AND uid = ".$uid;
        if($status){
    		$statusarr = explode(',', $status);
    		$where .= ' AND status ="'.$statusarr[0].'"  AND status_cid ="'.$statusarr[1].'"';
    	}
		$search['status'] = $status;
		$search['search_title'] = $search_title;
		$search['search_status'] = $search_status;
		if($search_title){
			//楼盘
			$property = M('property')->field('id')->where('title like "%'.$search_title.'%"')->select();
			if(!empty($property)){
				foreach ($property as $v) {
	    			$propertyid .= $v['id'].',';
	   			}
	   			$where .= " AND (property in(".substr($propertyid,0,-1).")  ";
			}else{
				$where .= " AND (property in(-1)  ";
			}
			//会员
			$myclient = M('myclient')->field('id')->where('name like "%'.$search_title.'%" or mobile="'.$search_title.'"')->select();
			if(!empty($myclient)){
				foreach ($myclient as $v) {
	    			$myclientid .= $v['id'].',';
	   			}
	   			$where .= " OR pid in(".substr($myclientid,0,-1).") ) ";
			}else{
				$where .= "   )";
			}
		}

 	$list = M('myclient_property')->query("select *, FROM_UNIXTIME(add_time,'%Y%m%d') days from fph_myclient_property where $where group by days order by add_time DESC  limit $start,10");
		//$list['count'] = 0;
		$search['count_user'] = M('myclient_property')->where($where.' AND add_time !=""')->count('id');
		foreach ($list as $key => $value) {
			$data = date('Y-m-d',$value['add_time']);
			$start_time = strtotime($data);
			$end_time = strtotime($data.' 23:59:59');
			$list[$key]['my_p'] = M('myclient_property')->where($where.' AND add_time between '.$start_time.' and '.$end_time."")->select();
			foreach ($list[$key]['my_p'] as $k => $v) {
				//$list['count'] += 1;
				$list[$key]['my_p'][$k]['title'] = M('property')->where('id='.$v['property'])->getField('title');
				$list[$key]['my_p'][$k]['name'] = M('myclient')->where('id='.$v['pid'])->getField('name');
				if($v['status'] == 6 AND $v['status_cid'] == 0){
					$list[$key]['my_p'][$k]['zhongzhi'] = M('myclient_status')->where('status = 5 AND mpid ='.$v['id'])->getfield('status_cid');
				}
			}
		}
	$html = '';
	foreach ($list as $key => $vo) {
		if($vo['add_time']){
	      	$html .='<label class="FORM_LABEL">'.date('Y-m-d',$vo['add_time']).'</label>';
	      	$html .='<section class="blocks">';
	        $html .='<ul class="display_list">';
          foreach ($vo['my_p'] as $k => $val) {
          	$html .='<li><a href="'.U('weixin/kehu/customer_detail',array('id'=>$val['pid'],'pro_id'=>$val['id'],'with_look'=>$val['with_look'])).'"><span class="title">'.$val['name'].'</span><span class="mobile">'.$val['title'].'</span>';
          	$str = '';
      		if($val['status']==1){
      			//$str ='带看申请';
      			$str = '<span class="status">带看申请</span>';
      		}
            if($val['status']==2){
            	if($val['status_cid']==1){
            		//$str = '邀约成功';
            		$str = '<span class="status">邀约成功</span>';
            	}else{
            		$str = '<span class="status">邀约失败</span>';
            	}
      		}
      		if($val['status']==3){
            	if($val['status_cid']==1){
            		$str = '<span class="status">开发商确认</span>';
            	}else{
            		$str = '<span class="status">开发商拒绝</span>';
            	}
      		}
      		if($val['status']==4){
            	if($val['status_cid']==1){
            		$str = '<span class="status">已到访</span>';
            	}else{
            		$str = '<span class="status">未到访</span>';
            	}
      		}
            if($val['status']==5){
            	if($val['status_cid']==1){
            		$str = '<span class="status lighten">支付意向金</span>';
            	}elseif($val['status_cid']==2){
            		$str = '<span class="status">支付团购费</span>';
            	}else{
            		$str = '<span class="status">意向终止</span>';
            	}
      		}
             if($val['status']==6){
            	if($val['status_cid']==1){
            		$str = '<span class="status lighten">支付定金</span>';
            	}else{
            		if($val['zhongzhi']==1){
            			$str = '<span class="status">退回意向金</span>';
            		}else{
            			$str = '<span class="status">退回团购费</span>';
            		}
            	}
      		}
            if($val['status']==7){
      			$str ='<span class="status lighten">签约成交</span>';
      		}
            if($val['status']==8){
      			$str ='<span class="status">违约</span>';
      		}
      		if($val['status']==9){
      			$str ='<span class="status">失效</span>';
      		}
            $html .= $str;
          	$html .= '<i></i></a></li>';
          }
        $html .= '</ul>';
      	$html .= '</section>';
      	}
	}
		if($html){
			$this->ajaxReturn(1,'',$html);
	    }else{
		    $this->ajaxReturn(0,'别滑动了，已经到底了...');
	    }
	
    }
    
    public function add() {
	
	if(IS_POST){
	    foreach ($_POST as $key=>$val) {
		$_POST[$key] = Input::deleteHtmlTags($val);
	    }
	    $name      = $this->_post('name','trim');
	    $mobile    = $this->_post('mobile','trim');
	    $area      = $this->_post('area','intval');
	    $price     = $this->_post('price','intval');
	    $time      = time();
	    if(!checkusername($name)){
		    $this->ajaxReturn(0,'客户姓名填写错误');
	    }
	    if(!checkMobile($mobile)){
		    $this->ajaxReturn(0,'手机号码填写错误');
	    }
	    if($area=='' || $price==''){
		    $this->ajaxReturn(0,'请选择意向区域和价格');
	    }
	    $mob_count = M('myclient')->where("mobile='".$mobile."'")->getField('id');
	    if(empty($mob_count)){
		$data2['uid']      = $this->visitor->info['id'];
		$data2['name']      = $name;
		$data2['mobile']    = $mobile;
		$data2['gender'] = 2;
		M('myclient')->add($data2);
			$mob_count['id'] = M('myclient')->getLastInsID();
	    }else {
			$this->ajaxReturn(0,'该客户已经被报备过');
	    }
	    $data['pid']       = $mob_count['id'];
	    $data['area']      = $area;
	    $data['price']     = $price;
	    $data['add_time']  = $time;
	    $data['update_time'] = $time;
	    
	    if($return !== M('myclient_twitter')->add($data)){
		    $this->ajaxReturn(1,'推客成功');
	    }else{
		    $this->ajaxReturn(1,'推客失败');
	    }
	}
	$this->assign('setTitle', '我要推客');
        $this->_config_seo();
        $this->display();
    }
	
	
    /*
    *客户详情
    */
    public function customer_detail()
    {
		//表前缀获取
		$fph = C('DB_PREFIX');
		$uid = $this->visitor->info['id'];
 		$pro_id = $this->_get('pro_id','intval');
		$id = $this->_get('id','intval');
		$with_look = $this->_get('with_look','intval');
		if(!$id || !$pro_id){
			$this->_404();
		}
		$user = M('myclient')->where('id ='.$id)->find();
		$info = M('myclient_property')->where("uid = ".$uid." AND with_look = ".$with_look." AND pid = ".$id.' AND id='.$pro_id)->find();
		$info['property_info'] = M('property')->where('id='.$info['property'])->find();
		$list = M('myclient_status')->where('mpid ='.$info['id'])->group('status')->select();
		$listliu = M('myclient_status')->where('status = 6 AND mpid ='.$info['id'])->find();
		if(!empty($listliu)){
			$listliu['deposit'] = explode(",",$listliu['deposit']);
			$listliu['info'] = explode(",",$listliu['info']);
			$listliu['signing_time'] = explode(",",$listliu['signing_time']);
			$this->assign('listliu',$listliu);
		}	
		$endlist = end($list);
		if($endlist['status'] == 7){
			$str = '';
			$info_s = M('myclient_status')->where('status = 7 AND mpid ='.$info['id'])->order('id')->select();
			foreach ($info_s as $key => $value) {
				$str .='您的客户已购买了'.$value['measure'].'㎡的房源，总价为:'.$value['total_price'].'元<br>';
			}
			$endlist['info'] = $str;
		}
		$this->assign('info', $info);
		$this->assign('list', $list);
		$this->assign('user', $user);
		$this->assign('endlist',$endlist);
		$this->assign('setTitle', '客户详情');
        $this->_config_seo();
        $this->display();
    }
    
    //意向楼盘
    public function ajax_property() {
		if(IS_POST){
			$fph = C('DB_PREFIX');
			$propertyarr = $this->_post('propertyarr','trim');//2,3,5
			$city = M('property')->field('id,title')
						->order('add_time DESC')
						->select();
			if($city){
				$str = '';
				foreach($city as $key=>$val){
					foreach($propertyarr as $v){
						if($v==$val['id']){
							$city[$key]['cur']=1;
						}
					}
					if($city[$key]['cur']){
						$str .= '<dd><a href="javascript:;" rel='.$val['id'].' class="J_click_property cur">'.$val['title'].'</a></dd>';
					}else{
						$str .= '<dd><a href="javascript:;" rel='.$val['id'].' class="J_click_property">'.$val['title'].'</a></dd>';
					}
				}
				$this->ajaxReturn(1,'',$str);
			}else{
				$this->ajaxReturn(0,'无数据');
			}
		}
    }
    
    //意向区域
    public function ajax_city(){
	if(IS_POST){
		$area = $this->_post('area','intval');
		$city = M('city')->field('id,name')->where('pid=803')->select();
		if($city){
		$str = '';
		foreach($city as $key=>$val){
			$str .='<li><a href="javascript:;" rel='.$val['id'].'>'.$val['name'].'</a></li>';
		}
		$this->ajaxReturn(1,'',$str);
		}else{
		$this->ajaxReturn(0,'无数据');
		}
	}
    }
    
    //意向价格
    public function ajax_price(){
	if(IS_POST){
		$price = $this->_post('price','intval');
		$city = M('ideal_price')->field('id,title')->select();
		if($city){
		$str = '';
		foreach($city as $key=>$val){
			if($val['title'] == 500)
			{
			    $str .='<li><a href="javascript:;" rel='.$val['id'].'>'.$val['title'].'万以上</a></li>';
			}
			else
			{
			    $str .='<li><a href="javascript:;" rel='.$val['id'].'>'.$val['title'].'万</a></li>';
			}
			
		}	
		$this->ajaxReturn(1,'',$str);
		}else{
		$this->ajaxReturn(0,'无数据');
		}
	}
    }
	
    //添加意向楼盘
    public function ajax_property_add() {
	if(IS_POST){
		$fph = C('DB_PREFIX');
		$propertyarr = $this->_post('propertyarr','trim');//2,3,5
		$id          = $this->_post('id','intval');
		!$id && $this->ajaxReturn(0,L('illegal_parameters'));
		$list = M('myclient_property')->field('property')->where(array('pid'=>$id))->select();
		$property_id = '';
		foreach($list as $key => $val){
			if(!$key){
				$property_id .= $val['property'];
			}else{
				$property_id .= ','.$val['property'];
			}
		}
		if($list){

			$time =time();
			$city = M('property')->field('A.id,A.title')
					->table("{$fph}property AS A LEFT JOIN {$fph}property_cooperation AS B ON A.id=B.pid")
					->where("A.id NOT IN ($property_id) and B.term_end>=$time and B.term_start<=$time")
					->order('A.add_time DESC')
					->select();
		}
		if($city){
			$str = '';
			foreach($city as $key=>$val){
				foreach($propertyarr as $v){
					if($v==$val['id']){
						$city[$key]['cur']=1;
					}
				}
				if($city[$key]['cur']){
					$str .= '<dd><a href="javascript:;" rel='.$val['id'].' class="J_click_property_add cur">'.$val['title'].'</a></dd>';
				}else{
					$str .= '<dd><a href="javascript:;" rel='.$val['id'].' class="J_click_property_add">'.$val['title'].'</a></dd>';
				}
			}
			$this->ajaxReturn(1,'',$str);
		}else{
			$this->ajaxReturn(0,'无数据');
		}
	}
    }
	
    //添加楼盘到数据库
    public function detail_ajax_property_add(){
	    $property = $this->_post('property','trim');
	    $id       = $this->_post('id','intval');
	    !$id && $this->ajaxReturn(0,L('illegal_parameters'));
	    $property_count = count($property);
	    $count = M('myclient_property')->where(array('pid'=>$id))->count('id');
	    $plus_num = $property_count+$count;
	    if($plus_num > 3){
		    $this->ajaxReturn(0,'最多只能添加三个楼盘');
	    }
	    if($property){
		    foreach($property as $key=>$val){
			    $data['property']  = $val;
			    $data['pid']  = $id;
			    $data['update_time'] = time();
			    M('myclient_property')->add($data);
		    }
		    $this->ajaxReturn(1,'添加成功');
	    }else{
		    $this->ajaxReturn(0,L('illegal_parameters'));
	    }
	    
    }
	
	
    //修改意向区域
    public function edit_ajax_city(){
	if(IS_POST){
		$city = M('city')->field('id,name')->where('pid=803')->select();
		if($city){
		$str = '';
		foreach($city as $key=>$val){
			$str .= '<dd><a href="javascript:;" rel='.$val['id'].' class="J_click_area_edit">'.$val['name'].'</a></dd>';
		}
		$this->ajaxReturn(1,'',$str);
		}else{
		$this->ajaxReturn(0,'无数据');
		}
	}
    }
	
    public function inster_ajax_city(){
	    $id       = $this->_post('id','trim');
	    $area_id  = $this->_post('area_id','intval');
	    !$id && $this->ajaxReturn(0,L('illegal_parameters'));
	    !$area_id && $this->ajaxReturn(0,L('illegal_parameters'));
	    if($return !== M('myclient')->where(array('id'=>$id))->save(array('area'=>$area_id))){
		    $this->ajaxReturn(1,'添加成功');
	    }else{
		    $this->ajaxReturn(1,'添加失败');
	    }
	    
    }
    
    //修改价格
    public function edit_ajax_price(){
	    if(IS_POST){
		    $city = M('ideal_price')->field('id,title')->select();
		    if($city){
		    $str = '';
		    foreach($city as $key=>$val){
			    $str .= '<dd><a href="javascript:;" rel='.$val['id'].' class="J_click_price_edit">'.$val['title'].'</a></dd>';
		    }	
		    $this->ajaxReturn(1,'',$str);
		    }else{
		    $this->ajaxReturn(0,'无数据');
		    }
	    }
    }
    
    public function inster_ajax_price(){
	    $id       = $this->_post('id','trim');
	    $price_id  = $this->_post('price_id','intval');
	    !$id && $this->ajaxReturn(0,L('illegal_parameters'));
	    !$price_id && $this->ajaxReturn(0,L('illegal_parameters'));
	    if($return !== M('myclient')->where(array('id'=>$id))->save(array('price'=>$price_id))){
		    $this->ajaxReturn(1,'添加成功');
	    }else{
		    $this->ajaxReturn(1,'添加失败');
	    }
	    
    }

    
}