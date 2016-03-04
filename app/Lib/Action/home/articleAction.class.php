<?php
class articleAction extends frontendAction {
    
    public function index() {
	   
        $this->assign('setTitle', $info['title']);
        $this->_config_seo();
        $this->display();
    }
    public function about(){

        //关于我们
		$this->assign('setTitle', '关于我们');
        $this->_config_seo();
        $this->display();
    }
    public function contact(){
        //联系我们
		
		$info = M('article_page')->field('title,info')->where(array('id'=>1))->find();
		$this->assign('info',$info);
	
		$this->assign('setTitle', $info['title']);
        $this->_config_seo();
        $this->display();
    }

    public function service(){

        //关于我们
        $this->assign('setTitle', '服务协议');
        $this->_config_seo();
        $this->display();
    }

}