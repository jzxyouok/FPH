<?php

class storesModel extends Model
{
	/*
	* 选择公司*显示对应的门店
	* chl
	*/
	public function stores_list($id){
		$list = M('stores')->where(array('pid'=>$id))->field('id,name')->order('add_time DESC')->select();	
		return $list;
	}
	
	/*
	* 验证门店代码
	* @param $code_id     门店代码
	* chl
	*/
	public function stores_id($code_id){
		$data = M('stores')->field('id,pid')->where(array('code_id'=>$code_id))->find();
		return $data;
	}
	/*
	* 获取门店ID
	* @param $code_id     门店代码
	* lishun
	*/
	public function get_stores_id($code_id){
		$data = M('stores')->field('id,name')->where(array('code_id'=>$code_id))->find();
		return $data;
	}
	/*
	* 搜索门店名称或代码
	* @param $code_id     门店代码
	* chl
	*/
	public function stores_search($stores_name){
		if(is_numeric($stores_name)){
			$list = M('stores')->field('id,name')->where("code_id=".$stores_name." OR name like '%".$stores_name."%'")->select();
		}else{
			$list = M('stores')->field('id,name')->where("name like '%".$stores_name."%'")->select();
			
		}
		return $list;
		
	}
    /*
    * 检测门店是否存在
    * lishun
    */
	public function name_exists($name, $id = 0) {
        $where = "name='" . $name . "' AND id<>'" . $id . "'";
        $result = $this->where($where)->find();
        if ($result['id']) {
            return $result['id'];
        } else {
            return false;
        }
    }
	/*
    * 检测所属公司是否存在
    * lishun
    */
    public function company_exists($name, $id = 0) {
        $where = "name='" . $name . "' AND id<>'" . $id . "'";
        $result = M('company')->where($where)->count('id');
        if ($result && $result!=0) {
            return true;
        } else {
            return false;
        }
    }
	
	//门店信息
	public function stores_info($id){
		$data = M('stores')->field('name')->where(array('id'=>$id))->find();
		return $data;
	}
	
	
	
	
	
	
	
	
	
}