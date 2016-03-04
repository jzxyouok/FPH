<?php
class pringlesAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('pringles');
    }

    public function _before_index() {
    	$p = $this->_get('p','intval',1);
        $this->assign('p',$p);
        //默认排序
        $this->sort = 'ordid';
        $this->order = 'ASC,id DESC';
    }

    protected function _search() {
        $map = array();
        ($time_start = $this->_request('time_start', 'trim')) && $map['add_time'][] = array('egt', strtotime($time_start));
        ($time_end = $this->_request('time_end', 'trim')) && $map['add_time'][] = array('elt', strtotime($time_end)+(24*60*60-1));
        $status = $this->_request('status', 'trim');
        ($keyword = $this->_request('keyword', 'trim')) && $map['title'] = array('like', '%'.$keyword.'%');
        if($status!=''){
            $map['status'] = $status;
        }
        $this->assign('search', array(
            'time_start' => $time_start,
            'time_end' => $time_end,
            'status'  => $status,
            'keyword' => $keyword,
        ));
        return $map;
    }

    public function _before_add()
    {
    	//获取分类
        $cate = M('pringles_cate')->field('id,name')->select();
        $this->assign('cate',$cate);
    }

    protected function _before_insert($data) {
        //上传图片
        if (!empty($_FILES['img']['name'])) {
			if($_FILES['img']['size']/1024 > C('pin_attr_allow_size')){
                $this->error('图片超过尺寸限制');
            }
			$fdfs_obj = new FastFile();
			$result = $fdfs_obj->fdfs_upload('img','527,568','848,948','_ios,_android',false);
			if($result){
				$data['img'] = $result['group_name'].'/'.$result['filename'];
			}else{
				 $this->error('上传图片出错');
			}
            /*$art_add_time = date('ym/d/');
            $result = $this->_upload($_FILES['img'], 'pringles/' . $art_add_time, array('width'=>'527,568', 'height'=>'848,948', 'suffix'=>'_ios,_android'));
            if ($result['error']) {
                $this->error($result['info']);
            } else {
                $ext = array_pop(explode('.', $result['info'][0]['savename']));
                $data['img'] = $art_add_time .'/'. str_replace('.' . $ext, '.' . $ext, $result['info'][0]['savename']);
            }*/
        }
        return $data;
    }

    public function _before_edit(){
        $id = $this->_get('id','intval');
        //获取分类
        $cate = M('pringles_cate')->field('id,name')->select();
        $this->assign('cate',$cate);
       
    }
    
    public function _after_edit($info){
    	//绑定楼盘名称
    	if(isset( $info['pid'] ))
    	{
    		$info['property_title']	= M('property')->where("id='{$info['pid']}'")->getField('title');
    	}
    	return $info;
    }

    protected function _before_update($data) {
		$suffix = array('_ios','_android');
        if (!empty($_FILES['img']['name'])) {
		
			$fdfs_obj = new FastFile();
			$result = $fdfs_obj->fdfs_upload('img','527,568','848,948','_ios,_android',false);
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
            $a = explode('.',$old_img);
            unlink('./data/upload/pringles/'.$old_img);
            unlink('./data/upload/pringles/'.$a[0].'_ios.'.$a[1]);
            unlink('./data/upload/pringles/'.$a[0].'_android.'.$a[1]);
            //上传新图
            $result = $this->_upload($_FILES['img'], 'pringles/' . $art_add_time, array('width'=>'527,568', 'height'=>'848,948', 'suffix'=>'_ios,_android'));
            if ($result['error']) {
                $this->error($result['info']);
            } else {
                $ext = array_pop(explode('.', $result['info'][0]['savename']));
                $data['img'] = $art_add_time .'/'. str_replace('.' . $ext, '.' . $ext, $result['info'][0]['savename']);
            }*/
        } 
        return $data;
    }
    
    //搜索楼盘
    public function input_search(){
    	$fph=C('DB_PREFIX');
    	$title = $this->_post('title','trim');
    	!$title && $this->ajaxReturn(0, '请输入要搜索的内容');
    	if($title){
    		$time=time();
    		$list = M('property')->field("id,title")->table("{$fph}property")->where("title like '%{$title}%'")->limit(50)->select();
    		$str = "";
    		$str .= "<ul class='popup'>";
    		foreach($list as $val) {
    			$title = $val['title'];
    			$str .= "<li rel=".$val['id'].">".msubstr($title,0,35,'utf-8',true)."</li>";
    		}
    		$str .= '</ul>';
    	}else{
    		$str .= "<div>无数据,请检查输入的关键字</div>";
    	}
    	$this->ajaxReturn(1, '未知错误！', $str);
    }
}