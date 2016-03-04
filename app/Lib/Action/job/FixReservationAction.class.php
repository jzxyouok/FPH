<?php

/**
 * Created by PhpStorm.
 * User: ge
 * Date: 15-11-6
 * Time: 下午2:38
 */
class FixReservationAction extends backendAction
{

    public function joinList()
    {
        M(NULL,NULL,C('DB_member'));
        $where = 'pid = eservation_id';
        $fields = 'id,pid,eservation_id,add_time';
        $list = D('join_reservation')->getList($where, $fields, $isPage = false);
        if(!$list) {echo '没有数据需要恢复'; exit;}
        $i = 0;
        foreach($list as $key => $val)
        {
            M('reservation','fph_',C('DB_activity'));
            $data = D('reservation')->getOneByPid($val['pid']);
            foreach($data as $k => $v)
            {
                if(($v['time_start'] <= $val['add_time']) && ($val['add_time']<= $v['time_end']+86400))
                {
                    //更新eservation_id
                    $condition= 'id = '.$val['id'];
                    $save = array(
                        'eservation_id' => $v['id'],
                    );
                    M(NULL,NULL,C('DB_member'));
                    D('join_reservation')->updateRecord($condition, $save);
                    $i++;
                    break;
                }
            }
        }
        echo '共恢复'. $i . '条数据';
    }
}