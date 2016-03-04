<?php
class operationdepartmentAction extends frontendAction {
    
   public function sign()
   {
   		M(NULL,NULL,C('DB_OPERATION_ACTIVITY'));
   		$openid = 'dataaaaa';
   		if(empty($openid)) 	$this->error('用户信息获取失败！');
   		$data = D('operationactivity')->allSignDate($openid);
   		$count = count($data);
   		$str = '';
   		foreach($data as $key => $val)
   		{
   			$str .= intval(date('d',$val['signdate'])).',' ;
   		}
   		$nickname = 'nickname';
   		$this->assign('lastsign',date('Ymd',$data[$count-1]['signdate']));
   		$data = base64_encode(rtrim($str,','));  	
   		$this->assign('openid',$openid);
   		$this->assign('data',$data);
   		$this->assign('nickname',$nickname);
   		$this->assign('count',$count);
        $this->display('index');
   }

   public function addsign()
   {
   		$data['openid']   = $this->_post('openid','trim');
   		!$data['openid'] && $this->ajaxReturn(0,'用户信息获取失败！');
   		$data['signtime'] = time();
   	    $data['signdate'] = strtotime(date('Y-m-d'));
   	    M(NULL,NULL,C('DB_OPERATION_ACTIVITY'));
   		$isSign = D('operationactivity')->isSign($data['signdate'],$data['openid']);
   		//echo D('operationactivity')->getlastsql();exit;
   		if(!$isSign)
   		{  
   			$data['clientip'] = get_ip();
   			$data['nickname'] = $this->_post('nickname','trim');
   			$r = D('operationactivity')->insertSignRecord($data);
   			$this->ajaxReturn(1,'OK');
   		}else{
   			$this->ajaxReturn(0,'OK','ERROR');
   		}

   }

}