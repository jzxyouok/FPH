<?php
class hezuo_propertyAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('hezuo_property');
        $this->_cate_mod = D('property');
    }

    public function index() {
		$ca = C('DB_PREFIX');
		$str = 'id,title';
		$where = 'pid=1';
		$keyword = $this->_request('keyword', 'trim');
		if($keyword){
			$where .= ' AND title like "%'.$keyword.'%"';
		}
		
		$count = $this->_cate_mod->where($where)->count('id');
		import("ORG.Util.Page");
		$p = new Page($count, 20);
		$page = $p->show();
		$hezuo_list = $this->_cate_mod->field($str)->where($where)->limit($p->firstRow.','.$p->listRows)->order('ordid ASC, add_time DESC')->select();
		foreach ($hezuo_list as $key => $val) {
			$boll = M('hezuo_yongjin')->where('pid='.$val['id'])->getField('carried');
			$hezuo_list[$key]['boll'] = 3;
			if($boll == 0 || $boll == 1 AND $boll != null)
			{
			    $hezuo_list[$key]['boll'] = $boll;
			}
			$hezuo_list[$key]['product'] = M('hezuo_property_product')->field('id,name,youhui,start_time,end_time')->where(array('pid'=>$val['id']))->select();
			$hezuo_list[$key]['pid_count'] = count($hezuo_list[$key]['product']);
		}
		$this->assign('hezuo_list', $hezuo_list);
		$this->assign('page_list', $page);
        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);
		$big_menu = array(
            'title' => '添加楼盘',
            'iframe' => U('hezuo_property/add'),
            'id' => 'add',
            'width' => '500',
            'height' => '300'
        );
        $this->assign('big_menu', $big_menu);
		
		$this->_search();
        $this->display();
    }

    protected function _search() {
        $map = array();
        $time_start = $this->_request('time_start', 'trim');
        $time_end = $this->_request('time_end', 'trim');
        $keyword = $this->_request('keyword', 'trim');
        $this->assign('search', array(
            'time_start' => $time_start,
            'time_end' => $time_end,
            'keyword' => $keyword,
        ));
    }

    public function _before_add()
    {
       
    }
	
	public function ajax_add(){
		if (IS_POST) {
			$pid = $this->_post('pid','intval');
			$id_count = M('property')->where(array('id'=>$pid))->count('id');
			!$id_count && $this->ajaxReturn(0, '楼盘名称选择错误');
			
			$pid_count = M('property')->where(array('id'=>$pid,'pid'=>1))->count('id');
			if($pid_count){
				$this->ajaxReturn(0, '该楼盘名称已经存在');
			}
			if (false !== M('property')->where(array('id'=>$pid))->save(array('pid'=>1))){
				$this->ajaxReturn(1, L('operation_success'));
			}else{
				$this->ajaxReturn(0, L('operation_failure'));
			}
		}
	}

    protected function _before_insert($data) {
		$pid = $this->_post('pid','intval');
		$id_count = $this->_cate_mod->where(array('id'=>$pid))->count('id');
		!$id_count && $this->ajaxReturn(0, '楼盘名称选择错误');
		
		$pid_count = $this->_mod->where(array('pid'=>$pid))->count('id');
		if($pid_count){
			$this->ajaxReturn(0, '该楼盘名称已经存在');
		}
        return $data;
    }

    public function _before_edit(){
        $id = $this->_request('id','intval');
		$ca = C('DB_PREFIX');
		$str = 'B.title';
		$info_title = $this->_mod->field($str)->table("{$ca}hezuo_property AS A LEFT JOIN {$ca}property AS B ON B.id=A.pid")->where("A.id=$id")->find();
		$this->assign('info_title', $info_title);
    }

    protected function _before_update($data) {
     	$pid = $this->_post('pid','intval');
		$id  = $this->_post('id','intval');
		$id_count = $this->_cate_mod->where(array('id'=>$pid))->count('id');
		!$id_count && $this->ajaxReturn(0, '楼盘名称选择错误');
		
		$pid_count = $this->_mod->where("pid=".$pid." AND id <> ".$id."")->count('id');
		if($pid_count){
			$this->ajaxReturn(0, '该楼盘名称已经存在');
		}
        return $data;
    }
    
    //开启已删除楼盘
    public function kaiqi()
    {
	$id  = $this->_get('id', 'intval');
	M('hezuo_yongjin')->where(array('pid'=>$id))->setField('carried', 1);
	$this->ajaxReturn(1, L('operation_success'));
    }
	
    //前置删除下级分类
    public function _before_delete(){
	$id  = $this->_get('id', 'intval');
	$pid = $this->_get('pid', 'intval');
	$did = $this->_get('did', 'intval');
	if ($pid)
	{
	    $boll = M('hezuo_yongjin')->where('cid='.$pid)->getField('id');
	    if(!empty($boll))
	    {
		$this->ajaxReturn(0, L('删除失败，已设置佣金规则不能删除'));
	    }
	    if (false !== M('hezuo_property_product')->where(array('id'=>$pid))->delete())
	    {
		$this->ajaxReturn(1, L('operation_success'));
	    }
	    return false;
	}elseif($id)
	{
	    M('hezuo_yongjin')->where(array('pid'=>$id))->setField('carried', 0);
	    $this->ajaxReturn(1, L('operation_success'));
	}
	elseif($did)
	{
	    M('property')->where(array('id'=>$did))->setField('pid',0);
	    $this->ajaxReturn(1, L('operation_success'));
	}
    }
	
    //输入模糊搜索
    public function input_search(){
        $title = $this->_post('title','trim');
        !$title && $this->ajaxReturn(0, '请输入要搜索的内容');
        if($title){
			$list = $this->_cate_mod->field("id,title")->where('title like "%'.$title.'%" AND status=1')->order('add_time DESC')->limit(50)->select();
            $str = "";
			$str .= "<ul class='popup'>";
				foreach($list as $val) {
					$title = $val['title'];
					$str .= "<li rel=".$val['id'].">".msubstr($title,0,35,'utf-8',true)."</li>";
				}
			$str .= '</ul>';
        }else{
         $str .= "<div>无数据,请检查输入的关键字</div>";
        }
        $this->ajaxReturn(1, '未知错误！', $str);
    }
	
	/**
     * ajax检测店铺名称是否存在
     */
    public function ajax_check_name() {
        $pid = $this->_get('pid', 'intval');
        $id = $this->_get('id', 'intval');
        if ($this->_mod->name_exists($pid,  $id)) {
            $this->ajaxReturn(0, '该楼盘已经存在');
        } else {
            $this->ajaxReturn();
        }
    }
	
}