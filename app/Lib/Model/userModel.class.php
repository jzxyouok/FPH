<?php

class userModel extends Model
{
    protected $_validate = array(
        array('username', 'require', '{%username_require}'), //不能为空
        //array('repassword', 'password', '{%inconsistent_password}', 0, 'confirm'), //确认密码
        //array('email', 'email', '{%email_error}'), //邮箱格式
        //array('username', '1,20', '{%username_length_error}', 0, 'length', 1), //用户名长度
        //array('password', '6,20', '{%password_length_error}', 0, 'length', 1), //密码长度
        //array('username', '', '{%username_exists}', 0, 'unique', 1), //新增的时候检测重复
    );

    protected $_auto = array(
        array('password','md5',1,'function'), //密码加密
        array('reg_time','time',1,'function'), //注册时间
        array('reg_ip','get_client_ip',1,'function'), //注册IP
    );

    /**
     * 修改用户名
     */
    public function rename($map, $newname) {
        if ($this->where(array('username'=>$newname))->count('id')) {
            return false;
        }
        $this->where($map)->save(array('username'=>$newname));
        $uid = $this->where(array('username'=>$newname))->getField('id');
        //修改商品表中的用户名
        M('item')->where(array('uid'=>$uid))->save(array('uname'=>$newname));
        //修改专辑表中的用户名
        M('album')->where(array('uid'=>$uid))->save(array('uname'=>$newname));
        //评论和微薄暂时不修改。
        return true;
    }

