<?php
class activityAction extends backendAction
{
    public function _initialize() {
	    parent::_initialize();
		M(NULL, 'fph_', C('DB_activity'));
		$this->RedisDataBaseACTIVITY= C('DB_REDIS_ACTIVITY_KANJIA');

    }

    /**
     * 活动列表
     * mihailong
     */
    public function index() {
		$count = M('activity')->count('id');
		$p = new Page($count, 20);
		$page = $p->show();
		$list = M('activity')->limit($p->firstRow.','.$p->listRows)->select();
		$this->assign('list',$list);
		$this->assign('page',$page);
		$this->assign('p',$p);
		$this->display();
    }


	/**
	 * 新增活动
	 * mihailong
	 */
	public function add(){
		$relation_list = M('activity_relation')->where('aid = 0')->select();
		$this->assign('relation_list', $relation_list);
		if(IS_POST){
			$data['title']       		= $this->_post('title','trim');
			$data['intro']      		= $this->_post('intro','trim');
			$data['author']      		= $this->_post('author','trim');
			$data['type']        		= $this->_post('type','intval');
			$data['time_start']       	= strtotime($this->_post('time_start','trim'));
			$data['time_end']       	= strtotime($this->_post('time_end','trim'));
			$data['limit']       		= $this->_post('limit','intval');
			$data['only']       		= $this->_post('only','intval');
			$data['latitude']       	= $this->_post('latitude','trim');
			$data['telephone']       	= $this->_post('telephone','trim');
			$data['sign']       		= $this->_post('sign','trim');
			$data['web_url']       		= $this->_post('web_url','trim');
			$data['mobile_url']       	= $this->_post('mobile_url','trim');
			$data['rule']       		= $this->_post('rule','trim');
			$data['status']       		= $this->_post('status','intval');
			$data['choose']       		= $this->_post('choose','intval');
			$data['web_img']       		= $this->_post('web_img','trim');
			$data['mobile_img']         = $this->_post('mobile_img','trim');
			$code         				= $this->_post('code','trim');
			$data['add_time']       	= time();
			$channel       = $this->_post('channel');
			$data['web']= in_array(1,$channel) ? 1 : 0;
			$data['wechat']= in_array(2,$channel) ? 1 : 0;
			$data['c_port']= in_array(3,$channel) ? 1 : 0;
			$data['b_port']= in_array(4,$channel) ? 1 : 0;
			if($data['sign'] ==1 ){
				$sign_time_start      			= strtotime($this->_post('sign_time_start','trim'));
				$sign_time_end        			= strtotime($this->_post('sign_time_end','trim'));
				if(($data['time_start'] > $sign_time_start)||($data['time_end'] < $sign_time_end)){
					$this->error('报名时间必须在活动时间内');
				}
			}
			$id = M('activity')->add($data);
			if($id){
				$result = true;
				if($data['sign'] ==1 ){
					$sign['aid'] 					= $id;
					$sign['sign_number']      		= $this->_post('sign_number','intval');
					$sign['sign_time_start']      	= $sign_time_start;
					$sign['sign_time_end']        	= $sign_time_end;
					$sign['sign_web_url']       	= $this->_post('sign_web_url','trim');
					$sign['sign_mobile_url']       	= $this->_post('sign_mobile_url','trim');
					$result = M('activity_extent')->add($sign);
				}
				M('activity_relation')->where("code ='$code'")->save(array('aid'=>$id));
				//修改的数据写入缓存
				$redis = new CacheRedis($this->RedisDataBaseACTIVITY);
				$redisKey = 'activity_'. $code;
				$redis->handler->set($redisKey, $id);
				if($result){
					$this->success('提交成功');exit;
				}else{
					$this->error('提交失败');
				}
			}else{
				$this->error('提交失败');
			}
		}
		$this->assign('type', 'edit');
		$this->display();
	}


