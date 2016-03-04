<?php
class vote_youngAction extends frontendAction {
    
	/*
	* 花样年华投票活动
	* gyw
	* 20150604
	*/
	public function _initialize() {
        parent::_initialize();
        $cache = F('actinfo');
		if(empty($cache)){
			$this->getactivity_info();
			echo "<script language=JavaScript> location.replace(location.href);</script>";
		}

		$time = explode('||', $cache[2]['data']);
		$start_time = $time[0];
		$start_end = $time[1];
		if(time() < $start_time){
			$this->error('活动尚未开始！');
		}
		if(time() > $start_end){
			$this->error('活动已经结束！');
		}

		$this->assign('actinfo',F('actinfo'));

		$this->AppID     = C('AppID');
		$this->AppSecret = C('AppSecret');
    }

	public function index() {
		$cache = F('actinfo');
		if(empty($cache)){
			$this->getactivity_info();
			$cache = F('actinfo');
		}
		$where = 'status=1';					
		$paiminglist =M('vote_young')->field('id,clickfans')->order('clickfans desc')->select(); 
		$list =  M('vote_young')->field('id,vote_personal,clickfans,avatar,vote_id')->where($where)->order('vote_id asc')->limit(0,16)->select();
		foreach ($paiminglist as $k => $v) {
				foreach ($list as $key => $value) {
					if($list[$key]['id'] == $paiminglist[$k]['id']){
						 $list[$key]['order'] = $k+1;
					}
				}
		}
		//分享	
		$time = time();	
		$this->assign('jssdk', A('weixin/jssdk')->getSignPackage());
		$this->assign('isclick', $isclick);
		$this->assign('time', $time);
		$url = 'http://www.fangpinhui.com';
		$this->assign('url', $url);
		$this->assign('countlp', count($list));
		$this->assign('list',$list);		
		$this->display();		
	}

	//向下滑动加载
	public function ajax_list()    {
		$fph     = C('DB_PREFIX');
		$where = 'status=1';
		$time = time();
		$page = $this->_post('page','intval');//获取请求的页数	
		$start = $page*16;
		$paiminglist =M('vote_young')->field('id,clickfans')->order('clickfans desc')->select(); 
		$list =  M('vote_young')->field('id,vote_personal,clickfans,avatar,vote_id')->where($where)->order('vote_id asc')->limit($start,16)->select();
		foreach ($paiminglist as $k => $v) {
				foreach ($list as $key => $value) {
					if($list[$key]['id'] == $paiminglist[$k]['id']){
						 $list[$key]['order'] = $k+1;
					}
				}
		}
		//html 显示
		$str ='';
		foreach ($list as $key => $value) {			
			$str .='<li><a href="'.u("vote_young/detail",array("id"=>$value["id"])).'"><figure><img src="'.get_fdfs_image($value['avatar'],'').'"></figure><h4>'.$value['vote_personal'].'<span>'.$value['vote_id'].'号</span></h4><p class="vote">'.$value['clickfans'].'个赞<span>(排名'.$value['order'].')</span></p></a></li>';

		}
	    if($str){
			$this->ajaxReturn(1,'',$str);
		}else{
			$this->ajaxReturn(0,'别滑动了，已经到底了...');
		}    
	}

	//获取活动信息并且缓存
    public function getactivity_info(){
    	$actinfo = M('vote_settings')->where(array('activity'=>'vote_young'))->select();
    	F('actinfo',$actinfo);
    }

    //选手详情
    public function detail(){
		load("@.wechat_functions");
    	$id = $this->_get('id','intval');
    	$openid = cookie('vote_young_openid');
    	$time = time();
    	if(empty($vote_young_openid)){
			//获取微信用户openid
			$code = $this->_get('code','trim');
			if(!$code){
				$url='http://www.fangpinhui.com/?g=weixin&m=vote_young&a=detail&id='.$id;
				redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->AppID.'&redirect_uri='.urlencode($url).'&response_type=code&scope=snsapi_base&state=123#wechat_redirect');
				exit;
			}
			$get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->AppID.'&secret='.$this->AppSecret.'&code='.$code.'&grant_type=authorization_code';
			$json_obj = json_decode(httpGet($get_token_url),true);
			$openid = $json_obj['openid'];
			cookie('vote_young_openid',$openid);
    	}
    	//判断是否赞过
    	     $tmptime = $time -600;  
    	     $isclick = M('vote_young_tmp')->where(array('openid'=>$openid,'vid'=>$id,'addtime'=>array('gt',$tmptime)))->getfield('id');


    	$res =  M('vote_young')->where(array('id'=>$id))->find();
    	if(empty($res)){
    		$this->error('请求参数错误！');
    		exit;
    	}else{
    		//上一个下一个
    		$next = M('vote_young')->where('id >'.$id)->order('id asc')->limit(1)->getfield('id');
    	    $pre = M('vote_young')->where('id <'.$id)->order('id desc')->limit(1)->getfield('id');
    		$this->assign('info',$res);
    		$this->assign('pre',$pre);
    		$this->assign('next',$next);
    	}

    	//分享		
		$this->assign('jssdk', A('weixin/jssdk')->getSignPackage());
		$this->assign('isclick', $isclick);
		$this->assign('time', $time);
		$url = 'http://www.fangpinhui.com';
		$this->assign('url', $url);

    	$this->display();
    }

    //点赞功能  每10分钟可以点赞一次
    public function addfans(){
		$openid = cookie('vote_young_openid');
		if(empty($openid)){
			$result = array('status'=>0,'msg'=>'每10分钟可赞一次');exit(json_encode($result));
		}
		$time = time();
		$vid = $this->_post('vid','intval');
		$voteinfo = array('openid'=>$openid,'addtime'=>$time,'vid'=>$vid);
		$tmptime = $time -600;     		 //10分钟
		//判断10分钟内该ip是否为此选手投过票
		$isvote = M('vote_young_tmp')->where(array('openid'=>$openid,'vid'=>$vid,'addtime'=>array('gt',$tmptime)))->getfield('id');		
		if(empty($isvote)){
				//删除先前此人为该选手的投票记录
				M('vote_young_tmp')->where(array('openid'=>$openid,'vid'=>$vid))->delete();
				$id = M('vote_young_tmp')->data($voteinfo)->add();
				M('vote_young')->where('id ='.$vid)->setInc('clickfans');
				$clickfans = M('vote_young')->where('id ='.$vid)->getfield('clickfans');				
				/*$result = array('status'=>1,'msg'=>M('vote_young_tmp')->getlastsql());
				exit(json_encode($result));*/
				$result = array('status'=>1,'msg'=>$clickfans);
		}else{
				$result = array('status'=>0,'msg'=>'每10分钟可赞一次');
		}    		
		exit(json_encode($result));
    }

    //花样年华投票排名
   	public function ranking(){
   			$list = M('vote_young')->where('status = 1')->order('clickfans desc')->limit(6)->select();
   			$this->assign('list',$list);
   			$this->display();
   	}

}