<?php
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidBroadcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidFilecast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidGroupcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidUnicast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidCustomizedcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSBroadcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSFilecast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSGroupcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSUnicast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSCustomizedcast.php');

class Demo {
	protected $appkey           = NULL; 
	protected $masterSecret     = NULL;
	protected $timestamp        = NULL;
	protected $validation_token = NULL;

	function __construct($key, $secret) {
		$this->appkey = $key;
		$this->masterSecret = $secret;
		$this->timestamp = strval(time());
		$this->validation_token = md5(strtolower($this->appkey) . strtolower($this->masterSecret) . strtolower($this->timestamp));
	}
	//广播
	function sendAndroidBroadcast($city_id1,$city_id2,$title,$info,$activityName,$pid) {  
		try {
			$brocast = new AndroidBroadcast();
			$brocast->setPredefinedKeyValue("appkey",           $this->appkey);
			$brocast->setPredefinedKeyValue("timestamp",        $this->timestamp);
			$brocast->setPredefinedKeyValue("validation_token", $this->validation_token);
			$brocast->setPredefinedKeyValue("ticker",           $title);
			$brocast->setPredefinedKeyValue("title",            $title);
			$brocast->setPredefinedKeyValue("text",             $info);
			$brocast->setPredefinedKeyValue("after_open",       "go_activity");
			
			$brocast->setPredefinedKeyValue("activity",       'com.example.fangpinhui.view.MainTabHostActivity');//新增跳转
			
			// Set 'production_mode' to 'false' if it's a test device. 
			// For how to register a test device, please see the developer doc.
			//lishun 20150325 true改为false
			$brocast->setPredefinedKeyValue("production_mode", "false");
			// [optional]Set extra fields
			
			//extra
			$brocast->setExtraField("id",                      $pid);//推送的id号，文章的，活动的，楼盘的
			$brocast->setExtraField("activityname",            $activityName);//推送的id号，文章的，活动的，楼盘的
			
		//	print("Sending broadcast notification, please wait...\r\n");
			$brocast->send();
		//	print("Sent SUCCESS\r\n");
		} catch (Exception $e) {
		//	print("Caught exception: " . $e->getMessage());
		}
	}
     //单个手机测试，推送消息
	function sendAndroidUnicast() {
		try {
			$unicast = new AndroidUnicast();
			$unicast->setPredefinedKeyValue("appkey",           $this->appkey);
			$unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);
			$unicast->setPredefinedKeyValue("validation_token", $this->validation_token);
			// Set your device tokens here
			$unicast->setPredefinedKeyValue("device_tokens",    "AkRIXIB6WsFAlVFvTbHs4On2MEZh8nCX3nRrsrwVBnGi"); //设备token
			$unicast->setPredefinedKeyValue("ticker",           "消息描述");
			$unicast->setPredefinedKeyValue("title",            "消息标题");
			$unicast->setPredefinedKeyValue("text",             "消息文本");
			$unicast->setPredefinedKeyValue("after_open",       "go_app");
			// Set 'production_mode' to 'false' if it's a test device. 
			// For how to register a test device, please see the developer doc.
			$unicast->setPredefinedKeyValue("production_mode", "false");  
			// Set extra fields
			$unicast->setExtraField("test", "helloworld");
			print("Sending unicast notification, please wait...\r\n");
			$unicast->send();
			print("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			print("Caught exception: " . $e->getMessage());
		}
	}

