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

class AppcaseAction extends baseAction {
		
	   protected $path = 'http://192.168.1.115';
	   protected $local_path = 'http://192.168.1.115/fangpinhui';
	   
	/**
    +----------------------------------------------------------
    * 初始化
    +----------------------------------------------------------
   */
	public function _initialize(){
		parent::_initialize();
	    header("Content-Type:text/html; charset=utf-8");
	    $_POST['token']!=md5('home_app'.date('Y-m-d',time()).'#$@%!*') && $this->ajaxReturn(51,'token'.L('app_token'));    
	  }
 
	 /**
    +----------------------------------------------------------
    * 登录
    +----------------------------------------------------------
   */
	  
	function  login(){
	    $data=json_decode($_POST['params'],TRUE);
	    
		!$data['username'] && $this->ajaxReturn(51,"用户名不为空");
        !$data['password'] && $this->ajaxReturn(51,"密码不为空");
		
		$user = M('admin')->field('id,role_id,username,password')->where("(username='".$data['username']."' OR mobile='".$data['username']."') AND status=1")->find();
		if (!$user) {
			$this->ajaxReturn(1002,L('user_not_exist'));exit();
		}
		if ($user['password'] != md5($data['password'])) {
			$this->ajaxReturn(1001,L('password_error'));exit();
		}
		$case_field = M('case_field')->where(array('admin_id'=>$user['id']))->count('id');
		!$case_field && $this->ajaxReturn(51,"你没有权限登录！");
		
		$this->ajaxReturn(200,L('login_successe'),$user) ;
	
	}
	
	/**
	 +----------------------------------------------------------
	 * 登录uuid检验
	 +----------------------------------------------------------
	 */
	public function  check_uuid($uid){
		
		!M('admin')->where(array('id'=>$uid))->count('id') && $this->ajaxReturn(51,L('app_requre'));
	}
	
	
	

