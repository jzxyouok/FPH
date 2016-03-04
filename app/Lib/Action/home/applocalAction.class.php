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

class ApplocalAction extends baseAction {
		
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
	    $_POST['token']!=md5('home_app'.date('Y-m-d',time()).'#$@%!*') && $this->ajaxReturn(51,L('app_token'));    
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
	      $booluser = M('user')->where("mobile='".$data['mobile']."' ")->count('id');
	      if(!empty($booluser))
		      $this->ajaxReturn(1004, L('app_username_exist'));
	  	
	      !$data['mobile_code'] && $this->ajaxReturn(51,L('app_mobile_code_empty'));
	      
	      !$data['password'] && $this->ajaxReturn(51,L('app_password_empty'));
	      strlen($data['password'])<6 && $this->ajaxReturn(1006,L('app_password_format'));
	      //手机号码归属地
          $city_name = get_city($data['mobile']);
          $city_id = M('city')->where("name='".$city_name."'")->getfield('id');
          if(!$city_id) $city_id=0;
	      $data['reg_time']    = time();
	      $data['last_time']   = time();
	      $data['city_id']     = $city_id;
	      $data['password']    = md5($data['password']);
	      $data['gender']      = 2;
	      $data['origin']      = $data['equipment_type']+1;
	      
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
			if($uid){
				$data['type'] = 4;
			}else{
				$data['type'] = 1;
			}
			// if($data['type']=='1'){  //1注册2忘记密码
			// 	$uid &&  $this->ajaxReturn(1004, L('app_mobile_exist'));
			// }
			// if($data['type']=='2'){
			// 	!$uid &&  $this->ajaxReturn(1002,'用户不存在');	
			// }
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
			// if($data['mobile'] != $_SESSION['mobile']){
			// 	$this->ajaxReturn(51,'手机号输入错误');
			// }
			!checkMobile($data['mobile']) && $this->ajaxReturn(51,L('app_mobile_format'));
			
	        //!$data['password'] && $this->ajaxReturn(51,L('app_password_empty'));
	        //strlen($data['password'])<6 && $this->ajaxReturn(51,L('app_password_format'));
				
			$user = M('user')->field('id,username')->where(array('mobile'=>$data['mobile'], 'status'=>1))->find();
			if (!$user) {
				//$this->ajaxReturn(1002,L('user_not_exist'));
				$data_add['reg_time']    = time();
				$data_add['last_time']   = time();
				$data_add['gender']      = 2;
				$data_add['origin']      = $data['equipment_type'];//1:微信 2:IOS 3:Android 4:PC
				$data_add['mobile']      = $data['mobile'];
				$add_uid=M('user')->add($data_add);
				$user = M('user')->field('id,username')->where(array('id'=>$add_uid, 'status'=>1))->find();
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
    * 修改手机号码 lishun 2015-05-11
    +----------------------------------------------------------
   */
	function  save_mobile(){
	    $data=json_decode($_POST['params'],TRUE);
		!$data['mobile'] && $this->ajaxReturn(51,L('app_mobile_empty'));
		!checkMobile($data['mobile']) && $this->ajaxReturn(51,L('app_mobile_format'));
		!$data['uid'] && $this->ajaxReturn(51,'参数有误');
		!$data['mobile_code'] && $this->ajaxReturn(51,L('app_mobile_code_empty'));
		$user = M('user')->field('id,username')->where(array('id'=>$data['uid'],'status'=>1))->find();
		if (!$user) {
			$this->ajaxReturn(1002,L('user_not_exist'));
		}
		$last_id = M('user')->where(array('id'=>$data['uid']))->save(array('last_time'=>time(),'mobile'=>$data['mobile']));//原表记录一条登录记录
		$res_data['uuid']=sprintf( '%04x%04x%04x%04x%04x%04x%04x%04x',
					mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
					mt_rand(0, 0x0fff) | 0x4000,
					mt_rand(0, 0x3fff) | 0x8000,
					mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));//设置唯一的标示
		$total_data['uuid']=$res_data['uuid'];
		$total_data['id']=$data['uid'];
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
				M('app_login')->add($res_data) ? $this->ajaxReturn(200,L('login_successe'),$total_data) : $this->ajaxReturn(1003,'操作数据库无效');
			}
		// if($last_id){
		// 	$this->ajaxReturn(200,'修改成功',$total_data);
		// }else{
		// 	$this->ajaxReturn(51,'修改失败');
		// }
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
				//$temp =  "select id from {$fph}city where id in(".$area.") or spid LIKE '%".$area."%' ";
				$where = "and ".$time.">B.term_start and ".$time." < B.term_end ";
				 $where .=' AND A.city_id in(select id from fph_city where id = '.$area.' or spid RLIKE "[[:<:]]'.$area.'[[:>:]]")';
		      }else {
				 $where = "and ".$time.">B.term_start and ".$time." < B.term_end ";
		      }
			      
				    
		      //$this->ajaxReturn(200,L('app_requre_normal'),$area,$where);
		      
		      $page = $data['page'];
		      $number_each_page=$data['number_each_page'];
		      $start = $page*$number_each_page;
		      
		      
		      //楼盘显示
		      $list = M('property')->field('A.id,A.title,A.prefer,A.img_thumb as img,A.list_price as commission,A.item_price,A.city_id,A.add_time,A.property_type')
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


				    //带看奖标签 是否有带看奖
					$prize = M('property_prize')->field('id,pid,prize,stores_id')->where('pid='.$v['id'].' AND stores_id="" AND time_start<'.$time.' AND time_end > '.$time)->order('id DESC')->find();
					$list[$k]['prize'] = $prize['prize'];
					 if($data['uid']){
					 	$prize = M('property_prize')->field('id,pid,prize,stores_id')->where('stores_id != "" AND pid='.$v['id'].' AND time_start<'.$time.' AND time_end > '.$time)->order('id DESC')->find();
					 	if($prize['id']){
					 		$stores_id = M('user')->where('id='.$data['uid'].' AND stores_id in('.$prize['stores_id'].')')->getField('stores_id');
					 		if($stores_id){
					 			$list[$k]['prize'] = $prize['prize'];
					 		}
					 	}
					}

					//独家标签 是否合作

					/*$bool = M('property_cooperation')->where('term_start < "'.$time.'" AND term_end > "'.$time.'" AND pid ='.$v['id'])->find();
					$list[$k]['pid_hz'] =1;
					if(empty($bool)){
						$list[$k]['pid_hz'] = 0;
					}*/
				  //$data['uid'] = 103;
				  if(empty($data['uid'])){
				  		$list[$k]['pid_hz'] = 0;
				  }else{
				  		$pid = $v['id'];
			      //获取uid对应的storeid	user信息表
			      $stores_idby_uid = M('user')->where('id = '.$data['uid'])->getfield('stores_id');
			      //根据楼盘id从commission表中获取适用的物业类型（property_type）以及stores_id
			      $ptype_storesid = M('property_commission')->where('pid ='.$pid)->field('stores_id,property_type')->select();
			      foreach($ptype_storesid as $kk => $vv){
			      			if(!empty($ptype_storesid[$kk]['stores_id'])){
			      				$ptype_storesid[$kk]['stores_id'] = explode(',', $vv['stores_id']);
			      				$ptype_storesid[$kk]['property_type'] = explode(',', $vv['property_type']);		      				
			      			}else{
								unset($ptype_storesid[$kk]);
								$list[$k]['pid_hz'] = 0;		      				
			      			}	      			
			      			
			      }
			      $list[$k]['ptype_storesid'] = $ptype_storesid;
			      //遍历查找uid对应的stores_id	;    
			      foreach($list[$k]['ptype_storesid'] as $k1 => $v1){
			      		if(in_array($stores_idby_uid, $v1['stores_id'])){
			      			 $flag1 = 1;			      			
			      		}	
			      		$list[$k]['property_type'] = explode(',', $list[$k]['property_type']);		      		
			      		foreach ($list[$k]['property_type'] as $key => $value) {
			      			if(in_array($value, $v1['property_type'])){
			      				$flag2 = 1;
			      			}
			      		}

			      	    if($flag1 ==1 && $flag2 ==1){
			      	    			$list[$k]['pid_hz'] = 1;			      	    	
			      	    }else{
			      	    			$list[$k]['pid_hz'] = 0;	
			      	    }	
			      	     $flag1 = $flag2 = 0;	

			     	 }	

				  }
		      }

		      $list=array_values($list);
		      $list ? $list : $list=array();
		      
			 //print_r($list);
		      
		    $this->ajaxReturn(200,L('app_requre_normal'),$list);
	}
	
	
  /**
    +----------------------------------------------------------
    * 合作楼盘详情 lishun 2015-05-08
    +----------------------------------------------------------
   */
