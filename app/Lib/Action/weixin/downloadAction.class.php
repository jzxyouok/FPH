<?php
class downloadAction extends frontendAction {
    
    public function index() {
		
		$ios = M('app_download')->field('title,size,url,add_time')->where(array('type'=>1))->order('add_time DESC')->find();
		$this->assign('ios', $ios);
		
		$android = M('app_download')->field('title,size,url,add_time')->where(array('type'=>2))->order('add_time DESC')->find();
		$this->assign('android', $android);
	   
        $this->assign('setTitle', '房品汇手机客户端下载');
        $this->_config_seo();
        $this->display();
    }
}