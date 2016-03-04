<?php
class vote_mengbaoAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('vote_young');
    }

    public function index() {

        $clickfans     = $this->_get('clickfans','intval');
        $vote_personal   = $this->_get('vote_personal','trim');

        $where = '1 and vote_item = 2';
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
        $list = $this->_mod->field('id,vote_personal,vote_id,clickfans,avatar,gender,age,status')->where($where)->limit($p->firstRow.','.$p->listRows)->order($order)->select();  
         //   $this->_mod->getlastsql(); 
         //print_r($list);            
        $this->assign('list', $list);
        $this->assign('page',$page);
        $this->assign('vote_personal',$vote_personal);
        $this->assign('clickfans',$clickfans);
        $this->display();
    }

    public function add(){
        if(IS_POST){
            $data = array(
                    'vote_personal' => $this->_post('vote_personal','trim'),
                    'vote_id' => $this->_post('vote_id','trim'),
                    'age' => $this->_post('age','intval'),
                    'gender' => $this->_post('gender','intval'),
                    'description' => $this->_post('description','trim'),
                    'vote_item' => $this->_post('vote_item','intval'),
                );
            $data['avatar'] = '/static/css/default/weixin/vote_mengbao/img/head/h3.jpg';
            $res = M('vote_young')->data($data)->add();
            exit;
            $this->success('操作成功！');
        }
            
        $this->display();
    }
    
    public function _before_edit() {
        $id = $this->_get('id','intval');
        
    }

    public function edit() {
        $id = $this->_get('id','intval');
        if(IS_POST){
            $data = array(
                    'vote_personal' => $this->_post('vote_personal','trim'),
                    'tel' => $this->_post('tel','trim'),
                    'age' => $this->_post('age','intval'),
                    'gender' => $this->_post('gender','intval'),
                    'description' => $this->_post('description','trim')
                );

            $id =  $this->_post('id','intval');
            M('vote_young')->where('id ='.$id)->data($data)->save();            
            $this->success('操作成功！');exit;


        }
        $info = M('vote_young')->find($id);
       // print_r($info);
        $this->assign('info',$info);
        $this->display();
        
    }

    public function _before_update($data=''){
        
        return $data;
    }	
	

   
    //上传图片
    public function ajax_upload_img() {
        $type = $this->_get('type', 'trim', 'img');
        if (!empty($_FILES[$type]['name'])) {
/*			$fdfs_obj = new FastFile();
			$result = $fdfs_obj->fdfs_upload($type);
			if($result){
				$savename = $result['group_name'].'/'.$result['filename'];
				$this->ajaxReturn(1, L('operation_success'), $savename);
			}else{
				 $this->ajaxReturn(0, L('illegal_parameters'));
			}*/
            $dir = date('ym/d/');
            $result = $this->_upload($_FILES[$type], 'vote_young/'. $dir );
            if ($result['error']) {
                 $this->ajaxReturn(0, $result['info']);
            } else {
                $savename = $dir . $result['info'][0]['savename'];
                $this->ajaxReturn(1, L('operation_success'), $savename);
            }
        } else {
            $this->ajaxReturn(0, L('illegal_parameters'));
        }
    }

    /**
     * 删除
     */
    public function delete()
    {
        
        $mod = D($this->_name);

        $pk = $mod->getPk();
        
        $ids = trim($this->_request($pk), ',');

        if ($ids) {
            if (false !== $mod->where('id in ('.$ids.')')->save(array('status'=>0))) {
                $this->admin_log($mod,$ids);
                IS_AJAX && $this->ajaxReturn(1, L('operation_success'));
                $this->success(L('operation_success'));
            } else {
                IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
                $this->error(L('operation_failure'));
            }
        } else {
            IS_AJAX && $this->ajaxReturn(0, L('illegal_parameters'));
            $this->error(L('illegal_parameters'));
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

    

}