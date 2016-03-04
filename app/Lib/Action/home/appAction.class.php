<?php
// +----------------------------------------------------------------------
// | fangpinhui [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014.8.22-3000.8.22 http://www.fangpinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.fangpinhui.com/index.php )
// +----------------------------------------------------------------------
// | Author: H.J.H <hujiuhua@fangpinhui.com>
// +----------------------------------------------------------------------

class AppAction extends baseAction {
		
	   protected $path = 'http://www.fangpinhui.com';
	   protected $local_path = 'http://www.fangpinhui.com';
	   
	/**
    +----------------------------------------------------------
    * 初始化
    +----------------------------------------------------------
   */
	public function _initialize(){
		parent::_initialize();
	    header("Content-Type:text/html; charset=utf-8");
	    //$_POST['token']!=md5('home_apps'.date('Y-m-d',time()).'#$@%!*') && $this->ajaxReturn(51,L('app_token'));    
	  }

   /**
    +----------------------------------------------------------
    * 注册
    +----------------------------------------------------------
   */
  public function register(){
	  	
	  	  $data=json_decode($_POST['params'],TRUE);
	      
	      !$data['mobile'] && $this->ajaxReturn(51,L('app_mobile_empty'));
	      !checkMobile($data['mobile']) && $this->ajaxReturn(51,L('app_mobile_format'));
	      M('user')->where("mobile='".$data['mobile']."' and status=1")->count('id') &&  $this->ajaxReturn(1004, L('app_username_exist'));

	  	
	      !$data['mobile_code'] && $this->ajaxReturn(51,L('app_mobile_code_empty'));
	      
	      !$data['password'] && $this->ajaxReturn(51,L('app_password_empty'));
	      strlen($data['password'])<6 && $this->ajaxReturn(1006,L('app_password_format'));
	      
	      $data['share_id'] && !(M('user')->where("id ='".$data['share_id']."' and status=1")->count('id')) && $this->ajaxReturn(51,'share_id'.L('app_username_exist'));
	      
	      $data['reg_time']    = time();
	      $data['last_time']   = time();
	      $data['password']    = md5($data['password']);
	      $data['gender']      = 2;
	      $data['origin']      =$data['equipment_type']+1;
	      
	      $add_uid=M('user')->add($data);
	      
	      $res_data['uuid']=sprintf( '%04x%04x%04x%04x%04x%04x%04x%04x',
	      		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
	      		mt_rand(0, 0x0fff) | 0x4000,
	      		mt_rand(0, 0x3fff) | 0x8000,
	      		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));//设置唯一的标示
	      $total_data['uuid']=$res_data['uuid'];
	      $total_data['id']=$add_uid;
	      $total_data['mobile']=$data['mobile'];
	      //每次登陆都要给uuid，纪录不同时间段的登录。
	      if(M('app_login')->where(array('equipment_number'=>$data['equipment_number'],'uid'=>$add_uid,'status'=>1))->count('id')){
	      	M('app_login')->where(array('equipment_number'=>$data['equipment_number']))->save(array('last_time'=>time(),'uuid'=>$res_data['uuid']));
	      	$this->ajaxReturn(200,L('reg_successe'),$total_data);
	      }else{
	      	$res_data['uid']=$add_uid;
	      	$res_data['equipment_number']=$data['equipment_number'];
	      	$res_data['equipment_type']=$data['equipment_type'];
	      	$res_data['last_time']=time();
	      	M('app_login')->add($res_data) ? $this->ajaxReturn(200,L('reg_successe'),$total_data) : $this->ajaxReturn(1003,L('reg_error'));
	      }
	      
	  }
	  
  /**
    +----------------------------------------------------------
    * 获取验证码
    +----------------------------------------------------------
   */
	function get_mobile_code(){
		
			$data=json_decode($_POST['params'],TRUE); 
			
			$mobile_code = random(6,1);
			
			!$data['mobile'] && $this->ajaxReturn(51,L('app_mobile_empty'));
			!checkMobile($data['mobile']) && $this->ajaxReturn(51,L('app_mobile_format'));
			
		
			$uid =M('user')->where("mobile='".$data['mobile']."'")->count('id');
			if($data['type']=='1'){  //1注册2忘记密码
				$uid &&  $this->ajaxReturn(1004, L('app_mobile_exist'));
			}
			if($data['type']=='2'){
				!$uid &&  $this->ajaxReturn(1002,'用户不存在');	
			}
			$send_sms = D('send_sms');
			
			$result   = $send_sms->Messages($data['mobile'],$agent_mobile,$agent_name,$client_name,$client_mobile,$title,$str,$mobile_code,'3',false,'2',$data['type']);
			$res_data['code']=$mobile_code;
			
			$result ? $this->ajaxReturn(200,L('app_requre_normal'),$res_data) : $this->ajaxReturn(1003,'Database operation failure');
	}
	
	 /**
    +----------------------------------------------------------
    * 登录
    +----------------------------------------------------------
   */
	  
	function  login(){
		
		    $data=json_decode($_POST['params'],TRUE);
				
			!$data['mobile'] && $this->ajaxReturn(51,L('app_mobile_empty'));
			!checkMobile($data['mobile']) && $this->ajaxReturn(51,L('app_mobile_format'));
			
	        !$data['password'] && $this->ajaxReturn(51,L('app_password_empty'));
	        strlen($data['password'])<6 && $this->ajaxReturn(51,L('app_password_format'));
				
			$user = M('user')->field('id,password,username')->where(array('mobile'=>$data['mobile'], 'status'=>1))->find();
			if (!$user) {
				$this->ajaxReturn(1002,L('user_not_exist'));
			}
			if ($user['password'] != md5($data['password'])) {
				$this->ajaxReturn(1001,L('password_error'));
			}
			M('user')->where(array('id'=>$user['id']))->save(array('last_time'=>time(),'last_ip'=>$data['last_ip']));//原表记录一条登录记录

			$res_data['uuid']=sprintf( '%04x%04x%04x%04x%04x%04x%04x%04x',
					mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
					mt_rand(0, 0x0fff) | 0x4000,
					mt_rand(0, 0x3fff) | 0x8000,
					mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));//设置唯一的标示
			$total_data['uuid']=$res_data['uuid'];
			$total_data['id']=$user['id'];
			$total_data['mobile']=$data['mobile'];
			$total_data['username']=$user['username'];
			//每次登陆都要给uuid，纪录不同时间段的登录。
			if(M('app_login')->where(array('equipment_number'=>$data['equipment_number'],'uid'=>$user['id'],'status'=>1))->count('id')){	
			   M('app_login')->where(array('equipment_number'=>$data['equipment_number']))->save(array('last_time'=>time(),'uuid'=>$res_data['uuid']));
			   $this->ajaxReturn(200,L('login_successe'),$total_data);
			}else{
				$res_data['uid']=$user['id'];
				$res_data['equipment_number']=$data['equipment_number'];
				$res_data['equipment_type']=$data['equipment_type'];
				$res_data['last_time']=time(); 
				M('app_login')->add($res_data) ? $this->ajaxReturn(200,L('login_successe'),$total_data) : $this->ajaxReturn(1003,L('login_error'));
			}
	}
	

	/**
	 +----------------------------------------------------------
	 * 忘记密码 --直接设置新密码
	 +----------------------------------------------------------
	 */
	 public  function forgot_password (){
		
	 	$data=json_decode($_POST['params'],TRUE);
	 	
	 	!$data['mobile'] && $this->ajaxReturn(51,L('app_mobile_empty'));
	 	!checkMobile($data['mobile']) && $this->ajaxReturn(51,L('app_mobile_format'));
	 		
	 	!$data['password'] && $this->ajaxReturn(51,L('app_password_empty'));
	 	strlen($data['password'])<6 && $this->ajaxReturn(51,L('app_password_format'));
	 	
	 	!$data['mobile_code'] && $this->ajaxReturn(51,L('app_mobile_code_empty'));
	 	!$data['equipment_type'] && $this->ajaxReturn(51,'equipment_type'.L('app_empty'));
	 	!$data['equipment_number'] && $this->ajaxReturn(51,'equipment_number'.L('app_empty'));
	 	
	 	$user = M('user')->field('id,password,username')->where(array('mobile'=>$data['mobile'], 'status'=>1))->find();
	 	if (!$user) {
	 		$this->ajaxReturn(1002,L('user_not_exist'));
	 	}

	 	M('user')->where(array('id'=>$user['id']))->save(array('last_time'=>time(),'password'=>md5($data['password'])));//原表记录一条登录记录
	 	
	 	$res_data['uuid']=sprintf('%04x%04x%04x%04x%04x%04x%04x%04x',
					mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
					mt_rand(0, 0x0fff) | 0x4000,
					mt_rand(0, 0x3fff) | 0x8000,
					mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));//设置唯一的标示
			$total_data['uuid']=$res_data['uuid'];
			$total_data['id']=$user['id'];
			$total_data['mobile']=$data['mobile'];
			$total_data['username']=$user['username'];
			//每次登陆都要给uuid，纪录不同时间段的登录。
			if(M('app_login')->where(array('equipment_number'=>$data['equipment_number'],'status'=>1))->count('id')){	
			   M('app_login')->where(array('equipment_number'=>$data['equipment_number']))->save(array('last_time'=>time(),'uuid'=>$res_data['uuid']));
			   $this->ajaxReturn(200,L('login_successe'),$total_data);
			}else{
				$res_data['uid']=$user['id'];
				$res_data['equipment_number']=$data['equipment_number'];
				$res_data['equipment_type']=$data['equipment_type'];
				$res_data['last_time']=time(); 
				M('app_login')->add($res_data) ? $this->ajaxReturn(200,L('login_successe'),$total_data) : $this->ajaxReturn(1003,L('login_error'));
			}
		
	} 
	
	
	/**
	 +----------------------------------------------------------
	 * 登录uuid检验
	 +----------------------------------------------------------
	 */
	public function  check_uuid($uuid,$uid){
		
		!M('app_login')->where(array('uuid'=>$uuid,'uid'=>$uid))->count('id') && $this->ajaxReturn(51,L('app_requre'));
	}
	
	
	/**
	 +----------------------------------------------------------
	 * 合作楼盘列表
	 +----------------------------------------------------------
	 */
	 
