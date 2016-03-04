<?php
class guaguakaAction extends frontendAction {

	public function _initialize() {
		parent::_initialize();
		$this->_mod = M('weixin_guaguaka');
	}
	
   public function index() {
   		//setcookie("winning","",time()-1);
    	 $uid = $this->visitor->info['id'];
   	     if($_COOKIE['winning'] && !$uid){
   	         	$url = urlencode($_SERVER['REQUEST_URI']);//获取当前页面url
   	         	$login_url = __ROOT__.'/index.php?g=weixin&m=user&a=login&url='.$url;
   	         	header("Location: $login_url");
   	       }
	      if(!$uid){   //不登陆状态
	      	   $uid=0;
	           $sum=3;
	      }else {
			  $datatime = date("Ymd", time());  //登录状态
			  $info_hits=M('weixin_lottery')->where("uid=".$uid." AND FROM_UNIXTIME(add_time,'%Y%m%d') = ".$datatime."")->count('id');
			  $win_state=M('weixin_lottery')->where("pid=1 AND uid=".$uid." AND FROM_UNIXTIME(add_time,'%Y%m%d') = ".$datatime."")->count('id');
			  if(($info_hits < 3)&&($win_state == 0)){ //当天的次数  有没有中奖
			  	if($_COOKIE['winning']){                                      //未登录中奖了，就插入数据库
				  	$data=unserialize($_COOKIE['winning']);
				  	$data['uid']=$uid;
				  	$data['hits']=0;
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
	    //刮刮卡中奖纪录
	    $win_sum = M('weixin_guaguaka_total')->where('pid=1')->count('id');
	    $this->assign('win_sum', $win_sum+800);
	    //中奖作假纪录   
	    $win_user = M('user')->field('username,mobile')->where('id !='.$uid)->limit(4)->order('rand()')->select();
	    foreach ($win_user as $key=>$val){
	    	$win_user[$key]['the_phone']=mt_rand(1,5000)/100;
	    	$win_user[$key]['mobile']=substr_replace($val['mobile'],'****',3,4);
	    }
	    $this->assign('win_user', $win_user);
	    
		$this->assign('info_hits', ($info_hits+$_COOKIE['htis']));//刮奖次数
		$guaguaka = M('weixin_guaguaka')->field('id,min,max,geiling,proportion,cond,status')->order('id DESC')->select();
		$this->assign('lottery', $guaguaka);
		
		$this->assign('setTitle', '每天刮3次，刮到年底不停手！');
		$this->assign('id', $uid);
        $this->display();
    }
	
    //点击刮刮卡
	public function scratch(){
		
		$id = $this->_post('id','intval');
		if($id==0){ //未登录······
			$interval=M('weixin_guaguaka')->field('id,min,max,geiling,proportion')->where('cond=0')->find();//0门槛区间相关参数
			$sum_total = M('weixin_lottery')->where('`interval`='.$interval['id'])->sum('amount')*100;
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
					$one=substr($random, -1); // 输出 最后一个数字
					$two=substr($random,-2,-1); // 输出 倒数第二个数字
					$three=substr($random,0,-2); // 输出 倒数第三个数字
					if($three!=''){
						$result_arr['award'].=$three.'元';
					}if($two){
						$result_arr['award'].=$two.'角';
					}if($one){
						$result_arr['award'].=$one.'分';
					}
				}else {
					$result_arr['success']=0;
					$result_arr['award']='谢谢参与';
					$data['amount']=0;
				}
			}
			
			$arr_str = serialize(array('interval'=>$interval['id'],'amount'=>$data['amount'],'add_time'=>time()));
			setcookie('winning',$arr_str, time()+3600*24);  //记录中奖信息
			
			$url = urlencode('/fangpinhui/index.php?g=weixin&m=guaguaka&a=index');//获取当前页面url
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

		$cond=M('weixin_lottery')->where("`interval`!=0 and `uid`=".$id)->count('id');//总次数
	
		$res= M('weixin_guaguaka')->field('id')->where('cond<='.$cond)->select();//中奖档次

		$interval_id = $res[mt_rand(0, count($res)-1)]['id'];//获得中奖区间id...

		$interval=M('weixin_guaguaka')->field('min,max,geiling,proportion,cond')->where('id='.$interval_id)->find();//中奖区间相关参数

		$data['uid']=$id;
		$data['interval']=$interval_id;
		$data['amount']=0;
		$data['hits']=0;
		$data['pid']=0;
		$data['add_time']=time();

		//前两次刮中，剩余次数刮不中了
		if(M('weixin_lottery')->where("uid=".$id." AND `amount`!=0 AND FROM_UNIXTIME(add_time,'%Y%m%d') = ".$datatime."")->count('id')){
			M('weixin_lottery')->add($data);//抽奖记录
			$result_arr['success']=0;
			$result_arr['award']='谢谢参与';
			echo json_encode($result_arr);
			exit();
		}
		
		$sum_total = M('weixin_lottery')->where('`interval`='.$interval_id)->sum('amount')*100;
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
			     $one=substr($random, -1); // 输出 最后一个数字
			     $two=substr($random,-2,-1); // 输出 倒数第二个数字
			     $three=substr($random,0,-2); // 输出 倒数第三个数字
			     if($three!=''){
			     	$result_arr['award'].=$three.'元';
			     }if($two){
			     	$result_arr['award'].=$two.'角';
			     }if($one){
			     	$result_arr['award'].=$one.'分';
			     }
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
	
	//引导用户注册页面
	public function  enterance(){
		$this->display();
	}
	
	//申请提现
	
	public function recharge(){
		$uid = $this->visitor->info['id'];
		if(IS_POST){
			$password            = $this->_post('password','trim');
			$user_password =M('user')->where(array('id'=>$uid, 'status'=>1))->getField('password');
			$user_password != md5($password) ? $this->ajaxReturn(0,L('password_error')) : $this->ajaxReturn(1,'密码正确');
			
		}else{
		$win_sum=M('weixin_lottery')->where("uid=".$uid." AND pid = 1")->count('id');//总次数
		$win_totol=M('weixin_guaguaka_total')->where('uid='.$uid." AND pid=1")->getField('total');//累计总额
        $the_phone=floor($win_totol);//总额最大的正整数
		$this->assign('win_sum', $win_sum);
		$this->assign('win_totol', $win_totol);
		$this->assign('the_phone', $the_phone);
		
		$this->display();
	   }
	}
	  //确认提现
	public function recharge_confirm(){
		
		 if(IS_POST){
		    $data['the_phone']=$this->_post('the_phone','trim');
		    $data['mobile']=$this->_post('mobile','trim');
		    $uid = $this->visitor->info['id'];
		    switch ($data['the_phone']){//再次判断是整十数
		    	case 10:
		    		break;
		    	case 20:
		    		break;
		    	case 30:
		    		break;
		    	case 50:
		    		break;
		    	case 100:
		    		break;
		    	default:
		    		$this->ajaxReturn(0,'失败');
		    }
		    $win_totol=M('weixin_guaguaka_total')->where('uid='.$uid." AND pid=1")->getField('total');//尚未领取话费总额
		    ($data['the_phone'] > $win_totol) && $this->ajaxReturn(0,'申请未能成功'); //提交金额大与总额
		    
			$data['uid']=$uid;
			$data['pid']=2;
			$data['add_time']=time();	
			
			if(M('weixin_guaguaka_total')->add($data)){
				$result=M('weixin_guaguaka_total')->where('pid=1 and uid='.$data['uid'])->setDec('total',$data['the_phone']); 
				$this->ajaxReturn(1,$result);
			}else {
				$this->ajaxReturn(0,'失败');
			}	
		}
		$this->assign('mobile',$this->_get('mobile','trim'));
		$this->assign('the_phone',$this->_get('the_phone','trim'));
		$this->display();
	}
	
	public function  recharge_succeed(){
		//楼盘显示
		$fph=C('DB_PREFIX');
		$data['A.status'] = array('eq',1);
		$data['B.term_end'] = array('egt',time());
		$data['B.term_start'] = array('elt',time());
		$list = M('property')->table("{$fph}property AS A LEFT JOIN {$fph}property_cooperation AS B ON A.id=B.pid")->field('A.id,A.title,A.img_thumb,A.list_price,A.item_price,A.city_id')
		        ->where($data)
			    ->limit(1)->order('rand()')->select();
    		foreach($list as $k => $v){
    			//获取区域
    			$str_city = M('city')->field('id,name,pid')->where(array('id'=>$v['city_id']))->find();
    			if($str_city['pid'] != 803)
    			{
    					$str_city2 = M('city')->field('id,name,pid')->where(array('id'=>$str_city['pid']))->find();
    					$str_city['name'] = $str_city2['name'].' '.$str_city['name'];
    					$str_city['id'] = $str_city2['id'];
    		    }
    		  $list[$k]['city_id'] = $str_city['id'];
    		  $list[$k]['city'] = $str_city['name'];
    		}
		
		$this->assign('list', $list);
	
		$this->display();
	}
	
	
	
	
	
	
	
}