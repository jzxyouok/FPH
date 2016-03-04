<?php
class home_floAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('home_flo');
    }
    public function _before_index() {
        
        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);
        //默认排序
        $this->sort = 'ordid';
        $this->order = 'ASC,add_time DESC';
    }

    protected function _search() {
        $map = array();
        ($time_start = $this->_request('time_start', 'trim')) && $map['add_time'][] = array('egt', strtotime($time_start));
        ($time_end = $this->_request('time_end', 'trim')) && $map['add_time'][] = array('elt', strtotime($time_end)+(24*60*60-1));
        $status = $this->_request('status', 'trim');
        ($keyword = $this->_request('keyword', 'trim')) && $map['title'] = array('like', '%'.$keyword.'%');
        if($status!=''){
            $map['status'] = $status;
        }
        $this->assign('search', array(
            'time_start' => $time_start,
            'time_end' => $time_end,
            'status'  => $status,
            'keyword' => $keyword,
        ));
        return $map;
    }

    protected function _before_insert($data) {
        $data['add_time'] = time();
        return $data;
    }

    public function _before_edit(){
        $id = $this->_get('id','intval');
    }
    
    //上传图片
    public function ajax_upload_img() {
        $type = $this->_get('type', 'trim', 'img');
        if (!empty($_FILES[$type]['name'])) {
			$fdfs_obj = new FastFile();
			$result = $fdfs_obj->fdfs_upload('img');
			if($result){
				$savename = $result['group_name'].'/'.$result['filename'];
				$this->ajaxReturn(1, L('operation_success'), $savename);
			}else{
				 $this->ajaxReturn(0, '上传图片出错');
			}
            /*$dir = date('ym/d/');
            $result = $this->_upload($_FILES[$type], 'home/'. $dir );
            if ($result['error']) {
                $this->ajaxReturn(0, $result['info']);
            } else {
                $savename = $dir . $result['info'][0]['savename'];
                $this->ajaxReturn(1, L('operation_success'), $savename);
            }*/
        } else {
            $this->ajaxReturn(0, L('illegal_parameters'));
        }
    }
     //删除图片
    public function del_img(){
        $img_path = $this->_post('img_path', 'trim');
		$fdfs_obj = new FastFile();
		$result = $fdfs_obj->fast_del_img($img_path);
		if($result){
			$this->ajaxReturn(1, '删除成功');	
		}else{
			$this->ajaxReturn(0, '删除失败');
		}
    }
    //后台图片列表'浏览'
    public function info(){
        $id = $this->_get('id','intval');
        $info = M('home_flo')->where(array('id'=>$id))->find();
	$this->assign('info',$info);
        $this->assign('setTitle', $info['title']);
        $this->display();
    }
}