public function cooperation_building() {
	    $fph = C('DB_PREFIX');
		$data=json_decode($_POST['params'],TRUE);
		
		!isset($data['page']) && $this->ajaxReturn(51,'页号不能为空');
		!$data['number_each_page'] && $this->ajaxReturn(51,'页条数不能为空');
		!$data['type'] && $this->ajaxReturn(51,'城市不能为空');
		
		$area =$data['type'];
		$time  = time();
        if($area<10000){
		  $temp =  "select id from {$fph}city where id in(".$area.") or spid LIKE '%".$area."%' ";
		  $where = "and ".$time.">B.term_start and ".$time." < B.term_end and A.city_id in (".$temp.")";
        }else {
          $where = "and ".$time.">B.term_start and ".$time." < B.term_end ";
        }
		
		
		//$this->ajaxReturn(200,L('app_requre_normal'),$area,$where);
		
		$page = $data['page'];
		$number_each_page=$data['number_each_page'];
		$start = $page*$number_each_page;
		
		
		//楼盘显示
		$list = M('property')->field('A.id,A.title,A.prefer,A.img_thumb as img,A.list_price as commission,A.item_price,A.city_id,A.add_time')
		->table("{$fph}property AS A INNER JOIN {$fph}property_cooperation AS B ON A.id = B.pid")
		->where('A.status=1 '.$where)
		->order('A.add_time DESC')
		->limit($start,$number_each_page)
		->select();
		
		//避免传过去空指针和空值
        (!$list) && $this->ajaxReturn(52,'请求无数据',array());
        
		foreach($list as $k => $v){
			$str_city = M('city')->field('id,name,pid,spid')->where(array('id'=>$v['city_id']))->find();//获取区域
			($v['prefer']=="暂无" || $v['prefer']=="待定" ||$v['prefer']=="")? $list[$k]['is_prefer']=0 : $list[$k]['is_prefer']=1;
				$str_city2 = M('city')->field('id,name,pid')->where(array('id'=>$str_city['pid']))->find();
				$str_city['name'] = $str_city2['name'].' '.$str_city['name'];
				$list[$k]['city'] = $str_city['name'];
				$list[$k]['favorites'] = M('favorites')->where(array('pid'=>$v['id']))->count('id');//获取楼盘收藏量
				if($data['uid']){
					$list[$k]['is_favorite'] = M('favorites')->where(array('pid'=>$v['id'],'uid'=>$data['uid']))->count('id');//是否收藏
				}
				//$list[$k]['img']=$this->path.attach(get_thumb($v['img'], '_app_list_thumb'), 'property/thumbnail');
				$list[$k]['img']=get_fdfs_image($v['img'], '_app_list_thumb');
				$list[$k]['add_time']=date("Y-m-d H:i:s", $v['add_time']);
		}
		$list=array_values($list);
		$list ? $list : $list=array();
		
		   //print_r($list);
		
		$this->ajaxReturn(200,L('app_requre_normal'),$list);
	}
	
	
  /**
    +----------------------------------------------------------
    * 合作楼盘详情
    +----------------------------------------------------------
   */
