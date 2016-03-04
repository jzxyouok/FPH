<?php
class indexAction extends frontendAction {
    
    public function index() {

        $this->assign('setTitle', '房品汇客户端下载');
        $this->_config_seo();
        $this->display();
    }

    public function mobile() {

        $this->assign('setTitle', '房品汇客户端下载');
        $this->_config_seo();
        $this->display();
    }

    public function mobileNew() {

        $this->assign('setTitle', '房品汇客户端下载');
        $this->_config_seo();
        $this->display();
    }
}