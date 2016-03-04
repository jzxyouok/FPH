<?php
class baobeiAction extends weixin_userbaseAction {

    public function index() {
        ini_set('date.timezone','Asia/Shanghai');
        $id =  $this->_get('id','intval');
		$fph=C('DB_PREFIX');
		$data['A.id'] = array('eq',$id);
		$data['B.term_end'] = array('EGT',time());
		$data['B.term_start'] = array('ELT',time());
		$info = M('property')->field('A.id,A.title')->table("{$fph}property AS A LEFT JOIN {$fph}property_cooperation AS B ON A.id=B.pid")->where($data)->find();
        if(!$info){
            $this->error('您输入的不是合作楼盘');exit;
        }

        $uid = $this->visitor->info['id'];
        $this->assign('uid', $uid);
        $this->assign('info', $info);
	    $this->assign('setTitle', '选择带看类型');
        $this->_config_seo();
        $this->display();
    }
    public function info() {
        if(IS_POST){
            $data['property'] =  $this->_post('property','intval');
            $data['uid']       =  $this->visitor->info['id'];
            $data['with_look'] =  $this->_post('with_look','intval');
            if(!$data['property']){
                $this->error('请输入文章内容');exit;
            }
            if(!$data['uid']){
                $this->error('报备者不能为空');exit;
            }
            if(!$data['with_look']){
                $this->error('请选择带看类型');exit;
            }
            //判断带看,委托 次数
            $fph=C('DB_PREFIX');
            $databaobei['A.uid'] = $data['uid'];
            $databaobei['A.add_time'] =  array('between',array(strtotime(date('Y-m-d')),strtotime(date('Y-m-d 23:59:59'))));
            $countbaobei = M('myclient_property')->table("{$fph}myclient_property AS A LEFT JOIN {$fph}myclient AS B ON A.pid=B.id")->where($databaobei)->count('B.id');
            if($countbaobei>=C('pin_daikan_views')){
                    $this->error('您今天已报备了'.C('pin_daikan_views').'次，达到了每日上限，请明天再来吧');exit;
             }
            $info = M('property')->field('title')->where(array('id'=>$this->_post('property','intval')))->find();
            $data['title'] =  $info['title'];
            $this->assign('data', $data);
            $this->assign('setTitle', '客户报备');
            $this->_config_seo();
            $this->display();
        }


    }
    public function add() {
        //以下注释的代码为一期,二期改版注释,如长期不用可删除 CHL
		if(IS_POST){
            $gettime            = time();
            $uid                = $this->visitor->info['id'];
            $data['name']       =  $this->_post('name','trim');
            //$data['gender']   =  $this->_post('gender','intval');
            $data['mobile']     =  $this->_post('mobile','trim');
            $datap['uid']       =  $uid;
            $datap['property']  =  $this->_post('property','trim');
            $datap['with_look'] =  1;
            $datap['add_time']  =  $gettime;
            $time = strtotime(date("Y-m-d",$gettime));
            /*if($datap['with_look']==1){
                //带看有效期
			    $datap['visit_time'] = strtotime($this->_post('visit_time','trim'));
                if($datap['visit_time'] <$time){
                    $this->ajaxReturn(0,'到访时间不能小于当天时间');
                }
            }*/
            if(!$datap['uid']){
                $this->ajaxReturn(0,'请先登录,再报备');
	        }
            if(!checkusername($data['name'])){
                    $this->ajaxReturn(0,'客户姓名填写错误');
            }
            if(!checkMobile($data['mobile'])){
                $this->ajaxReturn(0,'手机号码填写错误');
	        }
            if(!$datap['property']){
                $this->ajaxReturn(0,'请选择所要报备的楼盘');
	        }
       		/*if(!$datap['with_look']){
                $this->ajaxReturn(0,'请选择带看类型');
	        }
            if($datap['with_look']==1){
                if(!$datap['add_time']){
                    $this->ajaxReturn(0,'请选择日期');
                }
            }*/
            $myclient_id = M('myclient')->field("id")->where(array('mobile'=>$data['mobile']))->find();
            $myclientid = $myclient_id['id'];
            $info = M('property')->field('title,protection_time_status,protection_time')->where(array('id'=>$datap['property']))->find();
            $title =  $info['title'];
            $str = 'id,pid,property,with_look,status,add_time,status_cid,protection_expire,look_expire';
            $mproperty = M('myclient_property')->field($str)->where(array('property'=>$datap['property'],'pid'=>$myclientid))->order('id DESC')->find();

            /*
             * 判断是否有流程正在进行
             */
            $is_ok	= FALSE;
            if(empty( $mproperty ))																$is_ok	= TRUE;	//第一次报备
            else if($mproperty['status_cid'] == 0)												$is_ok	= TRUE; //流程已终止
            else if($mproperty['status'] == 1 && $mproperty['protection_expire'] < $gettime)	$is_ok	= TRUE; //已经报备失效
            else if($mproperty['status'] == 3 && $mproperty['look_expire'] < $gettime)			$is_ok	= TRUE; //已经带看失效

            if( $is_ok == FALSE ){
					$this->ajaxReturn(0,'该客户已被其他经纪人报备此楼盘.');
             }else{
				//报备过期日
             	$protection_time			= empty( $info['protection_time_status'] ) ? C('pin_protection_time') : $info['protection_time'];	//报备有效天数
             	$protection_expire			= strtotime(date('Y-m-d 23:59:59') . " " . ($protection_time-1) . " days");	//报备过期时间
             	$datap['protection_expire']	= $protection_expire;
                $datap['protection_time']	= C('pin_protection_time');

                //判断用户是否存在
                if(!$myclient_id){
                    $last_id = M('myclient')->add($data);
                    $datap['pid'] =  $last_id;
                }else{
                    M('myclient')->where(array('mobile'=>$data['mobile']))->save(array('name' => $data['name']));
                    $datap['pid'] =  $myclientid;
                }
                $mpid = M('myclient_property')->add($datap);
                //向我的客户-附表 状态表录入信息
                $datas['mpid']       =  $mpid;
                $datas['pid']        =  $datap['property'];
                $datas['status']     =  1;
                $datas['status_cid'] =  1;
                $datas['with_look']  =  $datap['with_look'];
                $datas['add_time']   =  $gettime;
                /*if($datap['with_look']==1){
                    $datas['visit_time'] = strtotime($this->_post('visit_time','trim'));
                }*/
                if(false !== M('myclient_status')->add($datas)){
					$datap['mpid'] =  $mpid;
					//发送短信提醒****
					$mobile = M('user')->where('id ='.$datap['uid'])->getField('mobile');

					//是否请求接口
					$send_sms = D('send_sms');
					$result   = $send_sms->Messages($mobile,$agent_mobile,$agent_name,$client_name,$data['mobile'],$title,$str,$mobile_code,'1',false,1,$mobile_code_origin);
					 /* if($data['gender']==1){
							$data['sex'] = '男';
					  }elseif($data['gender']==0){
							$data['sex'] = '女';
					  }*/
					  //****
					$this->chit($datap,$title,$data);
					$this->ajaxReturn(1,'您的客户信息已经成功提交！房品汇客服经理正与开发商进行客户有效性确认。请保持手机畅通，我们的客服经理确认后会与您电话联系。');
				}else{
					$this->ajaxReturn(0,'报备失败');
				}
            }

        }
            $daikanstr =  $this->_get('daikanstr','trim');
            $title =  $this->_get('title','trim');
            $this->assign('title', $title);
            $this->assign('daikanstr', $daikanstr);
            $this->assign('setTitle', '客户报备');
            $this->_config_seo();
            $this->display();
    }

