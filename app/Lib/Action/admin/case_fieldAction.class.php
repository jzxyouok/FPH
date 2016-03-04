<?php
class case_fieldAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('case_field');
    }

    public function index() {

		$fph = C('DB_PREFIX');
		$str = 'A.*,B.username';
		$where = '1=1';
		$keyword = $this->_request('keyword', 'trim');
		$name = $this->_request('name', 'trim');
		
		if($name){
			$admin_id = M('admin')->where("username='".$name."'")->getfield('id');
			if($admin_id){
				$where .= " AND admin_id =".$admin_id;
			}
		}
		if($keyword){
			$where .= " AND mobile = '".$keyword."'";
		}
		
		$count = $this->_mod->table("{$fph}case_field AS A")->where($where)->count('id');
		import("ORG.Util.Page");
		$p = new Page($count, 20);
		$page = $p->show();
		$case_list = $this->_mod->field($str)->table("{$fph}case_field AS A LEFT JOIN {$fph}admin AS B ON B.id=A.admin_id")->where($where)->limit($p->firstRow.','.$p->listRows)->order('A.id DESC')->select();
		foreach($case_list as $k=>$v){

			if(empty($v['property'])){
			    $v['property'] = 0;
			}
			$property = M('property')->field('title')->where('id in('.$v['property'].')')->select();
			$case_list[$k]['property'] = '';
			foreach($property as $n){
				$case_list[$k]['property_name'] .= $n['title']."、";
			}
			$case_list[$k]['property_count'] = count($property);
			//查询admin表id对应mobile
            $case_list[$k]['mobile'] = M('admin')->where('id='.$v['admin_id'])->getField('mobile');
			
		}
		
		$this->assign('case_list', $case_list);
		$this->assign('page_list', $page);

		$p = $this->_get('p','intval',1);
		$this->assign('p',$p);
			
		$this->_search();
		$this->display();
    }
	
    protected function _search() {
        $map = array();
        $keyword = $this->_request('keyword', 'trim');
		$name = $this->_request('name', 'trim');
        $this->assign('search', array(
            'keyword' => $keyword,
			'name' => $name
        ));
        return $map;
    }

    public function _before_add(){
    	$fph = C('DB_PREFIX');
		$admin_user = M('admin')->field('id,username,mobile')->where("status=1")->order('id ASC')->select();
		$this->assign('admin_user',$admin_user);
		
		$time = time();
		$admin_id =	$_COOKIE['admin']['id'];
		$city_id = M('admin')->where('id ='.$admin_id)->getfield('city_id');
		if(empty($city_id)){
			$city_id = -1;
		}else{
			$citystr = '';
			$cityarr = explode(',',$city_id);
			foreach($cityarr as $v){
				$cityarr2 = M('city')->field('id')->where('id = '.$v.' OR spid RLIKE "[[:<:]]'.$v.'[[:>:]]"')->select();
				foreach($cityarr2 as $v){
					$citystr .= $v['id'].',';
				}
			}
			$city_id = $citystr = substr($citystr,0,-1);
		}
		$wherep =' AND A.city_id in('.$city_id.')';
		$list = M('property')->field('A.title,A.city_id,B.pid')
							 ->table("{$fph}property AS A LEFT JOIN {$fph}property_cooperation AS B ON A.id = B.pid")
							 ->where("B.term_start < ".$time." AND B.term_end > ".$time."".$wherep)
							 ->select();
		foreach($list as $k=>$v){
			$spid = M('city')->where('id ='.$v['city_id'])->getfield('spid');
			$spid_arr = explode('|',$spid);
			foreach($spid_arr as $key=>$val){
				if($val){
					if($key!=0){
						$list[$k]['city_name'] .= M('city')->where('id ='.$val)->getfield('name');
					}
				}
			}
		}
		
		$this->assign('list', $list);
    }

    protected function _before_insert($data) {
		$property   = $this->_post('property','trim');
		$admin_id   = $this->_post('admin_id','intval');
		$sms_mobile = $this->_post('sms_mobile','trim');
		$docking    = $this->_post('docking','trim');
		if(!empty($property)){
		    $data['property'] = implode(',',$property);
		}else{
		    $data['property'] = '';
		}
		if(!empty($sms_mobile)){
		    $data['sms_mobile'] = implode(',',$sms_mobile);
		}else{
		    $data['sms_mobile'] = '';
		}
		//楼盘对接人
		if($docking){
			foreach($docking as $val){
				$docking_data['uid'] = $admin_id;
				$docking_data['pid'] = $val;
				M('property_docking')->add($docking_data);
			}
		}
		//写入手机号码到case_field表
		/*$data['mobile'] = M('admin')->where('id ='.$admin_id)->getfield('mobile');
		if(empty($data['mobile'])){
		    $data['mobile']  = '';
		}*/
		
		$result = $this->_mod->where("admin_id=" . $admin_id . "")->count('id');
		if($result){
			$this->ajaxReturn(0, '该用户已经分配过权限');
		}
        return $data;
    }

    public function _before_edit(){
    	$fph = C('DB_PREFIX');
        $id = $this->_get('id','intval');
		$admin_user = M('admin')->field('id,username,mobile')->where("status=1")->order('id ASC')->select();
		$this->assign('admin_user',$admin_user);
		$time = time();
		$admin_id =	$_COOKIE['admin']['id'];
		$city_id = M('admin')->where('id ='.$admin_id)->getfield('city_id');
		if(empty($city_id)){
			$city_id = -1;
		}else{
			$citystr = '';
			$cityarr = explode(',',$city_id);
			foreach($cityarr as $v){
				$cityarr2 = M('city')->where('id = '.$v.' OR spid RLIKE "[[:<:]]'.$v.'[[:>:]]"')->select();
				foreach($cityarr2 as $v){
					$citystr .= $v['id'].',';
				}
			}
			$city_id = $citystr = substr($citystr,0,-1);
		}
		$wherep =' AND A.city_id in('.$city_id.')';
		$list = M('property')->table("{$fph}property AS A LEFT JOIN {$fph}property_cooperation AS B ON A.id = B.pid")->where("B.term_start < ".$time." AND B.term_end > ".$time."".$wherep)->select();
		
		foreach($list as $k=>$v){
			$spid = M('city')->where('id ='.$v['city_id'])->getfield('spid');
			$spid_arr = explode('|',$spid);
			foreach($spid_arr as $key=>$val){
				if($val){
					if($key!=0){
						$list[$k]['city_name'] .= M('city')->where('id ='.$val)->getfield('name');
					}
				}
			}
		}
		$this->assign('list', $list);
    }

    protected function _before_update($data) {
		$admin_id = $this->_post('admin_id','intval');
		$id = $this->_post('id','intval');
		
		$property = $this->_post('property','trim');
		if(!empty($property)){
		    $data['property'] = implode(',',$property);
		}else{
		    $data['property'] = '';
		}
		$sms_mobile = $this->_post('sms_mobile','trim');
		if(!empty($sms_mobile)){
		    $data['sms_mobile'] = implode(',',$sms_mobile);
		}else{
		    $data['sms_mobile'] = '';
		}
		//写入手机号码到case_field表
		/*$data['mobile'] = M('admin')->where('id ='.$admin_id)->getfield('mobile');
		if(empty($data['mobile'])){
		    $data['mobile']  = '';
		}*/
		
		$result = $this->_mod->where("admin_id=" . $admin_id . " AND id <> ".$id."")->count('id');
		if($result){
			$this->ajaxReturn(0, '该用户已经分配过权限');
		}
        return $data;
    }
	
	/**
     * ajax检测用户权限是否分配
     */
    public function ajax_check_name() {
        $admin_id = $this->_get('admin_id', 'intval');
        $id = $this->_get('id', 'intval');
        if ($this->_mod->name_exists($admin_id,  $id)) {
            $this->ajaxReturn(0, '该用户已经分配过权限');
        } else {
	//    $fph = C('DB_PREFIX');
	//    $city_id = M('admin')->where('id ='.$_SESSION['admin']['id'])->getfield('city_id');
	//    $time = time();
	//    if(empty($city_id))
	//    {
	//        $city_id = -1;
	//    }
	//    else
	//    {
	//	    $citystr = '';
	//	    $cityarr = explode(',',$city_id);
	//	    foreach($cityarr as $v)
	//	    {
	//		    $cityarr2 = M('city')->where('id = '.$v.' OR spid RLIKE "[[:<:]]'.$v.'[[:>:]]"')->select();
	//		    foreach($cityarr2 as $v)
	//		    {
	//			$citystr .= $v['id'].',';
	//		    }
	//	    }
	//	    $city_id = $citystr = substr($citystr,0,-1);
	//    }
	//    $wherep =' AND A.city_id in('.$city_id.')';
	//    
	//    $list = M('property')
	//		->table("{$fph}property AS A
	//		LEFT JOIN {$fph}property_cooperation AS B ON A.id = B.pid")
	//		->where("B.term_start < ".$time." AND B.term_end > ".$time."".$wherep)->select();
	//    $str1 = $str2 = '';
	//    foreach($list as $k=>$v)
	//    {
	//	    if($k%2 == 0)
	//	    {
	//		$str1 .='<br>';
	//		$str2 .='<br>';
	//	    }
	//	    $str1 .= '<label ><input type="checkbox" name="property[]" id="qq'.$k.'" value="'.$v['pid'].'" /> '.$v['title'].'&nbsp;&nbsp;</label>';
	//	    $str2 .= '<label ><input type="checkbox" name="sms_mobile[]"  value="'.$v['pid'].'" /> '.$v['title'].'&nbsp;&nbsp;</label>';
	//	   
	//    }
	    
            $this->ajaxReturn(1);
        }
    }
}