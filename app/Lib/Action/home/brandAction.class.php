<?php
class brandAction extends frontendAction {
    
    public function index() {
        $fph = C('DB_PREFIX');
        $eg     =  $this->_get('eg','trim');
        $this->assign('eg', $eg);
	$time = time();
        
        $e = array('A'=>'a', 'B'=>'b', 'C'=>'c', 'D'=>'d', 'E'=>'e', 'F'=>'f', 'G'=>'g', 'H'=>'h', 'I'=>'i', 'J'=>'j', 'K'=>'k', 'L'=>'l', 'M'=>'m', 'N'=>'n', 'O'=>'o', 'P'=>'p', 'Q'=>'q', 'R'=>'r', 'S'=>'s', 'T'=>'t', 'U'=>'u', 'V'=>'v', 'W'=>'w', 'X'=>'x', 'Y'=>'y', 'Z'=>'z');
        $this->assign('e', $e);
        
        //获取楼盘品牌表
        $where = 'status = 2';
        $search_name     = $this->_get('search_name', 'trim');
        if($search_name){
            $where .=' AND business like "%'.$search_name.'%"';
        }
        if($eg){
            $where .= ' AND letter like "'.$eg.'%"';
        }
        
        $user_count = M('property_pinpai')->where($where)->count('id');
        $pagesize=10;
        $pager = new Page($user_count, $pagesize);
        $list = M('property_pinpai')->field("id,logo,banner_img,business_jian,business")->where($where)->limit($pager->firstRow.','.$pager->listRows)->order('ordid ASC,id DESC')->select();
        $page = $pager->show();
	
	$field = $fiecid = $where_field  = '';
	
	//城市过滤
	if(!empty($_COOKIE['head_city']))
	{
	    
	    $headcity = M('city')->field('id')->where('id = '.$_COOKIE['head_city'].' or spid RLIKE "[[:<:]]'.$_COOKIE['head_city'].'[[:>:]]"')->select();
	    foreach($headcity as $k=>$v)
	    {
		$fiecid .= $v['id'].',';
	    }
	    $fiecid = substr($fiecid,0,-1);
	    $c_where = ' AND A.city_id in('.$fiecid.')';
	}
    
	//获取合作楼盘 id 进行排序
	$cooperation = M('property_cooperation')->field('pid')->where('term_start < '.$time.' AND term_end > '.$time.'')->order('pid ASC')->select();
	foreach($cooperation as $k=>$v)
	{
	    $field .= $v['pid'].',';
	}
	
	//判断是否有合作楼盘 如果没有 将不进行 根据 合作楼盘排序
	if(empty($cooperation))
	{
	    $where_field .=' A.add_time DESC';
	}
	else
	{
	    $field = substr($field,0,-1);
	    $where_field .=' FIELD(A.id ,'.$field.') DESC,A.add_time DESC';
	}


        foreach($list as $k=>$v)
        {
	    $list[$k]['property']  = M('property')->field('A.id,A.title')->table("{$fph}property AS A
								INNER JOIN {$fph}city AS C ON  A.city_id = C.id")
						->where('A.status = 1 AND A.sales =1 AND A.pin_id='.$v['id'].$c_where)
						->limit('0,8')
						->order($where_field)
						->select();
	    //$list[$k]['property'] = M('property')->where('sales = 1 AND pin_id ='.$v['id'])->limit('0,8')->order('id DESC')->select();
	    foreach ($list[$k]['property'] as $k1=>$v1)
	    {
		$bool = M('property_cooperation')->where('term_start < "'.$time.'" AND term_end > "'.$time.'" AND pid ='.$v1['id'])->find();
		$list[$k]['property'][$k1]['pid'] =1;
		if(empty($bool))
		   $list[$k]['property'][$k1]['pid'] = 0;
		if($v1['status'] != 1)
		  $list[$k]['property'][$k1]['pid'] = 0;
	    }
            $list[$k]['zaisou'] = M('property')->where('sales = 1 AND pin_id ='.$v['id'])->count();
            $list[$k]['yusou'] = M('property')->where('sales = 2 AND pin_id ='.$v['id'])->count();
        }
        $this->assign('list', $list);
        $this->assign('page', $page);
		
	$this->assign('search_name', $search_name);
        $this->assign('setTitle', '品牌汇');
        $this->_config_seo();
        $this->display();
	
    }
    
    //品牌详情介绍
    public function detailed() {
		$fph = C('DB_PREFIX');
		$time = time();
        $id = $this->_get('id','intval');
        if($id){
            $where = 'id ='.$id;
        }
		$list['banner_img'] = str_replace('_s','_l',$list['banner_img']);
        $list = M('property_pinpai')->where($where)->find();
	
		//热销楼盘
        $list['property'] = M('property')->field('id,title,img_thumb,item_price,open_time,property_type')->where('sales = 1 AND pin_id ='.$list['id'])->limit('0,3')->order('id DESC')->select();
		foreach ($list['property'] as $k1=>$v1){
			$bool = M('property_cooperation')->where('term_start < "'.$time.'" AND term_end > "'.$time.'" AND pid ='.$v1['id'])->find();
			$list['property'][$k1]['pid'] =1;
			if(empty($bool))
			   $list['property'][$k1]['pid'] = 0;
			if($v1['status'] != 1)
			 $list['property'][$k1]['pid'] = 0;
			$cate = M('property_cate')->where('id in('.$v1['property_type'].')')->select();
			$list['property'][$k1]['leixing'] = '';
			foreach($cate as $k=>$v){
				$list['property'][$k1]['leixing'] .= $v['name'].',';
			}
			//楼盘分类
			$list['property'][$k1]['leixing'] = substr($list['property'][$k1]['leixing'],0,-1);
		}

        $list['zaisou'] = M('property')->where('sales = 1 AND pin_id ='.$list['id'])->count();
        $list['yusou'] = M('property')->where('sales = 2 AND pin_id ='.$list['id'])->count();
        $this->assign('list', $list);
        $this->assign('setTitle', $list['business']);
        $this->_config_seo();
        $this->display();
    }
    
    //品牌在售介绍
    public function onsell(){
        $id = $this->_get('id','intval');
		$time = time();
        if($id){
            $where = 'id ='.$id;
        }
        $list = M('property_pinpai')->where($where)->find();
        $list['property'] = M('property')->field('id,open_time,img_thumb,title,address,property_type,info')->where('sales = 1 AND pin_id ='.$list['id'])->order('open_time ASC,id DESC')->select();
        foreach($list['property'] as $k1=>$v1){
			$bool = M('property_cooperation')->where('term_start < "'.$time.'" AND term_end > "'.$time.'" AND pid ='.$v1['id'])->find();
			$list['property'][$k1]['pid'] =1;
			if(empty($bool))
			   $list['property'][$k1]['pid'] = 0;
			if($v1['status'] != 1)
			  $list['property'][$k1]['pid'] = 0;
		  
			$cate = M('property_cate')->where('id in('.$v1['property_type'].')')->select();
			$list['property'][$k1]['leixing'] = '';
			foreach($cate as $k=>$v){
				$list['property'][$k1]['leixing'] .= $v['name'].',';
			}
			//楼盘分类
			$list['property'][$k1]['leixing'] = substr($list['property'][$k1]['leixing'],0,-1);
        }
        $list['zaisou'] = M('property')->where('sales = 1 AND pin_id ='.$list['id'])->count();
        $list['yusou'] = M('property')->where('sales = 2 AND pin_id ='.$list['id'])->count();
        $this->assign('list', $list);
        $this->assign('setTitle', $list['business']);
        $this->_config_seo();
        $this->display();
    }
    
    //品牌在售介绍
    public function tosell()
    {
        $id = $this->_get('id','intval');
		$time = time();
        if($id){
            $where = 'id ='.$id;
        }
        $list = M('property_pinpai')->where($where)->find();
		$list['property'] = M('property')->field('id,open_time,img_thumb,title,address,property_type,info')->where('sales = 2 AND pin_id ='.$list['id'])->order('open_time ASC,id DESC')->select();
        foreach($list['property'] as $k1=>$v1){
			$bool = M('property_cooperation')->where('term_start < "'.$time.'" AND term_end > "'.$time.'" AND pid ='.$v1['id'])->find();
			$list['property'][$k1]['pid'] =1;
			if(empty($bool))
			   $list['property'][$k1]['pid'] = 0;
			if($v1['status'] != 1)
			  $list['property'][$k1]['pid'] = 0;
			
			$cate = M('property_cate')->where('id in('.$v1['property_type'].')')->select();
			$list['property'][$k1]['leixing'] = '';
			foreach($cate as $k=>$v){
				$list['property'][$k1]['leixing'] .= $v['name'].',';
			}
			//楼盘分类
			$list['property'][$k1]['leixing'] = substr($list['property'][$k1]['leixing'],0,-1);
		}
        $list['zaisou'] = M('property')->where('sales = 1 AND pin_id ='.$list['id'])->count();
        $list['yusou'] = M('property')->where('sales = 2 AND pin_id ='.$list['id'])->count();
        $this->assign('list', $list);
        $this->assign('setTitle', $list['business']);
        $this->_config_seo();
        $this->display();
    }
}