	function sendAndroidFilecast() {
		try {
			$filecast = new AndroidFilecast();
			$filecast->setPredefinedKeyValue("appkey",           $this->appkey);
			$filecast->setPredefinedKeyValue("timestamp",        $this->timestamp);
			$filecast->setPredefinedKeyValue("validation_token", $this->validation_token);
			$filecast->setPredefinedKeyValue("ticker",           "Android filecast ticker");
			$filecast->setPredefinedKeyValue("title",            "Android filecast title");
			$filecast->setPredefinedKeyValue("text",             "Android filecast text");
			$filecast->setPredefinedKeyValue("after_open",       "go_app");  //go to app
			print("Uploading file contents, please wait...\r\n");
			// Upload your device tokens, and use '\n' to split them if there are multiple tokens
			$filecast->uploadContents("aa"."\n"."bb");
			print("Sending filecast notification, please wait...\r\n");
			$filecast->send();
			print("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			print("Caught exception: " . $e->getMessage());
		}
	}
//组播
	function sendAndroidGroupcast($city_id1,$city_id2,$title,$info,$activityName,$pid,$after_open) {
		try {
			/* 
		 	 *  Construct the filter condition:
		 	 *  "where": 
		 	 *	{
    	 	 *		"and": 
    	 	 *		[
      	 	 *			{"tag":"test"},
      	 	 *			{"tag":"Test"}
    	 	 *		]
		 	 *	}
		 	 */
			$filter = 	array(
							"where" => 	array(
								    		"or" 	=>  array(
								    						  array(
							     								"tag" =>$city_id1
															    ),
								    				          array(
								    						  "tag" =>$city_id2
								    				         )
								     		 			)
								   		)
					  	);
					  
			$groupcast = new AndroidGroupcast();
			
			$groupcast->setPredefinedKeyValue("appkey",           $this->appkey);
			$groupcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
			$groupcast->setPredefinedKeyValue("validation_token", $this->validation_token);
			//extra
			$groupcast->setExtraField('id',                       $pid);
            $groupcast->setExtraField('activityname',             $activityName);

            //暂先随意定义
            $groupcast->setPredefinedKeyValue('custom',            '11');//可选 display_type=message, 或者display_type=notification且"after_open"为"go_custom"时，  该字段必填。用户自定义内容, 可以为字符串或者JSON格式。
            		
			// Set the filter condition
			$groupcast->setPredefinedKeyValue("filter",           $filter);
			$groupcast->setPredefinedKeyValue("ticker",           $title);
			$groupcast->setPredefinedKeyValue("title",            $title);
			$groupcast->setPredefinedKeyValue("text",             $info);
			$groupcast->setPredefinedKeyValue("after_open",       $after_open);
			
			$groupcast->setPredefinedKeyValue("activity",       'com.example.fangpinhui.view.MainTabHostActivity');//新增跳转
			
			// Set 'production_mode' to 'false' if it's a test device. 
			// For how to register a test device, please see the developer doc.
			//lishun 20150325 true改为false
			$groupcast->setPredefinedKeyValue("production_mode", "true");
		//	print("Sending groupcast notification, please wait...\r\n");
			$groupcast->send();
		//	print("Sent SUCCESS\r\n");
		} catch (Exception $e) {
		//	print("Caught exception: " . $e->getMessage());
		}
	}
//自定义广播
	function sendAndroidCustomizedcast($alias_uid,$alert_info,$title,$with_look,$activityName,$activity,$reporte_id=0,$data,$after_open) {
		try {
			$customizedcast = new AndroidCustomizedcast();
			$customizedcast->setPredefinedKeyValue("appkey",           $this->appkey);
			$customizedcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
			$customizedcast->setPredefinedKeyValue("validation_token", $this->validation_token);
			
			$customizedcast->setExtraField('cid',                     $reporte_id); //额外参数
		    $customizedcast->setExtraField('mLookType',               $with_look);//新增带看类型
		    $customizedcast->setExtraField('muclient_name',           $data['muclient_name'] ? $data['muclient_name'] : 0);//客户名字
		    $customizedcast->setExtraField('muclient_mobile',         $data['muclient_mobile'] ? $data['muclient_mobile'] : 0);//客户电话
		    $customizedcast->setExtraField('agent_name',              $data['agent_name'] ? $data['agent_name'] : 0);//经纪人名字
		    $customizedcast->setExtraField('agent_mobile',            $data['agent_mobile'] ? $data['agent_mobile'] : 0);//经纪人电话
		    $customizedcast->setExtraField('activityname',            $activityName);
		    
		    //暂先随意定义
		    $customizedcast->setPredefinedKeyValue('custom',            '11');//可选 display_type=message, 或者display_type=notification且"after_open"为"go_custom"时，  该字段必填。用户自定义内容, 可以为字符串或者JSON格式。
			
			
			// Set your alias here, and use comma to split them if there are multiple alias.
			// And if you have many alias, you can also upload a file containing these alias, then 
			// use file_id to send customized notification.
			$customizedcast->setPredefinedKeyValue("alias",            $alias_uid);
			// Set your alias_type here
			$customizedcast->setPredefinedKeyValue("alias_type",       "UID");//自定义类型
			$customizedcast->setPredefinedKeyValue("ticker",           $title);// 必填 通知栏提示文字
			$customizedcast->setPredefinedKeyValue("title",            $title);// 必填 通知标题
			$customizedcast->setPredefinedKeyValue("text",             $alert_info);// 必填 通知文字描述 
			$customizedcast->setPredefinedKeyValue("after_open",       $after_open ? $after_open : "go_activity");//"go_app":打开应用   "go_url":跳转到URL "go_activity":打开特定的activity "go_custom": 用户自定义内容	
			
			$customizedcast->setPredefinedKeyValue("activity",         $activity);//新增跳转
			
		//	print("Sending customizedcast notification, please wait...\r\n");
			$customizedcast->send();
		  //  print("Sent SUCCESS\r\n");
			return true ;
		} catch (Exception $e) {
		//	print("Caught exception: " . $e->getMessage());
		}
	}
       //广播
	function sendIOSBroadcast($city_id1,$city_id2,$title,$info,$cate_id,$pid) {
		try {
			$brocast = new IOSBroadcast();
			$brocast->setPredefinedKeyValue("appkey",           $this->appkey);
			$brocast->setPredefinedKeyValue("timestamp",        $this->timestamp);
			$brocast->setPredefinedKeyValue("validation_token", $this->validation_token);
			$brocast->setPredefinedKeyValue("alert", $title);
			$brocast->setPredefinedKeyValue("badge", 0);
			$brocast->setPredefinedKeyValue("sound", "chime");
			// Set 'production_mode' to 'true' if your app is under production mode
			//lishun 20150325 true改为false
			$brocast->setPredefinedKeyValue("production_mode", "true");
			// Set customized fields
			
			$brocast->setCustomizedField("id",                 $pid);//自定义的id
			$brocast->setCustomizedField("cate_id",            $cate_id);//自定义的id
			
			//print("Sending broadcast notification, please wait...\r\n");
			$brocast->send();
			//print("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			//print("Caught exception: " . $e->getMessage());
		}
	}  
     //给特定的手机发送
	function sendIOSUnicast() {
		try {
			$unicast = new IOSUnicast();
			$unicast->setPredefinedKeyValue("appkey",           $this->appkey);
			$unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);
			$unicast->setPredefinedKeyValue("validation_token", $this->validation_token);
			// Set your device tokens here
			$unicast->setPredefinedKeyValue("device_tokens",    "9085c4901c6c04f30c1b1d16b72f96af457d72a66b3b85994cdeaf753aa9d4f3"); //iphone tokens 
			$unicast->setPredefinedKeyValue("alert", "IOS 单播111测试");
			$unicast->setPredefinedKeyValue("badge", 0);
			$unicast->setPredefinedKeyValue("sound", "chime");
			// Set 'production_mode' to 'true' if your app is under production mode
			//lishun 20150325 true改为false
			$unicast->setPredefinedKeyValue("production_mode", "false");
			// Set customized fields
			$unicast->setCustomizedField("test", "helloworld");
			print("Sending unicast notification, please wait...\r\n");
			$unicast->send();
			print("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			print("Caught exception: " . $e->getMessage());
		}
	}

	function sendIOSFilecast() {
		try {
			$filecast = new IOSFilecast();
			$filecast->setPredefinedKeyValue("appkey",           $this->appkey);
			$filecast->setPredefinedKeyValue("timestamp",        $this->timestamp);
			$filecast->setPredefinedKeyValue("validation_token", $this->validation_token);
			$filecast->setPredefinedKeyValue("alert", "IOS 文件播测试");
			$filecast->setPredefinedKeyValue("badge", 0);
			$filecast->setPredefinedKeyValue("sound", "chime");
			// Set 'production_mode' to 'true' if your app is under production mode
			//lishun 20150325 true改为false
			$filecast->setPredefinedKeyValue("production_mode", "false");
			print("Uploading file contents, please wait...\r\n");
			// Upload your device tokens, and use '\n' to split them if there are multiple tokens
			$filecast->uploadContents("aa"."\n"."bb");
			print("Sending filecast notification, please wait...\r\n");
			$filecast->send();
			print("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			print("Caught exception: " . $e->getMessage());
		}
	}
    //组播
	function sendIOSGroupcast($city_id1,$city_id2,$title,$info,$cate_id,$pid) {
		try {
			/* 
		 	 *  Construct the filter condition:
		 	 *  "where": 
		 	 *	{
    	 	 *		"and": 
    	 	 *		[
      	 	 *			{"tag":"iostest"}
    	 	 *		]
		 	 *	}
		 	 */
			$filter = 	array(
							"where" => 	array(
								    		"or" 	=>  array(
								    						array(
							     								"tag" => $city_id1
															),
								    						array(
								    							"tag" => $city_id2
								    						)
								     		 			)
								   		)
					  	);
					  
			$groupcast = new IOSGroupcast();
			$groupcast->setPredefinedKeyValue("appkey",           $this->appkey);
			$groupcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
			$groupcast->setPredefinedKeyValue("validation_token", $this->validation_token);
			// Set the filter condition
			$groupcast->setPredefinedKeyValue("filter",           $filter);
			$groupcast->setPredefinedKeyValue("alert",            $title.$info);
			$groupcast->setPredefinedKeyValue("badge", 0);
			$groupcast->setPredefinedKeyValue("sound", "chime");
			
			$groupcast->setCustomizedField('id',                  $pid);  //自定义的id
			$groupcast->setCustomizedField('cate_id',             $cate_id);//自定义的类型
			
			// Set 'production_mode' to 'true' if your app is under production mode
			//lishun 20150325 true改为false
			$groupcast->setPredefinedKeyValue("production_mode", "true");
			//print("Sending groupcast notification, please wait...\r\n");
			$groupcast->send();
			//print("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			//print("Caught exception: " . $e->getMessage());
		}
	}
        //定制广播
	function sendIOSCustomizedcast($alias_uid,$alert_info,$title,$with_look,$reporte_id=0,$data=0) {
		try {
			$customizedcast = new IOSCustomizedcast();
			$customizedcast->setPredefinedKeyValue("appkey",           $this->appkey);
			$customizedcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
			$customizedcast->setPredefinedKeyValue("validation_token", $this->validation_token);
			// Set your alias here, and use comma to split them if there are multiple alias.
			// And if you have many alias, you can also upload a file containing these alias, then 
			// use file_id to send customized notification.
			
			$customizedcast->setCustomizedField("type",                  $with_look);//带看类型
			$customizedcast->setCustomizedField("cid",                   $alias_uid);//经纪人的id
			$customizedcast->setCustomizedField("cate_id",              '0');//区分楼盘、文章、活动
			
			$customizedcast->setCustomizedField("reporte_id",            $reporte_id);//报备生成的id
			$customizedcast->setCustomizedField("data",                  $data);//报备详情页面的相关信息
			
			
			$customizedcast->setPredefinedKeyValue("alias",             $alias_uid);
			$customizedcast->setPredefinedKeyValue("alias_type",        "UID");//自定义类型
			$customizedcast->setPredefinedKeyValue("alert",             $title.$alert_info);
			$customizedcast->setPredefinedKeyValue("badge",             0);
			$customizedcast->setPredefinedKeyValue("sound",             "chime");
			// Set 'production_mode' to 'true' if your app is under production mode
			$customizedcast->setPredefinedKeyValue("production_mode", "true");
			//print("Sending customizedcast notification, please wait...\r\n");
			$customizedcast->send();
			//print("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			//print("Caught exception: " . $e->getMessage());
		}
	}
}




