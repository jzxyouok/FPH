<?php
class memberAction extends backendAction
{

    public function index() {
        $brokerMobile = $this->_get('broker_mobile','trim');
        $mobile       = $this->_get('mobile','trim');
        $username     = $this->_get('username','trim');
        $status       = $this->_get('status','trim');
        $start_time   = $this->_get('start_time','trim');
        $end_time     = $this->_get('end_time','trim');
        $city_id      = $this->_get('city_id','trim');
        $list = D('user')->member_index($mobile, $username, $city_id, $brokerMobile, $start_time, $end_time, $status);
        //print_r($list[0]);
        $this->assign('list',$list[0]);
        $this->assign('page',$list[1]);
        $this->assign('selected_ids_city',$list[2]);

        $MemberCount = D('user')->MemberCount();
        $this->assign('MemberCount',$MemberCount);

        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);

        $this->assign('search', array(
            'mobile' => $mobile,
            'username' => $username,
            'status' => $status,
            'city_id' => $city_id,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'broker_mobile' => $brokerMobile,
        ));
        $this->assign('list_table',true);
        $this->display();
    }

    //添加用户
    public function add(){
        if(IS_POST){
            foreach ($_POST as $key=>$val) {
                $_POST[$key] = htmltag($val);
            }
            $username = $this->_post('username','trim');
            $mobile      = $this->_post('mobile','trim');
            $city_id       = $this->_post('city_id','intval');
            $status        = $this->_post('status','intval');
            $reg_time    = time();

            $MemberCount = D('user')->MomberMobileCount($mobile);
            if($MemberCount){
                $this->error('相同的手机号码已经存在！');exit;
            }

            if(false !== D('user')->member_edit_insert($username,$mobile,$city_id,$status,$reg_time)){
                $this->success('添加成功');
                exit;
            }else{
                $this->error('添加失败');
            }
        }
        $this->display();
    }

    //编辑用户
    public function edit(){
        //$id = $this->_request('id','intval');
        $id = $this->_get('id','intval');
        $this->assign('id',$id);
        if(IS_POST){
            foreach ($_POST as $key=>$val) {
                $_POST[$key] = htmltag($val);
            }
            $id              = $this->_post('id','intval');
            $username        = $this->_post('username','trim');
            $mobile          = $this->_post('mobile','trim');
            $city_id         = $this->_post('city_id','intval');
            $status          = $this->_post('status','intval');
            $disable_reasons = $this->_post('disable_reasons','trim');
            if($status == 1){
                $disable_reasons = '';
            }
            if(false !== D('user')->member_edit_update($id, $username, $mobile, $city_id, $status, $disable_reasons)){
                $this->success('修改成功');
                exit;
            }else{
                $this->error('修改失败');
            }
        }
        $info = D('user')->member_edit($id);
        $this->assign('info',$info[0]);
        $this->assign('selected_ids_city',$info[1]);
        $this->display();
    }

    //删除用户
    public function delete(){
        $id = $this->_get('id','trim');
        !$id && $this->ajaxReturn(0, L('operation_failure'));
        if(false !== D('user')->member_delete($id)){
            $this->ajaxReturn(1, '删除成功');
        }else{
            $this->ajaxReturn(0, L('operation_failure'));
        }

    }

    //导入客户
    public function import(){
        if(IS_POST){
            if($_FILES['import']['type'] != 'text/plain') {
                $this->error('文件类型错误');
                exit;
            }
            $flag = 0;
            if(is_uploaded_file($_FILES['import']['tmp_name'])){
                $root_dir ="data/runtime/";
                $filename =  $_FILES['import']['name'];
                $uploadFile = $root_dir . $filename;
                if (move_uploaded_file($_FILES['import']['tmp_name'], $uploadFile)) {
                    $handle = fopen($uploadFile, "r");//读取二进制文件时，需要将第二个参数设置成'rb'
                    while(!feof($handle)) {
                        $username =  trim(iconv("GB2312","UTF-8//IGNORE",fgets($handle)));
                        if($username){
                            //insert table Member
                            $uid = D('user')->importMember();
                            //insert table member_extend
                            $id = D('user')->importMemberextend($uid, $username);
                            //echo $username . ' 导入成功<br/>';
                            $flag++;
                        }
                    }
                    fclose($handle);
                    unlink($uploadFile);
                }
                $this->success('成功导入' .$flag. '条数据');
            }
        }

        $this->display();
    }

    /**
     * 现金记录
     */

    public function cash(){
        $id = $this->_get('id','intval');
        $this->assign('id',$id);
        M('journal_account','fph_',C('DB_log'));
        $journalAccountModel = M('journal_account');
        $where = "uid=$id and table_type != 2 and type not in(5,10,12,14,16)";
        $dataList = $journalAccountModel->where($where)->select();
        foreach($dataList as $key => $val)
        {
            switch($val['table_type'])
            {
                // 领取路费
                case 1:
                    M('receive','fph_',C('DB_member'));
                    $pid = D('receive')->getPid($val['sid']);
                    if($pid)
                    {
                        M('property','fph_',C('DB_fangpinhui'));
                        $dataList[$key]['title'] = D('property')->findPropertyField(array('id'=> $pid), 'title');
                    }
                    break;
                //新人注册奖
                case 3:
                    $dataList[$key]['title'] = '新人注册奖';
                    break;
                //预约奖励
                case 4:
                    M('join_reservation','fph_',C('DB_member'));
                    $pid = D('join_reservation')->getOneField(array('id' => $val['sid']), 'pid');
                    if($pid)
                    {
                        M('property','fph_',C('DB_fangpinhui'));
                        $dataList[$key]['title'] = D('property')->findPropertyField(array('id'=> $pid), 'title');
                    }
                    break;
                case 6:
                    if($val['type'] == 2){
                        $dataList[$key]['title'] = '房展会兑换高档水果礼盒';
                        $dataList[$key]['journal_account'] = '<span style="color:red;font-weight:600;font-size:15px;">- ' . $val['journal_account'] .'</span>';
                    } elseif ($val['type'] == 17){
                        $dataList[$key]['title'] = '橙动天津兑换水果优惠券';
                        $dataList[$key]['journal_account'] = '<span style="color:red;font-weight:600;font-size:15px;">- ' . $val['journal_account'] .'</span>';
                    } else {
                        unset ($dataList[$key]);  //如果没有不显示
                    }
                    break;
            }

            switch($val['type'])
            {
                case 9:
                    $dataList[$key]['title'] = '新人礼包';
                    break;
                case 11:
                    $dataList[$key]['title'] = '首次分享';
                    break;
                case 13:
                    $dataList[$key]['title'] = '路费分享';
                    break;
                case 15:
                    $dataList[$key]['title'] = '邀请好友';
                    break;
                case 18:
                    $dataList[$key]['title'] = '邀请看房';
                    break;
            }
            if($val['city_id']){
                //城市
                M('city','fph_',C('DB_fangpinhui'));
                $fieldCity = 'name,pid';
                $whereCity = 'id = '.$val['city_id'];
                $cityInfo  = D('city')->getCity($fieldCity, $whereCity);
                $dataList[$key]['city_name'] = $cityInfo['name'];
                //省
                if($cityInfo['pid']){
                    $fieldProvince = 'name';
                    $whereProvince = 'id = '.$cityInfo['pid'];
                    $provinceInfo  = D('city')->getCity($fieldProvince, $whereProvince);
                    $dataList[$key]['province_name'] = $provinceInfo['name'];
                }
            }
        }
        $this->assign('list',$dataList);
        $this->display();
    }

    /**
     * 基金记录
     */

    public function fund(){
        $id = $this->_get('id','intval');
        $this->assign('id',$id);
        M('giftbag_record','fph_',C('DB_giftbag'));
        $notUsed = M('giftbag_record')->where(array('uid'=>$id,'status'=>0))->select();
        if($notUsed){
            foreach($notUsed as $val){
                if(strtotime("+6 month",$val['record_time'])<time()){
                    M('giftbag_record')->where(array('id'=>$val['id']))->save(array('status'=>2));
                }
            }
        }
        $count = M('giftbag_record')->where(array('uid'=>$id))->count();
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();
        $receiveList = M('giftbag_record')->where(array('uid'=>$id))->limit($p->firstRow.','.$p->listRows)->select();
        foreach($receiveList as $k=>$val){
            $list[$k]['id'] = $val['id'];
            $list[$k]['add_time'] = $val['record_time'];
            $list[$k]['journal_account'] = $val['fund'];
            $list[$k]['by_time'] = strtotime("+6 month",$val['record_time']);
            $list[$k]['remark'] = $val['remark']?$val['remark']:'';
            $couponList = M('coupon_giftbag')->field('tid')->where(array('id'=>$val['gid']))->find();
            $giftbagType = M('giftbag_type')->where(array('id'=>$couponList['tid']))->find();
            if($giftbagType['id']==2){
                M('member','fph_',C('DB_member'));
                $member = M('member')->field('mobile')->where(array('id'=>$val['bid']))->find();
                $list[$k]['remark'] = $member['mobile'];
            }
            $list[$k]['title'] = $giftbagType['name'];
            $list[$k]['status'] = $val['status'];
        }
        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->display();
    }

    /**
     * 提现记录
     */

    public function withdraw(){
        $id = $this->_get('id','intval');
        $this->assign('id',$id);
        $list = D('commission')->withdraw_cash($id);
        $this->assign('list',$list[0]);
        $this->assign('page',$list[1]);
        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);
        $this->assign('list_table',true);
        $this->display();
    }

    /**
     * 兑现
     */
    public function editCash(){
        $id = $this->_get('id','intval');
        M('giftbag_record','fph_',C('DB_giftbag'));
        $result = M('giftbag_record')->where(array('id'=>$id))->save(array('status'=>1,'use_time'=>time()));
        if($result){
            $this->success('操作成功');exit;
        }else{
            $this->error('操作失败');exit;
        }
    }

    /**
     * 撤销
     */
    public function editRevoke(){
        $id = $this->_get('id','intval');
        M('giftbag_record','fph_',C('DB_giftbag'));
        $result = M('giftbag_record')->where(array('id'=>$id))->save(array('status'=>0,'use_time'=>0));
        if($result){
            $this->success('操作成功');exit;
        }else{
            $this->error('操作失败');exit;
        }
    }

    //打款
    public function play(){
        $id = $this->_request('id','intval');
        if(IS_POST){
            $id   = $this->_post('id','intval');
            $pid = $this->_post('pid','intval');
            if($pid!=2 && $pid!=3){
                $this->ajaxReturn(0, '系统参赛出错,提交失败');
            }
            $data = D('commission')->withdraw_cash_update($id,$pid);
            $this->ajaxReturn($data[0], $data[1]);
        }
        $info = D('commission')->withdraw_cash_edit($id);

        $this->assign('info',$info);
        $this->display();
    }

    /**
     * 活动记录
     * mihailong
     */
    public function activity_record(){
        $uid = $this->_request('id','intval');
        $this->assign('id',$uid);
        M('action','fph_',C('DB_activity'));
        $actionList = M('action') ->field('aid,add_time') -> where("uid = $uid") ->select();

        if($actionList){
            foreach($actionList as $k => $val){
                $activityList = M('activity') ->field('title,intro') -> where("id = ".$val['aid'])-> find();
                $list[$k]['id']         = $val['aid'];
                $list[$k]['add_time']   = $val['add_time'];
                $list[$k]['title']      = $activityList['title'];
                $list[$k]['intro']       = $activityList['intro'];
            }
            $this->assign('list',$list);
        }
        $this->display();
    }

}