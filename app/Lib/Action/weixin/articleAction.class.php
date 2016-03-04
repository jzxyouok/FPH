<?php
class articleAction extends frontendAction {
    
    public function index() {
		$id = $this->_get('id','intval');
        $info = M('weixin_article')->field('title,author,info,add_time')->where(array('id'=>$id))->find();
		$this->assign('info',$info);
        $this->assign('setTitle', $info['title']);
        $this->_config_seo();
        $this->display();
    }
}