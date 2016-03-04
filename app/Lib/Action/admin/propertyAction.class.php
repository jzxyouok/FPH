<?php
class propertyAction extends backendAction
{
    public function _initialize() {
	    parent::_initialize();
	    $this->_mod = D('property');
	    $this->_cate_mod = D('property_cate');
        $this->RedisDataBase= C('DB_REDIS_PROPERTYMQ');
		$this->RedisDataBaseEXPENSES= C('DB_REDIS_PROPERTY_EXPENSES');
		$this->redisSuggest= C('DB_REDIS_PROPERTY_SUGGEST');
		$this->redisSuggestKey = 'property.suggest.';
    }

    /*
    *@Descriptions：楼盘显示
    *@Date:2014-11-19
    *@Author: wsj
    */
    public function index() {
    	$fph = C('DB_PREFIX');
    	$p = $this->_get('p','intval',1);
    	$where = '1=1';
    	$property_type = $this->_get('property_type','intval');
    	$status = $this->_get('status','intval');
    	$title = htmltag($this->_get('title','trim'));
    	$time_start = $this->_get('time_start', 'trim');
    	$time_end = $this->_get('time_end', 'trim');
    	$cooperation = $this->_get('cooperation', 'trim');
    	$b_head = htmltag($this->_get('b_head','trim'));
    	$select_city_id = $this->_get('city_id','intval');
        $activity          = $this->_get('activity','intval');
    	$select_city_spid = '';
    	$time = time();

    	if(!empty($property_type)){
    	    $where .= ' AND find_in_set('.$property_type.',A.property_type)';
    	}

    	if(!empty($title)){
    	    $where .=' AND A.title like "%'.$title.'%"';
    	}

    	if($status === 1 or $status === 0){
    	    $where .= ' AND A.status ='.$status;
    	}

    	if(!empty($time_start) AND !empty($time_end)){
    	    $where .=' AND A.add_time between '.strtotime($time_start).' AND '.(strtotime($time_end)+(24*60*60-1));
    	}
    	if(!empty($cooperation)){
    	    if($cooperation == 1){
    		  $where .=' AND B.term_start < "'.$time.'" AND B.term_end > "'.$time.'"';
    	    }
            if($cooperation == 2){
    		  $where .=' AND B.term_end < "'.$time.'"';
    	    }
    	    if($cooperation == 3){
    		  $where .=' AND A.id not in (select pid from fph_property_cooperation where term_start < '.$time.' AND term_end > '.$time.')';
    	    }
    	}
    	if(!empty($b_head)){
    	    $where .=' AND B.b_head = "'.$b_head.'"';
    	}
    	if(!empty($select_city_id)){
    	    $select_city_spid = M('city')->where('id ='.$select_city_id)->getField('spid');
    	    if($select_city_spid != 0){
    		  $select_city_spid = $select_city_spid.$select_city_id;
    	    }else{
    		  $select_city_spid = $select_city_id;
    	    }
    	    $where .=' AND A.city_id in(select id from fph_city where id = '.$select_city_id.' or spid RLIKE "[[:<:]]'.$select_city_id.'[[:>:]]")';
    	}
        if($activity==1){
            $where .=' AND A.id in (select pid from fph_property_prize where time_start < '.$time.' AND time_end > '.$time.')';
        }
        if($activity==2){
            $ids = D('property')->expenses($time);
            if($ids){
                $where .=' AND A.id in ('.$ids.')';
            }else{
				$where .=' AND A.id in (0)';
			}
        }
        if($activity==3){
            $ids = D('property')->groupBy($time);
            if($ids){
                $where .=' AND A.id in ('.$ids.')';
            }
        }
		if($activity==4){
				$where .=' AND A.suggest = 1';
		}
        M(NULL, 'fph_', C('DB_fangpinhui'));
    	$str = 'A.id,A.title,A.item_price,A.city_id,A.author,A.add_time,A.ordid,B.b_head,B.term_start,B.term_end,B.client_time_start,B.client_time_end,A.suggest';
    	$count = M('property')->field($str)->table("{$fph}property AS A LEFT JOIN {$fph}property_cooperation AS B ON A.id = B.pid")->where($where)->count('A.id');
    	$p = new Page($count, 20);
		$page = $p->show();
    	$list = M('property')->field($str)
			                 ->table("{$fph}property AS A LEFT JOIN {$fph}property_cooperation AS B ON A.id = B.pid")
						     ->where($where)
						     ->limit($p->firstRow.','.$p->listRows)
						     ->order('A.ordid,A.add_time DESC')
						     ->select();

    	foreach ($list as $key => $value) {
    		$city_spid = M('city')->where('id ='.$value['city_id'])->getField('spid');
    		$spid_arr = explode('|', $city_spid.$value['city_id']);
    		$name = '';
    		foreach ($spid_arr as $k => $v) {
    			$name .= M('city')->where('id ='.$v)->getField('name').' ';
    		}
    		$list[$key]['city_name'] = $name;

			$list[$key]['cooperation'] = 0;
			if($time >= $value['term_start'] && $time <= $value['term_end']){
				$list[$key]['cooperation'] = 1;
			}
			$list[$key]['client_cooperation'] = 0;
			if($time >= $value['client_time_start'] && $time <= $value['client_time_end']){
				$list[$key]['client_cooperation'] = 1;
			}
		}

		$cate = array('property_type'=>1,'property_feature'=>12,'building_type'=>15,'decoration'=>23);
		$catelist = $this->_mod->property_cate($cate);

		$this->assign('search', array(
			'property_type' => $property_type,
			'title' => $title,
			'status' => $status,
			'time_start' => $time_start,
			'time_end' => $time_end,
			'cooperation' => $cooperation,
			'b_head' => $b_head,
			'city_id' => $select_city_id,
			'city_spid' => $select_city_spid,
			'activity' => $activity
		));

		$this->assign('catelist',$catelist);
		$this->assign('list',$list);
		$this->assign('page',$page);
		$this->assign('p',$p);
		$this->display();
    }

    /*
    *@Descriptions：楼盘上下架
    *@Date:2014-12-1
    *@Author: wsj
    */
    public function status()
    {
        $id = $this->_get('id','intval');
        $status = $this->_get('status','intval');

        if(empty($id) AND empty($status)){
            $this->_404;
        }
        if($status == 1){
            $status = 0;
        }else{
            $status = 1;
        }
        M('property')->where('id ='.$id)->save(array('status'=>$status));
        //修改的数据写入缓存
        $redis = new CacheRedis($this->RedisDataBase);
        $redis->lRem('propertyMQ', $id , 0);
        $redis->lpush('propertyMQ', $id);

        $this->success(L('operation_success'));
    }

    /*
    *@Descriptions：楼盘户型图上传
    *@Date:2014-12-2
    *@Author: wsj
    */
    public function ajax_ordid_edit()
    {
        if(isajax){
            $id = $this->_post('id','intval');
            $ordid = $this->_post('ordid','intval');
            M('property')->where('id ='.$id)->save(array('ordid'=>$ordid));
            //修改的数据写入缓存
            $redis = new CacheRedis($this->RedisDataBase);
            $redis->lRem('propertyMQ', $id , 0);
            $redis->lpush('propertyMQ', $id);

            $this->ajaxReturn(1, L('操作成功'));
        }
        $this->ajaxReturn(0, L('操作失败'));
    }

    /*
    *@Descriptions：图片管理选择滚轮
    *@Date:2014-12-11
    *@Author: wsj
    */
    public function ajax_checkboxtype()
    {
        if(isajax){
            $id = $this->_post('id','intval');
            $type = $this->_post('type','intval');
            if($type == 1)
                $type = 2;
            else
                $type = 1;

            M('property_img')->where('id ='.$id)->save(array('type'=>$type));
            $this->ajaxReturn(1, L('操作成功'));
        }
    }

    /*
    *@Descriptions：图片管理
    *@Date:2014-12-2
    *@Author: wsj
    */
    public function propertyimg(){

        $id = $this->_get('id','intval');
        $propertyimg = C('propertyimg');
        $list = M('property_img')->where('pid ='.$id)->select();
        $this->assign('id', $id);
        $this->assign('list', $list);
        $this->assign('propertyimg', $propertyimg);
        $this->assign('type', 'propertyimg');

		//上传图片需要的参数
		$time=date(DATE_RFC822);
		$this->assign('time',$time);
        $this->display();
    }

    /*
    *@Descriptions：图片管理 删除
    *@Date:2014-12-3
    *@Author: wsj
    */
    public function delpropertyimg(){
        if(IS_POST){
            $id  = $this->_post('id','intval');
			$pid = $this->_post('pid','intval');
            if(!$id || !$pid){
                $this->ajaxReturn(0, L('illegal_parameters'));
            }
			$fdfs_obj  = new FastFile();
            $img       = M('property_img')->where('id ='.$id)->getfield('img');
			$img_thumb = M('property')->where('id ='.$pid)->getfield('img_thumb');


			//删除焦点图
			$setfocus      = C('setfocus');
			$thumbSuffix   = explode(',',$setfocus['suffix']);
			$img_thumb_exp = explode('.',$img_thumb);
			foreach($thumbSuffix as $k=>$v){
				$img_thumb_path = $img_thumb_exp[0].$v.'.'.$img_thumb_exp[1];
				$fdfs_obj->fast_del_img($img_thumb_path);
			}

			//删除图片
			$propertyimg        = C('propertyimg');
			$propertyimg_suffix = explode(',',$propertyimg[1]['suffix']);
			$img_exp            = explode('.',$img);
			$fdfs_obj->fast_del_img($img);
			foreach($propertyimg_suffix as $k=>$v){
				$img_thumb_Suffix = $img_exp[0].$v.'.'.$img_exp[1];
				$fdfs_obj->fast_del_img($img_thumb_Suffix);
			}
			if(false !== M('property_img')->where('id ='.$id)->delete()){
				$img_thumb = M('property')->where('id ='.$pid)->save(array('img_thumb'=>NULL));
                //修改的数据写入缓存
                $redis = new CacheRedis($this->RedisDataBase);
                $redis->lRem('propertyMQ', $pid , 0);
                $redis->lpush('propertyMQ', $pid);

				$this->ajaxReturn(1, '', $id);
			}else{
				$this->ajaxReturn(1, '删除失败');
			}
        }
        $this->ajaxReturn(0, L('illegal_parameters'));
    }

