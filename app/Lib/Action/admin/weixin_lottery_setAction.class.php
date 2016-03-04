<?php
class weixin_lottery_setAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('weixin_lottery_set');
    }

    public function _before_index() {

        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);

        //默认排序
        $this->sort = 'id';
        $this->order = 'ASC';
    }

    protected function _search() {
        $map = array();
        ($time_start = $this->_request('time_start', 'trim')) && $map['add_time'][] = array('egt', strtotime($time_start));
        ($time_end = $this->_request('time_end', 'trim')) && $map['add_time'][] = array('elt', strtotime($time_end)+(24*60*60-1));
        $status = $this->_request('status', 'trim');
        ($keyword = $this->_request('keyword', 'trim')) && $map['title'] = array('like', '%'.$keyword.'%');
        $cate_id = $this->_request('cate_id', 'intval');
		if($status!=''){
			$map['status'] = $status;
		}
        $selected_ids = '';
        if ($cate_id) {
            $id_arr = $this->_cate_mod->get_child_ids($cate_id, true);
            $map['cate_id'] = array('IN', $id_arr);
            $spid = $this->_cate_mod->where(array('id'=>$cate_id))->getField('spid');
            $selected_ids = $spid ? $spid . $cate_id : $cate_id;
        }
        $this->assign('search', array(
            'time_start' => $time_start,
            'time_end' => $time_end,
            'cate_id' => $cate_id,
            'selected_ids' => $selected_ids,
            'status'  => $status,
            'keyword' => $keyword,
        ));
        return $map;
    }

    public function _before_add()
    {
        $author = $_COOKIE['admin']['username'];
        $this->assign('author',$author);

      

    }

    protected function _before_insert($data) {
		$time_start = $this->_post('time_start','trim');
		$time_end   = $this->_post('time_end','trim');
		$data['time_start'] = strtotime($time_start);
		$data['time_end']   = strtotime($time_end);
        
        return $data;
    }

    public function _before_edit(){
        $id = $this->_get('id','intval');
        
    }

    protected function _before_update($data) {
		$time_start = $this->_post('time_start','trim');
		$time_end   = $this->_post('time_end','trim');
		$data['time_start'] = strtotime($time_start);
		$data['time_end']   = strtotime($time_end);
		

        return $data;
    }
	
	
	//上传奖品图片
	public function ajax_upload_img() {
        //上传图片
        if (!empty($_FILES['img']['name'])) {
			$dir = date('Ymd');
            $result = $this->_upload($_FILES['img'], 'jiangpin/'. $dir, array('width'=>'300', 'height'=>'300', 'remove_origin'=>true));
            if ($result['error']) {
                $this->ajaxReturn(0, $result['info']);
            } else {
                $ext = array_pop(explode('.', $result['info'][0]['savename']));
                $data['img'] = str_replace('.' . $ext, '_thumb.' . $ext, $result['info'][0]['savename']);
                $this->ajaxReturn(1, L('operation_success'), $dir .'/'. $data['img']);
            }
        } else {
            $this->ajaxReturn(0, L('illegal_parameters'));
        }
    }
	
	 //删除图片
    public function del_img(){
        $img_path = $this->_post('img_path', 'trim');
        if(unlink($img_path)){
            $this->ajaxReturn(1, '删除成功');
        }else{
             $this->ajaxReturn(0, '删除失败');
        }
    }
}