<?php
/**
 * 项目跟进
 * @author lishun
 */
class projectAction extends weixin_userbaseAction {
    public function _initialize() {
		parent::_initialize();
		$this->_mod_p = D('property');//楼盘表
		$this->_mod_c = D('property_contact');//楼盘联系人
		$this->_mod_l = D('property_log');//项目日志
	}
	public function project_list(){
		$uid = $this->visitor->info['id'];
		$search = array();
		$where = ' 1 = 1 ';
		$fph = C('DB_PREFIX');
		$select_city = $this->_get('select_city','intval');
		
		$str_pro = '';
		if($select_city){
		    $search['select_city'] = $select_city;
		    $search['select_name'] = M('city')->where('id = '.$select_city)->getField('name');
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
		$where .=' AND city_id in(select id from fph_city where id = '.$select_city_id.$str_pro.' or spid RLIKE "[[:<:]]'.$select_city_id.'[[:>:]]")';
		$list = $this->_mod_p->field('id,title,address')->where($where)->order(' id DESC ')->limit(0,10)->select();
		$search['count'] = count($this->_mod_p->where($where)->select());
		//区域显示
		$city = $this->_mod_p->field('city_id')->where('city_id !=0 ')->group('city_id')->select();
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
		$this->assign('count', $count);
		$this->assign('list', $list);
		$this->assign('countlp', count($list));
		$this->assign('citylist', $citylist);
		$this->assign('setTitle', '项目列表');
		$this->_config_seo();
		$this->display();
	}
	public function ajax_project_list(){
		$uid = $this->visitor->info['id'];
		$search = array();
		$where = ' 1 = 1 ';
		$fph = C('DB_PREFIX');
		$select_city = $this->_post('select_city','intval');
		$page = $this->_post('page','intval');
		$start = $page*10; 
		$str_pro = '';
		if($select_city){
		    $search['select_city'] = $select_city;
		    $search['select_name'] = M('city')->where('id = '.$select_city)->getField('name');
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
		$where .=' AND city_id in(select id from fph_city where id = '.$select_city_id.$str_pro.' or spid RLIKE "[[:<:]]'.$select_city_id.'[[:>:]]")';
		$list = $this->_mod_p->field('id,title,address')->where($where)->order(' id DESC ')->limit($start,10)->select();
		$str = '';
		foreach($list as $k=>$v){
             $str .='<li><a href="'.U('weixin/project/project_detail',array('id'=>$v['id'])).'"><h2>'.$v['title'].'</h2><span class="address">'.$v['address'].'</span><i></i></a></li>';
		}
		if($str){
		    $this->ajaxReturn(1,'',$str);
	    }else{
		    $this->ajaxReturn(0,'别滑动了，已经到底了...');
	    }
		
	}
	public function project_add(){
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

		$property_cate = M('property_cate')->field('id,name')->where(array('pid'=>1))->select();
		$property_count = count($property_cate);
        $this->assign('property_cate', $property_cate);
		$this->assign('property_count', $property_count);
		$this->assign('setTitle', '项目新建');
		$this->_config_seo();
		$this->display();
	}
	//项目详情
	public function project_detail(){
		$uid = $this->visitor->info['id'];
		$id = $this->_get('id','intval');
		$stores_info = array();
		$user_project_id = $id;
		$info = $this->_mod_p->field('id,title,address,img_thumb,property_type,item_price')->where('id='.$user_project_id)->find();
		if($info['property_type']){
			$property_cate_arr = M('property_cate')->field('id,name')->where("id IN (".$info['property_type'].")")->select();
			$this->assign('property_cate_arr', $property_cate_arr);
		}


		//联系人列表
		$property_contact_list = M('property_contact')->where('pid='.$id)->field('id,pid,name,tel')->order('id DESC')->select();
		//发布跟进
		$project_log_list = M('property_log')->where('pid='.$id)->field('id,pid,uid,info,add_time')->order('id DESC')->limit(0,5)->select();
		foreach($project_log_list as $k=>$v){
			$project_log_list[$k]['name'] =  M('user')->where(array('id'=>$v['uid']))->getField('username');
			$project_log_list[$k]['mobile'] =  M('user')->where(array('id'=>$v['uid']))->getField('mobile');
		}
		$fph  = C('DB_PREFIX');
		$this->assign('property_contact_list', $property_contact_list);
		$this->assign('project_log_list', $project_log_list);
		$this->assign('info', $info);
		$this->assign('http_host', 'http://'.$_SERVER['HTTP_HOST']);
		$this->assign('id', $id);
		$this->assign('count_log_list', count($project_log_list));
		$this->assign('user_project_id', $user_project_id);
		$this->assign('setTitle', '面目详情');
		$this->_config_seo();
		$this->display();
	}
	//项目联系人添加
	public function project_contact_add(){
		$uid = $this->visitor->info['id'];
		$id = $this->_get('id','intval');
		$info = array();
		$cid = $this->_get('cid','intval');
		$this->assign('setTitle', '新增联系人');
		if($cid){
			$info = M('property_contact')->where('id='.$cid)->field('id,pid,name,thework,tel')->find();
			$this->assign('setTitle', '修改联系人');
		}
		$this->assign('info', $info);
		$this->assign('id', $id);
		$this->_config_seo();
		$this->display();
	}
	//ajax 项目联系人添加
	public function ajax_project_contact_add(){
		$uid = $this->visitor->info['id'];
		$thework = $this->_post('thework','trim');
		$name = $this->_post('name','trim');
		$tel = $this->_post('tel','trim');
		$id = $this->_post('project_contact_id','intval');
		$cid = $this->_post('project_contact_cid','intval');
		$project_data['pid'] = $id;
		$project_data['name'] = $name;
		$project_data['thework'] = $thework;
		$project_data['tel'] = $tel;
		if($cid){
			$last_id = M('property_contact')->where('id='.$cid)->save($project_data);
		}else{
			$last_id = M('property_contact')->add($project_data);
		}
		if($last_id){
		    $this->ajaxReturn(1,'操作成功',$id);
	    }else{
		    $this->ajaxReturn(0,'操作失败');
	    }
	}
	//ajax 新建项目
	public function ajax_project_contact_add_arr(){
		$uid = $this->visitor->info['id'];
		//项目信息
		$data['title'] = $this->_post('title','trim');
		$data['address'] = $this->_post('address','trim');
		$data['property_type'] =$this->_post('property_cate_id','trim');
		$data['item_price'] =$this->_post('item_price','trim');
		$data['city_id'] =$this->_post('get_city_id','intval');
		$data['add_time'] =time();
		$property_id = M('property')->add($data);
		//联系人信息
		$thework = $this->_post('str_thework','trim');
		$name = $this->_post('str_name','trim');
		$tel = $this->_post('str_tel','trim');
		$thework_arr = explode(',', $thework);
		$name_arr = explode(',', $name);
		$tel_arr = explode(',', $tel);
		foreach($thework_arr as $k=>$v){
			if($v){
				$data_contact['pid'] = $property_id;
				$data_contact['name'] = $name_arr[$k];
				$data_contact['thework'] = $v;
				$data_contact['tel'] = $tel_arr[$k];
				$con_id = M('property_contact')->add($data_contact);
			}
		}
		if($con_id){
		    $this->ajaxReturn(1,'操作成功');
	    }else{
		    $this->ajaxReturn(0,'操作失败');
	    }
	}
	//项目跟进滑动加载
	public function ajax_project_log_list(){
		$uid = $this->visitor->info['id'];
		$id = $this->_post('id','intval');
		$page = $this->_post('page','intval');
		$start = $page*5; 
		$project_log_list = M('property_log')->where('pid='.$id)->field('id,pid,uid,info,add_time')->order('id DESC')->limit($start,5)->select();
		$str = '';
		foreach($project_log_list as $k=>$v){
			$project_log_list[$k]['name'] =  M('user')->where(array('id'=>$v['uid']))->getField('username');
			$project_log_list[$k]['mobile'] =  M('user')->where(array('id'=>$v['uid']))->getField('mobile');
				$str .='<li>';
				$str .='<div class="records_list">';
				$str .='<section class="top">';
            	$str .= ' <span class="contactor">'.$project_log_list[$k]['name'].' '.$project_log_list[$k]['mobile'].' </span><span class="date">'.date('Y-m-d',$v['add_time']).'</span> </section>
            		<p>'.$v['info'].'</p>';
				$str .='</div>';
				$str .='</li>';
		}

		if($str){
		    $this->ajaxReturn(1,'',$str);
	    }else{
		    $this->ajaxReturn(0,'别滑动了，已经到底了...');
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
    public function project_log_insert(){
    	$uid = $this->visitor->info['id'];
    	$project_info  = $this->_post('project_info','trim');
    	$project_id  = $this->_post('project_id','intval');
    	$tim = time();
    	$data_log['pid']  = $project_id;
       	$data_log['uid']  = $uid;
       	$data_log['info']  = $project_info;
       	$data_log['add_time'] = $tim;
       	$lastid_log = M('property_log')->add($data_log);
    	if ($lastid_log) {
    		$user_info = M('user')->field('username,mobile')->where(array('id'=>$uid))->find();
    		$user_info['info']=$project_info;
    		$user_info['add_time']=date('Y-m-d',$tim);
            $this->ajaxReturn(1, '操作成功',$user_info);
        } else {
            $this->ajaxReturn(0,'操作失败');
        }
    }	
   

   
	
	
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}