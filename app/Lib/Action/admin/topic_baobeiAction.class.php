<?php
class topic_baobeiAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('topic_baobei');
    }

    public function _before_index() {
        $res = M('user')->field('id,username')->select();
        $user_list = array();
        foreach ($res as $val) {
            $user_list[$val['id']] = $val['username'];
        }
        $this->assign('user_list', $user_list);
        
        
        $res = M('property')->field('id,title')->select();
        $property_list = array();
        foreach ($res as $val) {
        	$property_list[$val['id']] = $val['title'];
        }
        $this->assign('property_list', $property_list);
        

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
        $status = $this->_request('status', 'trim');
        ($keyword = $this->_request('keyword', 'trim')) && $map['title'] = array('like', '%'.$keyword.'%');
        $cate_id = $this->_request('cate_id', 'intval');
		if($status!=''){
			$map['status'] = $status;
		}
        $selected_ids = '';
        if ($cate_id) {
            $id_arr = $this->_cate_mod->get_child_ids($cate_id, true);
            $map['cate_id'] = array('IN', $id_arr);
            $spid = $this->_cate_mod->where(array('id'=>$cate_id))->getField('spid');
            $selected_ids = $spid ? $spid . $cate_id : $cate_id;
        }
        $this->assign('search', array(
            'time_start' => $time_start,
            'time_end' => $time_end,
            'cate_id' => $cate_id,
            'selected_ids' => $selected_ids,
            'status'  => $status,
            'keyword' => $keyword,
        ));
        return $map;
    }

    
}