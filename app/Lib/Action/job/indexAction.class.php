<?php
class indexAction extends backendAction {

    public function index() {
        if(IS_CLI != 1 || MODE_NAME != 'cli')exit;
        if($_GET["time"]){
            $date = $_GET["time"];
        }else{
            $date = date("Y-m-d",strtotime("-1 days",time()));
        }
        $start_time = strtotime($date);
        $end_time = strtotime("+1 days",$start_time);
        M('receive', 'fph_', C('DB_member'));
        $where = "receive_time>=$start_time and receive_time<$end_time";
        $list = M('receive')->field('id,uid,pid,rule_id,add_time,receive_time,status')->where($where)->select();
        foreach ($list as $k => $val){
            $receiveList[$val['pid']]=$val['rule_id'];
            $addReceiveData[$val['pid']][]=$val;

        }
        foreach ($receiveList as $pid => $rule_id){
            $data['pid']        = $pid?$pid:0;
            $data['visit']      = M('receive')->where($where." and pid=$pid")->count('id');
            $rule_ids           = M('receive')->where($where." and pid=$pid and status=1")->select();
            $data['actual']     = count($rule_ids);


            $sumRule = 0;
            $rule = 0;
            foreach($rule_ids as $v){
                //$rule = M('expenses','fph_', C('DB_property'))->where("id=".$v['rule_id'])->getField('rule');
                $rule = M('journal_account','fph_', C('DB_log'))->where("sid=".$v['id']." and table_type = 1")->getField('journal_account');
                $sumRule += $rule;
            }
            $data['amount'] =  $sumRule ;
            $expenses           = M('expenses','fph_', C('DB_property'))->field('copies,rule')->where("id=$rule_id")->find();
            $data['trip']       = $expenses['rule']?$expenses['rule']:0;
            $data['total']      = $expenses['copies']?$expenses['copies']:0;
            M('property', 'fph_', C('DB_fangpinhui'));
            $propertyInfo       = M('property')->where("id=$pid")->field('id,title,city_id')->find();
            $city_id            = $propertyInfo['city_id'];
            if($city_id){
                $city_spid      = M('city')->where('id ='.$city_id)->getField('spid');
            }else{
                $city_spid      = '';
            }

            $spid_arr           = explode('|', $city_spid.$city_id);
            $data['prov_id']    = $spid_arr[0]?$spid_arr[0]:0;
            $data['city_id']    = $spid_arr[1]?$spid_arr[1]:0;
            $data['area_id']    = $spid_arr[2]?$spid_arr[2]:0;
            $data['time']       = $start_time;
            M('trip_stat', 'fph_', C('DB_log'));
            $tripInfo = M('trip_stat')->where(array('pid'=>$pid,'time'=>$start_time))->find();
            if($tripInfo){
                $tsid = M('trip_stat')->where(array('id'=>$tripInfo['id']))->save($data);
                if($tsid){
                    M('trip_stat_record')->where(array('tsid'=>$tripInfo['id']))->delete();
                }
            }else{
                $tsid = M('trip_stat')->add($data);
            }
            if($tsid){
                foreach($addReceiveData[$pid] as $k => $val){
                    $addRecord[$k]['tsid']          = $val['pid'];
                    $addRecord[$k]['uid']           = $val['uid'];
                    $addRecord[$k]['apply_time']    = $val['add_time'];
                    $addRecord[$k]['record_time']   = $val['receive_time'];
                    $addRecord[$k]['status']        = $val['status'];
                }
                M('trip_stat_record')->addAll($addRecord);
            }
        }
    }

    /**
     * 生成随机码
     * mihailong
     */
    public function createCode(){
        if(IS_CLI != 1 || MODE_NAME != 'cli')exit;
        M(NULL, 'fph_', C('DB_activity'));
        $tasks = M('tasks')->where(array('status'=>0))->select();
        if(!$tasks)return;
        foreach($tasks as $val){
            $data = array();
            if(!$val['pid'])return;
            $i=0;
            while($i < $val['number']){
                $code = $this->randCode();
                $data = array(
                    'pid'			=> $val['pid'],
                    'time_start' 	=> $val['time_start'],
                    'time_end' 		=> $val['time_end'],
                    'status' 		=> 0,
                    'verify_code'   => $code,
                    'add_time' 		=> time(),
                );
                $result = M('verify_code')->add($data);
                if($result){
                    $i++;
                }
            }
            if($i==$val['number']){
                M('tasks')->where(array('id'=>$val['id']))->save(array('status'=>1));
            }
        }
    }

    private function randCode(){
        $randStr = str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789');
        $randNum = rand(1,26);
        $rand = substr($randStr,$randNum,6);
        return $rand;
    }
}