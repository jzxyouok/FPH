<?php
class joinusAction extends frontendAction {
    
    public function index() {
        $list = M('joinus')->field('title,people,duty,requirement')->order('add_time desc')->where(array('status'=>1))->select();
        $this->assign('list',$list);
		
		$this->assign('setTitle', '加入我们');
        $this->_config_seo();
        $this->display();
    }
}