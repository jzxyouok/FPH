<?php
class join_reservationModel extends Model
{

    /**
     * @param $option
     * @param $fields 'id,uid,info,praise,status,add_time'
     * @return array
     */
    public function getList($option, $fields, $isPage = true)
    {
        M('join_reservation','fph_',C('DB_member'));
        $where = $option;
        if(!$isPage)
        {
            $list = M('join_reservation')->field($fields)->where($where)->select();
            return $list;
        }
        $count = M('join_reservation')->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();
        $list = M('join_reservation')->field($fields)->where($where)->order('id DESC')->limit($p->firstRow.','.$p->listRows)->select();
        if(!$list) return $list;
        foreach($list as $key=>$val){
                $timeStart = date('Y-m-d H:i',$val['order_time_start']);
                $timeEnd = date('Y-m-d H:i',$val['order_time_end']);
                $time[0] =  substr($timeStart,0, 10);
                $time[1]  = substr($timeStart,-5);
                $time[2]  = substr($timeEnd,-5);
                $list[$key]['order_time'] = $time[0] .' '.$time[1].'-'.$time[2];
        }
        return array($page,$list);
    }

    public function countNUm($where = false)
    {
        M('join_reservation','fph_',C('DB_member'));
        if(!$where){
            $count = M('join_reservation')->field('eservation_id, count(eservation_id) as num')->group('eservation_id')->select();
        }else{
            $count = M('join_reservation')->where($where)->field('eservation_id, count(eservation_id) as joinNum')->group('eservation_id')->select();
        }
        return $count;
    }

    public function getEnableByPid($pid, $field)
    {
        M('join_reservation','fph_',C('DB_member'));
        return  M('join_reservation')->where('status = 1 and pid = '. $pid)->getField($field);
    }

    /**
     * @param $option
     * @param $fields 'id,uid,info,praise,status,add_time'
     * @return array
     */
    public function Lists($where, $fields, $order, $isPage = false){
        if(!$isPage){
            $list = M('join_reservation')->field($fields)->where($where)->order($order)->select();
        }else{
            $count = M('join_reservation')->where($where)->count('id');
            import("ORG.Util.Page");
            $p = new Page($count, $isPage);
            $page = $p->show();
            $list = M('join_reservation')->field($fields)->where($where)->order($order)->limit($p->firstRow.','.$p->listRows)->select();
            if(!$list) return $list;
        }

        //读取电话号码
        $uidArr = array_unique(i_array_column($list, 'uid'));
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

        //读取楼盘名称
        $pidArr = array_unique(i_array_column($list, 'pid'));
        $propertyWhere = 'id in ('. implode(',', $pidArr).')';
        $propertyField = 'id,title,city_id';
        M('property', 'fph_', C('DB_fangpinhui'));
        $propertyList = M('property')->field($propertyField)->where($propertyWhere)->select();

        $tmp = array();
        foreach($propertyList as $key => $val){
            $tmp[$val['id']] = $val['title'];
        }

        //读取预约奖励
        $reservation_idArr = array_unique(i_array_column($list, 'eservation_id'));
        $reservationWhere = 'id in ('. implode(',', $reservation_idArr).')';
        $reservationField = 'id,money';
        M('reservation', 'fph_', C('DB_activity'));
        $reservationList = M('reservation')->field($reservationField)->where($reservationWhere)->select();

        $money = array();
        foreach($reservationList as $key => $val){
            $money[$val['id']] = $val['money'];
        }
        foreach($list as $key=>$val){
            $list[$key]['title'] = $tmp[$val['pid']];
            $list[$key]['mobile'] = $mobile[$val['uid']];
            $list[$key]['username'] = $username[$val['uid']];
            $list[$key]['money'] = $money[$val['eservation_id']];
        }
        return array($list, $page);
    }

    public function getOneField($where, $field)
    {
        $data = M('join_reservation')->where($where)->getField($field);
        return $data;
    }

    public function updateRecord($where, $data)
    {
        $data = M('join_reservation')->where($where)->save($data);
        return $data;
    }
}