<?php
class resetfdfsAction extends frontendAction {
    
    public function index() {
	   //$this->_render_thumbnail(file_get_contents('http://www.baidu.com/img/bdlogo.png'),'image/png');
	   //$info = $this->getImageInfo('http://a.pic1.ajkimg.com/display/xinfang/0e135f6e9720709618ec43890299b311/403x335n.jpg');
	  // print_r($info);exit;
	  // $this->_render_thumbnail(file_get_contents('http://a.pic1.ajkimg.com/display/xinfang/0e135f6e9720709618ec43890299b311/403x335n.jpg'),$info['mime']);
       
		if(IS_POST){
			$fdfs_obj = new FastFile();

    		$res = $fdfs_obj->fdfs_upload('upload1','100,280,640,800','75,210,480,600','_100x75,_280x210,_640x480,_800x600',false);
			
			/*$file_tmp = $_FILES["upload1"]['tmp_name'];
			$real_name = $_FILES["upload1"]['name'];
			$file_name = dirname($file_tmp)."/".$real_name;
			rename($file_tmp, $file_name);
			$format = explode('.',$_FILES["upload1"]['name']);
			//fastdfs_storage_upload_slave_by_filename($file_name, $res['group_name'], $res['filename'], '_10X10');
			$info = $this->getImageInfo($file_name);
			$this->_render_thumbnail(file_get_contents($file_name),$info,100,100,$res['group_name'],$res['filename'],$format[1]);
			//$this->thumbimg($file_name,100,100,$res['group_name'],$res['filename']);*/
			print_r($res);

		}
		$this->display();
    }
	
	//01 楼盘缩略图*表 property
	//ALTER TABLE `fph_property` ADD `img_thumb_t` VARCHAR( 150 ) NULL COMMENT '楼盘缩略图' AFTER `img_thumb` ;
	public function reset_property_thumb_img(){
		$info = M('property')->where("img_thumb!=''")->field('id,img_thumb')->limit(0,387)->select();
        //print_r($info);exit;
		
		$suffix = array('_app_list_thumb','_app_thumb','_pc_thumb','_weixin_thumb');
		//print_r($suffix);
		
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
		
		foreach($info as $key=>$val){
			$img_thumb = explode('.',$val['img_thumb']);
			//先上传一张图片*获取到图片名
			$original_file1 = 'static/css/admin/bgimg/logo_login.gif';
			$res = fastdfs_storage_upload_by_filename($original_file1, null, array(), null, $tracker, $storage);
			foreach($suffix as $k=>$v){
				//再上传所有图片
				$original_file2 = 'data/upload/property/thumbnail/'.$img_thumb[0].$v.'.'.$img_thumb[1].'';
				if(file_exists($original_file2)){
					$thumb_res = fastdfs_storage_upload_slave_by_filename($original_file2, $res['group_name'], $res['filename'], $v);
				}
					$img_path = $thumb_res['group_name'].'/'.$thumb_res['filename'];
					echo '<a href=http://img.corp.com/'.$img_path.' target=_blank>'.$img_path.'</a><br>';
			}
			
			$img_gs = $res['group_name'].'/'.$res['filename'];
			$img_thumb = str_replace('.gif','.'.$img_thumb[1],$img_gs);
			//写入数据库
			M('property')->where(array('id'=>$val['id']))->save(array('img_thumb'=>$img_thumb));
			//删除源文件
			fastdfs_storage_delete_file($res['group_name'], $res['filename']);
			$yt_img_path = $res['group_name'].'/'.$res['filename'];
			echo '<a href=http://img.corp.com/'.$yt_img_path.' target=_blank style="color:#ff0000">'.$yt_img_path.'</a><br><br>';
		}
		//print_r($info);
	}
	
