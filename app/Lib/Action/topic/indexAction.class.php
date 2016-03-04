<?php
class indexAction extends frontendAction {
    
	
	public function index(){
		$this->redirect('index/loupan');
		
		$this->assign('setTitle', '微信专题');
		$this->_config_seo();
		$this->display();
	}
	
	public function loupan(){
		if(IS_POST){
			$name   = $this->_post('name','tirm');
			$mobile = $this->_post('mobile','tirm');
			$uid    = $this->visitor->info['id'];
			
			!$name && $this->ajaxReturn(0,'请输入姓名');
			!$mobile && $this->ajaxReturn(0,'请输入手机号码');
			if(!$uid){
				$uid = 0;
			}
			$data['uid']      = $uid;
			$data['pid']      = 197;
			$data['name']     = $name;
			$data['mobile']   = $mobile;
			$data['add_time'] = time();
			if(false !== M('topic_baobei')->add($data)){
				$this->ajaxReturn(1,'提交成功');
			}else{
				$this->ajaxReturn(0,'提交失败');
			}
			
		}
		
		//分享
		$time = time();
		$this->assign('jssdk', A('weixin/jssdk')->getSignPackage());
		$this->assign('time', $time);
		
		$url = 'http://www.fangpinhui.com';
		$this->assign('url', $url);
		
		
		$this->assign('setTitle', '楼盘专题');
		$this->_config_seo();
		$this->display();
	}

	//关于我们
	public function about(){

		//分享
		$time = time();
		 
		$this->assign('jssdk', A('weixin/jssdk')->getSignPackage());
		$this->assign('time', $time);
		
		$url = 'http://www.fangpinhui.com';
		$this->assign('url', $url);
		
		
		$this->assign('setTitle', '楼盘专题');
		$this->_config_seo();
		$this->display();
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
            $curl_data='{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": "12"}}}';
        } 
        $json_info=json_decode($this->api_notice_increment($json_url,$curl_data),true); 
        print_r($json_info);
        exit;
    }

    function access_token(){
    	$appid = "wx3ce1eceec205c6c4";
		$appsecret = "6a377f78c74e13ff5e4e425af7b11ecc";
		$access_token=getAccessTokenforjssdk($appid,$appsecret);
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