    //报备成功后发送给案场负责人
    public function chit($arr,$title,$data)
    {
	   $case = M('case_field')->field('admin_id,sms_mobile')->select();

	foreach($case as $v)
	{
		if(in_array($arr['property'],explode(",",$v['sms_mobile'])))
		{
		    $user = M('user')->where('id ='.$arr['uid'])->find();
            if($user['gender']==1){
                $user['sex'] = '男';
            }elseif($user['gender']==0){
                $user['sex'] = '女';
            }
		    if($arr['with_look'] ==1){
    			//带看申请成功
    			$str = "由我带看";
    		    }elseif($arr['with_look'] ==2){
    			$str = "委托带看，到访时间无";
		    }
		    //查询admin表id对应mobile
            $admin_mobile = M('admin')->where('id='.$v['admin_id'])->getField('mobile');
		    $send_sms = D('send_sms');
		    $send_sms->Messages($admin_mobile,$user['mobile'],$user['username'],$data['name'],$data['mobile'],$title,$str,$mobile_code,'2',false,1,$mobile_code_origin);

            //给案场的人推送报备成功的消息
            $app_push = D('app_push');
            $arr_data=array();
            $arr_data['muclient_name']=$data['name'];
            $arr_data['muclient_mobile']=$data['mobile'];
            $arr_data['agent_name']=$user['username']."(".$user['sex'].")";
            $arr_data['agent_mobile']=$user['mobile'];
            $info="已有经纪人".$user['username']."，".$user['mobile']."于".date('Y-m-d H:i',time())."成功报备客户".$data['name']."，".$data['mobile']."至".$title."，报备性质：".$str."，请及时联系。" ;
            $app_push->push_case($info,$v['admin_id'],$arr['mpid'],$arr['with_look'],$arr_data);

		}
	}
    }




}