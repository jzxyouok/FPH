<?php
class weixin_yijianAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('weixin_yijian');
    }

    public function _before_index() {
        

        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);

        //默认排序
        $this->sort = 'add_time';
        $this->order = 'ASC';
    }

    protected function _search() {
        $map = array();
        ($time_start = $this->_request('time_start', 'trim')) && $map['add_time'][] = array('egt', strtotime($time_start));
        ($time_end = $this->_request('time_end', 'trim')) && $map['add_time'][] = array('elt', strtotime($time_end)+(24*60*60-1));
		 $this->assign('search', array(
            'time_start' => $time_start,
            'time_end' => $time_end,
        ));
        return $map;
    }

    public function _before_add()
    {
       

    }


    protected function _before_insert($data) {
		
        return $data;
    }

    public function _before_edit(){
       
    }

    protected function _before_update($data) {

        return $data;
    }
}