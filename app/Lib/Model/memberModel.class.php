<?php
/**
 * Created by PhpStorm.
 * User: geyouwen
 * Date: 15-8-28
 * Time: 上午10:23
 */

class memberModel extends Model
{

    public function getList($where, $fields, $order='id DESC' )
    {
        M('member', 'fph_', C('DB_member'));
        $list = M('member')->field($fields)->where($where)->order($order)->select();
        return $list;
    }

    public function getField($where, $fields)
    {
        M('member', 'fph_', C('DB_member'));
        $list = M('member')->where($where)->getField($fields);
        return $list;
    }

    /**
     * 统计符合条件的推广人数
     */

    public function countNum($where)
    {
        M('member_extend', 'fph_', C('DB_member'));
        $count = M('member_extend')->where($where)->count('id');
        return $count;
    }

    /**
     * 查询会员扩展表
     */
    public function getListByExtend($where, $fields, $order='id DESC'){
        M('member_extend', 'fph_', C('DB_member'));
        $list = M('member_extend')->field($fields)->where($where)->order($order)->select();
        return $list;
    }


}