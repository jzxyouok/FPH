<?php
class uploadAction extends frontendAction {

    public function uploadify(){
		$m = date('Ymd');
    	$targetFolder = $_POST['url']; // Relative to the root
    	$targetPath = $m.'/';
		//echo $_POST['token'];
		$verifyToken = md5($_POST['timestamp']);
		$thumbMaxWidth = $this->_post('thumbMaxWidth', 'trim');
		$thumbSuffix = $this->_post('thumbSuffix', 'trim');
		$thumb = explode(',', $thumbSuffix);
		
		
		if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
				import("ORG.Net.UploadFile");
				$name=time().rand();	//设置上传图片的规则
				
				$upload = new UploadFile();   //实例化上传类
				$upload->autoCheck=true;      //是否自动检测附件
   				$upload->uploadReplace=true;  //如果存在同名文件是否进行覆盖
				
				//缩略图
				$upload->thumb          = true;    
				$upload->thumbMaxWidth  = '100,480'; //缩略图宽   
				$upload->thumbMaxHeight = '75,360'; //缩略图高
				$upload->thumbPrefix    = '';
				$upload->thumbSuffix    = $thumbSuffix;  //缩略图后缀
				$upload->thumbPath      = '';    

				$upload->maxSize  = 3145728 ;// 默认为-1，不限制上传大小
				$upload->allowExts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
				$upload->savePath =  './data/upload/property/'.$m.'/';// 设置附件上传目录
				$upload->saveRule = $name;  //设置上传图片的规则
				$upload->thumbRemoveOrigin=0;//如果生成缩略图，是否删除原图

				if(!$upload->upload()) {// 上传错误提示错误信息
				//return false;
				echo $upload->getErrorMsg();
				//echo $targetPath;
			}else{// 上传成功 获取上传文件信息
				$info =  $upload->getUploadFileInfo();
				echo $targetPath.$info[0]["savename"];//返回原图
				
				//$a = explode(".",$info[0]["savename"]);//返回缩略图
				//echo $targetPath.$a[0].$thumb[0].'.'.$a[1];
			}
		}
    }
	//删除图片-组图
	public function del_imgarr(){
		if($_POST['name']!=""){
			$info = $this->_post('name', 'trim');
			$thumbSuffix = $this->_post('thumbSuffix', 'trim');
			$thumb = explode(",",$thumbSuffix);
			foreach($thumb as $key=>$val){
				$a = explode(".",$info);
				unlink('./data/upload/property/'.$a[0].$val.'.'.$a[1]);
    		}
			unlink('./data/upload/property/'.$info);
			$this->ajaxReturn(1, '删除成功');
    	}else{
    		$this->ajaxReturn(1, '删除失败');
		}
    }
	
	//更新数据库
	public function updatepicarr(){
		if( IS_POST ){
			$id = $this->_post('id', 'intval');
			$picarr = $this->_post('picarr', 'trim');
			$db = $this->_post('db', 'trim');
			M($db)->where(array('id'=>$id,'uid'=>$this->visitor->info['id']))->save(array('picarr' => $picarr));
		}
	}
	
	//更新数据库
	

}