<?php
class article_pageAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('article_page');
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
        
        return $map;
    }

    public function _before_add()
    {  
    	
        $author = $_COOKIE['admin']['username'];
        $this->assign('author',$author);
       
    }

    protected function _before_insert($data) {
		$data['last_time']   = time();
		
        //上传图片
        if (!empty($_FILES['img']['name'])) {
            $art_add_time = date('ym/d/');
            $result = $this->_upload($_FILES['img'], 'article_page/' . $art_add_time, array('width'=>'720', 'height'=>'540', 'remove_origin'=>true));
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
        $author = $_COOKIE['admin']['username'];
        $this->assign('author',$author);
    }

    protected function _before_update($data) {
		$data['last_time']   = time();
		
        if (!empty($_FILES['img']['name'])) {
            $art_add_time = date('ym/d/');
            //删除原图
            $old_img = $this->_mod->where(array('id'=>$data['id']))->getField('img');
			$old_img = './data/upload/article_page/'. $old_img;//修改后
            //$old_img = $this->_get_imgdir() . $old_img;
            is_file($old_img) && @unlink($old_img);
            //上传新图
            $result = $this->_upload($_FILES['img'], 'article_page/' . $art_add_time, array('width'=>'720', 'height'=>'540', 'remove_origin'=>true));
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