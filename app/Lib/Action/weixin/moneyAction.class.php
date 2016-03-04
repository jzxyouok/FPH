<?php
class moneyAction extends weixin_userbaseAction
{
	public function index(){
		$data['uid'] = $this->visitor->info['id'];
		$soso = '';
		$fph = C('DB_PREFIX');
		$list = array();
		//累计
		$list['addup'] = 0;
		//已结
		$list['closedaccount'] = 0;
		//未结
		$list['notknot'] = 0;
		
		$list['commission'] = array();
		
		$where = 'A.status = 7 AND B.uid = '.$data['uid'] ;
		if(isset($_POST) AND $_POST)
		{
			if(!empty($_POST['soso']))
			{
				$where .= ' AND A.username LIKE "%'.$_POST['soso'].'%" ';
				$soso = $_POST['soso'];
			}
			
		}
		$list['commission'] = M('myclient_status')
		->field('A.id,A.pid,A.username,A.mobile,C.expenditure,C.status as cstatus')
		->table("{$fph}myclient_status AS A
		 INNER JOIN {$fph}myclient_property AS B ON A.mpid = B.id
		 INNER JOIN {$fph}commission as C ON C.pid=A.id
		 ")
		->where($where)
		->select();
		
		foreach($list['commission'] as $k=>$v)
		{
			
			   $list['commission'][$k]['commission_time'] = M('expenditure')->where('pid ='.$v['id'])->order('add_time DESC')->getField('add_time');
			   $list['commission'][$k]['commission_time'] = date('Y-m-d', $list['commission'][$k]['commission_time']);
			   $list['commission'][$k]['property_name'] = M('property')->where('id ='.$v['pid'])->getField('title');
			   $list['commission'][$k]['meincome'] = M('expenditure')->where('pid='.$v['id'])->sum('price');
			   if(empty($list['commission'][$k]['meincome']))
					 $list['commission'][$k]['meincome'] = 0;
			       
			   $list['commission'][$k]['surplus'] = $v['expenditure'] - $list['commission'][$k]['meincome'];
			   $list['closedaccount'] = $list['closedaccount'] + $list['commission'][$k]['meincome'];
			   $list['notknot'] = $list['notknot'] + $v['expenditure'];
			   
			   if($list['commission'][$k]['meincome'] == 0)
			   {
					 $list['commission'][$k]['commissionstatus'] = 1;
			   }
			   
			   elseif($list['commission'][$k]['meincome'] > 0)
			   {
					 $list['commission'][$k]['commissionstatus'] = 2;
			   }
			   
			   if($list['commission'][$k]['meincome'] == $v['expenditure'])
			   {
					 $list['commission'][$k]['commissionstatus'] = 3;
			   }
			   
			   if($list['commission'][$k]['surplus'] < 0)
					 $list['commission'][$k]['surplus'] = 0;
				      
			   $list['commission'][$k]['surplus'] = sprintf("%.2f", $list['commission'][$k]['surplus']);
			   $list['commission'][$k]['meincome'] = sprintf("%.2f", $list['commission'][$k]['meincome']);
		}
		
		$list['notknot'] = $list['notknot'] - $list['closedaccount'];
		if($list['notknot'] < 0)
		    $list['notknot']  = 0;
		    
		$list['addup'] = $list['notknot'] + $list['closedaccount'];
		
		$list['addup'] = sprintf("%.2f", $list['addup']);
		$list['closedaccount'] = sprintf("%.2f", $list['closedaccount']);
		$list['notknot'] = sprintf("%.2f", $list['notknot']);
		
		
		foreach($list['commission'] as $k=>$v)
		{
			$list['commission_list'][$v['commission_time']][] = $v;
		}
		
		$this->assign('list', $list);
		$this->assign('soso', $soso);
		$this->assign('setTitle', '我的佣金');
		$this->_config_seo();
		$this->display();
	}
	
	
	public function details()
	{
		$id = $this->_get('id','intval');
		
		if(empty($id))
		{
			$this->_404();
		}
		$ic = M('income')->where('pid ='.$id)->select();
		$ex = M('expenditure')->where('pid ='.$id)->select();
		$list = array();
		foreach($ic as $k=>$v)
		{
			$ic[$k]['status'] = 1;
			$list[] = $ic[$k];
		}
		foreach($ex as $k=>$v)
		{
			$ex[$k]['status'] = 2;
			$list[] = $ex[$k];
		}
		
		$money = array();
		$money['expenditure'] = M('commission')->where('pid='.$id)->getField('expenditure');
		$money['price'] =M('expenditure')->where('pid='.$id)->sum('price');
		$money['notprice'] =  $money['expenditure'] - $money['price'];
		$money['expenditure'] = sprintf("%.2f", $money['expenditure']);
		$money['price'] = sprintf("%.2f", $money['price']);
		$money['notprice'] = sprintf("%.2f", $money['notprice']);
		
		$mys = M('myclient_status')->where('id ='.$id)->find();
		$mys['title'] = M('property')->where('id='.$mys['pid'])->getField('title');
		
		$this->assign('mys',$mys);
		$this->assign('money',$money);
		$this->assign('list',$list);
		$this->assign('setTitle', '我的佣金');
		$this->_config_seo();
		$this->display();
	}
}