    /*
    *@Descriptions：图片管理 设置焦点
    *@Date:2014-12-3
    *@Author: wsj
    */
    public function ajax_setfocus() {
        if(IS_POST){
			$fdfs_obj = new FastFile();
            $id       = $this->_post('id','intval');
            $pid     = $this->_post('pid','intval');

            if(empty($pid) && empty($id)){
                $this->ajaxReturn(0, L('illegal_parameters'));
            }
			//查找图片
			$img_path_db = M('property_img')->where('id ='.$id)->getfield('img');
			$img_path    = C('img_url').$img_path_db;

			$tracker = fastdfs_tracker_get_connection();
			if(!fastdfs_active_test($tracker)){
				error_log("errno: " . fastdfs_get_last_error_no() . ", error info: " . fastdfs_get_last_error_info());
				exit(1);
			}
			$storage = fastdfs_tracker_query_storage_store();
				if(!$storage){
				error_log("errno: " . fastdfs_get_last_error_no() . ", error info: " . fastdfs_get_last_error_info());
				exit(1);
			}

			//先默认一个原图
			$res = fastdfs_storage_upload_by_filename('static/css/admin/bgimg/logo_login.gif', null, array(), null, $tracker, $storage);
			$setfocus     = C('setfocus');
			$thumbWidth   = explode(',',$setfocus['width']);
			$thumbHeight  = explode(',',$setfocus['height']);
			$thumbSuffix  = explode(',',$setfocus['suffix']);

			$format      = explode('.',$img_path);//文件格式后缀 *.jpg
			$info        = $fdfs_obj->getImageInfo($img_path);//文件信息
			//缩略图
			foreach($thumbWidth as $key=>$val){
				$fdfs_obj->_render_thumbnail(file_get_contents($img_path),$info,$thumbWidth[$key],$thumbHeight[$key],$res['group_name'],$res['filename'],$thumbSuffix[$key],end($format));
			}
			$img = $res['group_name'].'/'.$res['filename'];
			$fdfs_obj->fast_del_img($img);

			$img_gs = str_replace('gif',end($format),$res['group_name'].'/'.$res['filename']);
			M('property')->where('id ='.$pid)->save(array('img_thumb'=>$img_gs));
			M('property_img')->where('pid ='.$pid)->save(array('focus_img'=>0));
			M('property_img')->where('id ='.$id)->save(array('focus_img'=>1));

			//查找图片删除
            $img_thumb_path = M('property')->where('id ='.$pid)->getfield('img_thumb');
			if($img_thumb_path){
				$img_thumb_exp = explode('.',$img_thumb_path);
				foreach($thumbSuffix as $k=>$v){
					$img_thumb = $img_exp[0].$v.'.'.$img_exp[1];
					$fdfs_obj->fast_del_img($img_thumb);
				}
			}
            //修改的数据写入缓存
            $redis = new CacheRedis($this->RedisDataBase);
            $redis->lRem('propertyMQ', $pid , 0);
            $redis->lpush('propertyMQ', $pid);

            $this->ajaxReturn(1, '', $id);
        }
        $this->ajaxReturn(0, L('illegal_parameters'));
    }

	//执行图片缩略图*临时执行缩略图
	public function imgtest(){
		$property = M('property')->field('img_thumb')->limit(0,40)->select();
		print_r($property);
		//echo count();exit;
		$find = M('property_img')->field('status,img,id')->select();
		$setfocus = C('setfocus');
		$propertyimg = C('propertyimg');
		$thum_width = explode(',', $setfocus['width']);
		$thum_height = explode(',', $setfocus['height']);
		$thum_suffix = explode(',', $setfocus['suffix']);
		//print_r($thum_width);
		//exit;
		foreach($find as $key => $val){
			switch ($val['status'])
			{
			case 1:
			  $dir = 'property/xiaoguo';
			  break;
			case 2:
			  $dir = 'property/guihua';
			  break;
			case 3:
			  $dir = 'property/peitao';
			  break;
			case 4:
			  $dir = 'property/shijing';
			  break;
			 case 5:
			  $dir = 'property/jiaotong';
			  break;
			 case 6:
			  $dir = 'property/yangban';
			  break;
			}
			$str=substr_replace($val['img'],"",0,9);
			foreach($property as $k => $v){
				if($str == $v['img_thumb']){
					echo $dir_img = $dir.'/'.$val['img'];
					$img_name = explode('.', $v['img_thumb']);
					foreach($thum_width as $kk => $vv){
						Image::thumb('./data/upload/'.$dir_img,'./data/upload/property/aaa/'.$img_name[0].$thum_suffix[$kk].'.jpg','',$thum_width[$kk],$thum_height[$kk], true);
					}
				}
			}

		}
		//print_r($property);
	}

    /*
    *@Descriptions：楼盘图片上传
    *@Date:2014-12-2
    *@Author: wsj
    */
    public function ajax_propertyimg() {

        //上传图片
        if (!empty($_FILES['Filedata']['name'])){

			$pid         = $this->_post('pid','intval');
			$status      = $this->_post('status','intval');
			$propertyImg = C('propertyimg');
            if($_FILES['Filedata']['size']/1024 > C('pin_attr_allow_size')){
                $this->ajaxReturn(0,'图片超过尺寸限制');
            }
			$FastDFS_obj    = new FastFile();
			$result = $FastDFS_obj->fdfs_upload('Filedata',$propertyImg[1]['width'],$propertyImg[1]['height'],$propertyImg[1]['suffix'],false);

			if($result){
				$data_img = $result['group_name'].'/'.$result['filename'];

				$imgId = M('property_img')->add(array('pid'=>$pid,'status'=>$status,'img'=>$data_img));
				$data_img = str_replace('.', '_360x240.', $data_img);
                $array['name']   = C('img_url').$data_img;
                $array['status'] = $status;
                $array['id']     = $imgId;
				$array['pid']    = $pid;

                //修改的数据写入缓存
                $redis = new CacheRedis($this->RedisDataBase);
                $redis->lRem('propertyMQ', $pid , 0);
                $redis->lpush('propertyMQ', $pid);
                $this->ajaxReturn(1, '上传成功', $array);
			}else{
				$this->ajaxReturn(0, '上传图片出错');
			}
        } else {
            $this->ajaxReturn(2, L('illegal_parameters'));
        }
    }

	//上传图片
	public function ajax_upload_img() {
		$type = $this->_get('type', 'trim', 'img');

		if (!empty($_FILES[$type]['name'])) {
			$FastDFS_obj = new FastFile();
			$result = $FastDFS_obj->fdfs_upload($type);
			if($result){
				$saveName = $result['group_name'].'/'.$result['filename'];
				$this->ajaxReturn(1, L('operation_success'), $saveName);
			}else{
				$this->ajaxReturn(0, L('illegal_parameters'));
			}
		} else {
			$this->ajaxReturn(0, L('illegal_parameters'));
		}
	}

	//删除图片
	public function del_map_img(){
		$img_path = $this->_post('img_path', 'trim');
		$FastDFS_obj = new FastFile();
		$result = $FastDFS_obj->fast_del_img($img_path);
		if($result){
			$this->ajaxReturn(1, '删除成功');
		}else{
			$this->ajaxReturn(0, '删除失败');
		}
	}

    /*
    *@Descriptions：楼盘添加 前置操作
    *@Date:2014-11-18
    *@Author: wsj
    */
    public function _before_add(){
    	$cate = array('property_type'=>1,'property_feature'=>12,'building_type'=>15,'decoration'=>23);
    	$catelist = $this->_mod->property_cate($cate);
    	$author = $_COOKIE['admin']['username'];
    	$this->assign('author',$author);
    	$this->assign('catelist',$catelist);

    }

    /*
    *@Descriptions：楼盘添加 插入数据前操作
    *@Date:2014-11-18
    *@Author: wsj
    */
    protected function _before_insert($data)
    {
		$open_time               = $this->_post('open_time', 'trim');
		$check_time              = $this->_post('check_time', 'trim');
		$preferential_time_start = $this->_post('preferential_time_start', 'trim');
		$preferential_time_end   = $this->_post('preferential_time_end', 'trim');
		$comp_time               = $this->_post('comp_time', 'trim');
		$property_feature        = $this->_post('property_feature', 'trim');
		$property_type           = $this->_post('property_type', 'trim');
		$building_type           = $this->_post('building_type', 'trim');
		$decoration              = $this->_post('decoration', 'trim');
		$protection_time_status  = $this->_post('protection_time_status', 'intval', 0);
		$look_time_status        = $this->_post('look_time_status', 'intval', 0);
		if(!empty($open_time)){
			$data["open_time"]  = strtotime($open_time);
		}
		if(!empty($check_time)){
			$data["check_time"] = strtotime($check_time);
		}
		if(!empty($comp_time)){
			$data["comp_time"] = strtotime($comp_time);
		}
		if(!empty($preferential_time_start)){
			$data["preferential_time_start"] = strtotime($preferential_time_start);
		}
		if(!empty($preferential_time_end)){
			$data["preferential_time_end"] = strtotime($preferential_time_end);
		}
		$data['property_type'] = implode(',', $property_type);

		if(!empty($property_feature)){
			$data['property_feature'] = implode(',', $property_feature);
		}
		if(!empty($building_type)){
			$data['building_type'] = implode(',', $building_type);
		}
		if(!empty($decoration)){
			$data['decoration'] = implode(',', $decoration);
		}
		$data["add_time"] = time();
		$data["protection_time_status"] = $protection_time_status;
		$data["look_time_status"]       = $look_time_status;
        return $data;
    }

