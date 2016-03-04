<?php
class hbAction extends frontendAction {

	public function _initialize() {
		parent::_initialize();
		$this->_mod = M('weixin_guaguaka');
	}
	
   public function index() {
   
   		//setcookie("winning","",time()-1);
    	 $uid = $this->visitor->info['id'] ? $this->visitor->info['id'] : 0;
   	     if($_COOKIE['winning'] && !$uid){//第二次必须登录
   	     	
   	     	$this->assign('hit', 1);
   	     	$url = urlencode($_SERVER['REQUEST_URI']);//获取当前页面url
   	     	$login_url = __ROOT__.'/index.php?g=weixin&m=user&a=login&url='.$url;
   	     	$this->assign('login_url', $login_url);
   	     	
   	       }else{
   	       
			  $datatime = date("Ymd", time());  //登录状态
			  $info_hits=M('weixin_lottery')->where("uid=".$uid." AND FROM_UNIXTIME(add_time,'%Y%m%d') = ".$datatime."")->count('id');
			  $win_state=M('weixin_lottery')->where("pid=1 AND uid=".$uid." AND FROM_UNIXTIME(add_time,'%Y%m%d') = ".$datatime."")->count('id');
			  if(($info_hits < 3)&&($win_state == 0)){ //当天的次数  有没有中奖
			  	if($_COOKIE['winning']){                                      //未登录中奖了，就插入数据库
				  	$data=unserialize($_COOKIE['winning']);
				  	$data['uid']=$uid;
				  	$data['hits']=0;
				  	$data['type']=2;
				  	$data['amount']!=0 ? $data['pid']=1 : $data['pid']=0;        //此处方便吴双杰做中奖纪录，但凡刮中统一设为1
				  	M('weixin_lottery')->add($data);                          //刮奖纪录  
					cookie('winning', null);                                 //清除cookie
					setcookie("winning","",time()-1);                                 //清除cookie
					if($data['amount']!=0){
					  	if($total=M('weixin_guaguaka_total')->where(array('uid'=>$uid,'pid'=>1))->getField('total')){ //中奖纪录
					  		$save['total']=$data['amount']+$total;
					  		$save['add_time']=$data['add_time'];
					  		M("weixin_guaguaka_total")->where(array('uid'=>$uid,'pid'=>1))->save($save);
					  	}else {
						  	$total_data['pid']=1;
						  	$total_data['uid']=$uid;
						  	$total_data['total']=$data['amount'];
						  	$total_data['add_time']=$data['add_time'];
						  	M("weixin_guaguaka_total")->add($total_data);                 //中奖纪录
					  	}
					}
			   }
			}
			cookie('winning', null);                                 //清除cookie
			setcookie("winning","",time()-1);                                 //清除cookie
	      }

	    
		$this->assign('info_hits', ($info_hits+$_COOKIE['htis']));//刮奖次数
		
		$this->assign('id', $uid);
   	
        $this->display();
    }
	
    
    
    
    //点击刮刮卡
	public function scratch(){
		
		$id = $this->_post('id','intval');
		if($id==0){ //未登录······
			$interval=M('weixin_guaguaka')->field('id,min,max,geiling,proportion')->where('cond=0')->find();//0门槛区间相关参数
			$sum_total = M('weixin_lottery')->where('`type`=2 and `interval`='.$interval['id'])->sum('amount')*100;
			if($sum_total >= $interval['geiling']){ //该区间最高限额已发完
				$result_arr['success']=0;
				$result_arr['award']='谢谢参与';
				$data['amount']=0;
			}else{
				$randNum=mt_rand(1,100);  //计算中奖概率及其金额
				if($randNum>=1 && $randNum<=$interval['proportion']){
					$result_arr['success']=1;
					$random=mt_rand($interval['min'],$interval['max']);
					$data['amount']=$random/100;
					$result_arr['award']=$data['amount'];
				}else {
					$result_arr['success']=0;
					$result_arr['award']='谢谢参与';
					$data['amount']=0;
				}
			}
			
			$arr_str = serialize(array('interval'=>$interval['id'],'amount'=>$data['amount'],'add_time'=>time()));
			setcookie('winning',$arr_str, time()+3600*24);  //记录中奖信息
			
			$url = urlencode('/fangpinhui/index.php?g=weixin&m=hb&a=index');//获取当前页面url
			$login_url = __ROOT__.'/index.php?g=weixin&m=user&a=login&url='.$url;
			$result_arr['login_url']=$login_url;
			echo json_encode($result_arr);
			exit();
		}
		
		$datatime = date("Ymd", time());
		if(M('weixin_lottery')->where("uid=".$id." AND FROM_UNIXTIME(add_time,'%Y%m%d') = ".$datatime."")->count('id') >=3 ){
			$result_arr['success']=2;
			$result_arr['award']='今天刮奖次数已用完';
			echo json_encode($result_arr);
			exit();
		}

		$cond=M('weixin_lottery')->where("`type`=2 and `interval`!=0 and `uid`=".$id)->count('id'); //总次数
	
		$res= M('weixin_guaguaka')->field('id')->where('cond<='.$cond)->select();//中奖档次

		$interval_id = $res[mt_rand(0, count($res)-1)]['id'];//获得中奖区间id...

		$interval=M('weixin_guaguaka')->field('min,max,geiling,proportion,cond')->where('id='.$interval_id)->find(); //中奖区间相关参数

		$data['uid']=$id;
		$data['interval']=$interval_id;
		$data['amount']=0;
		$data['hits']=0;
		$data['pid']=0;
		$data['type']=2;//区分红包的
		$data['add_time']=time();

		//前两次抽中，剩余次数抽不中了
		if(M('weixin_lottery')->where("uid=".$id." AND `amount`!=0 AND FROM_UNIXTIME(add_time,'%Y%m%d') = ".$datatime."")->count('id')){
			M('weixin_lottery')->add($data);//抽奖记录
			$result_arr['success']=0;
			$result_arr['award']='谢谢参与';
			echo json_encode($result_arr);
			exit();
		}
		
		$sum_total = M('weixin_lottery')->where('`type`=2 and `interval`='.$interval_id)->sum('amount')*100;
		if($sum_total >= $interval['geiling']){ //该区间最高限额已发完 
			     M('weixin_lottery')->add($data);//抽奖记录
			     $result_arr['success']=0;
			     $result_arr['award']='谢谢参与';
			echo json_encode($result_arr);
		    exit();
		} 
		
	    $randNum=mt_rand(1,100);  //计算中奖概率及其金额
		if($randNum>=1 && $randNum<=$interval['proportion']){
			     $result_arr['success']=1;
			     $random=mt_rand($interval['min'],$interval['max']);
			     $data['amount']=$random/100;
                 $result_arr['award']=$data['amount'];
			     if($total=M('weixin_guaguaka_total')->where(array('uid'=>$id,'pid'=>1))->getField('total')){ //中奖纪录
			     	$save['total']=$data['amount']+$total;
			     	$save['add_time']=time();
			     	M("weixin_guaguaka_total")->where(array('uid'=>$id,'pid'=>1))->save($save);
			     }else {
			     	$add['total']=$data['amount'];
			     	$add['add_time']=time();
			     	$add['uid']=$id;
			     	$add['pid']=1;
			     	M('weixin_guaguaka_total')->add($add);
			     }
			     $data['pid']=1;//此处方便吴双杰做中奖纪录，但凡刮中统一设为1
		}else{
			$result_arr['success']=0;
			$result_arr['award']='谢谢参与';
		}
		     M('weixin_lottery')->add($data);//抽奖记录
		     
		     $result_arr['hits']=$sum_hits;
		echo  json_encode($result_arr);
		exit();
	}
	
	
	
	
	
	
}