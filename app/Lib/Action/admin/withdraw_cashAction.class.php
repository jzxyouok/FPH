<?php
class withdraw_cashAction extends backendAction
{
    public function index() {
        $username       = $this->_get('username','trim');
        $mobile         = $this->_get('mobile','trim');
        $account        = $this->_get('account','trim');
        $name           = $this->_get('name','trim');
        $account_type   = $this->_get('account_type','trim');
        $status         = $this->_get('status','trim');
        $add_time_start = $this->_get('add_time_start','trim');
        $add_time_end   = $this->_get('add_time_end','trim');
        $pay_time_start = $this->_get('pay_time_start','trim');
        $pay_time_end   = $this->_get('pay_time_end','trim');
        $list = D('commission')->withdraw_cash_index($username, $mobile, $status, $account, $name, $account_type, $add_time_start, $add_time_end, $pay_time_start, $pay_time_end);
        $this->assign('list',$list[0]);
        $this->assign('page',$list[1]);
        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);
        $this->assign('search', array(
            'mobile' => $mobile,
            'username' => $username,
            'account' => $account,
            'name' => $name,
            'account_type' => $account_type,
            'status' => $status,
            'add_time_start' => $add_time_start,
            'add_time_end' => $add_time_end,
            'pay_time_start' => $pay_time_start,
            'pay_time_end' => $pay_time_end,
        ));
        $this->assign('list_table',true);
        $this->display();
    }
    
    /**
     * 检查是否可以打款
     */
	public  function payTest()
    {
		$id          = $this->_post('id','intval');
		$pid         = $this->_post('pid','intval');
		$bill_number = $this->_post('bill_number','trim');

		if( $pid != 2 )						return $this->ajaxReturn(0, '系统参数出错,提交失败');
		if(strlen( $bill_number ) == 0)		return $this->ajaxReturn(0, '请填写票据单号');

		$D_commission			= D('commission');
		M(NULL,NULL,C('DB_member'));

		$withdraw_cash_info	= M('withdraw_cash')->field('money,uid,status,city_id,wallet_type')->where(array('id'=>$id))->find();
		$data				= $D_commission->checkCanPay( $withdraw_cash_info );

		if($data === TRUE )	return $this->ajaxReturn('success','成功');
		else				return $this->ajaxReturn($data[0], $data[1]);
    }

    //打款
    public function edit(){
        $id = $this->_request('id','intval');
        if(IS_POST){
            $id          = $this->_post('id','intval');
            $pid         = $this->_post('pid','intval');
            $bill_number = $this->_post('bill_number','trim');

            if($pid!=2 && $pid!=3 && $pid!=4)	$this->ajaxReturn(0, '系统参数出错,提交失败');
            if(!$bill_number && $pid == 2)		$this->ajaxReturn(0, '请填写票据单号');

            $data = D('commission')->withdraw_cash_update($id, $pid, $bill_number);
            $this->ajaxReturn($data[0], $data[1]);
        }
        $info = D('commission')->withdraw_cash_edit($id);
        //print_r($info);
        $this->assign('info',$info);
        $this->display();
    }

    /**
     * 审核领路费记录
     */
    public function check()
    {
    	M(NULL,NULL,C('DB_member'));

    	/*
    	 * 审核
    	 */
    	if(IS_POST){
    		$ids			= $this->_post('id','array');
    		$check_status	= $this->_post('check_status','intval');

    		if(empty( $ids ))								return $this->error('请选择审核项');
    		if( $check_status != 1 && $check_status != 2 )	return $this->error('非法操作1');

    		// 本次审核金额
    		$member_uid					= 0;
    		$sum_journal_account		= 0;
    		$sum_journal_account_citys	= array();
    		if( $check_status == 1 )
    		{
    			$journal_accounts			= M('journal_account','fph_',C('DB_log'))->field('uid,journal_account,city_id')->where("table_type='1' AND sid IN('".implode("','", $ids )."')")->select();
    			foreach( $journal_accounts AS $journal_account)
    			{
    				$member_uid	= $journal_account['uid'];
    				if($journal_account['city_id'] == 0)
    				{
    					$sum_journal_account		= $sum_journal_account + $journal_account['journal_account'];
    				}
    				else
    				{
    					$city_id								= $journal_account['city_id'];
    					if(!isset( $sum_journal_account_citys[$city_id] ))	$sum_journal_account_citys[$city_id]	= 0;
    					$sum_journal_account_citys[$city_id]	= $sum_journal_account_citys[$city_id] + $journal_account['journal_account'];
    				}
    			}
    		}

    		//更新
    		try
    		{
    			M('receive','fph_',C('DB_member'))->startTrans();

	    		$where			= "id IN('".implode("','", $ids)."') AND check_status='0'";
	    		$save			= array('check_status'=>$check_status,'check_time'=>time());
	    		$affected_rows	= M('receive')->where($where)->save($save);
				if( $affected_rows != count( $ids ))	throw new Exception('可能有其他人正在处理审核动作.');

				if( $sum_journal_account > 0 )
				{
					$where			= "uid='{$member_uid}' AND freeze >= '{$sum_journal_account}'";
					$affected_rows	= M('member_wallet')->where($where)->setDec('freeze',$sum_journal_account);
					if( $affected_rows != 1 )			throw new Exception('审核过程中,解除冻结资金失败.[1]');
				}

				foreach( $sum_journal_account_citys AS $city_id	=> $sum_journal_account_city )
				{
					$where			= "uid='{$member_uid}' AND freeze >= '{$sum_journal_account_city}' AND city_id='{$city_id}'";
					$affected_rows	= M('member_wallet_city')->where($where)->setDec('freeze',$sum_journal_account_city);
					if( $affected_rows != 1 )			throw new Exception('审核过程中,解除冻结资金失败.[2]');
				}

				M('receive','fph_',C('DB_member'))->commit();

				return $this->success('成功.');
    		}
    		catch( Exception $e )
    		{
    			M('receive','fph_',C('DB_member'))->rollback();
    			return $this->error($e->getMessage());
    		}

    		return $this->error('操作失败');
    	}

    	/*
    	 * 列表
    	 */

    	$withdraw_cash_id	= $this->_get('withdraw_cash_id','intval');
    	$uid				= $this->_get('uid','intval');

    	M(NULL,NULL,C('DB_member'));

    	$where	= "uid = '{$uid}' AND status='1' AND check_status='0'";	//已领取待审核状态
    	$count	= M('receive')->where( $where )->count('id');

    	import("ORG.Util.Page");
    	$p		= new Page($count, $page);
    	$page	= $p->show();

    	if( $count > 0 )
    	{
    		//领路费记录
			$list				= M('receive')->where( $where )->order('receive_time DESC')->limit($p->firstRow.','.$p->listRows)->select();

    		//取手机号
			$mobile				= M('member')->where(array('id'=>$uid))->getfield('mobile');

			//取姓名
			$username			= M('member_extend')->where(array('uid'=>$uid))->getfield('username');

			//取签到令路费图片信息
			$receive_signs					= array();
			$receive_ids					= array();
			foreach( $list AS $item )
			{
				$receive_id					= $item['id'];
				if($item['type'] == '3')	$receive_ids[$receive_id]	= $receive_id;
			}
			$tmp_receive_signs			= M('receive_sign')->field('receive_id,photo,photo_time,latitude')->where("receive_id IN('".implode("','",$receive_ids)."')")->select();
			foreach( $tmp_receive_signs AS $tmp_receive_sign )
			{
				$receive_id					= $tmp_receive_sign['receive_id'];
				$receive_signs[$receive_id]	= $tmp_receive_sign;
			}

			//取楼盘名
			$propertys		= array();
			$property_pids	= array();
			foreach( $list AS $item )
			{
				$property_pid					= $item['pid'];
				$property_pids[$property_pid]	= $property_pid;
			}
			$tmp_propertys	= M('property','fph_',C('DB_fangpinhui'))->field('id,title,latitude')->where("id IN('".implode("','", $property_pids)."')")->select();
			foreach( $tmp_propertys AS $tmp_property )
			{
				$property_pid				= $tmp_property['id'];
				$propertys[$property_pid]	= $tmp_property;
			}
			
			//领取金额
			$journal_accounts				= array();
			$receive_ids					= array();
			foreach( $list AS $item )
			{
				$receive_id					= $item['id'];
				$receive_ids[$receive_id]	= $receive_id;
			}
			$tmp_journal_accounts	= M('journal_account','fph_',C('DB_log'))->field('sid,journal_account')->where("table_type='1' AND sid IN('".implode("','", $receive_ids)."')")->select();
			foreach( $tmp_journal_accounts AS $tmp_journal_account )
			{
				$receive_id						= $tmp_journal_account['sid'];
				$journal_accounts[$receive_id]	= $tmp_journal_account;
			}
			
			//获取广告机编码
			$expensess				= array();
			$rule_ids				= array();
			foreach( $list AS $item )
			{
				$rule_id			= $item['rule_id'];
				$rule_ids[$rule_id]	= $rule_id;
			}
			$tmp_expensess	= M('expenses','fph_',C('DB_property'))->field('id,machine_code')->where("id IN('".implode("','", $rule_ids)."')")->select();
			foreach( $tmp_expensess AS $tmp_expenses )
			{
				$rule_id				= $tmp_expenses['id'];
				$expensess[$rule_id]	= $tmp_expenses;
			}
				

			foreach( $list AS $key => $item )
			{
				$score						= 0;
				$receive_id					= $item['id'];
				$property_pid				= $item['pid'];
				$rule_id					= $item['rule_id'];

				//楼盘位置信息加分
				$parse_property_latitude	= isset( $propertys[$property_pid]['latitude'] ) ? explode(',', $propertys[$property_pid]['latitude'] ) : array(0,0);
				$parse_receive_latitude		= isset( $receive_signs[$receive_id] ) ? explode(',', $receive_signs[$receive_id]['latitude'] ) : array(0,0);
				$property_lng				= (float)(string) (isset( $parse_property_latitude[0] ) ? $parse_property_latitude[0] : 0);
				$property_lat				= (float)(string) (isset( $parse_property_latitude[1] ) ? $parse_property_latitude[1] : 0);
				$receive_lng				= (float)(string) (isset( $parse_receive_latitude[0] ) ? $parse_receive_latitude[0] : 0);
				$receive_lat				= (float)(string) (isset( $parse_receive_latitude[1] ) ? $parse_receive_latitude[1] : 0);
				
				//楼盘经纬度转换(楼盘百度转高德)
				list( $property_lng, $property_lat )	= bd_decrypt( $property_lng, $property_lat );
				
				$distance					= getDistance(  $receive_lat, $receive_lng, $property_lat, $property_lng );
				
				if( $distance > 200 )	$score = $score + 2.5;
				else					$score = $score + 5;


				//拍照时间信息加分
				$receive_time				= $item['receive_time'];
				$photo_time					= isset( $receive_signs[$receive_id]['photo_time'] ) ? $receive_signs[$receive_id]['photo_time'] : 0;
				if( $receive_time - $photo_time > 1800 || $receive_time == 0)	$score = $score + 2.5;
				else															$score = $score + 5;

				$list[$key]['mobile']			= $mobile;
				$list[$key]['username']			= $username;
				$list[$key]['title']			= isset( $propertys[$property_pid]['title'] ) ? $propertys[$property_pid]['title'] : '数据异常找不到相应楼盘';
				$list[$key]['photo']			= isset( $receive_signs[$receive_id]['photo'] ) ? $receive_signs[$receive_id]['photo'] : '-';
				$list[$key]['photo_time']		= isset( $receive_signs[$receive_id]['photo_time'] ) ? $receive_signs[$receive_id]['photo_time'] : '';
				$list[$key]['score']			= $score;
				$list[$key]['journal_account']	= isset( $journal_accounts[$receive_id]['journal_account'] ) ? $journal_accounts[$receive_id]['journal_account'] : '数据异常找不到相应领取金额';
				$list[$key]['machine_code']		= isset( $expensess[$rule_id]['machine_code'] ) ? $expensess[$rule_id]['machine_code'] : '数据异常找不到相应领取规则';
			}
    	}
    	
    	/*
    	 * View
    	 */
    	$p = $this->_get('p','intval',1);
    	$this->assign('p',$p);
    	$this->assign('withdraw_cash_id',$withdraw_cash_id);
    	$this->assign('page',$page);
    	$this->assign('list',$list);
    	$this->display();
    }

    //导出数据
    public function export(){
        M(NULL,NULL,C('DB_member'));
        ini_set('memory_limit', '1024M');
        $username       = $this->_get('username','trim');
        $mobile         = $this->_get('mobile','trim');
        $account        = $this->_get('account','trim');
        $name           = $this->_get('name','trim');
        $account_type   = $this->_get('account_type','trim');
        $status         = $this->_get('status','trim');
        $add_time_start = $this->_get('add_time_start','trim');
        $add_time_end   = $this->_get('add_time_end','trim');
        $pay_time_start = $this->_get('pay_time_start','trim');
        $pay_time_end   = $this->_get('pay_time_end','trim');

        $where = '1=1';
        if($status!=''){
            $where .= ' AND status ='.$status;
        }
        if($mobile){
            $user_list= M('member')->field('id')->where("mobile = '".$mobile."'")->select();
        }elseif($username){
            $user_list = M('member_extend')->field('uid as id')->where("username like '%".$username."%'")->select();
        }
        foreach($user_list as $val){
            $user_ids .=$val['id'].',';
        }
        $user_ids =  substr($user_ids, 0, -1);
        if($mobile || $username){
            if(!$user_ids) $user_ids=0;
            $where .= ' AND uid in('.$user_ids.')';
        }
        if($account){
            $where .= " AND account = '".$account."'";
        }
        if($name){
            $where .= " AND name = '".$name."'";
        }
        if($account_type!=''){
            $where .= ' AND account_type ='.$account_type;
        }
        if($add_time_start){
            $where .= ' AND add_time > '.strtotime($add_time_start);
        }
        if($add_time_end){
            $add_time_end = strtotime($add_time_end) + 68400;
            $where .= ' AND add_time < '.$add_time_end;
        }
        if($pay_time_start){
            $where .= ' AND pay_time > '.strtotime($pay_time_start);
        }
        if($pay_time_end){
            $pay_time_end = strtotime($pay_time_end) + 68400;
            $where .= ' AND pay_time < '.$pay_time_end;
        }
        $str = 'id,uid,money,account_type,add_time,pay_time,status,bank,account_bank,name,account,bill_number';
        $list = M('withdraw_cash')->field($str)->where($where)->order('id DESC')->select();

        $total=count($list);//总数

        Vendor("Classes.PHPExcel");
        Vendor("Classes.PHPExcel.php");

        //创建处理对象实例
        $objPhpExcel=new PHPExcel();
        $objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
        //设置表格的宽度  手动
        $objPhpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPhpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPhpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
        $objPhpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPhpExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $objPhpExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
        $objPhpExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
        $objPhpExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
        $objPhpExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
        $objPhpExcel->getActiveSheet()->getColumnDimension('M')->setWidth(40);


        //设置标题
        $rowVal = array(0=>'编号',1=>'ID', 2=>'客户姓名', 3=>'手机号码', 4=>'提现金额', 5=>'帐号类型', 6=>'收款银行', 7=>'开户行', 8=>'帐号', 9=>'真实姓名', 10=>'申请时间', 11=>'打款时间', 12=>'票据单号', 13=>'状态');
        foreach ($rowVal as $k=>$r){
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)
                ->getFont()->setBold(true);//字体加粗
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
            getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
            $objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
        }

        $objPhpExcel->getActiveSheet()->setCellValue('A1', '编号');
        $objPhpExcel->getActiveSheet()->setCellValue('B1', 'ID');
        $objPhpExcel->getActiveSheet()->setCellValue('C1', '客户姓名');
        $objPhpExcel->getActiveSheet()->setCellValue('D1', '手机号码');
        $objPhpExcel->getActiveSheet()->setCellValue('E1', '提现金额');
        $objPhpExcel->getActiveSheet()->setCellValue('F1', '帐号类型');
        $objPhpExcel->getActiveSheet()->setCellValue('G1', '收款银行');
        $objPhpExcel->getActiveSheet()->setCellValue('H1', '开户行');
        $objPhpExcel->getActiveSheet()->setCellValue('I1', '帐号');
        $objPhpExcel->getActiveSheet()->setCellValue('J1', '真实姓名');
        $objPhpExcel->getActiveSheet()->setCellValue('K1', '申请时间');
        $objPhpExcel->getActiveSheet()->setCellValue('L1', '打款时间');
        $objPhpExcel->getActiveSheet()->setCellValue('M1', '票据单号');
        $objPhpExcel->getActiveSheet()->setCellValue('N1', '状态');

        //设置当前的sheet索引 用于后续内容操作
        $objPhpExcel->setActiveSheetIndex(0);
        $objActSheet=$objPhpExcel->getActiveSheet();
        //设置当前活动的sheet的名称
        $title="路费提现";
        $objActSheet->setTitle($title);
        //设置单元格内容
        $j=1;

        foreach($list as $k => $v)
        {
            $user_info = M('member')->field('mobile')->where(array('id'=>$v['uid']))->find();
            $user_extend = M('member_extend')->field('username')->where(array('uid'=>$v['uid']))->find();
            if($v['account_type']==1){
                $v['account_type']='支付宝';
            }else{
                $v['account_type']='银行卡';
            }
            switch ($v['status']) {
                case 1:
                    $v['status'] = '已申请';
                    break;
                case 2:
                    $v['status'] = '已打款';
                    break;
                case 3:
                    $v['status'] = '打款失败';
                    break;
                case 4:
                    $v['status'] = '延迟打款';
                    break;
            }
            $v['pay_time'] = $v['pay_time'] ? date('Y-m-d H:i', $v['pay_time']) : '';
            $num=$k+2;
            $objPhpExcel->setActiveSheetIndex(0)
                //Excel的第A列，uid是你查出数组的键值，下面以此类推
                ->setCellValue('A'.$num, $j)
                ->setCellValue('B'.$num, $v['id'])
                ->setCellValue('C'.$num, $user_extend['username'])
                ->setCellValue('D'.$num, $user_info['mobile'])
                ->setCellValue('E'.$num, $v['money'])
                ->setCellValue('F'.$num, $v['account_type'])
                ->setCellValue('G'.$num, $v['bank'])
                ->setCellValue('H'.$num, $v['account_bank'])
                ->setCellValue('I'.$num, ' '.$v['account'])
                ->setCellValue('J'.$num, $v['name'])
                ->setCellValue('K'.$num, date('Y-m-d H:i',$v['add_time']))
                ->setCellValue('L'.$num, $v['pay_time'])
                ->setCellValue('M'.$num, ' '.$v['bill_number'])
                ->setCellValue('N'.$num, $v['status']);
            $j++;
        }
        //$objPhpExcel->setActiveSheetIndex(0)->setCellValue('I2', $total);//单行信息
        $title="路费提现";
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