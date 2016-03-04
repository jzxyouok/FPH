<?php

// +----------------------------------------------------------------------
// | Descriptions:本页面作为 app信息推送接口
// +----------------------------------------------------------------------
// | State : 不可以删除此页面，如要删除，前联系作者。
// +----------------------------------------------------------------------
// | Date: 2014-12-09
// +----------------------------------------------------------------------
// | Author: H.J.H
// +----------------------------------------------------------------------

class app_pushModel extends RelationModel
{
	//定义常量不可更改
	const APP_KEY_IOS = '54365c9afd98c52843047b2c';
	const APP_MASTER_SECRET_IOS = 'dgjix0glamxbzxzrtfv2l7s7zqulnrgt';
	
	const APP_KEY_ANDROID= '54373b9efd98c5ac390125ec';
	const APP_MASTER_SECRET_ANDROID = '0pjknuyuvwxvegfzadakonaidsv26ico';

	const APPCASE_KEY_IOS = '54be012cfd98c5be5a0004c6';
	const APPCASE_MASTER_SECRET_IOS = 'lxhb8lkhjlmu5j917hsgv6c9deeeqvrc';
	
	const APPCASE_KEY_ANDROID= '54a266b3fd98c58b770006a8';
	const APPCASE_MASTER_SECRET_ANDROID = 'jugahmgqiuxcr8u55o7wxrvlpoexwcmr';
	
	public function _initialize() {
		parent::_initialize();
		Vendor("Push.Demo");
		
	}
	
	
    //自动完成
    protected $_auto = array(
        array('add_time', 'time', 1, 'function'),
    );
    //自动验证
    protected $_validate = array(
        array('title', 'require', '{%article_title_empty}'),
    );
	
    public function addtime() {
        return date("Y-m-d H:i:s",time());
    }
    

    /*
     * 组播接口统一调用模型   
    *  功能描述：实现楼盘，文章，活动的推送消息
    * @param string  $alias_uid       经纪人uid
    * @param string  $device_ type     设备类型  1:ios 2:android
    * @param string  $title          标题
    * @param string  $text            内容
    * @return bool
    */
    
    public function groupcast($city_id1,$city_id2,$title,$info,$cate_id,$pid,$cast){
    	     //判断跳转的activity
    	    if($cate_id ==1 )
    	    	$activityName = 'BuilingDetailsActivity';//楼盘
    	    if($cate_id ==2 )
    	    	$activityName = 'BuildingDetailsHuodong';//活动
    	    if($cate_id ==3 )
    	    	$activityName = 'PkbDetailsActivity';//文章

    	    
    		$demo_andriod = new Demo(self::APP_KEY_ANDROID, self::APP_MASTER_SECRET_ANDROID);
    		$demo_ios = new Demo(self::APP_KEY_IOS, self::APP_MASTER_SECRET_IOS);
    		//if($cast==1){//组播
    			$demo_andriod->sendAndroidGroupcast($city_id1,$city_id2,$title,$info,$activityName,$pid,$after_open='go_custom');
    		    $demo_ios->sendIOSGroupcast($city_id1,$city_id2,$title,$info,$cate_id,$pid,$after_open='go_activity');
    		//}else{ //广播
    	  //  	$demo_andriod->sendAndroidBroadcast($city_id1,$city_id2,$title,$info,$activityName,$pid);
    	 //       $demo_ios->sendIOSBroadcast($city_id1,$city_id2,$title,$info,$cate_id,$pid);
    	//	}
    		
    }
    
    /*
     * 定制广播,给经纪人推送跟踪报备消息
    *
    * @param  $alias_uid  经纪人的id
    * @param  $alert_info  跟踪的信息
    * @param  $with_look   带看类型
    * @param  $title 状态提示
    * @return bool
    */
    public function customizedCast($alias_uid,$alert_info,$title,$with_look,$mpid){
    	
    	    $activityName = 'PersonalMyClientDetailsActivity';      //android
    	    $activity     = 'com.example.fangpinhui.view.MainTabHostActivity';   //android
    
    		$demo_andriod = new Demo(self::APP_KEY_ANDROID, self::APP_MASTER_SECRET_ANDROID);
    		$demo_andriod->sendAndroidCustomizedcast($alias_uid,$alert_info,$title,$with_look,$activityName,$activity,$mpid,'',$after_open="go_custom");
    
    	    $demo_ios = new Demo(self::APP_KEY_IOS, self::APP_MASTER_SECRET_IOS);
    	    $demo_ios->sendIOSCustomizedcast($alias_uid,$alert_info,$title,$with_look,$mpid,$data=0);
    
    }
    
    
    
    /*
     * 报备成功后推送消息给案场的app
    *
    * @param string $info 和发送给案场人员的短息是一样的
    * @param  $case_id   案场的负责人的id（前提必须选择好权限，不要忘记勾选）
    * @param  $reporte_id   生成的一个报备的id
    * @param  $with_look   带看类型
    * @param  $data  array 数组（$data['muclient_name']：客户名字(男或女)；$data['muclient_mobile']：客户电话；$data['agent_name']：经纪人名字(男或女)；$data['agent_mobile']：经纪人电话）
    * @return bool
    */
    public function push_case($info,$case_id,$reporte_id,$with_look,$data){  
    	//判断跳转的activity
    	$activityName = 'StatusDetailsActivity';      //android
    	$activity     ='com.example.fphcases.view.MainActivity';   //android
    		
    	$demo_andriod = new Demo(self::APPCASE_KEY_ANDROID, self::APPCASE_MASTER_SECRET_ANDROID);
    	
     	$demo_ios = new Demo(self::APPCASE_KEY_IOS, self::APPCASE_MASTER_SECRET_IOS);
	
    	$demo_andriod->sendAndroidCustomizedcast($case_id,'您有一条新的报备信息','房品汇 ',$with_look,$activityName,$activity,$reporte_id,$data,$after_open="go_custom");
    	$demo_ios->sendIOSCustomizedcast($case_id,'您有一条新的报备信息','房品汇 ',$with_look,$reporte_id,$data);
    }
     	
}