function  cooperation_building_detail(){
		
		$data = json_decode($_POST['params'],TRUE);    
	 
		$id   = $data['pid'];
		//$id=182;
        !$id && $this->ajaxReturn(51,L('app_requre'));

        $str_list='A.id,A.title,A.bus as traffic,A.open_time,A.check_time,A.info,A.property_costs,A.sales_address,A.property_type as leixing,A.business,A.item_price,A.prefer,A.feature,A.latitude,A.address as weizhi,A.list_price as commission';
        $fph = C('DB_PREFIX');
		$list = M('property')->field($str_list)
		                     ->table("{$fph}property AS A INNER JOIN {$fph}property_cate AS B ON A.property_type = B.id")
		                     ->where(array('A.id'=>$id))->find();
		$list['mianji']='';
		//去除html标签
		$list['feature']= substr(preg_replace("/\s+/", "\n",str_replace(array(" ","&nbsp;"),'',strip_tags($list['feature']))),0);//楼盘卖点儿
		$list['info']= substr(preg_replace("/\s+/", "\n",str_replace(array(" ","&nbsp;"),'',strip_tags($list['info']))),0);//项目描述
		$list['traffic']= preg_replace("/\s+/", "\n",str_replace(array(" ","&nbsp;"),'',strip_tags($list['traffic'])));//项目描述

		$list['commission'] = $list['commission'] ? $list['commission'] : '';
		
		//时间戳转化
		$list['open_time']=date("Y-m-d",$list['open_time']);//开盘时间
		$list['check_time']=date("Y-m-d",$list['check_time']);	//入住时间
		
		//判断是否收藏
		$user_info['uid'] = $data["uid"];
		if($user_info['uid']){
		 //	$this->check_uuid($data["uuid"],$user_info['uid']);
			$favorites_count = M('favorites')->where(array('pid'=>$id,'uid'=>$user_info['uid']))->count('id');
			$list['is_favorite']=$favorites_count;
		}
		$list['favorites'] = M('favorites')->where(array('pid'=>$id))->count('id');//获取楼盘收藏量
		//判断是否收藏
		
		//热门活动
		$srt = 'id,pid,title';
		$hdlist = M('article')->field($srt)->where(array('pid'=>$id))->select();
		$list['hdlist']=$hdlist;
		//热门活动
		
		//在售户型
		$srt = 'id,house_name as name,house_img as img,house_room as fangxinshi,house_hall as fangxinting,house_wc as fangxinwei,house_area as mianji,status as sell';
		$hxlist = M('property_housetype')->field($srt)->where(array('pid'=>$id))->select();
		foreach($hxlist as $k=>$v){
			if($hxlist[$k]['sell']!=0) $hxlist[$k]['sell'] = $hxlist[$k]['sell']+1;
			$hxlist[$k]['img']=$this->path.attach(get_thumb($v['img'], '_280x210'), 'property/huxing');
		}
		$list['hxlist']=$hxlist;
		//在售户型
		//物业类型
		$leixing = M('property_cate')->field('id,name')->where('id in ('.$list['leixing'].')')->select();
		$Property_type='';
		foreach($leixing as $k=>$v){
			 $Property_type .= $leixing[$k]['name'].' ';
		}
		$list['leixing'] = $Property_type;
		//物业类型
		
		//7张图片
		$xglist = M('property_img')->where(array('pid'=>$id,'status'=>1))->select();
		$pic_img = array();
		if(!empty($xglist))
		{
			foreach($xglist as $k => $v)
			{
				$pic_img[$k]['img'] = $this->path.attach(get_thumb($v['img'], '_640x480'), 'property/xiaoguo');
				
				$pic_img[$k]['title'] = $v['title'] ? $v['title'] : ' ';
			}
		}
		$list['pic_img']=$pic_img;
		//7张图片
		
		//print_r($list);die();
		
		$this->ajaxReturn(200,L('app_requre_normal'),$list);
	}
	/**
    +----------------------------------------------------------
    * 合作楼盘的户型详情
    +----------------------------------------------------------
   */
    public function door_model_detail() {
		$data=json_decode($_POST['params'],TRUE);
		$id=$data['pid'];
		//$id =101;
		$srt = 'id,house_name as name,house_img as img,house_room as fangxinshi,house_hall as fangxinting,house_wc as fangxinwei,house_info as maidian,status as sell';
		$info = M('property_housetype')->field($srt)->where(array('id'=>$id))->find();
		
		$info['img'] =$this->path.attach(get_thumb($info['img'], '_640x480'), 'property/huxing');
		$info['sell'] =$info['sell']+1;
		
		$this->ajaxReturn(200,L('app_requre_normal'),$info);
	}
	
	/**
    +----------------------------------------------------------
    * 活动详情
    +----------------------------------------------------------
   */
	public function activity_detail() {
		$data=json_decode($_POST['params'],TRUE);
		$id=$data['pid'];
		//$id = 144 ;
        $info = M('article')->field('id,title,img,time_start,time_end,info,pid,city_id')->where(array('cate_id'=>1,'id'=>$id))->find();
        
        //去除html标签
        $info['info'] = preg_replace("/\s+/", "\n",str_replace(array(" ","&nbsp;"),'',strip_tags($info['info'])));
        
        //时间转换
        $info['time_start']=date("Y-m-d",$info['time_start']);
        $info['time_end']=date("Y-m-d",$info['time_end']);
        
        
        $info['img'] =$this->path.attach(get_thumb($info['img'], ''), 'article'); 
        $info['protitle'] = M('property')->where(array('status'=>1,'id'=>$info['pid']))->getField('title');
            
        $cityinfo = M('city')->field('pid,name')->where(array('id'=>$info['city_id']))->find();
        $info['city'] = M('city')->field('name')->where(array('id'=>$cityinfo['pid']))->getField('name');
   
        $info['quyu']=$cityinfo['name'];
        
        $info['kefu_tel']=C('pin_kefu_tel');
            
          // print_r($info);die();
        $this->ajaxReturn(200,L('app_requre_normal'),$info);
    }
	
	/**
    +----------------------------------------------------------
    * 收藏/取消
    +----------------------------------------------------------
   */
    public function collection (){   	
        $data=json_decode($_POST['params'],TRUE);
		$pid=$data['pid'];
		$user_info['id']=$data['uid'];
		$uuid=$data['uuid'];
		
		//$pid =105;$user_info['id']=314;
		
        (!$user_info['id']) && $this->ajaxReturn(51,'uid'.L('app_empt'));	 		
        (!$pid) && $this->ajaxReturn(51,'pid'.L('app_empt'));
        (!$uuid) && $this->ajaxReturn(51,'uuid'.L('app_empt'));
        
        $this->check_uuid($uuid,$user_info['id']);
    		 
        M('favorites')->where(array('pid'=>$pid,'uid'=>$user_info['id']))->count('id') && $this->ajaxReturn(200,L('app_collected'));
    	 
    	   $data['uid']  = $user_info['id'];
    	   $data['pid']  = $pid;
    	   $data['add_time']=time();
    	if($return !== M('favorites')->add($data)){
    		$this->ajaxReturn(200,L('app_favorite_successe'));
    	}else{
    		$this->ajaxReturn(1003,L('app_operation_error'));
    	}
     
    }
    public function cancel_collection (){
        $data=json_decode($_POST['params'],TRUE);
		$pid=$data['pid'];
		$user_info['id']=$data['uid'];
		$uuid=$data['uuid'];
		//$pid =105;$user_info['id']=314;
    
       (!$user_info['id']) && $this->ajaxReturn(51,uid.L('app_empt'));
       (!$pid) && $this->ajaxReturn(51,pid.L('app_empt'));
       (!$uuid) && $this->ajaxReturn(51,uuid.L('app_empt'));
       
       $this->check_uuid($uuid,$user_info['id']);
       
       (!M('favorites')->where(array('pid'=>$pid,'uid'=>$user_info['id']))->count('id')) && $this->ajaxReturn(200,L('app_cancel_collected'));
    		
    	if($return !==M('favorites')->where(array('pid'=>$pid,'uid'=>$user_info['id']))->delete()){
    			$this->ajaxReturn(200,L('app_cancel_collect'));
    	}else{
    			$this->ajaxReturn(1003,L('app_operation_error'));
    	}
    }
	
