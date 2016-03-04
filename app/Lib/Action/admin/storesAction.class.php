<?php
class storesAction extends backendAction
{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('stores');//门店
    }
	//门店列表
	public function index(){
		$keyword     = $this->_get('keyword', 'trim');
        $city_id     = $this->_get('city_id', 'intval');
        $time_start  = $this->_request('time_start', 'trim');
        $time_end    = $this->_request('time_end', 'trim');
        $status      = $this->_request('status', 'trim');
        $store_type  = $this->_request('store_type', 'intval');
        $this->assign('search', array(
            'keyword' => $keyword,
            'city_id' => $city_id,
            'time_start' => $time_start,
            'time_end'=> $time_end,
            'status'=> $status,
            'store_type'=> $store_type
        ));
        $spid_city = M('city')->where(array('id'=>$city_id))->getField('spid');
        if($spid_city==0){
            $spid_city = $city_id;
        }else{
            $spid_city .= $city_id;
        }
        $where = '1=1 AND type='.$store_type.' ';
        if($status!=''){
            $where .= ' AND status='.$status;
        }
        if($keyword){
            $where .= ' AND (code_id = "'.$keyword.'" OR name like "%'.$keyword.'%" )';
        }
        if($city_id){
            $where .=' AND city_id in(select id from fph_city where id = '.$city_id.' or spid RLIKE "[[:<:]]'.$city_id.'[[:>:]]")';
        }
        if($time_start AND $time_end){
            $where .=' AND add_time  between '.strtotime($time_start).' AND '.strtotime($time_end.' 23:59:59');
        }
       
        $count = $this->_mod->where($where)->count('id');
        $p = new Page($count, 22);
		$list = M('stores')->where($where)->limit($p->firstRow.','.$p->listRows)->order('add_time DESC')->select();
		foreach($list as $k=>$v){
			$list[$k]['user_edit'] = M('user')->where('id='.$v['mid'])->getField('username');
			if($v['type_table']==1){//admin
				$list[$k]['user_edit'] = M('admin')->where('id='.$v['mid'])->getField('username');
			}
			$list[$k]['count'] = count(M('user')->where('stores_id='.$v['id'])->select());
            $city_spid = M('city')->where('id ='.$v['city_id'])->getField('spid');
            $spid_arr = explode('|', $city_spid.$v['city_id']);
            $get_cityname = '';
            foreach ($spid_arr as $key => $value) {
                $get_cityname .= M('city')->where('id ='.$value)->getField('name').' ';
            }
            $list[$k]['city_names'] = $get_cityname;
		}
        $this->assign('selected_ids_city',$spid_city);
        $page = $p->show();
        $this->assign('page', $page);
		$this->assign('list', $list);
		$this->display();
	}
    //待审核门店
    public function store_pass(){
        $keyword     = $this->_get('keyword', 'trim');
        $time_start = $this->_request('time_start', 'trim');
        $time_end   = $this->_request('time_end', 'trim');
        $this->assign('search', array(
            'keyword' => $keyword,
            'city_id' => $city_id,
            'time_start' => $time_start,
            'time_end'=> $time_end
        ));

         $where = 'status =0';
        if($keyword){
            $where .= ' AND (code_id = "'.$keyword.'" OR name like "%'.$keyword.'%" )';
        }
        if($time_start AND $time_end){
            $where .=' AND add_time  between '.strtotime($time_start).' AND '.strtotime($time_end.' 23:59:59');
        }
        $count = $this->_mod->where($where)->count('id');
        $p = new Page($count, 22);
        $list = M('stores')->where($where)->limit($p->firstRow.','.$p->listRows)->order('add_time DESC')->select();
        foreach($list as $k=>$v){
            $list[$k]['user_edit'] = M('user')->where('id='.$v['uid'])->getField('username');
            $list[$k]['mobile'] = M('user')->where('id='.$v['uid'])->getField('mobile');
            $list[$k]['count'] = count(M('user')->where('stores_id='.$v['id'])->select());
        }
        $this->assign('list_table', true);
        $page = $p->show();
        $this->assign('page', $page);
        $this->assign('list', $list);
        $this->display();
    }
    //搜索名单
	public function stores(){
		$stores_name = $this->_post('stores_name','trim');
		if($stores_name){
			$list = D('stores')->stores_search($stores_name);
			if($list){
				$this->ajaxReturn(1, '',$list);
			}else{
				$this->ajaxReturn(0, '没有找到相关门店');
			}
		}else{
			$this->ajaxReturn(0, '');
		}
	}
	
	public function _before_edit(){
		$id     = $this->_request('id','intval');
		$menuid = $this->_request('menuid','intval');
		$fph  = C('DB_PREFIX');
		$str = 'A.pid,A.uid,A.mid,A.type_table,A.city_id,C.username as admin_username,C.mobile as admin_mobile';
		$list =  $this->_mod->field($str)->table("{$fph}stores AS A LEFT JOIN {$fph}admin AS C ON C.id = A.sid")->where('A.id ='.$id)->find();
		$spid_city = M('city')->where(array('id'=>$list['city_id']))->getField('spid');
		if($spid_city==0){
		    $spid_city = $list['city_id'];
		}else{
		    $spid_city .= $list['city_id'];
		}
		$list['company_name'] = M('company')->where(array('id'=>$list['pid']))->getField('name');
		//门店所属者*创建人
		if($list['type_table']==1){
			$list['user_info'] = M('admin')->field('username,mobile')->where('id='.$list['mid'])->find();
		}else{
			$list['user_info'] = M('user')->field('username,mobile')->where('id='.$list['mid'])->find();
		}
		$this->assign('list', $list);
		$this->assign('selected_ids_city',$spid_city);
		$this->assign('menuid', $menuid);
	}
	
	public function _before_add(){
		$store_type = $this->_request('store_type','intval');
		$this->assign('store_type', $store_type);
	  
	}
   
    protected function _before_insert($data) {
		$uid = $_COOKIE['admin']['id'];
		$pid = $this->_post('stores_id','intval');
		!$pid && $this->error('所属公司不能为空!');
		
        $code_id = M('stores')->order('code_id DESC')->getField('code_id');
        $data['mid']      = $uid;
		$data['sid']      = $uid;
        $data['pid']      = $pid;
        $data['code_id']  = $code_id + 1;
		$data['add_time'] = time();
        $data['status']   = 1;
        return $data;
    }
	
	/*
	*@Descriptions：门店修改 前置操作
	*@Date:2015-02-09
	*@Author: lishun
	*/
	protected function _before_update($data) {
		$uid = $_COOKIE['admin']['id'];
		$pid = $this->_post('stores_id','intval');
		!$pid && $this->error('所属公司不能为空!');
		
        $data['pid']      = $pid;
        return $data;
    }
	
    //获取城市信息
    public function ajax_city() {
        $id = $this->_get('id', 'intval');
        $return = M('city')->field('id,name')->where(array('pid'=>$id))->select();
        if ($return) {
            $this->ajaxReturn(1, L('operation_success'), $return);
        } else {
            $this->ajaxReturn(0, L('operation_failure'));
        }
    }
	
	//wsj 删除门店人员
	public function delstores()
	{
		$uid = $this->_get('uid', 'intval');
		M('user')->where(array('id'=>$uid))->save(array('stores_id'=>0));
		$this->success(L('operation_success'));
	}
	
	//wsj 门店人员管理
	public function storesname()
	{
		$fph = C('DB_PREFIX');
		$p = $this->_get('p','intval',1);
		$id = $this->_get('id', 'intval');
		$store_type = $this->_get('store_type','intval');
		$menuid = $this->_get('menuid','intval');
		if(empty($id))
			 $this->_404();

		$count = M('user')->where('stores_id ='.$id)->count('id');
		$p = new Page($count, 20);
		$page = $p->show();
		$list = M('user')->field('id,reg_time,origin,username,mobile')->where('stores_id ='.$id)->limit($p->firstRow.','.$p->listRows)->select();
		
		foreach($list as $k=>$v){
			$list[$k]['daikan'] = M('myclient_property')->where('with_look = 1 AND uid='.$v['id'])->count('id');
			$list[$k]['weituo'] = M('myclient_property')->where('with_look = 2 AND uid='.$v['id'])->count('id');
			$list[$k]['chengjiao'] = M('myclient_property')->where('status = 7 AND uid='.$v['id'])->count('id');
			$my = M('myclient_status')->field('A.id,A.pid,A.username,A.mobile,C.expenditure,C.status as cstatus')
									  ->table("{$fph}myclient_status AS A
											   INNER JOIN {$fph}myclient_property AS B ON A.mpid = B.id
											   INNER JOIN {$fph}commission as C ON C.pid=A.id")
									  ->where('A.status = 7 AND B.uid = '.$v['id'])->select();
			$list[$k]['yongjin'] = 0;
			foreach($my as $k1=>$v1)
			{
				$list[$k]['yongjin'] = $list[$k]['yongjin'] + M('expenditure')->where('pid='.$v1['id'])->sum('price');
			}
		}
		
		//$stores = $this->_mod->where('id ='.$id)->find();
		
		$this->assign('page', $page);
		$this->assign('id', $id);
		$this->assign('store_type', $store_type);
		$this->assign('list', $list);
		$this->assign('stores', $stores);
		$this->assign('menuid', $menuid);
		$this->display();
	}
	
	//wsj  单个门店人员批量添加
	public function addstoresname()
	{
		$id = $this->_get('id', 'intval');
		$bool = '';
		$strmessagestrue ='';
		$strmessagesfalse ='';
		if(isset($_POST) AND $_POST)
		{
			$name = $this->_post('name','trim');
			$mobile = $this->_post('mobile','trim');
			$bool = 'true';
			foreach($name as $k=>$v)
			{
				if(!empty($v) AND !empty($mobile[$k]))
				{
					$user = M('user')->where('mobile ='.$mobile[$k])->find();
					if(empty($user))
					{
						$rand_code         = random(6,1);
						$data['username']  = $v;
						$data['mobile']    = $mobile[$k];
						$data['password']  = md5($rand_code);
						$data['stores_id'] = $id;
						$data['reg_time']  = time();
						$data['reg_ip']    = get_client_ip();
						$strmessagestrue .= $data['username'].',';
						$last_id = M('user')->add($data);
						if($last_id){
							//是否请求接口
							$send_sms = D('send_sms');
							$result = $send_sms->Messages($data['mobile'],$agent_mobile,$agent_name,$client_name,'',$title,$str,$rand_code,'5',false,1,$mobile_code_origin);
						}
					}
					else
					{
						if($user['stores_id'] == 0)
						{
							M('user')->where(array('id'=>$user['id']))->save(array('stores_id'=>$id));
							$strmessagestrue .= $user['username'].',';
						}
						else
						{
							$storesname = M('stores')->where('id ='.$user['stores_id'])->getField('name');
							$strmessagesfalse .= $user['username'].'('.$storesname.'),';
						}
					}
				}
			}
		}

		$list =  $this->_mod->where('id ='.$id)->find();
		$this->assign('list', $list);
		$this->assign('bool',$bool);
		$this->assign('strmessagestrue', substr($strmessagestrue,0,-1));
		$this->assign('strmessagesfalse', substr($strmessagesfalse,0,-1));
		$this->display();
	}
   

   
	
	
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}