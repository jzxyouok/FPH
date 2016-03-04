<?php
class myshareAction extends weixin_userbaseAction {
    
    public function index() {
	
		$uid = $this->visitor->info['id'];
		$fph   = C('DB_PREFIX');
		$list = M('user')->where('share_id ='.$uid)->limit(0,15)->select();
		$money = 0;
		foreach($list as $k => $v)
		{
		    $my =  M('myclient')->table("{$fph}myclient AS A
			    INNER JOIN {$fph}myclient_property AS B ON A.id=B.pid")
			    ->where('A.uid='.$v['id'].' AND B.status = 4')
			    ->count();
		    if($my)
		    {
			$money = $money + 2000;
		    }
		}
		$share_id = md5($uid);
		$this->assign('share_id', $share_id);
		$this->assign('money', $money);
		$this->assign('setTitle', '友钱同赚');
        $this->_config_seo();
        $this->display();
    }
    
    //我的二维码
    public function qrcode()
    {

	    $uid = $this->visitor->info['id'];
		if(empty($uid)){
		    $login_url = __ROOT__.'/index.php?g=weixin&m=user&a=login';
		    header("Location: $login_url");
		}
		$user = M('user')->field('id,username,city_id')->where('id ='.$uid)->find();
		
		//显示市 区
		$user['address'] = '';
		if(!empty($user['city_id']))
		{
		    $qu = M('city')->where('id ='.$user['city_id'])->find();
		    $shi = M('city')->where('id ='.$qu['pid'])->find();
		    $user['address'] = $shi['name'].'&nbsp;&nbsp;&nbsp;'.$qu['name'];
		}
		//扫描二维码-share
		$urlToEncode = C('website')."/t/t/d/".$uid;
		//$urlToEncode = C('website')."/index.php?g=weixin&m=myshare&a=register&share_id=".md5($uid)."";
		$chl = urlencode($urlToEncode);
		$this->assign('uid', $uid);
		$this->assign('rq_img', $urlToEncode);
		$this->assign('user', $user);
		$this->assign('setTitle', '我的二维码');
		$this->_config_seo();
        $this->display();
    }
    
    
    //我的推荐邀请码
    public function invitation(){
		$uid = $this->visitor->info['id'];
		$id = $this->_get('id','intval');
		$fph   = C('DB_PREFIX');
		if(!$id){
			$this->error('非法提交');
			exit;
		}
		$http_referer = $_SERVER['HTTP_REFERER'];
		if(!$uid){
		    $url = __ROOT__.'/index.php?g=weixin&m=myshare&a=register&id='.$id.'';
		    header("Location: $url");
			exit;
		}else{
			if(!$http_referer){
				$url = __ROOT__.'/index.php?g=weixin&m=stores&a=index';
				header("Location: $url");
				exit;
			}
		}

		
		$info = M('stores')->field('A.code_id,A.name,B.teamwork')
							->table("{$fph}stores AS A INNER JOIN {$fph}company AS B ON A.pid=B.id")
							->where('A.id ='.$id)
							->find();
		if(!$info['teamwork']){
			$this->error('该门店为内部门店,禁止邀请');
			exit;
		}
		$this->assign('info', $info);
		
		//微信接口
		/*
		Vendor("weixinApi.jssdk");
		$jssdk = new JSSDK("wx3ce1eceec205c6c4", "6a377f78c74e13ff5e4e425af7b11ecc");
		$signPackage = $jssdk->GetSignPackage();
		$this->assign('signPackage', $signPackage);
		*/
		
		
		$domain = $_SERVER['HTTP_HOST'];
		$this->assign('domain', $domain);

		$current_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$this->assign('current_url', $current_url);

		$this->assign('setTitle', '我的推广');
		$this->_config_seo();
	    $this->display();
    }
    
    //邀请注册
    public function register(){

	    $uid = $this->visitor->info['id'];
		$id = $this->_get('id','intval');
		$fph   = C('DB_PREFIX');
		if(!$id){
			$this->error('非法提交');
			exit;
		}
		if($uid){
		    $url = __ROOT__.'/index.php?g=weixin&m=stores&a=index';
		    header("Location: $url");
			exit;
		}
		$send_code = session('send_code',random(6,1)); 
		$send_code = session('send_code');
		
		
		$url = urlencode($this->_get('url','trim'));
		if(!$url){
			$url = urlencode(__ROOT__.'/index.php?g=weixin&m=user&a=index');
		}
		$this->assign('url', $url);
	
		$info = M('stores')->field('A.code_id,A.name,B.teamwork')
							->table("{$fph}stores AS A INNER JOIN {$fph}company AS B ON A.pid=B.id")
							->where('A.id ='.$id)
							->find();
		if(!$info['teamwork']){
			$this->error('该门店为内部门店,禁止邀请');
			exit;
		}
		$this->assign('info', $info);
		$this->assign('send_code', $send_code);
		$this->assign('setTitle', '我的推广');
		$this->_config_seo();
        $this->display();
    }
    
    //ajax邀请码标题内容提交
    public function ajax_invitation()
    {
		if(IS_POST)
		{

		    $uid = $this->visitor->info['id'];
		    $data['uid'] = $uid;
		    $data['title'] = trim($this->_post('title','trim'));
		    $data['content'] = trim($this->_post('content','trim'));
		    $data['add_time'] = time();
		    if(empty($data['title']) || empty($data['content']))
		    {
				$this->ajaxReturn(0,'邀请函标题或内容不可为空');
		    }
		    
		    if(strlen($data['title']) > 50)
		    {
				$this->ajaxReturn(0,'邀请函标题不能过长');
		    }
		    
		    if(strlen($data['content']) > 255)
		    {
				$this->ajaxReturn(0,'邀请函内容不能过长');
		    }
		    
		    $myshare_id = M('user_myshare')->where('uid ='.$data['uid'])->getfield('id');
		    
		    if(empty($myshare_id))
		    {
				M('user_myshare')->add($data);
		    }
		    else
		    {
				M('user_myshare')->where('id ='.$myshare_id)->save($data);
		    }
		    $this->ajaxReturn(1,'修改成功');
		}
    }
    
}