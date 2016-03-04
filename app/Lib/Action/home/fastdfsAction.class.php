<?php
class fastdfsAction extends frontendAction {
    
    public function index() {
	   //$this->_render_thumbnail(file_get_contents('http://www.baidu.com/img/bdlogo.png'),'image/png');
	   //$info = $this->getImageInfo('http://a.pic1.ajkimg.com/display/xinfang/0e135f6e9720709618ec43890299b311/403x335n.jpg');
	  // print_r($info);exit;
	  // $this->_render_thumbnail(file_get_contents('http://a.pic1.ajkimg.com/display/xinfang/0e135f6e9720709618ec43890299b311/403x335n.jpg'),$info['mime']);
       
		if(IS_POST){
			$fdfs_obj = new FastFile();
    		$res = $fdfs_obj->fdfs_upload('upload1','100,200,300','100,200,300','_100X100,_200X200,_300X300',false);
			
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
	
	//楼盘缩略图
	public function reset_property_thumb_img(){
		$info = M('property')->field('img_thumb')->limit(2)->select();
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
			$original_file1 = '/home/www/www/static/css/admin/bgimg/logo_login.gif';
			$res = fastdfs_storage_upload_by_filename($original_file1, null, array(), null, $tracker, $storage);
			foreach($suffix as $k=>$v){
				
				$original_file2 = '/home/www/www/data/upload/property/thumbnail/'.$img_thumb[0].$v.'.'.$img_thumb[1].'';
				$thumb_res = fastdfs_storage_upload_slave_by_filename($original_file2, $res['group_name'], $res['filename'], $v);
				print_r($thumb_res).'<br>';
			}
			fastdfs_storage_delete_file($res['group_name'], $res['filename']);
			echo '<br><br>---';
		}
		//print_r($info);
	}
	
	
	/*
	二进制数据流生成缩略图
	@$data 二进制图像数据流
	@$mimetype 图像类型
	@$thumbnailHeight 缩略图高度
	@$thumbnailWidth 缩略图宽度
	return 缩略图的二进制数据流
	*/
	public function _render_thumbnail($data, $info,$thumbnailHeight=30,$thumbnailWidth=30,$group_name,$filename,$format){
        if ($info['mime'] !== 'image/jpeg' && $info['mime'] !== 'image/pjpeg' && $info['mime'] !== 'image/gif' && $info['mime'] !== 'image/png'){
            throw new Exception('Unsupported file type.');
        }

        $ret = '';

        $imageType = substr($info['mime'], 6);
 		$image = imagecreatefromstring($data);

		$srcWidth = $info['width'];
		$srcHeight = $info['height'];
		$scale = min($thumbnailHeight / $srcWidth, $thumbnailWidth / $srcHeight); // 计算缩放比例
		if ($scale >= 1) {
			// 超过原图大小不再缩略
			$width = $srcWidth;
			$height = $srcHeight;
		} else {
			// 缩略图尺寸
			$width = (int) ($srcWidth * $scale);
			$height = (int) ($srcHeight * $scale);
		}
        
        //创建图片
		$factor = $thumbnailHeight / $height;
		$targetWidth = round($factor * $width);
		$imageNew = imagecreatetruecolor($width, $height);
		imagecopyresampled($imageNew, $image, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);

        ob_start();

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
		fastdfs_storage_upload_slave_by_filebuff1($ret, $group_name.'/'.$filename, '_10X10',$format);
       // echo  $ret;
    }
	
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
