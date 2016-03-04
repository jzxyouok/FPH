<?php
class property_staffAction extends backendAction
{

    public function _initialize() {
        parent::_initialize();

    }

    /*
    *@Descriptions：团购/砍价
    *@Date:2014-07-21
    *@Author: chl
    */
    public function index() {
    	$p = $this->_get('p','intval',1);
        $pid = $this->_get('id','intval',1);

        $query = "garrison = 1";
        $garrison = D('property')->propertyStaffIndex($pid, $query);
        $this->assign('garrison',$garrison);

        $query = "bargain = 1";
        $bargain = D('property')->propertyStaffIndex($pid, $query);
        $this->assign('bargain',$bargain);

        $query = "principal = 1";
        $principal = D('property')->propertyStaffIndex($pid, $query);
        $this->assign('principal',$principal);


    	$this->assign('p',$p);
        $this->assign('id',$pid);
    	$this->display();
    }

    /*
   *@Descriptions：编辑楼盘团购
   *@Date:2014-07-03
   *@Author: chl
   */
    public function edit() {
        $id   = $this->_request('id','intval',1);
        $pid = $this->_request('pid','intval',1);

        if(IS_POST){
            $uid       = $this->_post('uid','intval');
            $garrison  = $this->_post('garrison','intval');
            $bargain   = $this->_post('bargain','intval');
            $principal = $this->_post('principal','intval');
            $status    = $this->_post('status','intval');
            $pid       = $this->_post('pid','intval');

            $data['uid']       = $uid;
            $data['garrison']  = $garrison;
            $data['bargain']   = $bargain;
            $data['principal'] = $principal;
            $data['status']    = $status;
            if(false !== D('property')->propertyStaffEdit($pid, $data)){
                $this->success('修改成功');
                exit;
            }else{
                $this->success('修改失败');
            }
        }

        $admin_list = D('admin')->admin_list();
        $this->assign('admin_list',$admin_list);

        $info = D('property')->propertyStaffInfo($pid);
        $this->assign('info',$info);

        $this->assign('id',$id);
        $this->assign('pid',$pid);
        $this->display();
    }

    /*
   *@Descriptions：添加楼盘团购
   *@Date:2014-07-03
   *@Author: chl
   */
    public function add() {
        $id = $this->_request('id','intval',1);


        if(IS_POST){
            $uid       = $this->_post('uid','intval');
            $garrison  = $this->_post('garrison','intval');
            //$bargain   = $this->_post('bargain','intval');
            $principal = $this->_post('principal','intval');
            $pid       = $this->_post('pid','intval');
            $id        = $this->_post('id','intval');
            if(!$uid){
                $this->error('请选择用户');
            }
            if(!$garrison && !$principal){
                $this->error('至少选择一个权限');
            }
            //判断是否存在
            if($id){
                $data['garrison']  = $garrison;
                //$data['bargain']   = $bargain;
                $data['principal'] = $principal;
                if(false !== D('property')->propertyStaffEdit($id, $data)){
                    $this->success('修改成功');
                    exit;
                }else{
                    $this->success('修改失败');
                }
            }
            $data['uid']       = $uid;
            $data['garrison']  = $garrison;
            //$data['bargain']   = $bargain;
            $data['principal'] = $principal;
            $data['status']    = 1;
            $data['pid']       = $pid;
            $data['add_time']  = time();
            if(false !== D('property')->propertyStaffAdd($data)){
                $this->success('添加成功');
                exit;
            }else{
                $this->error('添加失败');
            }
        }

        $admin_list = D('admin')->admin_list();
        $this->assign('admin_list',$admin_list);

        $this->assign('id',$id);
        $this->display();
    }

    //删除
    public function delete(){
        if(IS_AJAX){
            $id   = $this->_get('id','intval');
            $type = $this->_get('type','intval');
            if(false !== D('property')->propertyStaffDelete($id, $type)){
                $this->ajaxReturn(1, '删除成功');
            }else{
                $this->ajaxReturn(0, '删除成功');
            }
        }
    }

    //ajax
    public function ajaxStaffInfo(){
        if(IS_AJAX){
            $uid = $this->_post('uid','intval');
            $pid = $this->_post('pid','intval');
            $data = D('property')->ajaxStaffInfo($uid, $pid);
            if($data){
                $this->ajaxReturn(1, '成功', $data);
            }else{
                $this->ajaxReturn(0, '无数据');
            }
        }
    }


}