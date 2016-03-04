<?php
class hezuo_propertyModel extends RelationModel
{
	//检测楼盘是否存在
	public function name_exists($pid, $id=0) {
        $where = "id=" . $pid . " AND pid=1";
        $result = M('property')->where($where)->count('id');
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
}