	//02 楼盘户型图*表 property_housetype
	//ALTER TABLE `fph_property_housetype` ADD `house_img_t` VARCHAR( 150 ) NULL COMMENT '楼盘户型图' AFTER `house_img` ;
	public function reset_property_housetype_img(){
		$info = M('property_housetype')->where("house_img!=''")->field('id,house_img')->select();
        //print_r($info);exit;
		$width  = array('100','280','640','800');
		$height = array('75','210','480','600');
		$suffix = array('_100x75','_280x210','_640x480','_800x600');
		
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
		
		foreach($info as $key=>$val){
			$original_file1 = 'data/upload/property/huxing/'.$val['house_img'].'';
			if(file_exists($original_file1)){
				$res = fastdfs_storage_upload_by_filename($original_file1, null, array(), null, $tracker, $storage);
				$format = explode('.',$original_file1);
				$info   = $this->getImageInfo($original_file1);//文件信息
				foreach($width as $k=>$v){
					$thumb_res = $this->_render_thumbnail(file_get_contents($original_file1),$info,$width[$k],$height[$k],$res['group_name'],$res['filename'],$suffix[$k],$format[1]);
					echo '<a href=http://img.corp.com/'.$thumb_res.' target=_blank>'.$thumb_res.'</a><br>';
				}
				//写入数据库
				M('property_housetype')->where(array('id'=>$val['id']))->save(array('house_img'=>$res['group_name'].'/'.$res['filename']));
			}
			$yt_img_path = $res['group_name'].'/'.$res['filename'];
			echo '<a href=http://img.corp.com/'.$yt_img_path.' target=_blank style="color:#ff0000">'.$yt_img_path.'</a><br><br>';
		}
	}
	
	//03 楼盘效果图等*表 property_img
	//ALTER TABLE `fph_property_img` ADD `img_t` VARCHAR( 150 ) NULL COMMENT '楼盘图片' AFTER `img` ;
	//ALTER TABLE `fph_property_img` ADD `focus_img` TINYINT( 1 ) NOT NULL DEFAULT '0' COMMENT '焦点图 1是 0否' AFTER `type` ;
	public function reset_property_img(){
		set_time_limit(0);//设置不超时 
		ini_set('memory_limit', '512M');//设置PHP能使用的内存大小
		
		$info = M('property_img')->where("img!=''")->field('id,status,img')->limit(2000,3000)->select();

		$width  = array('800','720','640','480','360','100');
		$height = array('600','540','480','360','240','75');
		$suffix = array('_800x600','_720x540','_640x480','_480x360','_360x240','_100x75');
		
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
		foreach($info as $key=>$val){
			//获取文件夹
			if($val['status']==1){
				$dir = 'xiaoguo';
			}elseif($val['status']==2){
				$dir = 'guihua';
			}elseif($val['status']==3){
				$dir = 'peitao';
			}elseif($val['status']==4){
				$dir = 'shijing';
			}elseif($val['status']==5){
				$dir = 'jiaotong';
			}elseif($val['status']==6){
				$dir = 'yangban';
			}
			$original_file1 = 'data/upload/property/'.$dir.'/'.$val['img'].'';
			if(file_exists($original_file1)){
				$res = fastdfs_storage_upload_by_filename($original_file1, null, array(), null, $tracker, $storage);
				$format = explode('.',$original_file1);
				$info   = $this->getImageInfo($original_file1);//文件信息
				foreach($width as $k=>$v){
					$thumb_res = $this->_render_thumbnail(file_get_contents($original_file1),$info,$width[$k],$height[$k],$res['group_name'],$res['filename'],$suffix[$k],$format[1]);
					echo '<a href=http://img.corp.com/'.$thumb_res.' target=_blank>'.$thumb_res.'</a><br>';
				}
				//写入数据库
				M('property_img')->where(array('id'=>$val['id']))->save(array('img'=>$res['group_name'].'/'.$res['filename']));
			}
			$yt_img_path = $res['group_name'].'/'.$res['filename'];
			echo '<a href=http://img.corp.com/'.$yt_img_path.' target=_blank style="color:#ff0000">'.$yt_img_path.'</a>-'.$val['id'].'<br><br>';
			
		}
	}
	
