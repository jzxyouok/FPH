<?php
class vote_mengbaoAction extends frontendAction {
    
	/*
	* 萌宝投票活动
	* gyw
	* 20150702
	*/
            public function _initialize() {
                    parent::_initialize();
                    /*$start_time = $time[0];
                    $start_end = $time[1];
                    if(time() < $start_time){
                    $this->error('活动尚未开始！');
                    }
                    if(time() > $start_end){
                    $this->error('活动已经结束！');
                    }*/

                    $this->AppID     = 'wx7e8bef9f536e5fe2';//$this->AppID;
                    $this->AppSecret = 'd523fe45dca04dae341f483ac8ed4c9c';//$this->AppSecret;

            }

	public function index() {
            		//分享  
                    $time = time(); 
                    $this->assign('jssdk', A('weixin/jssdk')->getSignPackage());
                    $this->assign('time', $time);
                    $url = 'http://www.fangpinhui.com';
                    $this->assign('url', $url);
                    $this->display();		
           }

	//向下滑动加载
	public function ajax_list()    {
		$fph     = C('DB_PREFIX');
		$where = 'status=1 and vote_item =2';
		$search = $this->_post('search_','trim');
		if($search){
    		      $where .= 'and (vote_personal like "%'.$search.'%" or vote_id ="'.$search.'")';
    	           }
		$time = time();
		$page = $this->_post('page','intval');//获取请求的页数	
		$start = $page*6;
		$list =  M('vote_young')->field('id,vote_personal,clickfans,avatar,vote_id')->where($where)->order('clickfans desc,id asc')->limit($start,6)->select();
		foreach ($list as $key => $value) {
                		 if($key == 0){
                		 	$list[$key]['rank'] = 1;
                		 }
                		 if($key == 1){
                		 	$list[$key]['rank'] = 2;
                		 }
                		 if($key == 2){
                		 	$list[$key]['rank'] = 3;
                		 }
                		 if($key == 3){
                		 	$list[$key]['rank'] = 4;
                		 }
                		 $color = fmod($key,4);
                		 if($color == 0) $list[$key]['color'] = 'blue';
                		 if($color == 1) $list[$key]['color'] = 'pink';
                		 if($color == 2) $list[$key]['color'] = 'yellow';
                		 if($color == 3) $list[$key]['color'] = 'orange';

    	           }
		//html 显示
		$str ='';
		foreach ($list as $key => $value) {			
			$str .='<li data-id="'.$value['id'].'"><div class="img-box '.$value['color'].'"><div class="box"><a href="'.u('vote_mengbao/view',array('id'=>$value['id'])).'"><img src="'.$value['avatar'].'"></a></div></div><button class="rank-state" id="votebtn_'.$value['id'].'">给Ta投票</button><div class="rank-state"><span class="r-id">'.$value['vote_id'].'号</span><span class="r-name">'.$value['vote_personal'].'</span><span class="r-votes"><label id="fans_'.$value['id'].'">'.$value['clickfans'].'</label> 票</span></div></li>';

		}
	    if($str){
			$this->ajaxReturn(1,'',$str);
		}else{
			$this->ajaxReturn(0,'别滑动了，已经到底了...');
		}    
	}

    //选手详情
    public function view(){
            load("@.wechat_functions_vote");
            $id = $this->_get('id','intval');
            $appid = $this->AppID;
            $appsecret = $this->AppSecret;
            load("@.wechat_functions_vote");	 //加载微信函数 位置common文件夹下面
            $openid = S('vote_mengbao_openid');
            $time = time();
            if(empty($openid)){
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
                    S('vote_mengbao_openid',$openid,3600);
    	}
		
    	//判断是否赞过
            if(empty($openid))
            {
                $this->error('请求参数错误！');
                exit;
            }
            $tmptime = $time - 86400;  
            $isclick = M('vote_young_tmp')->where(array('vote_item'=>2,'openid'=>$openid,'vid'=>$id,'addtime'=>array('gt',$tmptime)))->getfield('id');
            $res =  M('vote_young')->where(array('id'=>$id))->find();
            M('vote_young')->where(array('id'=>$id))->setInc('views');
    	if(empty($res['description'])){
    		$res['description'] = '这个人很懒，什么都没有留下~';
    	}
            $res['imgs'] = explode('|',$res['imgs']);
    	if(empty($res)){
    		$this->error('请求参数错误！');
    		exit;
    	}
    	$this->assign('info',$res);

    	//分享		
	$this->assign('jssdk', A('weixin/jssdk')->getSignPackage());
	$this->assign('isclick', $isclick);
	$this->assign('time', $time);
	$url = 'http://www.fangpinhui.com';
	$this->assign('url', $url);

    	$this->display();
    }

   