    /*
    *@Descriptions：楼盘添加后 跳转修改页面
    *@Date:2014-11-20
    *@Author: wsj
    */
    protected function _after_insert($id){
        //修改的数据写入缓存
        $redis = new CacheRedis($this->RedisDataBase);
        $redis->lRem('propertyMQ', $id , 0);
        $redis->lpush('propertyMQ', $id);

        $this->success(L('operation_success'),U('property/edit',array('id'=>$id,'menuid'=>298)));
    }

    /*
    *@Descriptions：楼盘修改 跳转列表页面
    *@Date:2014-11-20
    *@Author: wsj
    */
    protected function _after_update()
    {
        $this->success(L('operation_success'),U('property/index',array('menuid'=>298)));
    }

    /*
    *@Descriptions：楼盘修改 前置操作
    *@Date:2014-11-18
    *@Author: wsj
    */
    public function _before_edit(){
    	$id = $this->_get('id','intval');
    	$cate = array('property_type'=>1,'property_feature'=>12,'building_type'=>15,'decoration'=>23);
    	$catelist = $this->_mod->property_cate($cate);
    	$list =  $this->_mod->where('id ='.$id)->find();
    	$brand = M('property_pinpai')->field('id,business')->where('id ='.$list['pin_id'])->find();
    	$str = M('property')->where(array('id'=>$id))->getField('metro');
	$metro = $this->_mod->metro($str);
    	$spid_city = M('city')->where(array('id'=>$list['city_id']))->getField('spid');
        if($spid_city==0){
            $spid_city = $list['city_id'];
        }else{
            $spid_city .= $list['city_id'];
        }
        $author = $_COOKIE['admin']['username'];
    	$this->assign('author',$author);
        $this->assign('metro',$metro);
    	$this->assign('catelist',$catelist);
    	$this->assign('list',$list);
    	$this->assign('brand',$brand);
        $this->assign('id', $id);
        $this->assign('type', 'edit');
    	$this->assign('selected_ids_city',$spid_city);

    }

    /*
    *@Descriptions：楼盘修改 修改数据前操作
    *@Date:2014-11-20
    *@Author: wsj
    */
    protected function _before_update($data) {
		$open_time               = $this->_post('open_time', 'trim');
		$check_time              = $this->_post('check_time', 'trim');
		$comp_time               = $this->_post('comp_time', 'trim');
		$preferential_time_start = $this->_post('preferential_time_start', 'trim');
		$preferential_time_end   = $this->_post('preferential_time_end', 'trim');
		$property_type           = $this->_post('property_type', 'trim');
		$property_feature        = $this->_post('property_feature', 'trim');
		$building_type           = $this->_post('building_type', 'trim');
		$decoration              = $this->_post('decoration', 'trim');
		$protection_time_status  = $this->_post('protection_time_status', 'intval', 0);
		$look_time_status        = $this->_post('look_time_status', 'intval', 0);

		if(!empty($open_time)){
			$data["open_time"]  = strtotime($open_time);
		}
		if(!empty($check_time)){
			$data["check_time"] = strtotime($check_time);
		}
		if(!empty($comp_time)){
			$data["comp_time"]  = strtotime($comp_time);
		}
		if(!empty($preferential_time_start)){
			$data["preferential_time_start"] = strtotime($preferential_time_start);
		}
		if(!empty($preferential_time_end)){
			$data["preferential_time_end"] = strtotime($preferential_time_end);
		}
		if(!empty($data["preferential_time_start"])&&!empty($data["preferential_time_end"])&&($data["preferential_time_start"]>$data["preferential_time_end"])){
			$this->error('优惠开始时间不能大于结束时间');
		}
		$data['property_feature'] = implode(',', $property_feature);
		$data['building_type'] = implode(',', $building_type);
		$data['decoration'] = implode(',', $decoration);
        $data['property_type'] = implode(',', $property_type);

		$data['protection_time_status'] = $protection_time_status;
		$data['look_time_status']       = $look_time_status;

        //修改的数据写入缓存
        $redis = new CacheRedis($this->RedisDataBase);
        $redis->lRem('propertyMQ', $data['id'] , 0);
        $redis->lpush('propertyMQ', $data['id']);

		//楼盘推荐
		$redisSuggest = new CacheRedis($this->redisSuggest);
		$redisData = $redisSuggest->handler->hgetall($this->redisSuggestKey.$data['id']);
		if($redisData || $data['suggest'] == 0) $redisSuggest->rm($this->redisSuggestKey.$data['id']);
		if($data['suggest'] == 1 && $data['status'] == 1)
		{
			$redisSuggest->hmset($this->redisSuggestKey.$data['id'], array('id' => $data['id'], 'add_time' => time()));
		}
		return $data;
    }

    /*
    *@Descriptions：楼盘户型图上传
    *@Date:2014-11-19
    *@Author: wsj
    */
    public function ajax_housetype_img() {
        //上传图片
        if (!empty($_FILES['img']['name'])) {
            if($_FILES['img']['size']/1024 > C('pin_attr_allow_size')) {
                $this->ajaxReturn(0, '图片超过尺寸限制');
            }

			$width  = implode(',', C('house_thumb_Width'));
			$height = implode(',', C('house_thumb_Height'));
			$suffix = implode(',', C('house_thumb_Suffix'));

			$fdfs_obj = new FastFile();
			$result = $fdfs_obj->fdfs_upload('img',$width,$height,$suffix,false);
			if($result){
				$data_img = $result['group_name'].'/'.$result['filename'];
				//$data_img = str_replace('.', '_100x75.', $data_img);
				$this->ajaxReturn(1, L('operation_success'), $data_img);
			}else{
				 $this->error('上传图片出错');
			}
			/*$dir = date('Ymd');
            $result = $this->_upload($_FILES['img'], 'property/huxing/'. $dir, array('width'=>''.implode(',', C('house_thumb_Width')).'',
                'height'=>''.implode(',', C('house_thumb_Height')).'',
                'suffix'=>''.implode(',', C('house_thumb_Suffix')).''));
            if ($result['error']) {
                $this->ajaxReturn(0, $result['info']);
            } else {
                $ext = array_pop(explode('.', $result['info'][0]['savename']));
                $data['img'] = str_replace('.' . $ext, '.'.$ext, $result['info'][0]['savename']);
                $this->ajaxReturn(1, L('operation_success'), $dir .'/'. $data['img']);
            }*/
        } else {
            $this->ajaxReturn(0, L('illegal_parameters'));
        }
    }

    /*
    *@Descriptions：修改图片名称
    *@Date:2014-12-5
    *@Author: wsj
    */
    public function ajax_imgname()
    {
        if(isajax)
        {
            $id = $this->_post('id','intval');
            $imgname = $this->_post('imgname','trim');
            M('property_img')->where('id ='.$id)->save(array('title'=>$imgname));
            $this->ajaxReturn(1, L('操作成功'));
        }
    }

    /*
    *@Descriptions：删除图片
    *@Date:2014-11-19
    *@Author: wsj
    */
    public function del_img(){
        $img_path = $this->_post('img_path', 'trim');
		$fdfs_obj = new FastFile();
		$suffix   = C('house_thumb_Suffix');
		$img_path = str_replace('_100x75','',$img_path);
		if($img_path){
			$result = $fdfs_obj->fast_del_img($img_path);
			$img_exp = explode('.',$img_path);
			foreach($suffix as $k=>$v){
				$img_thumb = $img_exp[0].$v.'.'.$img_exp[1];
				$fdfs_obj->fast_del_img($img_thumb);
			}
		}
		if($result){
			$this->ajaxReturn(1, '删除成功');
		}else{
			$this->ajaxReturn(0, '删除失败');
		}
    }

    /*
	*@Descriptions：楼盘户型
	*@Date:2014-11-19
	*@Author: wsj
	*/
    public function housetype(){

    	$p = $this->_get('p','intval',1);
    	$id = $this->_get('id','intval');
    	$count = M('property_housetype')->where('pid ='.$id)->count('id');
		$p = new Page($count, 20);
		$page = $p->show();
		$list = M('property_housetype')->where('pid ='.$id)->limit($p->firstRow.','.$p->listRows)->order('id DESC')->select();
		foreach ($list as $key => $value) {
			$list[$key]['property_type'] = '';
			if(!empty($value['property_type']))
				$list[$key]['property_type'] = M('property_cate')->where('id ='.$value['property_type'])->getField('name');
		}
    	$this->assign('id', $id);
    	$this->assign('list', $list);
    	$this->assign('page', $page);
        $this->assign('type', 'house');
    	$this->display();
    }

