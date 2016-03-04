<?php
class friends_prizeAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = M('friends_prize');
    }

    public function _before_index() {
    	
    	$user = $this->_mod->field('id,uid')->select();
    	$name=array();
    	foreach ($user as $key => $val){
    		$name[$val['id']]= M('user')->where('id ='.$val['uid'])->getField('username').' '.M('user')->where('id ='.$val['uid'])->getField('mobile');
    	}
    	$this->assign('name',$name);
        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);
        //默认排序
        $this->sort = 'add_time';
        $this->order = 'DESC';
    }
   
    protected function _search() {
        $map = array();
		($time_start = $this->_request('time_start', 'trim')) && $map['add_time'][] = array('egt', strtotime($time_start));
        ($time_end = $this->_request('time_end', 'trim')) && $map['add_time'][] = array('elt', strtotime($time_end)+(24*60*60-1));
        ($status = $this->_request('status', 'trim'));
        if($status!=''){
        	$map['status'] = $status;
        }
        $this->assign('search', array(
            'status' => $status,
			'time_start' => $time_start,
            'time_end' => $time_end,
        ));
        return $map;
    } 
    
   public function  ajax_status(){
  
     	$id = $this->_post('id','intval'); //	$this->ajaxReturn($id,'未发放');exit();
     	if($id){
        	$resul	= $this->_mod->where('id = '.$id)->save(array('status'=>1));
        	$this->ajaxReturn(1,'','已发放'); exit;
     	}
     	$this->ajaxReturn(0,'','未发放');
   }

}