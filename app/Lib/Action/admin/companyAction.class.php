<?php
class companyAction extends backendAction
{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('company');//公司
    }
	//公司信息列表
	public function index(){
		$name     = $this->_get('keyword', 'trim');
        $time_start = $this->_request('time_start', 'trim');
        $time_end   = $this->_request('time_end', 'trim');
        $this->assign('search', array(
            'keyword' => $name,
            'time_start' => $time_start,
            'time_end'=> $time_end
        ));
        $where = '1=1';
        if($name){
            $where .= ' AND (name like "%'.$name.'%" OR short_name like "%'.$name.'%" )';
        }
        if($time_start AND $time_end){
          $where .=' AND add_time  between '.strtotime($time_start).' AND '.strtotime($time_end.' 23:59:59');
        }
        $count = $this->_mod->where($where)->count('id');
        $p = new Page($count, 22);
		$list = M('company')->where($where)->limit($p->firstRow.','.$p->listRows)->order('add_time DESC')->select();
		foreach($list as $k=>$v){
			$list[$k]['user_edit'] = M('admin')->where('id='.$v['mid'])->getField('username');
			$list[$k]['count'] = count(M('stores')->where('pid='.$v['id'])->select());
		}
        $page = $p->show();
        $this->assign('page', $page);
		$this->assign('list', $list);
		$this->display();
	}

	protected function _before_insert($data) {
		$uid = $_COOKIE['admin']['id'];
        $data['mid']  = $uid;
        $data['add_time'] = time();
        return $data;
    }
     /*
    *@Descriptions：公司修改 前置操作
    *@Date:2015-02-09
    *@Author: lishun
    */
    public function _before_edit(){
    	$id = $this->_request('id','intval');
		
		$fph  = C('DB_PREFIX');
		$str = 'A.city_id,B.username,B.mobile';
		$list =  $this->_mod->field($str)->table("{$fph}company AS A LEFT JOIN {$fph}admin AS B ON B.id = A.mid")->where('A.id ='.$id)->find();
		
    	$spid_city = M('city')->where(array('id'=>$list['city_id']))->getField('spid');
        if($spid_city==0){
            $spid_city = $list['city_id'];
        }else{
            $spid_city .= $list['city_id'];
        }
        $this->assign('list', $list);
    	$this->assign('selected_ids_city',$spid_city);
    }
	
	protected function _before_update($data) {
		$contact_tel = $this->_post('contact_tel','trim');
		$data['contact_tel'] = $contact_tel;		

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
	//搜索名单
	public function company(){
		$stores_name = $this->_post('stores_name','trim');
		if($stores_name){
			$list = D('company')->company_search($stores_name);
			if($list){
				$this->ajaxReturn(1, '',$list);
			}else{
				$this->ajaxReturn(0, '没有找到相关公司');
			}
		}else{
			$this->ajaxReturn(0, '');
		}
	}
    /**
     * ajax检测会员是否存在
     */
    public function ajax_check_mobile() {
        $mobile = $this->_get('mobile', 'trim');
        $id = $this->_get('id', 'intval');
        $res = D('company')->mobile_exists($mobile,  $id);
        if ($res) {
            $this->ajaxReturn(1,'',$res);
        } else {
            $this->ajaxReturn(0, '该号码不存在');
        }
    }

		
   

   
	
	
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}