	//04 活动图片 表*article
	//ALTER TABLE `fph_article` ADD `img_t` VARCHAR( 150 ) NULL COMMENT '图片' AFTER `img` ;
	public function reset_article_img(){
		set_time_limit(0);//设置不超时 
		ini_set('memory_limit', '512M');//设置PHP能使用的内存大小
		
		$info = M('article')->where("img!=''")->field('id,img')->select();
		$width  = array('720');
		$height = array('540');
		$suffix = array('_720x540');
		
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
		foreach($info as $key=>$val){
			$img_thumb = explode('.',$val['img']);
			//先上传一张图片*获取到图片名
				//再上传所有图片
				$original_file1 = 'data/upload/article/'.$val['img'].'';
				if(file_exists($original_file1)){
					$res = fastdfs_storage_upload_by_filename($original_file1, null, array(), null, $tracker, $storage);
					//缩略图
					$format = explode('.',$original_file1);
					$info   = $this->getImageInfo($original_file1);//文件信息
					foreach($width as $k=>$v){
					$thumb_res = $this->_render_thumbnail(file_get_contents($original_file1),$info,$width[$k],$height[$k],$res['group_name'],$res['filename'],$suffix[$k],$format[1]);
						echo '<a href=http://img.corp.com/'.$thumb_res.' target=_blank>'.$thumb_res.'</a><br>';
					}
				}
				$res_img = $res['group_name'].'/'.$res['filename'];
				echo '<a href=http://img.corp.com/'.$res_img.' target=_blank>'.$res_img.'</a><br>';
			//写入数据库
			M('article')->where(array('id'=>$val['id']))->save(array('img'=>$res_img));
			//删除源文件
			//fastdfs_storage_delete_file($res['group_name'], $res['filename']);
		}
	}
	
	//05 首页热销合作楼盘图片 表*home_flo 可直接更新img
	//ALTER TABLE `fph_home_flo` ADD `img_t` VARCHAR( 150 ) NULL COMMENT '热销楼盘图片' AFTER `img` ;
	public function reset_home_flo_img(){
		$info = M('home_flo')->field('id,img')->select();
		
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
		foreach($info as $key=>$val){
			$original_file1 = 'data/upload/home/'.$val['img'].'';
			if(file_exists($original_file1)){
				$res = fastdfs_storage_upload_by_filename($original_file1, null, array(), null, $tracker, $storage);
				//写入数据库
				M('home_flo')->where(array('id'=>$val['id']))->save(array('img'=>$res['group_name'].'/'.$res['filename']));
			}
			$thumb_res = $res['group_name'].'/'.$res['filename'];
			echo '<a href=http://img.corp.com/'.$thumb_res.' target=_blank>'.$thumb_res.'</a><br>';
		}
	}
	
	//06 合作媒体图片 表*media
	//ALTER TABLE `fph_media` ADD `img_t` VARCHAR( 150 ) NULL COMMENT '媒体图片' AFTER `img` ;
	public function reset_media_img(){
		set_time_limit(0);//设置不超时 
		ini_set('memory_limit', '512M');//设置PHP能使用的内存大小
		
		$info = M('media')->where("img != ''")->field('id,img')->select();
		
		//print_r($info);exit;
		
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
		foreach($info as $key=>$val){
			//先上传一张图片*获取到图片名
			$original_file1 = 'data/upload/media/'.$val['img'].'';
			if(file_exists($original_file1)){
				$res = fastdfs_storage_upload_by_filename($original_file1, null, array(), null, $tracker, $storage);
				//写入数据库
				M('media')->where(array('id'=>$val['id']))->save(array('img'=>$res['group_name'].'/'.$res['filename']));
			}
			$thumb_res = $res['group_name'].'/'.$res['filename'];
			echo '<a href=http://img.corp.com/'.$thumb_res.' target=_blank>'.$thumb_res.'</a><br>';
		}
	}
	
	//07 品客邦图片 表*pringles
	//ALTER TABLE `fph_pringles` ADD `img_t` VARCHAR( 150 ) NULL COMMENT '品客帮图片' AFTER `img` ;
	public function reset_pringles_img(){
		
		$info = M('pringles')->where("img != ''")->field('id,img')->select();
		$width  = array('527','848');
		$height = array('568','948');
		$suffix = array('_ios','_android');
		
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
		
		foreach($info as $key=>$val){
			$original_file1 = 'data/upload/pringles/'.$val['img'].'';
			if(file_exists($original_file1)){
				$res = fastdfs_storage_upload_by_filename($original_file1, null, array(), null, $tracker, $storage);
				$format = explode('.',$original_file1);
				$info   = $this->getImageInfo($original_file1);//文件信息
				foreach($width as $k=>$v){
					$thumb_res = $this->_render_thumbnail(file_get_contents($original_file1),$info,$width[$k],$height[$k],$res['group_name'],$res['filename'],$suffix[$k],$format[1]);
					echo '<a href=http://img.corp.com/'.$thumb_res.' target=_blank>'.$thumb_res.'</a><br>';
				}
				//写入数据库
				M('pringles')->where(array('id'=>$val['id']))->save(array('img'=>$res['group_name'].'/'.$res['filename']));
			}
			$thumb_res = $res['group_name'].'/'.$res['filename'];
			echo '<a href=http://img.corp.com/'.$thumb_res.' target=_blank>'.$thumb_res.'</a><br><br>';
		}
	}
	
