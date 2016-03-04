<?php
class adminAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('admin');
    }

    public function index() {
        $status     = $this->_get('status','trim');
        $username   = $this->_get('username','trim');
        $mobile     = $this->_get('mobile','trim');

        $where = '1=1';
        if($status!=''){
            $where .= " AND status = ".$status;
        }
        if($username!=''){
            $where .= " AND username like '%".$username."%'";
        }
        if($mobile!=''){
            $where .= " AND mobile like '%".$mobile."%'";
        }
        $count = $this->_mod->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();
        $list = $this->_mod->field('id,username,mobile,city_id,role_id,last_ip,last_time,status')->where($where)->order('id DESC')->limit($p->firstRow.','.$p->listRows)->select();
        foreach($list as $key=>$value){
            if($value['city_id']!=''){
                $arr_city = M('city')->field('name')->where('id in ('.$value['city_id'].')')->select();
                foreach($arr_city as $k=>$v){
                    $list[$key]['city_id_name'] .=$v['name'].',';
                }
                $list[$key]['city_id_name'] =  substr($list[$key]['city_id_name'],0,-1);
            }
           $list[$key]['role_id_name'] .= M('admin_role')->where('id='.$value['role_id'])->getField('name');
        }
        $this->assign('list', $list);
        $this->assign('page',$page);
        $this->assign('status',$status);
        $this->assign('username',$username);
        $this->assign('mobile',$mobile);
        $this->display();
    }

    public function _before_add() {
        $role_list = M('admin_role')->where('status=1')->select();
        $this->assign('role_list', $role_list);
        //所在城市
        $city_list = M("city")->field('id,name')->where(array("pid"=>0))->select();
        foreach ($city_list as $key => $val){
            $city_list[$key]['two'] = M("city")->field('id,name')->where(array("pid"=>$val['id']))->select();
        }
        $this->assign('city_list',$city_list);
    }

    public function _before_insert($data='') {
		
		$password      = $this->_post('password','trim');
		$send_mobile   = $this->_post('mobile','trim');
        if( ($data['password']=='')||(trim($data['password']=='')) ){
            unset($data['password']);
        }else{
            $data['password'] = md5($data['password']);
        }
        $city = $this->_post('city','trim');//接收城市组合字符串存入
        $data['city_id']= implode(',',$city);
        if(empty($data['city_id'])){
            $data['city_id'] = '';
        }
        $data['add_time'] = time();
        //获取最大的code_id
        $code_id = M('admin')->order('id DESC')->getfield('code_id');
        $data['code_id'] = $code_id+1;
		//开通账户发送短信
		$send_sms = D('send_sms');
		$result = $send_sms->Messages($send_mobile,$agent_mobile,$agent_name,$client_name,'',$title,$str,$password,'7',false,1,$mobile_code_origin);
		//print_r($data);exit;
        return $data;
    }

    public function _before_edit() {
        //$this->_before_add();
        $id = $this->_get('id','intval');
        $role_list = M('admin_role')->where('status=1')->select();
        $this->assign('role_list', $role_list);
        
        $admin_detail = $this->_mod->where(array('id'=>$id))->find();
        //城市问题
        $city_list = M("city")->field('id,name')->where(array("pid"=>0))->select();
        foreach ($city_list as $key => $val){
            $city_list[$key]['ture']=0;
            $city_list[$key]['two'] = M("city")->field('id,name')->where(array("pid"=>$val['id']))->select();
            foreach ($city_list[$key]['two'] as $keys =>$vo){
                $city_list[$key]['two'][$keys]['have']=0;
                if(in_array($vo[id],explode(',',$admin_detail['city_id']))){
                    $city_list[$key]['ture']='1';
                    $city_list[$key]['two'][$keys]['have']='1';
                }
            }
        }
        $this->assign('city_list',$city_list);
       
        $spid_city = M('city')->where(array('id'=>$admin_detail['location']))->getField('spid');
        if( $spid_city==0 ){
            $spid_city = $admin_detail['location'];
        }else{
            $spid_city .= $admin_detail['location'];
        }
        $this->assign('selected_ids_city',$spid_city);
    }

    public function _before_update($data=''){
 //echo $location   = $this->_post('location','trim');exit;
        if( ($data['password']=='')||(trim($data['password']=='')) ){
            unset($data['password']);
        }else{
            $data['password'] = md5($data['password']);
        }
        $data['city_id'] =  implode(',',$this->_post('city','trim'));
        if(empty($data['city_id'])){
            $data['city_id'] = '';
        }
        //判断修改手机号
        // $case_count = M('case_field')->where(array('admin_id'=>$data['id']))->count('id');
        // if($case_count != 0){
        //     M('case_field')->where(array('admin_id'=>$data['id']))->save(array('mobile'=>$data['mobile']));
        // }
        return $data;
    }
	
	//停用用户
	public function delete(){
		$id = $this->_get('id','intval');
		if (false !== M('admin')->where(array('id'=>$id))->save(array('status'=>0))) {
			$this->ajaxReturn(1, '操作成功,该用户已经被停用');
		}else{
			$this->ajaxReturn(0, '操作失败');
		}
	}
	
	//恢复用户
	public function recovery(){
		$id = $this->_get('id','intval');
		if (false !== M('admin')->where(array('id'=>$id))->save(array('status'=>1))) {
			$this->ajaxReturn(1, '操作成功,该用户可以正常使用');
		}else{
			$this->ajaxReturn(0, '操作失败');
		}
	}

    public function ajax_check_name() {
        $name = $this->_get('username', 'trim');
        $id = $this->_get('id', 'intval');
        if ($this->_mod->name_exists($name, $id)) {
           $this->ajaxReturn(0, '该账号已经存在');
        } else {
            $this->ajaxReturn();
        }
    }
	
	public function ajax_check_mobile() {
        $mobile = $this->_get('mobile', 'trim');
        $id = $this->_get('id', 'intval');
        if ($this->_mod->mobile_exists($mobile, $id)) {
           $this->ajaxReturn(0, '该手机号码已经存在');
        } else {
            $this->ajaxReturn();
        }
    }
}