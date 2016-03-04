<?php
// +----------------------------------------------------------------------
// | fangpinhui [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014.8.22-3000.8.22 http://www.fangpinhui.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.fangpinhui.com/index.php )
// +----------------------------------------------------------------------
// | Author: gyw <geyouwen@fangpinhui.com>
// +----------------------------------------------------------------------
class app_forfkb_beta1Action extends baseAction {
		
/*	   protected $path = 'http://www.fangpinhui.com';
	   protected $local_path = 'http://www.fangpinhui.com';*/
	   protected $path = 'http://www.geyouwen.corp.com';
	   protected $local_path = 'http://www.geyouwen.corp.com';
       private   $level, $buyingmotive;

	   
	/**
    +----------------------------------------------------------
    * 初始化
    +----------------------------------------------------------
   */
	   
	public function _initialize(){
		parent::_initialize();
	    header("Content-Type:text/html; charset=utf-8");
        $this->level = ['1'=>'A', '2'=>'B', '3'=>'C', '4'=>'D'];
        $this->buyingmotive = ['1'=>'自住', '2'=>'投资', '3'=>'刚需', '4'=>'其他'];
	   // $_POST['token']!=md5('home_app'.date('Y-m-d',time()).'#$@%!*') && $this->ajaxReturn(51,L('app_token'));    
	  }



