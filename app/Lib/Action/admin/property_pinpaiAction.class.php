<?php
class property_pinpaiAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
	$this->_mod = D('property_pinpai');
    }

    public function _before_index() {
	    $p = $this->_get('p','intval',1);
        $this->assign('p',$p);
	
	    //默认排序
        $this->sort = 'ordid';
        $this->order = 'ASC';
    }
    
    protected function _before_insert($data) {
    	Vendor("firstpy.py");
    	$py = new py();
    	$business  = $this->_post('business', 'trim');
    	$data['letter'] = $py->getFirstPY($business);
    	
    	$found_time  = $this->_post('found_time', 'trim');
    	$data["found_time"] = strtotime($found_time);
    	$data['banner_img']  = $this->_post('banner_img', 'trim');
    	$data['logo']  = $this->_post('logo', 'trim');
    	$data['image_img']  = $this->_post('image_img', 'trim');
        return $data;
    }
    
    protected function _before_update($data) {
    	Vendor("firstpy.py");
    	$py = new py();
    	$business  = $this->_post('business', 'trim');
    	$data['letter'] = $py->getFirstPY($business);
    	
    	$found_time  = $this->_post('found_time', 'trim');
    	$data["found_time"] = strtotime($found_time);
    	$data['banner_img']  = $this->_post('banner_img', 'trim');
    	$data['logo']  = $this->_post('logo', 'trim');
    	$data['image_img']  = $this->_post('image_img', 'trim');
        return $data;
    }

    public function _before_delete() {
    	$id = $this->_request('id','intval');
    	M('property_pinpai')->where('id='.$id)->delete();
    }
    
    public function ajax_logo_img() {
        //上传图片 logo
        if (!empty($_FILES['img']['name'])) {
		
			$fdfs_obj = new FastFile();
			$result = $fdfs_obj->fdfs_upload('img','220','65','_220x65',true);
			if($result){
				$ext = $result['group_name'].'/'.$result['filename'];
				$savename = str_replace('.', '_220x65.', $ext);
				$this->ajaxReturn(1, L('operation_success'), $savename);
			}else{
				 $this->ajaxReturn(0, '上传图片出错');
			}
			
			/*$dir = date('Ymd');
            $result = $this->_upload($_FILES['img'], 'pinpai/logo/'. $dir, array('width'=>'220', 'height'=>'65', 'remove_origin'=>true));
            if ($result['error']) {
                $this->ajaxReturn(0, $result['info']);
            } else {
                $ext = array_pop(explode('.', $result['info'][0]['savename']));
                $data['img'] = str_replace('.' . $ext, '_thumb.' . $ext, $result['info'][0]['savename']);
                $this->ajaxReturn(1, L('operation_success'), $dir .'/'. $data['img']);
            }*/
        } else {
            $this->ajaxReturn(0, L('illegal_parameters'));
        }
    }
    
    public function ajax_banner_img() {
        //上传图片 banner
        if (!empty($_FILES['img']['name'])) {
		
			$fdfs_obj = new FastFile();
			$result = $fdfs_obj->fdfs_upload('img','990,528','500,300','_l,_s',false);
			if($result){
				$savename = $result['group_name'].'/'.$result['filename'];
				$this->ajaxReturn(1, L('operation_success'), $savename);
			}else{
				 $this->ajaxReturn(0, '上传图片出错');
			}
			/*$dir = date('Ymd');
            $result = $this->_upload($_FILES['img'], 'pinpai/banner/'. $dir, array('width'=>'990,528', 'height'=>'500,300', 'suffix'=>'_l,_s' ,'remove_origin'=>true));
            if ($result['error']) {
                $this->ajaxReturn(0, $result['info']);
            } else {
                $ext = array_pop(explode('.', $result['info'][0]['savename']));
                $data['img'] = str_replace('.' . $ext, '_s.'.$ext, $result['info'][0]['savename']);
                $this->ajaxReturn(1, L('operation_success'), $dir .'/'. $data['img']);
            }*/
        } else {
            $this->ajaxReturn(0, L('illegal_parameters'));
        }
    }
    
    
    public function ajax_image_img() {
        //上传图片 image
        if (!empty($_FILES['img']['name'])) {
		
			$fdfs_obj = new FastFile();
			$result = $fdfs_obj->fdfs_upload('img','340','220','_340x220',true);
			if($result){
				$ext = $result['group_name'].'/'.$result['filename'];
				$savename = str_replace('.', '_340x220.', $ext);
				$this->ajaxReturn(1, L('operation_success'), $savename);
			}else{
				 $this->ajaxReturn(0, '上传图片出错');
			}
			/*$dir = date('Ymd');
            $result = $this->_upload($_FILES['img'], 'pinpai/image/'. $dir, array('width'=>'340', 'height'=>'220', 'remove_origin'=>true));
            if ($result['error']) {
                $this->ajaxReturn(0, $result['info']);
            } else {
                $ext = array_pop(explode('.', $result['info'][0]['savename']));
                $data['img'] = str_replace('.' . $ext, '_thumb.' . $ext, $result['info'][0]['savename']);
                $this->ajaxReturn(1, L('operation_success'), $dir .'/'. $data['img']);
            }*/
        } else {
            $this->ajaxReturn(0, L('illegal_parameters'));
        }
    }
    
    /**
     * ajax检测开放商名称是否存在
     */
    public function ajax_check_name() {
        $business = $this->_get('business', 'trim');
        $id = $this->_get('id', 'intval');
        if ($this->_mod->name_exists($business,  $id)) {
            $this->ajaxReturn(0, '该开放商已经存在');
        } else {
            $this->ajaxReturn();
        }
    }
    
    
    //删除图片
    public function del_img(){
        $img_path = $this->_post('img_path', 'trim');
		$suffix = array('_l','_s');
		
		$fdfs_obj = new FastFile();
		if(strstr($img_path,'_220x65') || strstr($img_path,'_340x220')){
			$result = $fdfs_obj->fast_del_img($img_path);
		}else{
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
		/*$img = explode('.',$img_path);
        $thumb = '.'.$img[1].'_thumb.'.$img[2];
		if(strstr($img_path,'_s')){
			$thumb_l = str_replace('_s','_l',$img_path);
			unlink($img_path);
			unlink($thumb_l);
		}
		unlink($thumb);
		$this->ajaxReturn(1, '删除成功');*/
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
}