	/**
	 * 编辑活动
	 * mihailonng
	 */
	public function edit(){
		$id = $this->_request('id','intval');
		$info = M('activity')->where(array('id'=>$id))->find();
		$sign = M('activity_extent')->where(array('aid'=>$id))->find();
		$this->assign('info',$info);
		$this->assign('sign',$sign);
		$this->assign('id', $id);
		$this->assign('type', 'edit');
		if(IS_POST){
			$data['title']       		= $this->_post('title','trim');
			$data['intro']      		= $this->_post('intro','trim');
			$data['author']      		= $this->_post('author','trim');
			$data['type']        		= $this->_post('type','intval');
			$data['time_start']       	= strtotime($this->_post('time_start','trim'));
			$data['time_end']       	= strtotime($this->_post('time_end','trim'));
			$data['limit']       		= $this->_post('limit','intval');
			$data['only']       		= $this->_post('only','intval');
			$data['latitude']       	= $this->_post('latitude','trim');
			$data['telephone']       	= $this->_post('telephone','trim');
			$data['sign']       		= $this->_post('sign','trim');
			$data['web_url']       		= $this->_post('web_url','trim');
			$data['mobile_url']       	= $this->_post('mobile_url','trim');
			$data['rule']       		= $this->_post('rule','trim');
			$data['status']       		= $this->_post('status','intval');
			$data['choose']       		= $this->_post('choose','intval');
			$data['web_img']       		= $this->_post('web_img','trim');
			$data['mobile_img']         = $this->_post('mobile_img','trim');
			$data['add_time']       	= time();
			$channel       = $this->_post('channel');
			$data['web']= in_array(1,$channel) ? 1 : 0;
			$data['wechat']= in_array(2,$channel) ? 1 : 0;
			$data['c_port']= in_array(3,$channel) ? 1 : 0;
			$data['b_port']= in_array(4,$channel) ? 1 : 0;
			$signActivity = true;
			if($data['sign'] ==1 ){
				$sign_time_start      			= strtotime($this->_post('sign_time_start','trim'));
				$sign_time_end        			= strtotime($this->_post('sign_time_end','trim'));
				if(($data['time_start'] > $sign_time_start)||($data['time_end'] < $sign_time_end)){
					$signActivity = false;
					$this->error('报名时间必须在活动时间内');
				}
				$sign['sign_number']      		= $this->_post('sign_number','intval');
				$sign['sign_time_start']      	= $sign_time_start;
				$sign['sign_time_end']        	= $sign_time_end ;
				$sign['sign_web_url']       	= $this->_post('sign_web_url','trim');
				$sign['sign_mobile_url']       	= $this->_post('sign_mobile_url','trim');
				$signActivity  = M('activity_extent')->where(array('aid'=>$id))->find();
				if($signActivity){
					M('activity_extent')->where(array('aid'=>$id))->save($sign);
				}else{
					$sign['aid'] = $id;
					M('activity_extent')->add($sign);
				}
			}
			if($signActivity){
				$result = M('activity')->where(array('id'=>$id))->save($data);
			}
			if($result){
				$this->success('提交成功');exit;
			}else{
				$this->error('提交失败');
			}
		}
		$this->display();
	}


	/**
	 * 合作伙伴列表
	 * mihailong
	 */
	public function partner_list(){
		$aid = $this->_request('id','intval');
		$count = M('partner')->where(array('aid' =>$aid ))->count();
		$this->assign('type', 'partner_list');
		$p = new Page($count, 20);
		$page = $p->show();
		$list = M('partner')->where(array('aid' =>$aid ))->limit($p->firstRow.','.$p->listRows)->select();
		$this->assign('id',$aid);
		$this->assign('list',$list);
		$this->assign('page',$page);
		$this->assign('p',$p);
		$this->display();
	}

	/**
	 * 合作伙伴新增
	 * mihailong
	 */
	public function partner_add(){
		$aid = $this->_request('id','intval');
		$this->assign('id', $aid);
		$this->assign('type', 'partner_list');
		if(IS_POST){
			$data['aid']       			= $aid;
			$data['name']       		= $this->_post('name','trim');
			$data['intro']      		= $this->_post('intro','trim');
			$data['pid']      		    = $this->_post('pid','intval');
			$data['money']        		= $this->_post('money','trim');
			$data['number']        		= $this->_post('number','intval');
			$data['time_start']       	= strtotime($this->_post('time_start','trim'));
			$data['time_end']       	= strtotime($this->_post('time_end','trim'));
			$data['latitude']       	= $this->_post('latitude','trim');
			$data['icon_web_img']       = $this->_post('icon_web_img','trim');
			$data['icon_mobile_img']    = $this->_post('icon_mobile_img','trim');
			$data['status']       		= $this->_post('status','intval');
			$data['add_time']       	= time();
			$result = M('partner')->add($data);
			if($result){
				$this->success('提交成功');exit;
			}else{
				$this->error('提交失败');
			}
		}
		$this->display();
	}

