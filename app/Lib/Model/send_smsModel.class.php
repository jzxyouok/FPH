<?php
// +----------------------------------------------------------------------
// | Descriptions:本页面作为 短信发送接口
// +----------------------------------------------------------------------
// | State : 不可以删除此页面，如要删除，前联系作者。
// +----------------------------------------------------------------------
// | Date: 2014-12-01
// +----------------------------------------------------------------------
// | Author: chl
// +----------------------------------------------------------------------

class send_smsModel extends RelationModel{
	
	/*
	 * 短信接口统一调用模型
	*
	* @param string $send_mobile 发送的手机号
	* @param  $agent_mobile   经纪人手机号
	* @param  $agent_name   经纪人名字
	* @param  $client_mobile   客户手机号
	* @param  $client_name   客户名字
	* @param  $title   楼盘名称
	* @param  $str   报备性质
	* @param  $mobile_code   手机验证码
	* @param  $type   接口类型  1：发送给经纪人  2：发送给案场  3注册，找回密码 4 注册成功短信提示 5 添加经纪人
	* @param  $rtninfo  发送短信返回值
	* @param  $origin  接口请求终端   1微信 2app 3pc
	* @param  $mobile_code_origin   哪种功能获取手机验证码
	* @return bool
	*/
   public function Messages($send_mobile,$agent_mobile,$agent_name,$client_name,$client_mobile,$title,$str,$mobile_code,$type,$rtninfo = null,$origin,$mobile_code_origin) {
   	 if($type==1){
   		$post_data = "account=cf_obwd&password=eUr56C&mobile=".$send_mobile."&content=".rawurlencode("不错哦！您已成功提交了客户".$client_mobile."到".$title."报备的信息。稍后我们将与您电联，请保持手机畅通哦！您已迈出了成功的第一步，加油！我的微信号：fangpinhui2014");
   	    $send_content = "不错哦！您已成功提交了客户".$client_mobile."到".$title."报备的信息。稍后我们将与您电联，请保持手机畅通哦！您已迈出了成功的第一步，加油！我的微信号：fangpinhui2014";
   	 }
     if($type==2){
   		$post_data = "account=cf_obwd&password=eUr56C&mobile=".$send_mobile."&content=".rawurlencode("已有经纪人".$agent_name."，".$agent_mobile."于".date('Y-m-d H:i',time())."成功报备客户".$client_name."，".$client_mobile."至".$title."，请及时联系。");
   		$send_content = "已有经纪人".$agent_name."，".$agent_mobile."于".date('Y-m-d H:i',time())."成功报备客户".$client_name."，".$client_mobile."至".$title."，请及时联系。" ;
     }
     
     if($mobile_code_origin == '1'){  //注册
     	$prefix = "[注册] ";
     }elseif ($mobile_code_origin == '2'){  //忘记密码
     	$prefix = "[修改密码] ";
     }elseif ($mobile_code_origin == '3'){  //修改手机号码
     	$prefix = "[修改手机号] ";
     }elseif ($mobile_code_origin == '4'){  //登录
     	$prefix = "[登录] ";
     }
     
     if($type==3){
     	$post_data = "account=cf_obwd&password=eUr56C&mobile=".$send_mobile."&content=".rawurlencode("您的验证码是：".$mobile_code."，打死都不能告诉别人。如非本人操作，可不用理会！");
     	$send_content = $prefix."您的验证码是：".$mobile_code."，打死都不能告诉别人。如非本人操作，可不用理会！";
     
     }
     if($type==4){
     	$post_data   = "account=cf_obwd&password=eUr56C&mobile=".$send_mobile."&content=".rawurlencode("恭喜你".$send_mobile."，你已经注册成功！请到会员中心完善个人资料，以便获得更好的服务。我们的微信号：fangpinhui2014");
     	$send_content = "恭喜你".$send_mobile."，你已经注册成功！请到会员中心完善个人资料，以便获得更好的服务。我们的微信号：fangpinhui2014";
     }
     if($type==5){
     	 $post_data   = "account=cf_obwd&password=eUr56C&mobile=".$send_mobile."&content=".rawurlencode("恭喜你，你已成功注册成为房品汇的经纪人！你的账号:".$send_mobile."，密码:".$mobile_code."，为获得更好的服务，请关注我们的微信号：fangpinhui2014");
     	 $send_content = "恭喜你，你已成功注册成为房品汇的经纪人！你的账号:".$send_mobile."，密码:".$mobile_code."，为获得更好的服务，请关注我们的微信号：fangpinhui2014";
     }
	 if($type==6){
     	 $post_data   = "account=cf_obwd&password=eUr56C&mobile=".$send_mobile."&content=".rawurlencode("你服务的门店".$str."，已有经纪人".$agent_name.$agent_mobile."，报备客户".$client_name.$client_mobile."，到".$title."，开发商已经确认。");
     	 $send_content = "你服务的门店".$str."，已有经纪人".$agent_name.$agent_mobile."，报备客户".$client_name.$client_mobile."，到".$title."，开发商已经确认。";
     }
	 if($type==7){
     	 $post_data   = "account=cf_obwd&password=eUr56C&mobile=".$send_mobile."&content=".rawurlencode("你的房品云服务账号已开通，账号：".$send_mobile."，密码：".$mobile_code."，请登录房品云修改密码，http://myun.fangpinhui.com");
     	 $send_content = "[后台添加管理员]你的房品云服务账号已开通，账号：".$send_mobile."，密码：".$mobile_code."，请登录房品云修改密码，http://myun.fangpinhui.com";
     }
     $target = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
	  //密码可以使用明文密码或使用32位MD5加密
	  $gets =  xml_to_array(Post($post_data, $target));
	  
	  if($gets['SubmitResult']['code']!=2){
	  	$send_content = "未发送成功!";
	  }

	  //插入短信数据库
	  $data['mobile']  = $send_mobile;  //发送手机号
	  $data['code']    = $send_content;//发送内容
	  $data['pid']     = $type;
	  $data['origin']  = $origin;//接口请求终端
	  $data['result']  = $gets['SubmitResult']['code'];//发送短信返回code
	  $data['msg']     = $gets['SubmitResult']['msg'];//发送短信文字提示
	  $data['add_time']= time();
	   
	  $result = $this->add($data);
	  
	  if($rtninfo){
	  	return $gets;
	  } else {
	  	return $result;
	  }

   }
   

	
}