<?php
class joinusAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('joinus');
    }

    public function _before_index() {
        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);
         
        //默认排序
        $this->sort = 'id';
        $this->order = 'DESC';
    }

    protected function _before_insert($data) {
        $data['add_time']  = time();
        return $data;
    }

    protected function _before_update($data) {
        $data['add_time']  = time();
        return $data;
    }

}