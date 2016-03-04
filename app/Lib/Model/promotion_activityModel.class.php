<?php
class promotion_activityModel extends RelationModel
{
    //读取C端注册用户列表
    public function lists($pageSize){
        $where = 'type = 1';
        $count = M('promotion_activity')->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, $pageSize);
        $page = $p->show();
        $str = 'id,mobile,receive_time,add_time,type';
        $list = M('promotion_activity')->field($str)->where($where)->order('receive_time ASC')->limit($p->firstRow.','.$p->listRows)->select();
        return array($list, $page);
    }
}