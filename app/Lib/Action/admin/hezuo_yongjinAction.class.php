<?php
class hezuo_yongjinAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('hezuo_yongjin');
        $this->_cate_mod = D('property');
    }

    public function index() {
        
		$ca = C('DB_PREFIX');
		$where = 'yid=0';
		$tiaodian = $this->_get('tiaodian','intval');
		$carried = $this->_get('carried','trim');
		$keyword = $this->_request('keyword', 'trim');
		if($tiaodian){
			$where .= ' AND A.tiaodian='.$tiaodian;
		}
		if($carried!=''){
			$where .= ' AND A.carried='.$carried;
		}
		if($keyword){
			$ksr = 'id';
			$property_id = M('property')->field($ksr)->where("title like '%".$keyword."%' AND pid=1")->select();
			$id_arr = array();
			foreach($property_id as $v=>$k){
				$id_arr[$v] = $k['id'];
			}
			$id_arr = implode(',',$id_arr);
			if (!$id_arr) $id_arr=0.1;
			if($id_arr){
				$where .= " AND A.pid IN ($id_arr)";
			}
		}
		$count = $this->_mod->table("{$ca}hezuo_yongjin AS A")->where($where)->count('id');
		import("ORG.Util.Page");
		$p = new Page($count, 20);
		$page = $p->show();
		$str = 'A.id,A.cid,A.tiaodian,A.set_num,A.tiaodian_price,A.set_num2,A.tiaodian_price2,A.carried,B.title,B.id as pid';
		$yongjin_list = $this->_mod->field($str)->table("{$ca}hezuo_yongjin AS A 
														LEFT JOIN {$ca}property AS B ON B.id=A.pid")
														->where($where)
														->limit($p->firstRow.','.$p->listRows)
														->order('A.id DESC')->select();
		$fel = 'A.id,A.pid,A.cid,A.source,A.total_price,A.share_price,A.set_num,A.tiaodian_price,A.set_num2,A.tiaodian_price2,A.carried,B.name,B.youhui,B.start_time,B.end_time';
		foreach ($yongjin_list as $key => $val) {
            $yongjin_list[$key]['yongjin_rule'] = $this->_mod->field($fel)
															 ->table("{$ca}hezuo_yongjin AS A 
																	 LEFT JOIN {$ca}hezuo_property_product AS B ON B.id=A.cid")
															 ->where(array('yid'=>$val['id']))->select();
			foreach ($yongjin_list[$key]['yongjin_rule'] as $k => $v) {
				$yongjin_list[$key]['yongjin_rule'][$k]['minus_price'] = $v['total_price']-$v['share_price'];
			}
        }
		$this->assign('yongjin_list', $yongjin_list);
		$this->assign('page_list', $page);
		
		
        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);

        $this->_search();
        $this->display();
    }

    protected function _search() {
        $map = array();
        $tiaodian = $this->_get('tiaodian','intval');
        $keyword = $this->_request('keyword', 'trim');
        $carried = $this->_get('carried','intval');
        $this->assign('search', array(
            'tiaodian' => $tiaodian,
            'keyword' => $keyword,
			'carried'=> $carried,
        ));
        return $map;
    }
	
    public function add_rule() {
        $mod = D($this->_name);
        if (IS_POST) {
            if (false === $data = $mod->create()) {
                IS_AJAX && $this->ajaxReturn(0, $mod->getError());
                $this->error($mod->getError());
            }
            if (method_exists($this, '_before_insert')) {
                $data = $this->_before_insert($data);
            }
            if( $mod->add($data) ){
                $this->admin_log($mod,$mod->getLastInsID());
                if( method_exists($this, '_after_insert')){
                    $id = $mod->getLastInsID();
                    $this->_after_insert($id);
                }
                IS_AJAX && $this->ajaxReturn(1, L('operation_success'), '', 'add');
                $this->success(L('operation_success'));
            } else {
                IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
                $this->error(L('operation_failure'));
            }
        } else {
            $this->assign('open_validator', true);
            if (IS_AJAX) {
				$id  = $this->_get('id','intval');
				$ca = C('DB_PREFIX');
				$str = 'A.id,A.pid,A.tiaodian,B.title';
				$info = $this->_mod->field($str)->table("{$ca}hezuo_yongjin AS A 
														 LEFT JOIN {$ca}property AS B ON B.id=A.pid")
													 ->where(array('A.id'=>$id))
													 ->order('A.id DESC')->find();
				$this->assign('info', $info);
				
				$porduct = M('hezuo_property_product')->field('id,name,youhui')->where(array('pid'=>$info['pid']))->order('id ASC')->select();
				$this->assign('porduct', $porduct);
                $response = $this->fetch();
                $this->ajaxReturn(1, '', $response);
            } else {
				$this->display();
            }
        }
    }
	
	public function ajax_youhui(){
		$id = $this->_post('id','intval');
		if(!$id){
			$this->ajaxReturn(0, '非法参数');
		}
		$youhui = M('hezuo_property_product')->where(array('id'=>$id))->getfield('youhui');
		$this->ajaxReturn(1, '',$youhui);
	}
	
	public function minus(){
		$total_price = $this->_post('total_price','trim');
		$share_price = $this->_post('share_price','trim');
		if($total_price && $share_price){
			$data = $total_price-$share_price;
			$this->ajaxReturn(1, '',$data);
		}else{
			$this->ajaxReturn(0, '非法参数');
		}
	}

    public function _before_add(){

    }

    protected function _before_insert($data) {
		
        return $data;
    }
    
    public function _before_edit(){
        $id = $this->_get('id','intval');
	$pid = $this->_get('pid','intval');
	if($id)
	{
	    $product = M('property')->where('id ='.$pid)->getfield('title');
	    $info_list =  M('hezuo_yongjin')->where('id ='.$id)->find();
	    $this->assign('product', $product);
	    $this->assign('info_list', $info_list);
	    $this->assign('id', $id);
	}
    }
    

    protected function _before_update($data) {
        return $data;
    }
	
	//执行、作废
	public function carried(){
		$id       = $this->_get('id', 'intval');
		$carried  = $this->_get('carried', 'intval');
		if(!$id){
			$this->ajaxReturn(0, L('illegal_parameters'));
		}
		if($carried){
			$data['carried'] = 0;
		}else{
			$data['carried'] = 1;
		}
		if(false !== $this->_mod->where(array('id'=>$id,'carried'=>$carried))->save($data)){
			$this->ajaxReturn(1, L('operation_success'));
		}else{
			$this->ajaxReturn(0, L('operation_failure'));
		}
		
	}
	
	//前置删除下级分类
	public function _before_delete(){
		$id  = $this->_get('id', 'intval');
		if($id){
			$cid = M('hezuo_yongjin')->where(array('id'=>$id))->getfield('cid');
			$boll = M('myclient_property')->where(array('buy_product'=>$cid))->getfield('id');
			if(!empty($boll))
			{
			    $this->ajaxReturn(0, '正在执行中，不能删除产品');
			}
		} 
	}
	
	//输入模糊搜索
    public function input_search(){
        $title = $this->_post('title','trim');
        !$title && $this->ajaxReturn(0, '请输入要搜索的内容');
		$ca = C('DB_PREFIX');
        if($title){
			$list = $this->_cate_mod->field("id,title")->where("pid=1 AND title like '%".$title."%'")->order('id DESC')->select();
            $str = "";
			$str .= "<ul class='popup'>";
				foreach($list as $val) {
					$title = $val['title'];
					$str .= "<li rel=".$val['id'].">".msubstr($title,0,35,'utf-8',true)."</li>";
				}
			$str .= '</ul>';
			if($list){
				$this->ajaxReturn(1, '未知错误！', $str);
			}
        }else{
         $str .= "<div>无数据,请检查输入的关键字</div>";
        }
    }
	
	public function ajax_check_product() {
        $cid = $this->_get('cid', 'intval');
        $id = $this->_get('id', 'intval');
        if ($this->_mod->name_exists_product($cid,  $id)) {
            $this->ajaxReturn(0, '该产品已经存在');
        } else {
            $this->ajaxReturn();
        }
    }
	
	/**
     * 
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