function  cooperation_building_detail(){
		
		$data = json_decode($_POST['params'],TRUE);    
	 
		$id   = $data['pid'];
		//$id=367;
        !$id && $this->ajaxReturn(51,L('app_requre'));
        $time = time();
        //A.sub_title 及后 新加
        $str_list='A.id,A.title,A.bus as traffic,A.open_time,A.check_time,A.info,A.property_costs,A.sales_address,A.property_type as leixing,A.business,A.item_price,A.prefer,A.feature,A.latitude,A.address as weizhi,A.list_price as commission,A.metro,A.elevated,A.sub_title,A.property_feature,A.property_type,A.tel,A.commission_info,A.report_info,A.project,A.sales,A.payment,A.property_age,A.decoration,A.volume_rate,A.green_rate,A.gouseholds,A.floors,A.progress,A.propert_company,A.parking,A.parking_ratio,A.city_id,A.building_type';
        $fph = C('DB_PREFIX');
		$list = M('property')->field($str_list)
		                     ->table("{$fph}property AS A INNER JOIN {$fph}property_cate AS B ON A.property_type = B.id")
		                     ->where(array('A.id'=>$id))->find();
		//分享链接
		$cityinfo = M('city')->field('id,name,spid')->where('id='.$list['city_id'])->find();
		$arr_c = explode('|', $cityinfo['spid']);
		if($cityinfo['spid'] && $arr_c[1]){
			$city_name =  M('city')->where('id='.$arr_c[1])->getField('name');
			$select_city = $arr_c[1];
		}else{
			$city_name =  M('city')->where('id='.$cityinfo['id'])->getField('name');
			$select_city = $cityinfo['id'];
		}
		$list['fx_url'] = "http://www.fangpinhui.com/?g=weixin&m=loupan&a=detail&id=".$list['id']."&select_city=".$select_city."&select_name=".$city_name."&origin=app";
		//
		//楼盘特色
	    $list['property_feature_name'] = '';
		if(!empty($list['property_feature'])){
		    $property_type = M('property_cate')->field('id,name')->where('id in('.$list['property_feature'].')')->select();
		    foreach($property_type as $k=>$v){
				$list['property_feature_name'] .= $v['name'].'、';
		    }
		    $list['property_feature_name'] = substr($list['property_feature_name'],0,-3);
		}
		//建筑类型
	    $list['building_type_name'] = '';
		if(!empty($list['building_type'])){
		    $building_type = M('property_cate')->field('id,name')->where('id in('.$list['building_type'].')')->select();
		    foreach($building_type as $k=>$v){
				$list['building_type_name'] .= $v['name'].'、';
		    }
		    $list['building_type_name'] = substr($list['building_type_name'],0,-3);
		}
		//渠道佣金 适用物业 规则 uid
		$yj_where = 'pid ='.$id;
		$l_stores_id = M('property_commission')->where("stores_id !='' AND pid =".$id)->order('id DESC')->getField('stores_id');
		if($data['uid'] && $l_stores_id){
			$u_count = M('user')->where("id=".$data['uid']." AND stores_id in(".$l_stores_id.")")->count('id');
		}
		if(!$u_count){
			$yj_where .= ' AND stores_id=""'; 
		}
		$commission = M('property_commission')->field('id,property_type,price')->where($yj_where)->order('add_time DESC')->select();
		foreach ($commission as $key => $value) {
			$catearr = M('property_cate')->field('name')->where('id in('.$value['property_type'].')')->select();
		    $commission[$key]['cate'] = '';
		    foreach ($catearr as $k => $v) {
		       $commission[$key]['cate'] .= $v['name'].',';
		    }
		    $commission[$key]['cate'] = substr($commission[$key]['cate'],0,-1);
		}
		$list['yj_commission']=$commission;

		//销售状态  0未知 1在售 2待售 3售罄
		switch ($list['sales']){
			case 1:
			  $sales_status = '在售';
			  break;
			case 2:
			  $sales_status = '待售';
			  break;
			case 3:
			  $sales_status = '售罄';
			  break;
			default:
			  $sales_status = '未知';
		}
		$list['sales_status'] = $sales_status;
		//产权年限 0未知 1-70年产权 2-50年产权 3-40年产权
		switch ($list['property_age']){
			case 1:
			  $property_age = '70年产权';
			  break;
			case 2:
			  $property_age = '50年产权';
			  break;
			case 3:
			  $property_age = '40年产权';
			  break;
			default:
			  $property_age = '未知';
		}
		$list['property_age'] = $property_age;

		//工程进度
		switch ($list['progress']){
			case 1:
			  $progress = '在建中';
			  break;
			case 2:
			  $progress = '已竣工';
			  break;
			case 3:
			  $progress = '未动工';
			  break;
			default:
			  $progress = '未知';
		}
		$list['progress'] = $progress;

		//装修情况
		$list['decoration_name'] = '';
		if(!empty($list['decoration'])){
		    $property_type = M('property_cate')->where('id in('.$list['decoration'].')')->select();
		    foreach($property_type as $k=>$v){
				$list['decoration_name'] .= $v['name'].'、';
		    }
		    $list['decoration_name'] = substr($list['decoration_name'],0,-3);
		}
		//带看奖(开始时间,结束时间,带看奖) 
		$prize = M('property_prize')->field('id,prize,time_start,time_end')->where('pid='.$list['id'].' AND stores_id="" AND time_start<'.$time.' AND time_end > '.$time)->order('id DESC')->find();
		$list['prize'] = $prize;
		 if($data['uid']){
		 	$prize = M('property_prize')->field('id,prize,time_start,time_end,stores_id')->where('stores_id != "" AND pid='.$list['id'].' AND time_start<'.$time.' AND time_end > '.$time)->order('id DESC')->find();
		 	if($prize['id']){
		 		$stores_id = M('user')->where('id='.$data['uid'].' AND stores_id in('.$prize['stores_id'].')')->getField('stores_id');
		 		if($stores_id){
		 			$list['prize'] = $prize;
		 		}
		 	}
		}
		$list['mianji']='';
		//去除html标签
		$list['feature']= substr(preg_replace("/\s+/", "\n",str_replace(array(" ","&nbsp;"),'',strip_tags($list['feature']))),0);//楼盘卖点儿
		$list['info']= substr(preg_replace("/\s+/", "\n",str_replace(array(" ","&nbsp;"),'',strip_tags($list['info']))),0);//项目描述
		
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
			//$hxlist[$k]['img']=$this->path.attach(get_thumb($v['img'], '_280x210'), 'property/huxing');
			$hxlist[$k]['img']=get_fdfs_image($v['img'], '_280x210');
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
				$pic_img[$k]['img'] = get_fdfs_image($v['img'], '_640x480');
				
				$pic_img[$k]['title'] = $v['title'] ? $v['title'] : ' ';
			}
		}
		$list['pic_img']=$pic_img;
		//7张图片
		
		
		if(!empty($list['metro']))
		{
		      $str  = $list['metro'];
		       //地铁关联
		      $array = array();
		      $strarray = explode('|', $str);
		      foreach ($strarray as $k1 => $v) {
			  $arr = explode('&', $v);
			  $metro = M('metro')->field('id,pid,name')->where('id ='.reset($arr))->find();
			  $array[$k1]['metro_id'] = $metro['id'];
			  $array[$k1]['metro_pid'] = $metro['pid'];
			  $array[$k1]['metro_name'] = $metro['name'];
			  $endarr = explode(',', end($arr));
			  foreach ($endarr as $k2 => $n) {
			      $array[$k1]['metro_end'][$k2] = M('metro')->field('id,pid,name')->where('id ='.$n)->find();
			  }
		      }
		      $list['metro'] = $array;
		      
		      foreach($list['metro'] as $k=>$v)
		      {
				 if(!empty($v['metro_end']))
					    $list['metro'][$k]['metro_name'] .= ':';
					    
				 foreach($v['metro_end'] as $k1=>$v1)
				 {
					    $list['metro'][$k]['metro_name'] .= ' '.$v1['name'];
				 }
		      }
		}
		else
		{
		      $list['metro'] = array();;
		}
		
		if(empty($list['elevated']))
		      $list['elevated'] = '';
	       if(empty($list['traffic']))
		      $list['traffic'] = '';
		     //var_dump($list);exit;
		$this->ajaxReturn(200,L('app_requre_normal'),$list);
	}
	//楼盘详情图片	1效果图，2规划图，3配套图，4实景图，6交通图，7样板图
	public function property_img(){
    	$data = json_decode($_POST['params'],TRUE);
		$id   = $data['pid'];
		$id   = (int)$id;
		//$id=370;
        !$id && $this->ajaxReturn(51,L('app_requre'));
        $list = M('property_img')->where('pid ='.$id)->order('id DESC')->select();
        $img_thumb = M('property')->where('id ='.$id)->getField('img_thumb');
        $arr_num=array(1,2,3,4,5,6);
        $arr_list = array();
        foreach ($list as $key => $value) {
            $list[$key]['checked'] = 0;
            if($img_thumb == substr($value['img'],9)){
            	$list[$key]['checked'] = 1;
            }
            $status = $value['status'];
			$arr_list[$status][] = $list[$key];
	
        }
        for ($i=1; $i<=6; $i++) {
	       if(empty($arr_list[$i])){
	       	$arr_list[$i]=array();
	       }
        }
      
        //var_dump($arr_list);
        //exit;
        $this->ajaxReturn(200,L('app_requre_normal'),$arr_list);
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
		
		$info['img'] =get_fdfs_image($info['img'], '_640x480');
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
		//$id =105;
        $info = M('article')->field('id,title,img,time_start,time_end,info,pid,city_id')->where(array('cate_id'=>1,'id'=>$id))->find();
        
        //去除html标签
        $info['info']=substr(preg_replace("/\s+/", "\n",str_replace(array(" ","&nbsp;"),'',strip_tags($info['info']))),0);
        
        //介绍中的详情图片替换
        $info['info']=str_replace('<img src="','<img src="'.$this->local_path,$info['info']);
        
        //时间转换
        $info['time_start']=date("Y-m-d",$info['time_start']);
        $info['time_end']=date("Y-m-d",$info['time_end']);
        
        
       // $info['img'] =$this->path.attach(get_thumb($info['img'], ''), 'article');
		$info['img'] =get_fdfs_image($info['img'], ''); 
        $info['protitle'] = M('property')->where(array('status'=>1,'id'=>$info['pid']))->getField('title');
            
        $cityinfo = M('city')->field('pid,name')->where(array('id'=>$info['city_id']))->find();
        $info['city'] = M('city')->field('name')->where(array('id'=>$cityinfo['pid']))->getField('name');
   
        $info['quyu']=$cityinfo['name'];
        
        $info['kefu_tel']=C('pin_kefu_tel');
            
            //print_r($info);die();
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
    * 获取楼盘对应信息
    +----------------------------------------------------------
   */
	public function reporte_info() {
		//加楼盘名字 佣金
		$datas=json_decode($_POST['params'],TRUE);
		$id = $datas['pid'];
		//$id = 367;
		!$id && $this->ajaxReturn(51,L('app_requre'));
		$info = M('property')->field('id,property_type,commission_info,report_info,title')->where('id='.$id)->find();
		//渠道佣金 适用物业 规则 
		$yj_where = 'pid ='.$id;
		//$data['uid'] = 11395;
		$l_stores_id = M('property_commission')->where("stores_id !='' AND pid=".$id)->order('id DESC')->getField('stores_id');
		if($data['uid'] && $l_stores_id){
			$u_count = M('user')->where("id=".$data['uid']." AND stores_id in(".$l_stores_id.")")->count('id');
		}
		if(!$u_count){
			$yj_where .= ' AND stores_id=""'; 
		}
		$commission = M('property_commission')->field('id,property_type,price')->where($yj_where)->order('add_time DESC')->select();
		foreach ($commission as $key => $value) {
			$catearr = M('property_cate')->field('name')->where('id in('.$value['property_type'].')')->select();
		    $commission[$key]['cate'] = '';
		    foreach ($catearr as $k => $v) {
		       $commission[$key]['cate'] .= $v['name'].',';
		    }
		    $commission[$key]['cate'] = substr($commission[$key]['cate'],0,-1);
		}
		$info['yj_commission']=$commission;

		// $info['property_type_name'] = '';
		// if(!empty($info['property_type'])){
		//     $property_type = M('property_cate')->field('id,name')->where('id in('.$info['property_type'].')')->select();
		//     foreach($property_type as $k=>$v){
		// 		$info['property_type_name'] .= $v['name'].'、';
		//     }
		//     $info['property_type_name'] = substr($info['property_type_name'],0,-3);
		// }

		$this->ajaxReturn(200,L('app_requre_normal'),$info);

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
    	$list = M('pringles')->field('id,title,author,img,info')
    	                     ->where('status=1 and cate_id=1')
    	                     ->order('add_time DESC')
    	                     ->select();
    	
    	foreach($list as $k => $v){
    		$list[$k]['img'] = get_fdfs_image($v['img'],$houzui);
    		$arr_info = explode('。', $v['info']);
    		$list[$k]['intro'] = trim(strip_tags($arr_info[0]));
    	}
    	//print_r($list);
    	//die();
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
       	$list['url'] = "http://www.fangpinhui.com/?g=weixin&m=pringles_detail&a=info&id=".$data['pid'];
    	//print_r($list);die();
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
		$info = M('user')->field('id,username,avatar,city_id,mobile,address,stores_id')->where(array('id'=>$user_info['id'], 'status'=>1))->find();
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
		 $reporte = M('myclient')->field('A.id,A.name,A.mobile,B.status,B.with_look,B.status_cid,C.title')
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
		
		//$info['head_picture']=$this->path.attach(get_thumb($info['avatar'], '_100'), 'avatar');
		  $info['head_picture']=get_fdfs_image($info['avatar'], '_100x100');

		//服务专员添加 lishun 2015 05 11
		$fph  = C('DB_PREFIX');
		$str = 'C.username as admin_username,C.mobile as admin_mobile';
		$zhuanyuan =  M('stores')->field($str)->table("{$fph}stores AS A LEFT JOIN {$fph}admin AS C ON C.id = A.sid")->where('A.id ='.$info['stores_id'])->find();
		$info['zhuanyuan'] = $zhuanyuan;
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
     	
     	$info = M('user')->field('id,username,mobile,gender,city_id,property_cate_id')->where(array('id'=>$user_info['id'],'status'=>1))->find();
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
	$user_info['id'] = $data['uid'];//=11395
	(!$data['uid']) && $this->ajaxReturn(51,'uid'.L('app_empt'));
	$uuid=$data['uuid'];
	(!$uuid) && $this->ajaxReturn(51,'uuid'.L('app_empt'));
	$this->check_uuid($uuid,$user_info['id']);

	$with_look=$data['type'] ;//= 1
	(!$with_look) && $this->ajaxReturn(51,'type'.L('app_empt'));
	!isset($data['page']) && $this->ajaxReturn(51,'页号不能为空');
	!$data['number_each_page'] && $this->ajaxReturn(51,'页条数不能为空');
	//状态
	//$statusarr = explode(',', $data['status']);
	$where = "with_look = 1 AND uid = ".$data['uid'];
	if($data['status']){
		$statusarr = explode(',', $data['status']);
		$where .= ' AND status ="'.$statusarr[0].'"  AND status_cid ="'.$statusarr[1].'"';
    }
    $page = $data['page'];
	$number_each_page=$data['number_each_page'];
	$start = $page*$number_each_page;

	//表前缀获取
	$fph = C('DB_PREFIX');	
	$list = M('myclient_property')->field('id,pid,status,status_cid,property')->where($where)->limit($start,$number_each_page)->select();
	foreach ($list as $k => $v) {
		$list[$k]['title'] = M('property')->where('id='.$v['property'])->getField('title');
		$list[$k]['name'] = M('myclient')->where('id='.$v['pid'])->getField('name');
		if($v['status'] == 6 AND $v['status_cid'] == 0){
			$list[$k]['zhongzhi'] = M('myclient_status')->where('status = 5 AND mpid ='.$v['id'])->getfield('status_cid');
		}
	}

	//累计客户
	$mycount = count(M('myclient_property')->where('uid ='.$user_info['id'])->group('pid')->select());
	//累计成交
	$chengjiao = count(M('myclient_property')->where("status = 7 AND uid = ".$user_info['id'])->group('pid')->select());
	$list ? $list : $list=array();

	$info['item']=$list;
   	$info['client_sum']=$mycount;
	$info['deal_sum']=$chengjiao;
	if(empty($info['item'])){
  		$info['item'] = array();
  	}
  	//var_dump($info);exit;
  	// echo json_encode($info).'<BR>';
	$this->ajaxReturn(200,L('app_requre_normal'),$info);
}

  /**
    +----------------------------------------------------------
    * 我的客户详情 lishun 2015-05-11
    +----------------------------------------------------------
  */
     public function client_detail(){
	  	$data=json_decode($_POST['params'],TRUE);
	  	$user_info['id'] = $data['uid'];//= 11395
	  	(!$data['uid']) && $this->ajaxReturn(51,'uid'.L('app_empt'));
	  	$uuid=$data['uuid'];
	  	(!$uuid) && $this->ajaxReturn(51,'uuid'.L('app_empt'));
	  	$this->check_uuid($uuid,$user_info['id']);
	  	$with_look=$data['type'] ;//= 1
	  	(!$with_look) && $this->ajaxReturn(51,'type'.L('app_empt'));
  		$fph = C('DB_PREFIX');
  		$id  = $data['cid'];//= 2227
  		if($data['tag']=='2'){//1非消息推送 2消息推送
  			$id = M('myclient_property')->field('pid')->where('id='.$id)->getField('pid') ;
  		}
  		(!$id) && $this->ajaxReturn(51,'cid'.L('app_empt'));
  		(!$data['pro_id']) && $this->ajaxReturn(51,'pro_id'.L('app_empt'));
  		//////////////////////////////////////////////////////////
		$info = M('myclient_property')->field('id,uid,pid,property')->where("uid = ".$data['uid']." AND with_look = ".$with_look." AND pid = ".$id.' AND id='.$data['pro_id'])->find();
		//$info = M('myclient_property')->field('id,uid,pid,property')->where("uid = 11395 AND with_look = ".$with_look." AND pid = ".$id.' AND id=2304')->find();
		$list['item'] = M('myclient_status')->field('id,mpid,pid,status,status_cid,with_look,info,intention_price,deposit,add_time')->where('mpid ='.$info['id'])->order('status DESC')->group('status')->select();

		$list['property_info'] = M('property')->field('tel,list_price,title')->where('id='.$info['property'])->find();
		$list['user'] = M('myclient')->field('name,mobile')->where('id ='.$id)->find();

		$listliu = M('myclient_status')->field('deposit,info,signing_time')->where('status = 6 AND mpid ='.$info['id'])->find();
		if(!empty($listliu)){
			$listliu['deposit'] = explode(",",$listliu['deposit']);
			$listliu['info'] = explode(",",$listliu['info']);
			$listliu['signing_time'] = explode(",",$listliu['signing_time']);
			$list['listliu'] = $listliu;
		}	
		$endlist = end($list);
		if($endlist['status'] == 7){
			$str = '';
			$info_s = M('myclient_status')->where('status = 7 AND mpid ='.$info['id'])->order('id')->select();
			foreach ($info_s as $key => $value) {
				$str .='您的客户已购买了'.$value['measure'].'㎡的房源，总价为:'.$value['total_price'].'元<br>';
			}
			$endlist['info'] = $str;
		}
  		$list['kefu_tel']=C('pin_kefu_tel');
  		// echo '<pre>';
 		//var_dump($list);exit;
  		$this->ajaxReturn(200,L('app_requre_normal'),$list);
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
   		$list[$k]['img']=get_fdfs_image($v['img'], '_app_thumb');
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
						->where('a.uid='.$uid.' AND b.buy_product ='.$value['buy_product'].' AND b.status = 5')->count('a.id');
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
     * 统计app相关数据
     +----------------------------------------------------------
     */
    
    public function  statistical(){
    
    	$data=json_decode($_POST['params'],TRUE);
    	 
    	$add_data['uid']= $data['uid'] ;
    	$add_data['origin']= $data['equipment_type'];
    	$add_data['get_ip']= $data['equipment_number'];
    	$add_data['add_time']=time();
    	$date = date('Y-m-d',time());
    	if($add_data['uid']){                  //app消息交互
    		if(M('online')->where("uid =".$data['uid']." and origin =".$data['equipment_type']." and add_time between ".strtotime($date.' 00:00:00')." and ".strtotime($date.' 23:59:59')."")->count('id')){
    			
    				$this->ajaxReturn(200,L('appHY Have statistical'));
    		}else {
    			 M('online')->add($add_data);
    			$this->ajaxReturn(200,L('appHY Statistical success'));
    		}
    	}else{                                //APP_UV
    		if(M('online')->where("get_ip ='".$data['equipment_number']."' and origin =".$data['equipment_type']." and add_time between ".strtotime($date.' 00:00:00')." and ".strtotime($date.' 23:59:59')."")->count('id')){
    			$this->ajaxReturn(200,L('appUV Have statistical'));
    		}else {
    			M('online')->add($add_data);
    			$this->ajaxReturn(200,L('appUV Statistical success'));
    		}
    	}
    }  
    
    
    /**
     +----------------------------------------------------------
     * 推送消息
     +----------------------------------------------------------
     */
    public function Push_message (){

        //给案场的人推送报备成功的消息
    		/*	$app_push = D('app_push');
    			$arr_data=array();
    			$arr_data['muclient_name']=$data['name']='客';
    			$arr_data['muclient_mobile']=$data['mobile']='123';
    			$arr_data['agent_name']=$user['username']='经';
    			$arr_data['agent_mobile']=$user['mobile']='321';
    			$info="已有经纪人".$user['username']."，".$user['mobile']."于".date('Y-m-d H:i',time())."成功报备客户".$data['name']."，".$data['mobile']."至".$title."，报备性质：".$str."，请及时联系。" ;
    			$app_push->push_case($info,23,1223,1,$arr_data);
    		*/	
    			// descr 开始  app 需求推送 这个邀约状态的相关信息 给相对应的经纪人
    		//	D('app_push')->customizedCast(4830,'1111','222',1);
    			// 结束 app 需求推送 这个邀约状态的相关信息 给相对应的经纪人
    	
    }
    
	   
	   /**
	   +----------------------------------------------------------
	   *  注册接口 不提交只验证信息
	   +----------------------------------------------------------
	   */
	   public function authentication()
	   {
		      $data = json_decode($_POST['params'],TRUE);
		      
		      //判断手机号是否存在
		      if(empty($data['mobile']))
		      {
				 $this->ajaxReturn(51,L('app_mobile_empty'));
		      }
		      
		      //验证手机
		      if(checkMobile($data['mobile']) == false)
		      {
				 $this->ajaxReturn(51,L('app_mobile_format'));
		      }
		      //验证是否重复
		      $boll = M('user')->where("mobile='".$data['mobile']."' and status=1")->count('id');
		      if(!empty($boll))
		      {
				 $this->ajaxReturn(1004, L('app_username_exist'));
		      }
		      
		      //验证 验证码是否为空
		      if(empty($data['mobile_code']))
		      {
				 $this->ajaxReturn(51,L('app_mobile_code_empty'));
		      }
		      
		      //验证 密码是否为空
		      if(empty($data['password']))
		      {
				 $this->ajaxReturn(51,L('app_password_empty'));
		      }
		      
		      if(strlen($data['password'])<6)
		      {
				 $this->ajaxReturn(1006,L('app_password_format'));
		      }
		      
		      $this->ajaxReturn(200,'验证成功');
	   }
       
	   /**
	   +----------------------------------------------------------
	   * 注册 发送 公司-门店所属公司
	   +----------------------------------------------------------
	   */
	   public function list_company()
	   {
		      $list = array();
		      $list = M('company')->field('id,name,short_name')->where('teamwork = 1')->select();
		      if(empty($list))
		      {
				 $list = array();
		      }
		      $this->ajaxReturn(200,'所属公司',$list);
	   }
	   
	   
	   
	   /**
	   +----------------------------------------------------------
	   * 注册 根据城市 -市级 进行 - 发送 门店 
	   +----------------------------------------------------------
	   */
	   public function list_stores()
	   {
		      $data = json_decode($_POST['params'],TRUE);
		      
		      $where = ' AND 1=1';
		      //判断是否 传门店所属公司
		      if(empty($data['company_id']))
		      {
				 $this->ajaxReturn(51,'门店所属公司不能为空');
		      }
		      
		      $list = array();
		      $list['city_id'] = '';
		      if(!empty($data['city_id']))
		      {
				 $where .= ' AND city_id in(select id from fph_city where id = '.$data['city_id'].' or spid RLIKE "[[:<:]]'.$data['city_id'].'[[:>:]]")';
				 $list['city_id'] = $data['city_id'];
		      }
		      //获取 拥有门店的城市
		      $city_s = '';
		      $stores_all =  M('stores')->field('city_id')->where('pid ='.$data['company_id'])->select();
		      foreach($stores_all as $v)
		      {
				 $city_one = $this->city_one($v['city_id']);
				 if(!empty($city_one))
				 {
					    $city_s .=$city_one.',';
				 }
				
		      }
		      
		      if(!empty($stores_all))
		      {
				 $city_s = substr($city_s,0,-1);
				 $list['city'] = M('city')->field('id,name')->where('id in('.$city_s.')')->select();
		      }
		      
		      $list['info'] = M('stores')->field('id,code_id,name')->where('pid ='.$data['company_id'].$where)->order('id DESC')->select();
		      if(empty($list['info']))
		      {
				 $list['info'] = array();
		      }
		      if(empty($list['city']))
		      {
				 $list['city'] = array();
		      }
		      $this->ajaxReturn(200,'门店，城市',$list);
		      
	   }
	   
	   /**
	   +----------------------------------------------------------
	   * 我的门店
	   +----------------------------------------------------------
	   */
	   public function me_stores()
	   {
		      $data = json_decode($_POST['params'],TRUE);
		      
		      if(empty($data['uid']))
		      {
				 $this->ajaxReturn(51,'没有用户');
		      }
		      
		      $list = array();
		      $user = M('user')->where('id ='.$data['uid'])->find();
		      if(empty($user))
		      {
				 $this->ajaxReturn(51,'没有用户');
		      }
		      if($user['stores_id'] != 0)
		      {
				 $list['stores'] = M('stores')->field('id,uid,type,code_id,name,address,status')->where('id ='.$user['stores_id'])->find();
				 $bossuser = M('user')->where('id ='.$list['stores']['uid'])->find();
				 if(empty($list['stores']))
				 {
					    $list['stores'] = array();
				 }
				 else
				 {
					    $list['stores']['username'] = $bossuser['username'];
					    $list['stores']['mobile'] = $bossuser['mobile'];
				 }
				 $list['user']  = array();
				 if($list['stores']['type'] == 2)
				 {
					    $list['user'] = M('user')->field('id,username,mobile')->where('stores_id ='.$user['stores_id'])->select();
					    
				 }
				 else
				 {
					    $list['user'] = M('user')->field('id,username,mobile')->where('id ='.$data['uid'])->select();
				 }
				 
				 foreach($list['user'] as $k=>$v)
				 {
					    $list['user'][$k]['status'] = 2;
					//    if($list['stores']['uid'] == $v['id'])
					//    {
					//	       $list['user'][$k]['status'] = 1;
					//    }
					    if(empty($v['username']))
					    {
						$list['user'][$k]['username'] = '';
					    }
					    $list['user'][$k]['count'] =M('myclient_property')->where('status = 7 AND status_cid = 0 AND uid='.$v['id'])->count('id');
					    if(empty($list['user'][$k]['count']))
					    {
						   $list['user'][$k]['count'] = 0;
					    }
				 }
				
		      }
		      $this->ajaxReturn(200,'成功',$list);
	   }
	   
	   
	   /**
	   +----------------------------------------------------------
	   * 注册
	   +----------------------------------------------------------
	   */
	   public function new_register()
	   {
	   
		      $data = json_decode($_POST['params'],TRUE);
		      
		       //判断手机号是否存在
		      if(empty($data['mobile']))
		      {
				 $this->ajaxReturn(51,L('app_mobile_empty'));
		      }
		      
		      //验证手机
		      if(checkMobile($data['mobile']) == false)
		      {
				 $this->ajaxReturn(51,L('app_mobile_format'));
		      }
		      
		      //验证 密码是否为空
		      if(empty($data['password']))
		      {
				 $this->ajaxReturn(51,L('app_password_empty'));
		      }
		      
		       //验证 密码长度
		      if(strlen($data['password'])<6)
		      {
				 $this->ajaxReturn(1006,L('app_password_format'));
		      }
		      
		       //判断 门店代码
		      if(empty($data['code_id']))
		      {
				 $this->ajaxReturn(51,'门店代码不能为空');
		      }
		      else
		      {
				 $stores = M('stores')->where('code_id ='.$data['code_id'])->find();
				 if(empty($stores))
				 {
					    $this->ajaxReturn(51,'门店代码错误');
				 }
				 $company = M('company')->where('teamwork = 1 AND id ='.$stores['pid'])->find();
				 if(empty($company))
				 {
					    $this->ajaxReturn(51,'门店代码错误');
				 }
				 $add['stores_id'] = $stores['id'];
		      }

		      
		       //验证 密码是否为空
		      if(empty($data['equipment_number']))
		      {
				 $this->ajaxReturn(51,'设备号不能为空');
		      }
		      
		       //验证 密码是否为空
		      if(empty($data['equipment_type']))
		      {
				 $this->ajaxReturn(51,'设备类型号不能为空');
		      }
		          
		      $add['mobile'] = $data['mobile'];
		      $add['reg_time']    = time();
		      $add['last_time']   = time();
		      $add['password']    = md5($data['password']);
		      $add['gender']      = 2;
		      $add['origin']      = $data['equipment_type']+1;
		      
		      $add_uid=M('user')->add($add);
		      
		      $res_data['uuid']=sprintf( '%04x%04x%04x%04x%04x%04x%04x%04x',
			      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			      mt_rand(0, 0x0fff) | 0x4000,
			      mt_rand(0, 0x3fff) | 0x8000,
			      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));//设置唯一的标示
		      $total_data['uuid']=$res_data['uuid'];
		      $total_data['id']=$add_uid;
		      $total_data['mobile']=$add['mobile'];
		      //每次登陆都要给uuid，纪录不同时间段的登录。
		      if(M('app_login')->where(array('equipment_number'=>$data['equipment_number'],'uid'=>$add_uid,'status'=>1))->count('id')){
				 M('app_login')->where(array('equipment_number'=>$data['equipment_number']))->save(array('last_time'=>time(),'uuid'=>$res_data['uuid']));
				 $this->ajaxReturn(200,L('reg_successe'),$total_data);
		      }else{
				 $res_data['uid']=$add_uid;
				 $res_data['equipment_number']=$data['equipment_number'];
				 $res_data['equipment_type']=$data['equipment_type'];
				 $res_data['last_time']=time();
				 M('app_login')->add($res_data);
				 $this->ajaxReturn(200,L('reg_successe'),$total_data);
		      }
	   }
	   
	   //获取区域 市级别
	   public function city_one($id)
	   {
		      $city = M('city')->where('id ='.$id)->find();
		      $count = explode('|',$city['spid']);
		      if(count($count) == 2)
		      {
			  return $id;
		      }
		      if($city['pid'] == 0)
		      {
			  return M('city')->where('id ='.$id)->getField('pid');
		      }
		      return $this->city_one($city['pid']);
	   }
	   
	   
	   //市级 城市
	   public function city_list()
	   {
		      $city_s = '';
		      $stores_all =  M('stores')->field('city_id')->select();
		      foreach($stores_all as $v)
		      {
				 $city_one = $this->city_one($v['city_id']);
				 if(!empty($city_one))
				 {
					    $city_s .=$city_one.',';
				 }
				
		      }
		      $city_s = substr($city_s,0,-1);
		      $list = M('city')->where('id in('.$city_s.')')->select();
		      
		      if(empty($list))
		      {
				 $list = array();
		      }
		      else
		      {
				 foreach($list as $k=>$v)
				 {
					    $list[$k]['city'] = M('city')->where('pid = '.$v['id'].'')->select();
					    if(empty($list[$k]['city']))
					    {
						       $list[$k]['city'] = array();
					    }
					    else
					    {
						       $list[$k]['city'] = M('city')->where('pid = '.$v['id'].'')->select();
					    }
					    foreach($list[$k]['city'] as $k1 => $v1)
					    {
						       $list[$k]['city'][$k1]['city'] = M('city')->where('pid = '.$v1['id'].'')->select();
						       if(empty($list[$k]['city'][$k1]['city']))
								  $list[$k]['city'][$k1]['city'] = array();
					    }
				 }
		      }
		      
		      $this->ajaxReturn(200,'城市',$list);
	   }
	   
	   //根据id获取下级 区域
	   public function city_pid()
	   {
		       $data = json_decode($_POST['params'],TRUE);
		       
		       if(empty($data['city_id']))
		       {
				 $this->ajaxReturn(51,'请传值');
		       }
		       
		      $list = M('city')->where('pid ='.$data['city_id'])->select();
		      if(empty($list))
		      {
				 $list = array();
		      }
		       $this->ajaxReturn(200,'下级',$list);
	   }
	   
	   //加入门店 根据代码
	   public function add_stores()
	   {
		      $data = json_decode($_POST['params'],TRUE);
		      
		      if(empty($data['uid']))
		      {
				 $this->ajaxReturn(51,'没有用户');
		      }
		      
		      //门店代码
		      if(empty($data['stores']))
		      {
				  $this->ajaxReturn(51,'请输入门店代码');
		      }
		      
		      $stores = M('stores')->where('type = 2 AND code_id = '.$data['stores'])->find();
		      
		      //判断门店是否存在
		      if(empty($stores))
		      {
				 $this->ajaxReturn(51,'输入的门店不存在');
		      }
		      
		      //判断是否加入门店
		      if(empty($data['status']))
		      {
				 $this->ajaxReturn(51,'非法操作');
		      }
		      
		      if($data['status'] == 1)
		      {
				 M('user')->where('id='.$data['uid'])->save(array('stores_id'=>$stores['id']));
		      } 
		      
		      $this->ajaxReturn(200,'加入门店成功',$stores);
	   }
	   
	   //搜索公司
	   public function select_company()
	   {
		      $data = json_decode($_POST['params'],TRUE);
		       
		      if(empty($data['company']))
		      {
				 $this->ajaxReturn(51,'请输入公司');
		      }
		      
		     $list['company']  = M('company')->where('name like "%'.$data['company'].'%" OR short_name like "%'.$data['company'].'%" ')->select();
		      
		      if(empty($list))
		      {
				$list['company'] = array();
		      }
		      
		      $user = M('user')->where('internal = 1  AND id = '.$data['uid'])->find();
		      if(empty($user))
		      {
				 $list['status'] = array('status'=>2);
		      }
		      else
		      {
				 $list['status'] = array('status'=>1);
		      }
		      
		      $this->ajaxReturn(200,'加入门店成功',$list);
		      
	   }
	   
	   
	   //新建门店
	   public function new_stores()
	   {
		      
		      $data = json_decode($_POST['params'],TRUE);
		      
		      if(empty($data['uid']))
		      {
				 $this->ajaxReturn(51,'没有用户');
		      } 
		      
		      //判断城市是否选择
		      if(empty($data['city_id']))
		      {
				 $this->ajaxReturn(51,'请选择城市');
		      }
		      
		      //判断
		      if(empty($data['company']))
		      {
				 $this->ajaxReturn(51,'请选择公司');
		      }
		      
		      //判断门店名称
		      if(empty($data['name']))
		      {
				 $this->ajaxReturn(51,'请输入门店名称');
		      }
		      
		      //详细地址
		      if(empty($data['address']))
		      {
				  $this->ajaxReturn(51,'请输入详细地址');
		      }
		      
		       //判断手机号是否存在
		      if(empty($data['contact_tel']))
		      {
				 $this->ajaxReturn(51,'请输入手机好');
		      }
		      
		      //验证手机
		      if(checkMobile($data['contact_tel']) == false)
		      {
				 $this->ajaxReturn(51,'请输入正确手机号');
		      }
		      
		      //联系人
		      if(empty($data['contact']))
		      {
				 $this->ajaxReturn(51,'请输入联系人');
		      }
		      
		      
		      $company = M('company')->where('name = "'.$data['company'].'" ')->find();
		      if(empty($company))
		      {
				 $user = M('user')->where('internal = 1  AND id = '.$data['uid'])->find();
				 if(empty($user))
				 {
					    $this->ajaxReturn(51,'没有权限创建公司');
				 }
				 
				 $companyadd['uid'] = $data['uid'];
				 $companyadd['name'] = $data['company'];
				 $companyadd['short_name'] = $data['company'];
				 $companyadd['add_time'] = time();
				 
				 $companypid  = M('company')->add($companyadd);
		      }
		      else
		      {
				$companypid =   $company['id'];
		      }
		      
		      $code_id = M('stores')->order('code_id DESC')->getField('code_id');
		      
		      $storesarr['uid'] = $data['uid'];
		      $storesarr['pid'] = $companypid;
		      $storesarr['code_id']  = $code_id+1;
		      $storesarr['name']  =  $data['name'];
		      $storesarr['city_id']  = $data['city_id'];
		      $storesarr['address'] = $data['address'];
		      $storesarr['contact'] = $data['contact'];
		      $storesarr['contact_tel'] =$data['contact_tel'];
		      $storesarr['add_time'] = time();
		      $storesarr['type'] = 2;
		      $storesarr['status'] = 0;
		      //是因为登录方式需要修改,暂时获取不到sid
		      //$storesarr['sid'] = $data['sid'];//sid 为传过来的admin表ID
		      
		      $user = M('user')->where('internal = 1  AND id = '.$data['uid'])->find();
		      if(!empty($user))
		      {
				  $storesarr['status'] = 1;
				  $storesarr['service'] = $data['uid'];
		      }
		      
		      if(empty($data['stores_id']))
		      {
				 
				 $stores_id = M('stores')->add($storesarr);
				 M('user')->where('id ='.$data['uid'])->save(array('stores_id'=>$stores_id));

		      }
		      else
		      {
				 M('stores')->where('id ='.$data['stores_id'])->save($storesarr);
		      }
		      
		      $this->ajaxReturn(200,'新建门店成功',1);
		      
	   }
	   
	   
	   //佣金
	   public function commission()
	   {
		      $data = json_decode($_POST['params'],TRUE);
		      
		      if(empty($data['uid']))
		      {
				 $this->ajaxReturn(51,'没有用户');
		      }
		      
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
		      $list['commission'] = M('myclient_status')
		      ->field('A.id,A.pid,A.username,A.mobile,C.expenditure,C.status as cstatus')
		      ->table("{$fph}myclient_status AS A
		       INNER JOIN {$fph}myclient_property AS B ON A.mpid = B.id
		       INNER JOIN {$fph}commission as C ON C.pid=A.id
		       ")->where($where)->select();
		      
		      foreach($list['commission'] as $k=>$v)
		      {
			      
				 $list['commission'][$k]['commission_time'] = M('expenditure')->where('pid ='.$v['id'])->order('add_time DESC')->getField('add_time');
				 $list['commission'][$k]['commission_time'] = date('Y-m-d H:i:s', $list['commission'][$k]['commission_time']);
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
		      
		      $this->ajaxReturn(200,'我的佣金',$list);
	   }
	   
	   
	
 }