    /*
	*@Descriptions：楼盘户型添加
	*@Date:2014-11-19
	*@Author: wsj
	*/
    public function housetype_add()
    {
    	$id = $this->_get('id','intval');
        if(empty($id))
            $this->_404();

    	$catelist = $this->_mod->property_cate(array('property_type'=>1,'property_label'=>27));
    	if(isset($_POST) AND $_POST){
    		$house_name = $this->_post('house_name', 'trim');
    		$property_label = $this->_post('property_label', 'trim');
    		$house_img = $this->_post('house_img', 'trim');
    		$property_type = $this->_post('property_type', 'trim');
    		$status = $this->_post('status', 'intval');
    		$house_area = $this->_post('house_area', 'trim');
    		$house_room = $this->_post('house_room', 'intval');
    		$house_hall = $this->_post('house_hall', 'intval');
    		$house_wc = $this->_post('house_wc', 'intval');
    		$house_info = $this->_post('house_info', 'trim');

    		$data['pid'] = $id;
    		$data['house_name'] = $house_name;
			$data['property_label'] = implode(',', $property_label);
			$data['house_img'] = $house_img;
			$data['property_type'] =$property_type;
    		$data['status'] = $status;
    		$data['house_area'] = $house_area;
    		$data['house_room'] = $house_room;
    		$data['house_hall'] = $house_hall;
    		$data['house_wc'] = $house_wc;
    		$data['house_info'] = $house_info;
    		$data['add_time'] = time();

    		M('property_housetype')->add($data);

            //修改的数据写入缓存
            $redis = new CacheRedis($this->RedisDataBase);
            $redis->lRem('propertyMQ', $id , 0);
            $redis->lpush('propertyMQ', $id);

            $this->success(L('operation_success'),U('property/housetype',array('id'=>$id)));
            exit;
    	}

    	$this->assign('catelist',$catelist);
    	$this->assign('id', $id);
        $this->assign('type', 'house');
    	$this->display();
    }

    /*
    *@Descriptions：楼盘户型修改
    *@Date:2014-11-19
    *@Author: wsj
    */
    public function housetype_edit()
    {
        $id = $this->_get('id','intval');
        $houseid = $this->_get('houseid','intval');
        if(empty($houseid) || empty($id))
            $this->_404();

        if(isset($_POST) AND $_POST)
        {
            $pid                = $this->_post('pid', 'intval');
            $house_name = $this->_post('house_name', 'trim');
            $property_label = $this->_post('property_label', 'trim');
            $house_img = $this->_post('house_img', 'trim');
            $property_type = $this->_post('property_type', 'trim');
            $status = $this->_post('status', 'intval');
            $house_area = $this->_post('house_area', 'trim');
            $house_room = $this->_post('house_room', 'intval');
            $house_hall = $this->_post('house_hall', 'intval');
            $house_wc = $this->_post('house_wc', 'intval');
            $house_info = $this->_post('house_info', 'trim');

            $data['house_name'] = $house_name;
            $data['property_label'] = implode(',', $property_label);
            $data['house_img'] = $house_img;
            $data['property_type'] =$property_type;
            $data['status'] = $status;
            $data['house_area'] = $house_area;
            $data['house_room'] = $house_room;
            $data['house_hall'] = $house_hall;
            $data['house_wc'] = $house_wc;
            $data['house_info'] = $house_info;

            M('property_housetype')->where('id ='.$houseid)->save($data);
            //修改的数据写入缓存
            $redis = new CacheRedis($this->RedisDataBase);
            $redis->lRem('propertyMQ', $pid , 0);
            $redis->lpush('propertyMQ', $pid);

            $this->success(L('operation_success'),U('property/housetype',array('id'=>$id)));
            exit;
        }
        $list = M('property_housetype')->where('id ='.$houseid)->find();
        $catelist = $this->_mod->property_cate(array('property_type'=>1,'property_label'=>27));
        $this->assign('list',$list);
        $this->assign('houseid',$houseid);
        $this->assign('catelist',$catelist);
        $this->assign('id', $id);
        $this->assign('type', 'house');
        $this->display();
    }

    /*
    *@Descriptions：楼盘户型 删除
    *@Date:2014-11-20
    *@Author: wsj
    */
    public function housetype_del()
    {
        $id = $this->_get('id','intval');
        $houseid = $this->_get('houseid','intval');
        if(empty($houseid) || empty($id))
            $this->_404();


        $house_info = M('property_housetype')->field('pid,house_img')->where('id ='.$houseid)->find();

		$fdfs_obj = new FastFile();
		$suffix   = C('house_thumb_Suffix');
		$img_path = str_replace('_100x75','',$house_info['house_img']);
		if($img_path){
			$result = $fdfs_obj->fast_del_img($img_path);
			$img_exp = explode('.',$img_path);
			foreach($suffix as $k=>$v){
				$img_thumb = $img_exp[0].$v.'.'.$img_exp[1];
				$fdfs_obj->fast_del_img($img_thumb);
			}
		}

        M('property_housetype')->where('id ='.$houseid)->delete();

        //修改的数据写入缓存
        $redis = new CacheRedis($this->RedisDataBase);
        $redis->lRem('propertyMQ', $house_info['pid'] , 0);
        $redis->lpush('propertyMQ', $house_info['pid']);

        $this->success(L('operation_success'));
    }

    /*
    *@Descriptions：楼盘活动
    *@Date:2014-11-21
    *@Author: wsj
    */
    public function activities()
    {
        $p = $this->_get('p','intval',1);
        $id = $this->_get('id','intval');
        $count = M('article')->where('pid ='.$id)->count('id');
        $p = new Page($count, 20);
        $page = $p->show();
        $list = M('article')->where('pid ='.$id)->limit($p->firstRow.','.$p->listRows)->order('add_time DESC')->select();
        foreach ($list as $key => $value) {
            $list[$key]['count'] = 0;
        }

        $this->assign('id', $id);
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('type', 'activities');
        $this->display();
    }

    /*
    *@Descriptions：楼盘活动 添加
    *@Date:2014-11-21
    *@Author: wsj
    */
    public function activities_add()
    {
        $id = $this->_get('id','intval');
        $property = M('property')->field('id,title')->where('id ='.$id)->find();
        $this->assign('property', $property);
        $this->assign('id', $id);
        $this->assign('type', 'activities');
        $this->display();
    }

    /*
    *@Descriptions：楼盘活动 修改
    *@Date:2014-11-21
    *@Author: wsj
    */
    public function activities_edit()
    {
        $id = $this->_get('id','intval');
        $activities_id = $this->_get('activities','intval');

        $id = $this->_get('id','intval');
        $article = M('article')->where(array('id'=>$activities_id))->find();
        $property = M('property')->field('id,title')->where('id ='.$id)->find();
        $spid = $this->_cate_mod->where(array('id'=>$article['cate_id']))->getField('spid');
        if( $spid==0 ){
            $spid = $article['cate_id'];
        }else{
            $spid .= $article['cate_id'];
        }
        $spid_city = M('city')->where(array('id'=>$article['city_id']))->getField('spid');
        if( $spid_city==0 ){
            $spid_city = $article['city_id'];
        }else{
            $spid_city .= $article['city_id'];
        }
        $this->assign('property', $property);
        $this->assign('selected_ids_city',$spid_city);
        $this->assign('selected_ids',$spid);
        $this->assign('info',$article);
        $this->assign('id', $id);
        $this->assign('type', 'activities');
        $this->display();
    }

    /*
    *@Descriptions：佣金管理 显示
    *@Date:2014-11-21
    *@Author: wsj
    */
    public function commission()
    {

        $p = $this->_get('p','intval',1);
        $id = $this->_get('id','intval');
        $count = M('property_commission')->where('pid ='.$id)->count('id');
        $p = new Page($count, 20);
        $page = $p->show();
        $list = M('property_commission')->where('pid ='.$id)->limit($p->firstRow.','.$p->listRows)->order('add_time DESC')->select();

        foreach ($list as $key => $value) {
	    $catearr = M('property_cate')->where('id in('.$value['property_type'].')')->select();
	    $list[$key]['cate'] = '';
	    foreach ($catearr as $k => $v) {
	       $list[$key]['cate'] .= $v['name'].',';
	    }
	    $list[$key]['cate'] = substr($list[$key]['cate'],0,-1);
	    $storesname = '';
	    if(!empty($value['stores_id']))
	    {
		$stores = M('stores')->where('id in('.$value['stores_id'].')')->select();
		foreach($stores as $v1)
		{
		    $storesname .= $v1['name'].',';
		}
	    }

	    $storesname = substr($storesname,0,-1);
	    $list[$key]['storesname'] = $storesname;
        }

        $this->assign('id', $id);
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('type', 'commission');
        $this->display();
    }

    /*
    *@Descriptions：佣金管理 添加
    *@Date:2014-11-21
    *@Author: wsj
    */
    public function commission_add()
    {
        $id = $this->_get('id','intval');
        if(empty($id))
            $this->_404();

        if(isset($_POST) AND $_POST)
        {
            $price = $this->_post('price', 'trim');
            $each = $this->_post('each', 'trim');
            $money = $this->_post('money', 'trim');
	    $stores_id = $this->_post('stores_id', 'trim');
            $property_type = $this->_post('property_type', 'trim');
            $term_start = $this->_post('term_start', 'trim');
            $term_end = $this->_post('term_end', 'trim');

	    $stores_id = explode(',',$stores_id);
	    $stores_id = array_unique($stores_id);
	    $stores_id = implode(',',$stores_id);

            $data['price'] = $price;
            $data['each'] = $each;
            $data['money'] = $money;
	    $data['stores_id'] = $stores_id;
            $data['property_type'] = implode(',', $property_type);
            $data['term_start'] = strtotime($term_start);
            $data['term_end'] = strtotime($term_end)+(3600*24)-1;
            $data['pid'] = $id;
            $data['add_time'] = time();

            M('property_commission')->add($data);

            $this->success(L('operation_success'),U('property/commission',array('id'=>$id)));
            exit;
        }


        $cate = array('property_type'=>1);
        $catelist = $this->_mod->property_cate($cate);
        $this->assign('catelist',$catelist);
        $this->assign('id', $id);
        $this->assign('type', 'commission');
        $this->display();
    }

