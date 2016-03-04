<?php
class agent_visitAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod_s = D('stores');//门店
        $this->_mod_l = D('stores_log');//门店
    }
    public function min_date(){
        $l_date = $this->_mod_l->order('add_time asc')->getField('add_time');
        $s_date = $this->_mod_s->order('add_time asc')->getField('add_time');
        if($l_date>$s_date){
            $date = $s_date;//最小日期
            if($date==''){
                $date = $l_date;//最小日期
            }
        }else{
            $date = $l_date;//最小日期
            if($date==''){
                $date = $s_date;//最小日期
            }
        }
        return $date;
    }
    //月统计
    public function agent_visit_monthly() {

        $fph = C('DB_PREFIX');
        $search = array();
         //拓展部
        $search['v_1'] = array('5059'=>'范洁');
        //一组
        $search['v_2'] = array('25'=>'徐昊','4263'=>'庞明明','41'=>'程刚','4749'=>'陆敏中','1380'=>'张德玉','9822'=>'林毅');
        //二组
        $search['v_3'] = array('9591'=>'黎强','9586'=>'武玉文','9589'=>'刘伟','9606'=>'张德奇','9592'=>'詹昌斌');
        $uid = $this->_get('uid','trim');
        $agent_status = $this->_get('agent_status','intval');
        $search['agent_status'] = $agent_status;
        $search['uid'] = $uid;
        $where_uid = $where_uid2 = '';
        if(($uid=='one' && $agent_status==1) || $agent_status==2 ){
             $where_uid = ' AND uid in(25,4263,41,4749,1380)';
             $where_uid2 = ' AND A.uid in(25,4263,41,4749,1380)';
        }elseif(($uid=='two' && $agent_status==1) || $agent_status==3){
             $where_uid = ' AND uid in(9591,9586,9589,9606,9592)';
             $where_uid2 = ' AND A.uid in(9591,9586,9589,9606,9592)';
        }
        if($uid != '' && $uid!='one' && $uid!='two'){
            $where_uid = ' AND uid='.$uid.' ';
            $where_uid2 = ' AND A.uid='.$uid.'';
        }
        $time_start = $this->_get('time_start','trim');
        $arr_tim = explode('-', $time_start);
        $month = $this->_get('month','trim');
        $get_month = $this->_get('get_month','intval');
        $agent_status = $this->_get('agent_status','intval');
        $search['agent_status'] = $agent_status;
        $is_min = $this->_get('is_min','intval');
        $search['is_min'] = $is_min;
        $search['month'] = $month;
        $select_city = $this->_get('select_city','intval');
        if($select_city){
            $search['select_city'] = $select_city;
            if($select_city==803){
                $str_pro = ' OR id=802 ';
            }
        }else{
            $select_city = 803;
            $search['select_city'] = 803;
            $str_pro = ' OR id=802 ';
        }
        $where =' AND city_id in(select id from fph_city where id = '.$select_city.$str_pro.' or spid RLIKE "[[:<:]]'.$select_city.'[[:>:]]")';
        $type = $this->_get('type','intval');
        $search['type'] = $type;
        //根据当前时间 判定周几 当天所在的周一和周日
        //$search['time_start'] = date('Y',strtotime($time_start)).'-'.$month;
        $search['time_start'] = $time_start;
        $date = $time_start;
        //if($arr_tim[2]){
        if($get_month==1){
            $date = date('Y',strtotime($time_start)).'-'.$month.'-01';
            $search['time_start'] = date('Y',strtotime($time_start)).'-'.$month.'-01';
        }else{
            //$search['time_start'] = date('Y-m',strtotime($time_start));
            $search['month'] = date('m',strtotime($time_start));
        }
        $min_date = $this->min_date();
        $min_date = strtotime(date('Y-m-d',$min_date));
        $search['min_date'] = date('Y-m-d',$min_date);
        if(strtotime($date)<$min_date){
            $date = date('Y-m-d',$min_date);
            $search['time_start'] = date('Y-m-d',$min_date);
            $search['is_min'] = 1;
            $search['month'] = date('m',$min_date);
        }

         //如果大于当天
        if(strtotime($date) >strtotime(date('Y-m-d'))){
            $date = date('Y-m-d');
            $search['time_start'] = date('Y-m-d');
            $search['is_min'] = 2;
            $search['month'] = date('m',strtotime($date));
        }
        if($type==1){
             $this->redirect('admin/agent_visit/agent_visit_weekly',array('time_start'=>$search['time_start'],'is_min'=>$search['is_min'],'select_city'=>$select_city,'agent_status'=>$agent_status,'uid'=>$uid,'type'=>1,'roleid'=>1));
        }elseif($type==''){
            $type=2;
        }
        //根据月份返回当年天数
        $days_m = date("t",strtotime($date));
        $search['time'] = date('m月',strtotime($date));//当前月份
        $search['date_n'] = $date;//当前日期
        $month_num = array();
        $get_week = $this->get_week($date);
        $search['week'] = $get_week;
        $search['date_w'] = $get_week['date_w'];
        $days=round(strtotime($get_week['next'])-strtotime($get_week['pre']));
        $wkday_ar=array('一','二','三','四','五','六','日');
        foreach($wkday_ar as $k=>$v){
            $search['w_date'] = $wkday_ar;
        }
        for($i=1;$i<=$days_m;$i++){
            $month_num[$i]['num'] = $i;
        }
        $arr_date = $this->getMonthRange($date);
        $m_pro = $arr_date['sdate'];
        $m_next = $arr_date['edate'];
        $w_date = date('w',strtotime($m_pro));//前空几个空格
        if($w_date==0){
            $w_date=7;
        }
        $w_date = $w_date-1;
        $c_num = array();
        for($i=1;$i<=$w_date;$i++){
            $c_num[] = $i;
        }
        $c_num_1 = array();
        foreach($c_num as $k=>$v){
           $k = $w_date - $k;
           $c_num_1[$k]['num'] = $v;
           //$c_num_1[$k]['link'] = date('d',strtotime($m_pro)-($k*3600*24));
           //$c_num_1[$k]['bg'] =1;
        }
        $this->assign('ym', date('Y-m',strtotime($time_start)));
        $search['month_num'] = $month_num;
        foreach($search['month_num'] as $key=>$val){
            $nn = $val['num'];
            if($val['num']<10){
                $nn = '0'.$val['num'];
            }
            $now = strtotime(date('Y-m-d'));
            $link_n = strtotime(date('Y-m',strtotime($date)).'-'.$val['num']);
            $search['month_num'][$key]['link']='<b><a href="'.U('admin/agent_visit/agent_visit_weekly',array('time_start'=> date('Y-m',strtotime($search['time_start'])).'-'.$nn,'select_city'=>$search['select_city'],'uid'=>$uid,'agent_status'=>$agent_status,'roleid'=>1)).'">'.$val['num'].'</a></b>';
            if(($link_n>$now) || ($link_n<$min_date)){
                $search['month_num'][$key]['link']=$val['num'];
                $search['month_num'][$key]['bg']=1;
            }
        }
        $search['month_num'] = array_merge($c_num_1,$search['month_num']);

        //月统计
        //拓展门店信息
        $m_l_list = $this->_mod_s->where("add_time between ".strtotime($m_pro.' 00:00:00')." and ".strtotime($m_next.'23:59:59')."".$where.$where_uid)->select();
        $search['m_s_count'] = count($m_l_list);
        $m_u_count = 0;
        foreach($m_l_list as $ke=>$va){
            $m_u_count += M('user')->where("stores_id=".$va['id'])->count('id');
        }
        $search['m_u_count'] = $m_u_count;
        //拜访门店信息 stores_log
        $where2 =' AND B.city_id in(select id from fph_city where id = '.$select_city.$str_pro.' or spid RLIKE "[[:<:]]'.$select_city.'[[:>:]]")';
         $search['m_l_count'] = count($this->_mod_l
        ->table("{$fph}stores_log AS A
        LEFT JOIN {$fph}stores AS B ON A.pid = B.id")
        ->where("A.add_time between ".strtotime($m_pro.' 00:00:00')." and ".strtotime($m_next.' 23:59:59')."".$where2.$where_uid2)->group('A.pid')->select());
        //区域显示
        $city = $this->_mod_s->field('city_id')->where('city_id !=0 ')->group('city_id')->select();
        $city_id = '';
        $id_str =array();
        $str = '';
        foreach($city as $k=>$v){
           $str  = $this->get_city($v['city_id']);
           if($str){
              $id_str[]=$str;
           }
        }
        $city_id = array_unique($id_str);
        $citylist = M('city')->where('id in('.implode(',',$city_id).')')->order('FIELD(id ,'.$search['select_city'].') DESC ')->select();
        if($time_start==''){
            $this->assign('ym', date('Y-m',strtotime(date('Y-m-d'))));
        }
        $this->assign('search', $search);
        $this->assign('citylist', $citylist);
        $this->display();
    }
    
    //周统计
    public function agent_visit_weekly() {
        $fph = C('DB_PREFIX');
        $search = array();
         //拓展部
        $search['v_1'] = array('5059'=>'范洁');
        //一组
        $search['v_2'] = array('25'=>'徐昊','4263'=>'庞明明','41'=>'程刚','4749'=>'陆敏中','1380'=>'张德玉','9822'=>'林毅');
        //二组
        $search['v_3'] = array('9591'=>'黎强','9586'=>'武玉文','9589'=>'刘伟','9606'=>'张德奇','9592'=>'詹昌斌');

        $time_start = $this->_get('time_start','trim');
        $uid = $this->_get('uid','trim');
        $select_city = $this->_get('select_city','intval');
        $is_min = $this->_get('is_min','intval');
        $agent_status = $this->_get('agent_status','intval');
        $search['agent_status'] = $agent_status;
        $search['uid'] = $uid;
        $where_uid = $where_uid2 = '';
        if(($uid=='one' && $agent_status==1) || $agent_status==2 ){
             $where_uid = ' AND uid in(25,4263,41,4749,1380)';
             $where_uid2 = ' AND A.uid in(25,4263,41,4749,1380)';
        }elseif(($uid=='two' && $agent_status==1) || $agent_status==3){
             $where_uid = ' AND uid in(9591,9586,9589,9606,9592)';
             $where_uid2 = ' AND A.uid in(9591,9586,9589,9606,9592)';
        }
        if($uid != '' && $uid!='one' && $uid!='two'){
            $where_uid = ' AND uid='.$uid.' ';
            $where_uid2 = ' AND A.uid='.$uid.'';
        }

        $search['is_min'] = $is_min;
        if($select_city){
            $search['select_city'] = $select_city;
            if($select_city==803){
                $str_pro = ' OR id=802 ';
            }
        }else{
            $select_city = 803;
            $search['select_city'] = 803;
            $str_pro = ' OR id=802 ';
        }
        $where =' AND city_id in(select id from fph_city where id = '.$select_city.$str_pro.' or spid RLIKE "[[:<:]]'.$select_city.'[[:>:]]")';

        $type = $this->_get('type','intval');
        $search['type'] = $type;
        //根据当前时间 判定周几 当天所在的周一和周日
        $date = date('Y-m-d');
        if($time_start){
            $date = date('Y-m-d',strtotime($time_start));
            $search['time_start'] = $time_start;
        }else{
            $search['time_start'] = $date;
        }
        $min_date = $this->min_date();
        $search['min_date_bj'] = $min_date;
        if(strtotime($date)<$min_date){
            $date = date('Y-m-d',$min_date);
            $search['time_start'] = date('Y-m-d',$min_date);
            $search['is_min'] = 1;
        }
        $search['min_date'] = date('Y-m-d',$min_date);
        
        $search['time'] = date('m月d日',strtotime($date));
        $search['date_n'] = $date;
        //如果大于当天
        if(strtotime($date) >strtotime(date('Y-m-d'))){
            $date = date('Y-m-d');
            $search['time_start'] = date('Y-m-d');
            $search['is_min'] = 2;
            $search['date_n'] = $date;
            //$search['min_date_bj'] = date('Y-m-d');
        }
        if($type==2){
           $this->redirect('admin/agent_visit/agent_visit_monthly',array('time_start'=>$search['time_start'],'is_min'=>$search['is_min'],'select_city'=>$select_city,'agent_status'=>$agent_status,'uid'=>$uid,'type'=>2,'month'=>date('m',strtotime($time_start)),'roleid'=>1));
        }elseif($type==''){
            $type=1;
        }
        $get_week = $this->get_week($date);
        $search['week'] = $get_week;
        $search['date_w'] = $get_week['date_w']-1;
        if($search['date_w']==0){
            $search['date_w']=7;
        }
        $days=round(strtotime($get_week['next'])-strtotime($get_week['pre']));
        $wkday_ar=array('一','二','三','四','五','六','日');
        //日期与星期对齐判断
        if((date('Y-m-d',(strtotime($time_start)-(3600*24*6)))==$get_week['pre']) &&$search['date_w']!=7 ){
            $get_week[pre] = date('Y-m-d',(strtotime($get_week['pre'])+(3600*24*6)));
        }
        foreach($wkday_ar as $k=>$v){
            $search['date']['tim'][$k]['tim'] = date('Y-m-d',strtotime("$get_week[pre] $k days"));
            $search['date']['dat'] = $wkday_ar;
        }
         foreach($search['date']['tim'] as $key=>$val){
            $now = strtotime(date('Y-m-d'));
            $link_n = strtotime($val['tim']);
            $search['date']['tim'][$key]['link']='<a  href="'.U('admin/agent_visit/agent_visit_weekly',array('time_start'=>$val['tim'],'uid'=>$uid,'agent_status'=>$agent_status,'select_city'=>$search['select_city'],'roleid'=>1)).'">'.$val['tim'].'</a>';
            if(($link_n>$now) || ($link_n<$min_date)){
                $search['date']['tim'][$key]['link']=$val['tim'];
                $search['date']['tim'][$key]['bg']=1;
            }
        }

        //拓展门店信息
        $search['s_list'] = $this->_mod_s->field("id,uid,name,add_time")->where("add_time between ".strtotime($date.' 00:00:00')." and ".strtotime($date.'23:59:59')."".$where.$where_uid)->select();
        $s_num = 0;
        foreach($search['s_list'] as $k1=>$v1){
            $s_num++;
            //经济人数量
            $search['s_list'][$k1]['count'] = M('user')->where("stores_id=".$v1['id'])->count('id');
            $search['s_list']['u_count'] += $search['s_list'][$k1]['count'];
            //服务专员
            $service = M('stores')->field('id,uid,service,sid')->where("id=".$v1['id'])->find();
            if($service['sid']!=0){
                $search['s_list'][$k1]['username'] = M('admin')->where(array('id'=>$service['sid']))->getField('username');
            }else{
                $search['s_list'][$k1]['username'] = M('user')->where(array('id'=>$service['uid']))->getField('username');
            }
        }

        $search['s_list']['s_count'] =$s_num;
        $where2 =' AND B.city_id in(select id from fph_city where id = '.$select_city.$str_pro.' or spid RLIKE "[[:<:]]'.$select_city.'[[:>:]]")';
       
        //拜访门店信息 stores_log
        $search['l_list'] = $this->_mod_l
        ->field('A.id,A.pid,A.add_time')
        ->table("{$fph}stores_log AS A
        LEFT JOIN {$fph}stores AS B ON A.pid = B.id")
    ->where("A.add_time between ".strtotime($date.' 00:00:00')." and ".strtotime($date.' 23:59:59')."".$where2.$where_uid2)->group('A.pid')->select();
        $l_num = 0;
        foreach($search['l_list'] as $k2=>$v2){
            $l_num++;
            $search['l_list'][$k2]['name'] = M('stores')->where('id='.$v2['pid'])->getField('name');
            //服务专员
           $service = M('stores')->field('id,uid,service,sid')->where("id=".$v2['pid'])->find();
            if($service['sid']!=0){
                $search['l_list'][$k2]['username'] = M('admin')->where(array('id'=>$service['sid']))->getField('username');
            }else{
                $search['l_list'][$k2]['username'] = M('user')->where(array('id'=>$service['uid']))->getField('username');
            }
        }
        $search['l_list']['l_count'] =$l_num;
        //周统计
        //拜访门店信息 stores_log
        //$search['w_l_count'] = count($this->_mod_l->group('pid')->where("add_time between ".strtotime($get_week['pre'].' 00:00:00')." and ".strtotime($get_week['next'].' 23:59:59')."")->select());
         $search['w_l_count'] = count($this->_mod_l
    ->table("{$fph}stores_log AS A
        LEFT JOIN {$fph}stores AS B ON A.pid = B.id")
    ->where("A.add_time between ".strtotime($get_week['pre'].' 00:00:00')." and ".strtotime($get_week['next'].' 23:59:59')."".$where2.$where_uid2)->group('A.pid')->select());
        //拓展门店信息
        $w_l_list = $this->_mod_s->where("add_time between ".strtotime($get_week['pre'].' 00:00:00')." and ".strtotime($get_week['next'].'23:59:59')."".$where.$where_uid)->select();
        $search['w_s_count'] = count($w_l_list);
        $w_u_count = 0;
        foreach($w_l_list as $ke=>$va){
            $w_u_count += M('user')->where("stores_id=".$va['id'])->count('id');
        }
        $search['w_u_count'] = $w_u_count;
        foreach($search['week'] as $w=>$w1){
            $search['week'][$w] = date('m月d日',strtotime($w1));
        }
       
        //区域显示
        $city = $this->_mod_s->field('city_id')->where('city_id !=0 ')->group('city_id')->select();
        $city_id = '';
        $id_str =array();
        $str = '';
        foreach($city as $k=>$v){
           $str  = $this->get_city($v['city_id']);
           if($str){
              $id_str[]=$str;
           }
        }
        $city_id = array_unique($id_str);
        $citylist = M('city')->where('id in('.implode(',',$city_id).')')->order('FIELD(id ,'.$search['select_city'].') DESC ')->select();
        $this->assign('search', $search);
        $this->assign('citylist', $citylist);
        $this->display();
    }

    //获取对应城市ID
    public function get_city($id){
        $str='';
        $info = M('city')->field('id,pid,name,spid')->where('id='.$id)->find();
        $spid = $info['spid'];
        $arr_spid  =explode('|', $spid);
        $count = count(explode('|', $spid));
        if($count==2){
             $str = $info['id'];
        }elseif($count ==3){
             $str =M('city')->field('id')->where('id='.$info['pid'])->getField('id'); 
        }elseif($count >=4){
             $str =M('city')->field('id')->where('id='.$arr_spid[1])->getField('id');
        }
        return $str;
    }
     public function get_week($date){
        $first=1;//$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
        $w=date('w',strtotime($date));
        $ret['date_w'] = $w+1;
        $ret['pre']=date('Y-m-d',strtotime("$date -".($w ? $w - $first : 6).' days')); 
        $ret['next']=date('Y-m-d',strtotime("$ret[pre] +6 days")); 
        return $ret;
    }
    public function getMonthRange($date){
        $ret=array();
        $timestamp=strtotime($date);
        $mdays=date('t',$timestamp);
        // $ret['sdate']=date('Y-m-1 00:00:00',$timestamp);
        // $ret['edate']=date('Y-m-'.$mdays.' 23:59:59',$timestamp);
        $ret['sdate']=date('Y-m-1',$timestamp);
        $ret['edate']=date('Y-m-'.$mdays,$timestamp);
        return $ret;
     }
  



}