<?php
class articleAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('article');
        $this->_cate_mod = D('article_cate');
		$this->_cate_baoming = D('article_baoming');
    }

    public function _before_index() {
        $res = $this->_cate_mod->field('id,name')->select();
        $cate_list = array();
        foreach ($res as $val) {
            $cate_list[$val['id']] = $val['name'];
        }
        $this->assign('cate_list', $cate_list);
        
        
        $res = M('property')->field('id,title')->select();
        $property_list = array();
        foreach ($res as $val) {
        	$property_list[$val['id']] = $val['title'];
        }
        $this->assign('property_list', $property_list);
        

        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);
         
        //默认排序
        $this->sort = 'ordid';
        $this->order = 'ASC,add_time DESC';
    }

    protected function _search() {
        $map = array();
        ($time_start = $this->_request('time_start', 'trim')) && $map['add_time'][] = array('egt', strtotime($time_start));
        ($time_end = $this->_request('time_end', 'trim')) && $map['add_time'][] = array('elt', strtotime($time_end)+(24*60*60-1));
        $status = $this->_request('status', 'trim');
        ($keyword = $this->_request('keyword', 'trim')) && $map['title'] = array('like', '%'.$keyword.'%');
        $cate_id = $this->_request('cate_id', 'intval');
		if($status!=''){
			$map['status'] = $status;
		}
        $selected_ids = '';
        if ($cate_id) {
            $id_arr = $this->_cate_mod->get_child_ids($cate_id, true);
            $map['cate_id'] = array('IN', $id_arr);
            $spid = $this->_cate_mod->where(array('id'=>$cate_id))->getField('spid');
            $selected_ids = $spid ? $spid . $cate_id : $cate_id;
        }
        $this->assign('search', array(
            'time_start' => $time_start,
            'time_end' => $time_end,
            'cate_id' => $cate_id,
            'selected_ids' => $selected_ids,
            'status'  => $status,
            'keyword' => $keyword,
        ));
        return $map;
    }

    public function _before_add()
    {  
    	
    	$pid = $this->_get('pid','intval');
    	$pidtitle=D('property')->where(array('id'=>$pid))->getField('title');
    	$this->assign("pidtitle",$pidtitle);
    	$this->assign("pid",$pid);
    	
        $author = $_COOKIE['admin']['username'];
        $this->assign('author',$author);

        $author = $_COOKIE['admin']['username'];
        $this->assign('author',$author);

        $site_name = D('setting')->where(array('name'=>'site_name'))->getField('data');
        $this->assign('site_name',$site_name);

        $first_cate = $this->_cate_mod->field('id,name')->where(array('pid'=>0))->order('ordid DESC')->select();
        $this->assign('first_cate',$first_cate);
        //楼盘没有不能提交
        if (!empty($_POST['pid'])) {
        	if(!D('property')->where(array('id'=>$_POST['pid']))->find()) {
        		 $this->error(L('operation_failure'));
        	}
        }
    }

    protected function _before_insert($data) {
		$time_start = $this->_post('time_start','trim');
		$time_end   = $this->_post('time_end','trim');
		$data['time_start'] = strtotime($time_start);
		$data['time_end']   = strtotime($time_end);

        //上传图片
        if(!empty($_FILES['img']['name'])) {
            if($_FILES['img']['size']/1024 > C('pin_attr_allow_size')){
                $this->error('图片超过尺寸限制');
            }
			$fdfs_obj = new FastFile();
			$result = $fdfs_obj->fdfs_upload('img','720','540','_720x540',false);
			if($result){
				$data['img'] = $result['group_name'].'/'.$result['filename'];
			}else{
				 $this->error('上传图片出错');
			}
            /*$art_add_time = date('ym/d/');
            $result = $this->_upload($_FILES['img'], 'article/' . $art_add_time, array('width'=>'720', 'height'=>'540', 'remove_origin'=>true));
            if ($result['error']) {
                $this->error($result['info']);
            } else {
                $ext = array_pop(explode('.', $result['info'][0]['savename']));
                $data['img'] = $art_add_time .'/'. str_replace('.' . $ext, '_thumb.' . $ext, $result['info'][0]['savename']);
            }*/
        }
		
		
		
        return $data;
    }

    public function _before_edit(){
    	//楼盘没有不能提交
    	if (!empty($_POST['pid'])) {
    		if(!D('property')->where(array('id'=>$_POST['pid']))->count('id')) {
    			$this->error(L('楼盘不存在'));
    		}
    	}
    	
        $id = $this->_get('id','intval');
        $article = $this->_mod->field('id,cate_id,pid,city_id')->where(array('id'=>$id))->find();
        $spid = $this->_cate_mod->where(array('id'=>$article['cate_id']))->getField('spid');
        $pidtitle=M('property')->where(array('id'=>$article['pid']))->getField('title');
        if( $spid==0 ){
            $spid = $article['cate_id'];
        }else{
            $spid .= $article['cate_id'];
        }
		
		
		$spid_city = M('city')->where(array('id'=>$article['city_id']))->getField('spid');
        if( $spid_city==0 ){
            $spid_city = $article['city_id'];
        }else{
            $spid_city .= $article['city_id'];
        }
		
        $this->assign('selected_ids_city',$spid_city);
        $this->assign('selected_ids',$spid);
        $this->assign('pidtitle',$pidtitle);
        
    }

    protected function _before_update($data) {
		$time_start = $this->_post('time_start','trim');
		$time_end   = $this->_post('time_end','trim');
		$data['time_start'] = strtotime($time_start);
		$data['time_end']   = strtotime($time_end);
		$suffix             = array('_720x540');
		
        if (!empty($_FILES['img']['name'])) {
            if($_FILES['img']['size']/1024 > C('pin_attr_allow_size')){
                $this->error('图片超过尺寸限制');
            }
			
			$fdfs_obj = new FastFile();
			$result = $fdfs_obj->fdfs_upload('img','720','540','_720x540',false);
			if($result){
				$data['img'] = $result['group_name'].'/'.$result['filename'];
			}else{
				 $this->error('上传图片出错');
			}
			
			//删除原图
			$old_img = $this->_mod->where(array('id'=>$data['id']))->getField('img');
			if($old_img){
				$fdfs_obj->fast_del_img($old_img);
				$img_exp = explode('.',$old_img);
				foreach($suffix as $k=>$v){
					$img_thumb = $img_exp[0].$v.'.'.$img_exp[1];
					$fdfs_obj->fast_del_img($img_thumb);
				}
			}
            /*$art_add_time = date('ym/d/');
            //删除原图
            $old_img = $this->_mod->where(array('id'=>$data['id']))->getField('img');
			$old_img = './data/upload/article/'. $old_img;//修改后
            //$old_img = $this->_get_imgdir() . $old_img;
            is_file($old_img) && @unlink($old_img);
            //上传新图
            $result = $this->_upload($_FILES['img'], 'article/' . $art_add_time, array('width'=>'720', 'height'=>'540', 'remove_origin'=>true));
            if ($result['error']) {
                $this->error($result['info']);
            } else {
                $ext = array_pop(explode('.', $result['info'][0]['savename']));
                $data['img'] = $art_add_time .'/'. str_replace('.' . $ext, '_thumb.' . $ext, $result['info'][0]['savename']);
            }*/
        } else {
            unset($data['img']);
        }

        return $data;
    }
	
	//活动报名
	public function baoming(){
		$fph   = C('DB_PREFIX');
		$count = $this->_cate_baoming->count('id');
		import("ORG.Util.Page");
		$p = new Page($count, 20);
		$page = $p->show();
		$str = 'A.id,A.name,A.mobile,A.add_time,B.title';
		$list = $this->_cate_baoming->field($str)->table("{$fph}article_baoming AS A LEFT JOIN {$fph}article AS B ON B.id=A.pid")->limit($p->firstRow.','.$p->listRows)->order("A.add_time DESC")->select();
		$this->assign('list',$list);
		$this->assign('page',$page);
		$p = $this->_get('p','intval',1);
        $this->assign('p',$p);
		$this->display();
	}
	
	//删除报名
	public function delete_baoming(){
		$mod = D($this->_name);
        $pk = $mod->getPk();
        $ids = trim($this->_request($pk), ',');
		//$this->ajaxReturn(0, $ids);
        if ($ids) {
            if (false !== $this->_cate_baoming->delete($ids)) {
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

    /*
    *@Descriptions：楼盘活动 跳转页面
    *@Date:2014-11-21
    *@Author: wsj
    */
    protected function _after_insert()
    {
        $id = $this->_get('pid','intval');

        if(!empty($id))
            $this->success(L('operation_success'),U('property/activities',array('id'=>$id)));
    }

    /*
    *@Descriptions：楼盘活动 跳转页面
    *@Date:2014-11-21
    *@Author: wsj
    */
    protected function _after_update()
    {
        $id = $this->_get('pid','intval');
        
        if(!empty($id))
            $this->success(L('operation_success'),U('property/activities',array('id'=>$id)));
    }
}