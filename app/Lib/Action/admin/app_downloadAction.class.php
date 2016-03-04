<?php
class app_downloadAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('app_download');
    }

    public function _before_index() {

        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);
         
        //默认排序
        $this->sort = 'add_time';
        $this->order = 'DESC';
    }

    protected function _search() {
        $map = array();
        
        return $map;
    }

    public function _before_add(){  
    	
        $author = $_COOKIE['admin']['username'];
        $this->assign('author',$author);
       
    }

    protected function _before_insert($data) {

        return $data;
    }

    public function _before_edit(){
    	
        $id = $this->_get('id','intval');
    }

    protected function _before_update($data) {
		

        return $data;
    }
}