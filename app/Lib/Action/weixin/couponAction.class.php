<?php
class couponAction extends frontendAction {

	public function _initialize() {
		parent::_initialize();
		$this->_mod = M('coupon');
	}
	
   public function index() {
	   $id = $this->_get('str','intval');	
	   $info = $this->_mod->field('mobile')->where(array('id'=>$id))->find();
	   $this->assign('info',$info);
   	
       $this->display();
    }
	
    	
}