<?php
class weixin_articleAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('weixin_article');
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
        $author = $_COOKIE['admin']['username'];
        $this->assign('author',$author);
    }

    protected function _before_insert($data) {
        $info = $this->_post('info','trim');
        if(!$info){
            $this->error('请输入文章内容');
            exit;
        }
        //上传图片
        if (!empty($_FILES['img']['name'])) {
            $art_add_time = date('ym/d/');
            $result = $this->_upload($_FILES['img'], 'article/' . $art_add_time, array('width'=>'130', 'height'=>'100', 'remove_origin'=>true));
            if ($result['error']) {
                $this->error($result['info']);
            } else {
                $ext = array_pop(explode('.', $result['info'][0]['savename']));
                $data['img'] = $art_add_time .'/'. str_replace('.' . $ext, '_thumb.' . $ext, $result['info'][0]['savename']);
            }
        }
        return $data;
    }

    public function _before_edit(){
        $id = $this->_get('id','intval');
       
    }

    protected function _before_update($data) {
        $info = $this->_post('info','trim');
        if(!$info){
            $this->error('请输入文章内容');
            exit;
        }
        
        if (!empty($_FILES['img']['name'])) {
            $art_add_time = date('ym/d/');
            //删除原图
            $old_img = $this->_mod->where(array('id'=>$data['id']))->getField('img');
			$old_img = './data/upload/article/'. $old_img;//修改后
            //$old_img = $this->_get_imgdir() . $old_img;
            is_file($old_img) && @unlink($old_img);
            //上传新图
            $result = $this->_upload($_FILES['img'], 'article/' . $art_add_time, array('width'=>'130', 'height'=>'100', 'remove_origin'=>true));
            if ($result['error']) {
                $this->error($result['info']);
            } else {
                $ext = array_pop(explode('.', $result['info'][0]['savename']));
                $data['img'] = $art_add_time .'/'. str_replace('.' . $ext, '_thumb.' . $ext, $result['info'][0]['savename']);
            }
        } else {
            unset($data['img']);
        }

        return $data;
    }
}