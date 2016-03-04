<?php
class case_fieldModel extends RelationModel
{

//检测楼盘是否存在
	public function name_exists($admin_id, $id=0) {
        $where = "admin_id=" . $admin_id . " AND id<>'" . $id . "'";
        $result = $this->where($where)->count('id');
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    //获取楼盘负责人
    public function case_field_list($id){
        $list = M('case_field')->field('admin_id')->where('property RLIKE "[[:<:]]'.$id.'[[:>:]]"')->select();
        foreach($list as $val){
            $ids .= $val['admin_id'] . ',';
        }
        $ids = substr($ids,  0, -1);
        return $ids;
    }

    
}