	/**
	用户登录
	*URL  =  http://192.168.1.115/fangpinhui/index.php?g=home&m=app_forfkb_beta1&a=login
	@params  mobile(string)  必填
	@params  password(string)   必填
	@params  equipment_number(string)
	返回值 uuid(string)  id(int)  mobile(string) username(string)
	*/
	  public function login(){
	  	$data=json_decode($_POST['params'],TRUE);
	  	/*$data = array(
	  			'mobile' =>'13955198874',
	  			'password' =>'yan123'
	  			//'equipment_number' => 'B94ABCB84BEC445687ECD609454F14F7',
	  		);*/
	  	!$data['mobile'] && $this->ajaxReturn(51,L('app_mobile_empty'));
	  	!$data['password'] && $this->ajaxReturn(51,L('app_password_empty'));
	  	!checkMobile($data['mobile']) && $this->ajaxReturn(51,L('app_mobile_format'));

	  	$user = M('admin')->field('id,username')->where(array('password'=>md5($data['password']),'mobile'=>$data['mobile'], 'status'=>1))->find();
	  	
	  	if(!$user){
	  		//echo 'aaa';
	  		$this->ajaxReturn(51,L('login_error'));exit;
	  	}
		M('admin')->where(array('id'=>$user['id']))->save(array('last_time'=>time(),'last_ip'=>$data['last_ip']));//原表记录一条登录记录

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
		if(M('app_fkb_login')->where(array('equipment_number'=>$data['equipment_number'],'uid'=>$user['id'],'status'=>1))->count('id')){
		   M('app_fkb_login')->where(array('equipment_number'=>$data['equipment_number']))->save(array('last_time'=>time(),'uuid'=>$res_data['uuid']));
		   $this->ajaxReturn(200,L('login_successe'),$total_data);
		}else{
			$res_data['uid']=$user['id'];
			$res_data['equipment_number']=$data['equipment_number'];
			$res_data['equipment_type']=$data['equipment_type'] = 1;
			$res_data['last_time']=time(); 
			M('app_fkb_login')->add($res_data) ? $this->ajaxReturn(200,L('login_successe'),$total_data) : $this->ajaxReturn(1003,L('login_error'));
		}
	}


/**
新建客户
*URL  =  http://192.168.1.115/fangpinhui/index.php?g=home&m=app_forfkb_beta1&a=register
*返回值  新客户ID
*/	
	  public function register(){
	  		$data=json_decode($_POST['params'],TRUE);
	  		/*$data['customer'] = 'gy1w111';
	  		$data['sexy'] = 1;   // 1 男   2女  
	  		$data['brokerid'] = 104;   //葛有文	  		
	  		$data['client'] = 2;	//终端设备  1 安卓  2 IOS  3 PC 4 其他*/

	  		!$data['customer'] && $this->ajaxReturn(51,L('not_empty_customer'));	  		
	  		$data['addtime'] = time();
	  		$mod = M('app_fkb_customer');
	  		$namerepeat = $mod->where(array('customer'=>$data['customer'],'brokerid'=>$data['brokerid'],'isdel'=>0))->field('id,customer')->select();
	  		if(!empty($namerepeat)){
	  			$count = count($namerepeat);
	  			$suggestname = $data['customer'].'_'.$count;
	  			$this->ajaxReturn(51,L('suggestname'),$suggestname);exit;
	  		}
	  		$res = $mod->data($data)->add();
	  		if($res){
	  			$back = $this->customerfollow('新建客户',$res,$data['brokerid']);
	  			if(!$back){
	  				$mod->delete($res);
	  			}
	  		}
            $res['customerId'] = $res;
            $return = ['customerId'=>(string)$res];
	  		$this->ajaxReturn(200,L('reg_successe'),$return);

	  }

/**
客户列表
*URL  =  http://192.168.1.115/fangpinhui/index.php?g=home&m=app_forfkb_beta1&a=get_my_customerlist	  
@params uid(int) 当前经纪人（登录终端人）的ID
@param int page 
@param int number_each_page
*/
	  public function get_my_customerlist(){
			$data=json_decode($_POST['params'],TRUE);  

			//$data['uid'] = 104;
			$page = $data['page'];
			$number_each_page=$data['number_each_page'];
			!isset($data['page']) && $this->ajaxReturn(51,'页号不能为空');
			!$data['number_each_page'] && $this->ajaxReturn(51,'页条数不能为空');

			
			$start = $page*$number_each_page;
			$where = 'fph_app_fkb_customer.isdel = 0 and fph_app_fkb_customer.brokerid ='.$data['uid'];
			$order = 'updatetime desc,addtime desc';
			$limit = $start.','.$number_each_page;  //
			$list = M('app_fkb_customer')->field('fph_app_fkb_customer.id,fph_app_fkb_customer.customer,fph_app_fkb_customer.tel,fph_app_fkb_customer.addtime,fph_app_fkb_customer.updatetime')->join('fph_app_fkb_wanttobuy as a on a.customerid=fph_app_fkb_customer.id')->where($where)->order($order)->limit($limit)->select();

			$mod  = M(NULL,NULL,C('DB_FKB'));
			$_mod = D('customer_behavior');
			//$mod = M('fkb_customer_behavior_analysis','fph_','mysql://root:123456@192.168.1.115:3306/fph_fkb_behavior_analysis');
			
			$limit  = ' limit 1';
			foreach ($list as $key => $value) {
					$list[$key]["result_average_price"] = $this->fieldanalysis($mod,'average_price',$value['id'],$limit); 
					$list[$key]["result_sumprice"] = $this->fieldanalysis($mod,'sumprice',$value['id'],$limit); 
					$list[$key]["result_housetype"] = $this->fieldanalysis($mod,'housetype',$value['id'],$limit);
					if(!empty($list[$key]["result_housetype"])){
						$list[$key]["result_housetype"] = explode('-', $list[$key]['result_housetype']) ;
						$list[$key]["result_housetype"] = $list[$key]['result_housetype'][0].'室'.$list[$key]['result_housetype'][1].'厅'.$list[$key]['result_housetype'][2].'卫';
					}else{
						$list[$key]["result_housetype"] = '';
					}
					
			}
			if(empty($list)){$list = array();}			
			$this->ajaxReturn(200,'OK',$list);
	  }

	
/**
根据ID获取客户基本信息
*URL  =  http://192.168.1.115/fangpinhui/index.php?g=home&m=app_forfkb_beta1&a=customerinfo
@params 	customerid(int) 必须
*/  
	  public function customerinfo(){
	  		$data = json_decode($_POST['params'],TRUE);
	  		$data['customerid'] = 48;
	  		!$data['customerid'] && $this->ajaxReturn(51,'参数不完整');		
	  		$info = M('app_fkb_customer')->where(array('id'=>$data['customerid']))->field('id,customer,tel,address,basicinfo,sexy')->find();
	  		if(empty($info)){
	  			 $this->ajaxReturn(51,L('customer_not_exsist'));
	  		}else{
	  			 $this->ajaxReturn(200,L('customer_info_success'),$info);
	  		}
	  }

	
/**
//根据ID修改客户信息
*URL  =  http://192.168.1.115/fangpinhui/index.php?g=home&m=app_forfkb_beta1&a=customerinfo_edit
@params 	customerid(int) 必须  customer(string) 必须	tel(string) 非必须  address(string) 非必须  basicinfo(string) 非必须
*/
	public function customerinfo_edit(){
			$data = json_decode($_POST['params'],TRUE);
			/*$data['customerid'] = 48;
			$data['customer'] = '网222';
			$data['tel'] = '13955198875';*/
			!$data['customer'] && $this->ajaxReturn(51,'error');
			if(mb_strlen($data['customer']) <=3){
				$this->ajaxReturn(51,L('请完善用户名'));exit;
			}
			
			if(!empty($data['tel'])){
				!checkMobile($data['tel']) && $this->ajaxReturn(51,L('tel_format_error'));	
			}
			$data['updatetime'] = time();  	//客户信息修改时间
			$reslut = M('app_fkb_customer')->data($data)->where(array('id'=>$data['customerid']))->save();			
			if($reslut){
				$info = M('app_fkb_customer')->where(array('id'=>$data['customerid']))->field('id,customer,tel,address,basicinfo,sexy')->find();
				$this->ajaxReturn(200,'ok',$info);
			}
	}  
	

	
/**
//客户购买意向信息
*URL  =  http://192.168.1.115/fangpinhui/index.php?g=home&m=app_forfkb_beta1&a=customer_wanttobuy
@params  customerid(int) 必须
*/
	public function customer_wanttobuy(){
			$data = json_decode($_POST['params'],TRUE);
			//$data['customerid'] = 48;
			!$data['customerid'] && $this->ajaxReturn(51,'参数不完整');	
			$info = M('app_fkb_wanttobuy')->where(array('customerid'=>$data['customerid']))->field('id,customerid,buyingmotive,level')->find();
            $info['buyingmotive'] = str_replace('1', '自住', $info['buyingmotive']);
			$info['buyingmotive'] = str_replace('2', '投资', $info['buyingmotive']);
			$info['buyingmotive'] = str_replace('3', '刚需', $info['buyingmotive']);
			$info['buyingmotive'] = str_replace('4', '其他', $info['buyingmotive']);

			if($info['level'] == 0) $info['level'] = '';
            if(intval($info['level']) > 0)
            $info['level'] = $this->level[$info['level']];
			//分析数据
			$limit = ' limit 3';
			$mod = M('customer_behavior','fph_',C('DB_FKB'));
			//$_mod = D('customer_behavior');
			$customerid = $data['customerid'];			        
			$datatmp['result_average_price'] = $this->fieldanalysis($mod,'average_price',$customerid,$limit);
			$datatmp['result_housetype'] = $this->fieldanalysis($mod,'housetype',$customerid,$limit);
			$datatmp['result_housetype'] = explode(',',$datatmp['result_housetype']);
			foreach ($datatmp['result_housetype'] as $key => $value) {
				if(!empty($datatmp['result_housetype'][$key])){
			    	$datatmp['result_housetype'][$key] = explode('-',$datatmp['result_housetype'][$key]);			    
			    	$datatmp['result_housetype'][$key] = $datatmp['result_housetype'][$key][0].'室'.$datatmp['result_housetype'][$key][1].'厅'.$datatmp['result_housetype'][$key][2].'卫';
			    }			    
			}
			$datatmp['result_housetype'] = implode(',', $datatmp['result_housetype']);
			$datatmp['result_sumprice'] = $this->fieldanalysis($mod,'sumprice',$customerid,$limit);
			$datatmp['result_property_type'] = explode(',',$this->fieldanalysis($mod,'property_type',$customerid,$limit));
			$datatmp['result_property_feature'] = explode(',',$this->fieldanalysis($mod,'property_feature',$customerid,$limit));
			//M('property','fph_','mysql://root:123456@192.168.1.115:3306/fangpinhui');
            $datatmp['result_city_id'] = $this->fieldanalysis($mod,'city_id',$customerid,$limit);
            $datatmp['result_city_id'] = explode(',',$datatmp['result_city_id']);
            foreach ($datatmp['result_city_id'] as $key => $value) {
                foreach (F('citylist') as $k => $v) {
                    if($value == $v['id']){
                        $datatmp['result_city_id'][$key] = $v['name'];
                    }
                }
            }
            $datatmp['result_city_id'] = implode(',',$datatmp['result_city_id']);

            $datatmp['result_area_id'] = $this->fieldanalysis($mod,'area_id',$customerid,$limit);
            $datatmp['result_area_id'] = explode(',',$datatmp['result_area_id']);
            foreach ($datatmp['result_area_id'] as $key => $value) {
                foreach (F('citylist') as $k => $v) {
                    if($value == $v['id']){
                        $datatmp['result_area_id'][$key] = $v['name'];
                    }
                }
            }
            $datatmp['result_area_id'] = implode(',',$datatmp['result_area_id']);
			$mod = M(NULL,NULL,C('DB_FPH'));
			$cate = array('property_type'=>1,'property_feature'=>12,'building_type'=>15,'decoration'=>23);
			$catelist = D('property')->property_cate($cate);
			foreach ($datatmp['result_property_type'] as $key => $value) {
			    foreach ($catelist['property_type'] as $k => $v) {
			          if($v['id'] ==$value){
			                $datatmp['result_property_type'][$key] = $v['name'];
			          }
			    }
			}
			foreach ($datatmp['result_property_feature'] as $key => $value) {
			    foreach ($catelist['property_feature'] as $k => $v) {
			          if($v['id'] ==$value){
			                $datatmp['result_property_feature'][$key] = $v['name'];
			          }
			    }
			}
			$datatmp['result_property_feature'] = implode(' 、  ', $datatmp['result_property_feature']);
			$datatmp['result_property_type'] = implode(' 、  ', $datatmp['result_property_type']);
			$info['analysis'] = $datatmp;
            $info['analysis']['buyingmotive'] = $info['buyingmotive'];
            $info['analysis']['level'] = $info['level'];
            unset($info['level']);
            unset($info['buyingmotive']);
			if(empty($info)){
	  			 $this->ajaxReturn(51,L('信息尚未完善'));
	  		}else{
	  			 $this->ajaxReturn(200, 'ok', $info);
	  		}
	}



/**
//添加陪同者信息 
*URL  http://192.168.1.115/fangpinhui/index.php?g=home&m=app_forfkb_beta1&a=partner_add
@params  customerid(int) 必须   partnername(string) 必须 	tel(string)非必须  sexy(string) 必须   relationship(string) 非必须
*/
    public function partner_add(){
    	$data = json_decode($_POST['params'],TRUE);
/*    	$data['customerid'] = 48;
    	$data['partnername'] = 王二1a1;
    	$data['tel'] = 13955198111;
    	$data['sexy'] = 1;
    	$data['relationship'] = '配偶';*/
    	!$data['customerid'] && $this->ajaxReturn(51,'参数不完整');	
    	!$data['partnername'] && $this->ajaxReturn(51,'陪同者姓名不能为空');
    	if($data['tel']){
    		$isexsist = D('app_fkb_partner')->partner_isexsist($data['customerid'],$data['tel']);
    		if($isexsist){
    			$this->ajaxReturn(51,'陪同者信息重复！');
    		}
    	}
    	$data['name'] = $data['partnername'];
    	$res = M('app_fkb_partner')->data($data)->add();
    	if($res){
    		$this->ajaxReturn(200,'陪同者信息添加成功！',(string)$res);
    	}
    }


    
/**
//客户浏览楼盘记录添加
*URL  http://192.168.1.115/fangpinhui/index.php?g=home&m=app_forfkb_beta1&a=viewed_add
@params customerid(int) 必须    property_id(int) 必须
返回值
{
    "status": 200,
    "msg": "浏览记录更新成功",
    "data": "",
    "dialog": ""
}

*/    
    public function viewed_add(){
    	$data = json_decode($_POST['params'],TRUE);
/*    	$data['customerid'] = 48;
    	$data['property_id'] = 17211;*/
    	$data['addtime'] = time();
    	!$data['customerid'] && $this->ajaxReturn(51,'参数不完整');	
    	if(D('app_fkb_viewed')->viewed_isexsist($data['customerid'],$data['property_id'])){
    		//存在更新时间即可
    		//$data['times'] = 'times +1';
    		M('app_fkb_viewed')->where(array('customerid'=>$data['customerid'],'property_id'=>$data['property_id']))->save(array('addtime'=>$data['addtime']));
    		M('app_fkb_viewed')->where(array('customerid'=>$data['customerid'],'property_id'=>$data['property_id']))->setInc('times');

    	}else{
    		M('app_fkb_viewed')->add($data);
    	}
    	$this->ajaxReturn(200,'浏览记录更新成功');

    }

    
/**
//楼盘浏览记录列表list
*URL  http://192.168.1.115/fangpinhui/index.php?g=home&m=app_forfkb_beta1&a=viewedlist
@params 	customerid(int) 必须
*/    
    public function viewedlist(){
    	$data = json_decode($_POST['params'],TRUE);
    	$data['customerid'] = 48;
    	$page = $data['page'] = 0;
		$number_each_page=$data['number_each_page'] = 3;
		$start = $page*$number_each_page;
		!isset($data['page']) && $this->ajaxReturn(51,'页号不能为空');
		!$data['number_each_page'] && $this->ajaxReturn(51,'页条数不能为空');
		$list = M('app_fkb_viewed')->field('id,property_id,addtime,times')->where(array('customerid'=>$data['customerid']))
								->order('addtime desc')
								->limit($start,$number_each_page)
								->select();
								//print_r($list);
        $ids = implode(',',array_column($list, 'property_id'));
        $propertyTitle = M('property')->where('id in ('.$ids.')')->field('id,title')->select();
        foreach($list as $key =>$val)
        {
            foreach($propertyTitle as $k => $v)
            {
                if($val['property_id'] == $v['id'])
                    $list[$key]['title'] = $v['title'];
            }
        }
		if($list){
			$this->ajaxReturn(200,'楼盘浏览记录列表',$list);							
		}else{
			$this->ajaxReturn(51,'没有浏览过楼盘');							
		}
		

    }

    
/**
//客户添加收藏 取消收藏 app_fkb_brokersuggest
*URL  http://192.168.1.115/fangpinhui/index.php?g=home&m=app_forfkb_beta1&a=collect_add
@params  customerid(int) 必须    property_id(int) 必须
返回值
{
    "status": 200,
    "msg": "收藏成功",
    "data": "",
    "dialog": ""
}
*/    
    public function collect_add(){
    	$data = json_decode($_POST['params'],TRUE);
/*    	$data['customerid'] = 48;
    	$data['property_id'] = 17;*/
    	$type = 1;
    	!$data['customerid'] && $this->ajaxReturn(51,'客户参数错误');    	
    	!$data['property_id'] && $this->ajaxReturn(51,'楼盘参数错误');
    	$backtype = D('app_fkb_brokersuggest')->collect_isexsist($data['customerid'],$data['property_id']);
    	if($backtype == 1){
    		D('app_fkb_brokersuggest')->collect_cancle($data['customerid'],$data['property_id']);
    		$this->ajaxReturn(51,'取消收藏',1);
    	}
    	if(empty($backtype)){
    		$back_id = D('app_fkb_brokersuggest')->collect_isdel($data['customerid'],$data['property_id']);
    		if(empty($back_id)){
    			$res = D('app_fkb_brokersuggest')->propertycollect_add($data['customerid'],$data['property_id'],$type);
    		}else{
    			 D('app_fkb_brokersuggest')->change_collect_status($data['customerid'],$data['property_id']);
    	    }    		
    		$this->ajaxReturn(200,'收藏成功');
    	}
    	if($backtype ==2){
    		$this->ajaxReturn(51,'楼盘已经被推荐，无需收藏',2);
    	}
    	
    }

/**
客户信息完善度统计
*URL  http://192.168.1.115/fangpinhui/index.php?g=home&m=app_forfkb_beta1&a=get_coustomer_source_rate
@params customer_id(int) 必须
*/
    public function get_coustomer_source_rate(){
          $data = json_decode($_POST['params'],TRUE);
          //$data['customer_id'] = 49;
          !$data['customer_id'] && $this->ajaxReturn(51,'客户参数错误');          
          $rate = D('app_fkb_customer')->get_customer_info_rate($data['customer_id']);
          $this->ajaxReturn(200,'客户信息完整度统计',(string)($rate*100));
    }
    
    

