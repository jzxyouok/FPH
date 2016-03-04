<?php
class myclient_propertyAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('myclient');
        $this->_cate_mod = D('myclient_property');
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
		$str = 'A.property,A.buy_product,A.status,A.with_look,A.buy_time,B.name,B.mobile,B.uid,C.title';
		$info_data = $this->_cate_mod->field($str)->table("{$fph}myclient_property AS A 
														   LEFT JOIN {$fph}myclient AS B ON B.id=A.pid
														   LEFT JOIN {$fph}property AS C ON C.id=A.property")
												  ->where("A.id=".$id."")->find();
		if(!$info_data['property']){
			$info_data['area'] = M('city')->where(array('id'=>$info_data['area']))->getfield('name');
			$info_data['price'] = M('ideal_price')->where(array('id'=>$info_data['price']))->getfield('title');
		}
		$this->assign('info_data', $info_data);
		
		$user_info = M('user')->field('username,mobile')->where(array('id'=>$info_data['uid']))->find();
		$this->assign('user_info', $user_info);
		
		//楼盘产品
		$product_list = M('hezuo_property_product')->field('id,name')->where(array('pid'=>$info_data['property']))->select();
		$this->assign('product_list', $product_list);
		
		$product_count = count($product_list);
		$this->assign('product_count', $product_count);
		
		//判断客户购买楼盘状态大于4的时候显示楼盘产品
		if($info_data['status'] >= 4){
		    $property_name = M('hezuo_property_product')->field('name,end_time')->where('id ='.$info_data['buy_product'])->find();
		    $this->assign('property_name', $property_name);
		}
		
		if($info_data['buy_product']){
			$youhui = M('hezuo_property_product')->where(array('id'=>$info_data['property']))->getfield('youhui');
			$this->assign('youhui', $youhui);
		}
		
    }

    protected function _before_update($data) {
		$status = $this->_post('status','trim');
		$expects_rime = $this->_post('expects_rime','trim');
		$buy_time     = $this->_post('buy_time','trim');
		$name         = $this->_post('name','trim');
		$shenfenzheng = $this->_post('shenfenzheng','trim');
		$p_id     = $this->_post('p_id','intval');
		$buy_product = $this->_post('buy_product','intval');
		if($buy_time && (!$name || !$shenfenzheng)){
			$this->ajaxReturn(0, '成交时间、姓名、身份证均不能为空');
		}
		if($name && (!$buy_time || !$shenfenzheng)){
			$this->ajaxReturn(0, '成交时间、姓名、身份证均不能为空');
		}
		if($shenfenzheng && (!$name || !$buy_time)){
			$this->ajaxReturn(0, '成交时间、姓名、身份证均不能为空');
		}
		
		if($status==''){
			$this->ajaxReturn(0, '请选择状态');
		}
		if($buy_product){
			$p_count = M('hezuo_yongjin')->where(array('cid'=>$buy_product))->count('id');
			if(!$p_count){
				$this->ajaxReturn(0, '该产品没有设置佣金规则');
			}
		}
		
		if(!$buy_time && $status==4){
			$status=3;
		}elseif($buy_time && $status!=5){
			$status=4;
		}
		if($expects_rime && $buy_time){
			if(strtotime($expects_rime) > strtotime($buy_time)){
				$this->ajaxReturn(0, '成交时间不能小于预计成交时间 ');
			}
		}
		if($status!=5){
			$data['expects_rime'] = strtotime($expects_rime);
			$data['buy_time']     = strtotime($buy_time);
		}		
		$data['status']       = $status;
		$data['update_time']  = time();
        return $data;
    }
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}