	//08 开发商品牌图片 表*property_pinpai
	//ALTER TABLE `fph_property_pinpai` ADD `logo_t` VARCHAR( 255 ) NULL COMMENT 'logo图片' AFTER `logo` ;
	//ALTER TABLE `fph_property_pinpai` ADD `banner_img_t` VARCHAR( 150 ) NULL COMMENT 'banner图片' AFTER `banner_img` ;
	//ALTER TABLE `fph_property_pinpai` ADD `img_t` VARCHAR( 150 ) NULL COMMENT '形象图片' AFTER `img` ;
	public function reset_property_pinpai_img(){
		$info = M('property_pinpai')->field('id,logo,banner_img,img')->select();
		
		$width_logo  = array('220');
		$height_logo = array('65');
		$suffix_logo = array('_220x65');
		
		$width_banner  = array('990','500');
		$height_banner = array('528','300');
		$suffix_banner = array('_l','_s');
		
		$width_image  = array('340');
		$height_image = array('220');
		$suffix_image = array('_340x220');
		
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
		
		foreach($info as $key=>$val){
			//处理logo
			$img_thumb_log = explode('.',$val['logo']);
			//先上传一张图片*获取到图片名
			$original_file1 = 'static/css/admin/bgimg/logo_login.gif';
			
			
			//logo***************************
			$original_file_logo = 'data/upload/pinpai/logo/'.$val['logo'].'';
			if(file_exists($original_file_logo) && $val['logo']){
				$res_logo    = fastdfs_storage_upload_by_filename($original_file_logo, null, array(), null, $tracker, $storage);
				$format_logo = explode('.',$original_file_logo);//后缀格式
				$info_logo   = $this->getImageInfo($original_file_logo);//文件信息
				foreach($width_logo as $k=>$v){
					$thumb_res_logo = $this->_render_thumbnail(file_get_contents($original_file_logo),$info_logo,$width_logo[$k],$height_logo[$k],$res_logo['group_name'],$res_logo['filename'],$suffix_logo[$k],$format_logo[1]);
					echo '<a href=http://img.corp.com/'.$thumb_res_logo.' target=_blank>'.$thumb_res_logo.'</a><br>';
				}
				//写入数据库
				if($val['logo']){
					M('property_pinpai')->where(array('id'=>$val['id']))->save(array('logo'=>$thumb_res_logo));
				}
			}
			//删除原图
			fastdfs_storage_delete_file($res_logo['group_name'], $res_logo['filename']);
			$res_logo = $res_logo['group_name'].'/'.$res_logo['filename'];
			echo '<a href=http://img.corp.com/'.$res_logo.' target=_blank>'.$res_logo.'</a>-logo<br>';
			
			//banner***************************
			$img_thumb_banne = explode('.',$val['banner_img']);
			$original_file_banner = 'data/upload/pinpai/banner/'.$img_thumb_banne[0].'_l.'.$img_thumb_banne[1];
			$original_file_banner = str_replace('_s','',$original_file_banner);
			if(file_exists($original_file_banner) && $val['banner_img']){
				$res_banner    = fastdfs_storage_upload_by_filename($original_file_banner, null, array(), null, $tracker, $storage);
				$format_banner = explode('.',$original_file_banner);//后缀格式
				$info_banner   = $this->getImageInfo($original_file_banner);//文件信息
				foreach($width_banner as $k=>$v){
					$thumb_res_banner = $this->_render_thumbnail(file_get_contents($original_file_banner),$info_banner,$width_banner[$k],$height_banner[$k],$res_banner['group_name'],$res_banner['filename'],$suffix_banner[$k],$format_banner[1]);
					echo '<a href=http://img.corp.com/'.$thumb_res_banner.' target=_blank>'.$thumb_res_banner.'</a><br>';
				}
				//写入数据库
				if($val['banner_img']){
					M('property_pinpai')->where(array('id'=>$val['id']))->save(array('banner_img'=>$res_banner['group_name'].'/'.$res_banner['filename']));
				}
				$res_banner = $res_banner['group_name'].'/'.$res_banner['filename'];
				echo '<a href=http://img.corp.com/'.$res_banner.' target=_blank>'.$res_banner.'</a>-banner<br>';
			}
			
			//image**************************
			$original_file_image = 'data/upload/pinpai/image/'.$val['img'].'';
			if(file_exists($original_file_image) && $val['img']){
				$res_image = fastdfs_storage_upload_by_filename($original_file_image, null, array(), null, $tracker, $storage);
				$format_image = explode('.',$original_file_image);//后缀格式
				$info_image   = $this->getImageInfo($original_file_image);//文件信息
				foreach($width_image as $k=>$v){
					$thumb_res_image = $this->_render_thumbnail(file_get_contents($original_file_image),$info_image,$width_image[$k],$height_image[$k],$res_image['group_name'],$res_image['filename'],$suffix_image[$k],$format_image[1]);
					echo '<a href=http://img.corp.com/'.$thumb_res_image.' target=_blank>'.$thumb_res_image.'</a><br>';
				}
				//写入数据库
				if($val['img']){
					M('property_pinpai')->where(array('id'=>$val['id']))->save(array('img'=>$thumb_res_image));
				}
			}
			//删除原图
			fastdfs_storage_delete_file($res_image['group_name'], $res_image['filename']);
			$res_image = $res_image['group_name'].'/'.$res_image['filename'];
			echo '<a href=http://img.corp.com/'.$res_image.' target=_blank>'.$res_image.'</a>-image<br><br>';

		}
	}
	
