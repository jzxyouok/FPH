<?php
class hezuo_property_productAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('hezuo_property_product');
        $this->_cate_mod = D('property');
    }

    public function _before_index() {
       

        //默认排序
        $this->sort = 'id';
        $this->order = 'ASC';
    }


    public function _before_add(){
       $id = $this->_request('id','intval');
	   $this->assign('id', $id);
	   
    }

    protected function _before_insert($data) {
		$pid = $this->_post('pid','intval');
		$start_time = strtotime($this->_post('start_time','trim'));
		$end_time = strtotime($this->_post('end_time','trim'));
		$id_count = $this->_cate_mod->where(array('id'=>$pid))->count('id');
		!$id_count && $this->ajaxReturn(0, L('illegal_parameters'));
		if($start_time > $end_time){
			$this->ajaxReturn(0, '结束时间必须大于开始时间');
		}
		$data['start_time'] = $start_time;
		$data['end_time']   = $end_time;
		
        return $data;
    }

    public function _before_edit(){
        $id = $this->_request('id','intval');
		
    }

    protected function _before_update($data) {
     	$pid = $this->_post('pid','intval');
		$id  = $this->_post('id','intval');
		$start_time = strtotime($this->_post('start_time','trim'));
		$end_time = strtotime($this->_post('end_time','trim'));
		$id_count = $this->_cate_mod->where(array('id'=>$pid))->count('id');
		!$id_count && $this->ajaxReturn(0, L('illegal_parameters'));
		
		if($start_time > $end_time){
			$this->ajaxReturn(0, '结束时间必须大于开始时间');
		}
		$data['start_time'] = $start_time;
		$data['end_time']   = $end_time;
		
        return $data;
    }
	
	
	
	
}