<?php
class weixin_replyAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('weixin_reply');
    }

    public function _before_index() {

        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);

        //默认排序
        $this->sort = 'id';
        $this->order = 'DESC';
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
            'selected_ids' => $selected_ids,
            'status'  => $status,
            'keyword' => $keyword,
        ));
        return $map;
    }

    //单图文消息
    public function _before_add()
    {
        $author = $_COOKIE['admin']['username'];
        $this->assign('author',$author);
    }

    //文字消息
     public function addwenzi()
    {
         $this->display();
    }
    //图片消息
     public function addimg()
    {
         $this->display();
    }
    //多图文消息
     public function addarrimg()
    {
         $this->display();
    }

    protected function _before_insert($data) {
        $pid     = $this->_post('pid', 'intval');
        if($pid==4){
			$title   = $this->_post('title', 'trim');
            $url     = $this->_post('url', 'trim');
            $img     = $this->_post('img', 'trim');
            $data["title"]   = implode('//',$title);
            $data["url"]     = implode('@f&',$url);
            $data["img"]     = implode('@f&',$img);
        }elseif($pid==1){
			$title   = htmlspecialchars($this->_post('title', 'trim'));
			$data["title"]   = $title;
		}
        return $data;
    }

    //修改文字消息
     public function editwenzi(){
         $id = $this->_get('id','intval');
         $info = $this->_mod->field('id,title,status')->where(array('id'=>$id,'pid'=>1))->find();
         $this->assign('info',$info);
         $this->display();
    }
    //修改图片消息
     public function editimg(){
         $id = $this->_get('id','intval');
         $info = $this->_mod->field('id,title,img,url,status')->where(array('id'=>$id,'pid'=>2))->find();
         $this->assign('info',$info);
         $this->display();
    }
    //修改多图文消息
     public function editarrimg(){
         $id = $this->_get('id','intval');
         $info = $this->_mod->field('id,title,img,url,status')->where(array('id'=>$id,'pid'=>4))->find();
         $this->assign('info',$info);
         $title = explode('//',$info['title']);
         $img   = explode('@f&',$info['img']);
         $url   = explode('@f&',$info['url']);
         //把三个相同的一维数组合并成为一个二维数组
         //$cards = array_merge_recursive($title, $url,$img);
         //foreach ($title as $k => $r) {
         //    $Arr4[] = array($title[$k],$img[$k],$url[$k]);
         //}
         $tit_count = count($title);
         $res=array();
        for($i=0;$i<$tit_count;$i++){
             $res[$i]['title']=$title[$i];
             $res[$i]['img']=$img[$i];
             $res[$i]['url']=$url[$i];
        }
        //print_r($res);
        $this->assign('res',$res);
        $this->display();
    }
    public function _before_edit(){
        $id = $this->_get('id','intval');

    }

    protected function _before_update($data) {
        $pid     = $this->_post('pid', 'intval');
        if($pid==4){
			$title   = $this->_post('title', 'trim');
            $url     = $this->_post('url', 'trim');
            $img     = $this->_post('img', 'trim');
            $data["title"]   = implode('//',$title);
            $data["url"]     = implode('@f&',$url);
            $data["img"]     = implode('@f&',$img);
        }elseif($pid==1){
			$title   = htmlspecialchars($this->_post('title', 'trim'));
			$data["title"]   = $title;
		}
        return $data;
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
            $result = $this->_upload($_FILES[$type], 'advert/'. $dir );
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


}