	/**
	 * 合作伙伴修改
	 * mihailong
	 */
	public function partner_edit(){
		$id = $this->_request('id','intval');
		$p_id = $this->_request('p_id','intval');
		if(!$id || !$p_id){
			$this->error('缺少参数');
		}
		if(IS_POST){
			$data['name']       		= $this->_post('name','trim');
			$data['intro']      		= $this->_post('intro','trim');
			$data['pid']      		    = $this->_post('pid','intval');
			$data['money']        		= $this->_post('money','trim');
			$data['number']        		= $this->_post('number','intval');
			$data['time_start']       	= strtotime($this->_post('time_start','trim'));
			$data['time_end']       	= strtotime($this->_post('time_end','trim'));
			$data['latitude']       	= $this->_post('latitude','trim');
			$data['icon_web_img']       = $this->_post('icon_web_img','trim');
			$data['icon_mobile_img']    = $this->_post('icon_mobile_img','trim');
			$data['status']       		= $this->_post('status','intval');
			$data['add_time']       	= time();
			$result = M('partner')->where(array('id'=>$p_id))->save($data);
			if($result){
				$this->success('提交成功');exit;
			}else{
				$this->error('提交失败');
			}
		}
		$info = M('partner')->where(array('id'=>$p_id))->find();
		$this->assign('id', $id);
		$this->assign('p_id', $p_id);
		$this->assign('info', $info);
		$this->assign('type', 'partner_list');
		M('property', 'fph_', C('DB_fangpinhui'));
		$where = 'id = '.$info['pid'];
		$field = 'title';
		$propertyInfo = D('property')->findPropertyInfo($where, $field);
		$this->assign('propertyInfo', $propertyInfo);
		$this->display();
	}

	/**
	 * 合作伙伴删除
	 * mihailong
	 */
	public function partner_del(){
		$id = $this->_request('id','intval');
		if(!$id){
			$this->error('缺少参数');
		}
		$result = M('partner')->where(array('id'=>$id))->delete();
		if($result){
			$this->success('删除成功');exit;
		}else{
			$this->error('删除失败');
		}
	}


	/**
	 * 报名列表
	 * mihailong
	 */
	public function sign_list(){
		$id = $this->_request('id','intval');
		$mobile = $this->_get('mobile','trim');
		$time_start = $this->_get('time_start','trim');
		$time_end = $this->_get('time_end','trim');
		$where = "1=1";
		if($mobile){
			$uid = M('member','fph_','DB_member')->where(array('mobile'=>$mobile))->getField('id');
			if($uid > 0)$where .= " and uid = $uid";
		}
		if($time_start){
			$where .= " and add_time>=".strtotime($time_start);
		}
		if($time_end){
			$where .= " and add_time<=".(strtotime($time_end)+86400);
		}
		$search = array(
			'mobile' => $mobile,
			'time_start' => $time_start,
			'time_end' => $time_end,
		);
		$this->assign('id', $id);
		$this->assign('type', 'sign_list');
		$this->assign('search', $search);
		M(null,null,'DB_activity');
		$activity = M('activity')->where(array('id'=>$id))->find();
		if($activity['sign'] == 1){
			try{
				$count = M('sign_record_'.$id)->where($where)->count();
				$p = new Page($count, 20);
				$page = $p->show();
				$list = M('sign_record_'.$id)->where($where)->limit($p->firstRow.','.$p->listRows)->select();
				foreach($list as $k => $val ){
					$list[$k]['name'] = M('member_extend','fph_','DB_member')->where(array('uid'=>$val['uid']))->getField('username');
					$list[$k]['mobile'] = M('member','fph_','DB_member')->where(array('id'=>$val['uid']))->getField('mobile');
				}
				$this->assign('page',$page);
				$this->assign('p',$p);
				$this->assign('aid', $activity['id']);
				$this->assign('title', $activity['title']);
				$this->assign('list', $list);
				$this->assign('isExistTable', true);
			}catch (Exception $e){
				$this->assign('isExistTable', false);
			}
		}else{
			$this->assign('isExistTable', true);
		}

		$this->display();
	}

