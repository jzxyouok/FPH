<?php
 
class FastFile{

    public function __construct(){
        $this->tracker = fastdfs_tracker_get_connection();
        $this->server = fastdfs_connect_server($this->tracker['ip_addr'], $this->tracker['port']);
        $this->storage = fastdfs_tracker_query_storage_store();
 
        $this->server = fastdfs_connect_server($this->storage['ip_addr'], $this->storage['port']);
        if (!$this->server){
            error_log("errno1: " . fastdfs_get_last_error_no() . ", error info: " . fastdfs_get_last_error_info());
            exit(1);
        }
 
        $this->storage['sock'] = $this->server['sock'];
 
    }
	
	/*
	上传文件
	@$input_name      file 名称(name)
	@$thumbnailWidth  缩略图宽度
	@$thumbnailHeight 缩略图高度
	@$suffix          缩略图后缀
	@$remove_origin   缩略图生成之后是否删除原图 true删除 false不删除
	return            返回上传之后文件名
	*/
    public function fdfs_upload($input_name,$thumbnailWidth,$thumbnailHeight,$suffix,$remove_origin){
        $file_tmp  = $_FILES[$input_name]['tmp_name'];
        $real_name = $_FILES[$input_name]['name'];
        $file_name = dirname($file_tmp)."/".$real_name;
        //@copy($file_tmp, $file_name);
        rename($file_tmp, $file_name);
 		
		//上传源文件
        $file_info = fastdfs_storage_upload_by_filename($file_name, null, array(), null, $this->tracker, $this->storage);
		
		//是否生成缩略图
		if($thumbnailWidth && $thumbnailHeight && $suffix){
			$format      = explode('.',$_FILES[$input_name]['name']);//文件格式后缀 *.jpg
			$info        = $this->getImageInfo($file_name);//文件信息
			$thumbWidth	 = explode(',',$thumbnailWidth);
			$thumbHeight = explode(',',$thumbnailHeight);
            $thumbSuffix = explode(',',$suffix);
			foreach($thumbWidth as $key=>$val){
				$this->_render_thumbnail(file_get_contents($file_name),$info,$thumbWidth[$key],$thumbHeight[$key],$file_info['group_name'],$file_info['filename'],$thumbSuffix[$key],$format[1]);
			}
			//生成缩略图之后删除原图
			if($remove_origin){
				$this->fdfs_group_img_del($file_info['group_name'],$file_info['filename']);
			}
		}
        //print_r($file_info);
        /*if($file_info){
            $group_name = $file_info['group_name'];
            $remote_filename = $file_info['filename'];
 
            $i = fastdfs_get_file_info($group_name, $remote_filename);
            $storage_ip = $i['source_ip_addr'];
            //var_dump($file_info);
            return array($remote_filename, $group_name, $storage_ip, $real_name);
        }*/
        return $file_info;
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
		fastdfs_storage_upload_slave_by_filebuff1($ret, $group_name.'/'.$filename, $thumbSuffix,$format);
		// echo  $ret;
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
	
 	
	/*
	//下载服务器文件
	@$group_name 组名 * group1
	@$file_id 文件名  * M00/00/01/Cqh4zFVvu0KAdkvpAACOoWbNQs0561.jpg
	*/
    public function fdfs_down($group_name, $file_id){
        $file_content = fastdfs_storage_download_file_to_buff($group_name, $file_id);
        return $file_content;
    }
 	
	/*
	//删除服务器文件
	@$group_name 组名 * group1
	@$file_id 文件名  * M00/00/01/Cqh4zFVvu0KAdkvpAACOoWbNQs0561.jpg
	*/
    public  function fdfs_group_img_del($group_name, $file_id){
        $data = fastdfs_storage_delete_file($group_name, $file_id);
		return $data;
    }
	
	/*
	//删除服务器文件
	@$groupname_fileid 组名+文件名 * group1/M00/00/01/Cqh4zFVvu0KAdkvpAACOoWbNQs0561.jpg
	*/
	public function fast_del_img($groupname_fileid){
		$data = fastdfs_storage_delete_file1($groupname_fileid);
		return $data;
	}

}
 