    /*
    *@Descriptions：佣金管理 修改
    *@Date:2014-11-21
    *@Author: wsj
    */
    public function commission_edit()
    {
        $id = $this->_get('id','intval');
        $commission = $this->_get('commission','intval');
        if(empty($commission) || empty($id))
            $this->_404();

        $list = M('property_commission')->where('id ='.$commission)->find();

        if(isset($_POST) AND $_POST)
        {
            $price = $this->_post('price', 'trim');
            $each = $this->_post('each', 'trim');
            $money = $this->_post('money', 'trim');
	    $stores_id = $this->_post('stores_id', 'trim');
            $property_type = $this->_post('property_type', 'trim');
            $term_start = $this->_post('term_start', 'trim');
            $term_end = $this->_post('term_end', 'trim');

	    $stores_id = explode(',',$stores_id);
	    $stores_id = array_unique($stores_id);
	    $stores_id = implode(',',$stores_id);

            $data['price'] = $price;
            $data['each'] = $each;
            $data['money'] = $money;
	    $data['stores_id'] = $stores_id;
            $data['property_type'] = implode(',', $property_type);
            $data['term_start'] = strtotime($term_start);
            $data['term_end'] = strtotime($term_end)+(3600*24)-1;

            M('property_commission')->where('id ='.$commission)->save($data);

            $this->success(L('operation_success'),U('property/commission',array('id'=>$id)));
            exit;
        }

        $cate = array('property_type'=>1);
        $catelist = $this->_mod->property_cate($cate);
	$storesname = '';
	if(!empty($list['stores_id']))
	{
		$stores = M('stores')->where('id in('.$list['stores_id'].')')->select();

		foreach($stores as $k=>$v)
		{
		    $storesname .= $v['name'].',';
		}
		$storesname = substr($storesname,0,-1);
	}

        $this->assign('catelist',$catelist);
	$this->assign('storesname',$storesname);
        $this->assign('list',$list);
        $this->assign('id', $id);
        $this->assign('commission', $commission);
        $this->assign('type', 'commission');
        $this->display();
    }

    /*
    *@Descriptions：佣金管理 删除
    *@Date:2014-11-21
    *@Author: wsj
    */
    public function commission_del()
    {
        $id = $this->_get('id','intval');
        $commission = $this->_get('commission','intval');
        if(empty($commission) || empty($id))
            $this->_404();

        M('property_commission')->where('id ='.$commission)->delete();
        $this->success(L('operation_success'));
    }

    /*
    *@Descriptions：合作信息
    *@Date:2014-11-25
    *@Author: wsj
    */
    public function cooperation()
    {
        $id = $this->_get('id','intval');
        if(isset($_POST) AND $_POST)
        {
            $a_party = $this->_post('a_party', 'trim');
            $a_address = $this->_post('a_address', 'trim');
            $a_head = $this->_post('a_head', 'trim');
            $a_head_mobile = $this->_post('a_head_mobile', 'trim');
            $a_market = $this->_post('a_market', 'trim');
            $a_market_mobile = $this->_post('a_market_mobile', 'trim');
            $b_party = $this->_post('b_party', 'trim');
            $b_address = $this->_post('b_address', 'trim');
            $b_head = $this->_post('b_head', 'trim');
            $b_head_mobile = $this->_post('b_head_mobile', 'trim');
            $b_market = $this->_post('b_market', 'trim');
            $b_market_mobile = $this->_post('b_market_mobile', 'trim');
            $inventory = $this->_post('inventory', 'trim');
            $signing_time = $this->_post('signing_time', 'trim');
            $term_start = $this->_post('term_start', 'trim');
            $term_end = $this->_post('term_end', 'trim');
			$client_time_start = $this->_post('client_time_start', 'trim');
			$client_time_end = $this->_post('client_time_end', 'trim');

			if(strtotime($term_start) > strtotime($term_end)){
				$this->error('经纪人合作开始时间不能大于结束时间');
			}
			if(strtotime($client_time_start) > strtotime($client_time_end)){
				$this->error('客户端合作开始时间不能大于结束时间');
			}

            $data['pid'] = $id;
            $data['a_party'] = $a_party;
            $data['a_address'] = $a_address;
            $data['a_head'] = $a_head;
            $data['a_head_mobile'] = $a_head_mobile;
            $data['a_market'] = $a_market;
            $data['a_market_mobile'] = $a_market_mobile;
            $data['b_party'] = $b_party;
            $data['b_address'] = $b_address;
            $data['b_head'] = $b_head;
            $data['b_head_mobile'] = $b_head_mobile;
            $data['b_market'] = $b_market;
            $data['b_market_mobile'] = $b_market_mobile;
            $data['inventory'] = $inventory;
            $data['signing_time'] = strtotime($signing_time);
            $data['term_start'] = strtotime($term_start);
            $data['term_end'] = strtotime($term_end)+(3600*24)-1;
			$data['client_time_start'] = strtotime($client_time_start);
			$data['client_time_end'] = strtotime($client_time_end);

            $data['cooperation_file'] = M('property_cooperation')->where('pid ='.$id)->getField('cooperation_file');

            if(!empty($_FILES))
            {
                foreach ($_FILES['file']['name'] as $key => $value) {
                    if($value == '')
                    {
                        unset($_FILES['file']['name'][$key]);
                        unset($_FILES['file']['type'][$key]);
                        unset($_FILES['file']['tmp_name'][$key]);
                        unset($_FILES['file']['error'][$key]);
                        unset($_FILES['file']['size'][$key]);
                    }
                    $_FILES['file']['name'][$key] = str_replace(',','',$_FILES['file']['name'][$key]);
                    $_FILES['file']['name'][$key] = str_replace('&','',$_FILES['file']['name'][$key]);
                }
                $result = $this->_upload($_FILES['file'], 'property_file/'.$id ,'','','');
                foreach ($result['info'] as $key => $value) {
                    if(empty($data['cooperation_file']))
                    {
                        $data['cooperation_file'] = $id.'/'.$value['savename'];
                    }
                    else
                    {
                        $data['cooperation_file'] = $data['cooperation_file'].','.$id.'/'.$value['savename'];
                    }
                }
            }
            $save = M('property_cooperation')->where('pid ='.$id)->getField('id');
            if(empty($save)) {
                M('property_cooperation')->add($data);
            }else {
                M('property_cooperation')->where('pid ='.$id)->save($data);
            }
            //修改的数据写入缓存
            $redis = new CacheRedis($this->RedisDataBase);
            $redis->lRem('propertyMQ', $id , 0);
            $redis->lpush('propertyMQ', $id);

            $this->success(L('operation_success'));
            exit;
        }
        $list = M('property_cooperation')->where('pid ='.$id)->find();
        $methods = M('cooperation_methods')->where('pid ='.$id)->select();
        $catelist = $this->_mod->property_cate(array('property_type'=>1,'property_label'=>27));

        if(!empty($list['cooperation_file']))
        {
            $list['cooperation_file'] = explode(',', $list['cooperation_file']);
            foreach ($list['cooperation_file'] as $key => $value) {

                $list['cooperation_file'][$key] = end(explode('/', $value));
            }
        }

        $this->assign('catelist',$catelist);
        $this->assign('methods',$methods);
        $this->assign('id', $id);
        $this->assign('list', $list);
        $this->assign('type', 'cooperation');
        $this->display();
    }


    /*
    *@Descriptions：下载合作附件
    *@Date:2014-11-25
    *@Author: wsj
    */
    public function cooperation_download()
    {
        $id = $this->_get('id','trim');
        $file = $this->_get('file','trim');

        if(empty($id) AND empty($file))
            $this->_404;

        $file = PIN_DATA_PATH.'upload/property_file/'.$id.'/'.iconv('UTF-8','GB2312',$file);
        if (is_file($file))
        {
            header('Content-type: application/unknown');
            header('Content-Disposition: attachment; filename="' . $file . '"');
            readfile($file);
        }
        else
        {
            $this->error(L('illegal_parameters'));
        }
    }

    /*
    *@Descriptions：删除 下载合作附件
    *@Date:2014-11-25
    *@Author: wsj
    */
    public function cooperation_download_del()
    {
        $id = $this->_get('id','trim');
        $file = $this->_get('file','trim');
        if(empty($id) AND empty($file))
            $this->_404;

        $file_name  = $id.'/'.$file;
        $file = PIN_DATA_PATH.'upload/property_file/'.$id.'/'.iconv('UTF-8','GB2312',$file);
        if (is_file($file))
        {
            $property = M('property_cooperation')->where('pid ='.$id)->getField('cooperation_file');
            $arr = explode(',', $property);
            foreach ($arr as $key => $value) {
               if($value == $file_name)
               {
                    unset($arr[$key]);
               }
            }
            M('property_cooperation')->where('pid ='.$id)->save(array('cooperation_file'=>implode(',', $arr)));
            unlink($file);
        }
        $this->success(L('operation_success'));
        exit;
    }

    /*
    *@Descriptions：合作方式
    *@Date:2014-11-25
    *@Author: wsj
    */
    public function methods()
    {
        $p = $this->_get('p','intval',1);
        $id = $this->_get('id','intval');
        $count = M('cooperation_methods')->where('pid ='.$id)->count('id');
        $p = new Page($count, 20);
        $page = $p->show();
        $list = M('cooperation_methods')->where('pid ='.$id)->limit($p->firstRow.','.$p->listRows)->order('id DESC')->select();

//      $catelist = $this->_mod->property_cate(array('property_type'=>1,'property_label'=>27));
//	print_r($catelist);
//	exit;
//      $this->assign('catelist',$catelist);
	foreach($list as $k=>$v)
	{
		$list[$k]['property_type'] = M('property_cate')->where('id in('.$v['property_type'].')')->select();
	}

	//print_r($list);
	//exit;

        $this->assign('id', $id);
	$this->assign('list', $list);
	$this->assign('page', $page);
	$this->assign('type', 'methods');
	$this->display();
    }

