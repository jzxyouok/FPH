<?php
class vote_youngAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('vote_young');
    }

    public function index() {

        $clickfans     = $this->_get('clickfans','intval');
        $vote_personal   = $this->_get('vote_personal','trim');

        $where = '1';
        if($clickfans ==1){
            $order = ' clickfans desc,'; //1代表从高到底  2 代表从低到高
        }else{
            $order = ' clickfans asc,';
        }
        if($vote_personal!=''){
            $where .= " AND vote_personal like '%".$vote_personal."%'";
        }
        $order .= 'vote_id asc';
        $count = $this->_mod->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();
        $list = $this->_mod->field('id,vote_personal,vote_id,clickfans,avatar,touxian,hopeless,status')->where($where)->limit($p->firstRow.','.$p->listRows)->order($order)->select();  
            $this->_mod->getlastsql();             
        $this->assign('list', $list);
        $this->assign('page',$page);
        $this->assign('vote_personal',$vote_personal);
        $this->assign('clickfans',$clickfans);
        $this->display();
    }

    public function _before_add() {
       
    }

    public function _before_insert($data='') {
        $data['hopeless'] = $this->_post('hopeless','trim');
		
        return $data;
    }

    public function _before_edit() {
        $id = $this->_get('id','intval');
        
    }

    public function _before_update($data=''){
        
        return $data;
    }	
	

    //数据假删除 回收站铺垫
    public function ajax_delete(){
        $id = $this->_get('id','intval');
        if (false !== M('app_visitor_staff')->where(array('id'=>$id))->save(array('isdel'=>1))) {
            $this->ajaxReturn(1, '删除成功');
        }else{
            $this->ajaxReturn(0, '操作失败');
        }
    }
    //上传图片
    public function ajax_upload_img() {
        $type = $this->_get('type', 'trim', 'img');
        if (!empty($_FILES[$type]['name'])) {
			$fdfs_obj = new FastFile();
			$result = $fdfs_obj->fdfs_upload($type);
			if($result){
				$savename = $result['group_name'].'/'.$result['filename'];
				$this->ajaxReturn(1, L('operation_success'), $savename);
			}else{
				 $this->ajaxReturn(0, L('illegal_parameters'));
			}
            /*$dir = date('ym/d/');
            $result = $this->_upload($_FILES[$type], 'vote_young/'. $dir );
            if ($result['error']) {
                 $this->ajaxReturn(0, $result['info']);
            } else {
                $savename = $dir . $result['info'][0]['savename'];
                $this->ajaxReturn(1, L('operation_success'), $savename);
            }*/
        } else {
            $this->ajaxReturn(0, L('illegal_parameters'));
        }
    }

    //删除图片
    public function del_img(){
        $img_path = $this->_post('img_path', 'trim');
		$fdfs_obj = new FastFile();
		if($img_path){
			$result = $fdfs_obj->fast_del_img($img_path);
			if($result){
				$this->ajaxReturn(1, '删除成功');
			}else{
				$this->ajaxReturn(0, '删除失败');
			}
		}else{
			$this->ajaxReturn(0, L('illegal_parameters'));
		}
    }

    //活动设置
    public function settings(){
        $vote_activity = MODULE_NAME;
        $list = M('vote_settings')->where(array('activity'=>$vote_activity))->select();   
        foreach ($list as $key => $value) {
            # code...
            $list[$value['name']] = $value['data'];
            unset($list[$key]);
        }
        $list['vote_activity_time'] = explode('||',$list['vote_activity_time']);
        //print_r($list);
        if(IS_POST){
                
                $vote_activity_name = $this->_post('vote_activity_name',trim);
                $vote_activity_info = $this->_post('vote_activity_info',trim);
                $time_start = $this->_post('time_start',trim);
                $time_end = $this->_post('time_end',trim);
                if($time_start > $time_end){
                    $tmp = $time_start;
                    $time_start = $time_end;
                    $time_end = $tmp;
                }
                $vote_activity_time = strtotime($time_start).'||'.strtotime($time_end);
                $data = array(
                            '0' => array('activity'=>$vote_activity,'name'=>'vote_activity_name','data'=>$vote_activity_name),
                            '1' => array('activity'=>$vote_activity,'name'=>'vote_activity_info','data'=>$vote_activity_info),
                            '2' => array('activity'=>$vote_activity,'name'=>'vote_activity_time','data'=>$vote_activity_time),
                    );
                foreach($data as $key =>$val){
                        //判断是否有数据
                        $find = M('vote_settings')->where(array('name'=>$val['name'],'activity'=>$val['activity']))->find();
                        if(empty($find)){
                            M('vote_settings')->data($data[$key])->add();    
                        }else{
                            M('vote_settings')->where(array('name'=>$val['name'],'activity'=>$val['activity']))->data(array('data'=>$val['data']))->save();    
                        }
                        
                }
                $this->success('操作成功！');
        }
        $this->assign('list',$list);
        $this->display();
    }

}