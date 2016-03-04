<?php

class invitation_codeModel extends Model
{
    protected $_validate = array(
        array('username', 'require', '{%username_require}'), //不能为空
    );

    /**
     * code是否已经存在
     * @param $where
     * @param $field
     * @return mixed
     */
    public function codeExsist($where, $field)
    {
        M('invitation_code','fph_',C('DB_member'));
        return M('invitation_code')->where($where)->getField($field);
    }

    /**
     * 更新Code
     * @param $where
     * @param $data
     * @return bool
     */
    public function updateCode($where, $data)
    {
        M('invitation_code','fph_',C('DB_member'));
        return M('invitation_code')->where($where)->save($data);
    }

    /**
     * 插入新的code
     * @param $data
     * @return mixed
     */
    public function insertCode($data)
    {
        M('invitation_code','fph_',C('DB_member'));
        $id =  M('invitation_code')->add($data);
        //echo M('invitation_code')->getLastSql();
        return $id;
    }

    /**
     * 获取所有的有邀请码的推广人员
     */
    public function getInviteMember($where, $fields, $order, $pageSize)
    {
        M('invitation_code','fph_',C('DB_member'));
        $count = M('invitation_code ')->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, $pageSize);
        $page = $p->show();
        $list = M('invitation_code')->field($fields)->where($where)->order($order)->limit($p->firstRow.','.$p->listRows)->select();
        return array($list, $page);
    }



}