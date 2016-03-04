<?php
class weixin_guaguaka_totalAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('weixin_guaguaka_total');
        $this->_cate_mod = D('user');
    }

    public function _before_index() {
        $res = $this->_cate_mod->field('id,username,mobile')->select();
        $cate_list = array();
		$cate_mobile = array();
        foreach ($res as $val) {
            $cate_list[$val['id']] = $val['username'];
			$cate_mobile[$val['id']] = $val['mobile'];
        }
        $this->assign('cate_list', $cate_list);
		$this->assign('cate_mobile', $cate_mobile);
		
		//刮中奖总金额，发放刮奖总金额， 刮中奖总人数，
		$win_total=$this->_mod->where('pid=1')->sum('total');
		$win_people_total=$this->_mod->where('pid=1')->count('id');
		$win_issue_total=$this->_mod->where('pid=2')->sum('the_phone');
		$win_already_total=$this->_mod->where('pid=2 and status=1')->sum("the_phone");

		$this->assign('win_total', $win_total);
		$this->assign('win_people_total', $win_people_total);
		$this->assign('win_issue_total', $win_issue_total);
		$this->assign('win_already_total', $win_already_total);
		
        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);
         
        $pid=$this->_get('pid','intval',1);
        $this->assign('pid',$pid);
        //默认排序
        $this->sort  = 'total';
        $this->order = 'DESC';

    }

    protected function _search() {  
        $map = array();
        ($time_start = $this->_request('time_start', 'trim')) && $map['add_time'][] = array('egt', strtotime($time_start));
        ($time_end = $this->_request('time_end', 'trim')) && $map['add_time'][] = array('elt', strtotime($time_end)+(24*60*60-1));
        ($total = $this->_request('total', 'trim')) && $map['total'] = array('egt', $total);
        ($pid = $this->_request('pid', 'trim')) && $map['pid'] = array('eq', $pid);
        $username = $this->_request('username', 'trim');//用户名
        if($username){
        	$user_id = M('user')->where('`username` = "'.$username.'"')->getField('id');
        	$map['uid'] = array('eq', $user_id);
        }
        $mobile = $this->_request('mobile', 'trim');//手机号
        if($mobile){
        	$user_id = M('user')->where('mobile ='.$mobile)->getField('id');
        	$map['uid'] = array('eq', $user_id);
        }
        
        
        $pid=$this->_get('pid','intval',1);
        if ($pid){
        	$map['pid'] = array('eq', $pid);
        }
        
        $this->assign('search', array(
            'time_start' => $time_start,
            'time_end' => $time_end,
            'pid' => $pid,
            'total' => $total,
        	'mobile'=>$mobile,
        	'username'=>$username,	
        ));
        return $map;
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
    
    
    
    public function _before_delete(){
    	
    	$id = $this->_get('id','intval');
    	if(M('weixin_guaguaka_total')->where('pid=1 and id='.$id)->getField('total'))
    		IS_AJAX && $this->ajaxReturn(0, L('刮奖金额未提现完'));
    	if(!M('weixin_guaguaka_total')->where('pid=2 and id='.$id)->getField('status'))
    		IS_AJAX && $this->ajaxReturn(0, L('未领取奖金'));
    }
    
    
}