    //奖项设置
    public function reward(){
    	$this->display();
    }

    //活动规则
    public function rule(){
    	$this->display();
    }

    //我要投票
    public function vote(){

            /*$appid = $this->AppID;
            $appsecret = $this->AppSecret;      
            load("@.wechat_functions_vote");     //加载微信函数 位置common文件夹下面
            $openid = S('vote_mengbao_openid');
            if(empty($openid)){
                  $code = $_GET["code"];
                if(!$code){
                      $url='http://www.fangpinhui.com/?g=weixin&m=vote_mengbao&a=vote';
                      redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.urlencode($url).'&response_type=code&scope=snsapi_base&state=123#wechat_redirect');
                     exit;
                }
                $get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$appsecret.'&code='.$code.'&grant_type=authorization_code';
                $json_obj = json_decode(httpGet($get_token_url),true);
                $openid = $json_obj['openid'];//用户的openid
                S('vote_mengbao_openid',$openid,3600);
            }*/
    

    	$where = 'vote_item =2 and status =1 ';
    	$search = $this->_get('search','trim');
    	if($search){
    		$where .= 'and (vote_personal like "%'.$search.'%" or vote_id ="'.$search.'")';
    	}
    	$list = M('vote_young')->where($where)->field('id,vote_id,vote_personal,avatar,clickfans')->limit(0,6)->order('clickfans desc,id asc')->select();
    	//print_r($list);
    	foreach ($list as $key => $value) {
    		 if($key == 0){
    		 	$list[$key]['rank'] = 1;
    		 }
    		 if($key == 1){
    		 	$list[$key]['rank'] = 2;
    		 }
    		 if($key == 2){
    		 	$list[$key]['rank'] = 3;
    		 }
    		 if($key == 3){
    		 	$list[$key]['rank'] = 4;
    		 }
    		 $color = fmod($key,4);
    		 if($color == 0) $list[$key]['color'] = 'blue';
    		 if($color == 1) $list[$key]['color'] = 'pink';
    		 if($color == 2) $list[$key]['color'] = 'yellow';
    		 if($color == 3) $list[$key]['color'] = 'orange';

    	}
           $this->assign('sshkey',$openid);
    	$this->assign('list',$list);	
    	$this->assign('search',$search);
    	$this->display();
    }

     //我要参赛
    public function join(){

            $appid = $this->AppID;
            $appsecret = $this->AppSecret;		
            load("@.wechat_functions_vote");	 //加载微信函数 位置common文件夹下面
            $openid = S('vote_mengbao_openid');
            if(empty($openid)){
            $code = $_GET["code"];
            if(!$code){
            	$url='http://www.fangpinhui.com/?g=weixin&m=vote_mengbao&a=join';
            	redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.urlencode($url).'&response_type=code&scope=snsapi_base&state=123#wechat_redirect');
            	exit;
            }
                $get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$appsecret.'&code='.$code.'&grant_type=authorization_code';
                $json_obj = json_decode(httpGet($get_token_url),true);
                $openid = $json_obj['openid'];//用户的openid
                S('vote_mengbao_openid',$openid,3600);
            }
              $user_info=getwechatuser($appid,$appsecret,$openid);

            if($user_info['subscribe'] == 0){
                $url = 'http://mp.weixin.qq.com/s?__biz=MzA5MzQ5MzkxNA==&mid=206952954&idx=1&sn=fca4d34358eb7e57287f39176c450833#rd';
                $this->redirect($url);
                exit;
            }
	
