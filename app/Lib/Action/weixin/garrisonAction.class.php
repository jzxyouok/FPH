<?php
class garrisonAction extends weixin_userbaseAction {
    
    public function _initialize() {
		parent::_initialize();
		$this->AppID     = C('AppID');
		$this->AppSecret = C('AppSecret');
	}

	public function index(){
		$uid  = $this->visitor->info['id'];
		$fph  = C('DB_PREFIX');
		$time = time(); 
		//当前驻守的楼盘
		$stat_property = M('user')->where(array('id'=>$uid))->getfield('stat_property');
		$where = 'status = 1';
		if($stat_property){
			$where .= ' AND id in ('.$stat_property.')';
		}else{
			$where .= ' AND id in (0)';
		}
		
		$list =  M('property')->field('id,title,img_thumb,prefer,city_id,item_price,list_price')->where($where)->order($order)->select();
		foreach($list as $k=>$v){ 
			//区域
			$listspid = M('city')->field('id,pid,spid,name')->where('id ='.$v['city_id'])->find();
			$count_spid = explode('|',$listspid['spid']);
			if(count($count_spid) == 3){
				$list[$k]['area'] = M('city')->where('id ='.$listspid['pid'])->getField('name');
				$list[$k]['area'] .= '  '.$listspid['name'];
			}else if(count($count_spid) == 4){
				$list[$k]['area'] = M('city')->where('id ='.$listspid['pid'])->getField('name');
				$list[$k]['area'] .= '  '.M('city')->where('id ='.$listspid['id'])->getField('name');
			}else{
				$list[$k]['area'] = M('city')->where('id ='.$listspid['id'])->getField('name');
			}

			//标签 pid 1 为合作
			$bool = M('property_cooperation')->where('term_start < "'.$time.'" AND term_end > "'.$time.'" AND pid ='.$v['id'])->getfield('id');
			$list[$k]['pid'] =1;
			if(empty($bool)){
				$list[$k]['pid'] = 0;
			}			
		}
		//print_r($list);
		$this->assign('list', $list);

		$this->assign('setTitle', '驻守楼盘');
		$this->_config_seo();
		$this->display();
	}

	//门店详情
	public function edit(){
		$keyword = $this->_get('keyword','trim');
		$openid  = $this->_get('openid','trim');
		$uid     = $this->visitor->info['id'];
		
		//获取微信用户openid
    	if(!$openid){
	    	load("@.wechat_functions");
			$code = $this->_get('code','trim');
			if(!$code){
				$url='http://www.fangpinhui.com/?g=weixin&m=garrison&a=edit&keywoerd='.$keyword.'&openid='.$openid.'';
				redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->AppID.'&redirect_uri='.urlencode($url).'&response_type=code&scope=snsapi_base&state=123#wechat_redirect');
				exit;
			}
			$get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->AppID.'&secret='.$this->AppSecret.'&code='.$code.'&grant_type=authorization_code';
			$json_obj = json_decode(httpGet($get_token_url),true);
			$openid = $json_obj['openid'];
		}
		
		//$openid = 'opxklt1cXqYFy9NXKcNg5pV1HUnc';//测试
		//获取当前所在经纬度
		$location = M('user_location')->field('latitude,longitude')->where("openid='".$openid."'")->find();
		$lat = $location['latitude'] ;//当前纬度gps
		$lng = $location['longitude'];//当前经度gps
		

		$where = 'status = 1';
		if($keyword){
			$where .= " AND title like '%".$keyword."%' OR address like '%".$keyword."%'";
		}
		$order = 'ordid ASC,add_time DESC';
		$list =  M('property')->field('id,title,item_price,list_price,latitude')->where($where)->order($order)->limit(0,10)->select();

		if($location){
			foreach($list as  $k=>$v){		    			
				$latitude = explode(',', $v['latitude']);
				$q        = "http://api.map.baidu.com/geoconv/v1/?coords={$lng},{$lat}&from=1&to=5&ak=GD570cL3U7ZGAGjdoMY2m64R";
			    $result   = json_decode(file_get_contents($q));	
			    $lng1     = $result->result[0]->x;
	            $lat1     = $result->result[0]->y;
				$distance = getDistance($lat1,$lng1,$latitude[1],$latitude[0]);
				$distance = $distance/1000;
				$list[$k]['distance'] = round($distance,1);
			}
			$list = $this->ARRAY_sort_by_field($list,'distance');
		}
		$this->assign('list', $list);

