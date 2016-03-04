<?php
class myclientModel extends RelationModel
{

    //读取客户到访记录
    public function receive_index($mobile, $username, $pid, $city_id, $time_start, $time_end, $status, $page=false){

        M(NULL,NULL,C('DB_member'));
        $where = '1=1';
        if($status!=''){
            $where .= ' AND status ='.$status;
        }
        if($pid){
            $where .= ' AND pid ='.$pid;
        }/*elseif($case_field){
            $where .= ' AND pid in('.$case_field.')';
        }*/
        if($mobile){
            $user_list= M('member')->field('id')->where("mobile = '".$mobile."'")->select();
        }elseif($username){
            $user_list = M('member_extend')->field('uid as id')->where("username like '%".$username."%'")->select();
        }
        foreach($user_list as $val){
            $user_ids .=$val['id'].',';
        }
        $user_ids =  substr($user_ids, 0, -1);
        if($mobile || $username){
            if(!$user_ids) $user_ids=0;
            $where .= ' AND uid in('.$user_ids.')';
        }
        if($time_start){
            $where .= ' AND receive_time >= '.strtotime($time_start);
        }
        if($time_end){
            $where .= ' AND receive_time <= '.strtotime($time_end);
        }

        if($city_id){
            M(NULL,'fph_',C('DB_fangpinhui'));
            $citySlq = 'select id from fph_city where id = '.$city_id.' or spid RLIKE "[[:<:]]'.$city_id.'[[:>:]]"';
            $propertyId = M('property')->field('id')->where("city_id in ($citySlq)")->select();
            if($propertyId){
                foreach($propertyId as $key => $val){
                    $propertyIds .= $val['id'].',';
                }
            }
        }else{
            $propertyIds = 0;
        }
        if($propertyIds){
            $where .= ' AND pid in('.substr($propertyIds, 0, -1) .')';
        }

        M('receive','fph_',C('DB_member'));
        if($page){
            $count = M('receive ')->where($where)->count('id');
            import("ORG.Util.Page");
            $p = new Page($count, $page);
            $page = $p->show();
            $list = M('receive ')->where($where)->order('receive_time DESC')->limit($p->firstRow.','.$p->listRows)->select();
        }else{
            $list = M('receive ')->where($where)->order('receive_time DESC')->select();
        }
        foreach($list as $key=>$val){
            M('property','fph_',C('DB_fangpinhui'));
            $list[$key]['title'] = M('property')->where("id =".$val['pid'])->getfield('title');
            M('member','fph_',C('DB_member'));
            $user_info = M('member')->field('mobile')->where(array('id'=>$val['uid']))->find();
            $user_extend = M('member_extend')->field('username')->where(array('uid'=>$val['uid']))->find();
            $list[$key]['username'] = $user_extend['username'];
            $list[$key]['mobile']      = $user_info['mobile'];
        }

        return array($page,$list);
    }

    //客户资料
    public function receive_edit($id){
        M(NULL,NULL,C('DB_member'));
        $data = M('receive ')->field('id,uid,add_time,result_status,report_time,remark')->where(array('id'=>$id))->find();
        $user_info = M('member')->field('mobile')->where(array('id'=>$data['uid']))->find();
        $user_extend = M('member_extend')->field('username,origin')->where(array('uid'=>$data['uid']))->find();
        $data['username'] = $user_extend['username'];
        $data['mobile']      = $user_info['mobile'];
        $data['origin']      = $user_extend['origin'];
        return $data;
    }

    //插入数据
    public function receive_edit_update($id,$result_status,$report_time,$remark){
        M(NULL,NULL,C('DB_member'));
        $data['result_status'] = $result_status;
        $data['report_time']   = $report_time;
        $data['remark']          = $remark;
        $result = M('receive')->where(array('id'=>$id))->save($data);
        return $result;
    }

    //路费领取记录
    public function receive_list($id){
        M(NULL,NULL,C('DB_member'));
        $uid = M('receive')->where(array('id'=>$id))->getfield('uid');

        $where = 'uid = '.$uid;
        $count = M('receive ')->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();

        $str = 'id,pid,rule_id,add_time,receive_time,status';
        $list = M('receive ')->field($str)->where($where)->order('id DESC')->limit($p->firstRow.','.$p->listRows)->select();
        foreach($list as $key=>$val){
            M(NULL,'fph_',C('DB_fangpinhui'));
            $list[$key]['title'] = M('property')->where("id =".$val['pid'])->getfield('title');
            M('journal_account','fph_',C('DB_log'));
            $expenses = M('journal_account')->field('journal_account')->where(array('sid'=>$val['id'], 'table_type' => 1))->find();
            $list[$key]['rule'] = $expenses['journal_account'];
        }
        return array($page,$list);
    }

    //浏览记录
    public function skim_through_list($id){
        M(NULL,NULL,C('DB_member'));
        $uid = M('receive')->where(array('id'=>$id))->getfield('uid');

        M(NULL,'fph_',C('DB_log'));
        $where = 'uid = '.$uid.' AND type = 3';
        $count = M('client_pageview ')->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();

        $str = 'id,page_id,add_time';
        $list = M('client_pageview ')->field($str)->where($where)->order('id DESC')->limit($p->firstRow.','.$p->listRows)->select();
        foreach($list as $key=>$val){
            M('property','fph_',C('DB_fangpinhui'));
            $list[$key]['title']  = M('property')->where("id =".$val['page_id'])->getfield('title');
        }
        return array($page,$list);

    }


}