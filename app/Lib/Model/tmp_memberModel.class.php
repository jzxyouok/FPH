<?php

class tmp_memberModel extends Model
{
    protected $_validate = array(
        array('username', 'require', '{%username_require}'), //不能为空
    );

    /**
     * 临时人员是否已经存在
     * @param $where
     * @param $field
     * @return mixed
     */
    public function isExsist($where, $field)
    {
        M('tmp_member','fph_',C('DB_member'));
        return M('tmp_member')->where($where)->getField($field);
    }

    /**
     * 插入新的人员
     * @param $data
     * @return mixed
     */
    public function insertMember($data)
    {
        M('tmp_member','fph_',C('DB_member'));
        $id =  M('tmp_member')->add($data);
        //echo M('invitation_code')->getLastSql();
        return $id;
    }

    /**
     * 获取单个信息
     */
    public function getInfo($where, $fields)
    {
        M('tmp_member','fph_',C('DB_member'));
        $info = M('tmp_member')->where($where)->field($fields)->find();
        return $info;
    }

}