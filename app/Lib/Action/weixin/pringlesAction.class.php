<?php
class pringlesAction extends frontendAction {
    
    //微信品客邦分享页面
    public function info() {
		$id = $this->_get('id','intval');
        $info = M('pringles')->field('title,author,img,info,add_time')->where(array('id'=>$id))->find();
		$this->assign('info',$info);

        $this->assign('setTitle', $info['title']);
        $this->_config_seo();
        $this->display();
    }
}