	/**
	 * 验证码列表
	 * mihailong
	 */

	public function verify_code_list(){
		$id = $this->_request('id','intval');
		$aid = $this->_request('aid','intval');
		if(!$id || !$aid){
			$this->error('缺少参数');
		}
		$verify_mobile = $this->_get('verify_mobile','trim');
		$where['pid'] = $id;
		if($verify_mobile){
			$where['verify_mobile'] = $verify_mobile;
		}
		$partner = M('partner')->where(array('id' =>$id ))->find();
		$count = M('verify_code')->where($where)->count();
		$p = new Page($count, 50);
		$page = $p->show();
		$list = M('verify_code')->where($where)->limit($p->firstRow.','.$p->listRows)->select();
		$this->assign('id', $aid);
        $this->assign('pid', $id);
		$this->assign('verify_mobile', $verify_mobile);
		$this->assign('type', 'partner_list');
		$this->assign('name',$partner['name']);
		$this->assign('list',$list);
		$this->assign('page',$page);
		$this->assign('p',$p);
		$this->display();
	}

	/**
	 * 生成验证码
	 * mihailong
	 */

	public function verify_code_add(){
		$id = $this->_request('id','intval');
		if(!$id){
			$this->error('缺少参数');
		}
		$this->assign('id', $id);
		$this->assign('type', 'partner_list');
		if(IS_POST){
			$data = array(
				'pid'			=> $id,
				'number'		=> $this->_post('num','intval'),
				'time_start' 	=> strtotime($this->_post('time_start','trim')),
				'time_end' 		=> strtotime($this->_post('time_end','trim')),
				'status' 		=> 0,
				'add_time' 		=> time(),
			);
			$result = M('tasks')->add($data);
			if($result){
				$this->success('生成验证码准备完成');exit;
			}else{
				$this->error('生成验证码失败');
			}
		}
		$this->display();
	}


	/**
	 * 修改验证码状态
	 * mihailong
	 */

	public function code_edit(){
		$id = $this->_request('id','intval');
		$status = $this->_request('status','intval');
		if(!$id){
			$this->error('缺少参数');
		}
		$where['id'] = $id;
		$result = M('verify_code')->where($where)->save(array('status'=>$status));
		if($result){
			$this->success('修改成功');exit;
		}else{
			$this->error('修改失败');
		}
	}

	/**
	 * 验证码导报表
	 * mihailong
	 */

	public function codeExport(){
		$id = $this->_get('id','intval');
		$verify_mobile = $this->_get('verify_mobile','trim');
		if(!$id){
			$this->error('缺少参数');
		}
		$where['pid'] = $id;
		if($verify_mobile){
			$where['verify_mobile'] = $verify_mobile;
		}
		$partner = M('partner')->where(array('id' =>$id ))->find();
		$list = M('verify_code')->where($where)->select();
		foreach($list as $k => $val){
			$list[$k]['name'] = $partner['name'];
			if($val['status']==0)$list[$k]['status_txt'] = '未验证';
			if($val['status']==1)$list[$k]['status_txt'] = '已验证';
			if($val['status']==2)$list[$k]['status_txt'] = '已发放';
			if($val['status']==3)$list[$k]['status_txt'] = '不可用';
			if(!$val['verify_mobile'])$list[$k]['verify_mobile'] = '';
			if(!$val['verify_time']){
				$list[$k]['verify_time'] = '';
			}else{
				$list[$k]['verify_time'] = date('Y-m-d H:i:s',$val['verify_time']);
			}
		}
		$this->codeIsExcel($list);
	}


