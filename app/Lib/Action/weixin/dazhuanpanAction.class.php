<?php
class dazhuanpanAction extends weixin_userbaseAction {
    
    public function index() {

		$uid = $this->visitor->info['id'];
		if(!$uid){
			$url = urlencode($_SERVER['REQUEST_URI']);//获取当前页面url
       		$login_url = __ROOT__.'/index.php?g=weixin&m=user&a=login&url='.$url.'';
        	header("Location: $login_url");
		}
		
		$time = time();
		$datatime = date("Ymd", time());
		$info_count = M('weixin_lottery')->where("uid=".$uid." AND FROM_UNIXTIME(add_time,'%Y%m%d') = ".$datatime."")->count('id');
		$info_hits = M('weixin_lottery')->where("uid=".$uid." AND FROM_UNIXTIME(add_time,'%Y%m%d') = ".$datatime."")->getfield('hits');
		if(!$info_count){
			$info_hits=3;
		}else{
			$info_hits=$info_hits;
		}
		$this->assign('info_hits', $info_hits);
		$lottery = M('weixin_lottery_set')->field('id,one,two,three,four,info')->where("time_start<=$time AND time_end>=$time AND status=1")->order('id DESC')->find();
		$this->assign('lottery', $lottery);
		$this->assign('id', $uid);
        $this->_config_seo();
        $this->display();
    }
		
