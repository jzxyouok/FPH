<?php
class brokerAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('broker');
    }

    public function _before_index() {
		//echo $_SESSION['admin']['id'];
		//echo $_SESSION['admin']['role_id'];
        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);

        //默认排序
        $this->sort = 'ordid';
        $this->order = 'ASC';
    }

}