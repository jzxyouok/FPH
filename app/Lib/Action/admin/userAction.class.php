<?php
/**
 * 用户信息管理
 */
class userAction extends backendAction
{

    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('user');
    }

    public function index() {
        //搜索条件
        $username   = $this->_get('keyword', 'trim');
        $mobile     = $this->_get('mobile', 'trim');
        $city_id     = $this->_get('city_id', 'intval');
        $time_start = $this->_request('time_start', 'trim');
        $time_end   = $this->_request('time_end', 'trim');
		$stores_id  = $this->_request('stores_id', 'trim');
        $this->assign('search', array(
            'keyword' => $username,
            'mobile' => $mobile,
            'city_id' => $city_id,
            'time_start' => $time_start,
            'time_end'=> $time_end,
			'stores_id'=>$stores_id
        ));
        //查询所属城市
        $spid_city = M('city')->where(array('id'=>$city_id))->getField('spid');
        if($spid_city==0 ){
            $spid_city = $city_id;
        }else{
            $spid_city .= $city_id;
        }
        $this->assign('selected_ids_city',$spid_city);
        $where = '1=1';
        if($username){
            $where .= ' AND username like "%'.$username.'%"';
        }
        if($mobile){
            $where .= ' AND mobile = "'.$mobile.'"';
        }
        if($city_id){
            $where .=' AND city_id in(select id from fph_city where id = '.$city_id.' or spid RLIKE "[[:<:]]'.$city_id.'[[:>:]]")';
        }
        if($time_start AND $time_end)
        {
            $where .=' AND reg_time between '.strtotime($time_start).' AND '.strtotime($time_end);
        }
		if($stores_id == '1'){
			$where .=' AND stores_id!=0';
		}elseif($stores_id == '0'){
			$where .=' AND stores_id=0';
		}

        //查询用户数据
        $count = $this->_mod->where($where)->count('id');
        //分页
        $p = new Page($count, 25);
        $list = M('user')->field('id,origin,username,share_id,mobile,gender,reg_time,last_time,last_ip,status,stores_id,internal,city_id')->where($where)->limit($p->firstRow.','.$p->listRows)->order('reg_time DESC')->select();
        foreach ($list as $key => $value) {
            //查询推荐用户名称 与 所属公司  所属门店
            $list[$key]['tuijianzhe'] = M('user')->field('username')->where(array('id'=>$value['share_id']))->getField('username');
            //所属门店
            $stores_info = M('stores')->field('pid,name')->where(array('id'=>$value['stores_id']))->find();
            $list[$key]['stores_name'] = $stores_info['name'];
            //所属公司
            $list[$key]['company_name'] = M('company')->where(array('id'=>$stores_info['pid']))->getField('short_name');
            //所在城市
            if($value['city_id']){
                 $spid = M('city')->where('id='.$value['city_id'])->getField('spid');
                 $city_id = explode('|', $spid);
                if($city_id[1]){
                    $list[$key]['sheng_name']= M('city')->where('id='.$city_id[0])->getField('name');
                    $list[$key]['shi_name'] = M('city')->where('id='.$city_id[1])->getField('name');
                }elseif(!$city_id[0]){
                    $list[$key]['sheng_name']= M('city')->where('id='.$value['city_id'])->getField('name');
                    $list[$key]['shi_name'] = '';
                }else{
                    $list[$key]['sheng_name']= M('city')->where('id='.$city_id[0])->getField('name');
                    $list[$key]['shi_name'] = M('city')->where('id='.$value['city_id'])->getField('name');
                }
            }
        }
        $page = $p->show();
        $this->assign('page', $page);
        $this->assign('list', $list);

        //获取起始时间
        $start = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $end = mktime(23,59,59,date("m"),date("d"),date("Y"));
        $time_count = $this->_mod->where('reg_time BETWEEN '.$start.' AND '.$end)->count('id');
        $this->assign('time_count', $time_count);
        $this->assign('user_count', $count);

        $this->display();
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
    
    public function _before_add() {

    }

    public function _before_insert($data) {
        $city_id = $this->_post('city_id','intval');
        $city_pid= M('city')->where(array('id'=>$city_id))->getfield('pid');
        !$city_pid && $this->error('请完整选择省市区');
        if( ($data['password']!='')&&(trim($data['password'])!='') ){
            $data['password'] = $data['password'];
        }else{
            unset($data['password']);
        }
        $data['store_status'] = 1;
		$data['origin']       = 4;
        $data['city_id']      = $city_id;
        return $data;
    }

    public function _after_insert($id) {
        $img = $this->_post('img','trim');
        $this->user_thumb($id,$img);
    }
	
	public function _before_edit() {
       $id = $this->_get('id','intval');
	   $user_info = D('user')->user_info($id);
	   
	   $spid_city = M('city')->where(array('id'=>$user_info['city_id']))->getField('spid');
        if( $spid_city==0 ){
            $spid_city = $user_info['city_id'];
        }else{
            $spid_city .= $user_info['city_id'];
        }
        $this->assign('selected_ids_city',$spid_city);
	   
	   $stores_info = M('stores')->field('id,name')->where(array('id'=>$user_info['stores_id']))->find();
	   $this->assign('stores_info', $stores_info);
    }

    public function _before_update($data) {
        $city_id = $this->_post('city_id','intval');
        $city_pid= M('city')->where(array('id'=>$city_id))->getfield('pid');
        !$city_pid && $this->error('请完整选择省市区');
        if( ($data['password']!='')&&(trim($data['password'])!='') ){
            $data['password'] = md5($data['password']);
        }else{
            unset($data['password']);
        }
        $data['store_status'] = 1;
        $data['city_id']      = $city_id;
        return $data;
    }

    public function _after_update($id){
        $img = $this->_post('img','trim');
        if($img){
            $this->user_thumb($id,$img);
        }
    }

    public function user_thumb($id,$img){
        $img_path= avatar_dir($id);
        //会员头像规格
        $avatar_size = explode(',', C('pin_avatar_size'));
        $paths =C('pin_attach_path');

        foreach ($avatar_size as $size) {
            if($paths.'avatar/'.$img_path.'/' . md5($id).'_'.$size.'.jpg'){
                @unlink($paths.'avatar/'.$img_path.'/' . md5($id).'_'.$size.'.jpg');
            }
            !is_dir($paths.'avatar/'.$img_path) && mkdir($paths.'avatar/'.$img_path, 0777, true);
            Image::thumb($paths.'avatar/temp/'.$img, $paths.'avatar/'.$img_path.'/' . md5($id).'_'.$size.'.jpg', '', $size, $size, true);
        }

        @unlink($paths.'avatar/temp/'.$img);
    }

    public function add_users(){
        if (IS_POST) {
            $users = $this->_post('username', 'trim');
            $users = explode(',', $users);
            $password = $this->_post('password', 'trim');
            $gender = $this->_post('gender', 'intavl');
            $reg_time= time();
            $data=array();
            foreach($users as $val){
                $data['password']=$password;
                $data['gender']=$gender;
                $data['reg_time']=$reg_time;
                if($gender==3){
                    $data['gender']=rand(0,1);
                }
                $data['username']=$val;
                $this->_mod->create($data);
                $this->_mod->add();
            }
            $this->admin_log($this->_mod,$this->_mod->getLastInsID());
            $this->success(L('operation_success'));
        } else {
            $this->display();
        }
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

    public function ajax_upload_imgs() {
        //上传图片
        if (!empty($_FILES['img']['name'])) {
            $result = $this->_upload($_FILES['img'], 'avatar/temp/' );
            if ($result['error']) {
                $this->error($result['info']);
            }else {
                $data['img'] =  $result['info'][0]['savename'];
                $this->ajaxReturn(1, L('operation_success'), $data['img']);
            }


        } else {
            $this->ajaxReturn(0, L('illegal_parameters'));
        }
    }

    /**
     * ajax检测会员是否存在
     */
    public function ajax_check_name() {
        $name = $this->_get('username', 'trim');
        $id = $this->_get('id', 'intval');
        if ($this->_mod->name_exists($name,  $id)) {
            $this->ajaxReturn(0, '该会员已经存在');
        } else {
            $this->ajaxReturn();
        }
    }

    /**
     * ajax检测邮箱是否存在
     */
    public function ajax_check_email() {
        $name = $this->_get('email', 'trim');
        $id = $this->_get('id', 'intval');
        if ($this->_mod->email_exists($name,  $id)) {
            $this->ajaxReturn(0, '该邮箱已经存在');
        } else {
            $this->ajaxReturn();
        }
    }
	
	 /**
     * ajax检测手机是否存在
     */
    public function ajax_check_mobile() {
        $mobile = $this->_get('mobile', 'trim');
        $id = $this->_get('id', 'intval');
        if ($this->_mod->mobile_exists($mobile,  $id)) {
            $this->ajaxReturn(0, '该手机号码已经存在');
        } else {
            $this->ajaxReturn();
        }
    }
    
    /**
     * phpEscel导出用户表
     * @author H.J.H
     * date  2014.8.5 11:10
     */
    function pushExcel(){
    	
    	if($_COOKIE['admin']['role_id'] != 1) {
    		$this->error('无权限操作');
    	}
    	$total=$this->_mod->count('id');//总数
    	$res=$this->_mod->field('id,share_id,username,mobile,gender,address,last_time')->select();
    	
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
    	$rowVal = array(0=>'编号',1=>'级', 2=>'用户名', 3=>'手机号', 4=>'性别',5=>'地址',6=>'推荐人数',7=>'登录时间',8=>'会员总数');
    	foreach ($rowVal as $k=>$r){
    		$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)
    		->getFont()->setBold(true);//字体加粗
    		$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
    		getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
    		$objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
    	}
    	$objPhpExcel->getActiveSheet()->setCellValue('A1', '编号');
    	$objPhpExcel->getActiveSheet()->setCellValue('B1', '用户名');
    	$objPhpExcel->getActiveSheet()->setCellValue('C1', '推荐者');
    	$objPhpExcel->getActiveSheet()->setCellValue('D1', '手机号');
    	$objPhpExcel->getActiveSheet()->setCellValue('E1', '性别');
    	$objPhpExcel->getActiveSheet()->setCellValue('F1', '地址');
    	$objPhpExcel->getActiveSheet()->setCellValue('G1', '推荐人数');
    	$objPhpExcel->getActiveSheet()->setCellValue('H1', '最后登录时间');
    	$objPhpExcel->getActiveSheet()->setCellValue('I1', '总会员数');
    	//设置当前的sheet索引 用于后续内容操作
    	$objPhpExcel->setActiveSheetIndex(0);
    	$objActSheet=$objPhpExcel->getActiveSheet();
    	//设置当前活动的sheet的名称
    	$title="公司用户录";
    	$objActSheet->setTitle($title);
    	//设置单元格内容
    	foreach($res as $k => $v)
    	{   
    		$v['count']=$this->_mod->where('share_id='.$v['id'])->count('id');//推荐人数
    		$v['tname']=$this->_mod->where('id='.$v['share_id'])->getfield('username');//推荐人
    		
    		if($v['gender']==1){
    			$v['gender']="男";
    		}elseif ($v['gender']==0){
    			$v['gender']="女";
    		}else {
    			$v['gender']="";
    		}
    		$v['last_time']=$v['last_time']==0 ? '' : date('Y-m-d H:i',$v['last_time']);
    		$num=$k+2;
    		$objPhpExcel->setActiveSheetIndex(0)
    		//Excel的第A列，uid是你查出数组的键值，下面以此类推
    		->setCellValue('A'.$num, $v['id'])
    		->setCellValue('B'.$num, $v['username'])
    		->setCellValue('C'.$num, $v['tname'])
    		->setCellValue('D'.$num, $v['mobile'])
    		->setCellValue('E'.$num, $v['gender'])
    		->setCellValue('F'.$num, $v['address'])
    		->setCellValue('G'.$num, $v['count'])
    		->setCellValue('H'.$num, $v['last_time']);
    		
    	}
    	$objPhpExcel->setActiveSheetIndex(0)->setCellValue('I2', $total);
    	$title="公司用户录";
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
    
    //导入数据 2014.9.19 H.J.H
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
    		
    		for($j=1;$j<=$highestRow;$j++){
    
    			$data['username'] = trim((string)$objPHPExcel->getActiveSheet()->getCell("A".$j)->getValue());
    			$mobile   = (string)$objPHPExcel->getActiveSheet()->getCell("B".$j)->getValue();
    		/*	$data['stores']  = (string)$objPHPExcel->getActiveSheet()->getCell("C".$j)->getValue();
    			$quyu = (string)$objPHPExcel->getActiveSheet()->getCell("D".$j)->getValue();
    			$bankuai  = (string)$objPHPExcel->getActiveSheet()->getCell("E".$j)->getValue();
    			
    			$data['address']= $quyu.'-'.str_replace(' ', '-', $bankuai); 
    			
    			
    			$data['share_id']= (string)1332;
    			$data['password']=(string)'96e79218965eb72c92a549dd5a330112';
    			$data['gender']=(string)2;
    			
    			
    			$begin = (string)strtotime('2014-12-1 00:00:00');
    			$end  =  (string)strtotime('2014-12-31 00:00:00');
    			$timestamp = (string)rand($begin, $end);
    			
    			
    			$data['last_time'] = (string)$timestamp;
    			$data['reg_time'] = (string)$timestamp;
    			$data['status']=(string)1;
    			 */
    			$last_id = $this->_mod->where("mobile = '".$mobile."'")->save($data);//生成id  
    		
    			if($last_id){
    				echo "第".$j."行导入成功,fph_user表第：".$last_id."条 <br/>";
    			}else {
    				echo "第".$j."行导入失败<br/>";
    			}
    		} 
    	}
    }
    
    
    /**
     * 功能：待、已审核绑定门店
     * author H.J.H
     * date 2015/02/28
     * linkme QQ1481031592
     */
    public function check_store() {
    	$fph = C('DB_PREFIX');
    	//搜索条件
    	$username     = $this->_get('keyword', 'trim');
    	$mobile     = $this->_get('mobile', 'trim');
    	$time_start = $this->_request('time_start', 'trim');
    	$time_end   = $this->_request('time_end', 'trim');
    	 
    	$store_status     = $this->_get('store_status', 'trim');
    	 
    	$this->assign('search', array(
    			'keyword' => $username,
    			'mobile' => $mobile,
    			'time_start' => $time_start,
    			'time_end'=> $time_end
    	));
    	$where = 'stores_id !=0 AND store_status='.$store_status;
    	if($username){
    		$where .= ' AND username like "%'.$username.'%"';
    	}
    	if($mobile){
    		$where .= ' AND mobile = "'.$mobile.'"';
    	}
    	if($time_start AND $time_end)
    	{
    		$where .=' AND reg_time  between '.strtotime($time_start).' AND '.strtotime($time_end);
    	}
    
    	//查询用户总数据
    	$count = $this->_mod->where($where)->count('id');
    	 
    	//分页
    	$p = new Page($count, 25);
    	$list = M('user')->field('A.id,A.username,A.mobile,A.store_status,B.name,B.contact,B.address,B.code_id,B.uid')
    	->table("{$fph}user AS A LEFT JOIN {$fph}stores AS B ON A.stores_id = B.id")
    	->where($where)
    	->limit($p->firstRow.','.$p->listRows)
    	->order('reg_time DESC')
    	->select();
    	foreach ($list as $key => $value) {
    		$list[$key]['store_username'] = M('user')->where(array('id'=>$value['uid']))->getField('username');
    		$list[$key]['store_mobile'] = M('user')->where(array('id'=>$value['uid']))->getField('mobile');
    	}
    	$page = $p->show();
    	$this->assign('page', $page);
    	$this->assign('list', $list);
    	$this->assign('store_status', $store_status);
    	 
    	//菜单
    	$big_menu = array(
    			'title' => L('添加会员'),
    			'iframe' => U('user/add'),
    			'id' => 'add',
    			'width' => '620',
    			'height' => ''
    	);
    	$this->assign('big_menu', $big_menu);
    	 
    	$this->display();
    }
    
    /**
     * 功能：审核 —— 通过与退回
     * author H.J.H
     * date 2015/02/28
     * linkme QQ1481031592
     */
    public function checked_store() {
    	 
    	$id = $this->_get('id', 'intval');
    	$store_status = $this->_get('store_status', 'intval');
    	//通过
    	if($store_status==0 && $id){
    		$this->_mod->where('id = '.$id)->save(array('store_status'=>1));
    		$this->ajaxReturn();
    	}
    	if($store_status==1 && $id){
    		$this->_mod->where('id = '.$id)->save(array('store_status'=>0,'internal'=>0));
    		$this->ajaxReturn();
    	}
    }



    
    
    
    
    

}