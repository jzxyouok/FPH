<?php
class weixinapiAction extends frontendAction {
    public function _initialize() {
        //parent::_initialize();
		load("@.wechat_functions");	 //加载微信函数 位置common文件夹下面
		$this->appid='wx3ce1eceec205c6c4';
		$this->appsecret='6a377f78c74e13ff5e4e425af7b11ecc';

    }
	/*
	* 统计网站访问量*用户活跃度
	* chl
	* 20150127
	*/
    public function index() {
    	//echo '1111';exit;
	    $APPID='wx3ce1eceec205c6c4';
	    $REDIRECT_URI='http://www.fangpinhui.com/weixin/weixinapi/callback';
	    $scope='snsapi_base';
	    $state=1;
	     
	    $url='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$APPID.'&redirect_uri='.urlencode($REDIRECT_URI).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';    
	     header("Location:".$url);
		
	}

	//回调页面
	public function callback(){
		//echo '11';exit;
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

		/*$ch = curl_init();  
		curl_setopt($ch,CURLOPT_URL,$get_token_url);  
		curl_setopt($ch,CURLOPT_HEADER,0);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );  
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);  
		$res = curl_exec($ch);  
		curl_close($ch);  
		$json_obj = json_decode($res,true);*/ 
		
		$json_obj = json_decode(httpGet($get_token_url),true);
		//根据openid和access_token查询用户信息  
		$access_token = $json_obj['access_token'];  
		$openid = $json_obj['openid'];
		$user_info=getwechatuser($this->appid,$this->appsecret,$openid);
		dump($user_info);die;
		echo $openid;exit;


		$get_user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
		$ch = curl_init();  
		curl_setopt($ch,CURLOPT_URL,$get_user_info_url);  
		curl_setopt($ch,CURLOPT_HEADER,0);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );  
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);  
		$res = curl_exec($ch);  
		curl_close($ch);  

		//解析json  
		$user_obj = json_decode($res,true);  

		//$_SESSION['user'] = $user_obj;  
		print_r($user_obj);//exit;
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

    public function user_list(){
    	$access_token=$this->access_token();  //获取access_token
    	$json_url='https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$access_token;
    	$json_obj = json_decode(httpGet($json_url),true);

    	$time = $this->rand_time();
    	foreach($json_obj['data']['openid'] as $key=>$val){
    	 	//echo $val.'<br>';
    	 	$weixin_user = M('weixin_user')->where("fromusername='".$val."'")->count('id');
    	 	if(!$weixin_user){
    	 		if(false !== M('weixin_user')->add(array('fromusername'=>$val,'add_time'=>$time,'status'=>1))){
    	 			echo $val.'添加成功<br>';
    	 		}else{
					echo $val.'添加失败<br>';
    	 		}
    	 	}
    	}

    	 //print_r($json_obj['data']);
    }

    function access_token(){
    	$appid = "wx3ce1eceec205c6c4";
		$appsecret = "6a377f78c74e13ff5e4e425af7b11ecc";
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
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

    //随机时间戳
    function rand_time(){
		return $time = mt_rand(1398906060,1422253930);	
    }

    //现金红包-入口
    public function hongbao(){
    	Vendor("hongbao.pay");
    	$get = $_GET['param'];
		$code = $_GET['code'];
		echo '11111';exit;
		//判断code是否存在
		if($get=='access_token' && !empty($code)){
			$params['param'] = 'access_token';
			$params['code'] = $code;
			$packet = new Packet();
			//获取用户openid信息
			//print_r($param);
			$userinfo = $packet->_route('userinfo',$params);			
			if(empty($userinfo['openid'])){
				exit("个人信息读取失败");
			}
			//echo 'openid:'.$userinfo['openid'];exit;
			$msg = $packet->_route('wxpacket',array('openid'=>$userinfo['openid']));
			echo $msg.'-Action';//完成时候，返回参数
		}else{
			$packet = new Packet();
			$packet->_route('userinfo');
		}
    }
    

}