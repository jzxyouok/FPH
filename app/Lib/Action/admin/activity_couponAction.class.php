<?php
class activity_couponAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->RedisDataBase= C('DB_REDIS_ACTIVITY_COUPON');
    }

    public function index() {
        M(NULL,NULL,C('DB_activity'));

        $type = $this->_get('type','intval');
        $where = '1 = 1';
        if($type){
            $where .= ' AND type = '.$type;
        }

        $count = M('coupon')->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();
        $list = M('coupon')->where($where)->limit($p->firstRow.','.$p->listRows)->order("add_time DESC")->select();
        foreach($list as $key => $val){
            $list[$key]['number'] = M('coupon_receive_records')->where(array('cid'=>$val['id']))->count('id');
            $list[$key]['total_money'] = $list[$key]['number'] * $val['money'];

        }
        $this->assign('list',$list);
        $this->assign('page',$page);


        $this->assign('search', array(
            'type' => $type,
        ));
        $this->assign('p',$p);
        $this->display();
    }


    public function add(){
        if(IS_POST){
            $type           = $this->_post('type','intval');
            $money       = $this->_post('money','trim');
            $time_start  = $this->_post('time_start','trim');
            $time_end   = $this->_post('time_end','trim');
            $status        = $this->_post('status','intval');

            if(!$type){
                $this->error('请选择优惠券类型');
            }
            if(!$money){
                $this->error('请输入金额');
            }
            if(!$time_start){
                $this->error('请选择开始时间');
            }
            if(!$time_end){
                $this->error('请选择结束时间');
            }
            if(strtotime($time_start) >= strtotime($time_end)){
                $this->error('开始时间不能大于结束时间');
            }
            M(NULL,NULL,C('DB_activity'));

            if($type==1){
                $str = '新人奖励';
            }elseif($type==2){
                $str = '首次看房';
            }

            if($status){
                $activity_count = M('coupon')->where('type = '.$type.' AND status = 1')->count('id');
                if($activity_count){
                    $this->error('系统中已经存在'.$str.'的有效活动');
                }
            }
            $data['type']           = $type;
            $data['money']       = $money;
            $data['time_start']  = strtotime($time_start);
            $data['time_end']   = strtotime($time_end);
            $data['status']        = $status;
            $data['add_time']   = time();
            if($return_id= D('coupon')->add($data)){
                if($status){
                    $data['id'] = $return_id;
                    unset($data['add_time']);
                    $redis = new CacheRedis($this->RedisDataBase);
                    $redis->handler->hmset('activity.coupon.'.$type.'',$data);
                }
                $this->success('提交成功');
                exit;
            }else{
                $this->success('提交失败');
            }
        }
        $this->display();
    }



    public function edit(){
        M(NULL,NULL,C('DB_activity'));
        $id = $this->_request('id','intval');

        $info = M('coupon')->where(array('id'=>$id))->find();
        $this->assign('info',$info);

        if(IS_POST){
            $type           = $this->_post('type','intval');
            //$money       = $this->_post('money','trim');
            $time_start  = $this->_post('time_start','trim');
            $time_end   = $this->_post('time_end','trim');
            $status        = $this->_post('status','intval');

            if(!$type){
                $this->error('请选择优惠券类型');
            }
            /*if(!$money){
                $this->error('请输入金额');
            }*/
            if(!$time_start){
                $this->error('请选择开始时间');
            }
            if(!$time_end){
                $this->error('请选择结束时间');
            }
            if(strtotime($time_start) >= strtotime($time_end)){
                $this->error('开始时间不能大于结束时间');
            }

            if($status){
                if($type==1){
                    $str = '新人奖励';
                }elseif($type==2){
                    $str = '首次看房';
                }
                $activity_count = M('coupon')->where('type = '.$type.' AND status = 1 AND id !='.$id.'')->count('id');
                if($activity_count){
                    $this->error('系统中已经存在'.$str.'的有效活动');
                }
            }
            $data['type']           = $type;
            //$data['money']       = $money;
            $data['time_start']  = strtotime($time_start);
            $data['time_end']   = strtotime($time_end);
            $data['status']        = $status;
            if(false !== D('coupon')->where('id = '.$id.'')->save($data)){
                $redis = new CacheRedis($this->RedisDataBase);
                if($status){
                    $data['id']    = intval($id);
                    $data['money'] = $info['money'];
                    $redis->handler->hmset('activity.coupon.'.$type.'',$data);
                }else{
                    $redis->rm('activity.coupon.'.$type.'');
                }
                $this->success('提交成功');
                exit;
            }else{
                $this->success('提交失败');
            }
        }

        $this->display();
    }


	//活动报名
	public function lists(){
        $id = $this->_get('id','intval');
        M(NULL,NULL,C('DB_activity'));

        $info = M('coupon')->field('type')->where(array('id'=>$id))->find();
        $this->assign('info',$info);


		$count = M('coupon_receive_records')->where(array('cid'=>$id))->count('id');
		import("ORG.Util.Page");
		$p = new Page($count, 20);
		$page = $p->show();
		$list = M('coupon_receive_records')->where(array('cid'=>$id))->limit($p->firstRow.','.$p->listRows)->order("add_time DESC")->select();
        foreach($list as $key => $val){
            $list[$key]['money'] = M('coupon')->where(array('id'=>$val['cid']))->getfield('money');
            M('member','fph_',C('DB_member'));
            $user_info = M('member')->field('mobile')->where(array('id'=>$val['uid']))->find();
            $user_extend = M('member_extend')->field('username,origin')->where(array('uid'=>$val['uid']))->find();
            $list[$key]['username'] = $user_extend['username'];
            $list[$key]['mobile']      = $user_info['mobile'];
            $list[$key]['origin']      = $user_extend['origin'];
            if($val['pid']){
                M('property','fph_',C('DB_fangpinhui'));
                $list[$key]['title'] = M('property')->where(array('id'=>$val['pid']))->getfield('title');
            }

        }
		$this->assign('list',$list);
		$this->assign('page',$page);
        $this->assign('count',$count);

		$p = $this->_get('p','intval',1);
        $this->assign('p',$p);
		$this->display();
	}
	

}