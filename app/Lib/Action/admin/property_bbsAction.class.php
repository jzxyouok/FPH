<?php
class property_bbsAction extends backendAction
{
    /*
    *@Descriptions：楼盘评论
    *@Date:2014-07-03
    *@Author: chl
    */
    public function index() {
    	$fph = C('DB_PREFIX');

    	$p = $this->_get('p','intval',1);
        $pid = $this->_get('id','intval',1);

        $list = D('property')->property_bbs_index($pid);
        $this->assign('list', $list[1]);
        $this->assign('page',$list[0]);


    	$this->assign('p',$p);
        $this->assign('id',$pid);
    	$this->display();
    }

    /*
   *@Descriptions：编辑楼盘评论
   *@Date:2014-07-03
   *@Author: chl
   */
    public function edit() {
        $id   = $this->_request('id','intval',1);
        $pid = $this->_request('pid','intval',1);

        if(IS_POST){
            $img              = $this->_post('img','trim');
            foreach ($_POST as $key=>$val) {
                $_POST[$key] = htmltag($val);
            }
            $info    = $this->_post('info','trim');
            $status = $this->_post('status','trim');
            $origin = $this->_post('origin','trim');
            $img     = implode(',', $img);
            if(!$info && $origin==9){
                $this->error('请填写评论信息',U('property_bbs/edit',array('id'=>$id,'pid'=>$pid)));
            }
            if(false !== D('property')->property_bbs_undate($img,$info,$status,$pid,$origin)){
                $this->success('修改成功',U('property_bbs/edit',array('id'=>$id,'pid'=>$pid)));
                exit;
            }else{
                $this->success('修改失败',U('property_bbs/edit',array('id'=>$id,'pid'=>$pid)));
            }
        }

        $info = D('property')->property_bbs_info($pid);
        $this->assign('info',$info);

        $img_count = count($info['img']);
        $this->assign('img_count',$img_count);

        $this->assign('id',$id);
        $this->display();
    }

    /*
   *@Descriptions：添加楼盘评论
   *@Date:2014-07-03
   *@Author: chl
   */
    public function add() {
        $id   = $this->_request('id','intval',1);
        $pid = $this->_request('pid','intval',1);

        $propertyTitle = D('property')->propertyTitle($id);
        $this->assign('propertyTitle',$propertyTitle);

        if(IS_POST){
            $img              = $this->_post('img','trim');
            foreach ($_POST as $key=>$val) {
                $_POST[$key] = htmltag($val);
            }
            $uid              = $this->_post('uid','intval');
            $add_time    = $this->_post('add_time','trim');
            $info             = $this->_post('info','trim');
            $pid              = $this->_post('pid','intval');
            if(!$uid){
                $this->error('请选择发布人');
            }
            if(!$pid){
                $this->error('请选择评论楼盘');
            }
            if(!$add_time){
                $this->error('请选择发布时间');
            }else{
                $add_time = strtotime($add_time);
            }
            if(!$info){
                $this->error('请选择评论信息');
            }
            $img = implode(',', $img);
            if(false !== D('property')->property_bbs_add($uid,$add_time,$info,$pid,$img)){
                $this->success('提交成功');
                exit;
            }else{
                $this->success('提交失败');
            }
        }

        $user_list = D('property')->user_list();
        $this->assign('user_list',$user_list);



        $this->assign('p',$p);
        $this->assign('id',$id);
        $this->assign('time',date('Y-m-d H:i:s'));
        $this->display();
    }

    /*
  *@Descriptions：删除评论图
  *@Date:2014-07-03
  *@Author: chl
  */
    public function del_edit_img(){
        $img = $this->_post('img','trim',1);
        $this->ajaxReturn(1, '删除成功');
    }

    /*
 *@Descriptions：查看评论回复
 *@Date:2014-07-03
 *@Author: chl
 */
    public function reply_list(){
        $id   = $this->_request('id','intval',1);
        $pid = $this->_request('pid','intval',1);
        $p    = $this->_get('p','intval',1);

        $data = D('property')->reply_list($pid);
        $this->assign('comment_info',$data[0]);
        $this->assign('list',$data[1]);
        $this->assign('page',$data[2]);

        $this->assign('id',$id);
        $this->assign('pid',$pid);
        $this->assign('p',$p);
        $this->display();
    }

    //编辑评论回复
    public function reply_list_edit(){
        $id   = $this->_request('id','intval',1);
        $pid = $this->_request('pid','intval',1);

        if(IS_POST){
            $status = $this->_post('status','trim');
            $updateWhere = 'id = '.$pid;
            $updateData['status'] = $status;
            if(false !== D('property')->property_reply_undate($updateWhere,$updateData)){
                $this->success('修改成功',U('property_bbs/reply_list_edit',array('id'=>$id,'pid'=>$pid)));
                exit;
            }else{
                $this->success('修改失败',U('property_bbs/reply_list_edit',array('id'=>$id,'pid'=>$pid)));
            }
        }

        $info = D('property')->reply_list_info($pid);
        $this->assign('info',$info);

        $this->assign('id',$id);
        $this->assign('pid',$pid);
        $this->display();
    }

    public function ajax_upload_img() {
        //上传图片 logo
        if (!empty($_FILES['img']['name'])) {

            $fdfs_obj = new FastFile();
            $result = $fdfs_obj->fdfs_upload('img','500','500','_500x500',true);
            if($result){
                $ext = $result['group_name'].'/'.$result['filename'];
                $savename = str_replace('.', '_500x500.', $ext);
                $this->ajaxReturn(1, L('operation_success'), $savename);
            }else{
                $this->ajaxReturn(0, '上传图片出错');
            }
        } else {
            $this->ajaxReturn(0, L('illegal_parameters'));
        }
    }

    //删除图片
    public function del_img(){
        $img_path = $this->_post('img_path', 'trim');
        $fdfs_obj = new FastFile();
        $result = $fdfs_obj->fast_del_img($img_path);

        //if($result){
            $this->ajaxReturn(1, '删除成功');
        //}else{
            //$this->ajaxReturn(0, '删除失败');
        //}
    }



}