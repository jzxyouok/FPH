<?php
class hezuo_yongjin_ruleAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('hezuo_yongjin_rule');
        $this->_cate_mod = D('hezuo_yongjin');
    }

    public function _before_index() {
		
    }

    public function _before_add(){
		$id  = $this->_get('id','intval');
		
		$ca = C('DB_PREFIX');
		$str = 'A.id,A.pid,A.tiaodian,C.title';
		$info = $this->_cate_mod->field($str)->table("{$ca}hezuo_yongjin AS A 
													 LEFT JOIN {$ca}hezuo_property AS B ON B.id=A.pid 
													 LEFT JOIN {$ca}property AS C ON C.id=B.pid")
											 ->where(array('A.id'=>$id))
											 ->order('A.id DESC')->find();
		$this->assign('info', $info);
		
		$porduct = M('hezuo_property_product')->field('id,name,youhui')->where(array('pid'=>$info['pid']))->order('id ASC')->select();
		$this->assign('porduct', $porduct);
    }

    protected function _before_insert($data){
		
        return $data;
    }

    public function _before_edit(){
        $id = $this->_get('id','intval');
       
    }

    protected function _before_update($data) {
        return $data;
    }
	
	public function ajax_youhui(){
		$id = $this->_post('id','intval');
		if(!$id){
			$this->ajaxReturn(0, '非法参数');
		}
		$youhui = M('hezuo_property_product')->where(array('id'=>$id))->getfield('youhui');
		$this->ajaxReturn(1, '',$youhui);
	}
	
	public function minus(){
		$total_price = $this->_post('total_price','trim');
		$share_price = $this->_post('share_price','trim');
		if($total_price && $share_price){
			$data = $total_price-$share_price;
			$this->ajaxReturn(1, '',$data);
		}else{
			$this->ajaxReturn(0, '非法参数');
		}
	}
	
	/**
     * 
     */
    public function ajax_check_name() {
        $pid = $this->_get('pid', 'intval');
        $id = $this->_get('id', 'intval');
        if ($this->_mod->name_exists($pid,  $id)) {
            $this->ajaxReturn(0, '该产品已经存在');
        } else {
            $this->ajaxReturn();
        }
    }
}