    public function name_exists($name, $id = 0) {
        $where = "username='" . $name . "' AND id<>'" . $id . "'";
        $result = $this->where($where)->count('id');
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

     public function mobile_exists($mobile, $id = 0) {
        $where = "mobile='" . $mobile . "' AND id<>'" . $id . "'";
        $result = $this->where($where)->count('id');
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function email_exists($email, $id = 0) {
        $where = "email='" . $email . "' AND id<>'" . $id . "'";
        $result = $this->where($where)->count('id');
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
	
	//会员基本信息
	public function user_info($uid){
		$data = M('user')->field('id,username,mobile,stores_id,city_id')->where(array('id'=>$uid))->find();
		return $data;
	}
	
	//跟进手机号码查找用户基本信息
	public function user_mobile_info($mobile){
		$data = M('user')->field('id,username,mobile,stores_id')->where("mobile='".$mobile."'")->find();
		return $data;
	}

    //读取C端注册用户列表
    public function member_index($mobile, $username, $city_id, $brokerMobile, $start_time, $end_time, $status){

        $fph = C('db_prefix');
        $where = '(status=1 OR status=3)';
        if($status != ''){
            $where = 'status = '.$status;
        }
        /*if($mobile || $username){
            $where = '(status=1 OR status=0 OR status=2 OR status=3)';
        }*/
        if($mobile){
            $where .= " AND mobile = '".$mobile."'";
        }
        if($username){
            $where .= " AND id IN ( SELECT uid FROM {$fph}member_extend WHERE username like '%".$username."%' )";
        }
        if($brokerMobile){
            M('member_extend','fph_',C('DB_member'));
            $brokerMobileUid = M('member_extend')->field('uid')->where("broker_mobile = '".$brokerMobile."'")->select();
            $brokerMobileIds = '';
            foreach($brokerMobileUid as $key => $val){
                $brokerMobileIds .= $val['uid'].',';
            }
            $brokerMobileIds = substr($brokerMobileIds, 0, -1);
            $brokerMobileIds = $brokerMobileIds ? $brokerMobileIds : 0;
            $where .= " AND id IN ($brokerMobileIds)";
        }
        if($start_time){
            $where .= " AND reg_time >= ".strtotime($start_time);
        }
        if($end_time){
            $where .= " AND reg_time < ".(strtotime($end_time)+86400);
        }
        if($city_id){
            M('city','fph_',C('DB_fangpinhui'));
            $idsArr = M('city')->field('id')->where('id = '.$city_id.' or spid RLIKE "[[:<:]]'.$city_id.'[[:>:]]"')->select();
            foreach($idsArr as $val){
                $ids .= $val['id'].',';
            }
            $ids = substr($ids,0,-1);
            if($ids){
                $where .=' AND city_id in('.$ids.')';
            }
            //查询所属城市
            $spid_city = M('city')->where(array('id'=>$city_id))->getField('spid');
            if($spid_city==0 ){
                $spid_city = $city_id;
            }else{
                $spid_city .= $city_id;
            }
        }
        /*if($status!=''){
            $where .= " AND status=".$status;
        }*/
        M(NULL,NULL,C('DB_member'));
        $count = M('member ')->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();
        $str = 'id,mobile,city_id,reg_time,last_time,status';
        $list = M('member')->field($str)->where($where)->order('id DESC')->limit($p->firstRow.','.$p->listRows)->select();
        foreach($list as $key=>$val){
            $member_extendinfo = M('member_extend')->field('username,origin,broker_mobile,invite_time')->where('uid='.$val['id'])->find();
            $list[$key]['username'] = $member_extendinfo['username'];
            $list[$key]['origin'] = $member_extendinfo['origin'];
            $list[$key]['broker_mobile'] = $member_extendinfo['broker_mobile'];
            $list[$key]['invite_time'] = $member_extendinfo['invite_time'];
        }
        M(NULL,'fph_',C('DB_fangpinhui'));
        foreach($list as $key=>$val){
            //所在城市
            if($val['city_id']){
                $spid = M('city')->where('id='.$val['city_id'])->getField('spid');
                $city_id = explode('|', $spid);
                if($city_id[1]){
                    $Province_name= M('city')->where('id='.$city_id[0])->getField('name');
                    $city_name = M('city')->where('id='.$city_id[1])->getField('name');
                }elseif(!$city_id[0]){
                    $Province_name= M('city')->where('id='.$val['city_id'])->getField('name');
                    $city_name = '';
                }else{
                    $Province_name= M('city')->where('id='.$city_id[0])->getField('name');
                    $city_name = M('city')->where('id='.$val['city_id'])->getField('name');
                }
                $list[$key]['city_name'] = $Province_name.$city_name;
            }
        }
        return array($list,$page,$spid_city);
    }

    //编辑C端注册用户
    public function member_edit($id){
        M(NULL,NULL,C('DB_member'));
        $data = M('member')->field('id,mobile,city_id,reg_time,last_time,status')->where(array('id'=>$id))->find();
        $member_extendinfo = M('member_extend')->field('username,invite_time,disable_reasons')->where('uid='.$id)->find();
        $data['username']        = $member_extendinfo['username'];
        $data['invite_time']     = $member_extendinfo['invite_time'];
        $data['disable_reasons'] = $member_extendinfo['disable_reasons'];
            if($data['city_id']){
                M(NULL,'fph_',C('DB_fangpinhui'));
                $spid_city = M('city')->where(array('id'=>$data['city_id']))->getField('spid');
                if( $spid_city==0 ){
                    $spid_city = $data['city_id'];
                }else{
                    $spid_city .= $data['city_id'];
                }
            }
        return array($data,$spid_city);
    }
    //添加C端注册用户
    public function member_edit_insert($username,$mobile,$city_id,$status,$reg_time){
        M('member','fph_',C('DB_member'));
        $data['mobile']       = $mobile;
        $data['city_id']        = $city_id;
        $data['status']        = $status;
        $data['reg_time']   = $reg_time;
        $result = M('member')->add($data);
        if($result){
            M('member_extend')->add(array('uid'=>$result,'username'=>$username,'origin'=>9,'update_time'=>$reg_time));
        }
        return array($result);
    }

    //判断用户是否存在
    public function MomberMobileCount($mobile){
        M('member','fph_',C('DB_member'));
        return M('member')->where("mobile='".$mobile."'")->count('id');
    }

    //更新C端注册用户
    public function member_edit_update($id, $username, $mobile, $city_id, $status, $disable_reasons){
        M(NULL,NULL,C('DB_member'));
        $data['mobile']   = $mobile;
        $data['city_id']  = $city_id;
        $data['status']   = $status;
        $result = M('member')->where(array('id'=>$id))->save($data);
        $data_extend['username']        = $username;
        $data_extend['update_time']     = time();
        $data_extend['disable_reasons'] = $disable_reasons;
        M('member_extend')->where(array('uid'=>$id))->save($data_extend);
        return array($result);
    }

    //删除C端注册用户*逻辑删除
    public function member_delete($id){
        M(NULL,NULL,C('DB_member'));
        $id_arr = explode(',',$id);
        foreach($id_arr as $val){
            $result = M('member')->where(array('id'=>$val))->save(array('status'=>2));
        }
        return $result;
    }

    //统计会员
    public function MemberCount(){
        M('member','fph_',C('DB_member'));
        //正常
        $MemberCount = M('member')->where('status=1')->count('id');
        //已删除
        $MemberCountDelete = M('member')->where('status=2')->count('id');
        //预注册
        $MemberCountReg = M('member')->where('status=3')->count('id');
        //异常
        $MemberCountUnusual = M('member')->where('status=0')->count('id');
        $start = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $end = mktime(23,59,59,date("m"),date("d"),date("Y"));
        $time_count = M('member')->where('reg_time BETWEEN '.$start.' AND '.$end.' AND status=1')->count('id');
        return array($MemberCount, $time_count, $MemberCountDelete, $MemberCountReg, $MemberCountUnusual);
    }

    public function importMember()
    {
        M('member','fph_',C('DB_member'));
        $data = array('reg_time' => time(), 'status' => 1);
        $id = M('member')->add($data);
        return $id;
    }

    public function importMemberextend($uid, $username)
    {
        M('member_extend','fph_',C('DB_member'));
        $data = array('uid' => $uid, 'username' => $username, 'gender' => rand(1, 2), 'origin' => 9);
        $id = M('member_extend')->add($data);
        return $id;
    }

    /**
     * 获取经纪人的ID
     */
    public function getBrokerList($where, $fields, $pageSize, $countNum, $page)
    {
        M('user','fph_',C('DB_fangpinhui'));
        import("ORG.Util.Page");
        $p = new Page($countNum, $pageSize);
        $list = M('user')->field($fields)->where($where)->order('id DESC')->limit($page*$pageSize.','.$pageSize)->select();
        return array($list, $page);
    }

    public function brokerNumCount($where)
    {
        M('user','fph_',C('DB_fangpinhui'));
        $count = M('user ')->where($where)->count('id');
        return $count;
    }

    public function getList($where, $fields)
    {
        M('user','fph_',C('DB_fangpinhui'));
        $data = M('user')->field($fields)->where($where)->select();
        return $data;
    }


    public function getInfo($where, $fields)
    {
        M('user','fph_',C('DB_fangpinhui'));
        $data = M('user')->field($fields)->where($where)->find();
        return $data;
    }



}