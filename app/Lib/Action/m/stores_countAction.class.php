<?php
class stores_countAction extends m_userbaseAction {
    public function _initialize() {
		parent::_initialize();
		$this->_mod_s = D('stores');//门店
		$this->_mod_l = D('stores_log');//门店
		date_default_timezone_set('Asia/Shanghai');
	}
    
	//门店拜访日统计
	public function index(){
		$uid = $this->m_user_cookie['id'];
		$arr_day = $l_count_arr = $s_count_arr =  array();
		$l_date = $this->_mod_l->where('mid='.$uid)->order('id asc')->getField('add_time');
		$s_date = $this->_mod_s->where('mid='.$uid)->order('id asc')->getField('add_time');
		if($l_date>$s_date){
			$date = $s_date;//最小日期
			if($date==0){
				$date = $l_date;//最小日期
			}
		}else{
			$date = $l_date;//最小日期
			if($date==0){
				$date = $s_date;//最小日期
			}
		}
		$str_date = $date;
		$arr_hours = array();
		//日期数
		$days=round((strtotime(date('Y-m-d',time()))-strtotime(date('Y-m-d',$date)))/3600/24);

		$days_str = $days;
		if($days>=5){
			$days = 5;
		}elseif($days==0){
			$days ='0';
		}elseif($days==''){
			$days ='';
		}
		for($i=0;$i<=$days;$i++){
			$date   = strtotime("-$i day");// 03-11
			$arr_hours[$i]['date']= date("Y-m-d",$date);

		}
		foreach($arr_hours as $key=>$val){
			//拜访门店信息 stores_log
			$arr_hours[$key]['l_list'] = $this->_mod_l->field("id,pid,add_time")->group('pid')->where("mid=".$uid." AND add_time between ".strtotime($val['date'].' 00:00:00')." and ".strtotime($val['date'].' 23:59:59')."")->select();
			$arr_hours[$key]['l_count'] = count($arr_hours[$key]['l_list']);
			foreach($arr_hours[$key]['l_list'] as $k=>$v){
				$arr_hours[$key]['l_list'][$k]['name'] = M('stores')->where('id='.$v['pid'])->getField('name');
			}
			//拓展门店信息
			$arr_hours[$key]['s_list'] = $this->_mod_s->field("id,name,add_time")->where("mid=".$uid." AND add_time between ".strtotime($val['date'].' 00:00:00')." and ".strtotime($val['date'].'23:59:59')."")->select();
			$arr_hours[$key]['s_count'] = count($arr_hours[$key]['s_list']);
			foreach($arr_hours[$key]['s_list'] as $ke=>$va){
				//经纪人数量
				$arr_hours[$key]['s_list'][$ke]['s_user'] = M('user')->field('stores_id,username')->where("stores_id=".$va['id'])->count('id');
				$arr_hours[$key]['s_list']['u_count'] +=$arr_hours[$key]['s_list'][$ke]['s_user'];
			}
		}
		if($str_date=='' || $days==''){
			$arr_hours=array();
		}
		//print_r($arr_hours);
		$this->assign('str_date', $str_date);
		$this->assign('days', $days);

        $this->assign('days_str', $days_str+1);
		$this->assign('arr_hours', $arr_hours);
		$this->assign('setTitle', '门店拜访日统计');
		$this->_config_seo();
		$this->display();
	}
	public function ajax_stores_index_list(){
		$page = $this->_post('page','intval');
		$uid = $this->m_user_cookie['id'];
		$arr_day = $l_count_arr = $s_count_arr =  array();
		$l_date = $this->_mod_l->where('mid='.$uid)->order('id asc')->getField('add_time');
		$s_date = $this->_mod_s->where('mid='.$uid)->order('id asc')->getField('add_time');
        if($l_date>$s_date){
			$date = $s_date;//最小日期
			if($date==0){
				$date = $l_date;//最小日期
            }
		}else{
			$date = $l_date;//最小日期
			if($date==0){
				$date = $s_date;//最小日期
			}
		}
		$arr_hours = array();
		$str = '';
		//日期数
		$days2=round((strtotime(date('Y-m-d',time()))-strtotime(date('Y-m-d',$date)))/3600/24)+1;//39
		$days = 5+$page*5;//    42
		if($days>$days2){ 
			$days =$days2;
		}
		for($i=0;$i<$days;$i++){
			$date   = strtotime("-$i day");// 03-11
			$arr_hours[$i]['date']= date("Y-m-d",$date);
		}
		foreach($arr_hours as $key=>$val){
			//拜访门店信息 stores_log
			$arr_hours[$key]['l_list'] = $this->_mod_l->field("id,pid,add_time")->group('pid')->where("mid=".$uid." AND add_time between ".strtotime($val['date'].' 00:00:00')." and ".strtotime($val['date'].' 23:59:59')."")->select();
			$arr_hours[$key]['l_count'] = count($arr_hours[$key]['l_list']);
			foreach($arr_hours[$key]['l_list'] as $k=>$v){
				$arr_hours[$key]['l_list'][$k]['name'] = M('stores')->where('id='.$v['pid'])->getField('name');
			}
			//拓展门店信息
			$arr_hours[$key]['s_list'] = $this->_mod_s->field("id,name,add_time")->where("mid=".$uid." AND add_time between ".strtotime($val['date'].' 00:00:00')." and ".strtotime($val['date'].'23:59:59')."")->select();
			$arr_hours[$key]['s_count'] = count($arr_hours[$key]['s_list']);
			foreach($arr_hours[$key]['s_list'] as $ke=>$va){
				//经纪人数量
				$arr_hours[$key]['s_list'][$ke]['s_user'] = M('user')->field('stores_id,username')->where("stores_id=".$va['id'])->count('id');
				//$arr_hours[$key]['s_list']['u_count'] +=count($arr_hours[$key]['s_list'][$ke]['s_user']);
				$arr_hours[$key]['s_list']['u_count'] +=$arr_hours[$key]['s_list'][$ke]['s_user'];
			}
		}
		$maxdate = $arr_hours[0]['date'];
		$str = '';
		foreach($arr_hours as  $kk=>$vv){
			$str .= '<section class="table_blocks"> <span class="LABELS" style="display:block">';
			$str .= ''.$vv['date'].'';
			$str .= '</span><div class="tablebg"><table class="baifang"><thead><th>拜访门店(';
			$str .= ''.$vv['l_count'].'';	
			$str .= ')</th></thead><tbody>';
			foreach($vv['l_list'] as $k1=>$v1){
				$str .= '<tr><td>';
				$str .= ''.$v1[name].'';	
				$str .= '</td></tr>';
			}
			$str .= ' </tbody></table><table class="tuozhan"><colgroup><col width="50%"></colgroup><thead><th>拓展门店(';
				$str .= ''.$vv['s_count'].'';
	         $str .= ')</th><th>经纪人(';


	          if($vv['s_list']['u_count']){
					$str .=''.$vv['s_list']['u_count'].'';
				}else{
					$str .='0';
				}
	         	$str .= ')</th></thead><tbody>';
	         foreach($vv['s_list'] as $k2=>$v2){
	         	if($v2['name']){
	         		$str .= '<tr><td>';
	         		$str .=''.$v2['name'].'';
	         		$str .= '</td><td>';
	         		$str .=''.$v2['s_user'].'';
	         		$str .= '</td></tr>';
	         	}
	         }
	         $str .= '</tbody></table></div></section>';
		}
		if($days<=$days2){
			$this->ajaxReturn(1,'',$str.'$$$'.$days);
		}else{
			$this->ajaxReturn(0,'别滑动了，已经到底了...');
		}
	}
	public function getWeekRange($date){
		$ret=array();
		$first=1; //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
		$w=date('w',strtotime($date));  //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
		$ret['sdate']=date('Y-m-d',strtotime("$date -".($w ? $w - $first : 6).' days'));
		$ret['edate']=date('Y-m-d',strtotime("$now_start +6 days")); 
		return $ret;
	}
	//门店拜访周统计
	public function week(){
		$uid = $this->m_user_cookie['id'];
		$arr_day = $l_count_arr = $s_count_arr =  array();
		$l_date = $this->_mod_l->where('mid='.$uid)->order('id asc')->getField('add_time');
		$s_date = $this->_mod_s->where('mid='.$uid)->order('id asc')->getField('add_time');
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
	
		$str_date = $date;
		//日期数
		$days_str=round((strtotime(date('Y-m-d',time()))-strtotime(date('Y-m-d',$date)))/3600/24)+1;
		$days = $days_str;
		//$days = 35;
		if($days_str >=35){
			$days = 35;
		}

		for($i=0;$i<$days;$i++){
			$date   = strtotime("-$i day");// 03-11
			$arr_hours[$i]['date']= date("Y-m-d",$date);
		}
		$arr_week = array();
		foreach($arr_hours as $key=>$val){
			if($key%7 ==0){

				$tim = date('Y-m-d',strtotime($val['date'].'-1week'));
				$tim = date('Y-m-d',strtotime($tim)+(3600*24));
				$date = $val['date'];
				$first=1; 
				$w=date('w',strtotime($date));  
				$tim=date('Y-m-d',strtotime("$date -".($w ? $w - $first : 6).' days')); 
				$val['date']=date('Y-m-d',strtotime("$tim +6 days")); 
				$arr_week[$key]['title'] = date('Y年m月d日',strtotime($tim)).'-'.date('Y年m月d日',strtotime($val['date']));
				//拜访门店信息 stores_log
		 		$arr_week[$key]['l_count'] += count($this->_mod_l->group('pid')->where("mid=".$uid." AND add_time between ".strtotime($tim.' 00:00:00')." and ".strtotime($val['date'].' 23:59:59')."")->select());
		 		//拓展门店信息
		 		$arr_week[$key]['s_count'] += count($this->_mod_s->where("mid=".$uid." AND add_time between ".strtotime($tim.' 00:00:00')." and ".strtotime($val['date'].'23:59:59')."")->select());
		 		$arr_week[$key]['s_list'] = $this->_mod_s->field('id')->where("mid=".$uid." AND add_time between ".strtotime($tim.' 00:00:00')." and ".strtotime($val['date'].'23:59:59')."")->select();
		 		foreach($arr_week[$key]['s_list'] as $ke=>$va){
		 			$arr_week[$key]['s_list']['count'] += M('user')->where("stores_id=".$va['id'])->count('id');
		 		}
		 		
			}
		}
		if($str_date==''){
			$arr_week = array();
		}
		$this->assign('str_date', $str_date);
		$this->assign('days', $days);
		$this->assign('days_str', $days_str);
		$this->assign('arr_week', $arr_week);
		$this->assign('arr_hours', $arr_hours);
		$this->assign('setTitle', '门店拜访周统计');
		$this->_config_seo();
		$this->display();
	}
	public function ajax_stores_week_list(){
		$page = $this->_post('page','intval');
		$uid = $this->m_user_cookie['id'];
		$arr_day = $l_count_arr = $s_count_arr =  array();
		$l_date = $this->_mod_l->where('mid='.$uid)->order('id asc')->getField('add_time');
		$s_date = $this->_mod_s->where('mid='.$uid)->order('id asc')->getField('add_time');
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
        $aadd = date('Y-m-d',$date);
		//日期数
		$days2=round((strtotime(date('Y-m-d',time()))-strtotime(date('Y-m-d',$date)))/3600/24)+1;
		$days = 35+$page*35;
		if($days>$days2){
			$days =$days2;
		}
        $ww = '';
		for($i=0;$i<$days;$i++){
			$date   = strtotime("-$i day");// 03-11
            $arr_hours[$i]['date']= date("Y-m-d",$date);
            if($i % 7 ==0){
                $ww =$arr_hours[$i]['date'];
            }
		}
        $arr_date  =$cards=$cards_arr = array();
        if($days<=$days2){
            if($days==$days2){
              // $arr_hours = $cards_arr;
                if(strtotime($ww)>strtotime($aadd)){
	                foreach($arr_hours as $kk=>$vv){
	                    if(strtotime($vv['date'])>= strtotime($ww)){
	                           $cards_arr[$kk]=$vv;
	                    }
	                }
	                $num = (strtotime($ww)-strtotime($aadd))/(3600*24);
	                $get_w_d = $this->getWeekRange($aadd);
	                if((strtotime($ww)-(3600*24*7))>=strtotime($get_w_d['sdate'])){
	                    $num = 7;
	                }
	                for($j=1;$j<=$num;$j++){
	                    $arr_date[$j]['date'] = date('Y-m-d', strtotime($ww)-$j*24*3600);
	                }
	                $cards = array_merge($cards_arr, $arr_date);
	                $arr_hours = $cards;
                }
            }
        }
		$arr_week = array();
		$str = '';
        $dd = '';
		foreach($arr_hours as $key=>$val){
			if($key%7 ==0){
                $dd =  $val['date'];
				$tim = date('Y-m-d',strtotime($val['date'].'-1week'));
				$tim = date('Y-m-d',strtotime($tim)+(3600*24));
				$date = $val['date'];
				$first=1; 
				$w=date('w',strtotime($date));  
				$tim=date('Y-m-d',strtotime("$date -".($w ? $w - $first : 6).' days')); 
				$val['date']=date('Y-m-d',strtotime("$tim +6 days")); 
				$arr_week[$key]['title'] = date('Y年m月d日',strtotime($tim)).'-'.date('Y年m月d日',strtotime($val['date']));
				//拜访门店信息 stores_log
		 		$arr_week[$key]['l_count'] += count($this->_mod_l->group('pid')->where("mid=".$uid." AND add_time between ".strtotime($tim.' 00:00:00')." and ".strtotime($val['date'].' 23:59:59')."")->select());
		 		//拓展门店信息
		 		$arr_week[$key]['s_count'] += count($this->_mod_s->where("mid=".$uid." AND add_time between ".strtotime($tim.' 00:00:00')." and ".strtotime($val['date'].'23:59:59')."")->select());
		 		$arr_week[$key]['s_list'] = $this->_mod_s->field('id')->where("mid=".$uid." AND add_time between ".strtotime($tim.' 00:00:00')." and ".strtotime($val['date'].'23:59:59')."")->select();
		 		//经纪人数量
				foreach($arr_week[$key]['s_list'] as $ke=>$va){
		 			$arr_week[$key]['s_list']['count'] += M('user')->where("stores_id=".$va['id'])->count('id');
		 		}
                                
			}
		}
		foreach($arr_week as $k=>$v){
			$str .= '<section class="table_blocks"> <span class="LABELS">';
	 		$str .= ''.$v['title'].'';
			$str .= '</span><table><thead><th>拜访门店</th><th>拓展门店</th><th>经纪人数量</th></thead><tbody><tr><td>';
			if($v['l_count']){
	 			$str .= ''.$v['l_count'].'';
	 		}else{
	 			$str .= '0';
	 		}
	 		$str .= '</td><td>';
	 		$str .= ''.$v['s_count'].'';
	 		$str .= '</td><td>';
	 		if($v['s_list']['count']){
	 			$str .= ''.$v['s_list']['count'].'';
	 		}else{
	 			$str .= '0';
	 		}
            $str .= '</td></tr></tbody></table></section>';
		}
		if($days<=$days2){
			$this->ajaxReturn(1,'',$str.'$$$'.$days);
		}else{
			$this->ajaxReturn(0,'别滑动了，已经到底了...');
		}
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
	//门店拜访月统计
	public function month(){
		$uid = $this->m_user_cookie['id'];
		$arr_day = $l_count_arr = $s_count_arr =  array();
		$l_date = $this->_mod_l->where('mid='.$uid)->order('id asc')->getField('add_time');
		$s_date = $this->_mod_s->where('mid='.$uid)->order('id asc')->getField('add_time');
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
		$str_date = $date;
		//日期数
		$days_str=round((strtotime(date('Y-m-d',time()))-strtotime(date('Y-m-d',$date)))/3600/24)+1;
		$days = $days_str;
		if($days >=120){
			$days = 120;
		}
		for($i=0;$i<=$days;$i++){
			$date   = strtotime("-$i day");// 03-11
			$arr_hours[$i]['date']= date("Y-m-d",$date);
		}
		$time = date('Y-m-d H:i:s', time());
		$arr_month = array();
		foreach($arr_hours as $key=>$val){
			if($key%31 == 0){

				$tim = date('Y-m-d',strtotime($val['date'].'-1month'));
				$arr_date = $this->getMonthRange($val['date']);
				$tim = $arr_date['sdate'];
				$val['date'] = $arr_date['edate'];
				$arr_month[$key]['title'] = date('Y年m月',strtotime($val['date']));
				//拜访门店信息 stores_log
		 		$arr_month[$key]['l_count'] += count($this->_mod_l->group('pid')->where("mid=".$uid." AND add_time between ".strtotime($tim.' 00:00:00')." and ".strtotime($val['date'].' 23:59:59')."")->select());
		 		
		 		//拓展门店信息
		 		$arr_month[$key]['s_count'] += count($this->_mod_s->where("mid=".$uid." AND add_time between ".strtotime($tim.' 00:00:00')." and ".strtotime($val['date'].'23:59:59')."")->select());
		 		$arr_month[$key]['s_list'] = $this->_mod_s->field('id')->where("mid=".$uid." AND add_time between ".strtotime($tim.' 00:00:00')." and ".strtotime($val['date'].'23:59:59')."")->select();
		 		//经纪人数量
				foreach($arr_month[$key]['s_list'] as $ke=>$va){
		 			$arr_month[$key]['s_list']['count'] += M('user')->where("stores_id=".$va['id'])->count('id');
		 		}
			}
		}
		if($str_date==''){
			$arr_month = array();
		}
		$this->assign('str_date', $str_date);
		$this->assign('days', $days);
		$this->assign('days_str', $days_str);
		$this->assign('arr_month', $arr_month);
		$this->assign('arr_hours', $arr_hours);
		$this->assign('setTitle', '门店拜访月统计');
		$this->_config_seo();
		$this->display();
	}
	public function ajax_stores_month_list(){
		$page 	 = $this->_post('page','intval');
		$uid 	 = $this->m_user_cookie['id'];
		$arr_day = $l_count_arr = $s_count_arr =  array();
		$l_date  = $this->_mod_l->where('mid='.$uid)->order('id asc')->getField('add_time');
		$s_date  = $this->_mod_s->where('mid='.$uid)->order('id asc')->getField('add_time');
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
		//日期数
		$days2=round((strtotime(date('Y-m-d',time()))-strtotime(date('Y-m-d',$date)))/3600/24)+1;
		$days = 120+$page*120;
		if($days>$days2){
			$days =$days2;
		}
		
		for($i=0;$i<=$days;$i++){
			$date   = strtotime("-$i day");// 03-11
			$arr_hours[$i]['date']= date("Y-m-d",$date);
		}
		$time = date('Y-m-d H:i:s', time());
		$arr_month = array();
		$str = '';
		foreach($arr_hours as $key=>$val){
			if($key%31 == 0){
				$tim = date('Y-m-d',strtotime($val['date'].'-1month'));

				$arr_date = $this->getMonthRange($val['date']);
				$tim = $arr_date['sdate'];
				$val['date'] = $arr_date['edate'];

				$arr_month[$key]['title'] = date('Y年m月',strtotime($val['date']));
				//拜访门店信息 stores_log
		 		$arr_month[$key]['l_count'] += count($this->_mod_l->group('pid')->where("mid=".$uid." AND add_time between ".strtotime($tim.' 00:00:00')." and ".strtotime($val['date'].' 23:59:59')."")->select());
		 		//拓展门店信息
		 		$arr_month[$key]['s_count'] += count($this->_mod_s->where("mid=".$uid." AND add_time between ".strtotime($tim.' 00:00:00')." and ".strtotime($val['date'].'23:59:59')."")->select());
		 		$arr_month[$key]['s_list'] = $this->_mod_s->field('id')->where("mid=".$uid." AND add_time between ".strtotime($tim.' 00:00:00')." and ".strtotime($val['date'].'23:59:59')."")->select();
		 		//经纪人数量
		 		foreach($arr_month[$key]['s_list'] as $ke=>$va){
		 			$arr_month[$key]['s_list']['count'] += M('user')->where("stores_id=".$va['id'])->count('id');
		 		}
			}
		}
		foreach($arr_month as $k=>$v){
			$str .= '<section class="table_blocks"> <span class="LABELS">';
	 		$str .= ''.$v['title'].'';
			$str .= '</span><table><thead><th>拜访门店</th><th>拓展门店</th><th>经纪人数量</th></thead><tbody><tr><td>';
	 		$str .= ''.$v['l_count'].'';
	 		$str .= '</td><td>';
	 		$str .= ''.$v['s_count'].'';
	 		$str .= '</td><td>';
	 		if($v['s_list']['count']){
	 			$str .= ''.$v['s_list']['count'].'';
	 		}else{
	 			$str .= '0';
	 		}
            $str .= '</td></tr></tbody></table></section>';
		}
		if($days<=$days2){
			$this->ajaxReturn(1,'',$str.'$$$'.$days);
		}else{
			$this->ajaxReturn(0,'别滑动了，已经到底了...');
		}
	}

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}