	private function codeIsExcel($analysis)
	{
		Vendor("Classes.PHPExcel");
		Vendor("Classes.PHPExcel.php");
		//创建处理对象实例
		$objPhpExcel=new PHPExcel();
		$objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
		//设置表格的宽度  手动
		$objPhpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
		$objPhpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
		//设置标题
		$rowVal = array(0=>'合作伙伴',1=>'验证码', 2=>'生成时间', 3=>'验证手机', 4=>'开始时间', 5=>'结束时间',6=>'验证时间', 7=>'状态');
		foreach ($rowVal as $k=>$r){
			$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)
				->getFont()->setBold(true);//字体加粗
			$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
			getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
			$objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
		}
		$objPhpExcel->getActiveSheet()->setCellValue('A1', '合作伙伴')->getColumnDimension('A')->setWidth(30);
		$objPhpExcel->getActiveSheet()->setCellValue('B1', '验证码')->getColumnDimension('B')->setWidth(12);
		$objPhpExcel->getActiveSheet()->setCellValue('C1', '生成时间')->getColumnDimension('C')->setWidth(12);
		$objPhpExcel->getActiveSheet()->setCellValue('D1', '验证手机')->getColumnDimension('D')->setWidth(12);
		$objPhpExcel->getActiveSheet()->setCellValue('E1', '开始时间')->getColumnDimension('E')->setWidth(12);
		$objPhpExcel->getActiveSheet()->setCellValue('F1', '结束时间')->getColumnDimension('F')->setWidth(20);
		$objPhpExcel->getActiveSheet()->setCellValue('G1', '验证时间')->getColumnDimension('G')->setWidth(20);
		$objPhpExcel->getActiveSheet()->setCellValue('H1', '状态')->getColumnDimension('G')->setWidth(20);
		//设置当前的sheet索引 用于后续内容操作
		$objPhpExcel->setActiveSheetIndex(0);
		$objActSheet=$objPhpExcel->getActiveSheet();
		//设置当前活动的sheet的名称
		$title='验证码报表数据';
		$objActSheet->setTitle($title);
		//insert data start
		//print_r($analysis);exit;
		foreach($analysis as $k=>$v){
			$num=$k+2;
			$objPhpExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$num, $v['name'])
				->setCellValue('B'.$num, $v['verify_code'])
				->setCellValue('C'.$num, date('Y-m-d H:i:s',$v['add_time']))
				->setCellValue('D'.$num, $v['verify_mobile'])
				->setCellValue('E'.$num, date('Y-m-d',$v['time_start']))
				->setCellValue('F'.$num, date('Y-m-d',$v['time_end']))
				->setCellValue('G'.$num, $v['verify_time'])
				->setCellValue('H'.$num, $v['status_txt']);
		}
		// insert data end
		$name = date('Y-m-d');//设置文件名
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

	/**
	 * 报名列表导报表
	 * mihailong
	 */
	public function signExport(){
		$id = $this->_get('id','intval');
		$mobile = $this->_get('mobile','trim');
		$time_start = $this->_get('time_start','trim');
		$time_end = $this->_get('time_end','trim');
		$where = "1=1";
		if($mobile){
			$uid = M('member','fph_','DB_member')->where(array('mobile'=>$mobile))->getField('id');
			if($uid > 0)$where .= " and uid = $uid";
		}
		if($time_start){
			$where .= " and add_time>=".strtotime($time_start);
		}
		if($time_end){
			$where .= " and add_time<=".(strtotime($time_end)+86400);
		}
		M(null,null,'DB_activity');
		$activity = M('activity')->where(array('id'=>$id))->find();
		if($activity['sign'] == 1){
			$list = M('sign_record_'.$id)->where($where)->select();
			foreach($list as $k => $val){
				$list[$k]['name'] = M('member_extend','fph_','DB_member')->where(array('uid'=>$val['uid']))->getField('username');
				$list[$k]['mobile'] = M('member','fph_','DB_member')->where(array('id'=>$val['uid']))->getField('mobile');
				$list[$k]['title'] = $activity['title'];
				if($val['status'] == 0) $list[$k]['status']='预报名';
				if($val['status'] == 1) $list[$k]['status']='已报名';
				if($val['status'] == 2) $list[$k]['status']='报名失败';
			}
		}
		$this->signIsExcel($list);
	}


	private function signIsExcel($analysis)
	{
		Vendor("Classes.PHPExcel");
		Vendor("Classes.PHPExcel.php");
		//创建处理对象实例
		$objPhpExcel = new PHPExcel();
		$objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
		//设置表格的宽度  手动
		$objPhpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
		$objPhpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
		//设置标题
		$rowVal = array(0 => '活动名称', 1 => '姓名', 2 => '电话', 3 => '状态', 4 => '报名时间');
		foreach ($rowVal as $k => $r) {
			$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k, 1)
				->getFont()->setBold(true);//字体加粗
			$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k, 1)->
			getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
			$objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k, 1, $r);
		}
		$objPhpExcel->getActiveSheet()->setCellValue('A1', '活动名称')->getColumnDimension('A')->setWidth(30);
		$objPhpExcel->getActiveSheet()->setCellValue('B1', '姓名')->getColumnDimension('B')->setWidth(12);
		$objPhpExcel->getActiveSheet()->setCellValue('C1', '电话')->getColumnDimension('C')->setWidth(12);
		$objPhpExcel->getActiveSheet()->setCellValue('D1', '状态')->getColumnDimension('F')->setWidth(20);
		$objPhpExcel->getActiveSheet()->setCellValue('E1', '报名时间')->getColumnDimension('G')->setWidth(20);
		//设置当前的sheet索引 用于后续内容操作
		$objPhpExcel->setActiveSheetIndex(0);
		$objActSheet = $objPhpExcel->getActiveSheet();
		//设置当前活动的sheet的名称
		$title = '报名报表数据';
		$objActSheet->setTitle($title);
		//insert data start
		//print_r($analysis);exit;
		foreach ($analysis as $k => $v) {
			$num = $k + 2;
			$objPhpExcel->setActiveSheetIndex(0)
				->setCellValue('A' . $num, $v['title'])
				->setCellValue('B' . $num, $v['name'])
				->setCellValue('C' . $num, $v['mobile'])
				->setCellValue('D' . $num, $v['status'])
				->setCellValue('E' . $num, date('Y-m-d H:i:s', $v['add_time']));
		}
		// insert data end
		$name = date('Y-m-d');//设置文件名
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Transfer-Encoding:utf-8");
		header("Pragma: no-cache");
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $title . '_' . urlencode($name) . '.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPhpExcel, 'Excel5');
		$objWriter->save('php://output');
	}

	//上传图片
	public function ajax_upload_img() {
		$type = $this->_get('type', 'trim', 'img');

		if (!empty($_FILES[$type]['name'])) {
			$fdfs_obj = new FastFile();
			$result = $fdfs_obj->fdfs_upload($type);
			if($result){
				$savename = $result['group_name'].'/'.$result['filename'];
				$this->ajaxReturn(1, L('operation_success'), $savename);
			}else{
				$this->ajaxReturn(0, L('illegal_parameters'));
			}
		} else {
			$this->ajaxReturn(0, L('illegal_parameters'));
		}
	}

	/**
	 * 活动人数
	 * mihailong
	 */
	public function action(){
		$id = $this->_request('id','intval');
		if($id){
			$mobile = $this->_get('mobile','trim');
			$time_start = $this->_get('time_start','trim');
			$time_end = $this->_get('time_end','trim');
			$where = "aid = $id";
			if($mobile){
				$uid = M('member','fph_','DB_member')->where(array('mobile'=>$mobile))->getField('id');
				if($uid > 0)$where .= " and uid = $uid";
			}
			if($time_start){
				$where .= " and add_time>=".strtotime($time_start);
			}
			if($time_end){
				$where .= " and add_time<=".(strtotime($time_end)+86400);
			}
			$search = array(
				'mobile' => $mobile,
				'time_start' => $time_start,
				'time_end' => $time_end,
			);
			$this->assign('id', $id);
			M(null,null,'DB_activity');
			$activity = M('activity')->where(array('id'=>$id))->find();
			$list = M('action')->where($where)->select();
			$count = M('action')->where($where)->count();
			foreach($list as $k => $val ){
				$list[$k]['name'] = M('member_extend','fph_','DB_member')->where(array('uid'=>$val['uid']))->getField('username');
				$list[$k]['mobile'] = M('member','fph_','DB_member')->where(array('id'=>$val['uid']))->getField('mobile');
			}
			$p = new Page($count, 20);
			$page = $p->show();
			$this->assign('page',$page);
			$this->assign('p',$p);
			$this->assign('aid', $activity['id']);
			$this->assign('title', $activity['title']);
			$this->assign('list', $list);
		}
		$this->assign('search', $search);
		$this->assign('type', 'action');
		$this->display();
	}



	/**
	 * 活动人数导报表
	 * mihailong
	 */
	public function actionExport(){
		$id = $this->_get('id','intval');
		$mobile = $this->_get('mobile','trim');
		$time_start = $this->_get('time_start','trim');
		$time_end = $this->_get('time_end','trim');
		$where = "aid = $id";
		if($mobile){
			$uid = M('member','fph_','DB_member')->where(array('mobile'=>$mobile))->getField('id');
			if($uid > 0)$where .= " and uid = $uid";
		}
		if($time_start){
			$where .= " and add_time>=".strtotime($time_start);
		}
		if($time_end){
			$where .= " and add_time<=".(strtotime($time_end)+86400);
		}
		M(null,null,'DB_activity');
		$activity = M('activity')->where(array('id'=>$id))->find();
		$list = M('action')->where($where)->select();
		foreach($list as $k => $val ){
			$list[$k]['title'] 	= $activity['title'];
			$list[$k]['name'] 	= M('member_extend','fph_','DB_member')->where(array('uid'=>$val['uid']))->getField('username');
			$list[$k]['mobile'] = M('member','fph_','DB_member')->where(array('id'=>$val['uid']))->getField('mobile');
		}
		$this->actionIsExcel($list);
	}


	private function actionIsExcel($analysis)
	{
		Vendor("Classes.PHPExcel");
		Vendor("Classes.PHPExcel.php");
		//创建处理对象实例
		$objPhpExcel = new PHPExcel();
		$objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
		//设置表格的宽度  手动
		$objPhpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
		$objPhpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
		//设置标题
		$rowVal = array(0 => '活动ID',1 => '活动名称', 2 => '姓名', 3 => '电话', 4 => '提交时间');
		foreach ($rowVal as $k => $r) {
			$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k, 1)
				->getFont()->setBold(true);//字体加粗
			$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k, 1)->
			getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
			$objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k, 1, $r);
		}
		$objPhpExcel->getActiveSheet()->setCellValue('A1', '活动ID')->getColumnDimension('A')->setWidth(10);
		$objPhpExcel->getActiveSheet()->setCellValue('B1', '活动名称')->getColumnDimension('B')->setWidth(30);
		$objPhpExcel->getActiveSheet()->setCellValue('C1', '姓名')->getColumnDimension('C')->setWidth(12);
		$objPhpExcel->getActiveSheet()->setCellValue('D1', '电话')->getColumnDimension('D')->setWidth(20);
		$objPhpExcel->getActiveSheet()->setCellValue('E1', '提交时间')->getColumnDimension('E')->setWidth(30);
		//设置当前的sheet索引 用于后续内容操作
		$objPhpExcel->setActiveSheetIndex(0);
		$objActSheet = $objPhpExcel->getActiveSheet();
		//设置当前活动的sheet的名称
		$title = '活动人数报表数据';
		$objActSheet->setTitle($title);
		//insert data start
		//print_r($analysis);exit;
		foreach ($analysis as $k => $v) {
			$num = $k + 2;
			$objPhpExcel->setActiveSheetIndex(0)
				->setCellValue('A' . $num, $v['aid'])
				->setCellValue('B' . $num, $v['title'])
				->setCellValue('C' . $num, $v['name'])
				->setCellValue('D' . $num, $v['mobile'])
				->setCellValue('E' . $num, date('Y-m-d H:i:s', $v['add_time']));
		}
		// insert data end
		$name = date('Y-m-d');//设置文件名
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Transfer-Encoding:utf-8");
		header("Pragma: no-cache");
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $title . '_' . urlencode($name) . '.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPhpExcel, 'Excel5');
		$objWriter->save('php://output');
	}


	/**
	 * 用户信息配置表
	 * mihailong
	 */
	public function relation_list(){
		$list = M('activity_relation')->select();
		$this->assign('list', $list);
		$this->display();
	}

	/**
	 * 用户信息配置表
	 * mihailong
	 */
	public function relation_add(){
		if(IS_POST){
			$code = $this->_post('code','trim');
			if(!$code)$this->error('请输入code');
			$result = M('activity_relation')->add(array('code'=>$code));
			if($result){
				$this->success('添加成功');exit;
			}else{
				$this->error('添加失败');
			}
		}
		$this->display();
	}
}