	//添加客户跟进操作
	public function customerfollow_add(){
		$data = json_decode($_POST['params'],TRUE);
/*    	$data['customerid'] = 48;
    	$data['content'] ='你好啊';*/
    	$data['operator'] = $data['brokerid'];
    	!$data['customerid'] && $this->ajaxReturn(51,'客户参数错误');
    	!$data['content'] && $this->ajaxReturn(51,'跟进内容不能为空');
    	$id = $this->customerfollow($data['content'],$data['customerid'],$data['operator']);
    	$this->ajaxReturn(200,'跟进记录更新成功',$id);
	}	 


/**
客户筛选条件读取
*URL  http://192.168.1.115/fangpinhui/index.php?g=home&m=app_forfkb_beta1&a=getsearch_options
*/
	public function getsearch_options(){
		$citylist = F('citylist');
		$newcitylist = '';
		$i = 0;
		foreach ($citylist as $key => $value) {
				if($value['pid'] ==0){
					 $newcitylist[$i] =  $citylist[$key];					 
					 $i++;					
				}
		}		
		$buyingmotivelist = array(
                        '0' =>array('id'=>1,'name'=>'自住'),
                        '1' =>array('id'=>2,'name'=>'投资'),
                        '2' =>array('id'=>3,'name'=>'刚需'),
                        '3' =>array('id'=>4,'name'=>'其他'),
            );		//购买动机

		$getpricesum = D('app_fkb_customer')->price_sum_bycity(); 
		$average_price = D('app_fkb_customer')->average_price_bycity();
		$data['citylist'] = $newcitylist;
		$data['buyingmotivelist'] = $buyingmotivelist;
		$data['getpricesum'] = $getpricesum;
		$data['average_price'] = $average_price;
		$cate = array('property_type'=>1,'property_feature'=>12,'building_type'=>15,'decoration'=>23);
        $catelist = D('property')->property_cate($cate);
        $data['property_feature'] = $catelist['property_feature'];
        $data['property_type'] = $catelist['property_type'];
        $this->ajaxReturn(200,'筛选条件',$data);
	}

	
/**
//根据所选城市查询户型
*URL  http://192.168.1.115/fangpinhui/index.php?g=home&m=app_forfkb_beta1&a=gethousetypebycity
@params city_id(int)  必须
*/	
    public function gethousetypebycity(){
    	   $data = json_decode($_POST['params'],TRUE);
    	   $select_city_id = $data['city_id'];
    	   !$data['city_id'] && $this->ajaxReturn(51,'请选择城市');           
            if(!$select_city_id){
                $this->ajaxReturn(51,'请选择城市');exit;
            }
            $list = M('property_housetype')->where('pid in (select id from fph_property where city_id in(select id from fph_city where id = '.$select_city_id.' or spid RLIKE "[[:<:]]'.$select_city_id.'[[:>:]]"))')->field('house_room,house_hall,house_wc')->select();
           foreach ($list as $key => $value) {
                   $list[$key] = $value['house_room'].','.$value['house_hall'].','.$value['house_wc'];
               }
            $list = array_flip(array_flip($list));     //去除数组中重复的元素
            asort($list);
            $i =0;
            $tmp = '';
            foreach($list as $k=>$v){
                $list[$k] = explode(',', $v);    //室 厅 卫
                foreach($list[$k] as $kk)
                {
                    $tmp[$i]['room'] = $list[$k][0];
                    $tmp[$i]['hall'] = $list[$k][1];
                    $tmp[$i]['wc'] = $list[$k][2];
                }
                $i++;
            }
        if(!empty($list)){
                $this->ajaxReturn(200,'操作成功！',$tmp); exit;
           }else{
                $this->ajaxReturn(51,'没有数据');exit;
           }
           
    }   

	
/**
//模糊搜索楼盘
*URL  http://192.168.1.115/fangpinhui/index.php?g=home&m=app_forfkb_beta1&a=search_property
@params  property(string) 必须
返回值
{
    "status": 200,
    "msg": "",
    "data": {
        "0": {
            "id": "370",
            "title": "城建 爱上海",
            "address": "上海市金山区石化邮政局10米"
        },
        "count": 1
    },
    "dialog": ""
}
*/
    public function search_property(){
    	$data = json_decode($_POST['params'],TRUE); 
    	$property = $data['property'];
        if(empty($property)){
             $this->ajaxReturn(51,'请输入要推荐的楼盘！');
        }
        $list = M('property')->where('title like "%'.$property.'%"')->field('id,title,address,latitude')->select();
        !$list && $this->ajaxReturn(51,'没有符合的楼盘');
        foreach($list as $key =>$val)
        {
            $tmp = explode(',', $val['latitude']);
            $list[$key]['la_x'] = $tmp[0];
            $list[$key]['la_y'] = $tmp[1];
            unset($list[$key]['latitude']);
        }
        $list['count'] = count($list);

        $this->ajaxReturn(200,'',$list);           
    }

    
/**
//获取楼盘详情
*URL  http://192.168.1.115/fangpinhui/index.php?g=home&m=app_forfkb_beta1&a=getproperty_detail
@params  property_id(int) 必须
*/    
    public function getproperty_detail(){
    	$data = json_decode($_POST['params'],TRUE);
    	$data['property_id'];
    	!$data['property_id'] && $this->ajaxReturn(51,'楼盘参数错误');
    	$fields = 'id,title,sub_title,prefer,item_price,open_time,building_type,bus,property_feature,feature,metro,elevated,sales,check_time,payment,info,property_type,business,property_age,decoration,volume_rate,green_rate,gouseholds,floors,progress,property_costs,propert_company,parking,parking_ratio';
    	$info = M('property')->field($fields)->where('id ='.$data['property_id'])->find();
    	$info['housetype'] = M('property_housetype')->where('pid ='.$data['property_id'])->field('house_name,house_img,house_area,house_room,house_hall,house_wc')->select();
    	$info['activities'] = M('article')->field('id,title')->where('pid ='.$data['property_id'])->order('add_time desc')->select();
    	$cate = array('property_type'=>1,'property_feature'=>12,'building_type'=>15,'decoration'=>23);
        $catelist = D('property')->property_cate($cate);
        $info['property_feature'] = explode(',',$info['property_feature']);
        $info['property_type'] = explode(',',$info['property_type']);
        foreach ($info['property_feature'] as $key => $value) {
        		foreach ($catelist['property_feature'] as $k => $v) {
        			  if($value == $v['id']){
        			  		$info['property_feature'][$key] =$v['name'];
        			  }
        		}        		
        }
        foreach ($info['property_type'] as $key => $value) {
        		foreach ($catelist['property_type'] as $k => $v) {
        			  if($value == $v['id']){
        			  		$info['property_type'][$key] =$v['name'];
        			  }
        		}        		
        }    	
    	$info['property_imgs'] = M('property_img')->where('pid ='.$data['property_id'])->field('status,img')->order('status asc')->select();
        $imgType = ['1' => 'xiaoguo', '2' => 'guihua', '3' => 'peitao', '4' => 'shijing', '5' => 'jiaotong', '6' => 'yangban' ];
        foreach($imgType as $key)
        {
            $info['property_imgs'][$imgType[$key]][] = array();
        }
    	foreach ($info['property_imgs'] as $key => $value)
        {
          $info['property_imgs'][$imgType[$value['status']]][] = $value['img'];
          unset($info['property_imgs'][$key]);
    	}
    	$this->ajaxReturn(200,'楼盘详情数据',$info);


    }


	 //客户操作跟进记录
	  public function customerfollow($content,$customerid,$brokerid){
	        $data = array(
	              'content' => $content,				//跟进内容
	              'customerid' =>$customerid,			//跟进客户
	              'operator' =>$brokerid,				//当前软件使用者  经济人
	              'addtime' =>time(),
	          );
	        $back = M('app_fkb_brokerfollow')->data($data)->add();
	        return $back;
	  }

	/**
  客户筛选数据分析
  */
  public function fieldanalysis($mod,$fieldname,$customer_id,$limit){
          $sql_average_price = 'SELECT '.$fieldname.',COUNT(id) AS tp_count,addtime FROM `fph_customer_behavior` WHERE customer_id ='.$customer_id.' GROUP BY '.$fieldname.' order by tp_count desc,addtime desc'.$limit;
          $result_average_price =  $mod->query($sql_average_price);

          if(!empty($limit)){
               foreach ($result_average_price as $key => $value) {
                  unset($result_average_price[$key]['tp_count']);
                  unset($result_average_price[$key]['addtime']);
                  $result_average_price[$key] = $value[$fieldname];
               }
               return $result_average_price = implode(',',$result_average_price);        
          }else{
               return $result_average_price;        
          }    
         
      }

	
 }