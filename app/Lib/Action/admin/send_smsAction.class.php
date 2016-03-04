<?php
class send_smsAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = M('send_sms');
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
        ($mobile = $this->_request('mobile', 'trim')) && $map['mobile'] = array('like', '%'.$mobile.'%');
		($time_start = $this->_request('time_start', 'trim')) && $map['add_time'][] = array('egt', strtotime($time_start));
        ($time_end = $this->_request('time_end', 'trim')) && $map['add_time'][] = array('elt', strtotime($time_end)+(24*60*60-1));
        $this->assign('search', array(
            'mobile' => $mobile,
			'time_start' => $time_start,
            'time_end' => $time_end,
        ));
        return $map;
    } 

}