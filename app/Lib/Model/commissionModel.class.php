<?php
class commissionModel extends RelationModel
{
    //路费提现
    public function withdraw_cash_index($username, $mobile, $status, $account, $name, $account_type, $add_time_start, $add_time_end, $pay_time_start, $pay_time_end){

        M(NULL,NULL,C('DB_member'));
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
        $count = M('withdraw_cash')->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();

        $str = 'id,uid,money,account_type,account,name,add_time,pay_time,status,city_id';
        $list = M('withdraw_cash')->field($str)->where($where)->order('id DESC')->limit($p->firstRow.','.$p->listRows)->select();
        foreach($list as $key=>$val){
            $user_info = M('member')->field('mobile,status')->where(array('id'=>$val['uid']))->find();
            $user_extend = M('member_extend')->field('username,disable_reasons')->where(array('uid'=>$val['uid']))->find();
            $list[$key]['mobile']          = $user_info['mobile'];
            $list[$key]['member_status']   = $user_info['status'];
            $list[$key]['username']        = $user_extend['username'];
            $list[$key]['disable_reasons'] = $user_extend['disable_reasons'];

            $list[$key]['isNormal'] = (D('receive')->checkRecord($val['uid'])) ? '<b style="color: red">异常</b>' : '正常';

            if($val['city_id']){
                //城市
                M('city','fph_',C('DB_fangpinhui'));
                $fieldCity = 'name,pid';
                $whereCity = 'id = '.$val['city_id'];
                $cityInfo  = D('city')->getCity($fieldCity, $whereCity);
                $list[$key]['city_name'] = $cityInfo['name'];
                //省
                if($cityInfo['pid']){
                    $fieldProvince = 'name';
                    $whereProvince = 'id = '.$cityInfo['pid'];
                    $provinceInfo  = D('city')->getCity($fieldProvince, $whereProvince);
                    $list[$key]['province_name'] = $provinceInfo['name'];
                }
            }
        }

        return array($list,$page);
    }

    //打款
    public function withdraw_cash_edit($id){
        M(NULL,NULL,C('DB_member'));
        $str = 'id,uid,money,account_type,account,name,bank,account_bank,add_time,bill_number,status';
        $data = M('withdraw_cash')->field($str)->where(array('id'=>$id))->find();
        $user_info = M('member')->field('mobile,status')->where(array('id'=>$data['uid']))->find();
        $user_extend = M('member_extend')->field('username,disable_reasons')->where(array('uid'=>$data['uid']))->find();
        $data['mobile']          = $user_info['mobile'];
        $data['member_status']   = $user_info['status'];
        $data['username']        = $user_extend['username'];
        $data['disable_reasons'] = $user_extend['disable_reasons'];
        return $data;
    }

    /*
     * 判断是否可以打款
     */
    public function checkCanPay( $withdraw_cash_info )
    {
    	if(empty( $withdraw_cash_info ))
    	{
    		$msg = array(0,'提现申请不存在');
    		return $msg;
    	}
    	if($withdraw_cash_info['status'] == '2')
    	{
    		$msg = array(1,'已经打过款,不要再次提交');
    		return $msg;
    	}

    	if($withdraw_cash_info['status'] == '3')
    	{
    		$msg = array(0,'已经提交帐号有误,不要再次提交');
    		return $msg;
    	}

    	M(NULL,NULL,C('DB_member'));
    	$available_money		= 0;	//可用余额 (钱包显示金额＋提现处理中的金额 - 冻结金额)
    	$member_wallet			= $withdraw_cash_info['wallet_type'] == '0'		// money 钱包显示金额,freeze 冻结金额
    							? M('member_wallet')->field('money,freeze')->where("uid = '{$withdraw_cash_info['uid']}'")->find()
    							: M('member_wallet_city')->field('money,freeze')->where("uid = '{$withdraw_cash_info['uid']}' AND city_id='{$withdraw_cash_info['city_id']}'")->find();
		$withdraw_cash_money	= $withdraw_cash_info['wallet_type'] == '0'		// 正在提现的金额(提现申请中+延迟打款)
    							? M('withdraw_cash')->where("uid = '{$withdraw_cash_info['uid']}' AND status IN('1','4') AND city_id='0'")->sum('money')
    							: M('withdraw_cash')->where("uid = '{$withdraw_cash_info['uid']}' AND status IN('1','4') AND city_id='{$withdraw_cash_info['city_id']}'")->sum('money');
    	$available_money		= $member_wallet['money'] + $withdraw_cash_money - $member_wallet['freeze'];
    	if( $withdraw_cash_info['money'] > $available_money )
    	{
    		$has_checking	= FALSE;
    		$has_checking	= M('receive')->field('id')->where("uid = '{$withdraw_cash_info['uid']}' AND status='1' AND check_status='0'")->find();
    		$msg 			= empty( $has_checking ) ? array(2001,'用户钱包余额不足,确认后打款失败!') : array(2002,'请审核所有的现金记录后再打款!');
    		return $msg;
    	}

    	if( $withdraw_cash_info['money'] <= $available_money )	return TRUE;
    }