    	$this->display();
    }

  

    //执行投票
    public function addvote(){
    		$start_time = strtotime('2015-07-04 12:00');
    		$start_end = strtotime('2015-08-03 23:59');
    		$time = time();
			
    		if( $time < $start_time){;
			$result = array('status'=>0,'msg'=>'活动尚未开始!','url'=>'');exit(json_encode($result));
    		}
    		if( $time > $start_end){
			$result = array('status'=>0,'msg'=>'活动已经结束!','url'=>'');exit(json_encode($result));
    		}
			
    		//判断是否关注公众号  flag = 1  flag =0

		$appid = $this->AppID;
   		$appsecret = $this->AppSecret;	
		$openid =  1111;
		load("@.wechat_functions_vote");	 //加载微信函数 位置common文件夹下
		if($openid){
			$user_info=getwechatuser($appid,$appsecret,$openid);
		}
		if($user_info['subscribe'] != 1){
			$url = 'http://mp.weixin.qq.com/s?__biz=MzA5MzQ5MzkxNA==&mid=206952954&idx=1&sn=fca4d34358eb7e57287f39176c450833#rd';
			//$result = array('status'=>0,'msg'=>'请先关注公众号!','url'=>$url);exit(json_encode($result));
                                $result = array('status'=>0,'msg'=>$openid,'url'=>$url);exit(json_encode($result));
			exit;
		}

    		$flag = $user_info['subscribe'];
    		if($flag){

			if(empty($openid)){
				$url = u('vote_mengbao/vote');
				$result = array('status'=>0,'msg'=>'请先关注公众号！','url'=>$url);exit(json_encode($result));
			}
			$time = time();
			$vid = $this->_post('id','intval');
			$voteinfo = array('vote_item'=>2,'openid'=>$openid,'addtime'=>$time,'vid'=>$vid);
			$tmptime = $time -86400;     		 // 一天赞一次
			//判断10分钟内该openid是否为此选手投过票
			$isvote = M('vote_young_tmp')->where(array('vote_item'=>2,'openid'=>$openid,'addtime'=>array('gt',$tmptime)))->getfield('id');		
			if(empty($isvote)){
					//删除先前此人为该选手的投票记录
					M('vote_young_tmp')->where(array('vote_item'=>2,'openid'=>$openid))->delete();
					$id = M('vote_young_tmp')->data($voteinfo)->add();
					M('vote_young')->where('id ='.$vid)->setInc('clickfans');
					$clickfans = M('vote_young')->where('id ='.$vid)->getfield('clickfans');				
					$result = array('status'=>1,'msg'=>$clickfans);
			}else{

					$url = u('vote_mengbao/vote');
					$result = array('status'=>0,'msg'=>'每天仅有一次投票机会，今天你已经投过票了哦~','url'=>$url);

			}    		
			exit(json_encode($result));

    		}
    		
    }

    //报名参赛
    public function joinvote(){
           //判断是否关注公众号

	$openid = S('vote_mengbao_openid');		
	if(empty($openid)){
		$url = 'http://mp.weixin.qq.com/s?__biz=MzA5MzQ5MzkxNA==&mid=206952954&idx=1&sn=fca4d34358eb7e57287f39176c450833#rd';
		$this->redirect($url);
		exit;
	}

        $data = array(
                'vote_personal' => $this->_post('user','trim'),
                'age' => $this->_post('age','intval'),
                'gender' => $this->_post('sex','intval'),
                'tel' => $this->_post('tel','trim'),
                'description' => $this->_post('intro','trim'),
                'addtime' => time(),
                'vote_item'=>2
            );   
        //图片是否符合要求 200 *200
       $data['avatar'] = $this->_post('avatar','trim');
	   if($data['avatar']){
       	 //  $data['avatar'] = $this->fast_upload_img($data['avatar']);
	    }else{
			$this->redirect(u('vote_mengbao/voteError',array('msg'=>'请上传图片')));exit;
		}
        $imgstr = '';
        $data['imgs'] = $this->_post('imgs','trim');
        foreach ($data['imgs'] as $key => $value) {
            if(!empty($value)){
	       //$value = $this->fast_upload_img($value);
                    $imgstr .=$value.'|'; 
            }
        }
        $data['imgs'] = $imgstr;  
        //判断年龄是否合适 3-16
		/*
        if($data['age'] < 3 || $data['age'] > 16){
            //年龄不符合 跳出;
			$this->redirect(u('vote_mengbao/voteError',array('msg'=>'年龄须在3~16岁之间')));
            exit;
        } 
		*/
		if(empty($data['tel'])){
			$this->redirect(u('vote_mengbao/voteError',array('msg'=>'手机号不能为空')));
            exit;
		}     
		

        // 判断是否已经报名
        $isjoin = M('vote_young')->where(array('status'=>1,'vote_item'=>2,'tel'=>$data['tel']))->getfield('id');
        //指定编号
        $count = M('vote_young')->where(array('status'=>1,'vote_item'=>2))->count('id');
        if($count<=8){
            $data['vote_id'] = '00'.($count+1);
        }
        if($count>8 && $count<=98){
            $data['vote_id'] = '0'.($count+1);
        }
        if($count>98){
            $data['vote_id'] = $count+1;   
        }
        if($isjoin){
            $this->redirect(u('vote_mengbao/voteError',array('msg'=>'已经报名了！')));
            exit;
        }else{
            $back_id = M('vote_young')->data($data)->add();            
            $this->redirect(u('vote_mengbao/voteSuccess',array('back_id'=>$back_id)));
            exit;   
        }
    }

    public function voteSuccess(){
        $this->display('success');
    }

     public function voteError(){
	 	$url = $this->_get('url','trim');
		$msg = $this->_get('msg','trim');
		$this->assign('redirecturl',$url);
		$this->assign('redirectmsg',$msg);
        $this->display('error');
    }

    public function fast_upload_img($base64){
        //64数据流
        if($base64) {
				
	    $base64 = str_replace('data:image/jpeg;base64,','',$base64);
                $datap=base64_decode($base64);
                (!$datap) && $this->ajaxReturn(51,'base64转码'.L('app_empt'));
                $rand_num = time().rand(1,10000);
                $head_picture='data/upload/avatar/vote_temp/'.$rand_num.'.jpg';
                $thumbWidth   = array('700');
                $thumbHeight  = array('700');
                $thumbSuffix  = array('_700x700');
                //$head_picture64='data/upload/avatar/'.$avatar_dir.md5($uid).$rand.'_64.jpg';
                file_put_contents($head_picture,$datap);//将得到的二进制码写入图片文件中
                $fdfs_obj = new FastFile();
                $tracker  = fastdfs_tracker_get_connection();
                if(!fastdfs_active_test($tracker)){
                    error_log("errno: " . fastdfs_get_last_error_no() . ", error info: " . fastdfs_get_last_error_info());
                    exit(1);
                }
                $storage = fastdfs_tracker_query_storage_store();
                    if(!$storage){
                    error_log("errno: " . fastdfs_get_last_error_no() . ", error info: " . fastdfs_get_last_error_info());
                    exit(1);
                }                
                
                
                //先默认上传一个原图
                $res = fastdfs_storage_upload_by_filename($head_picture, null, array(), null, $tracker, $storage);
				
                
                $format      = explode('.',$head_picture);//文件格式后缀 *.jpg
                $info        = $fdfs_obj->getImageInfo($head_picture);//文件信息
                //缩略图
                foreach($thumbWidth as $key=>$val){
                    $fdfs_obj->_render_thumbnail(file_get_contents($head_picture),$info,$thumbWidth[$key],$thumbHeight[$key],$res['group_name'],$res['filename'],$thumbSuffix[$key],end($format));
                }
                $img_gs = $res['group_name'].'/'.$res['filename']; 
                unlink($head_picture);    
                $fdfs_obj->fast_del_img($img_gs);            
                $img_700 = str_replace('.','_700x700.',$img_gs);
                $img_700 = C('img_url').$img_700;
                return $img_700;
        }
        //数据流
    }

}