    //合作方式添加
    public function methods_add()
    {
	$id = $this->_get('id','intval');
        if(empty($id))
            $this->_404();

	if(isset($_POST) AND $_POST)
        {

            $property_type = $this->_post('property_type', 'trim');
            $term_start = $this->_post('term_start', 'trim');
            $term_end = $this->_post('term_end', 'trim');
	    $service_money = $this->_post('service_money', 'trim');
            $platform_money = $this->_post('platform_money', 'trim');

            $data['property_type'] = implode(',', $property_type);
            $data['term_start'] = strtotime($term_start);
            $data['term_end'] = strtotime($term_end)+(3600*24)-1;
	    $data['service_money'] = $service_money;
            $data['platform_money'] = $platform_money;
            $data['pid'] = $id;
            $data['add_time'] = time();

            M('cooperation_methods')->add($data);

            $this->success(L('operation_success'),U('property/methods',array('id'=>$id)));
            exit;
        }


        $catelist = $this->_mod->property_cate(array('property_type'=>1));

        $this->assign('catelist',$catelist);
        $this->assign('id', $id);
        $this->assign('type', 'methods');
        $this->display();
    }

    /*
    *@Descriptions：合作方式修改
    *@Date:2014-11-25
    *@Author: wsj
    */
    public function methods_edit()
    {
        $id = $this->_get('id','intval');
	$methods = $this->_get('methods','intval');
        if(empty($id) or empty($methods))
            $this->_404();

	$list = M('cooperation_methods')->where('id ='.$methods)->find();
	if(isset($_POST) AND $_POST)
        {

            $property_type = $this->_post('property_type', 'trim');
            $term_start = $this->_post('term_start', 'trim');
            $term_end = $this->_post('term_end', 'trim');
	    $service_money = $this->_post('service_money', 'trim');
            $platform_money = $this->_post('platform_money', 'trim');

            $data['property_type'] = implode(',', $property_type);
            $data['term_start'] = strtotime($term_start);
            $data['term_end'] = strtotime($term_end)+(3600*24)-1;
	    $data['service_money'] = $service_money;
            $data['platform_money'] = $platform_money;

            M('cooperation_methods')->where('id ='.$methods)->save($data);

            $this->success(L('operation_success'),U('property/methods',array('id'=>$id)));
            exit;
        }


        $catelist = $this->_mod->property_cate(array('property_type'=>1));
	$this->assign('list',$list);
        $this->assign('catelist',$catelist);
        $this->assign('id', $id);
        $this->assign('type', 'methods');
        $this->display();
    }

    /*
    *@Descriptions：合作方式 删除
    *@Date:2014-11-25
    *@Author: wsj
    */
    public function methods_del()
    {
        $id = $this->_get('id','intval');
        $methods_id = $this->_get('methods','intval');
        if(empty($methods_id) || empty($id))
            $this->_404();

        M('cooperation_methods')->where('id ='.$methods_id)->delete();
        $this->success(L('operation_success'),U('property/methods',array('id'=>$id)));
    }

    //佣金梁道搜索
    public function stores_select()
    {
	    $business = $this->_post('business','trim');
	    !$business && $this->ajaxReturn(0, '请输入要搜索的内容');
	    if($business){
		$list = M('stores')->where('name like "%'.$business.'%"')->limit(50)->select();
		$str = "";
		$str .= "<ul class='popup_s'>";
			foreach($list as $val) {
				$business = $val['name'];
				$str .= "<li rel=".$val['id'].">".msubstr($business,0,35,'utf-8',true)."</li>";
			}
		$str .= '</ul>';
	    }else{
	     $str .= "<div>无数据,请检查输入的关键字</div>";
	    }
	    $this->ajaxReturn(1, '未知错误！', $str);
    }


    /*
    *@Descriptions：地铁数据弹出页面
    *@Date:2014-11-18
    *@Author: chenli
    */
    public function metro(){

	    if (IS_AJAX) {
		    //修改时候 获取以选择关联地铁数据 进行处理
		    $id = $this->_get('id','intval');
		    $city_id = $this->_get('city_id','trim');
		    $metro = '';
		    if(!empty($id))
		    {
			    $str = M('property')->where(array('id'=>$id))->getField('metro');
			    $metro = $this->_mod->metro($str);
		    }
		    //地铁数据
		    $metro_list = M('metro')->field('id,name')->where('pid = 0 AND city_id ='.$city_id.'')->order('id ASC')->select();
		    foreach($metro_list as $key=>$val){
			    $metro_list[$key]['subway'] = M('metro')->field('id,name')->where(array('pid'=>$val['id']))->select();
			    if(!empty($id))
			    {
				    foreach ($metro_list[$key]['subway'] as $k => $v) {
					    $metro_list[$key]['subway'][$k]['status'] = 0;
					    foreach ($metro as $k1 => $v1) {
						    foreach ($v1['metro_end'] as $k2 => $v2) {
							    if($v['id'] == $v2['id'])
								    $metro_list[$key]['subway'][$k]['status'] = 1;
						    }
					    }
				    }
			    }
		    }
		    $this->assign('metro_list', $metro_list);

		    $response = $this->fetch();
		    $this->ajaxReturn(1, '', $response);

	    } else {
		    $this->display();
	    }
	}

	public function latitude() {
		$city_id = $this->_get('city_id','intval');
		$spid = M('city')->where(array('id'=>$city_id))->getfield('spid');
		$spid_arr = explode('|',$spid);
		if($spid){
			$name = '';
			foreach($spid_arr as $key => $val){
				if(!$val){
					unset($val);
				}
				$name .= M('city')->where(array('id'=>$val))->getfield('name');
			}
			$name .= M('city')->where(array('id'=>$city_id))->getfield('name');
		}else{
			$name = M('city')->where(array('id'=>$city_id))->getfield('name');
		}
		$address = $this->_get('address','trim');
		$this->assign('address', $name.$address);
        $this->display();
    }

    public function ajax_city() {
        $id = $this->_get('id', 'intval');
        $return = M('city')->field('id,name')->where(array('pid'=>$id))->select();
        if ($return) {
            $this->ajaxReturn(1, L('operation_success'), $return);
        } else {
            $this->ajaxReturn(0, L('operation_failure'));
        }
    }

    //查询品牌操作
    public function pinpai()
    {
	$business = $this->_post('business','trim');
        !$business && $this->ajaxReturn(0, '请输入要搜索的内容');
        if($business){
	    $list = M('property_pinpai')->field("id,business")->where('business like "%'.$business.'%"')->limit(50)->select();
	    $str = "";
			$str .= "<ul class='popup_s'>";
				foreach($list as $val) {
					$business = $val['business'];
					$str .= "<li rel=".$val['id'].">".msubstr($business,0,35,'utf-8',true)."</li>";
				}
			$str .= '</ul>';
        }else{
         $str .= "<div>无数据,请检查输入的关键字</div>";
        }
        $this->ajaxReturn(1, '未知错误！', $str);
    }

    public function ajax_check_name()
    {
		$business = $this->_post('business', 'trim');
		if(empty($business))
		{
		    $this->ajaxReturn(0, '不能为空');
		}
		$data = M('property_pinpai')->where("business='".$business."'")->count('id');
		if($data){
		    $this->ajaxReturn(0, '该品牌已经存在');
		}else{
		    $array['business'] = $business;
		    $array['status'] = '1';
		    M('property_pinpai')->add($array);
		    $id = M('property_pinpai')->getLastInsID();
		    $this->ajaxReturn(1, '成功！', $id);
		}
    }
    //带看奖
    public function property_prize(){
        $menuid = $this->_request('menuid', 'trim');
        $id     = $this->_request('id', 'trim');
        $author = $_COOKIE['admin']['username'];

        $count = M('property_prize')->where('pid ='.$id)->count('id');
        $p = new Page($count, 20);
        $page = $p->show();

        //初始数据
        $list = M('property_prize')->field('id,pid,time_start,time_end,prize,admin_name,add_time,stores_id')->where(array('pid'=>$id))->limit($p->firstRow.','.$p->listRows)->order('add_time DESC')->select();
        foreach($list as $key=>$val){
            $property_type = M('property')->where('id='.$val['pid'])->getField('property_type');
            $catearr = M('property_cate')->where('id in('.$property_type.')')->select();
            $list[$key]['cate'] = '';
            foreach ($catearr as $k => $v) {
               $list[$key]['cate'] .= $v['name'].',';
            }
            $list[$key]['cate'] = substr($list[$key]['cate'],0,-1);
            $storesname = '';
            if(!empty($val['stores_id'])){
                $stores = M('stores')->where('id in('.$val['stores_id'].')')->select();
                foreach($stores as $v1){
                    $storesname .= $v1['name'].',';
                }
            }
            $storesname = substr($storesname,0,-1);
            $list[$key]['storesname'] = $storesname;
        }
        $this->assign('list', $list);
        $this->assign('id', $id);
        $this->assign('page', $page);
        $this->assign('menuid', $menuid);
        $this->assign('sub_menu', '');
        $this->display();
    }

