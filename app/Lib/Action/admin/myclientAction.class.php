<?php
class myclientAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('myclient');
        $this->_cate_mod = D('myclient_property');
    }

    public function index() {
		$fph = C('DB_PREFIX');
		$p = $this->_get('p','intval',1);
    	$where = '1=1';
    	$name   = $this->_get('name', 'trim');
    	$mobile   = $this->_get('mobile', 'trim');

    	$status = $this->_get('status','trim');
    	$status_cid = $this->_get('status','trim');

    	if($name)
    	{
    		$where .= ' AND name ="'.$name.'"';
    	}
    	if($mobile)
    	{
    		$where .= ' AND mobile ="'.$mobile.'"';
    	}
    	if($status)
    	{
    		if($status == 1)
    			$where .= ' AND count >= 1';

    		if($status == 2)
    			$where .= ' AND count = 0';
    	}

		$count = M('myclient')->where($where)->count('id');

        $this->assign('search', array(
            'mobile' => $mobile,
            'name' => $name,
            'status' => $status
        ));

		$p = new Page($count, 15);
		$page = $p->show();

		$list = M('myclient')->field('id,mobile,count')->where($where)->limit($p->firstRow.','.$p->listRows)->order('id DESC')->select();

		foreach ($list as $key => $value) {
			$list[$key]['daikan'] =  M('myclient_property')->table("{$fph}myclient_property AS A
										INNER JOIN {$fph}myclient AS B ON A.pid = B.id ")
									->where('A.with_look = 1 AND B.mobile ='.$value['mobile'])
									->count('A.id');

			$list[$key]['weituo'] = M('myclient_property')->table("{$fph}myclient_property AS A
										INNER JOIN {$fph}myclient AS B ON A.pid = B.id ")
									->where('A.with_look = 2 AND B.mobile ='.$value['mobile'])
									->count('A.id');

			$list[$key]['add_time'] =  M('myclient_property')->field('add_time')->where('pid ='.$value['id'])->order('add_time DESC')->getfield('add_time');

			if($value['count'] >= 1)
			{
				$list[$key]['chengjiao'] = 1;
			}
			else
			{
				$list[$key]['chengjiao'] = 0;
			}
		}

		$this->assign('page_list', $page);
		$this->assign('list',$list);
		$this->assign('p',$p);
		$this->display();
    }

    //查看客户
    public function myclientinfo()
    {
    	$id = $this->_get('id','trim');
    	$mymobile = M('myclient')->where('id ='.$id)->getfield('mobile');
    	$list = M('myclient')->where('mobile ='.$mymobile)->order('id DESC,identity DESC')->find();
    	$this->assign('list',$list);
    	$this->assign('id',$id);
    	$this->display();
    }

    //客户带看详情
    public function myclientdaikan()
    {
    	$fph = C('DB_PREFIX');
    	$id = $this->_get('id','trim');

    	$where = 'A.with_look = 1';
    	if($id)
    	{
    		$mymobile = M('myclient')->where('id ='.$id)->getfield('mobile');
    		$where .= ' AND B.mobile ='.$mymobile;
    	}

    	$title   = $this->_get('title', 'trim');
    	$mobile   = $this->_get('mobile', 'trim');

    	if($title)
    	{
    		$where .= ' AND C.title ="'.$title.'"';
    	}
    	if($mobile)
    	{
    		$where .= ' AND D.mobile ="'.$mobile.'"';
    	}

    	$this->assign('search', array(
            'mobile' => $mobile,
            'name' => $name
        ));

    	$str = 'A.id,A.uid,A.property,A.status,A.status_cid,A.add_time,B.name,B.mobile,C.title,D.username,D.mobile as user_mobile';

    	$count =  M('myclient_property')->field($str)
					->table("{$fph}myclient_property AS A
						INNER JOIN {$fph}myclient AS B on A.pid = B.id
						INNER JOIN {$fph}property AS C on C.id = A.property
						INNER JOIN {$fph}user AS D on A.uid = D.id
						")
					->where($where)
					->order('A.add_time DESC')
					->count('A.id');

		$p = new Page($count, 20);
		$page = $p->show();

		$list = M('myclient_property')->field($str)
					->table("{$fph}myclient_property AS A
						INNER JOIN {$fph}myclient AS B on A.pid = B.id
						INNER JOIN {$fph}property AS C on C.id = A.property
						INNER JOIN {$fph}user AS D on A.uid = D.id
						")
					->where($where)
					->limit($p->firstRow.','.$p->listRows)
					->order('A.add_time DESC')->select();

		foreach ($list as $key => $value) {
			$list[$key]['case_name'] = ' ';
			$case = M('case_field')->select();
			foreach ($case as $k => $v) {
				if(strstr(''.$v['property'].'',''.$value['property'].''))
				{
					$list[$key]['case_name'] .= M('admin')->field('username')->where('id ='.$v['admin_id'])->getfield('username').',';
				}
			}
			$list[$key]['case_name'] = substr($list[$key]['case_name'], 0,-1);
			if($value['status'] == 6 AND $value['status_cid'] == 0)
			{
				$list[$key]['zhongzhi'] = M('myclient_status')->where('status = 5 AND mpid ='.$value['id'])->getfield('status_cid');
			}
		}

		$this->assign('page_list', $page);
		$this->assign('list',$list);
    	$this->assign('id',$id);
    	$this->assign('p',$p);
    	$this->display();
    }

    //客户委托详情
    public function myclientweituo()
    {
    	$fph = C('DB_PREFIX');
    	$id = $this->_get('id','trim');

    	$where = 'A.with_look = 2';

    	if($id)
    	{
    		$mymobile = M('myclient')->where('id ='.$id)->getfield('mobile');
    		$where .= ' AND B.mobile ='.$mymobile;
    	}

    	$title   = $this->_get('title', 'trim');
    	$mobile   = $this->_get('mobile', 'trim');

    	if($title)
    	{
    		$where .= ' AND C.title ="'.$title.'"';
    	}
    	if($mobile)
    	{
    		$where .= ' AND D.mobile ="'.$mobile.'"';
    	}

    	$this->assign('search', array(
            'mobile' => $mobile,
            'name' => $name
        ));

    	$str = 'A.id,A.uid,A.property,A.status,A.status_cid,A.add_time,B.name,B.mobile,C.title,D.username,D.mobile as user_mobile';

    	$count =  M('myclient_property')->field($str)
					->table("{$fph}myclient_property AS A
						INNER JOIN {$fph}myclient AS B on A.pid = B.id
						INNER JOIN {$fph}property AS C on C.id = A.property
						INNER JOIN {$fph}user AS D on A.uid = D.id
						")
					->where($where)
					->order('A.add_time DESC')
					->count('A.id');

		$p = new Page($count, 20);
		$page = $p->show();

		$list = M('myclient_property')->field($str)
					->table("{$fph}myclient_property AS A
						INNER JOIN {$fph}myclient AS B on A.pid = B.id
						INNER JOIN {$fph}property AS C on C.id = A.property
						INNER JOIN {$fph}user AS D on A.uid = D.id
						")
					->where($where)
					->limit($p->firstRow.','.$p->listRows)
					->order('A.add_time DESC')->select();

		foreach ($list as $key => $value) {
			$list[$key]['case_name'] = ' ';
			$case = M('case_field')->select();
			foreach ($case as $k => $v) {
				if(strstr(''.$v['property'].'',''.$value['property'].''))
				{
					$list[$key]['case_name'] .= M('admin')->field('username')->where('id ='.$v['admin_id'])->getfield('username').',';
				}
			}
			$list[$key]['case_name'] = substr($list[$key]['case_name'], 0,-1);
			if($value['status'] == 6 AND $value['status_cid'] == 0)
			{
				$list[$key]['zhongzhi'] = M('myclient_status')->where('status = 5 AND mpid ='.$value['id'])->getfield('status_cid');
			}
		}

		$this->assign('page_list', $page);
		$this->assign('list',$list);
    	$this->assign('id',$id);
    	$this->assign('p',$p);
    	$this->display();
    }

    //流程 不能点击
    public function liuchengnone()
    {
    	$fph = C('DB_PREFIX');
    	$mpid = $this->_get('id','trim');
    	$with_look = $this->_get('with_look','trim');

    	$list = M('myclient_status')->where('mpid ='.$mpid)->group('status')->select();
    	foreach ($list as $key => $value) {

    		if($value['status'] == 1)
    		{
    			$my_p = M('myclient_property')->field('uid,pid')->where('id ='.$value['mpid'])->find();
    			$list[$key]['myclient_mobile'] = M('myclient')->where('id ='.$my_p['pid'])->getField('mobile');
    			$list[$key]['user_name'] = M('user')->where('id ='.$my_p['uid'])->getField('username');
    		}

    		$list[$key]['title'] = M('property')->where('id='.$value['pid'])->getField('title');
    	}

    	$listliu = M('myclient_status')->where('status = 6 AND mpid ='.$mpid)->find();

		if(!empty($listliu))
		{
			$listliu['deposit'] = explode(",",$listliu['deposit']);
			$listliu['info'] = explode(",",$listliu['info']);
			$listliu['signing_time'] = explode(",",$listliu['signing_time']);
			$this->assign('listliu',$listliu);
		}

		$endlist = end($list);

		if($endlist['status'] == 7)
		{
			$str = '';
			$info = M('myclient_status')->where('status = 7 AND mpid ='.$mpid)->order('id')->select();
			foreach ($info as $key => $value) {
				$user_arr1 = explode(",",$value['username']);
				$user_arr2 = explode(",",$value['mobile']);
				$user_arr3 = explode(",",$value['identity']);
				$strname = '';
				foreach ($user_arr1 as $k => $v) {
					if(empty($v))
						$this->ajaxReturn(0, L('请填写名称'));
					if(empty($user_arr2[$k]))
							$this->ajaxReturn(0, L('请填写手机'));
					if(empty($user_arr3[$k]))
							$this->ajaxReturn(0, L('请填写身份证'));
					$strname .= '购买人：'.$v.'&nbsp;&nbsp;'.$user_arr3[$k].'，';
				}
				$str .='房号：'.$value['number'].'&nbsp;&nbsp;面积：'.$value['measure'].'㎡　　总价：'.$value['total_price'].'元　　首付：'.$value['first_price'].'元　　贷款：'.$value['loan'].'元　　尾款：'.$value['tail_price'].'元　'.substr($strname,0,-3).'<br>';
			}
			$endlist['info'] = $str;
		}

		$property_id = $property_name = '';
		$property_cate = M('property_cate')->field('id,name')->select();
		foreach ($property_cate as $key => $value) {
			$property_id .= $value['id'].',';
			$property_name .= $value['name'].',';
		}

		$this->assign('property_id',substr($property_id, 0,-1));
		$this->assign('property_name',substr($property_name, 0,-1));

		$this->assign('list',$list);
		$this->assign('mpid',$mpid);

		$this->assign('endlist',$endlist);
    	$this->display();
    }

    public function update_time()
    {
    	$fph = C('DB_PREFIX');
    	$mpid = $this->_get('id','trim');
    	$with_look = $this->_get('with_look','trim');
    	if(isset($_POST) AND $_POST)
    	{
    		$baobei_time = $this->_post('baobei_time','trim');
    		$add_time     = $this->_post('add_time', 'trim');
    		$visit_time     = $this->_post('visit_time', 'trim');
    		$signing_time     = $this->_post('signing_time', 'trim');
    		foreach ($add_time as $key => $value) {
    			$save['add_time'] = strtotime($value);
    			if($key == 1)
    			{
    				if(isset($visit_time[1]))
    				{
    					$save['visit_time'] = strtotime($visit_time[1]);
    				}
    			}
    			if($key == 2)
    			{
    				if(isset($visit_time[2]))
    				{
    					$save['visit_time'] = strtotime($visit_time[2]);
    				}
    			}
    			if($key == 6)
    			{
    				$save['signing_time'] = strtotime($signing_time);
    			}
    			M('myclient_status')->where('status = '.$key.' AND mpid ='.$mpid)->save($save);
    		}
			M('myclient_property')->where('id ='.$mpid)->save(array('add_time'=>strtotime($baobei_time)));
    	}
    	$list = M('myclient_status')->where('mpid ='.$mpid)->group('status')->select();

    	$sort_by		= array('1'=>1,'4'=>2,'3'=>3,'5'=>4,'6'=>5,'7'=>6,'8'=>7,'9'=>8);
    	$status_sort	= array();
    	foreach( $list AS $key => $row )
    	{
    		$tmp_status			= $row['status'];
    		if(!isset( $sort_by[$tmp_status] ))	unset($list[$key]);
    		else								$status_sort[$key]	= $sort_by[$tmp_status];
    	}
    	array_multisort( $status_sort, SORT_ASC , $list );

    	foreach ($list as $key => $value) {

    		if($value['status'] == 1)
    		{
    			$my_p = M('myclient_property')->field('uid,pid')->where('id ='.$value['mpid'])->find();
    			$list[$key]['myclient_mobile'] = M('myclient')->where('id ='.$my_p['pid'])->getField('mobile');
    			$list[$key]['user_name'] = M('user')->where('id ='.$my_p['uid'])->getField('username');
    			$myclient_mobile		= $this->_post('myclient_mobile','trim');
    			if(!empty( $myclient_mobile ) && $myclient_mobile != $list[$key]['myclient_mobile'] )
    			{
    				M('myclient')->where('id ='.$my_p['pid'])->save(array('mobile'=>$myclient_mobile));
    				$list[$key]['myclient_mobile'] = $myclient_mobile;
    			}
    		}

    		$list[$key]['title'] = M('property')->where('id='.$value['pid'])->getField('title');
    	}

    	$listliu = M('myclient_status')->where('status = 6 AND mpid ='.$mpid)->find();

		if(!empty($listliu))
		{
			$listliu['deposit'] = explode(",",$listliu['deposit']);
			$listliu['info'] = explode(",",$listliu['info']);
			$listliu['signing_time'] = explode(",",$listliu['signing_time']);
			$this->assign('listliu',$listliu);
		}

		$endlist = end($list);

		if($endlist['status'] == 7)
		{
			$str = '';
			$info = M('myclient_status')->where('status = 7 AND mpid ='.$mpid)->order('id')->select();
			foreach ($info as $key => $value) {
				$user_arr1 = explode(",",$value['username']);
				$user_arr2 = explode(",",$value['mobile']);
				$user_arr3 = explode(",",$value['identity']);
				$strname = '';
				foreach ($user_arr1 as $k => $v) {
					if(empty($v))
						$this->ajaxReturn(0, L('请填写名称'));
					if(empty($user_arr2[$k]))
							$this->ajaxReturn(0, L('请填写手机'));
					if(empty($user_arr3[$k]))
							$this->ajaxReturn(0, L('请填写身份证'));
					$strname .= '购买人：'.$v.'&nbsp;&nbsp;'.$user_arr3[$k].'，';
				}
				$str .='房号：'.$value['number'].'&nbsp;&nbsp;面积：'.$value['measure'].'㎡　　总价：'.$value['total_price'].'元　　首付：'.$value['first_price'].'元　　贷款：'.$value['loan'].'元　　尾款：'.$value['tail_price'].'元　'.substr($strname,0,-3).'<br>';
			}
			$endlist['info'] = $str;
		}

		$property_id = $property_name = '';
		$property_cate = M('property_cate')->field('id,name')->select();
		foreach ($property_cate as $key => $value) {
			$property_id .= $value['id'].',';
			$property_name .= $value['name'].',';
		}

		$property_times = M('myclient_property')->field('protection_expire,look_expire,add_time')->where('id ='.$mpid)->find();
		$baobei_time		= $property_times['add_time'];
		$protection_expire	= $property_times['protection_expire'];
		$look_expire		= $property_times['look_expire'];

		$this->assign('baobei_time',$baobei_time);
		$this->assign('protection_expire',$protection_expire);
		$this->assign('look_expire',$look_expire);
		$this->assign('property_id',substr($property_id, 0,-1));
		$this->assign('property_name',substr($property_name, 0,-1));

		$this->assign('list',$list);
		$this->assign('mpid',$mpid);
		$this->assign('with_look',$with_look);
		$this->assign('endlist',$endlist);
    	$this->display();
    }

	//有我带看
    public function with_look(){
    	$fph = C('DB_PREFIX');
    	$where = 'A.with_look = 1';

    	$mobile   = $this->_get('mobile', 'trim');
    	$user_mobile   = $this->_get('user_mobile', 'trim');
    	$title   = $this->_get('title', 'trim');
    	$status = $this->_get('status','trim');
    	$add_time_start = $this->_request('add_time_start', 'trim');
		$add_time_end = $this->_request('add_time_end', 'trim');
		$status_time_start = $this->_request('status_time_start', 'trim');
		$status_time_end = $this->_request('status_time_end', 'trim');

    	if($mobile)
    	{
    		$where .= ' AND B.mobile ="'.$mobile.'"';
    	}
    	if($user_mobile)
    	{
    		$where .= ' AND D.mobile ="'.$user_mobile.'"';
    	}
    	if($title)
    	{
    		$where .= ' AND C.title LIKE "%'.$title.'%"';
    	}
    	if($status)
    	{
    		$statusarr = explode(',', $status);
    		if( $statusarr[0] == '0' && $statusarr[1] == '1')
    		{
    			$where .= ' AND A.status ="1" AND A.status_cid !="0" AND A.protection_expire > 0 AND A.protection_expire < "'.time().'"';
    		}
    		else if( $statusarr[0] == '0' && $statusarr[1] == '2')
    		{
    			$where .= ' AND A.status = "3" AND A.status_cid !="0" AND A.look_expire > 0  AND A.look_expire < "'.time().'"';
    		}
    		else
    		{
    			$where .= ' AND A.status ="'.$statusarr[0].'"  AND A.status_cid ="'.$statusarr[1].'"';
    		}
    	}

		if($add_time_start && $add_time_end)
		{
		    $where .=' AND A.add_time between '.strtotime($add_time_start).' AND '.(strtotime($add_time_end)+(24*60*60-1));
		}

		if($status && $status_time_start && $status_time_end)
		{
			$statusarr = explode(',', $status);
			if($statusarr[0] != '0')	$where .=' AND E.status = '.$statusarr[0].' AND E.add_time between '.strtotime($status_time_start).' AND '.(strtotime($status_time_end)+(24*60*60-1));
		}
		else
		{
			$where .= ' AND E.status = 1';
		}

    	//获取案场人员 负责楼盘
		if($_COOKIE['admin']['id'] != 1)
		{
		    $case_property = M('case_field')->where('admin_id ='.$_COOKIE['admin']['id'])->getfield('property');
		    if(empty($case_property))
		    {
			    $case_property = 0;
		    }
		    $where .= ' AND C.id in('.$case_property.')';
		}

    	$this->assign('search', array(
            'mobile' => $mobile,
            'user_mobile' => $user_mobile,
            'title' => $title,
            'status' =>$status,
            'add_time_start' => $add_time_start,
            'add_time_end'=> $add_time_end,
            'status_time_start' => $status_time_start,
            'status_time_end'=> $status_time_end
        ));

    	$str = 'A.id,A.uid,A.property,A.status,A.status_cid,A.protection_expire,A.look_expire,A.add_time,B.name,B.mobile,C.title,D.username,D.mobile as user_mobile';

    	$count =  M('myclient_property')->field($str)
					->table("{$fph}myclient_property AS A
						INNER JOIN {$fph}myclient AS B on A.pid = B.id
						INNER JOIN {$fph}property AS C on C.id = A.property
						INNER JOIN {$fph}user AS D on A.uid = D.id
						INNER JOIN {$fph}myclient_status AS E on A.id = E.mpid
						")
					->where($where)
					->order('A.add_time DESC')
					->count('A.id');

		$p = new Page($count, 15);
		$page = $p->show();

		$list = M('myclient_property')->field($str)
					->table("{$fph}myclient_property AS A
						INNER JOIN {$fph}myclient AS B on A.pid = B.id
						INNER JOIN {$fph}property AS C on C.id = A.property
						INNER JOIN {$fph}user AS D on A.uid = D.id
						INNER JOIN {$fph}myclient_status AS E on A.id = E.mpid
						")
					->where($where)
					->limit($p->firstRow.','.$p->listRows)
					->order('A.add_time DESC')->select();

		foreach ($list as $key => $value) {
			$list[$key]['case_name'] = ' ';
			$case = M('case_field')->select();
			foreach ($case as $k => $v) {
				if(strstr(''.$v['property'].'',''.$value['property'].''))
				{
					$list[$key]['case_name'] .= M('admin')->field('username')->where('id ='.$v['admin_id'])->getfield('username').',';
				}
			}
			$list[$key]['case_name'] = substr($list[$key]['case_name'], 0,-1);
			if($value['status'] == 6 AND $value['status_cid'] == 0)
			{
				$list[$key]['zhongzhi'] = M('myclient_status')->where('status = 5 AND mpid ='.$value['id'])->getfield('status_cid');
			}
			$list[$key]['status_time'] = M('myclient_status')->where('mpid ='.$value['id'])->order('status DESC')->getfield('add_time');
		}
	    $this->assign('list',$list);

		$my_admin = array('username'=>$_COOKIE['admin']['username'], 'rolename'=>$_COOKIE['admin']['role_id']);
        $this->assign('my_admin', $my_admin);

	    $this->assign('page_list', $page);
    	$this->assign('p',$p);
    	$this->display();
    }


    //委托带看
    public function weituo(){
    	$fph = C('DB_PREFIX');
    	$where = 'A.with_look = 2';

    	$mobile   = $this->_get('mobile', 'trim');
    	$user_mobile   = $this->_get('user_mobile', 'trim');
    	$title   = $this->_get('title', 'trim');
    	$status = $this->_get('status','trim');
    	$add_time_start = $this->_request('add_time_start', 'trim');
		$add_time_end = $this->_request('add_time_end', 'trim');
		$status_time_start = $this->_request('status_time_start', 'trim');
		$status_time_end = $this->_request('status_time_end', 'trim');

    	if($mobile)
    	{
    		$where .= ' AND B.mobile ="'.$mobile.'"';
    	}
    	if($title)
    	{
    		$where .= ' AND C.title LIKE "%'.$title.'%"';
    	}
    	if($user_mobile)
    	{
    		$where .= ' AND D.mobile ="'.$user_mobile.'"';
    	}
    	if($status)
    	{
    		$statusarr = explode(',', $status);
    		$where .= ' AND A.status ="'.$statusarr[0].'"  AND A.status_cid ="'.$statusarr[1].'"';
    	}

		if($add_time_start && $add_time_end)
		{
		    $where .=' AND A.add_time between '.strtotime($add_time_start).' AND '.(strtotime($add_time_end)+(24*60*60-1));
		}

		if($status && $status_time_start && $status_time_end)
		{
			$statusarr = explode(',', $status);
		    $where .=' AND E.status = '.$statusarr[0].' AND E.add_time between '.strtotime($status_time_start).' AND '.(strtotime($status_time_end)+(24*60*60-1));
		}
		else
		{
			$where .= ' AND E.status = 1';
		}

		//获取案场人员 负责楼盘
		if($_COOKIE['admin']['id'] != 1)
		{
		    $case_property = M('case_field')->where('admin_id ='.$_COOKIE['admin']['id'])->getfield('property');
		    if(empty($case_property))
		    {
			    $case_property = 0;
		    }
		    $where .= ' AND C.id in('.$case_property.')';
		}


    	$this->assign('search', array(
            'mobile' => $mobile,
            'user_mobile' => $user_mobile,
            'title' => $title,
            'status' =>$status,
            'add_time_start' => $add_time_start,
            'add_time_end'=> $add_time_end,
            'status_time_start' => $status_time_start,
            'status_time_end'=> $status_time_end
        ));

    	$str = 'A.id,A.uid,A.property,A.status,A.status_cid,A.add_time,B.name,B.mobile,C.title,D.username,D.mobile as user_mobile';

    	$count =  M('myclient_property')->field($str)
					->table("{$fph}myclient_property AS A
						INNER JOIN {$fph}myclient AS B on A.pid = B.id
						INNER JOIN {$fph}property AS C on C.id = A.property
						INNER JOIN {$fph}user AS D on A.uid = D.id
						INNER JOIN {$fph}myclient_status AS E on A.id = E.mpid
						")
					->where($where)
					->order('A.add_time DESC')
					->count('A.id');

		$p = new Page($count, 15);
		$page = $p->show();

		$list = M('myclient_property')->field($str)
					->table("{$fph}myclient_property AS A
						INNER JOIN {$fph}myclient AS B on A.pid = B.id
						INNER JOIN {$fph}property AS C on C.id = A.property
						INNER JOIN {$fph}user AS D on A.uid = D.id
						INNER JOIN {$fph}myclient_status AS E on A.id = E.mpid
						")
					->where($where)
					->limit($p->firstRow.','.$p->listRows)
					->order('A.add_time DESC')->select();

		foreach ($list as $key => $value) {
			$list[$key]['case_name'] = ' ';
			$case = M('case_field')->select();
			foreach ($case as $k => $v) {
				if(strstr(''.$v['property'].'',''.$value['property'].''))
				{
					$list[$key]['case_name'] .= M('admin')->field('username')->where('id ='.$v['admin_id'])->getfield('username').',';
				}
			}
			$list[$key]['case_name'] = substr($list[$key]['case_name'], 0,-1);
			if($value['status'] == 6 AND $value['status_cid'] == 0)
			{
				$list[$key]['zhongzhi'] = M('myclient_status')->where('status = 5 AND mpid ='.$value['id'])->getfield('status_cid');
			}
			$list[$key]['status_time'] = M('myclient_status')->where('mpid ='.$value['id'])->order('status DESC')->getfield('add_time');
		}
	    $this->assign('list',$list);

		$my_admin = array('username'=>$_COOKIE['admin']['username'], 'rolename'=>$_COOKIE['admin']['role_id']);
        $this->assign('my_admin', $my_admin);

	    $this->assign('page_list', $page);
    	$this->assign('p',$p);
    	$this->display();
    }

    //流程跟进
    public function liucheng()
    {
    	$fph = C('DB_PREFIX');
    	$mpid = $this->_get('id','trim');
    	$with_look = $this->_get('with_look','trim');

    	$list = M('myclient_status')->where('mpid ='.$mpid)->group('status')->order()->select();

    	$sort_by		= array('1'=>1,'4'=>2,'3'=>3,'5'=>4,'6'=>5,'7'=>6,'8'=>7,'9'=>8);
    	$status_sort	= array();
    	foreach( $list AS $key => $row )
    	{
    		$tmp_status			= $row['status'];
    		if(!isset( $sort_by[$tmp_status] ))	unset($list[$key]);
    		else								$status_sort[$key]	= $sort_by[$tmp_status];
    	}
    	array_multisort( $status_sort, SORT_ASC , $list );

    	foreach ($list as $key => $value) {

    		if($value['status'] == 1)
    		{
    			$my_p = M('myclient_property')->field('uid,pid')->where('id ='.$value['mpid'])->find();
    			$list[$key]['myclient_mobile'] = M('myclient')->where('id ='.$my_p['pid'])->getField('mobile');
    			$list[$key]['user_name'] = M('user')->where('id ='.$my_p['uid'])->getField('username');
    		}
    		$list[$key]['title'] = M('property')->where('id='.$value['pid'])->getField('title');
    		$list[$key]['prefer'] = M('property')->where('id='.$value['pid'])->getField('prefer');
    	}

    	$listliu = M('myclient_status')->where('status = 6 AND mpid ='.$mpid)->find();

		if(!empty($listliu))
		{
			$listliu['deposit'] = explode(",",$listliu['deposit']);
			$listliu['info'] = explode(",",$listliu['info']);
			$listliu['signing_time'] = explode(",",$listliu['signing_time']);
			$this->assign('listliu',$listliu);
		}

		$endlist = end($list);
		if($endlist['status'] == 7)
		{
			$str = '';
			$info = M('myclient_status')->where('status = 7 AND mpid ='.$mpid)->order('id')->select();
			foreach ($info as $key => $value) {
				$user_arr1 = explode(",",$value['username']);
				$user_arr2 = explode(",",$value['mobile']);
				$user_arr3 = explode(",",$value['identity']);
				$strname = '';
				foreach ($user_arr1 as $k => $v) {
					if(empty($v))
						$this->ajaxReturn(0, L('请填写名称'));
					if(empty($user_arr2[$k]))
							$this->ajaxReturn(0, L('请填写手机'));
					if(empty($user_arr3[$k]))
							$this->ajaxReturn(0, L('请填写身份证'));
					$strname .= '购买人：'.$v.'&nbsp;&nbsp;'.$user_arr3[$k].'，';
				}
				$info_cataname = M('property_cate')->where('id='.$value['cate_id'])->find();
				$str .='物业类型：'.$info_cataname['name'].'&nbsp;&nbsp;房号：'.$value['number'].'&nbsp;&nbsp;面积：'.$value['measure'].'㎡　　总价：'.$value['total_price'].'元　　首付：'.$value['first_price'].'元　　贷款：'.$value['loan'].'元　　尾款：'.$value['tail_price'].'元　'.substr($strname,0,-3).'<br>';
			}
			$endlist['info'] = $str;
		}

		$property_id = $property_name = '';
		$cate = array('property_type'=>1);
		$property_cate = D('property')->property_cate($cate);
		foreach ($property_cate['property_type'] as $key => $value) {
			$property_id .= $value['id'].',';
			$property_name .= $value['name'].',';
		}

		/*
		 * 判断是否已经过了有效期
		 */
		$expires	= M('myclient_property')->field('status,status_cid,protection_expire,look_expire')->where('id ='.$endlist['mpid'])->find();
		if(		($expires['status'] == 1 && $expires['status_cid'] != 0 && $expires['protection_expire'] < time() && $expires['protection_expire'] > 0)
			||	($expires['status'] == 3 && $expires['status_cid'] != 0 && $expires['look_expire'] < time() && $expires['look_expire'] > 0)
		){
			$endlist['status_cid']	= 0;
		}


		$this->assign('property_id',substr($property_id, 0,-1));
		$this->assign('property_name',substr($property_name, 0,-1));

		$this->assign('list',$list);
		$this->assign('mpid',$mpid);

		$this->assign('endlist',$endlist);
    	$this->display();
    }

    //ajax添加
    public function ajax_addnew()
    {
		$status = $this->_post('status','intval');
		$mpid = $this->_post('mpid','intval');
		$list = M('myclient_status')->where('mpid ='.$mpid)->order('status DESC')->find();
		$data['mpid'] = $list['mpid'];
		$data['pid'] = $list['pid'];
		$data['name'] = $_COOKIE['admin']['username'];
		$data['with_look'] = $list['with_look'];
		$select = $this->_post('select','intval');
		$data['info']  = $this->_post('info','trim');
		$data['add_time'] = time();

		/*
		 * 增加判断.如果已经失效不能再次跟进
		 */
		$str					= 'status,status_cid,protection_expire,look_expire';
		$mproperty_status		= M('myclient_property')->field($str)->where('id ='.$mpid)->find();

		/*
		 * 判断是否有流程正在进行
		 */
		$is_ok	= TRUE;
		if(empty( $mproperty_status ))																												$is_ok	= FALSE; //报备不存在
		else if($mproperty_status['status_cid'] == 0)																								$is_ok	= FALSE; //流程已终止
		else if($mproperty_status['status'] == 1 && $mproperty_status['protection_expire'] < time() && $mproperty_status['protection_expire'] > 0)	$is_ok	= FALSE; //已经报备失效
		else if($mproperty_status['status'] == 3 && $mproperty_status['look_expire'] < time() && $mproperty_status['protection_expire'] > 0)		$is_ok	= FALSE; //已经带看失效


		if( $is_ok == FALSE )
		{
			$this->error('该报备流程已失效,不能再做跟进.');
		}


		$init_prev_status	= $status;
		if($status == 1)	$init_prev_status	= 3;
		else if($status == 3)	$init_prev_status	= 4;
		else if($status == 4)	$init_prev_status	= 2;
		$boll_status = M('myclient_status')->where('with_look = '.$data['with_look'].'  AND mpid = '.$data['mpid'].' AND status = '.($init_prev_status+1).'')->count('id');
		if($boll_status == 1 )
		    $status = 999;

		if($status == 2)
		{
				$visit_time = $this->_post('visit_time','trim');

				if($select == 1)
				{
					if(empty($visit_time))
						$this->ajaxReturn(0, L('到访时间不能为空！'));

					$data['visit_time'] = strtotime($visit_time);

					$data['status'] = 2;
					$data['status_cid'] = 1;
					M('myclient_status')->add($data);
					$str = '邀约成功';
					$str2 = '到访时间：'.$visit_time;
					if(!empty($data['info']))
						$str2 = '到访时间：'.$visit_time.'&nbsp;&nbsp;备注：'.$data['info'];

				}
				else
				{
					if(empty($data['info']))
						$this->ajaxReturn(0, L('请填写备注！'));
					$data['status'] = 2;
					$data['status_cid'] = 0;
					M('myclient_status')->add($data);
					$str = '邀约失败';
				}

				$save['status'] = $data['status'];
				$save['status_cid'] = $data['status_cid'];
				M('myclient_property')->where('id ='.$mpid)->save($save);

				$info = M('myclient_status')->where('status = 2 AND mpid ='.$mpid)->find();

				if(!isset($str2))
					$str2 = '备注：'.$info['info'];

				// descr 开始  app 需求推送 这个邀约状态的相关信息 给相对应的经纪人
				D('app_push')->customizedCast(M('myclient_property')->where('id ='.$mpid)->getField('uid'),$str2,$str,$data['with_look'],$mpid);
				// 结束 app 需求推送 这个邀约状态的相关信息 给相对应的经纪人

				$html =  '<tr class="collapsed">';
				$html .= '<td  align="left">'.date("Y-m-d H:i",$data['add_time']).'</td>';
                $html .= '<td align="left">'.$str.'</td>';
                $html .= '<td align="left">'.$str2.'</td>';
                $html .= '<td align="center"> '.$info['name'].' </td></tr>';
				$this->ajaxReturn(1,$data['status_cid'],$html);
		}

		if($status == 4)
		{
			$str2 = '';
			if(!empty($data['info']))
				$str2 = '备注：'.$data['info'];
			if($select == 1)
			{
				$data['status'] = 3;
				$data['status_cid'] = 1;
				M('myclient_status')->add($data);

				//开发商确认发短信
				$fph = C('DB_PREFIX');
				//经纪人信息
				$user_info = M('myclient_property')->field('A.pid,A.property,B.stores_id,B.username,B.mobile,C.service,C.name,E.title')
												   ->table("{$fph}myclient_property AS A
														   LEFT JOIN {$fph}user AS B ON A.uid = B.id
														   LEFT JOIN {$fph}stores AS C ON C.id = B.stores_id
														   LEFT JOIN {$fph}property AS E ON E.id = A.property")
												   ->where('A.id ='.$mpid)->find();
				if($user_info['service']){
					//门店服务专员
					$user_info['server_mobile'] = M('user')->where(array('id'=>$user_info['service']))->getfield('mobile');

					//客户信息
					$kehu_info = M('myclient')->field('name,mobile')->where('id ='.$user_info['pid'])->find();

					$send_sms = D('send_sms');
					$result   = $send_sms->Messages($user_info['server_mobile'],$user_info['mobile'],$user_info['username'],$kehu_info['name'],$kehu_info['mobile'],$user_info['title'],$user_info['name'],$mobile_code,'6',false,1,$mobile_code_origin);
				}
				$str = '开发商确认';



				//myclient_property

			}
			else
			{
				if(empty($data['info']))
						$this->ajaxReturn(0, L('请填写备注！'));
				$data['status'] = 3;
				$data['status_cid'] = 0;
				M('myclient_status')->add($data);
				$str = '开发商拒绝';
			}

			//确认带看写入带看过期时间
			if( $data['status_cid'] != 0 )
			{
				$property			= M('property')->field('look_time_status,look_time')->where(array('id'=>$list['pid']))->find();
				$look_time			= empty( $property['look_time_status'] ) ? C('pin_delegate_time') : $property['look_time'];		//带看有效天数
				$look_expire		= strtotime(date('Y-m-d 23:59:59') . " " . ($look_time-1) . " days");												//带看过期时间
				$save['look_expire'] = $look_expire;
			}

			$save['status'] = $data['status'];
			$save['status_cid'] = $data['status_cid'];
			M('myclient_property')->where('id ='.$mpid)->save($save);

			$info = M('myclient_status')->where('status = 3 AND mpid ='.$mpid)->find();

			$html =  '<tr class="collapsed">';
			$html .= '<td align="left">'.date("Y-m-d H:i",$data['add_time']).'</td>';
            $html .= '<td align="left">'.$str.'</td>';
            $html .= '<td align="left">'.$str2.'</td>';
            $html .= '<td align="center"> '.$info['name'].' </td></tr>';
			$this->ajaxReturn(1,$data['status_cid'],$html);
		}

		if($status == 1)
		{
			if(empty($data['info']))
				$this->ajaxReturn(0, L('请填写备注！'));

			$str2 = '';
			if($select == 1)
			{
				$data['status'] = 4;
				$data['status_cid'] = 1;
				$data['affirm_one']  = $this->_post('affirm_one','trim');
				if(empty($data['affirm_one']))
				    $this->ajaxReturn(0, L('请填带看确认单！'));
				M('myclient_status')->add($data);
				$str2 = '带看确认单：'.$data['affirm_one'].'&nbsp;';
				$str = '已带看';
			}
			else
			{
				$data['status'] = 4;
				$data['status_cid'] = 0;
				M('myclient_status')->add($data);
				$str = '未带看';
			}
			$str2 .= '备注：'.$data['info'];
			$save['status'] = $data['status'];
			$save['status_cid'] = $data['status_cid'];
			M('myclient_property')->where('id ='.$mpid)->save($save);

			$info = M('myclient_status')->where('status = 4 AND mpid ='.$mpid)->find();

			$html =  '<tr class="collapsed">';
			$html .= '<td align="left">'.date("Y-m-d H:i",$data['add_time']).'</td>';
			$html .= '<td align="left">'.$str.'</td>';
			$html .= '<td align="left">'.$str2.'</td>';
			$html .= '<td align="center"> '.$info['name'].' </td></tr>';
			$this->ajaxReturn(1,$data['status_cid'],$html);
		}

		if($status == 3)
		{
			if($select == 1)
			{
				$data['intention_price']  = $this->_post('intention_price','trim');
				if(empty($data['intention_price']))
					$this->ajaxReturn(0, L('请填写意向金额'));

				$data['counterfoil']  = $this->_post('counterfoil','trim');
				if(empty($data['counterfoil']))
					$this->ajaxReturn(0, L('请填票据编号'));
				$data['status'] = 5;
				$data['status_cid'] = 1;
				M('myclient_status')->add($data);
				$str = '支付意向金';
				$str2 = '票据编号：'.$data['counterfoil'].'&nbsp;金额：'.$data['intention_price'].'元';
				if(!empty($data['info']))
						$str2 = '票据编号：'.$data['counterfoil'].'&nbsp;金额：'.$data['intention_price'].'元&nbsp;&nbsp;备注：'.$data['info'];
			}
			else if($select == 2)
			{
				$data['intention_price']  = $this->_post('intention','trim');
				if(empty($data['intention_price']))
					$this->ajaxReturn(0, L('请填选择团购费'));
				$data['counterfoil']  = $this->_post('counterfoil','trim');
				if(empty($data['counterfoil']))
					$this->ajaxReturn(0, L('请填票据编号'));
				$data['status'] = 5;
				$data['status_cid'] = 2;
				M('myclient_status')->add($data);
				$str = '支付团购费';
				$str2 = '票据编号：'.$data['counterfoil'].'&nbsp;团购费：'.$data['intention_price'];
				if(!empty($data['info']))
						$str2 = '票据编号：'.$data['counterfoil'].'&nbsp;团购费：'.$data['intention_price'].'&nbsp;&nbsp;备注：'.$data['info'];
			}
			else
			{
				if(empty($data['info']))
					$this->ajaxReturn(0, L('请填写备注'));
				$data['status'] = 5;
				$data['status_cid'] = 0;
				M('myclient_status')->add($data);
				$str = '意向终止';
				$str2 = '备注：'.$data['info'];
			}

			$save['status'] = $data['status'];
			$save['status_cid'] = $data['status_cid'];
			M('myclient_property')->where('id ='.$mpid)->save($save);

			$info = M('myclient_status')->where('status = 5 AND mpid ='.$mpid)->find();

			$html =  '<tr class="collapsed">';
			$html .= '<td align="left">'.date("Y-m-d H:i",$data['add_time']).'</td>';
            $html .= '<td align="left">'.$str.'</td>';
            $html .= '<td align="left">'.$str2.'</td>';
            $html .= '<td align="center"> '.$info['name'].' </td></tr>';
			$this->ajaxReturn(1,$data['status_cid'],$html);
		}
		if($status == 5)
		{
			if($select == 1)
			{
				$signing_time = substr($this->_post('signing_time','trim'), 0,-1);
				$data['deposit'] = substr($this->_post('deposit','trim'), 0,-1);
				$data['info'] = substr($data['info'], 0,-1);
				$arr = explode(",",$signing_time);
				$arr2 = explode(",",$data['deposit']);
				$arr3 = explode(",",$data['info']);
				$str2 = '';
				foreach ($arr as $key => $value) {

					if(empty($arr2[$key]))
						$this->ajaxReturn(0, L('请填写大定金额'));

					if(empty($value))
						$this->ajaxReturn(0, L('请填写签约时间'));

					if(!empty($arr3[$key]))
						$arr3[$key] = "&nbsp;&nbsp;备注：".$arr3[$key];

					$data['signing_time'] .= strtotime($value).',';
					$str2 .= '金额：'.$arr2[$key].'&nbsp;元'.'&nbsp;&nbsp;签约时间：'.$value.''.$arr3[$key].'<br>';

				}
				$data['signing_time'] = substr($data['signing_time'], 0,-1);
				$data['status'] = 6;
				$data['status_cid'] = 1;
				M('myclient_status')->add($data);
				$str = '支付定金';
			}
			else
			{
				$data['info'] = substr($data['info'], 0,-1);
				if(empty($data['info']))
						$this->ajaxReturn(0, L('请填写备注'));
				$data['status'] = 6;
				$data['status_cid'] = 0;
				M('myclient_status')->add($data);

				$tuihui = M('myclient_status')->where('status = 5 AND mpid ='.$mpid)->find();
				if($tuihui['status_cid'] == 1)
				{
					$str = '退回意向金';
				}else if($tuihui['status_cid'] == 2)
				{
					$str = '退回团购费';
				}

				$str2 = '备注：'.$data['info'];
			}

			$save['status'] = $data['status'];
			$save['status_cid'] = $data['status_cid'];
			M('myclient_property')->where('id ='.$mpid)->save($save);
			$info = M('myclient_status')->where('status = 6 AND mpid ='.$mpid)->find();

			$html =  '<tr class="collapsed">';
			$html .= '<td align="left">'.date("Y-m-d H:i",$data['add_time']).'</td>';
            $html .= '<td align="left">'.$str.'</td>';
            $html .= '<td align="left">'.$str2.'</td>';
            $html .= '<td align="center"> '.$info['name'].' </td></tr>';
			$this->ajaxReturn(1,$data['status_cid'],$html);
		}

		if($status == 6)
		{
			if($select == 1)
			{
				$data['status'] = 7;
				$data['status_cid'] = 0;

				$measure = substr($this->_post('measure','trim'), 0,-1);
				$number = substr($this->_post('number','trim'), 0,-1);
				$total_price = substr($this->_post('total_price','trim'), 0,-1);
				$first_price = substr($this->_post('first_price','trim'), 0,-1);
				$loan = substr($this->_post('loan','trim'), 0,-1);
				$tail_price = substr($this->_post('tail_price','trim'), 0,-1);
				$username = $this->_post('username','trim');
				$mobile = $this->_post('mobile','trim');
				$identity = $this->_post('identity','trim');
				$cate_id = substr($this->_post('cate_id','trim'), 0,-1);

				$measure = explode(",",$measure);
				$number = explode(",",$number);
				$total_price = explode(",",$total_price);
				$first_price = explode(",",$first_price);
				$loan = explode(",",$loan);
				$tail_price = explode(",",$tail_price);
				$username = explode("&",$username);
				$mobile = explode("&",$mobile);
				$identity = explode("&",$identity);
				$cate_id = explode(",",$cate_id);

				foreach ($measure as $key => $value) {
					$data['measure'] = $value;
					$data['number'] = $number[$key];
					$data['total_price'] = $total_price[$key];
					$data['first_price'] = $first_price[$key];
					$data['loan'] = $loan[$key];
					$data['tail_price'] = $tail_price[$key];
					$data['cate_id'] = $cate_id[$key];


					if(empty($data['measure']))
							$this->ajaxReturn(0, L('请填写面积'));

					if(empty($data['number']))
							$this->ajaxReturn(0, L('请填写房号'));

					if(empty($data['total_price']))
							$this->ajaxReturn(0, L('请填写总价'));

					if(empty($data['first_price']))
							$this->ajaxReturn(0, L('请填写首付'));

					if(empty($data['loan']))
					{
						if($data['loan'] !== '0')
							$this->ajaxReturn(0, L('请填写贷款'));
					}

					if(empty($data['tail_price']))
					{
						if($data['tail_price'] !== '0')
							$this->ajaxReturn(0, L('请填写尾款'));
					}

					$data['username'] = substr($username[$key],0,-1);
					$data['mobile'] = substr($mobile[$key],0,-1);
					$data['identity'] = substr($identity[$key],0,-1);

					$user_arr1 = explode(",",$data['username']);
					$user_arr2 = explode(",",$data['mobile']);
					$user_arr3 = explode(",",$data['identity']);
					foreach ($user_arr1 as $k => $v) {
						if(empty($v))
							$this->ajaxReturn(0, L('请填写名称'));
						if(empty($user_arr2[$k]))
								$this->ajaxReturn(0, L('请填写手机'));
						if(empty($user_arr3[$k]))
								$this->ajaxReturn(0, L('请填写身份证'));
					}
				}


				$str = '';
				foreach ($measure as $key => $value) {
					$data['measure'] = $value;
					$data['number'] = $number[$key];
					$data['total_price'] = $total_price[$key];
					$data['first_price'] = $first_price[$key];
					$data['loan'] = $loan[$key];
					$data['tail_price'] = $tail_price[$key];
					$data['cate_id'] = $cate_id[$key];

					$data['username'] = substr($username[$key],0,-1);
					$data['mobile'] = substr($mobile[$key],0,-1);
					$data['identity'] = substr($identity[$key],0,-1);

					$user_arr1 = explode(",",$data['username']);
					$user_arr2 = explode(",",$data['mobile']);
					$user_arr3 = explode(",",$data['identity']);

					$strname = '';

					$my_pid = M('myclient_property')->where('id ='.$mpid)->getfield('pid');
					$usermobile = M('myclient')->where('id ='.$my_pid )->getfield('mobile');

					foreach ($user_arr1 as $k => $v) {
						$strname .= '购买人：'.$v.'&nbsp;&nbsp;'.$user_arr3[$k].'，';
						if($user_arr2[$k] == $usermobile)
						{
							M('myclient')->where('mobile ='.$user_arr2[$k])->save(array('identity'=>$user_arr3[$k]));
						}
						$mycount = M('myclient')->where('id ='.$my_pid)->getfield('count') + 1;
						M('myclient')->where('id ='.$my_pid)->save(array('count'=> $mycount));
					}

					M('myclient_status')->add($data);
					$info_cataname = M('property_cate')->where('id='.$data['cate_id'])->find();
					$str .='物业类型：'.$info_cataname['name'].'&nbsp;&nbsp;房号：'.$data['number'].'&nbsp;&nbsp;面积：'.$data['measure'].'㎡　　总价：'.$data['total_price'].'元　　首付：'.$data['first_price'].'元　　贷款：'.$data['loan'].'元　　尾款：'.$data['tail_price'].'元　'.substr($strname,0,-3).'<br>';
				}

				$save['status'] = $data['status'];
				$save['status_cid'] = $data['status_cid'];
				M('myclient_property')->where('id ='.$mpid)->save($save);

				$info = M('myclient_status')->where('status = 7 AND mpid ='.$mpid)->find();

				$html =  '<tr class="collapsed">';
				$html .= '<td align="left">'.date("Y-m-d H:i",$data['add_time']).'</td>';
	            $html .= '<td align="left">签约成交</td>';
	            $html .= '<td align="left">'.$str.'</td>';
	            $html .= '<td align="center"> '.$info['name'].' </td></tr>';
				$this->ajaxReturn(1,$data['status_cid'],$html);
			}
			else
			{
				//毁约操作
				$data['status'] = 8;
				$data['status_cid'] = 0;

				if(empty($data['info']))
					$this->ajaxReturn(0, L('请填写备注'));
				M('myclient_status')->add($data);

				$save['status'] = $data['status'];
				$save['status_cid'] = $data['status_cid'];
				M('myclient_property')->where('id ='.$mpid)->save($save);

				$str = '备注：'.$data['info'];
				$html =  '<tr class="collapsed">';
				$html .= '<td align="left">'.date("Y-m-d H:i",$data['add_time']).'</td>';
	            $html .= '<td align="left">违约</td>';
	            $html .= '<td align="left">'.$str.'</td>';
	            $html .= '<td align="center"> '.$info['name'].' </td></tr>';
				$this->ajaxReturn(1,$data['status_cid'],$html);


			}

		}

		$this->ajaxReturn(0,'添加失败');
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

    public function _before_add(){
        $with_look = $this->_request('with_look','intval');
        if($with_look > 2) $with_look=2;
        !$with_look && $this->error('参数出错！');
        $this->assign('with_look',$with_look);
    }

    public function add(){
    	if($_POST){
			//判断写入经纪人信息
			$mobile     = $this->_post('mobile','trim');//客户电话
			$pid        = $this->_post('pid','intval');//楼盘id
			//$username   = $this->_post('username','trim');
			$tel        = $this->_post('tel','trim');
			$visit_time = strtotime($this->_post('visit_time','trim'));
			$with_look  = 1;
			$author = $_COOKIE['admin']['username'];//操作人
	        $uid= M('user')->where("mobile='".$tel."'")->getfield('id');
			if(!$uid){
				$this->error('该经纪人不存在,请先添加经纪人');
				exit;
			}
			//判断是否在有效期内
			$gettime = time();
			$myclient_id = M('myclient')->field("id")->where(array('mobile'=>$mobile))->find();
	        $myclientid = $myclient_id['id'];
	        $info = M('property')->field('title,protection_time,protection_time_status')->where(array('id'=>$pid))->find();
	        $title =  $info['title'];
	        $str = 'id,pid,property,with_look,status,add_time,status_cid,protection_expire,look_expire';
	        $mproperty = M('myclient_property')->field($str)->where(array('property'=>$pid,'pid'=>$myclientid))->order('id DESC')->find();

	        /*
	         * 判断是否有流程正在进行
	         */
	        $is_ok	= FALSE;
	        if(empty( $mproperty ))																$is_ok	= TRUE;	//第一次报备
	        else if($mproperty['status_cid'] == 0)												$is_ok	= TRUE; //流程已终止
	        else if($mproperty['status'] == 1 && $mproperty['protection_expire'] < $gettime)	$is_ok	= TRUE; //已经报备失效
	        else if($mproperty['status'] == 3 && $mproperty['look_expire'] < $gettime)			$is_ok	= TRUE; //已经带看失效


			if( $is_ok == FALSE )
			{
				$this->error('该客户已被其他经纪人报备此楼盘.');
			}

			$protection_time	= empty( $info['protection_time_status'] ) ? C('pin_protection_time') : $info['protection_time'];	//报备有效天数
			$protection_expire	= strtotime(date('Y-m-d 23:59:59') . " " . ($protection_time-1) . " days");

	        if(!$myclientid){
	        	$datam['name'] = $this->_post('name','trim');
	        	$datam['gender'] = $this->_post('gender','intval');
	        	$datam['mobile'] = $mobile;
	        	$myclientid = M('myclient')->add($datam);
	        }

			//写入报备数据
	        $data['uid']             = $uid;
	        $data['pid']             = $myclientid;
	        $data['property']        = $pid;
	        $data['status']          = 1;
	        //$data['update_time']     = $gettime;
	        $data['with_look']       = $with_look;

	        $data['protection_expire']	= $protection_expire;		//报备失效时间

	        $data['add_time']        	= $gettime;
	        if($data['with_look']==1){
			    $data['visit_time'] = $visit_time;
	        }
	        if (!$mpid = M('myclient_property')->add($data)){
				$this->error('报备数据添加失败');
				exit;
			}
			//写入记录*流程
			$datas['mpid']       = $mpid;
			$datas['pid']        = $pid;
			$datas['status']     = 1;
			$datas['status_cid'] = 1;
			$datas['with_look']  = $with_look;
			$datas['add_time']   = $gettime;
			$datas['name']       = $author;
			if($datas['with_look']==1){
			    $datas['visit_time'] = $visit_time;
	        }
			if (false === M('myclient_status')->add($datas)){
				$this->error('报备记录数据添加失败');
				exit;
			}
			$this->success(L('operation_success'));
    	}else{
    		$with_look = $this->_request('with_look','intval');
	        if($with_look > 2) $with_look=2;
	        !$with_look && $this->error('参数出错！');
	        $this->assign('with_look',$with_look);
    		$this->display();
    	}

    }
    public function _before_edit(){
        $id = $this->_get('id','intval');

    }

    protected function _before_update($data){

        return $data;
    }

	public function delete(){
		$mpid = $this->_get('id','intval');
		if($mpid)
		{
			$my_p = M('myclient_property')->field('uid,pid')->where('id = '.$mpid)->find();;
			$usercount = M('myclient_property')->where('pid = '.$my_p['pid'])->count('id');
			if($usercount == 1)
			{
				M('myclient')->where('id ='.$my_p['pid'])->delete();
			}
			M('myclient_property')->where('id ='.$mpid)->delete();
			M('myclient_status')->where('mpid ='.$mpid)->delete();
			$this->ajaxReturn(1, '删除成功!');
		}
		$this->ajaxReturn(0, '参数出错!');
	}

    /**
     * ajax检测会员是否存在
     */
    public function ajax_check_mobile() {
        $mobile = $this->_get('tel', 'trim');
        $id = $this->_get('id', 'intval');
        $user_info = M('user')->field('username')->where("mobile='".$mobile."'")->find();
        if($user_info){
        	 $this->ajaxReturn(1,'',$user_info);
        }else{
        	$this->ajaxReturn(0, '该经纪人不存在,请先添加经纪人');
        }
    }

    //搜索带看楼盘
    public function input_search(){
    	$fph=C('DB_PREFIX');
        $title = $this->_post('title','trim');
        !$title && $this->ajaxReturn(0, '请输入要搜索的内容');
        if($title){
			$time=time();
			$list = M('property')->field("A.id,A.title")->table("{$fph}property AS A LEFT JOIN {$fph}property_cooperation AS B ON A.id=B.pid")->where('A.title like "%'.$title.'%" AND A.status=1 AND B.term_start<='.$time.' AND B.term_end >='.$time)->order('add_time DESC')->limit(50)->select();
            $str = "";
			$str .= "<ul class='popup'>";
				foreach($list as $val) {
					$title = $val['title'];
					$str .= "<li rel=".$val['id'].">".msubstr($title,0,35,'utf-8',true)."</li>";
				}
			$str .= '</ul>';
        }else{
         $str .= "<div>无数据,请检查输入的关键字</div>";
        }
        $this->ajaxReturn(1, '未知错误！', $str);
    }

	//临时用，用后删除
	public function index_list() {
		$fph = C('DB_PREFIX');
		$where = '1=1';
		$username = $this->_get('username','trim');
		$name     = $this->_get('name','trim');
		$mobile   = $this->_get('mobile', 'trim');
		$loupan   = $this->_get('loupan', 'trim');
		$status   = $this->_get('status', 'trim');
		$output = $this->_get('output','trim');//判断是否导出
		$time_start = strtotime($this->_request('time_start', 'trim'));
		$time_end = strtotime($this->_request('time_end', 'trim'))+(24*60*60-1);
		$add_time_start = strtotime($this->_request('add_time_start', 'trim'));
		$add_time_end = strtotime($this->_request('add_time_end', 'trim'))+(24*60*60-1);
		$mystatus = $this->_get('mystatus', 'trim');

		if($username){
		    $where .= ' AND D.username = "'.$username.'"';
		}
		if($name){
		    $where .= " AND B.name='".$name."'";
		}
		if($mobile){
		    $where .= " AND B.mobile='".$mobile."'";
		}
		if($loupan)
		{
		    $where .= ' AND C.title = "'.$loupan.'"';
		}
		if($status){
		    $where .= ' AND A.status='.$status;
		}
		if($time_start && $time_end)
		{
		    $where .=' AND A.update_time between '.$time_start.' AND '.$time_end;
		}
		if($add_time_start && $add_time_end)
		{
		    $where .=' AND A.add_time between '.$add_time_start.' AND '.$add_time_end;
		}
		if($mystatus)
		{
		    $where .= ' AND A.with_look = "'.$mystatus.'"';
		}

		//获取案场人员 负责楼盘
		$case_property = M('case_field')->where('admin_id ='.$_COOKIE['admin']['id'])->getfield('property');
		if(!empty($case_property))
		{
		    $where .= ' AND C.id in('.$case_property.')';
		}

		$count = $this->_cate_mod->table("{$fph}myclient_property AS A
						INNER JOIN {$fph}myclient AS B ON A.pid = B.id
						INNER JOIN {$fph}property AS C ON A.property = C.id
						INNER JOIN {$fph}user as D ON B.uid = D.id
						")
					->where($where)
					->count('A.id');
		import("ORG.Util.Page");
		$p = new Page($count, 20);
		$page = $p->show();

		$str = 'A.id,A.status,A.update_time,A.buy_product,A.expects_rime,A.buy_time,A.visit_time,A.with_look,A.add_time,B.gender,B.name,B.mobile,C.id as pid,C.title,D.username,D.mobile as user_mobile,D.share_id';
		if($output!=1){//判断没有导出
		   $myclient_list = $this->_cate_mod->field($str)
					->table("{$fph}myclient_property AS A
						INNER JOIN {$fph}myclient AS B ON A.pid = B.id
						INNER JOIN {$fph}property AS C ON A.property = C.id
						INNER JOIN {$fph}user as D ON B.uid = D.id
						")
					->where($where)
					->limit($p->firstRow.','.$p->listRows)
					->order('A.add_time DESC')->select();
		}else{
		  $myclient_list = $this->_cate_mod->field($str)
					->table("{$fph}myclient_property AS A
						INNER JOIN {$fph}myclient AS B on A.pid = B.id
						INNER JOIN {$fph}property AS C on C.id = A.property
						INNER JOIN {$fph}user AS D on A.uid = D.id
						INNER JOIN {$fph}myclient_status AS E on A.id = E.mpid
						")
					->where($where)
					->order('A.add_time DESC')->select();

		}
		foreach($myclient_list as  $k => $v)
		{
		    $myclient_list[$k]['property_name'] = M('hezuo_property_product')->where(array('pid'=>$v['pid']))->getfield('name');
		    $myclient_list[$k]['share_name'] = M('user')->where(array('id'=>$v['share_id']))->getfield('username');

		}

		if($output!=1){//判断没有导出
		    $this->assign('myclient_list', $myclient_list);
		    $this->assign('page_list', $page);
		    $this->_search();
		    $p = $this->_get('p','intval',1);
		    $this->assign('p',$p);
		    $this->display();
		}else{
			if($_COOKIE['admin']['id'] != 1 and $_COOKIE['admin']['id'] != 11)
			{
			    $this->error('没有权限导出');
			    exit;
			}
			//判断有导出
			Vendor("Classes.PHPExcel");
			Vendor("Classes.PHPExcel.php");

			//创建处理对象实例
			$objPhpExcel=new PHPExcel();
			$objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
			//设置表格的宽度  手动
			$objPhpExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
			$objPhpExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
			$objPhpExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
			$objPhpExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
			//设置标题
			$rowVal = array(0=>'序号',1=>'ID', 2=>'经纪人', 3=>'手机号', 4=>'带看模式',5=>'意向楼盘',6=>'购买产品',7=>'用户',8=>'性别',9=>'手机号码',10=>'状态',11=>'发布时间',12=>'报备时间');
			foreach ($rowVal as $k=>$r){
				$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)
				->getFont()->setBold(true);//字体加粗
				$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
				getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
				$objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
			}
			$objPhpExcel->getActiveSheet()->setCellValue('A1', 'ID');
			$objPhpExcel->getActiveSheet()->setCellValue('B1', '经纪人');
			$objPhpExcel->getActiveSheet()->setCellValue('C1', '手机号码');
			$objPhpExcel->getActiveSheet()->setCellValue('D1', '带看模式');
			$objPhpExcel->getActiveSheet()->setCellValue('E1', '意向楼盘');
			$objPhpExcel->getActiveSheet()->setCellValue('F1', '购买产品');
			$objPhpExcel->getActiveSheet()->setCellValue('G1', '客户');
			$objPhpExcel->getActiveSheet()->setCellValue('H1', '性别');
			$objPhpExcel->getActiveSheet()->setCellValue('I1', '手机号码');
			$objPhpExcel->getActiveSheet()->setCellValue('J1', '状态');
			$objPhpExcel->getActiveSheet()->setCellValue('K1', '到访时间');
			$objPhpExcel->getActiveSheet()->setCellValue('L1', '状态更新时间');
			$objPhpExcel->getActiveSheet()->setCellValue('M1', '报备时间');
			//设置当前的sheet索引 用于后续内容操作
			$objPhpExcel->setActiveSheetIndex(0);
			$objActSheet=$objPhpExcel->getActiveSheet();
			//设置当前活动的sheet的名称
			$title="公司客户录";
			$objActSheet->setTitle($title);
			//设置单元格内容
			foreach($myclient_list as $k => $v)
			{

				if($v['status']==0){
					$v['status3']="开发商拒绝";
				}elseif ($v['status']==1){
					$v['status3']="已报备";
				}elseif ($v['status']==2){
					$v['status3']="开发商确认";
				}elseif ($v['status']==3){
					$v['status3']="已带看";
				}elseif ($v['status']==5) {
					$v['status3']="已结佣";
				}elseif ($v['status']==4 && $v['buy_time']!=0) {
					$v['status3']="已成交";
				}elseif ($v['status']==4 && $v['buy_time']==0) {
					$v['status3']="已带看";
				}

				if($v['gender']==0){
					$v['gender']='女';
				}elseif($v['gender']==1){
					$v['gender']='男';
				}else {
					$v['gender']='-';
				}

				$v['with_look']==1 ? $v['visit_time']=date('Y-m-d',$v['visit_time']) : $v['visit_time']='-' ;
				$v['with_look']==1 ? $v['with_look']='由我带看' : $v['with_look']='委托带看' ;
				   $v['status']>=4 ? $v['status']=$v['property_name'] : $v['status']='未购买';
			  $v['update_time']!=0 ? $v['update_time']=date('Y-m-d H:i',$v['update_time']) : $v['update_time']='-' ;

				$num=$k+2;
				$objPhpExcel->setActiveSheetIndex(0)
				//Excel的第A列，uid是你查出数组的键值，下面以此类推
				->setCellValue('A'.$num, $v['id'])
				->setCellValue('B'.$num, $v['username'])
				->setCellValue('C'.$num, $v['user_mobile'])
				->setCellValue('D'.$num, $v['with_look'])
				->setCellValue('E'.$num, $v['title'])
				->setCellValue('F'.$num, $v['status'])
				->setCellValue('G'.$num, $v['name'])
				->setCellValue('H'.$num, $v['gender'])
				->setCellValue('I'.$num, $v['mobile'])
				->setCellValue('J'.$num, $v['status3'])
				->setCellValue('K'.$num, $v['visit_time'])
				->setCellValue('L'.$num, $v['update_time'])
				->setCellValue('M'.$num, date('Y-m-d H:i',$v['add_time']));

			}
			$name=date('Y-m-d');//设置文件名
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Transfer-Encoding:utf-8");
			header("Pragma: no-cache");
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$title.'_'.urlencode($name).'.xls"');
			header('Cache-Control: max-age=0');
			$objWriter = PHPExcel_IOFactory::createWriter($objPhpExcel, 'Excel5');
			$objWriter->save('php://output');

		}
    }

    //导入数据 2014.9.16 H.J.H
    public function out_put(){
    	if($_POST['leadExcel'] == "true"){

    		$tmp_name = $_FILES['inputExcel']['tmp_name'];

    		Vendor("Classes.PHPExcel");
    		Vendor("Classes.PHPExcel.php");
    		Vendor("Classes.PHPExcel.IOFactory");
    		Vendor("Classes.PHPExcel.Reader.Excel5");
    		$objReader = PHPExcel_IOFactory::createReader('Excel5');
    		$objPHPExcel = $objReader->load($tmp_name);
    		$sheet = $objPHPExcel->getSheet(0);
    		$highestRow = $sheet->getHighestRow(); // 取得总行数
    		$highestColumn = $sheet->getHighestColumn(); // 取得总列数
    		$k = 0;
    		for($j=3;$j<=$highestRow;$j++){
    			$add_time = $objPHPExcel->getActiveSheet()->getCell("A".$j)->getValue();
    			$data['name'] = $objPHPExcel->getActiveSheet()->getCell("B".$j)->getValue();
    			$data['mobile'] = $objPHPExcel->getActiveSheet()->getCell("D".$j)->getValue();
    			$user_mobile  = $objPHPExcel->getActiveSheet()->getCell("E".$j)->getValue();

    	        $data['uid']= M('user')->where('mobile='.$user_mobile)->getField('id');
    	        $data['gender']=2;
    	        if($data['uid']){
    	             $last_id = $this->_mod->add($data);//生成id
    	             if($last_id){
    	             	echo "第".$j."行导入成功,fph_myclient表第：".$last_id."条 ";
		    	         	//导入fph_myclient_property
		    	           $datas['pid']=$last_id;
		    	           $datas['property']=10;
		    	           $datas['status']=3;
		    	           $datas['update_time']= strtotime($add_time);

		    	           $datas['visit_time']= strtotime($add_time);
		    	           $datas['with_look']=1;
		    	           $datas['add_time']= strtotime($add_time);

		    	           $last_id=$this->_cate_mod->add($datas);
    	             	if($last_id){
    	             		echo "fph_myclient_property表第：".$last_id."条<br/>";
    	             	}else {
    	             		echo "fph_myclient_property表导入失败<br/>";
    	             	}
    	             }else {
    	             	 echo "第".$j."行导入失败<br/>";
    	             }
    	        }else {
    	        	echo "第".$j."行导入失败,业务员不存在<br/>";
    	        }
    		}

    	}
    }


    //批量修改老大的下线的用户时间
    public function add_save(){
    	if($_POST['leadExcel'] == "true"){
    		$tmp_name = $_FILES['inputExcel']['tmp_name'];
    		Vendor("Classes.PHPExcel");
    		Vendor("Classes.PHPExcel.php");
    		Vendor("Classes.PHPExcel.IOFactory");
    		Vendor("Classes.PHPExcel.Reader.Excel5");
    		$objReader = PHPExcel_IOFactory::createReader('Excel5');
    		$objPHPExcel = $objReader->load($tmp_name);
    		$sheet = $objPHPExcel->getSheet(0);
    		$highestRow = $sheet->getHighestRow(); // 取得总行数
    		$highestColumn = $sheet->getHighestColumn(); // 取得总列数
    		$k = 0;
    		for($j=2;$j<=$highestRow;$j++){

    			$mobile   = $objPHPExcel->getActiveSheet()->getCell("C".$j)->getValue();

    			$hour=mt_rand(8,17);
    			$minute=mt_rand(0,60);
    			$second=mt_rand(0,60);
    			$month=mt_rand(7,9);
    			$day=mt_rand(1,22);

    			$time=mktime($hour,$minute,$second,$month,$day,2014);

    			$data['last_time']=$time;
    			$data['reg_time']=$time;

    			//ip数组
    			$ip=array('114.80.166.240','114.80.68.233','114.80.163.38','211.144.219.66','124.75.29.75','220.248.93.53',
    					'119.18.145.86','119.18.146.118','58.24.104.222','58.24.104.222','58.24.104.222','58.24.104.222',
    					'58.24.104.222','221.137.112.47','119.18.147.200','220.234.244.5','112.64.180.114','58.246.200.114',
    					'61.152.94.108','58.246.37.194','222.66.37.226','112.65.135.54','221.137.77.193','220.248.3.202',
    					'222.73.69.198','220.248.3.203', '222.72.132.166','61.172.244.107','61.172.249.94','222.68.173.101',
    					'114.80.67.252','210.51.57.168','222.73.27.232','58.246.76.76','61.172.207.105', '61.172.207.107',
    					'61.172.207.53','61.172.207.110','211.144.106.58','222.73.28.42', '61.172.244.108','210.51.54.197',
    					'61.172.249.96','61.152.96.22','222.73.161.107','222.73.161.124','222.73.161.112','222.73.28.120',
    					'222.66.116.108','222.66.116.109');

    			$data['last_ip']=$ip[mt_rand(0,49)];

    			if(M('user')->where('share_id=1332 and mobile='.$mobile)->save($data)){
    				echo "第".$j."行修改成功,fph_user表  mobile=".$mobile."<br/>";
    			}else {
    				echo "第".$j."行导入失败<br/>";
    			}
    		}
    	}
    }

    //导出数据
    public function export_excel()
    {
    	$fph = C('DB_PREFIX');
    	$with_look  = $this->_get('with_look','intval');
    	$mobile   = $this->_get('mobile', 'trim');
    	$user_mobile   = $this->_get('user_mobile', 'trim');
    	$title   = $this->_get('title', 'trim');
    	$status = $this->_get('status','trim');
    	$add_time_start = $this->_request('add_time_start', 'trim');
		$add_time_end = $this->_request('add_time_end', 'trim');
		$status_time_start = $this->_request('status_time_start', 'trim');
		$status_time_end = $this->_request('status_time_end', 'trim');
		$where = '1=1';

		if($mobile)
    	{
    		$where .= ' AND B.mobile ="'.$mobile.'"';
    	}
    	if($user_mobile)
    	{
    		$where .= ' AND D.mobile ="'.$user_mobile.'"';
    	}
    	if($title)
    	{
    		$where .= ' AND C.title like "%'.$title.'%"';
    	}
    	if($status)
    	{
    		$statusarr = explode(',', $status);
    		$where .= ' AND A.status ="'.$statusarr[0].'"  AND A.status_cid ="'.$statusarr[1].'"';
    	}

		if($add_time_start && $add_time_end)
		{
		    $where .=' AND A.add_time between '.strtotime($add_time_start).' AND '.(strtotime($add_time_end)+(24*60*60-1));
		}

		if($status && $status_time_start && $status_time_end)
		{
			$statusarr = explode(',', $status);
		    $where .=' AND E.status = '.$statusarr[0].' AND E.add_time between '.strtotime($status_time_start).' AND '.(strtotime($status_time_end)+(24*60*60-1));
		}
		else
		{
			$where .= ' AND E.status = 1';
		}

    	//判断有导出
		Vendor("Classes.PHPExcel");
		Vendor("Classes.PHPExcel.php");

		//创建处理对象实例
		$objPhpExcel=new PHPExcel();
		$objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
		//设置表格的宽度  手动
		$objPhpExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
		$objPhpExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
		$objPhpExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
		$objPhpExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
		//设置标题
		$rowVal = array(0=>'ID',1=>'报备人', 2=>'客户', 3=>'带看楼盘', 4=>'当前状态',5=>'报备时间',6=>'状态时间',7=>'案场人员');
		foreach ($rowVal as $k=>$r){
			$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)
			->getFont()->setBold(true);//字体加粗
			$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
			getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
			$objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
		}
		$objPhpExcel->getActiveSheet()->setCellValue('A1', 'ID');
		$objPhpExcel->getActiveSheet()->setCellValue('B1', '报备人');
		$objPhpExcel->getActiveSheet()->setCellValue('C1', '客户');
		$objPhpExcel->getActiveSheet()->setCellValue('D1', '带看楼盘');
		$objPhpExcel->getActiveSheet()->setCellValue('E1', '当前状态');
		$objPhpExcel->getActiveSheet()->setCellValue('F1', '报备时间');
		$objPhpExcel->getActiveSheet()->setCellValue('G1', '状态时间');
		$objPhpExcel->getActiveSheet()->setCellValue('H1', '案场人员');

		//设置当前的sheet索引 用于后续内容操作
		$objPhpExcel->setActiveSheetIndex(0);
		$objActSheet=$objPhpExcel->getActiveSheet();
		//设置当前活动的sheet的名称
		$title="公司客户录";
		$objActSheet->setTitle($title);

		$str = 'A.id,A.uid,A.property,A.status,A.status_cid,A.add_time,B.name,B.mobile,C.title,D.username,D.mobile as user_mobile';

		$list = M('myclient_property')->field($str)
				->table("{$fph}myclient_property AS A
						INNER JOIN {$fph}myclient AS B on A.pid = B.id
						INNER JOIN {$fph}property AS C on C.id = A.property
						INNER JOIN {$fph}user AS D on A.uid = D.id
						INNER JOIN {$fph}myclient_status AS E on A.id = E.mpid
						")
				->where($where.' AND A.with_look ='.$with_look)
				->order('A.add_time DESC')->select();
		//设置单元格内容
		foreach($list as $k => $v)
		{

			$list[$k]['case_name'] = ' ';
			$case = M('case_field')->select();
			foreach ($case as $k1 => $v1) {
				if(strstr(''.$v1['property'].'',''.$v['property'].''))
				{
					$list[$k]['case_name'] .= M('admin')->field('username')->where('id ='.$v1['admin_id'])->getfield('username').',';
				}
			}
			$v['case_name'] = substr($list[$k]['case_name'], 0,-1);
			$v['time'] =  M('myclient_status')->where('mpid ='.$v['id'])->order('status DESC')->getfield('add_time');
			if($v['status'] == 1)
			{
				if($with_look == 1)
				{
					$v['status'] = '带看申请';
				}
				else
				{
					$v['status'] = '委托申请';
				}
			}

			if($v['status'] == 2)
			{
				if($v['status_cid'] == 1)
				{
					$v['status'] = '邀约成功';
				}
				else
				{
					$v['status'] = '邀约失败';
				}
			}

			if($v['status'] == 3)
			{
				if($v['status_cid'] == 1)
				{
					$v['status'] = '开发商确认';
				}
				else
				{
					$v['status'] = '开发商拒绝';
				}
			}

			if($v['status'] == 4)
			{
				if($v['status_cid'] == 1)
				{
					$v['status'] = '已到访';
				}
				else
				{
					$v['status'] = '未到访';
				}
			}

			if($v['status'] == 5)
			{
				if($v['status_cid'] == 1)
				{
					$v['status'] = '支付意向金';
				}
				else if($v['status_cid'] == 2)
				{
					$v['status'] = '支付团购费';
				}
				else
				{
					$v['status'] = '意向终止';
				}
			}

			if($v['status'] == 6)
			{
				if($v['status_cid'] == 1)
				{
					$v['status'] = '支付定金';
				}
				else
				{
					if($v['zhongzhi'] == 1)
					{
						$v['status'] = '退回意向金';
					}
					else
					{
						$v['status'] = '退回团购费';
					}
				}
			}

			if($v['status'] == 7)
			{
				$v['status'] = '签约成交';
			}

			$num=$k+2;
			$objPhpExcel->setActiveSheetIndex(0)
			//Excel的第A列，uid是你查出数组的键值，下面以此类推
			->setCellValue('A'.$num, $v['id'])
			->setCellValue('B'.$num, $v['username'].'-'.$v['user_mobile'])
			->setCellValue('C'.$num, $v['name'].'-'.$v['mobile'])
			->setCellValue('D'.$num, $v['title'])
			->setCellValue('E'.$num, $v['status'])
			->setCellValue('F'.$num, date('Y-m-d H:i',$v['add_time']))
			->setCellValue('G'.$num, date('Y-m-d H:i',$v['time']))
			->setCellValue('H'.$num, $v['case_name']);

		}
		$name=date('Y-m-d');//设置文件名
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Transfer-Encoding:utf-8");
		header("Pragma: no-cache");
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$title.'_'.urlencode($name).'.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPhpExcel, 'Excel5');
		$objWriter->save('php://output');

    }

    public function testaaa()
    {
    	$list = M('myclient_property')->select();
    	foreach ($list as $key => $value) {
    		$mystatus = M('myclient_status')->where('status = 1 AND mpid ='.$value['id'])->find();
    		if(!empty($mystatus))
    		{
    			$save['add_time'] = $mystatus['add_time'];
    			M('myclient_property')->where('id ='.$value['id'])->save($save);
    		}
    	}
    }

    /*
    *@Descriptions：AJAX楼盘名称获取
    *@Date:2014-12-15
    *@Author: wsj
    */
    public function ajax_title()
    {
    	$title = $this->_post('title','trim');

    	if(empty($title))
        	$this->ajaxReturn(0, '请输入要搜索的内容');

        $list = M('property')->field("id,title")->where('title like "%'.$title.'%"')->select();
		$str = "";
		if(!empty($list))
		{
			$str .= "<ul class='popup_s'>";
				foreach($list as $val) {
					$str .= "<li rel=".$val['title'].">".msubstr($val['title'],0,35,'utf-8',true)."</li>";
				}
			$str .= '</ul>';
		}
		//else
		//{
		//	$str .= "<div>无数据,请检查输入的关键字</div>";
		//}
        $this->ajaxReturn(1, '搜索', $str);
    }

}