	/**
    +----------------------------------------------------------
    * 新增申请   （报备申请）
    +----------------------------------------------------------
   */
    public function report_list (){
    	   	
        $data=json_decode($_POST['params'],TRUE);
		$_SESSION['admin']['id']=$data['uid'];
		$with_look=$data['type'];
		
		(!$with_look) && $this->ajaxReturn(51,'type'.L('app_empt'));
		!isset($data['page']) && $this->ajaxReturn(51,'页号不能为空');
		!$data['number_each_page'] && $this->ajaxReturn(51,'页条数不能为空');
        (!$_SESSION['admin']['id']) && $this->ajaxReturn(51,'uid'.L('app_empt'));	
        
        $page = $data['page'];
        $number_each_page=$data['number_each_page'];
        $start = $page*$number_each_page;
                
        $fph = C('DB_PREFIX');
        if($with_look==1){
        	$where = 'A.with_look = 1';
        }elseif ($with_look==2){
        	$where = 'A.with_look = 2';
        }else {
        	$where = '1=1';
        }
        
        $where .= ' AND A.status = 1 AND E.status = 1 ';//不知道怎么回事哦···
        //获取案场人员 负责楼盘
        $case_property = M('case_field')->where('admin_id ='.$_SESSION['admin']['id'])->getfield('property');
        if(!empty($case_property)){
        	$where .= ' AND C.id in('.$case_property.')';
        }
        $str = 'A.id,A.uid,A.property,A.status,A.status_cid,A.add_time,A.with_look,B.name,B.mobile,B.gender,C.title,D.username,D.mobile as user_mobile,D.gender as user_gender';
        
        $list = M('myclient_property')->field($str)
							          ->table("{$fph}myclient_property AS A
							        INNER JOIN {$fph}myclient AS B on A.pid = B.id
							        INNER JOIN {$fph}property AS C on C.id = A.property
							        INNER JOIN {$fph}user AS D on A.uid = D.id
							        INNER JOIN {$fph}myclient_status AS E on A.id = E.mpid
							        ")
							         ->where($where)
							         ->limit($start,$number_each_page)
							         ->order('A.add_time DESC')->select();
        
        foreach ($list as $key => $value) {
          $list[$key]['add_time']= date("Y-m-d H:i", $value['add_time']) ;
          $list[$key]['case_name'] = ' ';
          $case = M('case_field')->select();
          foreach ($case as $k => $v) {
             if(strstr(''.$v['property'].'',''.$value['property'].'')){
        		$list[$key]['case_name'] .= M('admin')->field('username')->where('id ='.$v['admin_id'])->getfield('username').',';
        	  }
          }
          $list[$key]['case_name'] = substr($list[$key]['case_name'], 0,-1);
		  if($value['status'] == 6 AND $value['status_cid'] == 0){
        		$list[$key]['zhongzhi'] = M('myclient_status')->where('status = 5 AND mpid ='.$value['id'])->getfield('status_cid');
          }
        $list[$key]['status_time'] =date("Y-m-d H:i", M('myclient_status')->where('mpid ='.$value['id'])->order('status DESC')->getfield('add_time'));
       }
       
       //print_r($list);die();
       
       $this->ajaxReturn(200,L('app_favorite_successe'),$list);

    }

	
/**
    +----------------------------------------------------------
    * 报备详情
    +----------------------------------------------------------
   */
  public function reporte_detail() {
        $datas     =  json_decode($_POST['params'],TRUE);
        $mpid      =  $datas['id'];
        (!$mpid) && $this->ajaxReturn(51,'id'.L('app_empt'));
        
        $fph = C('DB_PREFIX');
    	$list = M('myclient_status')->where('mpid ='.$mpid)->group('status')->select();
    	foreach ($list as $key => $value) {
    	    $list[$key]['visit_time']= date("Y-m-d", $value['visit_time']);
    		$list[$key]['add_time']= date("Y-m-d H:i", $value['add_time']);
    		if($value['status'] == 1){
    			$my_p = M('myclient_property')->field('uid,pid')->where('id ='.$value['mpid'])->find();
    			$list[$key]['myclient_mobile'] = M('myclient')->where('id ='.$my_p['pid'])->getField('mobile');
    			$list[$key]['user_name'] = M('user')->where('id ='.$my_p['uid'])->getField('username');
    		}
    		$list[$key]['title'] = M('property')->where('id='.$value['pid'])->getField('title');
    		$list[$key]['prefer'] = M('property')->where('id='.$value['pid'])->getField('prefer');
    	}

    	$listliu = M('myclient_status')->where('status = 6 AND mpid ='.$mpid)->find();

		if(!empty($listliu)){
			$listliu['deposit'] = explode(",",$listliu['deposit']);
			$listliu['info'] = explode(",",$listliu['info']);
			$listliu['signing_time'] = explode(",",$listliu['signing_time']);
			//$this->assign('listliu',$listliu);  
		}	

		$endlist = end($list);
		if($endlist['status'] == 7){
			$str = '';
			$info = M('myclient_status')->where('status = 7 AND mpid ='.$mpid)->order('id')->select();
			foreach ($info as $key => $value) {
				$user_arr1 = explode(",",$value['username']);
				$user_arr2 = explode(",",$value['mobile']);
				$user_arr3 = explode(",",$value['identity']);
				$strname = '';
				foreach ($user_arr1 as $k => $v) {
					if(empty($v))
						$this->ajaxReturn(0, L('请填写名称'));
					if(empty($user_arr2[$k]))
							$this->ajaxReturn(0, L('请填写手机'));
					if(empty($user_arr3[$k]))
							$this->ajaxReturn(0, L('请填写身份证'));
					$strname .= '购买人：'.$v.'&nbsp;&nbsp;'.$user_arr3[$k].'，';
				}
				$str .='房号：'.$value['number'].'&nbsp;&nbsp;面积：'.$value['measure'].'㎡　　总价：'.$value['total_price'].'元　　首付：'.$value['first_price'].'元　　贷款：'.$value['loan'].'元　　尾款：'.$value['tail_price'].'元　'.substr($strname,0,-3).'<br>';
			}
			$endlist['info'] = $str;
		}
		
		$this->assign('list',$list);

		//$this->assign('endlist',$endlist);
            
      // print_r($list);die();
       $this->ajaxReturn(200,L('app_favorite_successe'),$list);
      

    }

    
    /**
     +----------------------------------------------------------
     * 添加跟进
     +----------------------------------------------------------
     */

 public function add_following() {
 	
 	    $datas           = json_decode($_POST['params'],TRUE);
 	    $name            = $datas['name'];
		$mpid            = $datas['id'];
		$status          = $datas['status'];
		$select          = $datas['status_cid'];
		$data['info']    = $datas['info'];
		$visit_time      = $datas['visit_time'];
		
		(!$datas['id'])         && $this->ajaxReturn(51,'id'.L('app_empt'));
		(!$datas['status'])     && $this->ajaxReturn(51,'status'.L('app_empt'));
		!isset($datas['status_cid']) && $this->ajaxReturn(51,'status_cid'.L('app_empt'));
		//(!$datas['info'])       && $this->ajaxReturn(51,'info'.L('app_empt'));
		(!$datas['name'])       && $this->ajaxReturn(51,'name'.L('app_empt'));
		
		$list              = M('myclient_status')->where('mpid ='.$mpid)->order('status DESC')->find();
		$data['mpid']      = $list['mpid'];
		$data['pid']       = $list['pid'];
		$data['name']      = $name;          
		$data['with_look'] = $list['with_look'];
		$data['add_time']  = time();
		
		if($status == 1){
				if($select == 1){
					$data['visit_time'] = strtotime($visit_time);
					$data['status'] = 2;
					$data['status_cid'] = 1;
					M('myclient_status')->add($data);
					$str = '邀约成功';
					$str2 = '到访时间：'.$visit_time;
					if(!empty($data['info']))
						$str2 = '到访时间：'.$visit_time.'&nbsp;&nbsp;备注：'.$data['info'];
					
				} else {
					$data['status'] = 2;
					$data['status_cid'] = 0;
					M('myclient_status')->add($data);
					$str = '邀约失败';
				}
				$save['status'] = $data['status'];
				$save['status_cid'] = $data['status_cid'];
				M('myclient_property')->where('id ='.$mpid)->save($save);

				$info = M('myclient_status')->where('status = 2 AND mpid ='.$mpid)->find();

				if(!isset($str2))
					$str2 = '备注：'.$info['info'];

				// descr 开始  app 需求推送 这个邀约状态的相关信息 给相对应的经纪人   
				D('app_push')->customizedCast(M('myclient_property')->where('id ='.$mpid)->getField('uid'),$str2,$str,$data['with_look'],$mpid);
				// 结束 app 需求推送 这个邀约状态的相关信息 给相对应的经纪人
				

				$this->ajaxReturn(200,"success");
		}
		$this->ajaxReturn(51,L('app_requre'));

    }
     
     /**
    +----------------------------------------------------------
    * 邀约成功
    +----------------------------------------------------------
   */
public function invite_success (){
    	   	
        $data=json_decode($_POST['params'],TRUE);
		$_SESSION['admin']['id']=$data['uid'];
		$with_look=$data['type'];
		
		(!$with_look) && $this->ajaxReturn(51,'type'.L('app_empt'));
		(!$_SESSION['admin']['id']) && $this->ajaxReturn(51,'uid'.L('app_empt'));
		
		!isset($data['page']) && $this->ajaxReturn(51,'页号不能为空');
		!$data['number_each_page'] && $this->ajaxReturn(51,'页条数不能为空');
	
        
        $page = $data['page'];
        $number_each_page=$data['number_each_page'];
        $start = $page*$number_each_page;
                
        $fph = C('DB_PREFIX');
        if($with_look==1){
        	$where = 'A.with_look = 1';
        }elseif ($with_look==2){
        	$where = 'A.with_look = 2';
        }else {
        	$where = '1=1';
        }
        
        $where .= ' AND A.status = 2 AND A.status_cid = 1 AND E.status = 2';//不知道怎么回事哦···
        //获取案场人员 负责楼盘
        $case_property = M('case_field')->where('admin_id ='.$_SESSION['admin']['id'])->getfield('property');
        if(!empty($case_property)){
        	$where .= ' AND C.id in('.$case_property.')';
        }
        $str = 'A.id,A.uid,A.property,A.status,A.status_cid,A.add_time,A.with_look,B.name,B.mobile,B.gender,C.title,D.username,D.mobile as user_mobile,D.gender as user_gender';
        
        $list = M('myclient_property')->field($str)
							          ->table("{$fph}myclient_property AS A
							        INNER JOIN {$fph}myclient AS B on A.pid = B.id
							        INNER JOIN {$fph}property AS C on C.id = A.property
							        INNER JOIN {$fph}user AS D on A.uid = D.id
							        INNER JOIN {$fph}myclient_status AS E on A.id = E.mpid
							        ")
							         ->where($where)
							         ->limit($start,$number_each_page)
							         ->order('E.add_time DESC')->select();
        
        foreach ($list as $key => $value) {
          $list[$key]['add_time']= date("Y-m-d H:i", $value['add_time']);
          $list[$key]['case_name'] = ' ';
          $case = M('case_field')->select();
          foreach ($case as $k => $v) {
             if(strstr(''.$v['property'].'',''.$value['property'].'')){
        		$list[$key]['case_name'] .= M('admin')->field('username')->where('id ='.$v['admin_id'])->getfield('username').',';
        	  }
          }
          $list[$key]['case_name'] = substr($list[$key]['case_name'], 0,-1);
		  if($value['status'] == 6 AND $value['status_cid'] == 0){
        		$list[$key]['zhongzhi'] = M('myclient_status')->where('status = 5 AND mpid ='.$value['id'])->getfield('status_cid');
          }
        $list[$key]['status_time'] = date("Y-m-d H:i", M('myclient_status')->where('mpid ='.$value['id'])->order('status DESC')->getfield('add_time'));
       }
       
       //print_r($list);die();
       
       $this->ajaxReturn(200,L('app_favorite_successe'),$list);

    }
    
     

     /**
      +----------------------------------------------------------
      * 邀约失败
      +----------------------------------------------------------
      */
     
public function invite_failed (){
    	   	
        $data=json_decode($_POST['params'],TRUE);
		$_SESSION['admin']['id']=$data['uid'];
		$with_look=$data['type'];
		
		(!$with_look) && $this->ajaxReturn(51,'type'.L('app_empt'));
		(!$_SESSION['admin']['id']) && $this->ajaxReturn(51,'uid'.L('app_empt'));
		
		!isset($data['page']) && $this->ajaxReturn(51,'页号不能为空');
		!$data['number_each_page'] && $this->ajaxReturn(51,'页条数不能为空');
	
        
        $page = $data['page'];
        $number_each_page=$data['number_each_page'];
        $start = $page*$number_each_page;
                
        $fph = C('DB_PREFIX');
        if($with_look==1){
        	$where = 'A.with_look = 1';
        }elseif ($with_look==2){
        	$where = 'A.with_look = 2';
        }else {
        	$where = '1=1';
        }
        
        $where .= ' AND A.status = 2 AND A.status_cid = 0 AND E.status = 2 ';//不知道怎么回事哦···
        //获取案场人员 负责楼盘
        $case_property = M('case_field')->where('admin_id ='.$_SESSION['admin']['id'])->getfield('property');
        if(!empty($case_property)){
        	$where .= ' AND C.id in('.$case_property.')';
        }
        $str = 'A.id,A.uid,A.property,A.status,A.status_cid,A.add_time,A.with_look,B.name,B.mobile,B.gender,C.title,D.username,D.mobile as user_mobile,D.gender as user_gender';
        
        $list = M('myclient_property')->field($str)
							          ->table("{$fph}myclient_property AS A
							        INNER JOIN {$fph}myclient AS B on A.pid = B.id
							        INNER JOIN {$fph}property AS C on C.id = A.property
							        INNER JOIN {$fph}user AS D on A.uid = D.id
							        INNER JOIN {$fph}myclient_status AS E on A.id = E.mpid
							        ")
							         ->where($where)
							         ->limit($start,$number_each_page)
							         ->order('E.add_time DESC')
									 ->select();
        
        foreach ($list as $key => $value) {
          $list[$key]['add_time']= date("Y-m-d H:i", $value['add_time']);
          $list[$key]['case_name'] = ' ';
          $case = M('case_field')->select();
          foreach ($case as $k => $v) {
             if(strstr(''.$v['property'].'',''.$value['property'].'')){
        		$list[$key]['case_name'] .= M('admin')->field('username')->where('id ='.$v['admin_id'])->getfield('username').',';
        	  }
          }
          $list[$key]['case_name'] = substr($list[$key]['case_name'], 0,-1);
		  if($value['status'] == 6 AND $value['status_cid'] == 0){
        		$list[$key]['zhongzhi'] = M('myclient_status')->where('status = 5 AND mpid ='.$value['id'])->getfield('status_cid');
          }
        $list[$key]['status_time'] = date("Y-m-d H:i",M('myclient_status')->where('mpid ='.$value['id'])->order('add_time DESC')->getfield('add_time'));
       }
       
      // print_r($list);die();
       
       $this->ajaxReturn(200,L('app_favorite_successe'),$list);

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
        		//会员头像规格
        		$avatar_size = explode(',', C('pin_avatar_size'));
        		//回去会员头像保存文件夹
        		$uid = abs(intval($user_info['id']));
        		$suid = sprintf("%09d", $uid);
        		$dir1 = substr($suid, 0, 3);
        		$dir2 = substr($suid, 3, 2);
        		$dir3 = substr($suid, 5, 2);
        		$avatar_dir = $dir1.'/'.$dir2.'/'.$dir3.'/';
        		$head_picture_path='data/upload/avatar/'.$avatar_dir;
        		if (!is_dir($head_picture_path)){//新用户创建目录
        			mkdir($head_picture_path);
        		}

        		$head_picture='data/upload/avatar/'.$avatar_dir.md5($uid).'_100.jpg';
        		$head_picture64='data/upload/avatar/'.$avatar_dir.md5($uid).'_64.jpg';
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
        		
        		$image_address['head_picture']=$this->local_path.'/'.$head_picture;  //print_r($image_address);
                $this->ajaxReturn(200, L('upload_success'), $image_address);
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
  		$row['property'] = M('myclient_property')->field('a.id as pid,a.status,a.with_look,c.id,c.title,c.commission')
  	                                        	 ->table("{$fph}myclient_property AS a INNER JOIN {$fph}property AS c ON c.id=a.property")
  		                                         ->where("a.uid=".$user_info['id']." and a.pid=".$row['id']." and a.with_look=".$with_look)
			                                     ->select();
  		
  		foreach($row['property'] as $k => $v){
  			$row['property'][$k]['update_time'] = date('Y-m-d', M('myclient_status')->where('status = '.$v['status'].' AND mpid ='.$v['pid'])->getfield('add_time'));
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
   	$list = M('favorites')->field('B.id,B.title,B.weizhi as city,B.img,B.item_price,B.commission')
   	                     ->table("{$fph}favorites AS A INNER JOIN {$fph}property AS B ON A.pid=B.id")
   	                     ->where('A.uid ='.$user_info['id'])
   	                     ->order('A.add_time DESC')
   	                     ->limit($start,$number_each_page)
   			             ->select();
   	foreach($list as $k => $v){
   		$list[$k]['img']=$this->path.attach(get_thumb($v['img'], ''), 'property_s');
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
    	
    	foreach($cityList as $k => $v){
    			$area_sting.= $v['id'].',';
    	}
    	
    	$cityList[] = array('id'=>substr($area_sting,0,-1),'name'=>'全部');
    	$info['city'] = $cityList;

    	$info['kefu_tel']=C('pin_kefu_tel');
    
    //	print_r($info);die();
    
    	$this->ajaxReturn(200,L('app_requre_normal'),$info);
    
    }
    
    
    /**
     +----------------------------------------------------------
     * 推送消息
     +----------------------------------------------------------
     */
    public function Push_message (){

    	D('app_push')->customizedCast('4830','通知描述 ','标题',1);
    	
    }
    
    
    
    
    
    
    
    
    
    
    
    
	
 }