/**
    +----------------------------------------------------------
    * 报备带看——推客
    +----------------------------------------------------------
   */
 public function reporte() {
        	$datas=json_decode($_POST['params'],TRUE);

            $gettime = time();
            $data['name']   =  $datas['name'];
            $data['gender'] =  $datas['gender']; 
            $data['mobile'] =  $datas['mobile'];
            
            $datap['uid']       =  $datas['uid'];
            $datap['property']  =  $datas['pid'];
            $datap['with_look'] =  $datas['with_look'];
            $datap['add_time']  =  $gettime;
            
            $time = strtotime(date("Y-m-d",$gettime));
            if($datap['with_look']==1){
                //带看有效期
			    $datap['visit_time'] = strtotime($datas['visit_time']);
                if($datap['visit_time'] <$time){
                    $this->ajaxReturn(1009,'到访时间不能小于当天时间');
                }
            }
            if(!$datas['uid']){
                $this->ajaxReturn(51,'报备uid不能为空');
	        }
	        
 	        //判断带看,委托 次数
	        $fph=C('DB_PREFIX');
	        $databaobei['A.uid'] = $datas['uid'];
	        $databaobei['A.add_time'] =  array('between',array(strtotime(date('Y-m-d')),strtotime(date('Y-m-d 23:59:59'))));
	        $countbaobei = M('myclient_property')->table("{$fph}myclient_property AS A LEFT JOIN {$fph}myclient AS B ON A.pid=B.id")->where($databaobei)->count('B.id');
            if($countbaobei>=C('pin_daikan_views')){
                $this->ajaxReturn(1003,'您今天已报备了'.$countbaobei.'次，达到了每日上限，请明天再来吧');
             }
	        
	        
            if(!checkusername($data['name'])){
                    $this->ajaxReturn(1010,'客户姓名不规范');
            }
            if(!checkMobile($data['mobile'])){
                $this->ajaxReturn(1010,'手机号码填写错误');
	        }           
            if(!$datap['property']){
                 $this->ajaxReturn(51,'报备楼盘不为能为空');
	        }
            if(!$datap['with_look']){
                $this->ajaxReturn(51,'请选择带看类型');
	        }
            
            $myclient_id = M('myclient')->field("id")->where(array('mobile'=>$data['mobile']))->find();
            $myclientid = $myclient_id['id'];
            $info = M('property')->field('title,protection_time')->where(array('id'=>$datap['property']))->find();
            $title =  $info['title'];
            $str = 'id,pid,property,with_look,status,add_time,status_cid';
            $mproperty = M('myclient_property')->field($str)->where(array('property'=>$datap['property'],'pid'=>$myclientid))->order('id DESC')->find();
            if(empty($mproperty)){
                 $m_addtime['add_time'] = 0;
            }else{
                //获取带看时间
                $m_addtime = M('myclient_status')->field('add_time')->where(array('mpid'=>$mproperty['id'],'status'=>$mproperty['status']))->find();
            }
            //系统有效保护期
            if($datap['with_look'] ==1){
                $systems_time = C('pin_protection_time');
            }else{
                $systems_time = C('pin_delegate_time');
            }
            $protection_time = $m_addtime['add_time']+$systems_time*24*3600; 
            //开发商有效保护期
            $infoprotection_time = $info['protection_time']*24*3600;
            $update_time = 0;
            //已带看时触发信息
            if($mproperty['status']>=4){
                //获取带看时间
                $protection_time = 0;
                if($mproperty['status'] ==4){
                    $update_time = $m_addtime['add_time'];
                }else{
                    $infoprotection_time = $gettime +3600;
                }
            }
           if(($protection_time>$gettime || ($update_time+$infoprotection_time)>$gettime) && ($mproperty['status_cid'] !=0)){
	              if($datap['with_look'] ==1){
	              	     //带看申请失败
	                    //$daikanstr = '带看申请失败！<br>抱歉，该客户已被他人带看过'.$title.'了，目前尚处保护期，您无法重复带看。';
	                    $daikanstr = "提交失败，该客户已在该楼盘被其他经纪人推荐或委托，请更换楼盘或推荐其他客户";
	                }elseif($datap['with_look'] ==2){
	                    //委托带看失败
	                    //$daikanstr = '委托带看失败<br>抱歉，该用户已被他人委托过了，目前尚处保护期，您无法重复委托。';
	                	$daikanstr = "提交失败，该客户已在该楼盘被其他经纪人推荐或委托，请更换楼盘或推荐其他客户";
	                }
	                
	                $this->ajaxReturn(1003,$daikanstr);
	                
           }else{

                if($datap['with_look'] ==1){
                   //由我带看有效期
                    $datap['protection_time'] = C('pin_protection_time');
                    //带看申请成功
                    $daikanstr = '带看申请成功！<br>我们将在2小时内与开发商确认客户是否有效，您可以在个人中心-我的客户内查看客户状态。<br>请耐心等待……';
                }elseif($datap['with_look'] ==2){
                    //委托带看有效期
                    $datap['protection_time'] = C('pin_delegate_time');
                    //委托带看成功
                    $daikanstr = '委托带看成功<br>我们将在2小时内与开发商确认客户是否有效，您可以在个人中心-我的客户内查看客户状态。<br>请耐心等待……';
                }
                //判断用户是否存在
                if(!$myclient_id){
                    $last_id = M('myclient')->add($data);
                    $datap['pid'] =  $last_id;
                }else{
                    M('myclient')->where(array('mobile'=>$data['mobile']))->save(array('name' => $data['name']));
                    $datap['pid'] =  $myclientid;
                }
                $mpid = M('myclient_property')->add($datap);
                //向我的客户-附表 状态表录入信息
                $datasm['mpid']       =  $mpid;
                $datasm['pid']        = $datap['property'];
                $datasm['status']     =  1;
                $datasm['status_cid'] = 1;
                $datasm['with_look']  =  $datap['with_look'];
                $datasm['add_time']   =  $gettime;
                if($datap['with_look']==1){
                    $datasm['visit_time'] = strtotime($datas['visit_time']);
                }
                M('myclient_status')->add($datasm);
                $datap['mpid'] =  $mpid;
              //发送短信提醒****
              $mobile = M('user')->where('id ='.$datap['uid'])->getField('mobile');

              //是否请求接口
              $send_sms = D('send_sms');
              $result   = $send_sms->Messages($mobile,$agent_mobile,$agent_name,$client_name,$data['mobile'],$title,$str,$mobile_code,'1',false,'2',null);
              $this->chit($datap,$title,$data);
              $result ? $this->ajaxReturn(200,$daikanstr) : $this->ajaxReturn(1003,'Database operation failure');
            }

    }
    
    //报备成功后发送给案场负责人
    public function chit($arr,$title,$data)
    {
    	$case = M('case_field')->select();
    
    	foreach($case as $v)
    	{
    		if(in_array($arr['property'],explode(",",$v['sms_mobile'])))
    		{
    			$user = M('user')->where('id ='.$arr['uid'])->find();
    
    
    			if($arr['with_look'] ==1){
    				//带看申请成功
    				$str = "由我带看，到访时间".date('Y-m-d H:i',$arr['visit_time']);
    			}elseif($arr['with_look'] ==2){
    				$str = "委托带看，到访时间无";
    			}
    			$send_sms = D('send_sms');
    			$send_sms->Messages($v['mobile'],$user['mobile'],$user['username'],$data['name'],$data['mobile'],$title,$str,$mobile_code,'2',false,'2',null);
    			 
    			//给案场的人推送报备成功的消息
    			$app_push = D('app_push');
    			$arr_data=array();
    			$arr_data['muclient_name']=$data['name'];
    			$arr_data['muclient_mobile']=$data['mobile'];
    			$arr_data['agent_name']=$user['username'];
    			$arr_data['agent_mobile']=$user['mobile'];
    			$info="已有经纪人".$user['username']."，".$user['mobile']."于".date('Y-m-d H:i',time())."成功报备客户".$data['name']."，".$data['mobile']."至".$title."，报备性质：".$str."，请及时联系。" ;
    			$app_push->push_case($info,$v['admin_id'],$arr['mpid'],$arr['with_look'],$arr_data);

    		}
    	}
    }
    
    
    /**
     +----------------------------------------------------------
     * 品客帮
     +----------------------------------------------------------
     */
    public function pringles(){
    	$data=json_decode($_POST['params'],TRUE);
    	!isset($data['equipment_type']) && $this->ajaxReturn(51,'设备类型为空');
    	//$data['equipment_type']=1;
    	if($data['equipment_type']==1) $houzui='_ios';
    	if($data['equipment_type']==2) $houzui='_android';
    	$list = M('pringles')->field('id,img')
    	                     ->where('status=1 and cate_id=1')
    	                     ->order('add_time DESC')
    	                     ->select();
    	
    	foreach($list as $k => $v){
    		$list[$k]['img']=$this->path.attach(get_thumb($v['img'],$houzui), 'pringles');
    	}
    	//print_r($list);die();
    	$this->ajaxReturn(200,L('app_requre_normal'),$list);
    }
    /**
     +----------------------------------------------------------
     * 品客帮详情
     +----------------------------------------------------------
     */
    public function pringles_detail(){
    	$data=json_decode($_POST['params'],TRUE);
    	!isset($data['pid']) && $this->ajaxReturn(51,'文章id为空');
    	
       	$list = M('pringles')->field('id,title,author,info')
                             ->where('status=1 and cate_id=1 and id='.$data['pid'])
    	                     ->order('add_time DESC')
    	                     ->find();
       	$list['info']=str_replace('<img src="','<img src="'.$this->local_path,$list['info']);
    	$this->ajaxReturn(200,L('app_requre_normal'),$list);
    }    
     
  /**
    +----------------------------------------------------------
    * 个人中心首页
    +----------------------------------------------------------
   */
 public function personal_center() {
 	
 	    $data=json_decode($_POST['params'],TRUE);
		$user_info['id'] = $data['uid'];
		(!$data['uid']) && $this->ajaxReturn(51,'uid'.L('app_empt'));
		
		$uuid=$data['uuid'];
		(!$uuid) && $this->ajaxReturn(51,'uuid'.L('app_empt'));
         $this->check_uuid($uuid,$user_info['id']);

		$fph   = C('DB_PREFIX');
		$info = M('user')->field('id,username,avatar,city_id,mobile,address')->where(array('id'=>$user_info['id'], 'status'=>1))->find();
		$count = array();
		$count['kehu'] = $count['favorites'] = $count['share_id'];
		
		if(!empty($info['id'])){
			//我的客户
			$count['kehu'] = M('myclient')->where('uid ='.$info['id'])->count();
			//我的收藏
			$count['favorites'] = M('favorites')->where('uid ='.$info['id'])->count();
			//我的团队
			$count['team'] = M('user')->where('share_id ='.$info['id'])->count();
		}
		
		//最近推客
		 $reporte = M('myclient')->field('A.id,A.name,A.mobile,B.status,B.with_look,C.title')
		                           ->table("{$fph}myclient AS A JOIN {$fph}myclient_property AS B ON A.id=B.pid INNER JOIN {$fph}property AS C ON C.id=B.property")
		                           ->where('B.uid='.$user_info['id'])
		                           ->order('B.add_time DESC')
		                           ->limit(0,1)
		                           ->select();
		$info['reporte']=$reporte[0];
		
		//计算佣金 已结佣
		$money_user = M('myclient')->field('B.buy_product,B.status,B.with_look')
	                    	       ->table("{$fph}myclient AS A INNER JOIN {$fph}myclient_property AS B ON A.id=B.pid")
		                           ->where('A.uid='.$user_info['id'].' AND B.status = 5')
		                           ->order('B.add_time DESC')
		                           ->select();
		$array = array();
		$count['yijie'] = 0;
		foreach($money_user as $k=>$v){
			$property_money = M('hezuo_property_product')->field('b.yid,b.tiaodian,b.total_price,b.share_price,b.set_num,b.tiaodian_price,b.set_num2,b.tiaodian_price2')
			                                             ->table("{$fph}hezuo_property_product AS a INNER JOIN {$fph}hezuo_yongjin AS b ON a.id=b.cid")
			                                             ->where('a.id='.$v['buy_product'].' AND b.total_price != 0')
			                                             ->find();
			
			//佣金计算 如果委托带看 佣金减去 信息分成
			if($v['with_look'] == 2){
			    $array['money'] = $property_money['total_price'] - $property_money['share_price'];
			}else{
			    $array['money'] = $property_money['total_price'];
			}
			
			//获取当先经纪人下 总成交总数
			//跳点 金额
			$tiaodian_money = 0;
			//判断如果 如果不是无跳点进行 跳点金额计算
			if($property_money['tiaodian'] != 3){
				$mycount = M('myclient_property')->table("{$fph}myclient_property AS a INNER JOIN {$fph}myclient AS b ON a.pid=b.id")
				                                 ->where("a.status = 5 AND a.buy_product ='".$property_money['id']."' AND b.uid =".$user_info['id']."")->count();
				
				//判断如果是全盘跳点 进行父id查询
				if($property_money['tiaodian'] == 1){
					$hezuo_money_yid = M('hezuo_yongjin')->field('set_num,set_num2,tiaodian_price,tiaodian_price2')->where('id ='.$property_money['yid'])->find();
					$property_money['set_num'] = $hezuo_money_yid['set_num'];
					$property_money['set_num2'] = $hezuo_money_yid['set_num2'];
					$property_money['tiaodian_price'] = $hezuo_money_yid['tiaodian_price'];
					$property_money['tiaodian_price2'] = $hezuo_money_yid['tiaodian_price2'];
				}
				
				//判断跳点 进行金额计算
				if($mycount > $property_money['set_num'] AND $mycount < $property_money['set_num2']){
					$tiaodian_money = $property_money['tiaodian_price'];
				}else if($mycount > $property_money['set_num2']){
					$tiaodian_money = $property_money['tiaodian_price2'];
				}
		    }
			
			$array['money'] = $array['money'] + $tiaodian_money;
			//计算已结 佣金
			if($v['status'] == 5){
				$count['yijie'] = $count['yijie'] + $array['money'];
			}
			
		}
		
		$info['head_picture']=$this->path.attach(get_thumb($info['avatar'], '_100'), 'avatar');;

		$info=$info+$count;//合并数组
	   //print_r($info);die();
		$this->ajaxReturn(200,L('app_req;ure_normal'),$info);

    }
     
     /**
    +----------------------------------------------------------
    * 个人修改资料
    +----------------------------------------------------------
   */
     public function personal_detail(){
     	
     	$data=json_decode($_POST['params'],TRUE);
     	$user_info['id'] = $data['uid'];
     	(!$data['uid']) && $this->ajaxReturn(51,'uid'.L('app_empt'));
     	
     	$uuid=$data['uuid'];
     	(!$uuid) && $this->ajaxReturn(51,'uuid'.L('app_empt'));
     	$this->check_uuid($uuid,$user_info['id']);
     	
     	$info = M('user')->field('id,username,mobile,gender,city_id,property_cate_id,share_id')->where(array('id'=>$user_info['id'],'status'=>1))->find();
     	!$info && $this->ajaxReturn(1002,'无用户');
     	
     	if($info['property_cate_id']){ //所擅长的物业类型
     		$property_cate_arr = M('property_cate')->field('id,name')->where("id IN (".$info['property_cate_id'].")")->select();
     	}
     	$info['property_cate_id']=$property_cate_arr;
     	
		//print_r($info);
		$this->ajaxReturn(200,L('app_requre_normal'),$info);
   
     }
     

     /**
      +----------------------------------------------------------
      * 擅长物业类型
      +----------------------------------------------------------
      */
     
     public function  property(){
     	
     	$property_cate = M('property_cate')->field('id,name')->where(array('pid'=>1,'status'=>1))->select();
     	
     	//print_r($property_cate);
     	$this->ajaxReturn(200,L('app_requre_normal'),$property_cate);
     }
     
     
     /**
      +----------------------------------------------------------
      * 个人资料的修改
      +----------------------------------------------------------
      */
      
     public function  personal_editor(){

     	$data=json_decode($_POST['params'],TRUE);
     	$user_info['id'] = $data['uid'];
     	(!$data['uid']) && $this->ajaxReturn(51,'uid'.L('app_empt'));
     
     	$uuid=$data['uuid'];
     	(!$uuid) && $this->ajaxReturn(51,'uuid'.L('app_empt'));
     	$this->check_uuid($uuid,$user_info['id']);

     	
        if($data['username']){
        	$add_data['username']=$data['username'];
        	M('user')->where('id='.$user_info['id'])->save($add_data);
        }
        if(isset($data['gender'])){
        	$add_data['gender']=$data['gender'];
        	M('user')->where('id='.$user_info['id'])->save($add_data);
        }
        if($data['mobile']){
        	$add_data['mobile']=$data['mobile'];
        	M('user')->where('id='.$user_info['id'])->save($add_data);
        }
        if($data['city_id']){
        	$add_data['city_id']=$data['city_id'];
        	M('user')->where('id='.$user_info['id'])->save($add_data);
        }
        if($data['address']){
        	$add_data['address']=$data['address'];
        	M('user')->where('id='.$user_info['id'])->save($add_data);
        }
        if($data['property_cate_id']){
        	$add_data['property_cate_id']=$data['property_cate_id'];
        	M('user')->where('id='.$user_info['id'])->save($add_data);
        }                                            
        $base64=$this->_post('image','trim');
        //$base64=L('app_base64');
        if($base64) {
        	    $datap=base64_decode($base64);
        	    (!$datap) && $this->ajaxReturn(51,'base64转码'.L('app_empt'));
				
				$head_picture='data/upload/avatar/temp/'.md5($uid).$rand.'.jpg';
				$thumbWidth   = array('64','100');
				$thumbHeight  = array('64','100');
				$thumbSuffix  = array('_64x64','_100x100');
        		//$head_picture64='data/upload/avatar/'.$avatar_dir.md5($uid).$rand.'_64.jpg';
        		file_put_contents($head_picture,$datap);//将得到的二进制码写入图片文件中
				$fdfs_obj = new FastFile();
				$tracker  = fastdfs_tracker_get_connection();
				if(!fastdfs_active_test($tracker)){
					error_log("errno: " . fastdfs_get_last_error_no() . ", error info: " . fastdfs_get_last_error_info());
					exit(1);
				}
				$storage = fastdfs_tracker_query_storage_store();
					if(!$storage){
					error_log("errno: " . fastdfs_get_last_error_no() . ", error info: " . fastdfs_get_last_error_info());
					exit(1);
				}
				
				//删除原图
				$old_img = M('user')->where(array('id'=>$user_info['id']))->getField('avatar');
				if($old_img){
					$fdfs_obj->fast_del_img($old_img);
					$img_exp = explode('.',$old_img);
					foreach($thumbSuffix as $k=>$v){
						$img_thumb = $img_exp[0].$v.'.'.$img_exp[1];
						$fdfs_obj->fast_del_img($img_thumb);
					}
				}
				
				//先默认一个原图
				$res = fastdfs_storage_upload_by_filename($head_picture, null, array(), null, $tracker, $storage);
				
				$format      = explode('.',$head_picture);//文件格式后缀 *.jpg
				$info        = $fdfs_obj->getImageInfo($head_picture);//文件信息
				//缩略图
				foreach($thumbWidth as $key=>$val){
					$fdfs_obj->_render_thumbnail(file_get_contents($head_picture),$info,$thumbWidth[$key],$thumbHeight[$key],$res['group_name'],$res['filename'],$thumbSuffix[$key],end($format));
				}
				$img_gs = $res['group_name'].'/'.$res['filename'];
				
				M('user')->where('id='.$user_info['id'])->data(array('avatar'=>$img_gs))->save();
				unlink($head_picture);
				
				$img_64 = str_replace('.','_64x64.',$img_gs);
				$this->ajaxReturn(200, L('upload_success'), C('img_url').$img_64);
				
        		/*//会员头像规格
        		$avatar_size = explode(',', C('pin_avatar_size'));
        		//回去会员头像保存文件夹
        		$uid = abs(intval($user_info['id']));
        		$suid = sprintf("%09d", $uid);
        		$dir1 = substr($suid, 0, 3);
        		$dir2 = substr($suid, 3, 2);
        		$dir3 = substr($suid, 5, 2);
        		$avatar_dir = $dir1.'/'.$dir2.'/'.$dir3.'/';
        		$rand = mt_rand(1,999);
        		$head_picture_path='data/upload/avatar/'.$avatar_dir;  
        		if (!is_dir($head_picture_path)){  //新用户创建目录
        			mkdir($head_picture_path);
        		}

        		$head_picture='data/upload/avatar/'.$avatar_dir.md5($uid).$rand.'_100.jpg';
        		$head_picture64='data/upload/avatar/'.$avatar_dir.md5($uid).$rand.'_64.jpg';
        		file_put_contents($head_picture,$datap);//将得到的二进制码写入图片文件中
        		
        		$src=imagecreatefromjpeg($head_picture);//图片的等比缩放
        		$size_src=getimagesize($head_picture);
        		$w=$size_src['0'];
        		$h=$size_src['1'];
        		$max=100;
        		$min=64;
        		if($w > $h){
        			$max_w=$max;
        			$max_h=$h*($max/$size_src['0']);
        			
        			$min_w=$min;
        			$min_h=$h*($min/$size_src['0']);
        		}else{
        			$max_h=$max;
        			$max_w=$w*($max/$size_src['1']);
        			
        			$min_h=$min;
        			$min_w=$w*($min/$size_src['1']);
        		}
        		$image_max=imagecreatetruecolor($max_w, $max_h);
        		$image_min=imagecreatetruecolor($min_w, $min_h);
        		imagecopyresampled($image_max, $src, 0, 0, 0, 0, $max_w, $max_h, $size_src['0'], $size_src['1']);
        		imagecopyresampled($image_min, $src, 0, 0, 0, 0, $min_w, $min_h, $size_src['0'], $size_src['1']);
        		imagepng($image_max,$head_picture);
        		imagepng($image_min,$head_picture64);
        		imagedestroy($image_max);
        		imagedestroy($image_min);
        		imagedestroy($src);//销毁资源
        		
        		M('user')->where(array('id'=>$user_info['id']))->save(array('avatar'=>$avatar_dir.md5($uid).$rand.'.jpg'));
        		
        		$image_address['head_picture']=$this->local_path.'/'.$head_picture;  //print_r($image_address);
                $this->ajaxReturn(200, L('upload_success'), $image_address);*/
       }
        	
    	$this->ajaxReturn(200,L('app_requre_normal'),'修改成功');
        
     }
     
     
  /**
    +----------------------------------------------------------
    * 我的客户
    +----------------------------------------------------------
  */
