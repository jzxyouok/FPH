<?php
class weixinapiAction extends frontendAction {
    
	
    public function index() {
    	//echo '1111';exit;
	    $APPID='wx3ce1eceec205c6c4';
	    $REDIRECT_URI='http://www.fangpinhui.com/weixin/weixinapi/callback';
	    $scope='snsapi_base';
	    $state=1;
	     
	    $url='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$APPID.'&redirect_uri='.urlencode($REDIRECT_URI).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';    
	     header("Location:".$url);
		
	}

	//获取用户信息
	public function callback(){
		$appid = "wx3ce1eceec205c6c4";  
		$secret = "6a377f78c74e13ff5e4e425af7b11ecc"; 
		$REDIRECT_URI='http://www.fangpinhui.com/weixin/weixinapi/callback'; 
		$scope='snsapi_base';
		$state=1;
		$code = $_GET["code"];

		if(!$code){
			$url='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.urlencode($REDIRECT_URI).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';  
			header("Location:".$url);
		}

		$get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';	
		$json_obj = httppost($get_token_url);
		$access_token = $json_obj['access_token'];  
		$openid = $json_obj['openid'];
		echo $openid;exit;

		/*
		$get_user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
		$ch = curl_init();  
		curl_setopt($ch,CURLOPT_URL,$get_user_info_url);  
		curl_setopt($ch,CURLOPT_HEADER,0);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );  
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);  
		$res = curl_exec($ch);  
		curl_close($ch);   
		$user_obj = json_decode($res,true);  

		//$_SESSION['user'] = $user_obj;  
		print_r($user_obj);//exit;
		*/
	}

	public function createewm(){
        $access_token=$this->access_token();  //获取access_token
        $json_url='https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;
         
        $action_name='QR_LIMIT_SCENE';   //生成类型(临时、永久)
        $create_num=$this->_post('create_num');     //生成数量

        //临时 post的json数据
        if($action_name=='QR_SCENE'){
            $curl_data='{"expire_seconds": 1800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": "123"}}';
        }
        
        //永久 post的json数据
        if($action_name=='QR_LIMIT_SCENE'){
            $curl_data='{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": 12}}}';
        } 
        $json_info=json_decode($this->api_notice_increment($json_url,$curl_data),true); 
        print_r($json_info);
        exit;
    }

    function access_token(){
    	$appid = "wx3ce1eceec205c6c4";
		$appsecret = "6a377f78c74e13ff5e4e425af7b11ecc";
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";

		/*$ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    $output = curl_exec($ch);
	    curl_close($ch);
	    $jsoninfo = json_decode($output, true);
	    $access_token = $jsoninfo["access_token"];
	    return $access_token;*/

		$jsoninfo = access_token($url);
		$access_token = $jsoninfo["access_token"];
		return $access_token;

		

    }
	
	function api_notice_increment($url, $data){
        $ch = curl_init();
        $header = "Accept-Charset: utf-8";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        if (curl_errno($ch)) {
            //curl_close( $ch )
            return $ch;
        }else{
            //curl_close( $ch ) 
            return $tmpInfo;
        }
        curl_close( $ch ) ;
    }
    

}