		$stat_property = M('user')->where(array('id'=>$uid))->getfield('stat_property');
		$this->assign('stat_property', $stat_property);

		$this->assign('openid', $openid);
		$this->assign('keyword', $keyword);
		$this->assign('countlp', count($list));
		$this->assign('setTitle', '选择楼盘');
		$this->_config_seo();
		$this->display();
	}

	//下拉加载楼盘
	public function ajax_edit_list(){
		$keyword = $this->_post('keyword','trim');
		$openid  = $this->_post('openid','trim');
		$uid     = $this->visitor->info['id'];
		$page    = $this->_post('page','intval');
		$start   = $page * 10;

		!$openid && $this->ajaxReturn(0,'系统参数出错');
		!$page && $this->ajaxReturn(0,'页码错误');

		//当前驻守楼盘
		$stat_property = M('user')->where(array('id'=>$uid))->getfield('stat_property');
		$stat_property = explode(",", $stat_property);
		//$this->ajaxReturn(0,$stat_property);

		//$openid = 'opxklt1cXqYFy9NXKcNg5pV1HUnc';
		//获取当前所在经纬度
		$location = M('user_location')->field('latitude,longitude')->where("openid='".$openid."'")->find();
		$lat = $location['latitude'] ;//当前纬度gps
		$lng = $location['longitude'];//当前经度gps
		

		$where = 'status = 1';
		if($keyword){
			$where .= " AND title like '%".$keyword."%' OR address like '%".$keyword."%'";
		}
		$order = 'ordid ASC,add_time DESC';
		$list =  M('property')->field('id,title,item_price,list_price,latitude')->where($where)->order($order)->limit($start,10)->select();

		if($location){
			foreach($list as  $k=>$v){		    			
				$latitude = explode(',', $v['latitude']);
				$q        = "http://api.map.baidu.com/geoconv/v1/?coords={$lng},{$lat}&from=1&to=5&ak=GD570cL3U7ZGAGjdoMY2m64R";
			    $result   = json_decode(file_get_contents($q));	
			    $lng1     = $result->result[0]->x;
	            $lat1     = $result->result[0]->y;
				$distance = getDistance($lat1,$lng1,$latitude[1],$latitude[0]);
				$distance = $distance/1000;
				$list[$k]['distance'] = round($distance,1);
			}
			$list = $this->ARRAY_sort_by_field($list,'distance');
		}

		$str = '';
		foreach($list as $key=>$val){			
			foreach($stat_property as $v){
				$checked = '';
				if($v == $val['id']){
					$checked = 'class="J_select_add cur"';
					break;
				}else{
					$checked = 'class="J_select_remove"';
					//break;
				}				
			}
			$str .= '<li rel="'.$val['id'].'">';
			  if($val['distance']){
              	$str .= '<h2>'.$val['title'].' <span>&lt;'.$val['distance'].'km</span></h2>';
              }
              $str .= '<span class="address">';
              	if($val['item_price']){
              		$str .= '均价'.$val['item_price'].'/元平米';
              	}
              	if($val['list_price']){           
                	$str .= ' 佣金'.$val['list_price'].'元起';
                }
              $str .= '</span>';
              $str .= '<button '.$checked.'>选择</button>';
            $str .= '</li>';
		}
		if($str){
		    $this->ajaxReturn(1,'Success',$str);
	    }else{
		    $this->ajaxReturn(0,'别滑动了，已经到底了...');
	    }
	}

	//保存选择的驻守楼盘
	public function submit_save(){
		$ids = $this->_post('ids','trim');
		!$ids && $this->ajaxReturn(0,'请选择驻守楼盘');
		$uid = $this->visitor->info['id'];
		if(false !== M('user')->where(array('id'=>$uid))->save(array('stat_property'=>$ids))){
			$this->ajaxReturn(1,'保存成功');
		}else{
			$this->ajaxReturn(0,'保存失败');
		}
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
	

	
	
	
	
}