    //修改带看奖
    public function property_prize_edit()
    {
        $author = $_COOKIE['admin']['username'];
        $id = $this->_request('id','intval');//楼盘ID
        $p_id = $this->_request('p_id','intval');//带看奖ID

        if(empty($p_id) || empty($id))
            $this->_404();
        if(isset($_POST) AND $_POST)
        {
            $time_start = $this->_post('time_start', 'trim');
            $time_end   = $this->_post('time_end', 'trim');
            $prize      = $this->_post('prize', 'trim');
            $stores_id  = $this->_post('stores_id', 'trim');
            if(!$id || !$p_id){
                $this->error('参数有误');exit;
            }
            $data['time_start'] = strtotime($time_start);
            $data['time_end']   = strtotime($time_end);
            $data['prize']      = $prize;
            $data['admin_name'] = $author;
            $data['stores_id']  = $stores_id;
            M('property_prize')->where('id ='.$p_id)->save($data);
            $this->success(L('operation_success'),U('property/property_prize',array('id'=>$id)));
            exit;
        }
        $info = M('property_prize')->where('id ='.$p_id)->find();
        $storesname = '';
        if(!empty($info['stores_id'])){
            $stores = M('stores')->where('id in('.$info['stores_id'].')')->select();
            foreach($stores as $k=>$v){
                $storesname .= "<a href='javascript:;' class='stores_id' rel=".$v['id']."&".$p_id.">".$v['name'].'<img src='.__STATIC__.'/images/admin/toggle_disabled.gif></a>&nbsp;&nbsp;';
            }
            $storesname = substr($storesname,0,-1);
        }

        $info['storesname'] = $storesname;
        $this->assign('info',$info);
        $this->assign('id', $id);
        $this->assign('p_id', $p_id);
        $this->display();
    }
    //点击删除对应渠道
    public function property_prize_store()
    {
        $stores_id = $this->_post('stores_id','trim');

        !$stores_id && $this->ajaxReturn(0, '选项不能为空');
        if($stores_id){
            $arr = explode('&',$stores_id);
            $stores = M('property_prize')->where('id='.$arr[1])->getField('stores_id');
            $arr_s = explode(',', $stores);
            $str = '';
            foreach($arr_s as $k=>$v){
                if($v!=$arr[0]){
                    $str .=$v.',';
                }
            }
            $str = substr($str, 0,-1);
            $id = M('property_prize')->where(array('id'=>$arr[1]))->save(array('stores_id'=>$str));
            if($id){
                $this->ajaxReturn(1, '修改成功!');
            }else{
                $this->ajaxReturn(0, '修改失败!');
            }
        }
    }
    //添加带看奖
    public function property_prize_add(){
        $author = $_COOKIE['admin']['username'];
        $id = $this->_request('id','intval');//楼盘ID
        if(empty($id))
            $this->_404();
        if(isset($_POST) AND $_POST)
        {
            $time_start = $this->_post('time_start', 'trim');
            $time_end = $this->_post('time_end', 'trim');
            $prize = $this->_post('prize', 'trim');
            $stores_id = $this->_post('stores_id', 'trim');
            $data['pid'] = $id;
            $data['time_start'] = strtotime($time_start);
            $data['time_end']   = strtotime($time_end);
            $data['prize']      = $prize;
            $data['admin_name'] = $author;
            $data['stores_id']  = $stores_id;
            $data['add_time']   = time();
            $lastid = M('property_prize')->add($data);
            if($lastid){
                 $this->success(L('operation_success'),U('property/property_prize',array('id'=>$id)));
            }
            exit;
        }

        $this->assign('id', $id);
        $this->assign('p_id', $p_id);
        $this->display();
    }
    /*
    *@Descriptions：带看奖 删除
    *@Date:2015-04-17
    *@Author: lishun
    */
    public function property_prize_del()
    {
        $id = $this->_get('id','intval');
        $p_id = $this->_get('p_id','intval');
        if(empty($p_id) || empty($id))
            $this->_404();
        M('property_prize')->where('id ='.$p_id)->delete();
        $this->success(L('operation_success'));
    }
    // //带看奖
    // public function property_prize(){
    //     $menuid = $this->_request('menuid', 'trim');
    //     $id     = $this->_request('id', 'trim');
    //     $author = $_SESSION['admin']['username'];
    //     if(IS_POST){
    //         $time_start = $this->_post('time_start', 'trim');
    //         $time_end   = $this->_post('time_end', 'trim');
    //         $prize      = $this->_post('prize', 'trim');

    //         !$time_start && $this->error('请选择开始时间');
    //         !$time_end && $this->error('请选择结束时间');
    //         if($time_start > $time_end){
    //             $this->error('开始时间怎么可以大于结束时间呢');
    //         }
    //         !$prize && $this->error('请输入奖金金额');

    //         //查询是否添加过数据
    //         $info_count = M('property_prize')->where(array('pid'=>$id))->count('pid');

    //         $data['pid']        = $id;
    //         $data['time_start'] = strtotime($time_start);
    //         $data['time_end']   = strtotime($time_end);
    //         $data['prize']      = $prize;
    //         $data['admin_name'] = $author;
    //         $data['add_time']   = time();

    //         if($info_count){
    //             if(false !== M('property_prize')->where(array('pid'=>$id))->save($data)){
    //                 $this->success('提交成功');
    //             }else{
    //                 $this->error('提交失败');
    //             }
    //         }else{
    //             if(false !== M('property_prize')->add($data)){
    //                 $this->success('提交成功');
    //             }else{
    //                 $this->error('提交失败');
    //             }
    //         }
    //         exit;
    //     }

    //     //初始数据
    //     $info = M('property_prize')->field('time_start,time_end,prize')->where(array('pid'=>$id))->find();
    //     $this->assign('info', $info);
    //     $this->assign('id', $id);
    //     $this->assign('menuid', $menuid);
    //     $this->display();
    // }

    //路费 列表
    public function expenses_index(){
        $id          = $this->_request('id','intval');//楼盘ID
        $menuid = $this->_request('menuid', 'trim');
        M(NULL,NULL,C('DB_property'));

        $list = D('property')->expenses_list($id);
        $list = D('property')->expenses_count_people($list);

        $this->assign('list', $list);

        $this->assign('id', $id);
		$this->assign('time', time());
        $this->display();
    }

    /**
     * 上传领路费图片样例
     */
    public function uploadExpensesPhoto()
    {
    	//上传图片
    	if (!empty($_FILES['photo'])) {    		
    		if($_FILES['photo']['size']/1024 > C('pin_attr_allow_size'))	return $this->ajaxReturn(0, '图片超过尺寸限制');
			$upload	= oss_image_upload( 'property', '8', $_FILES['photo'] );
			if( $upload['status'] == TRUE )	$this->ajaxReturn(1, L('operation_success'), $upload['decoded_response']['data']['imgUrl'] );
			else	return $this->ajaxReturn(0, '图片上传失败');
    	} else {
    		return $this->ajaxReturn(0, L('illegal_parameters'));
    	}    
    }
    
    /**
     * 删除领路费图片样例
     */
    public function deleteExpensesPhoto()
    {
    	$id         	= $this->_post('pid','intval');
        $photo			= $this->_post('photo', 'trim');

		if( $id > 0 )
		{
        	M(NULL,NULL,C('DB_property'));
			if(D('property')->expenses_update($id,array('photo'=>'')) === FALSE)	return $this->ajaxReturn(0, '删除失败');
		}
		
        $api_response	= oss_del_image_url( 'photo', $image );
        
		if($api_response == TRUE)	$this->ajaxReturn(1, '删除成功');
		else						$this->ajaxReturn(0, '删除失败');
    }
    