    //打款
    public function withdraw_cash_update($id, $pid, $bill_number){
        M(NULL,NULL,C('DB_member'));
        $withdraw_cash_info = M('withdraw_cash')->field('money,uid,status,city_id,wallet_type')->where(array('id'=>$id))->find();

        $data['status']          = $pid;
        if($pid==2){
            $data['pay_time']    = time();
            $data['bill_number'] = $bill_number;
        }

        //状态改为打款成功，需要判断路费审核情况，是否满足本次打款
        if( $pid == 2 )
        {
        	$check_can_pay		= $this->checkCanPay( $withdraw_cash_info );
        	if($check_can_pay !== TRUE)
        	{
        		$msg	= $check_can_pay;
        		return $msg;
        		exit;
        	}
        }

        //状态改为打款失败, 需要判断路费审核情况，如果存在打款审核未通过，并且没有其他打款申请的话，将打款审核未通过修改未审核未通过.用户钱包扣除这部分金额
        $update_receive_check_status3_ids	= array();	// 需要将审核状态改为审核不通过的id
        $check_failed_money					= 0;
        if( $pid == 3 || $pid == 2 )
        {
			$receives				= M('receive')->field('id')->where("uid = '{$withdraw_cash_info['uid']}' AND status='1' AND check_status='2'")->select();	//打款审核不通过的条目
			$withdraw_cash_count	= 1;
			if(!empty( $receives ))
			{
				$withdraw_cash_count	= $withdraw_cash_info['wallet_type'] == '0'
										? M('withdraw_cash')->field('id')->where("uid='{$withdraw_cash_info['uid']}' AND status IN('1','4') AND city_id='0'")->count()
										: M('withdraw_cash')->field('id')->where("uid='{$withdraw_cash_info['uid']}' AND status IN('1','4') AND city_id='{$withdraw_cash_info['city_id']}'")->count();
			}
			if( $withdraw_cash_count == 1 )
			{
				$receive_ids		= array();
				foreach( $receives AS $receive )
				{
					$receive_ids[]	= $receive['id'];
				}

				$journal_accounts	= $withdraw_cash_info['wallet_type'] == '0'
									? M('journal_account','fph_',C('DB_log'))->field('sid,journal_account')->where("table_type='1' AND sid IN('".implode("','", $receive_ids )."') AND city_id='0'")->select()
									: M('journal_account','fph_',C('DB_log'))->field('sid,journal_account')->where("table_type='1' AND sid IN('".implode("','", $receive_ids )."') AND city_id='{$withdraw_cash_info['city_id']}'")->select();

				$update_receive_check_status3_ids	= array();
				foreach( $journal_accounts AS $journal_account )
				{
					$update_receive_check_status3_ids[]	= $journal_account['sid'];
					$check_failed_money					= $check_failed_money + $journal_account['journal_account'];
				}
			}
        }

        try
        {
        	M('withdraw_cash','fph_',C('DB_member'))->startTrans();

        	$affected_rows	= M('withdraw_cash')->where("id='{$id}' AND status IN('1','4')")->save($data);
        	if( $affected_rows != 1 )	throw new Exception('提现状态修改失败.');

        	if( $pid == 3 )
        	{
        		$withdraw_cash_info['money']	= $withdraw_cash_info['money'] - $check_failed_money;
        		if( $withdraw_cash_info['money'] != 0 )
        		{
	        		$affected_rows					= $withdraw_cash_info['wallet_type'] == '0'
	        										? M('member_wallet')->where('uid='.$withdraw_cash_info['uid'])->setInc('money',$withdraw_cash_info['money'])
	        										: M('member_wallet_city')->where('uid='.$withdraw_cash_info['uid'].' AND city_id = '.$withdraw_cash_info['city_id'])->setInc('money',$withdraw_cash_info['money']);
	        		if( $affected_rows != 1 )	throw new Exception('用户钱包金额修改失败[1].');
        		}
        	}
        	
        	if( $pid == 2 && $check_failed_money > 0 )
        	{
        		$affected_rows					= $withdraw_cash_info['wallet_type'] == '0'
        										? M('member_wallet')->where('uid='.$withdraw_cash_info['uid'])->setDec('money',$check_failed_money)
        										: M('member_wallet_city')->where('uid='.$withdraw_cash_info['uid'].' AND city_id = '.$withdraw_cash_info['city_id'])->setDec('money',$check_failed_money);
				if( $affected_rows != 1 )	throw new Exception('用户钱包金额修改失败[2].');        		
        	}
        	
        	if( $check_failed_money > 0 )
        	{
        		$affected_rows					= $withdraw_cash_info['wallet_type'] == '0'
        										? M('member_wallet')->where('uid='.$withdraw_cash_info['uid'])->setDec('freeze',$check_failed_money)
        										: M('member_wallet_city')->where('uid='.$withdraw_cash_info['uid'].' AND city_id = '.$withdraw_cash_info['city_id'])->setDec('freeze',$check_failed_money);
        		if( $affected_rows != 1 )	throw new Exception('用户钱包冻结金额修改失败.');
        	}

        	if(!empty( $update_receive_check_status3_ids ))
        	{
        		$affected_rows	= M('receive')->where("id IN('".implode("','", $update_receive_check_status3_ids)."') AND check_status='2'")->save(array('check_status'=>'3','check_time'=>time()));
        		if( $affected_rows != count( $update_receive_check_status3_ids ))	throw new Exception('从打款审核不通过为审核不通过发生异常.');
        	}

        	M('withdraw_cash','fph_',C('DB_member'))->commit();
        	$msg = array(1,'提交成功');
        	return $msg;
        }
        catch( Exception $e )
        {
        	M('withdraw_cash','fph_',C('DB_member'))->rollback();

        	$msg = array(0,$e->getMessage());
            return $msg;
        }
    }

    //路费提现
    public function withdraw_cash($uid){
        M(NULL,NULL,C('DB_member'));
        if($uid){
            $count = M('withdraw_cash')->where(array('uid'=>$uid))->count('id');
            import("ORG.Util.Page");
            $p = new Page($count, 20);
            $page = $p->show();
            $str = 'id,uid,money,account_type,account,add_time,pay_time,status';
            $list = M('withdraw_cash')->field($str)->where(array('uid'=>$uid))->order('id DESC')->limit($p->firstRow.','.$p->listRows)->select();
        }
        return array($list,$page);
    }



}