	//活动规则
	public function show(){
		$uid = $this->_get('uid','intval');
		$pid = $this->_get('pid','intval');
		$table = M('weixin_lottery');
		$ca = C('DB_PREFIX');
		
		$info = M('weixin_article')->where(array('id'=>4,'status'=>1))->find();
		$this->assign('info', $info);
		if($uid && $pid){
			$weixin_lottery_con = $table->where("uid=$uid AND pid=$pid AND prizetype!=0 AND status=0")->count('id');
			if(!$weixin_lottery_con){
				$this->error('你么有中奖或者已经领取');
				exit;
			}
			
			$str = 'A.pid,A.prizetype,B.mobile';
			$weixin_lottery = $table->field($str)
						  ->table("{$ca}weixin_lottery AS A
						  LEFT JOIN {$ca}user AS B ON B.id=".$uid."")
						  ->where("A.uid=$uid AND A.pid=$pid AND A.prizetype!=0 AND A.status=0")
						  ->find();
						  
			$weixin_lottery['mobile'] = substr_replace($weixin_lottery['mobile'],'****',3,4);
			$this->assign('weixin_lottery', $weixin_lottery);
			
			$weixin_lottery_set = M('weixin_lottery_set')->field('one,two,three,four')->where("id=".$weixin_lottery['pid']." AND status=1")->find();
			$this->assign('weixin_lottery_set', $weixin_lottery_set);
			$uid = $this->visitor->info['id'];
			$this->assign('s_id', $uid);
		}
		$this->assign('uid', $uid);
		$this->assign('pid', $pid);
		$this->assign('setTitle', '抽奖规则');
		$this->_config_seo();
        $this->display();
	}
	
	//点击大转盘
	public function click_pointer(){
		$id = $this->_get('id','intval');
		if(!$id){
			$this->ajaxReturn(0,L('illegal_parameters'));
		}
		$datatime = date("Ymd", time());
		$time = time();
		$info_count = M('weixin_lottery')->where("uid=".$id." AND FROM_UNIXTIME(add_time,'%Y%m%d') = ".$datatime."")->count('id');
		if($info_count){
			$info_hits = M('weixin_lottery')->field('id,hits')->where("uid=".$id." AND FROM_UNIXTIME(add_time,'%Y%m%d') = ".$datatime."")->find();
			if(!$info_hits['hits']){
				$this->ajaxReturn(0,'每天只有三次抽奖机会哦');
			}
		}
		
		$lottery_set = M('weixin_lottery_set')->field('id,one,two,three,four')->where("time_start<=$time AND time_end>=$time AND status=1")->order('id DESC')->find();
		if(!$lottery_set){
			$this->ajaxReturn(0,'对不起，本期活动已经结束');
		}
		
		$order_sn=date("Y").date("m").date("d").date("H").date("i").date("s").rand(1,99);
		
		$prize_arr = array( 
			'0' => array('id'=>1,'degree'=>1,'min'=>1,'max'=>5,'prize'=>'一等奖','v'=>0), 
			'1' => array('id'=>2,'degree'=>32,'min'=>7,'max'=>35,'prize'=>'不要灰心','v'=>16), 
			'2' => array('id'=>3,'degree'=>62,'min'=>37,'max'=>65,'prize'=>'三等奖','v'=>0.15),
		//	'2' => array('id'=>3,'degree'=>62,'min'=>37,'max'=>65,'prize'=>'不要灰心,祝您好运','v'=>12), 
			'3' => array('id'=>4,'degree'=>92,'min'=>67,'max'=>95,'prize'=>'祝您好运','v'=>16), 
			'4' => array('id'=>5,'degree'=>122,'min'=>97,'max'=>125,'prize'=>'二等奖','v'=>0.15), 
			'5' => array('id'=>6,'degree'=>150,'min'=>127,'max'=>155,'prize'=>'再接再励','v'=>16), 
			'6' => array('id'=>7,'degree'=>178,'min'=>157,'max'=>185,'prize'=>'三等奖','v'=>0.15), 
			'7' => array('id'=>8,'degree'=>208,'min'=>187,'max'=>215,'prize'=>'运气先藏着','v'=>16), 
			'8' => array('id'=>9,'degree'=>238,'min'=>217,'max'=>245,'prize'=>'四等奖','v'=>2), 
			'9' => array('id'=>10,'degree'=>266,'min'=>247,'max'=>275,'prize'=>'要加油哦','v'=>16), 
		//  '10' => array('id'=>11,'degree'=>298,'min'=>277,'max'=>305,'prize'=>'要加油哦,谢谢你的参与','v'=>12),
			'10' => array('id'=>11,'degree'=>298,'min'=>277,'max'=>305,'prize'=>'二等奖','v'=>0.15), 
			'11' => array('id'=>12,'degree'=>330,'min'=>307,'max'=>335,'prize'=>'谢谢参与','v'=>17.4) 
		); 
		/**
		函数getRand()会根据数组中设置的几率计算出符合条件的id，我们可以接着调用getRand()。
		代码中，我们调用getRand()，获得通过概率运算后得到的奖项，然后根据奖项中配置的角度范围，在最小角度和最大角度间生成一个角度值，并构建数组，包含角度angle和奖项prize，最终以json格式输出。
		**/
		
		$info_count_yes = M('weixin_lottery')->where("uid=".$id." AND pid=".$lottery_set['id']." AND prizetype!=0")->count('id');
		$sumid2  = M('weixin_lottery')->where(array('prizetype'=>2))->count('id');
		$sumid3  = M('weixin_lottery')->where(array('prizetype'=>3))->count('id');
		$sumid4  = M('weixin_lottery')->where(array('prizetype'=>4))->count('id');
		if($info_count_yes || $sumid2==5 || $sumid3==10 || $sumid4==100){
			//$characters = array("2","3","4","6","8","10","11","12");
			$characters = array("2","4","6","8","10","12");    
			shuffle($characters);   
			$rid = $characters[mt_rand(0, count($characters)-1)];   
		}else{
			foreach ($prize_arr as $key => $val) { 
				$arr[$val['id']] = $val['v']; 
			}
			$rid = getRand($arr); //根据概率获取奖项id 
		}
		
		$res = $prize_arr[$rid-1]; //中奖项 
		//$res = $prize_arr[6]; //中奖项 
		//$res = $prize_arr[4]; //中奖项 
		//print_r($res);
		$degree=$res['degree']; 
		//$res['id']=9;
		$min = $res['min']; 
		$max = $res['max']; 
		
		//$res['id']=1;
		if($res['id']==1){
			$result_arr =array('error'=>'','success'=>'true','prizetype'=>1,'sn'=>$order_sn,'pid'=>$lottery_set['id'],'lottery_name'=>$lottery_set['one'],'msg'=>$res['prize']); //这是成功的
		}elseif($res['id']==2){
			$result_arr =array('error'=>'true','success'=>'false','sn'=>'null','msg'=>$res['prize']); //这是失败的
		}elseif($res['id']==3){
			$result_arr =array('error'=>'','success'=>'true','prizetype'=>3,'sn'=>$order_sn,'pid'=>$lottery_set['id'],'lottery_name'=>$lottery_set['three'],'msg'=>$res['prize']); //这是成功的
			//$result_arr =array('error'=>'true','success'=>'false','sn'=>'null','msg'=>$res['prize']); //这是失败的
		}elseif($res['id']==4){
			$result_arr =array('error'=>'true','success'=>'false','sn'=>'null','msg'=>$res['prize']); //这是失败的
		}elseif($res['id']==5){
			$result_arr =array('error'=>'','success'=>'true','prizetype'=>2,'sn'=>$order_sn,'pid'=>$lottery_set['id'],'lottery_name'=>$lottery_set['two'],'msg'=>$res['prize']); //这是成功的
		}elseif($res['id']==6){
			$result_arr =array('error'=>'true','success'=>'false','sn'=>'null','msg'=>$res['prize']); //这是失败的
		}elseif($res['id']==7){
			$result_arr =array('error'=>'','success'=>'true','prizetype'=>3,'sn'=>$order_sn,'pid'=>$lottery_set['id'],'lottery_name'=>$lottery_set['three'],'msg'=>$res['prize']); //这是成功的
		}elseif($res['id']==8){
			$result_arr =array('error'=>'true','success'=>'false','sn'=>'null','msg'=>$res['prize']); //这是失败的
		}elseif($res['id']==9){
			$result_arr =array('error'=>'','success'=>'true','prizetype'=>4,'sn'=>$order_sn,'pid'=>$lottery_set['id'],'lottery_name'=>$lottery_set['four'],'msg'=>$res['prize']); //这是成功的
		}elseif($res['id']==10){
			$result_arr =array('error'=>'true','success'=>'false','sn'=>'null','msg'=>$res['prize']); //这是失败的
		}elseif($res['id']==11){
			$result_arr =array('error'=>'','success'=>'true','prizetype'=>2,'sn'=>$order_sn,'pid'=>$lottery_set['id'],'lottery_name'=>$lottery_set['two'],'msg'=>$res['prize']); //这是成功的
			//$result =array('error'=>'true','success'=>'false','sn'=>'null','msg'=>$res['prize']); //这是失败的
		}elseif($res['id']==12){
			$result_arr =array('error'=>'true','success'=>'false','sn'=>'null','msg'=>$res['prize']); //这是失败的
		}
		$result_arr['angle'] = mt_rand($min,$max); //随机生成一个角度 ,这是以前的
		$result_arr['angle']=$degree; //直接生成一个数组里固定的角度
		if(!$result_arr['msg']){
			$result_arr['msg']='谢谢您的参与，下次再接再厉';
		}
		//print_r($result_arr);
		
		$data['uid']=$id;
		if($result_arr['prizetype']){
			$data['prizetype']=$result_arr['prizetype'];
			$data['pid']=$result_arr['pid'];
		}
		if($info_count){
			$data['hits']=$info_hits['hits']-1;
			if($return === M('weixin_lottery')->where(array('id'=>$info_hits['id'],'uid'=>$id))->save($data)){
				$this->ajaxReturn(0,L('illegal_parameters'));
			}
		}else{
			$data['add_time']=time();
			$data['hits']=2;
			if($return === M('weixin_lottery')->add($data)){
				$this->ajaxReturn(0,L('illegal_parameters'));
			}
		}
		
		echo json_encode($result_arr); 
		//***	
	}
}