<?php

class companyModel extends Model
{
	/*
	* 选择公司
	* chl
	*/
	public function company_list(){
		$list = M('company')->field('id,short_name')->where(array('teamwork'=>1))->order('add_time DESC')->select();
		return $list;
	}
    
    public function mobile_exists($mobile, $id = 0) {
        $where = "mobile='" . $mobile . "' AND id<>'" . $id . "'";
        $result = M('user')->where($where)->getfield('id');
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }
    /*
    * 检测所属公司
    * lishun
    */
    public function company_exists($name, $id = 0) {
        $where = "name like '%" . $name . "%' AND teamwork = 1  AND id<>'" . $id . "'";
        $result = M('company')->where($where)->count('id');
        if ($result && $result!=0) {
            return true;
        } else {
            return false;
        }
    }
    /*
    * 查找对应所属公司列表
    * lishun
    */
    public function company_list_exists($name) {
        $where = "name like '%".$name."%' AND teamwork = 1 ";
        $result = M('company')->where($where)->select();
        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    /*
    * 检测所属公司
    * lishun
    */
    public function company_search($stores_name){
        $list = M('company')->field('id,name,short_name')->where("name like '%".$stores_name."%' OR short_name like '%".$stores_name."%' ")->select();
        return $list;
        
    }
	
	/*
	* 查找公司是否为合作公司
	* @param int $id 公司id
	* chl
	*/
	public function company_teamwork($id){
		$data = M('company')->where(array('id'=>$id))->getfield('teamwork');
		return $data;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}