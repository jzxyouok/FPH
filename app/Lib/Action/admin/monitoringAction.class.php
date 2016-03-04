<?php

// +----------------------------------------------------------------------
// | Descriptions:本页面作为 统计公司需要的相关数据
// +----------------------------------------------------------------------
// | State : 不可以删除此页面，如要删除，前联系作者。
// +----------------------------------------------------------------------
// | Date: 2015-01-07
// +----------------------------------------------------------------------
// | Author: H.J.H
// +----------------------------------------------------------------------

class monitoringAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
       // $this->_mod = D('monitoring');
    }
    
    /*
     * 经纪人开拓周报
    *
    * @param  $weeks   时间显示  往前推6个zhou
    * @param  $count  注册人数
    * @param  $subscribe   关注人数
    * @param  $wx_uv  活跃人数
    * @param  $pc_uv   网站uv
    * @param  $appuv  App中每天和服务器发生交互的用户数
    * @param  $app_active   app活跃人数(App每天和服务器发生交互的注册用户数)
    * @return bool
    */
    public function agent_develop_weekly() {
    	
    	//按照周统计
   
    	$weeks=array();//时间
    	$count=array();//注册的人数
    	$subscribe=array();//关注人数
    	$wx_active = array();//活跃人数
    	$uv=array();//网站UV
    	$appuv=array();//App中每天和服务器发生交互的用户数
    	$app_active = array();//app活跃人数(App每天和服务器发生交互的注册用户数)
    	
    	$arr_day = array();//表格的时间字段
    	
    	for($i=0;$i<6;$i++){
    		$j = (5-$i)*7; 
    		$timestamp = strtotime("-$j day");;
    		$start = date('Y-m-d',$timestamp-(date('N',$timestamp)-1)*86400);//获取当前时间的周一日期
    		$last = date('Y-m-d',$timestamp + (7-date('N',$timestamp))*86400);//获取当前时间的周末日期

    		$weeks[] = "'".$start." 到 " .$last."'";
    		$count[] = M('user')->where(" reg_time between ".strtotime($start.' 00:00:00')." and ".strtotime($last.' 23:59:59')."")->count('id');//统计注册的人数
    		$subscribe[] = M('weixin_user')->where(" add_time between ".strtotime($start.' 00:00:00')." and ".strtotime($last.' 23:59:59')."")->count('id');//统计关注的人数
    		$wx_active[] = M('online')->where("origin=1 and uid!=0 and add_time between ".strtotime($start.' 00:00:00')." and ".strtotime($last.' 23:59:59')."")->count('id');
    		$uv[]        = M('online')->where("origin=4 and uid=0 and add_time between ".strtotime($start.' 00:00:00')." and ".strtotime($last.' 23:59:59')."")->count('id');;
    		$appuv[]     = M('online')->where("origin in(2,3) and uid=0 and add_time between ".strtotime($start.' 00:00:00')." and ".strtotime($last.' 23:59:59')."")->count('id');
    		$app_active[]= M('online')->where("origin in(2,3) and uid!=0 and add_time between ".strtotime($start.' 00:00:00')." and ".strtotime($last.' 23:59:59')."")->count('id');
    		
    		$arr_day[] = $start." 到 " .$last;
    	}
 
    	
        $this->assign('day', implode(',',$weeks));//时间
        $this->assign('count', implode(',',$count));//新注册人数
        $this->assign('subscribe', implode(',',$subscribe));//新增粉丝人数
        $this->assign('wx_active', implode(',',$wx_active));//新注册人数
        $this->assign('uv', implode(',',$uv));//新注册人数
        $this->assign('appuv', implode(',',$appuv));//app
        $this->assign('app_active', implode(',',$app_active));//app活跃人数
        
        $this->assign('arr_day', $arr_day);//时间
        $this->assign('arr_count', $count);//新注册人数
        $this->assign('arr_subscribe', $subscribe);//新增粉丝人数
        $this->assign('arr_wx_active', $wx_active);//新注册人数
        $this->assign('arr_uv', $uv);//新注册人数
        $this->assign('arr_appuv', $appuv);//app
        $this->assign('arr_app_active', $app_active);//app活跃人数
        

        $this->display();
    }
    /*
     * 经纪人开拓日报
    *
    * @param  $days   时间显示  往前推30天
    * @param  $count  注册人数
    * @param  $subscribe   关注人数
    * @param  $wx_uv  活跃人数
    * @param  $pc_uv   网站uv
    * @param  $appuv  App中每天和服务器发生交互的用户数
    * @param  $app_active   app活跃人数(App每天和服务器发生交互的注册用户数)
    * @return bool
    */
    public function agent_develop_daily() {
    	
    	$days=array();//时间
    	$count=array();//注册的人数
    	$subscribe=array();//关注人数
    	$wx_active = array();//活跃人数
    	$uv=array();//网站UV
    	$appuv=array();//App中每天和服务器发生交互的用户数
    	$app_active = array();//app活跃人数(App每天和服务器发生交互的注册用户数)
    	
    	$arr_hours=array();//表格中显示时间
    	
    	for($i=0;$i<30;$i++){ //往前推30天
    		$j=30-$i;
    		$date   = strtotime("-$j day");
    		
    		$days[] = "'".date("m-d",$date)."'";
    		$count[] = M('user')->where(" reg_time between ".strtotime(date('Y-m-d',$date).' 00:00:00')." and ".strtotime(date('Y-m-d',$date).' 23:59:59')."")->count('id');//统计注册的人数
    		$subscribe[] = M('weixin_user')->where(" add_time between ".strtotime(date('Y-m-d',$date).' 00:00:00')." and ".strtotime(date('Y-m-d',$date).' 23:59:59')."")->count('id');//统计关注的人数
    		$wx_active[] = M('online')->where("origin=1 and uid!=0 and add_time between ".strtotime(date('Y-m-d',$date).' 00:00:00')." and ".strtotime(date('Y-m-d',$date).' 23:59:59')."")->count('id');
    		$uv[]        = M('online')->where("origin=4 and uid=0 and add_time between ".strtotime(date('Y-m-d',$date).' 00:00:00')." and ".strtotime(date('Y-m-d',$date).' 23:59:59')."")->count('id');;
    		$appuv[]     = M('online')->where("origin in(2,3) and uid=0 and add_time between ".strtotime(date('Y-m-d',$date).' 00:00:00')." and ".strtotime(date('Y-m-d',$date).' 23:59:59')."")->count('id');
    		$app_active[]= M('online')->where("origin in(2,3) and uid!=0 and add_time between ".strtotime(date('Y-m-d',$date).' 00:00:00')." and ".strtotime(date('Y-m-d',$date).' 23:59:59')."")->count('id');
    		
    		$arr_hours[] = date("m-d",$date);
    		
    	}
        //print_r($hours); print_r($count);die();
     
    	$this->assign('hours', implode(',',$days));//时间
    	$this->assign('count', implode(',',$count));//新注册人数
    	$this->assign('subscribe', implode(',',$subscribe));//新增粉丝人数
    	$this->assign('wx_active', implode(',',$wx_active));//微信UV
    	$this->assign('uv', implode(',',$uv));//PV——UV
    	$this->assign('appuv', implode(',',$appuv));//app
    	$this->assign('app_active', implode(',',$app_active));//app活跃人数
    	
    	$this->assign('arr_hours', $arr_hours);//时间
    	$this->assign('arr_count', $count);//新注册人数
    	$this->assign('arr_subscribe', $subscribe);//新增粉丝人数
    	$this->assign('arr_wx_active', $wx_active);//微信UV
    	$this->assign('arr_uv', $uv);//PV——UV
    	$this->assign('arr_appuv', $appuv);//app
    	$this->assign('arr_app_active', $app_active);//app活跃人数
    
    	$this->display();
    }
    /*
     * 经纪人开拓月报
    *
    * @param  $moths   时间显示
    * @param  $count  注册人数
    * @param  $subscribe   关注人数
    * @param  $wx_uv  活跃人数
    * @param  $pc_uv   网站uv
    * @param  $appuv  App中每天和服务器发生交互的用户数
    * @param  $app_active   app活跃人数(App每天和服务器发生交互的注册用户数)
    * @return bool
    */
    public function agent_develop_month() {
    	
    	$moths=array();//时间
    	$count=array();//注册的人数 
    	$subscribe=array();//关注人数
    	$wx_active = array();//活跃人数
    	$uv=array();//网站UV
    	$appuv=array();//App中每天和服务器发生交互的用户数
    	$app_active = array();//app活跃人数(App每天和服务器发生交互的注册用户数)
    	
    	$arr_moths = array();//表格的时间部分
    	 
    	
    	$tmp_date=date("Ym",time());
    	//切割出年份
    	$tmp_year=substr($tmp_date,0,4);
    	//切割出月份
    	$tmp_mon =substr($tmp_date,4,2);

    	for($i=0;$i<6;$i++){
    		$firstday = mktime(0,0,0,$tmp_mon-(5-$i),1,$tmp_year);
    		$mktimes = date('Y-m-01', $firstday);
    		$lastday = strtotime("$mktimes +1 month -1 day");
    		
    		$moths[] = "'".date('m',$firstday)."'";
    		$count[] = M('user')->where(" reg_time between ".strtotime(date('Y-m-d',$firstday).' 00:00:00')." and ".strtotime(date('Y-m-d',$lastday).' 23:59:59')."")->count('id');//统计注册的人数
    		$subscribe[] = M('weixin_user')->where(" add_time between ".strtotime(date('Y-m-d',$firstday).' 00:00:00')." and ".strtotime(date('Y-m-d',$lastday).' 23:59:59')."")->count('id');//统计关注的人数
    		$wx_active[] = M('online')->where("origin=1 and uid!=0 and add_time between ".strtotime(date('Y-m-d',$firstday).' 00:00:00')." and ".strtotime(date('Y-m-d',$lastday).' 23:59:59')."")->count('id');
    		$uv[]        = M('online')->where("origin=4 and uid=0 and add_time between ".strtotime(date('Y-m-d',$firstday).' 00:00:00')." and ".strtotime(date('Y-m-d',$lastday).' 23:59:59')."")->count('id');;
    		$appuv[]     = M('online')->where("origin in(2,3) and uid=0 and add_time between ".strtotime(date('Y-m-d',$firstday).' 00:00:00')." and ".strtotime(date('Y-m-d',$lastday).' 23:59:59')."")->count('id');
    		$app_active[]= M('online')->where("origin in(2,3) and uid!=0 and add_time between ".strtotime(date('Y-m-d',$firstday).' 00:00:00')." and ".strtotime(date('Y-m-d',$lastday).' 23:59:59')."")->count('id');
    		
    		$arr_moths[] = date('Y-m',$firstday);
    		  
    	}
    	 
    	$this->assign('moths', implode(',',$moths));//时间
    	$this->assign('count', implode(',',$count));//新注册人数
    	$this->assign('subscribe', implode(',',$subscribe));//新增粉丝人数
    	$this->assign('wx_active', implode(',',$wx_active));//微信UV
    	$this->assign('uv', implode(',',$uv));//PV——UV
    	$this->assign('appuv', implode(',',$appuv));//app
    	$this->assign('app_active', implode(',',$app_active));//app活跃人数
    	
    	$this->assign('arr_moths', $arr_moths);//时间
    	$this->assign('arr_count', $count);//新注册人数
    	$this->assign('arr_subscribe', $subscribe);//新增粉丝人数
    	$this->assign('arr_wx_active', $wx_active);//微信UV
    	$this->assign('arr_uv', $uv);//PV——UV
    	$this->assign('arr_appuv', $appuv);//app
    	$this->assign('arr_app_active', $app_active);//app活跃人数
    	
    	$this->display();
    }
    
    /*
     * 经纪人活跃日报
    *
    * @param  $days   时间显示
    * @param  $active_users  活跃用户数：每个周期和房品汇发生数据交互（登陆，浏览，带看，报备。。。。）的注册用户数
    * @param  $effective   有效活跃数：每个周期（报备，带看）的注册用户数(所有终端)
    * @param  $total_users  总用户数：每个周期的最后时间点的注册用户总数
    * @param  $active_ratio   活跃比：（活跃用户数/总用户数）X100%
    * @param  $effective_active_ratio  有效活跃比：（有效活跃数/总用户数）X100%
    * @return bool
    */
    
    public function agent_active_daily() {
    	$days=array();//时间
    	$active_users =array();//活跃用户数：每个周期和房品汇发生数据交互（登陆，浏览，带看，报备。。。。）的注册用户数
    	$effective = array(); //有效活跃数：每个周期（报备，带看）的注册用户数(所有终端)
    	$total_users = array(); //总用户数：每个周期的最后时间点的注册用户总数
    	$active_ratio = array(); //活跃比：（活跃用户数/总用户数）X100%
    	$effective_active_ratio=array();//有效活跃比：（有效活跃数/总用户数）X100%
    	
    	$arr_hours = array();//表格的时间
    	 
    	$j=0;
    	for($i=0;$i<30;$i++){
    		$j=30-$i;
    		$date   = strtotime("-$j day");
    		
    		$days[$j] = "'".date("m-d",$date)."'";
    		$effective[$j] = M('myclient_property')->where("add_time between ".strtotime(date('Y-m-d',$date).' 00:00:00')." and ".strtotime(date('Y-m-d',$date).' 23:59:59')."")->count('id');//(报备，带看）的注册用户数
    		$total_users[$j] = M('user')->where("reg_time <".strtotime(date('Y-m-d',$date).' 23:59:59')."")->count('id');//最后时间点的注册用户总数
    		$active_users[$j] = $effective[$j] + M('online')->where("uid!=0 and add_time between ".strtotime(date('Y-m-d',$date).' 00:00:00')." and ".strtotime(date('Y-m-d',$date).' 23:59:59')."")->count('id');//活跃用户数
    		$active_ratio[$j] = number_format(($active_users[$j]/$total_users[$j]) * 100,2);//（活跃用户数/总用户数）X100%
    		$effective_active_ratio[$j]   =  number_format(($effective[$j]/$total_users[$j]) * 100,2);//有效活跃比：（有效活跃数/总用户数）X100%
    		
    		$arr_hours[]= date("m-d",$date);
    		
    		$j++;
    		
    	}
    	//print_r($hours); print_r($total_users);die();
    	 
    	$this->assign('hours', implode(',',$days));//时间
    	$this->assign('effective', implode(',',$effective));//有效活跃数
    	$this->assign('total_users', implode(',',$total_users));//总用户数
    	$this->assign('active_users', implode(',',$active_users));//活跃用户数
    	$this->assign('active_ratio', implode(',',$active_ratio));//（活跃用户数/总用户数）X100%
    	$this->assign('effective_active_ratio', implode(',',$effective_active_ratio));//有效活跃比：（有效活跃数/总用户数）X100%
    	
    	$this->assign('arr_hours', $arr_hours);//时间
    	$this->assign('arr_effective', $effective);//有效活跃数
    	$this->assign('arr_total_users', $total_users);//总用户数
    	$this->assign('arr_active_users', $active_users);//活跃用户数
    	$this->assign('arr_active_ratio', $active_ratio);//（活跃用户数/总用户数）X100%
    	$this->assign('arr_effective_active_ratio', $effective_active_ratio);//有效活跃比：（有效活跃数/总用户数）X100%
    	
    	$this->display();
    }
    
    /*
     * 经纪人活跃月报
    *
    * @param  $days   时间显示
    * @param  $active_users  活跃用户数：每个周期和房品汇发生数据交互（登陆，浏览，带看，报备。。。。）的注册用户数
    * @param  $effective   有效活跃数：每个周期（报备，带看）的注册用户数(所有终端)
    * @param  $total_users  总用户数：每个周期的最后时间点的注册用户总数
    * @param  $active_ratio   活跃比：（活跃用户数/总用户数）X100%
    * @param  $effective_active_ratio  有效活跃比：（有效活跃数/总用户数）X100%
    * @return bool
    */
    public function agent_active_month() {
    
    	$tmp_date=date("Ym",time());
    	//切割出年份
    	$tmp_year=substr($tmp_date,0,4);
    	//切割出月份
    	$tmp_mon =substr($tmp_date,4,2);
    	
    	$moths=array();//时间
    	$active_users =array();//活跃用户数：每个周期和房品汇发生数据交互（登陆，浏览，带看，报备。。。。）的注册用户数
    	$effective = array(); //有效活跃数：每个周期（报备，带看）的注册用户数(所有终端)
    	$total_users = array(); //总用户数：每个周期的最后时间点的注册用户总数
    	$active_ratio = array(); //活跃比：（活跃用户数/总用户数）X100%
    	$effective_active_ratio=array();//有效活跃比：（有效活跃数/总用户数）X100%
    	
    	$arr_moths = array();//表格的时间
    	 
    	$j=0;
    	for($i=0;$i<6;$i++){
    		
    		$mktimes = mktime(0,0,0,$tmp_mon-(5-$i),1,$tmp_year);
    		$firstday = date('Y-m-01', mktime(0,0,0,$tmp_mon-(5-$i),1,$tmp_year));
    		$lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
    		
    		$moths[$j] = "'".date('m',$mktimes)."'";
    		$effective[$j] = M('myclient_property')->where("add_time between ".strtotime($firstday.' 00:00:00')." and ".strtotime($lastday.' 23:59:59')."")->count('id');//(报备，带看）的注册用户数
    		$total_users[$j] = M('user')->where("reg_time <".strtotime($lastday.' 23:59:59')."")->count('id');//最后时间点的注册用户总数
    		$active_users[$j] = $effective[$j] + M('online')->where("uid!=0 and  add_time between ".strtotime($firstday.' 00:00:00')." and ".strtotime($lastday.' 23:59:59')."")->count('id');//活跃用户数
    		$active_ratio[$j] = number_format(($active_users[$j]/$total_users[$j]) * 100,2);//（活跃用户数/总用户数）X100%
    		$effective_active_ratio[$j]   =  number_format(($effective[$j]/$total_users[$j]) * 100,2);//有效活跃比：（有效活跃数/总用户数）X100%
    		   
    		$arr_moths[] = date('Y-m',$mktimes);
    		
    		$j++;
    		   
    	}
    	 
    	$this->assign('moths', implode(',',$moths));//时间
    	$this->assign('effective', implode(',',$effective));//有效活跃数
    	$this->assign('total_users', implode(',',$total_users));//总用户数
    	$this->assign('active_users', implode(',',$active_users));//活跃用户数
    	$this->assign('active_ratio', implode(',',$active_ratio));//（活跃用户数/总用户数）X100%
    	$this->assign('effective_active_ratio', implode(',',$effective_active_ratio));//有效活跃比：（有效活跃数/总用户数）X100%
    	
    	$this->assign('arr_moths', $arr_moths);//时间
    	$this->assign('arr_effective', $effective);//有效活跃数
    	$this->assign('arr_total_users', $total_users);//总用户数
    	$this->assign('arr_active_users', $active_users);//活跃用户数
    	$this->assign('arr_active_ratio', $active_ratio);//（活跃用户数/总用户数）X100%
    	$this->assign('arr_effective_active_ratio', $effective_active_ratio);//有效活跃比：（有效活跃数/总用户数）X100%
    	
    	$this->display();
    }
    /*
     * 经纪人活跃周报
    *
    * @param  $days   时间显示
    * @param  $active_users  活跃用户数：每个周期和房品汇发生数据交互（登陆，浏览，带看，报备。。。。）的注册用户数
    * @param  $effective   有效活跃数：每个周期（报备，带看）的注册用户数(所有终端)
    * @param  $total_users  总用户数：每个周期的最后时间点的注册用户总数
    * @param  $active_ratio   活跃比：（活跃用户数/总用户数）X100%
    * @param  $effective_active_ratio  有效活跃比：（有效活跃数/总用户数）X100%
    * @return bool
    */
    public function agent_active_weekly() {
    
         //按照周统计
    	$weeks=array();//时间
    	$active_users =array();//活跃用户数：每个周期和房品汇发生数据交互（登陆，浏览，带看，报备。。。。）的注册用户数
    	$effective = array(); //有效活跃数：每个周期（报备，带看）的注册用户数(所有终端)
    	$total_users = array(); //总用户数：每个周期的最后时间点的注册用户总数
    	$active_ratio = array(); //活跃比：（活跃用户数/总用户数）X100%
    	$effective_active_ratio=array();//有效活跃比：（有效活跃数/总用户数）X100%
    	
    	$arr_weeks = array();//表格时间部分
    	 
    	$j=0;
    	for($i=0;$i<6;$i++){
    		$k = (5-$i)*7;
    		$timestamp = strtotime("-$k day");;
    		$start = date('Y-m-d',$timestamp-(date('N',$timestamp)-1)*86400);//获取当前时间的周一日期
    		$last = date('Y-m-d',$timestamp + (7-date('N',$timestamp))*86400);//获取当前时间的周末日期
    		
    		$weeks[] = "'".$start." 到 ".$last."'";
    		$effective[$j] = M('myclient_property')->where("add_time between ".strtotime($start.' 00:00:00')." and ".strtotime($last.' 23:59:59')."")->count('id');//(报备，带看）的注册用户数
    		$total_users[$j] = M('user')->where("reg_time <".strtotime($last.' 23:59:59')."")->count('id');//最后时间点的注册用户总数
    		$active_users[$j] = $effective[$j] + M('online')->where("uid!=0 and  add_time between ".strtotime($start.' 00:00:00')." and ".strtotime($last.' 23:59:59')."")->count('id');//活跃用户数
    		$active_ratio[$j] = number_format(($active_users[$j]/$total_users[$j]) * 100,2);//（活跃用户数/总用户数）X100%
    		$effective_active_ratio[$j]   =  number_format(($effective[$j]/$total_users[$j]) * 100,2);//有效活跃比：（有效活跃数/总用户数）X100%
    		     	
    		$arr_weeks[] = $start." 到 ".$last;
    		
    		$j++;

    	}
    	
    	$this->assign('weeks', implode(',',$weeks));//时间
    	$this->assign('effective', implode(',',$effective));//有效活跃数
    	$this->assign('total_users', implode(',',$total_users));//总用户数
    	$this->assign('active_users', implode(',',$active_users));//活跃用户数
    	$this->assign('active_ratio', implode(',',$active_ratio));//（活跃用户数/总用户数）X100%
    	$this->assign('effective_active_ratio', implode(',',$effective_active_ratio));//有效活跃比：（有效活跃数/总用户数）X100%
    	
    	$this->assign('arr_weeks', $arr_weeks);//时间
    	$this->assign('arr_effective', $effective);//有效活跃数
    	$this->assign('arr_total_users', $total_users);//总用户数
    	$this->assign('arr_active_users', $active_users);//活跃用户数
    	$this->assign('arr_active_ratio', $active_ratio);//（活跃用户数/总用户数）X100%
    	$this->assign('arr_effective_active_ratio', $effective_active_ratio);//有效活跃比：（有效活跃数/总用户数）X100%
    	
        $this->display();
    }

    public function reservationIndex()
    {
        //
        $yearStart = '2015';
        $nowYear = date('Y');
        $year = array();
        $j = 0;
        for($i = $yearStart; $i <= $nowYear; $i++)
        {
            $year[$j] = $i;
            $j++;
        }
        $month = array();
        for($i=0; $i < 12; $i++)
        {
            $month[$i] = $i+1;
        }
        $this->assign('month', $month);
        $this->assign('year', $year);
        $this->display();
    }

    public function reservation()
    {
        $getYear = $this->_get('year', 'intval');
        $getMonth = $this->_get('month', 'intval');
        $property = $this->_get('property', 'trim');
        $where = 1;
        if(trim($property))
        {
            $this->assign('property', $property);
            $pid = D('property')->findField('title = "'.$property.'"','id');
            if(!$pid) $this->error('没有该楼盘的预约数据！');
            M('join_reservation','fph_',C('DB_member'));
            $rid = D('join_reservation')->getEnableByPid($pid,'id');
            if(!$rid) echo '楼盘暂无预约数据！';
            $where .=' and pid = '.$pid;
        }
        //$id = 367;
        $months = $getYear .'-'.$getMonth;  // search month  ex: 2015-8
        if(!$getYear && !$getMonth)
        {
            $months = date('Y-m',time());
            $getMonth = (int)date('m',time());
        }
        $end = date('t',strtotime($months)); // 获取指定月份天数
        $monthBefore = strtotime($months . '-01');
        $monthEnd = strtotime($months . '-'. $end) + 86400;
        $where .= ' and order_time_start >='.$monthBefore .' and order_time_end <= '. $monthEnd;
        M('join_reservation','fph_',C('DB_member'));
        $list = D('join_reservation')->getList($where, '*', false);
        $analysis = $this->buildAnalysisData($list, $end, $months);
        $categories = i_array_column($analysis, 'date');
        foreach($categories as $k => $v)
        {
            $categories[$k] = "'$v'";
        }
        $categories = implode(',', $categories);
        $count = implode(',', i_array_column($analysis, 'count'));
        $arrived = implode(',', i_array_column($analysis, 'arrived'));
        $arrivedBefore = implode(',', i_array_column($analysis, 'arrivedBefore'));
        $arrivedLate = implode(',', i_array_column($analysis, 'arrivedLate'));

        //
        $yearStart = '2015';
        $nowYear = date('Y');
        $year = array();
        $j = 0;
        for($i = $yearStart; $i <= $nowYear; $i++)
        {
            $year[$j] = $i;
            $j++;
        }
        $month = array();
        for($i=0; $i < 12; $i++)
        {
            $month[$i] = $i+1;
        }

        $this->assign('categories', $categories);
        $this->assign('count', $count);
        $this->assign('arrived', $arrived);
        $this->assign('arrivedBefore', $arrivedBefore);
        $this->assign('arrivedLate', $arrivedLate);
        $this->assign('year', $year);
        $this->assign('month', $month);
        $this->assign('getYear', $getYear);
        $this->assign('getMonth', $getMonth);
        $this->assign('memberNum',array_sum(i_array_column($analysis, 'count')));
        $this->display();
    }

    private function buildAnalysisData($list, $end, $months)
    {
        $analysis = array();
        for($begin = 1; $begin <= $end; $begin++)
        {
            $analysis[$begin]['date'] =date('m/d', strtotime($months . '-'. $begin));
            $analysis[$begin]['count'] = 0;
            $analysis[$begin]['arrived'] = 0;
            $analysis[$begin]['arrivedBefore'] = 0;
            $analysis[$begin]['arrivedLate'] = 0;
            foreach($list as $key => $val)
            {
                $todayStart = strtotime($months . '-'. $begin);
                $todayEnd = $todayStart  + 86400;
                if(($val['add_time'] >= $todayStart) && ($val['add_time'] < $todayEnd))
                {
                    $analysis[$begin]['count']++;
                }
                if(($val['arrived_time'] >= $todayStart) && ($val['arrived_time'] < $todayEnd) && $val['arrived_time'] <= $val['order_time_end'] && $val['arrived_time'] >= $val['order_time_start'])
                {
                    $analysis[$begin]['arrived']++;
                }
                if(($val['arrived_time'] >= $todayStart) && ($val['arrived_time'] < $todayEnd) && $val['arrived_time'] < $val['order_time_start'])
                {
                    $analysis[$begin]['arrivedBefore']++;
                }
                if(($val['arrived_time'] >= $todayStart) && ($val['arrived_time'] < $todayEnd) && $val['arrived_time'] > $val['order_time_end'])
                {
                    $analysis[$begin]['arrivedLate']++;
                }
            }
        }
        return $analysis;
    }

    public function export()
    {
        $id = $this->_get('pid', 'intval');
        $getYear = $this->_get('exYear', 'intval');
        $getMonth = $this->_get('exMonth', 'intval');
        $where ='pid = '.$id;
        $months = $getYear .'-'.$getMonth;  // search month  ex: 2015-8
        if(!$getYear || !$getMonth)
        {
            $this->error('请选择要导出的数据月份');
            exit;
        }
        $end = date('t',strtotime($months)); // 获取指定月份天数
        $monthBefore = strtotime($months . '-01');
        $monthEnd = strtotime($months . '-'. $end) + 86400;
        $where .= ' and order_time_start >='.$monthBefore .' and order_time_end <= '. $monthEnd;
        M('join_reservation','fph_',C('DB_member'));
        $list = D('join_reservation')->getList($where, '*', false);
        $analysis = $this->buildAnalysisData($list, $end, $months);
        $this->analysisExcel($analysis, $getYear, $getMonth);
    }

    private function analysisExcel($analysis, $getYear, $getMonth)
    {
        Vendor("Classes.PHPExcel");
        Vendor("Classes.PHPExcel.php");
        //创建处理对象实例
        $objPhpExcel=new PHPExcel();
        $objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
        //设置表格的宽度  手动
        $objPhpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPhpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
        $objPhpExcel->getActiveSheet()->getColumnDimension('N')->setWidth(45);
        //设置标题
        $rowVal = array(0=>'日期',1=>'到访总数', 2=>'到访（准点）', 3=>'到访（提前）', 4=>'到访（推迟）');
        foreach ($rowVal as $k=>$r){
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)
                ->getFont()->setBold(true);//字体加粗
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
            getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
            $objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
        }
        $objPhpExcel->getActiveSheet()->setCellValue('A1', '日期')->getColumnDimension('A')->setWidth(12);
        $objPhpExcel->getActiveSheet()->setCellValue('B1', '到访总数')->getColumnDimension('B')->setWidth(12);
        $objPhpExcel->getActiveSheet()->setCellValue('C1', '到访（准点）')->getColumnDimension('C')->setWidth(12);
        $objPhpExcel->getActiveSheet()->setCellValue('D1', '到访（提前）')->getColumnDimension('D')->setWidth(12);
        $objPhpExcel->getActiveSheet()->setCellValue('E1', '到访（推迟）')->getColumnDimension('E')->setWidth(12);
        //设置当前的sheet索引 用于后续内容操作
        $objPhpExcel->setActiveSheetIndex(0);
        $objActSheet=$objPhpExcel->getActiveSheet();
        //设置当前活动的sheet的名称
        $title=$getYear.'年'. $getMonth .'月份预约到访数据';
        $objActSheet->setTitle($title);
        //insert data start
        foreach($analysis as $k=>$v){
            $num=$k+1;
            $objPhpExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$num, $v['date'])
                ->setCellValue('B'.$num, $v['count'])
                ->setCellValue('C'.$num, $v['arrived'])
                ->setCellValue('D'.$num, $v['arrivedBefore'])
                ->setCellValue('E'.$num, $v['arrivedLate']);
        }
        // insert data end
        $name = date('Y-m-d');//设置文件名
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Transfer-Encoding:utf-8");
        header("Pragma: no-cache");
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$title.'_'.urlencode($name).'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPhpExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    public function trip(){
		$time_start 		= $this->_get('time_start','trim');
		$time_end 			= $this->_get('time_end','trim');
		$select_city_id 	= $this->_get('city_id','intval');
		$select_city_spid 	= '';
		$where = "1=1";
		if($time_start > $time_end){
			$this->error('开始时间不能大于结束时间');
			exit;
		}
		if($time_start){
			$where .= " and time>=".strtotime($time_start);
		}
		if($time_end){
			$where .= " and time<=".strtotime($time_end);
		}
		if(!empty($select_city_id)){
			$select_city_spid = M('city')->where('id ='.$select_city_id)->getField('spid');
			if($select_city_spid != 0){
				$select_city_spid = $select_city_spid.$select_city_id;
			}else{
				$select_city_spid = $select_city_id;
			}
			$city = explode('|',$select_city_spid);
			if($city[0]){
				$where .= " and prov_id=$city[0]";
			}
			if($city[1]){
				$where .= " and city_id=$city[1]";
			}
			if($city[2]){
				$where .= " and area_id=$city[2]";
			}
		}
		M('trip_stat','fph_',C('DB_log'));
		$count = M('trip_stat')->where($where)->count();
		$p = new Page($count, 20);
		$page = $p->show();
		$list = M('trip_stat')->limit($p->firstRow.','.$p->listRows)->where($where)->select();
		$property = M('property','fph_',C('DB_fangpinhui'))->field('id,title')->select();
		foreach($property as $val){
			$propertyData[$val['id']] = $val['title'];
		}
		M('property','fph_',C('DB_fangpinhui'));
		$property = M('property')->field('id,title')->select();
		foreach($property as $val){
			$propertyData[$val['id']] = $val['title'];
		}
		$cityList = M('city')->field('id,name')->select();
		foreach($cityList as $val){
			$cityData[$val['id']] = $val['name'];
		}
		foreach($list as $k => $val){
			$list[$k]['title'] 			= $propertyData[$val['pid']];
			$list[$k]['city_name'] 		= $cityData[$val['city_id']];
			$list[$k]['area_name'] 		= $cityData[$val['area_id']];
			$list[$k]['eta_total'] 		= $val['trip']*$val['total'];
		}
		$this->assign('search', array(
			'time_start' 	=> $time_start,
			'time_end' 		=> $time_end,
			'city_id' 		=> $select_city_id,
			'city_spid' 	=> $select_city_spid,
		));
		$this->assign('list',$list);
		$this->assign('page',$page);
		$this->assign('p',$p);
		$this->display();
	}

	public function ajax_city() {
		$id = $this->_get('id', 'intval');
		$return = M('city')->field('id,name')->where(array('pid'=>$id))->select();
		if ($return) {
			$this->ajaxReturn(1, L('operation_success'), $return);
		} else {
			$this->ajaxReturn(0, L('operation_failure'));
		}
	}

	public function tripExport(){
		$time_start 		= $this->_get('time_start','trim');
		$time_end 			= $this->_get('time_end','trim');
		$select_city_id 	= $this->_get('city_id','intval');
		$select_city_spid 	= '';
		$where = "1=1";
		if($time_start){
			$where .= " and time>=".strtotime($time_start);
		}
		if($time_end){
			$where .= " and time<=".strtotime($time_end);
		}
		if(!empty($select_city_id)){
			$select_city_spid = M('city')->where('id ='.$select_city_id)->getField('spid');
			if($select_city_spid != 0){
				$select_city_spid = $select_city_spid.$select_city_id;
			}else{
				$select_city_spid = $select_city_id;
			}
			$city = explode('|',$select_city_spid);
			if($city[0]){
				$where .= " and prov_id=$city[0]";
			}
			if($city[1]){
				$where .= " and city_id=$city[1]";
			}
			if($city[2]){
				$where .= " and area_id=$city[2]";
			}
		}
		M('trip_stat','fph_',C('DB_log'));

		$list = M('trip_stat')->where($where)->select();
		$property = M('property','fph_',C('DB_fangpinhui'))->field('id,title')->select();
		foreach($property as $val){
			$propertyData[$val['id']] = $val['title'];
		}
		M('property','fph_',C('DB_fangpinhui'));
		$property = M('property')->field('id,title')->select();
		foreach($property as $val){
			$propertyData[$val['id']] = $val['title'];
		}
		$cityList = M('city')->field('id,name')->select();
		foreach($cityList as $val){
			$cityData[$val['id']] = $val['name'];
		}
		foreach($list as $k => $val){
			$list[$k]['title'] 			= $propertyData[$val['pid']];
			$list[$k]['city_name'] 		= $cityData[$val['city_id']];
			$list[$k]['area_name'] 		= $cityData[$val['area_id']];
			$list[$k]['eta_total'] 		= $val['trip']*$val['total'];
		}
		$this->tripisExcel($list);
	}

	private function tripisExcel($analysis)
	{
		Vendor("Classes.PHPExcel");
		Vendor("Classes.PHPExcel.php");
		//创建处理对象实例
		$objPhpExcel=new PHPExcel();
		$objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
		//设置表格的宽度  手动
		$objPhpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
		$objPhpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
		$objPhpExcel->getActiveSheet()->getColumnDimension('N')->setWidth(45);
		//设置标题
		$rowVal = array(0=>'楼盘',1=>'区域', 2=>'城市', 3=>'路费', 4=>'总份数', 5=>'预计总金额', 6=>'到访客户人数', 7=>'实际领取用户数', 8=>'已领取金额', 9=>'时间');
		foreach ($rowVal as $k=>$r){
			$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)
				->getFont()->setBold(true);//字体加粗
			$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
			getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
			$objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
		}
		$objPhpExcel->getActiveSheet()->setCellValue('A1', '楼盘')->getColumnDimension('A')->setWidth(30);
		$objPhpExcel->getActiveSheet()->setCellValue('B1', '区域')->getColumnDimension('B')->setWidth(12);
		$objPhpExcel->getActiveSheet()->setCellValue('C1', '城市')->getColumnDimension('C')->setWidth(12);
		$objPhpExcel->getActiveSheet()->setCellValue('D1', '路费')->getColumnDimension('D')->setWidth(12);
		$objPhpExcel->getActiveSheet()->setCellValue('E1', '总份数')->getColumnDimension('E')->setWidth(12);
		$objPhpExcel->getActiveSheet()->setCellValue('F1', '预计总金额')->getColumnDimension('F')->setWidth(20);
		$objPhpExcel->getActiveSheet()->setCellValue('G1', '到访客户人数')->getColumnDimension('G')->setWidth(20);
		$objPhpExcel->getActiveSheet()->setCellValue('H1', '实际领取用户数')->getColumnDimension('H')->setWidth(20);
		$objPhpExcel->getActiveSheet()->setCellValue('I1', '已领取金额')->getColumnDimension('I')->setWidth(12);
		$objPhpExcel->getActiveSheet()->setCellValue('J1', '时间')->getColumnDimension('J')->setWidth(12);
		//设置当前的sheet索引 用于后续内容操作
		$objPhpExcel->setActiveSheetIndex(0);
		$objActSheet=$objPhpExcel->getActiveSheet();
		//设置当前活动的sheet的名称
		$title='路费报表数据';
		$objActSheet->setTitle($title);
		//insert data start
		//print_r($analysis);exit;
		foreach($analysis as $k=>$v){
			$num=$k+2;
			$objPhpExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$num, $v['title'])
				->setCellValue('B'.$num, $v['area_name'])
				->setCellValue('C'.$num, $v['city_name'])
				->setCellValue('D'.$num, $v['trip'])
				->setCellValue('E'.$num, $v['total'])
				->setCellValue('F'.$num, $v['eta_total'])
				->setCellValue('G'.$num, $v['visit'])
				->setCellValue('H'.$num, $v['actual'])
				->setCellValue('I'.$num, $v['amount'])
				->setCellValue('J'.$num, date('Y-m-d',$v['time']));
		}
		// insert data end
		$name = date('Y-m-d');//设置文件名
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Transfer-Encoding:utf-8");
		header("Pragma: no-cache");
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$title.'_'.urlencode($name).'.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPhpExcel, 'Excel5');
		$objWriter->save('php://output');
	}
}