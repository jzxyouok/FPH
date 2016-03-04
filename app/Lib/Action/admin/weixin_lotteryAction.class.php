<?php
class weixin_lotteryAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('weixin_lottery');
        $this->_cate_mod = D('user');
    }

    public function _before_index() {
    	
        $res = $this->_cate_mod->field('id,username,mobile')->select();
        $cate_list = array();
		$cate_mobile = array();
        foreach ($res as $val) {
            $cate_list[$val['id']] = $val['username'];
			$cate_mobile[$val['id']] = $val['mobile'];
        }
        $this->assign('cate_list', $cate_list);
		$this->assign('cate_mobile', $cate_mobile);
		
        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);

        //默认排序
        $this->sort = 'add_time';
        $this->order = 'DESC';
    }

    protected function _search() {
    	
        $map = array();
        ($time_start = $this->_request('time_start', 'trim')) && $map['add_time'][] = array('egt', strtotime($time_start));
        ($time_end = $this->_request('time_end', 'trim')) && $map['add_time'][] = array('elt', strtotime($time_end)+(24*60*60-1));
        $hits = $this->_request('hits', 'trim');
        $amount = $this->_request('amount', 'trim');
        $username = $this->_request('username', 'trim');//用户名
        if($username){
        	$user_id = M('user')->where('`username` = "'.$username.'"')->getField('id');
        	$map['uid'] = array('eq', $user_id);
        }
        $mobile = $this->_request('mobile', 'trim');//手机号
        if($mobile){
        	$user_id = M('user')->where('mobile ='.$mobile)->getField('id');
        	$map['uid'] = array('eq', $user_id);
        }
      
        $amount==1 && $map['amount'] = array('neq', 0);    //刮中
        if($amount==2){                                    //未刮中
        	$map['amount'] = array('eq', 0);
        	$map['interval'] = array('neq', 0);
        }
        $amount==3 && $map['interval'] = array('neq', 0);   //刮刮卡显示全部

        
        $hits==1 && $map['prizetype'] = array('neq', 0);    //大  转中
        if($hits==2){                                       //大 未转中
        	$map['prizetype'] = array('eq', 0);
        	$map['interval'] = array('eq', 0);
        }
        $hits==3  && $map['interval'] = array('eq', 0);    //大  所有

		
        $this->assign('search', array(
            'time_start' => $time_start,
            'time_end' => $time_end,
            'username' => $username,
        	'amount'  => $amount,
        	'hits'    => $hits,
        	'mobile'=>$mobile,	
        ));
        
        return $map;
		
    }

    public function _before_add()
    {
        $author = $_COOKIE['admin']['username'];
        $this->assign('author',$author);

        $site_name = D('setting')->where(array('name'=>'site_name'))->getField('data');
        $this->assign('site_name',$site_name);

        $first_cate = $this->_cate_mod->field('id,name')->where(array('pid'=>0))->order('ordid DESC')->select();
        $this->assign('first_cate',$first_cate);

    }

    protected function _before_insert($data) {
		$time_start = $this->_post('time_start','trim');
		$time_end   = $this->_post('time_end','trim');
		$data['time_start'] = strtotime($time_start);
		$data['time_end']   = strtotime($time_end);
        //上传图片
        if (!empty($_FILES['img']['name'])) {
            $art_add_time = date('ym/d/');
            $result = $this->_upload($_FILES['img'], 'article/' . $art_add_time, array('width'=>'300', 'height'=>'300', 'remove_origin'=>true));
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
        $article = $this->_mod->field('id,cate_id')->where(array('id'=>$id))->find();
        $spid = $this->_cate_mod->where(array('id'=>$article['cate_id']))->getField('spid');
        if( $spid==0 ){
            $spid = $article['cate_id'];
        }else{
            $spid .= $article['cate_id'];
        }
        $this->assign('selected_ids',$spid);
    }

    protected function _before_update($data) {
		$time_start = $this->_post('time_start','trim');
		$time_end   = $this->_post('time_end','trim');
		$data['time_start'] = strtotime($time_start);
		$data['time_end']   = strtotime($time_end);
		
        if (!empty($_FILES['img']['name'])) {
            $art_add_time = date('ym/d/');
            //删除原图
            $old_img = $this->_mod->where(array('id'=>$data['id']))->getField('img');
			$old_img = './data/upload/article/'. $old_img;//修改后
            //$old_img = $this->_get_imgdir() . $old_img;
            is_file($old_img) && @unlink($old_img);
            //上传新图
            $result = $this->_upload($_FILES['img'], 'article/' . $art_add_time, array('width'=>'300', 'height'=>'300', 'remove_origin'=>true));
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