	//09 店铺头像图片 表*stores
	//ALTER TABLE `fph_stores` ADD `img_t` VARCHAR( 150 ) NULL COMMENT '店铺logo' AFTER `img` ;
	public function reset_stores_img(){
		set_time_limit(0);//设置不超时 
		ini_set('memory_limit', '512M');//设置PHP能使用的内存大小
		
		$info = M('stores')->where("img != ''")->field('id,img')->select();
		$width  = array('100');
		$height = array('70');
		$suffix = array('_100x70');
		
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
		
		foreach($info as $key=>$val){
			$original_file1 = 'data/upload/stores_avatar/'.$val['img'].'';
			if(file_exists($original_file1)){
				$res = fastdfs_storage_upload_by_filename($original_file1, null, array(), null, $tracker, $storage);
				$format = explode('.',$original_file1);
				$info   = $this->getImageInfo($original_file1);//文件信息
				foreach($width as $k=>$v){
					$thumb_res = $this->_render_thumbnail(file_get_contents($original_file1),$info,$width[$k],$height[$k],$res['group_name'],$res['filename'],$suffix[$k],$format[1]);
					echo '<a href=http://img.corp.com/'.$thumb_res.' target=_blank>'.$thumb_res.'</a><br>';
				}
				//写入数据库
				if($val['img']){
					M('stores')->where(array('id'=>$val['id']))->save(array('img'=>$res['group_name'].'/'.$res['filename']));
				}
			}
			$res_image = $res['group_name'].'/'.$res['filename'];
			echo '<a href=http://img.corp.com/'.$res_image.' target=_blank>'.$res_image.'</a><br><br>';
		}
	}
	
