<?php
class promotion_activityAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        M('promotion_activity','fph_',C('DB_activity'));
    }

    public function index() {
        $pageSize = 20;
        $list = D('promotion_activity')->lists($pageSize);
        $this->assign('list',$list[0]);
        $this->assign('page',$list[1]);

        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);
        $this->display();
    }


}