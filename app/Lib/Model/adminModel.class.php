<?php
class adminModel extends RelationModel
{
    protected $_validate = array(
        array('username', 'require', '{%admin_username_empty}'), //不能为空
        array('username', '', '{%admin_name_exists}', 0, 'unique', 1), //新增的时候检测重复
    );

    protected $_link = array(
        //关联角色
        'role' => array(
            'mapping_type' => BELONGS_TO,
            'class_name' => 'admin_role',
            'foreign_key' => 'role_id',
        )
    );

    /*
     * 检测名称是否存在
     *
     * @param string $name
     * @param int $id
     * @return bool
     */
    public function name_exists($name, $id=0) {
        $pk = $this->getPk();
        $where = "username='" . $name . "'  AND ". $pk ."<>'" . $id . "'";
        $result = $this->where($where)->count($pk);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
	
	 /*
     * 检测手机号码是否存在
     *
     * @param string $mobile
     * @param int $id
     * @return bool
     */
    public function mobile_exists($mobile, $id=0) {
        $pk = $this->getPk();
        $where = "mobile='" . $mobile . "'  AND ". $pk ."<>'" . $id . "'";
        $result = $this->where($where)->count($pk);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    //admin表 信息
    public function admin_info($uid){
        $data = M('admin')->field('id,username,mobile,city_id')->where(array('id'=>$uid))->find();
        return $data;
    }

    //获取管理员
    public function admin_username($ids){
        $list = M('admin')->field('id,username')->where('id in('.$ids.')')->select();
        return $list;
    }

    //获取管理员
    public function admin_list($ids){
        M('admin','fph_',C('DB_fangpinhui'));
        $list = M('admin')->field('id,username,mobile')->where("status=1")->select();
        return $list;
    }

    //获取管理员
    public function adminListIn($ids){
        M('admin','fph_',C('DB_fangpinhui'));
        $list = M('admin')->field('id as adminid,username,mobile')->where('id in('.$ids.')')->select();
        return $list;
    }

    public function getList($where, $fields)
    {
        M('admin','fph_',C('DB_fangpinhui'));
        $data = M('admin')->field($fields)->where($where)->select();
        return $data;
    }

    public function getInfo($where, $fields)
    {
        M('admin','fph_',C('DB_fangpinhui'));
        $data = M('admin')->field($fields)->where($where)->find();
        return $data;
    }
}