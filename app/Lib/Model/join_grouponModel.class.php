<?php
class join_grouponModel extends RelationModel
{
    public function lists($field, $order, $page=20) {
        $where ='1 = 1';
        $count = M('join_groupon')->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, $page);
        $page = $p->show();

        $list = M('join_groupon')->field($field)->where($where)->order($order)->limit($p->firstRow.','.$p->listRows)->select();
        $uidArr = array_unique(i_array_column($list, 'uid'));
        $gidArr = array_unique(i_array_column($list, 'gid'));

        //读取电话号码
        $memberWhere = 'id in ('. implode(',', $uidArr).')';
        $memberField = 'id,mobile';
        M('member', 'fph_', C('DB_member'));
        $memberList = M('member')->field($memberField)->where($memberWhere)->select();
        $mobile = array();
        foreach($memberList as $key => $val){
            $mobile[$val['id']] = $val['mobile'];
        }

        //读取姓名
        $memberExWhere = 'uid in ('. implode(',', $uidArr).')';
        $memberExField = 'uid,username';
        $memberExList  = M('member_extend')->field($memberExField)->where($memberExWhere)->select();
        $username = array();
        foreach($memberExList as $key => $val){
            $username[$val['uid']] = $val['username'];
        }

        //读取pid
        M('groupon','fph_',C('DB_activity'));
        $grouponWhere = 'id in ('. implode(',', $gidArr).')';
        $grouponField = 'id,pid';
        $grouponList  = M('groupon')->field($grouponField)->where($grouponWhere)->select();
        $grouponPid = array();
        foreach($grouponList as $key => $val){
            $grouponPid[$val['id']] = $val['pid'];
        }

        foreach($list as $key=>$val){
            $list[$key]['mobile'] = $mobile[$val['uid']];
            $list[$key]['username'] = $username[$val['uid']];
            $list[$key]['pid'] = $grouponPid[$val['gid']];
        }

        //获取楼盘标题
        M('property','fph_',C('DB_fangpinhui'));
        $pidArr = array_unique(i_array_column($list, 'pid'));
        $propertyWhere = 'id in ('. implode(',', $pidArr).')';
        $propertyField = 'id,title';
        $propertyList  = M('property')->field($propertyField)->where($propertyWhere)->select();
        $title = array();
        foreach($propertyList as $key => $val){
            $title[$val['id']] = $val['title'];
        }
        foreach($list as $key=>$val){
            $list[$key]['title'] = $title[$val['pid']];
        }

        return array($list, $page);
    }

    //统计
    public function joinGrouponCount($where){
        M('join_groupon','fph_',C('DB_activity'));
        return $this->where($where)->count('id');
    }
}