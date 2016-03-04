<?php
class app_pushAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('app_push');
        $this->_cate_mod = D('app_push_cate');
    }

    public function _before_index() {
    	
    	//楼盘
    	$property_res = M('property')->field('id,title')->select(); 
    	$property_list = array();
    	foreach ($property_res as $val) {
    		$property_list[$val['id']] = $val['title'];
    	}
    	$this->assign('property_list',$property_list);
    	//楼盘活动
    	$article_res  = M('article')->field('id,title')->select();
    	$article_list = array();
    	foreach ($article_res as $val) {
    		$article_list[$val['id']] = $val['title'];
    	}
    	$this->assign('article_list',$article_list);
    	//文章
    	$pringles_res = M('pringles')->field('id,title')->select();
    	$pringles_list = array();
    	foreach ($pringles_res as $val) {
    		$pringles_list[$val['id']] = $val['title'];
    	}
    	$this->assign('pringles_list',$pringles_list);
    	//所属栏目
    	$push_cate_res = $this->_cate_mod->field('id,name')->select();
    	$push_cate_list = array();
    	foreach ($push_cate_res as $val) {
    		$push_cate_list[$val['id']] = $val['name'];
    	}
    	$this->assign('push_cate_list',$push_cate_list);
    	//推送城市
    	$this->assign('city_list',D('city')->get_proprerty_city());
    	
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
        $status = $this->_request('status', 'trim');
        ($keyword = $this->_request('keyword', 'trim')) && $map['info'] = array('like', '%'.$keyword.'%');
        ($city_id = $this->_request('city_id', 'intval')) && $map['city_id'] = array('like', '%'.$city_id.'%');
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
        	'city_id'=>$city_id,
            'keyword' => $keyword,
        ));
        return $map;
    }

    public function _before_add(){
    	
    	$this->assign('city_list',D('city')->get_proprerty_city());
    	
        $author = $_COOKIE['admin']['username']; // echo  $author; die();
        $this->assign('author',$author);
       
    }

    protected function _before_insert($data) {	
    	$city_id = $this->_post('city_id','trim');
    	$data['city_id']=implode(',',$city_id);
    	
    	$title   = $this->_post('title','trim');
    	$info    = $this->_post('info','trim');
    	$pid     = $this->_post('pid','trim');
    	$cate_id = $this->_post('cate_id','trim');
    	$cast    = $this->_post('cast','trim');
    	
    	if(count($city_id) <= 1){
    		$city_id[1]='';
    	}
    	$this->_mod->groupcast($city_id[0],$city_id[1],$title,$info,$cate_id,$pid,$cast);
        return $data;
    }

    public function _before_edit(){
    	
        $id = $this->_get('id','intval');
    }

    protected function _before_update($data) {

        return $data;
    }
    
    
    public function ajax_title(){
    	if(IS_POST){
	    	$pid=  $this->_post('pid','intval');
	    	$cate_id=  $this->_post('cate_id','intval');
	    	switch ($cate_id){
	    		case  "1";//楼盘
	    		       $title = M('property')->where('id = '.$pid)->getField('title');
	    		       break;
	    		case  "2";//活动
		    	      $title  = M('article')->where('id  = '.$pid)->getField('title');
		    	      break;
	    		case  "3";//品客帮  
		    	      $title  = M('pringles')->where('id = '.$pid)->getField('title');
		    	      break;
		    	default:
	    	}
    	  if ($title) {
            $this->ajaxReturn(1, L('operation_success'), $title);
          } else {
            $this->ajaxReturn(0, L('operation_failure'));
          }
      }
     
    }

    
    
    
}