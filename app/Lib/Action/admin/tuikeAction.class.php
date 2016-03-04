<?php
class tuikeAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('myclient');
        $this->_cate_mod = D('myclient_twitter');
    }

    public function index() { 
    	
		$fph = C('DB_PREFIX');
		$where = '1=1';
		$where2 = '1=1';
		$username = $this->_get('username','trim');
		$name     = $this->_get('name','trim');
		$mobile   = $this->_get('mobile', 'trim');
		$status   = $this->_get('status', 'trim');
		$city   = $this->_get('city', 'trim');
		$price   = $this->_get('price', 'trim');
		$time_start = strtotime($this->_request('time_start', 'trim'));
		$time_end = strtotime($this->_request('time_end', 'trim'))+(24*60*60-1);
		$add_time_start = strtotime($this->_request('add_time_start', 'trim'));
		$add_time_end = strtotime($this->_request('add_time_end', 'trim'))+(24*60*60-1);
		
		if($username)
		{
		    $where2 .=' AND B.username = "'.$username.'"';
		}
		
		if($mobile)
		{
		    $where2 .=' AND A.mobile = "'.$mobile.'"';
		}
		
		if($status){
		    $where .= ' AND A.status='.$status;
		}
		
		if($city){
		    $where .= ' AND A.area='.$city;
		}
		
		if($price){
		    $where .= ' AND A.price='.$price;
		}
		
		if($name)
		{
		    $where2 .=' AND A.name = "'.$name.'"';
		}
		
		if($time_start && $time_end)
		{
		    $where .=' AND A.update_time between '.$time_start.' AND '.$time_end;
		}
		
		if($add_time_start && $add_time_end)
		{
		    $where .=' AND A.add_time between '.$add_time_start.' AND '.$add_time_end;
		}
		
		
		$count = $this->_cate_mod->table("{$fph}myclient_twitter AS A LEFT JOIN {$fph}city AS B ON B.id=A.area LEFT JOIN {$fph}ideal_price AS C ON C.id=A.price")->where($where)->count('A.id');
		import("ORG.Util.Page");
		$p = new Page($count, 20);
		$page = $p->show();
		$str = 'A.*,B.name AS city_name,C.title AS price';
		$myclient_list = $this->_cate_mod->field($str)->table("{$fph}myclient_twitter AS A LEFT JOIN {$fph}city AS B ON B.id=A.area LEFT JOIN {$fph}ideal_price AS C ON C.id=A.price")
														 ->where($where)
														 ->limit($p->firstRow.','.$p->listRows)
														 ->order('A.id DESC')->select();
		//if($name)
		//{
		//    //echo $this->_cate_mod->getlastsql();
		//    //exit;
		//    print_r($myclient_list);exit;
		//}
		
		
		$fel = 'A.id AS myclient_id,A.name,A.gender,A.mobile,B.share_id,B.username,B.mobile AS user_mobile';												 
		foreach ($myclient_list as $key => $val) {
		$myclient_list[$key]['myclient'] = M('myclient')->field($fel)
						->table("{$fph}myclient AS A LEFT JOIN {$fph}user AS B ON B.id=A.uid")
						->where($where2." AND A.id=".$val['pid'])->select();
		foreach ($myclient_list[$key]['myclient'] as $k => $v) {
			if($myclient_list[$key]['myclient'][$k]['share_id'] != 0)
			{
			    $myclient_list[$key]['share_name'] = M('user')->where(array('id'=>$myclient_list[$key]['myclient'][$k]['share_id']))->getfield('username');
			}
			$myclient_list[$key]['myclient_id'] = $myclient_list[$key]['myclient'][$k]['myclient_id'];
			$myclient_list[$key]['name'] = $myclient_list[$key]['myclient'][$k]['name'];
			$myclient_list[$key]['gender'] = $myclient_list[$key]['myclient'][$k]['gender'];
			$myclient_list[$key]['mobile'] = $myclient_list[$key]['myclient'][$k]['mobile'];
			$myclient_list[$key]['username'] = $myclient_list[$key]['myclient'][$k]['username'];
			$myclient_list[$key]['user_mobile'] = $myclient_list[$key]['myclient'][$k]['user_mobile'];
		}
        }
	foreach ($myclient_list as $key => $val) {
		if(!$val['myclient']){
			unset($myclient_list[$key]);
		}
	}
	$this->assign('myclient_list', $myclient_list);
	
	
	$list_city = M('city')->field('id,name')->where('pid=803')->select();
	$this->assign('city', $list_city);
	$list_price = M('ideal_price')->field('id,title')->select();
	$this->assign('price', $list_price);
	
	$this->assign('page_list', $page);
	$this->_search();
	$p = $this->_get('p','intval',1);
	$this->assign('p',$p);
	$this->display();
    }

    protected function _search() {
	
        $map = array();
        $username = $this->_get('username','trim');
	$name     = $this->_get('name','trim');
	$mobile   = $this->_request('mobile', 'trim');
        $status = $this->_request('status', 'trim');
	$loupan = $this->_request('loupan', 'trim');
	$time_start = $this->_request('time_start', 'trim');
	$time_end = $this->_request('time_end', 'trim');
	$add_time_start = $this->_request('add_time_start', 'trim');
	$add_time_end = $this->_request('add_time_end', 'trim');
	$mystatus = $this->_get('mystatus', 'trim');
        $this->assign('search', array(
            'username' => $username,
            'name' => $name,
            'mobile' => $mobile,
            'status'  => $status,
	    'loupan'  => $loupan,
	    'time_start' => $time_start,
	    'time_end' => $time_end,
	    'add_time_start' => $add_time_start,
	    'add_time_end' => $add_time_end,
	    'mystatus' => $mystatus,
        ));
        return $map;
    }

    public function _before_add()
    {
        
    }

    protected function _before_insert($data) {
		
        return $data;
    }

    public function _before_edit(){
        $id = $this->_get('id','intval');
       
    }

    protected function _before_update($data) {

        return $data;
    }

}