    //添加路费
    public function expenses_add(){
        $author  = $_COOKIE['admin']['username'];
        $id      = $this->_request('id','intval');//楼盘ID
        $menuid  = $this->_request('menuid', 'trim');

        if(IS_POST){
			M(NULL,'fph_',C('DB_property'));
            $time_start   	= $this->_post('time_start','trim');
            $time_end     	= $this->_post('time_end','trim');
			$partners     	= $this->_post('partners','trim');
			$fangpinhui   	= $this->_post('fangpinhui','trim');
			$manner       	= $this->_post('manner','trim');
			$pid          	= $this->_post('id','intval');
			$share        	= $this->_post('share','intval');
			$machine_code	= $this->_post('machine_code','trim');
			$type			= $this->_post('type','intval');
			$check_gps		= $this->_post('check_gps','intval');
			$photo			= $this->_post('photo','trim');
			
			if($manner == 1){
				$total_amount = $this->_post('total_amount','trim');
				$copies       = $this->_post('copies','trim');
				$rule         = $this->_post('rule','trim');
			}elseif($manner == 2){
				$total_amount = $this->_post('total_amount2','trim');
				$copies       = $this->_post('copies2','trim');
				$rule         = $this->_post('rule2','trim');

				$toll_amount_small = $this->_post('toll_amount_small','trim');
				$toll_amount_max   = $this->_post('toll_amount_max','trim');
				$number            = $this->_post('number','trim');
				$amount 		   = $this->_post('amount','trim');

				$tmp  = count($toll_amount_small);
				$temp = array();
				for($i = 0; $i< $tmp; $i++)
				{
					$temp[$i] = array(
						'toll_amount_small' => $toll_amount_small[$i],
						'toll_amount_max' => $toll_amount_max[$i],
						'number' => $number[$i],
						'amount' => $amount[$i],
					);
				}
			}
							
			
			//验证
			$parse_role		= explode('.', $rule);
			if(date('Y-m-d',strtotime($time_start)) != $time_start)	return $this->error('开始时间格式错误');
			if(date('Y-m-d',strtotime($time_end)) != $time_end)		return $this->error('结束时间格式错误');
			if(strtotime( $time_start ) > strtotime( $time_end ))	return $this->error('开始时间不能大于结束时间');
			if($manner != 1 && $manner != 2)						return $this->error('发放方式异常.');
			if( $copies < 1 || $copies > 99999999 )					return $this->error('路费总份数不能少于1,不能大于99,999,999.');
			if(count( $parse_role ) > 2)							return $this->error('路费金额必须是数字.');
			if(isset( $parse_role[1] ) && $parse_role[1] > 99)		return $this->error('路费金额必须小数位数最多支持两位.');
			if( $rule < 0.01 || $rule > 999999.99)					return $this->error('路费金额不能小于0.01,不能大于999.999.99');
			if(mb_strlen( $machine_code, 'utf8' ) > 45)				return $this->error('广告机编码不能超过45个字');
			if(!in_array( $type, array(1,2,3,4)))					return $this->error('领取方式异常');
			if($check_gps != TRUE && $check_gps != FALSE)			return $this->error('是否GPS验证格式错误');
			if(mb_strlen($photo,'utf8') > 100)						return $this->error('图片路径格式异常');
			if( $total_amount != $fangpinhui + $partners)			return $this->error('总金额必须和路费来源金额相等');
			if( $manner == 1 && $rule * $copies > $total_amount )	return $this->error('固定金额的总金额不能小于发放份数×路费金额');
			if( $manner == 2 ){
				$total_rand_number	= 0;
				$total_amount_small	= 0;
				foreach( $temp AS $val )
				{
					if( $val['toll_amount_small'] > $val['toll_amount_max'] )			return $this->error('最小路费金额不能大于最大路费金额.');
					if( $val['toll_amount_small'] * $val['number'] > $val['amount'] )	return $this->error('最小金额×路费份数不能大于路费总金额.');
					$total_amount_small	= $total_amount_small + $val['toll_amount_small'];
					$total_rand_number	= $total_rand_number + $val['number'];
				}
				if( $total_amount_small > $total_amount )								return $this->error('随机发放路费总金额不能大于总金额.');
				if( $total_rand_number != $copies )										return $this->error('随机发放份数必须等于总份数.');
			}
			if(D('property')->expenses_count_status($pid))			return $this->error('系统中存在有效数据');
			

			$data['time_start']   = strtotime( $time_start );
			$data['time_end']     = strtotime( $time_end );
			$data['partners']     = $partners;
			$data['fangpinhui']   = $fangpinhui;
			$data['manner']       = $manner;
			$data['total_amount'] = $total_amount;
			$data['copies']       = $copies;
			$data['rule']         = $rule;
			$data['pid']          = $pid;
			$data['share']        = $share;
			$data['machine_code'] = $machine_code;
			$data['type']		  = $type;
			$data['check_gps']    = $check_gps;
			$data['photo']     	  = $photo;
			$data['add_time']     = time();
            $result = D('property')->expenses_insert($data);
            if($result ){
				if($manner == 2){
					//写入扩展表
					foreach($temp as $key => $val){
						$extendData['rule_id'] = $result;
						$extendData['toll_amount_small'] = $val['toll_amount_small'];
						$extendData['toll_amount_max']   = $val['toll_amount_max'];
						$extendData['number'] = $val['number'];
						$extendData['amount'] = $val['amount'];
						M('expenses_extend')->add($extendData);
					}
											
				}
                //修改的数据写入缓存
                $redis = new CacheRedis($this->RedisDataBase);
                $redis->lRem('propertyMQ', $pid , 0);
                $redis->lpush('propertyMQ', $pid);
                
                if($manner == 2){
                	//路费id写入redis
                	$redis->lRem('randRuleMQ', $result, 0);
                	$redis->lpush('randRuleMQ', $result);
                }

				//路费code
				$redis->lRem('propertyCodeMQ', $pid , 0);
				$redis->lpush('propertyCodeMQ', $pid);

				//路费数据
				$redis = new CacheRedis($this->RedisDataBaseEXPENSES);
				//删除原有数据
				$redis->rm('property.expenses.'.$pid);
				$info = D('property')->expenses_info($result);
				$info['receive'] = 0;
				$redis->handler->hmset('property.expenses.'.$pid.'',$info);
                $this->success('添加成功');
                exit;
            }else{
                $this->error('添加失败');
            }
        }

        $this->assign('id', $id);
        $this->assign('menuid', $menuid);
        $this->display();
    }

    //删除路费
    public function expenses_delete(){
        $id = $this->_request('id','intval');
        M(NULL,NULL,C('DB_property'));
        $info = D('property')->expenses_info($id);
		//判断删除到是否为有效数据
		$time = time();
		$result = D('property')->expenses_delete($id);
        if($result){
			if($info['time_start'] <= $time AND $info['time_end'] >= $time AND $info['status'] == 1){
				//修改的数据写入缓存
				$redis = new CacheRedis($this->RedisDataBase);
				$redis->lRem('propertyMQ', $info['pid'] , 0);
				$redis->lpush('propertyMQ', $info['pid']);
			}

			$redis = new CacheRedis($this->RedisDataBaseEXPENSES);
			$redis->rm('property.expenses.'.$info['pid']);
            $this->ajaxReturn(1, '删除成功');
        }else{
            $this->ajaxReturn(0, '删除失败');
        }
    }

    //编辑路费
    public function expenses_edit(){
		$id   = $this->_request('id','intval');
        $pid = $this->_request('pid','intval');
		$receive_copies = D('property')->expenses_count_copies($pid);
		$this->assign('receive_copies', $receive_copies);
        M(NULL,NULL,C('DB_property'));
		$info = D('property')->expenses_info($pid);
		$this->assign('info', $info);

		//金额随机
		if($info['manner'] == 2){
			$extend_list = M('expenses_extend')->where('rule_id = '.$info['id'])->select();
			$this->assign('extend_list', $extend_list);
		}
        if(IS_POST){
            $time_start 	= $this->_post('time_start','trim');
            $time_end   	= $this->_post('time_end','trim');
            //$rule     = $this->_post('rule','trim');
			$copies     = $this->_post('copies','intval');
            $id         = $this->_post('pid','intval');
            $pid        = $this->_post('id','intval');
			$share      = $this->_post('share','intval');
			
			$machine_code	= $this->_post('machine_code','trim');
			$type			= $this->_post('type','intval');
			$check_gps		= $this->_post('check_gps','intval');
			$photo			= $this->_post('photo','trim');
						
			if(date('Y-m-d',strtotime($time_start)) != $time_start)	return $this->error('开始时间格式错误');
			if(date('Y-m-d',strtotime($time_end)) != $time_end)		return $this->error('结束时间格式错误');
			if(strtotime( $time_start ) > strtotime( $time_end ))	return $this->error('开始时间不能大于结束时间');
			if(mb_strlen( $machine_code, 'utf8' ) > 45)				return $this->error('广告机编码不能超过45个字');
			if(!in_array( $type, array(1,2,3,4)))					return $this->error('领取方式异常');
			if($check_gps != TRUE && $check_gps != FALSE)			return $this->error('是否GPS验证格式错误');
			if(mb_strlen($photo,'utf8') > 100)						return $this->error('图片路径格式异常');
			
			$expenses_count = D('property')->expenses_count_status($pid, $time_end, $id);
			if($expenses_count)										return $this->error('系统中存在有效数据');

			/*if($copies < $receive_copies){
				$this->error('路费总份数不能小于'.$receive_copies.'份');
			}*/
			$data['time_start'] 	= strtotime($time_start);
			$data['time_end']   	= strtotime($time_end);
			//$data['copies']     = $copies;
			$data['share']      	= $share;
			$data['machine_code']   = $machine_code;
			$data['type']      		= $type;
			$data['check_gps']     	= $check_gps;
			$data['photo']     		= $photo;

            if(false !== D('property')->expenses_update($id,$data)){
                //修改的数据写入缓存
				$redis = new CacheRedis($this->RedisDataBaseEXPENSES);
				if($data['time_start'] > time() || time() > $data['time_end'])
				{

					$redis->rm('property.expenses.'.$pid);
				} else {
					$data['rule']    = $info['rule'];
					$data['pid']     = $pid;
					$data['id']      = $id;
					$data['copies']      = $copies;
					$data['receive'] = $receive_copies;
					$redis->handler->hmset('property.expenses.'.$pid.'', $data);
				}
				$redis2 = new CacheRedis($this->RedisDataBase);
				$redis2->lRem('propertyMQ', $pid , 0);
				$redis2->lpush('propertyMQ', $pid);
                $this->success('修改成功');
                exit;
            }else{
                $this->error('修改失败');
            }
        }
        $this->assign('id', $id);
        $this->display();
    }

	public function fund(){
		$id = $this->_request('id','intval');
		$this->assign('id', $id);
		$this->assign('type', 'fund');
		M('fund','fph_',C('DB_property'));
		$fund = M('fund');
		if(IS_POST){
			$data['pid']     		= $this->_post('pid','intval');
			$data['money']         	= $this->_post('money','intval');
			$data['time_start']    	= strtotime($this->_post('time_start','trim'));
			$data['time_end']      	= strtotime($this->_post('time_end','trim'));
			$where = array('pid'=>$data['pid']);
			if($fund->where($where)->find()){
				$result = $fund->where($where)->save($data);
			}else{
				$result = $fund->where($where)->add($data);
			}
			if($result){
				//修改的数据写入缓存
				$redis = new CacheRedis($this->RedisDataBase);
				$redis->lRem('propertyMQ', $data['pid'] , 0);
				$redis->lpush('propertyMQ', $data['pid']);
				$this->success('修改成功');
				exit;
			}else{
				$this->error('修改失败');
			}
		}else{
			$info = $fund->where(array('pid'=>$id))->find();
			if(!$info){
				$info = array(
					'pid'=>$id,
					'time_start' =>'',
					'time_end' =>''
				);
			}
			$this->assign('info', $info);
		}
		$this->display();
	}

	//ajax读取所有楼盘标题
	public function propertyList(){
		$title = $this->_post('title','trim');
		if($title){
			$where = 'title like "%'.$title.'%" ';
			$field = 'id,title';
			$order = 'add_time DESC';
			$limit = 50;
			$list = D('property')->propertyList($where, $field, $order, $limit);
			if($list){
				$str = "<ul class='popup'>";
				foreach($list as $val) {
					$title = $val['title'];
					$str .= "<li rel=".$val['id'].">".msubstr($title,0,35,'utf-8',true)."</li>";
				}
				$str .= '</ul>';
			}
		}else{
			$this->ajaxReturn(0, '请输入要搜索的内容');
		}
		$this->ajaxReturn(1, '成功', $str);
	}

}