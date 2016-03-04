<?php
class operationactivityAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
         M(NULL,NULL,C('DB_OPERATION_ACTIVITY'));
        $this->_mod = D('operationactivity');
    }

    public function index() {

       
        $order .= 'counts desc';
        $count = $this->_mod->group('openid')->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();
        $list = $this->_mod->group('openid')->field('id,openid,signdate,signtime,count(openid) as counts')->limit($p->firstRow.','.$p->listRows)->order($order)->select();  
         //   $this->_mod->getlastsql(); 
         //print_r($list);            
        $this->assign('list', $list);
        $this->assign('page',$page);
      
        $this->display();
    }

    
    

}