public  function my_client(){
	
	$data=json_decode($_POST['params'],TRUE);
	$user_info['id'] = $data['uid'];
	(!$data['uid']) && $this->ajaxReturn(51,'uid'.L('app_empt'));
	
	$uuid=$data['uuid'];
	(!$uuid) && $this->ajaxReturn(51,'uuid'.L('app_empt'));
	$this->check_uuid($uuid,$user_info['id']);
	
	$with_look=$data['type'];
	
	(!$with_look) && $this->ajaxReturn(51,'type'.L('app_empt'));

	!isset($data['page']) && $this->ajaxReturn(51,'页号不能为空');
	!$data['number_each_page'] && $this->ajaxReturn(51,'页条数不能为空');
	
	$page = $data['page'];
	$number_each_page=$data['number_each_page'];
	$start = $page*$number_each_page;
	
	$fph = C('DB_PREFIX');
	$list = M('myclient_property')->field('A.id,A.name,B.with_look')
	                             ->table("{$fph}myclient AS A INNER JOIN {$fph}myclient_property AS B ON A.id = B.pid")
	                             ->where("B.with_look = ".$with_look." AND B.uid = ".$user_info['id'])
	                             ->group('B.pid')
	                             ->order('add_time DESC')
	                             ->limit($start,$number_each_page)
	                             ->select();
	
	foreach ($list as $key => $value) {
		$list[$key]['property'] = M('myclient_property')->field('property,status,with_look')->where('uid = '.$user_info['id'].' AND with_look = '.$with_look.' AND pid='.$value['id'])->select();
		foreach ($list[$key]['property'] as $k => $v) {
			$list[$key]['property'][$k]['title'] = M('property')->where('id='.$v['property'])->getField('title');
		 /*if($v['status'] == 6 AND $v['status_cid'] == 0)
			{
				$list[$key]['my_p'][$k]['zhongzhi'] = M('myclient_status')->where('status = 5 AND mpid ='.$v['id'])->getfield('status_cid');
			}*/
		}
	}
	
	$mycount =count(M('myclient_property')->where('uid ='.$user_info['id'])->group('pid')->select());//累计客户
	$chengjiao = count(M('myclient_property')->where("status = 7 AND uid = ".$user_info['id'])->group('pid')->select());//累计成交
	
	$list ? $list : $list=array();
	
	$info['item']=$list;
   	$info['client_sum']=$mycount;
	$info['deal_sum']=$chengjiao;
	
	//print_r($info);
	
	$this->ajaxReturn(200,L('app_requre_normal'),$info);
}

  /**
    +----------------------------------------------------------
    * 我的客户详情
    +----------------------------------------------------------
  */

  public function client_detail(){

	  	$data=json_decode($_POST['params'],TRUE);
	  	$user_info['id'] = $data['uid'];
	  	(!$data['uid']) && $this->ajaxReturn(51,'uid'.L('app_empt'));
	  	
	  	$uuid=$data['uuid'];
	  	(!$uuid) && $this->ajaxReturn(51,'uuid'.L('app_empt'));
	  	$this->check_uuid($uuid,$user_info['id']);

	  	$with_look=$data['type'];
	  	(!$with_look) && $this->ajaxReturn(51,'type'.L('app_empt'));
	  	

  		$fph = C('DB_PREFIX');
  		$id = $data['cid'];
  		 (!$id) && $this->ajaxReturn(51,'cid'.L('app_empt'));
  		
    		//我的客户 根据 客户id查询 显示一行
  		$row = M('myclient')->field('id,name,mobile,gender')->where('id='.$id)->find();
  		
  		$row['property'] = M('myclient_property')->field('a.id as pid,a.status,a.with_look,c.id,c.title,c.list_price as commission')
  	                                        	 ->table("{$fph}myclient_property AS a INNER JOIN {$fph}property AS c ON c.id=a.property")
  		                                         ->where("a.uid=".$user_info['id']." and a.pid=".$row['id']." and a.with_look=".$with_look)
			                                     ->select();
  		
  		foreach($row['property'] as $k => $v){
  			$row['property'][$k]['update_time'] = date('Y-m-d', M('myclient_status')->where('status = '.$v['status'].' AND mpid ='.$v['pid'])->getfield('add_time'));
  			$row['property'][$k]['commission'] = $row['property'][$k]['commission'] ? $row['property'][$k]['commission'] : '';
  		}
  		
  		$row['kefu_tel']=C('pin_kefu_tel');
  		//print_r($row);die(); 

  		$this->ajaxReturn(200,L('app_requre_normal'),$row);
      }
      

   /**
    +----------------------------------------------------------
    * 我的楼盘
    +----------------------------------------------------------
    */
      
