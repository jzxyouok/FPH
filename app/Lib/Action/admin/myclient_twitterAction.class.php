<?php
class myclient_twitterAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('myclient');
        $this->_cate_mod = D('myclient_twitter');
    }

    public function _before_index() {
       
	    $p = $this->_get('p','intval',1);
        $this->assign('p',$p);

        //默认排序
        $this->sort = 'add_time';
        $this->order = 'DESC';
    }

    public function _before_edit(){
        $id = $this->_request('id','intval');
		$entry = $this->_request('entry','intval');
		$fph = C('DB_PREFIX');
		$str = 'A.status,A.area,A.price,A.update_time,A.add_time,B.name,B.mobile,B.uid';
		$info_data = $this->_cate_mod->field($str)->table("{$fph}myclient_twitter AS A LEFT JOIN {$fph}myclient AS B ON B.id=A.pid")
												  ->where("A.id=".$id."")->find();
		if($info_data){
			$info_data['area'] = M('city')->where(array('id'=>$info_data['area']))->getfield('name');
			$info_data['price'] = M('ideal_price')->where(array('id'=>$info_data['price']))->getfield('title');
		}
		$this->assign('info_data', $info_data);
		
		$user_info = M('user')->field('username,mobile')->where(array('id'=>$info_data['uid']))->find();
		$this->assign('user_info', $user_info);
		
		//楼盘产品
		//$product_list = M('hezuo_property_product')->field('id,name')->where(array('pid'=>$info_data['property']))->select();
		//$this->assign('product_list', $product_list);
		
		//$product_count = count($product_list);
		//$this->assign('product_count', $product_count);
		
		//判断客户购买楼盘状态大于4的时候显示楼盘产品
		/*if($info_data['status'] >= 4){
		    $property_name = M('hezuo_property_product')->field('name,end_time')->where('id ='.$info_data['buy_product'])->find();
		    $this->assign('property_name', $property_name);
		}
		
		if($info_data['buy_product']){
			$youhui = M('hezuo_property_product')->where(array('id'=>$info_data['property']))->getfield('youhui');
			$this->assign('youhui', $youhui);
		}*/
		
    }

    protected function _before_update($data) {
		$status = $this->_post('status','trim');
		if($status==''){
			$this->ajaxReturn(0, '请选择状态');
		}
		
		$data['status']       = $status;
		$data['update_time']  = time();
        return $data;
    }
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}