	//10 用户头像 表*user
	//ALTER TABLE `fph_user` ADD `avatar_t` VARCHAR( 150 ) NULL COMMENT '用户头像' AFTER `avatar` ;
	public function reset_user_avatar(){
		set_time_limit(0);//设置不超时 
		ini_set('memory_limit', '512M');//设置PHP能使用的内存大小
		
		$info = M('user')->where("avatar != ''")->field('id,avatar')->limit(0,30000)->select();
		$width  = array('100','64');
		$height = array('100','64');
		$suffix = array('_100x100','_64x64');
		
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
		
		foreach($info as $key=>$val){
			$img_thumb = explode('.',$val['avatar']);
			
			$original_file1 = 'data/upload/avatar/'.$img_thumb[0].'_100.'.$img_thumb[1].'';
			if(file_exists($original_file1)){
				$res = fastdfs_storage_upload_by_filename($original_file1, null, array(), null, $tracker, $storage);
				$format = explode('.',$original_file1);
				$info   = $this->getImageInfo($original_file1);//文件信息
				foreach($width as $k=>$v){
					$thumb_res = $this->_render_thumbnail(file_get_contents($original_file1),$info,$width[$k],$height[$k],$res['group_name'],$res['filename'],$suffix[$k],$format[1]);
					echo '<a href=http://img.corp.com/'.$thumb_res.' target=_blank>'.$thumb_res.'</a><br>';
				}
				//写入数据库
				if($val['avatar']){
					M('user')->where(array('id'=>$val['id']))->save(array('avatar'=>$res['group_name'].'/'.$res['filename']));
				}
				$res_image = $res['group_name'].'/'.$res['filename'];
				echo '<a href=http://img.corp.com/'.$res_image.' target=_blank>'.$res_image.'</a><br><br>';
			}
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*
	二进制数据流生成缩略图
	@$data 二进制图像数据流
	@$mimetype 图像类型
	@$thumbnailHeight 缩略图高度
	@$thumbnailWidth 缩略图宽度
	return 缩略图的二进制数据流
	*/
	public function _render_thumbnail($data,$info,$thumbnailWidth,$thumbnailHeight,$group_name,$filename,$thumbSuffix,$format){
		//header("content-type:image/jpeg");
		//echo $data;
		// file is not an image
		if ($info['mime'] !== 'image/jpeg' && $info['mime'] !== 'image/pjpeg' && $info['mime'] !== 'image/gif' && $info['mime'] !== 'image/png'){
			throw new Exception('Unsupported file type.');
		}
	
		$ret = '';
		
		$imageType = substr($info['mime'], 6);
		$image = imagecreatefromstring($data);
	
		/*$width  = imagesx($image);
		$height = imagesy($image);*/
		//echo $width.'--'.$height;exit;
	
		$srcWidth = $info['width'];
		$srcHeight = $info['height'];
		$scale = min($thumbnailWidth / $srcWidth, $thumbnailHeight / $srcHeight); // 计算缩放比例
		if ($scale >= 1) {
			// 超过原图大小不再缩略
			$width = $srcWidth;
			$height = $srcHeight;
		} else {
			// 缩略图尺寸
			$width = (int) ($srcWidth * $scale);
			$height = (int) ($srcHeight * $scale);
		}
		//echo $width.'--'.$height;exit;
	
	
		//创建图片
		$factor = $thumbnailHeight / $height;
		$targetWidth = round($factor * $width);
		$imageNew = imagecreatetruecolor($width, $height);
		imagecopyresampled($imageNew, $image, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
		
		ob_start();
		
		//生成的缩略图质量
		switch ($imageType){
			case 'jpeg' : imagejpeg($imageNew, null, 100);
				break;
			case 'pjpeg' : imagejpeg($imageNew, null, 100);
				break;
			case 'gif'  : imagegif($imageNew);
				break;
			case 'png'  : imagepng($imageNew, null, 0);
				break;
		}
	
		imagedestroy($imageNew);
		$ret = ob_get_clean();
		//上传缩略图
		$data = fastdfs_storage_upload_slave_by_filebuff1($ret, $group_name.'/'.$filename, $thumbSuffix,$format);
		return $data;
		// echo  $ret;
	}
	
	
	//删除图片
	public function fast_del_img(){
		//$res = fastdfs_storage_delete_file('group1', 'M00/00/01/Cq_EtFVvuyGAa4MvAAB-Ycapfso205_10X10.jpg');
		$res = fastdfs_storage_delete_file1('group1/M00/00/01/Cq_EtFVvuyGAa4MvAAB-Ycapfso205.jpg');
		print_r($res);
	}

	
	public function thumbimg(){
    	$res = $this->thumb('http://www.baidu.com/img/bdlogo.png','123.jpg','',300,300);

	}
	
	/**
     * 取得图像信息
     * @static
     * @access public
     * @param string $image 图像文件名
     * @return mixed
     */
	public function getImageInfo($img) {
        $imageInfo = getimagesize($img);
        if ($imageInfo !== false) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
            $imageSize = filesize($img);
            $info = array(
                "width" => $imageInfo[0],
                "height" => $imageInfo[1],
                "type" => $imageType,
                "size" => $imageSize,
                "mime" => $imageInfo['mime']
            );
            return $info;
        } else {
            return false;
        }
    }
	
	
	
	
	
    

}