public function my_building(){
   	$data=json_decode($_POST['params'],TRUE);
   	
   	$user_info['id'] = $data['uid'];
   	(!$data['uid']) && $this->ajaxReturn(51,'uid'.L('app_empt'));
   	
   	
   	!isset($data['page']) && $this->ajaxReturn(51,'页号不能为空');
   	!$data['number_each_page'] && $this->ajaxReturn(51,'页条数不能为空');
   	
   	$uuid=$data['uuid'];
   	(!$uuid) && $this->ajaxReturn(51,'uuid'.L('app_empt'));
   	$this->check_uuid($uuid,$user_info['id']);
   	 
   	
   	$page = $data['page'];
   	$number_each_page=$data['number_each_page'];
   	$start = $page*$number_each_page;
   	
   	$fph = C('DB_PREFIX');
   	$list = M('favorites')->field('B.id,B.title,B.address as city,B.img_thumb as img,B.item_price,B.list_price as commission')
   	                     ->table("{$fph}favorites AS A INNER JOIN {$fph}property AS B ON A.pid=B.id")
   	                     ->where('A.uid ='.$user_info['id'])
   	                     ->order('A.add_time DESC')
   	                     ->limit($start,$number_each_page)
   			             ->select();
   	
   	foreach($list as $k => $v){
   		$list[$k]['img']=$this->path.attach(get_thumb($v['img'], '_app_thumb'), 'property/thumbnail');
   	}
   	
    // print_r($list);
   	$this->ajaxReturn(200,L('app_requre_normal'),$list);
   	
   }
     
    /**
    +----------------------------------------------------------
    * 我的团队
    +----------------------------------------------------------
    */
      
   public function my_team(){
   	$data=json_decode($_POST['params'],TRUE);
   	
   	$user_info['id'] = $data['uid'];
   	(!$data['uid']) && $this->ajaxReturn(51,'uid'.L('app_empt'));
   	
   	!isset($data['page']) && $this->ajaxReturn(51,'页号不能为空');
   	!$data['number_each_page'] && $this->ajaxReturn(51,'页条数不能为空');
   	
   	$uuid=$data['uuid'];
   	(!$uuid) && $this->ajaxReturn(51,'uuid'.L('app_empt'));
   	$this->check_uuid($uuid,$user_info['id']);
   	 	
   	$page = $data['page'];
   	$number_each_page=$data['number_each_page'];
   	$start = $page*$number_each_page;
   
   	$fph = C('DB_PREFIX');

    $list['count'] = M('user')->where('share_id ='.$user_info['id'])->count('id');//总成员
    $list['list'] = M('user')->field('id,username,mobile')->where('share_id ='.$user_info['id'])->limit($start,$number_each_page)->select();
	foreach($list['list'] as $k => $v){
			$list['list'][$k]['status'] =  M('myclient_property')->where('status = 7 AND uid ='.$v['id'])->count('id');
	}
      //print_r($list);
   	$this->ajaxReturn(200,L('app_requre_normal'),$list);
   	
   }
      
   

       /**
    +----------------------------------------------------------
    * 我的佣金获取方法
    * uid 用户 id
    * status 状态 1 为累计佣金 2 为已结佣 3 为未结佣
    +----------------------------------------------------------
    */ 
    public function my_money(){
    	
    	$data=json_decode($_POST['params'],TRUE);
    	$uid= $data['uid'];
    	(!$data['uid']) && $this->ajaxReturn(51,'uid'.L('app_empt'));
    	 
    	$uuid=$data['uuid'];
    	(!$uuid) && $this->ajaxReturn(51,'uuid'.L('app_empt'));
    	$this->check_uuid($uuid,$uid);
    	
    	$status=$data['type'];
    	(!$status) && $this->ajaxReturn(51,'type'.L('app_empt'));
    	
    	
    	$fph = C('DB_PREFIX');
    	$array = array();

    	$myclient = M('myclient')->table("{$fph}myclient AS a
						INNER JOIN {$fph}myclient_property AS b ON a.id=b.pid")
						->where('a.uid='.$uid.' AND b.status IN(4,5)')
						->order('b.add_time DESC')->select();	
    	
    	
    	if(!empty($myclient))
    	{
    		$array['yijie'] = $array['weijie'] = $array['leiji'] = 0;
    		foreach ($myclient as $key => $value) {
    			$rule = M('hezuo_property_product')
						->field('a.id,a.name,a.pid,b.yid,b.tiaodian,b.total_price,b.share_price,b.set_num,b.tiaodian_price,b.set_num2,b.tiaodian_price2')
						->table("{$fph}hezuo_property_product AS a
								INNER JOIN {$fph}hezuo_yongjin AS b
								ON a.id=b.cid")
						->where('a.id='.$value['buy_product'].' AND b.total_price != 0')->find();
				//用户名称
				$array['info'][$key]['name'] = $value['name'];
				//用户手机
				$array['info'][$key]['mobile'] = $value['mobile'];
				//用户性别
				$array['info'][$key]['gender'] = $value['gender'];
				//楼盘产品名称
				$array['info'][$key]['cname'] = $rule['name'];
				//状态
				$array['info'][$key]['status'] = $value['status'];
				//时间
				$array['info'][$key]['add_time'] =date('Y-m-d',$value['add_time']) ;
				//楼盘名称
				$array['info'][$key]['title'] = M('property')->where('id ='.$rule['pid'])->getField('title');		

				if($value['with_look'] == 2)
					$array['info'][$key]['money'] = $rule['total_price'] - $rule['share_price'];
				else
					$array['info'][$key]['money'] = $rule['total_price'];

				if($rule['tiaodian'] != 3)
				{
					$jumppoint =  M('myclient')->table("{$fph}myclient AS a
						INNER JOIN {$fph}myclient_property AS b ON a.id=b.pid")
						->where('a.uid='.$uid.' AND b.buy_product ='.$value['buy_product'].' AND b.status = 5')->count('id');
					if($rule['tiaodian'] == 1)
					{
						$hezuo_money_yid = M('hezuo_yongjin')->where('id ='.$rule['yid'])->find();
						$rule['set_num'] = $hezuo_money_yid['set_num'];
						$rule['set_num2'] = $hezuo_money_yid['set_num2'];
						$rule['tiaodian_price'] = $hezuo_money_yid['tiaodian_price'];
						$rule['tiaodian_price2'] = $hezuo_money_yid['tiaodian_price2'];
					}

					if($jumppoint > $rule['set_num'] AND $jumppoint < $rule['set_num2'])
					{
						$tiaodian_money = $rule['tiaodian_price'];
					}
					else if($jumppoint > $rule['set_num2'])
					{
						$tiaodian_money = $rule['tiaodian_price2'];
					}
					$array['info'][$key]['money'] = $array['info'][$key]['money'] + $tiaodian_money;
				}

				//计算已结 佣金
				if($value['status'] == 5)
				{
					$array['yijie'] = $array['yijie'] + $array['info'][$key]['money'];
				}
				//计算 未结 佣金
				if($value['status'] == 4)
				{
					$array['weijie'] = $array['weijie'] + $array['info'][$key]['money'];
				}
    		}
    		$array['leiji'] = $array['yijie'] + $array['weijie'];

    		$unset = 0;
    		if($status == 2)
    			$unset = 4;
    		if($status == 3)
    			$unset = 5;
    		if($unset != 0)
    		{
    			foreach ($array['info'] as $key => $value) {
    				if($value['status'] == $unset)
    				{
    					unset($array['info'][$key]);
    				}
    			}
    			$array['info']=array_values($array['info']);//从新建立数字索引
    		}
    	}else{
    		$array=Array(
    				'leiji' =>'',
    				'weijie' =>'',
    				'yijie' =>'',
    				'info' =>Array()
    	          );
    	}
    	 $array['kefu_tel']=C('pin_kefu_tel');
    	 
    	 //print_r($array);
    	 
    	 $this->ajaxReturn(200,L('app_requre_normal'),$array);
    	
    } 
      
    /**
     +----------------------------------------------------------
     * 登录uuid检验
     +----------------------------------------------------------
     */
    public function  agreement(){
    	 
    	$array['agreement']=C('pin_reg_protocol');
    	//print_r($array['agreement']);
    	$this->ajaxReturn(200,L('app_requre_normal'),$array);
    }
    
    /**
     +----------------------------------------------------------
     * 选择城市  客服电话
     +----------------------------------------------------------
     */
    
    public function  select_city(){
    
    	$cityList = D('city')->get_proprerty_city();

    	$cityList[] = array('id'=>'10000','name'=>'全部');
    	$info['city'] = $cityList;

    	$info['kefu_tel']=C('pin_kefu_tel');
    
    	//print_r($info);die();
    
    	$this->ajaxReturn(200,L('app_requre_normal'),$info);
    
    }
    
    
    /**
     +----------------------------------------------------------
     * 推送消息
     +----------------------------------------------------------
     */
    public function Push_message (){
    	//$data=json_decode($_POST['params'],TRUE);
    	 
    	//Vendor("Push.notification");
    	Vendor("Push.Demo");
    	 
    	$demo = new Demo("54373b9efd98c5ac390125ec", "0pjknuyuvwxvegfzadakonaidsv26ico");
    	$demo->sendAndroidUnicast();
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
	
 }