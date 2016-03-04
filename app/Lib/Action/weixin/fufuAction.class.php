<?php
class fufuAction extends frontendAction {
    
    public function index() {
	    $domain = $_SERVER['HTTP_HOST'];
		$current_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$this->assign('current_url', $current_url);
		$send_code = session('send_code',random(6,1)); 
		$send_code = session('send_code');
		$url = urlencode(__ROOT__.'/index.php?g=weixin&m=fufu&a=links');
		$uid = $this->visitor->info['id'];
		$this->assign('domain', $domain);
		$this->assign('url', $url);
		$this->assign('uid', $uid);
		$this->assign('send_code', $send_code);
        $this->_config_seo();
        $this->display();
    }
	public function links() {
		$this->display();
	}
    
}