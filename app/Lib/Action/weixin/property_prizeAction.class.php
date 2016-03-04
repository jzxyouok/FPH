<?php
class property_prizeAction extends frontendAction {
	public function _initialize() {
		parent::_initialize();
		$this->_mod = D('property');//楼盘
		$this->_mod_z = D('property_prize');//带看奖
	}
	public function index(){
		$uid = $this->visitor->info['id'];
		$search = array();
		$time = time();
		$where = ' A.status = 1 AND B.prize!="" AND B.time_start<'.$time.' AND B.time_end > '.$time;
		$fph = C('DB_PREFIX');
		$select_city = $this->_get('select_city','intval');
		$select_name = $this->_get('select_name','trim');
		$str_pro = '';
		if($select_city){
		    $search['select_city'] = $select_city;
		    $search['select_name'] = $select_name;
		    $select_city_id = $select_city;
		    if($select_city==803){
		    	$str_pro = ' OR id=802 ';
		    }
		}else{
			//获取当前所在城市 
			vendor('Ip.IpLocation', '', '.class.php');
			$Ip = new IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
			$ip_addr = get_client_ip();
		    //F($ip_addr,null);
		    //获取缓存数据
		    $location=F($ip_addr);
		    if(!$location){
		    	F($ip_addr,$Ip->getlocation($ip_addr));
		    	$location=F($ip_addr);
		    }
			$city_name = preg_replace("/^[^省]*省/",'',$location['country']);
			$city_info = M('city')->field('id,name')->where("name like '%".$city_name."%'")->find();
			if($city_info['id']!=803 && $city_info['id']!=22 && $city_info['id']!=859 && $city_info['id']!=867 && $city_info['id']!=982 && $city_info['id']!=1533 && $city_info['id']!=824){
				$search['select_name'] = '上海市';
			    $search['select_city'] = 803;
			    $select_city_id = 803;
			    $str_pro = ' OR id=802 ';
			}else{
				$search['select_city'] = $city_info['id'];
				$search['select_name'] = $city_info['name'];
				$select_city_id = $city_info['id'];
			}
		}
		//是否搜索
		$search_key_status = $this->_get('search_key_status','intval');
		if($search_key_status){
			$search['search_key_status'] = 1;
		}
		//筛选数据
		//区域
		$search_shai_city = $this->_get('search_shai_city','intval');
		if($search_shai_city){
		    $select_city_id = $search_shai_city;
		    $search['search_shai_city'] = $search_shai_city;
		}
		//板块
		$search_ban_city = $this->_get('search_ban_city','intval');
		$search['search_ban_city_name'] ='';
		if($search_ban_city){
		    $select_city_id = $search_ban_city;
		    $search['search_ban_city'] = $search_ban_city;
		    $search['search_ban_city_name'] =  $this->_get('search_ban_city_name','trim');
		}
		$order = 'A.id DESC';
		//排序
        $search_status_name =  $this->_get('search_status_name','intval');
        if($search_status_name){
            // if($search_status_name==3){
            // 	$order = 'B.prize DESC,A.id DESC ';
            // }
            $search['search_status_name'] = $search_status_name;
        }
        $page = '';
		//微信端获取经纬度
		$open_id = $this->get_openid($page,$select_city,$select_name,$search_shai_city,$search_ban_city,$search_ban_city_name,$search_status_name);
		//根据open_id获取经纬度 longitude经度 latitude 纬度 
		$j_w_d = M('user_location')->field('openid,latitude,longitude')->where("openid='".$open_id."'")->find();
		$lat1 = $j_w_d['latitude'] ;//当前纬度gps
		$lng1 = $j_w_d['longitude'];//当前经度gps
		$where .=' AND A.city_id in(select id from fph_city where id = '.$select_city_id.$str_pro.' or spid RLIKE "[[:<:]]'.$select_city_id.'[[:>:]]")';
		$where_user .= ' AND B.stores_id !="" ';
		$where_n .= ' AND B.stores_id ="" ';

		//用户关联渠道对应的楼盘
		$list_user = $this->_mod->field('A.id,A.title,A.img_thumb,A.item_price,A.list_price,A.city_id,A.latitude,B.prize,B.time_start,B.time_end,B.stores_id')
		    ->table("{$fph}property AS A
			    LEFT JOIN {$fph}property_prize AS B ON A.id = B.pid")
		    ->where($where.$where_user)
		    ->order($order)
		    ->group('A.id')
		    ->select();
		    foreach($list_user as  $k=>$v){
		    	if($uid){
		 			$stores_id = M('user')->where('id='.$uid.' AND stores_id in('.$v['stores_id'].')')->getField('stores_id');
		 		}
		 		if($stores_id){
		 			$list_user[$k]['prize'] = $v['prize'];
		 		}
			 	if(!$list_user[$k]['prize']){
				 	$list_user[$k]['prize'] = 0;
				}
				//标签 pid 1 为合作
				$bool = M('property_cooperation')->where('term_start < "'.$time.'" AND term_end > "'.$time.'" AND pid ='.$v['id'])->find();
				$list_user[$k]['pid'] =1;
				if(empty($bool))
				$list_user[$k]['pid'] = 0;
				$latitude = explode(',', $v['latitude']);
	            $q = "http://api.map.baidu.com/geoconv/v1/?coords={$lng1},{$lat1}&from=1&to=5&ak=GD570cL3U7ZGAGjdoMY2m64R";
	            $result = json_decode(file_get_contents($q));
	            $lng1 = $result->result[0]->x;
	            $lat1 = $result->result[0]->y;
				$distance = getDistance($lat1,$lng1,$latitude[1],$latitude[0]);
				$distance = $distance/1000;
				$list_user[$k]['distance'] = round($distance,1);
				if(!$stores_id){
					unset($list_user[$k]);
				}
		    }

		$list = $this->_mod->field('A.id,A.title,A.img_thumb,A.item_price,A.list_price,B.prize,A.city_id,A.latitude,B.time_start,B.time_end,B.stores_id')
		    ->table("{$fph}property AS A
			    LEFT JOIN {$fph}property_prize AS B ON A.id = B.pid")
		    ->where($where.$where_n)
		    ->order($order)
		    ->limit(0,6)
		    ->group('A.id')
		    ->select();
		    $search['count'] = count($this->_mod->table("{$fph}property AS A
			    LEFT JOIN {$fph}property_prize AS B ON A.id = B.pid")
		    ->where($where.$where_n)
		    ->group('A.id')
		    ->select())+count($list_user);
		
		foreach($list as $k=>$v){
			//是否有带看奖
			$prize = M('property_prize')->field('id,pid,prize,stores_id')->where('pid='.$v['id'].' AND stores_id="" AND time_start<'.$time.' AND time_end > '.$time)->order('id DESC')->find();
			$list[$k]['prize'] = $prize['prize'];
			
			 if(!$list[$k]['prize']){
			 	$list[$k]['prize'] = 0;
			 }
			//标签 pid 1 为合作
			$bool = M('property_cooperation')->where('term_start < "'.$time.'" AND term_end > "'.$time.'" AND pid ='.$v['id'])->find();
			$list[$k]['pid'] =1;
			if(empty($bool))
			$list[$k]['pid'] = 0;

			$latitude = explode(',', $v['latitude']);
            $q = "http://api.map.baidu.com/geoconv/v1/?coords={$lng1},{$lat1}&from=1&to=5&ak=GD570cL3U7ZGAGjdoMY2m64R";
            $result = json_decode(file_get_contents($q));
            $lng1 = $result->result[0]->x;
            $lat1 = $result->result[0]->y;
			$distance = getDistance($lat1,$lng1,$latitude[1],$latitude[0]);
			$distance = $distance/1000;
			$list[$k]['distance'] = round($distance,1);
		}
		if($search_status_name=='' || $search_status_name==2){
            $list = $this->ARRAY_sort_by_field($list,'distance');
            $list_user = $this->ARRAY_sort_by_field($list_user,'distance');
            $search['search_status_name'] = 2;
        }elseif($search_status_name==3){
            $list = $this->ARRAY_sort_by_field($list,'prize',true);
            $list_user = $this->ARRAY_sort_by_field($list_user,'prize',true);
            $search['search_status_name'] = 3;
        }

		//区域
		$shailist_city = M('city')->where('pid ='.$search['select_city'])->select();
		//区域显示
		$city = $this->_mod->field('A.city_id,B.prize,B.time_start,B.time_end,B.pid')
		    ->table("{$fph}property AS A
			    LEFT JOIN {$fph}property_prize AS B ON A.id = B.pid")
		    ->where('A.city_id !=0 AND A.id=B.pid AND B.prize!="" AND B.time_start<'.$time.' AND B.time_end >'.$time)->group('A.city_id')->select();
		$city_id = '';
		$id_str =array();
		$str = '';
		foreach($city as $k=>$v){
		   $str  = $this->get_city($v['city_id']);
           if($str){
              $id_str[]=$str;
           }
		}

		//分享
		$time = time();
		$this->assign('jssdk', A('weixin/jssdk')->getSignPackage());
		$this->assign('time', $time);
		
		$url = 'http://www.fangpinhui.com';
		$this->assign('url', $url);
		
		$city_id = array_unique($id_str);
		$citylist = M('city')->where('id in('.implode(',',$city_id).')')->order('FIELD(id ,'.$search['select_city'].') DESC ')->select();
		$this->assign('shailist_city', $shailist_city);
		$this->assign('search', $search);
		$this->assign('list_user', $list_user);
		$this->assign('list', $list);
		$this->assign('open_id', $open_id);
		$this->assign('countlp', count($list));
		$this->assign('citylist', $citylist);
		$this->assign('setTitle', '带看奖');
		$this->_config_seo();
		$this->display();
	}
	
    
	public function ajax_prize_index(){

		$uid = $this->visitor->info['id'];
		$search = array();
		$time = time();
		$where = ' A.status = 1 AND B.prize!="" AND B.time_start<'.$time.' AND B.time_end >'.$time;
		$fph = C('DB_PREFIX');
		$page = $this->_post('page','intval');
	    $select_city = $this->_post('select_city','intval');
	    $select_name = $this->_post('select_name','trim');
	    $search_shai_city = $this->_post('search_shai_city','intval');
	    $search_ban_city = $this->_post('search_ban_city','intval');
	    $search_ban_city_name = $this->_post('search_ban_city_name','trim');
	    $search_status_name = $this->_post('search_status_name','intval');
	    $search_key_status = $this->_post('search_key_status','intval');
	    $open_id = $this->_post('open_id','trim');
	    $start = $page*6;
		$str_pro = '';

		if($select_city){
		    $search['select_city'] = $select_city;
		    $search['select_name'] = $select_name;
		    $select_city_id = $select_city;
		    if($select_city==803){
		    	$str_pro = ' OR id=802 ';
		    }
		}else{
			//获取当前所在城市 
			vendor('Ip.IpLocation', '', '.class.php');
			$Ip = new IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
			$ip_addr = get_client_ip();
		    //F($ip_addr,null);
		    //获取缓存数据
		    $location=F($ip_addr);
		    if(!$location){
		    	F($ip_addr,$Ip->getlocation($ip_addr));
		    	$location=F($ip_addr);
		    }
			$city_name = preg_replace("/^[^省]*省/",'',$location['country']);
			$city_info = M('city')->field('id,name')->where("name like '%".$city_name."%'")->find();
			if($city_info['id']!=803 && $city_info['id']!=22 && $city_info['id']!=859 && $city_info['id']!=867 && $city_info['id']!=982 && $city_info['id']!=1533 && $city_info['id']!=824){
				$search['select_name'] = '上海市';
			    $search['select_city'] = 803;
			    $select_city_id = 803;
			    $str_pro = ' OR id=802 ';
			}else{
				$search['select_city'] = $city_info['id'];
				$search['select_name'] = $city_info['name'];
				$select_city_id = $city_info['id'];
			}
		}
		if($search_key_status){
			$search['search_key_status'] = 1;
		}
		//筛选数据
		//区域
		if($search_shai_city){
		    $select_city_id = $search_shai_city;
		    $search['search_shai_city'] = $search_shai_city;
		}
		//板块
		$search['search_ban_city_name'] ='';
		if($search_ban_city){
		    $select_city_id = $search_ban_city;
		    $search['search_ban_city'] = $search_ban_city;
		    $search['search_ban_city_name'] = $search_ban_city_name;
		}
		$order = 'A.id DESC';
		//排序
        if($search_status_name){
            // if($search_status_name==3){
            // 	$order = 'B.prize DESC,A.id DESC ';
            // }
            $search['search_status_name'] = $search_status_name;
        }
        //根据open_id获取经纬度 longitude经度 latitude 纬度 
		$j_w_d = M('user_location')->field('openid,latitude,longitude')->where("openid='".$open_id."'")->find();
		$lat1 = $j_w_d['latitude'] ;//当前纬度gps
		$lng1 = $j_w_d['longitude'];//当前经度gps
		$where_n .= ' AND B.stores_id ="" ';
		$where .=' AND A.city_id in(select id from fph_city where id = '.$select_city_id.$str_pro.' or spid RLIKE "[[:<:]]'.$select_city_id.'[[:>:]]")';
		$list = $this->_mod->field('A.id,A.title,A.img_thumb,A.item_price,A.list_price,A.city_id,A.latitude,B.prize,B.time_start,B.time_end,B.stores_id,B.pid')
		    ->table("{$fph}property AS A
			    LEFT JOIN {$fph}property_prize AS B ON A.id = B.pid")
		    ->where($where.$where_n)
		    ->order($order)
		    ->limit($start,6)
		    ->group('A.id')
		    ->select();
		    foreach($list as $k=>$v){
		    	//是否有带看奖
				$prize = M('property_prize')->field('id,pid,prize,stores_id')->where('pid='.$v['id'].' AND stores_id="" AND time_start<'.$time.' AND time_end > '.$time)->order('id DESC')->find();
				$list[$k]['prize'] = $prize['prize'];
				 
				 if(!$list[$k]['prize']){
				 	$list[$k]['prize'] = 0;
				 }
		    	//标签 pid 1 为合作
				$bool = M('property_cooperation')->where('term_start < "'.$time.'" AND term_end > "'.$time.'" AND pid ='.$v['id'])->find();
				$list[$k]['pid'] =1;
				if(empty($bool))
				$list[$k]['pid'] = 0;
		    	$latitude = explode(',', $v['latitude']);
	            $q = "http://api.map.baidu.com/geoconv/v1/?coords={$lng1},{$lat1}&from=1&to=5&ak=GD570cL3U7ZGAGjdoMY2m64R";
	            $result = json_decode(file_get_contents($q));
	            $lng1 = $result->result[0]->x;  
	            $lat1 = $result->result[0]->y;
				$distance = getDistance($lat1,$lng1,$latitude[1],$latitude[0]);
				$distance = $distance/1000;
				$list[$k]['distance'] = round($distance,1);
		    }
		    if($search_status_name=='' || $search_status_name==2){
	            $list = $this->ARRAY_sort_by_field($list,'distance');
	            $search['search_status_name'] = 2;
	        }elseif($search_status_name==3){
	            $list = $this->ARRAY_sort_by_field($list,'prize',true);
	            $search['search_status_name'] = 3;
	        }
			// $search['count'] = $this->_mod->table("{$fph}property AS A
			//     LEFT JOIN {$fph}property_prize AS B ON A.id = B.pid")
		 //    ->where($where)
		 //    ->count('A.id');
		//区域
		$shailist_city = M('city')->where('pid ='.$search['select_city'])->select();
		//区域显示
		$city = $this->_mod->field('A.city_id,B.prize,B.time_start,B.time_end,B.pid')
		    ->table("{$fph}property AS A
			    LEFT JOIN {$fph}property_prize AS B ON A.id = B.pid")
		    ->where('A.city_id !=0 AND A.id=B.pid AND B.prize!="" AND B.time_start<'.$time.' AND B.time_end >'.$time)->group('A.city_id')->select();
		$city_id = '';
		$id_str =array();
		$str = '';
		foreach($city as $k=>$v){
		   $str  = $this->get_city($v['city_id']);
           if($str){
              $id_str[]=$str;
           }
		}
		$city_id = array_unique($id_str);
		$citylist = M('city')->where('id in('.implode(',',$city_id).')')->order('FIELD(id ,'.$search['select_city'].') DESC ')->select();
		$str = '';
        foreach($list as $ke=>$vo){
			$str .= '<li><a href="'.U('weixin/loupan/detail',array('id'=>$vo['id'],'select_city'=>$search['select_city'],'select_name'=>$search['select_name'])).'"><figure>';
			if($vo['pid']==1){
				$str .='<i></i>';
			}
			$str .='<img src="'.get_fdfs_image($vo['img_thumb'], '_weixin_thumb').'" width="120" height="90"></figure>';
			$str .= '<section class="detail">';
			$str .= '<h3>'.$vo['title'].'</h3>';
			$str .= '<span class="region">';
			if($vo['item_price']){
				$str .=''.$vo['item_price'].'元/㎡';
			}else{
				$str .='价格待定';
			}
			$str .= '</span><span class="price">';
			if($vo['list_price']){
				$str .= '佣金'.$vo['list_price'].'起每套';
			}else{
				$str .= '佣金暂无';
			}
			$str .= '</span> </section>';
			$str .= '<section class="status "> <i class="PREMISES_STATUS">奖</i> <span class="deposit">';
			if($vo['prize']){
				$str .=''.$vo['prize'].'元每客';
			}else{
				$str .='带看奖待定';
			}
			$str .='</span> <span class="unit">'.$vo['distance'].'km</span> </section>
			</a> </li>';
        }
        if($str){
		    $this->ajaxReturn(1,'',$str);
	    }else{
		    $this->ajaxReturn(0,'别滑动了，已经到底了...');
	    }
	}
	//微信端获取经纬度
	public function get_openid($page,$select_city,$select_name,$search_shai_city,$search_ban_city,$search_ban_city_name,$search_status_name){
		$appid = "wx3ce1eceec205c6c4";  
		$secret = "6a377f78c74e13ff5e4e425af7b11ecc"; 
		$REDIRECT_URI='http://www.fangpinhui.com/weixin/property_prize/index'; 
		$scope='snsapi_base';
		$state=1;
		$code = $_GET["code"];
		$c 	  = '?page='.$page.'&select_city='.$select_city.'&select_name='.$select_name.'&search_shai_city='.$search_shai_city.'&search_ban_city='.$search_ban_city.'&search_ban_city_name='.$search_ban_city_name.'&search_status_name='.$search_status_name.'';
		if(!$code){
			$url='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.urlencode($REDIRECT_URI.$c).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
			header("Location:".$url);
		}
		$get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';	
		$json_obj = httppost($get_token_url);
		$access_token = $json_obj['access_token'];  
		$openid = $json_obj['openid'];
		return $openid;
		//echo $openid;exit;
	}

	//根据某字段按倒序/顺序排列
	function ARRAY_sort_by_field($arr_data, $field, $descending = false){  
        $arrSort = array();  
        foreach ($arr_data as $key => $value){  
           $arrSort[$key] = $value[$field];  
        }  
        if($descending){
           arsort($arrSort);  
        }else{  
           asort($arrSort);  
        }  
        $resultArr = array();  
        foreach ($arrSort as $key => $value){  
           $resultArr[$key] = $arr_data[$key];  
        }  
        return $resultArr;  
    }  
	 //根据区域获取 板块
    public function shai_city(){
		$shai_city = $this->_post('shai_city','intval');
		$city = M('city')->where('pid ='.$shai_city)->select();
		$str = '<option class="strnull" value="">不限</option>';
		foreach($city as $k=>$v){
		    $str .= '<option id="shai_ban_id'.$v['id'].'" value="'.$v['id'].'">'.$v['name'].'</option>';
		}
		if($str){
			    $this->ajaxReturn(1,'',$str);
		}else{
			$this->ajaxReturn(0,'暂无数据');
		}
    }

	//获取对应城市ID
	public function get_city($id){
		$str='';
		$info = M('city')->field('id,pid,name,spid')->where('id='.$id)->find();
		$spid = $info['spid'];
		$arr_spid  =explode('|', $spid);
		$count = count(explode('|', $spid));
		if($count==2){
		     $str = $info['id'];
		}elseif($count ==3){
		     $str =M('city')->field('id')->where('id='.$info['pid'])->getField('id'); 
		}elseif($count >=4){
		     $str =M('city')->field('id')->where('id='.$arr_spid[1])->getField('id');
		}
		return $str;
    }
	
	
	
	
	
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}