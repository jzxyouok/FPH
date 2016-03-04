<?php
class mediaAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('media');
        $this->_cate_mod = D('media_cate');
        $this->_media_type = D('media_type');
    }

    public function _before_index() {
    	//媒体类型
        $res = $this->_mod->field('id,city_id,type')->select();
        $media_name_list=array();//媒体类型
        $media_name_list_b=array();//媒体类型
        $city_name_list=array();//选择城市
        foreach ($res as $val) {
            $media_cate = explode(',', $val['type']);
            foreach ($media_cate as $key =>$value){
            	$media_names .= $this->_cate_mod->where(array('id'=>$value))->getField('name')."  ";
            }
            $media_name_list[$val['id']]= $media_names;
            $cate_id= $this->_media_type->where('pid='.$val['id'])->getField('cate_id');
            $media_name_list_b[$val['id']]= $this->_cate_mod->where('id='.$cate_id)->getField('name');
            $media_names='';
           
            $city_cate = explode(',', $val['city_id']);
            foreach ($city_cate as $key =>$value){
            	$city_names .= M('city')->where(array('id'=>$value))->getField('name')."  ";
            }
            $city_name_list[$val['id']]= $city_names;
            $city_names='';
        }
        
        $this->assign('media_name_list', $media_name_list);
        $this->assign('media_name_list_b', $media_name_list_b);
        $this->assign('city_name_list', $city_name_list);

        
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
        ($keyword = $this->_request('keyword', 'trim')) && $map['name'] = array('like', '%'.$keyword.'%');

        $this->assign('search', array(
            'time_start' => $time_start,
            'time_end' => $time_end,
            'keyword' => $keyword,
        ));
        return $map;
    }

    public function _before_add() {
        $media_cate = $this->_cate_mod->field('id,name')->where(array('pid'=>0,'status'=>1))->order('ordid ASC')->select();
        $this->assign('media_cate',$media_cate);
		
		$media_cate_id = $this->_cate_mod->where(array('pid'=>0,'status'=>1))->order('ordid ASC')->getfield('id');
        $this->assign('media_cate_id',$media_cate_id);
		
		$media_cate_list = $this->_cate_mod->field('id,name')->where(array('pid'=>$media_cate_id,'status'=>1))->order('ordid ASC')->select();
        $this->assign('media_cate_list',$media_cate_list);

        
        $city_list = M("city")->field('id,name')->where(array("pid"=>0))->select();
        foreach ($city_list as $key => $val){
            $city_list[$key]['two'] = M("city")->field('id,name')->where(array("pid"=>$val['id']))->select();
        }
        $this->assign('city_list',$city_list);
        //print_r($city_list);die();
    }
	
	//前置插入
    protected function _before_insert($data) {
    	$city = $this->_post('city','trim');//接收城市组合字符串存入
    	$data['city_id']= implode(',',$city);
        return $data;
    }
    //后置插入
    protected function _after_insert($id) {
        $data['pid']= $id;
        //$data['url']     = $this->_post('url'.$val['id'],'trim');
        $data['cate_id']   = $this->_post('cate_id','intval');
        $data['account'] = $this->_post("account",'trim');
        $data['fans']    = $this->_post('fans','trim');
        $data['intro']   = $this->_post('intro','trim');
        if(empty($data['account'])){
                $this->error('渠道类型-账户信息项必填');            
        }
        if($data['account'] || $data['fans'] || $data['intro']){
            M('media_type')->add($data);
            $cate_id = M('media_type')->where(array('pid'=>$id))->getfield('cate_id');
            $pid = M('media_cate')->where(array('id'=>$cate_id))->getfield('pid');//13
            $this->_mod->where('id='.$id)->save(array('type'=>$pid));
        }
    	return $data;
    }
  
    public function _before_edit($data){
    	$pid = $this->_post('id','intval');
    	if($pid){
            $data['cate_id'] = $this->_post('cate_id','intval');
            $data['account'] = $this->_post("account",'trim');
            $data['fans']    = $this->_post('fans','trim');
            $data['intro']   = $this->_post('intro','trim');
            M('media_type')->where('pid='.$pid)->save($data);

            // if($data['account'] || $data['fans'] || $data['intro']){// }
	    	$_POST['city_id']= implode(',',$this->_post('city','trim'));//接收城市组合字符串存入
    	}

        $id = $this->_get('id','intval');
        $meida_detail = $this->_mod->where(array('id'=>$id))->find();
        $media_type = $this->_media_type->where(array('pid'=>$meida_detail['id']))->find();
        $media_cate_id = $this->_cate_mod->where(array('id'=>$media_type['cate_id']))->order('ordid ASC')->getfield('pid');
        $this->assign('media_cate_id',$media_cate_id);
        $media_cate_list = $this->_cate_mod->field('id,name')->where(array('pid'=>$media_cate_id,'status'=>1))->order('ordid ASC')->select();
        $this->assign('media_cate_list',$media_cate_list);
        $media_cate = $this->_cate_mod->field('id,name')->where(array('pid'=>0,'status'=>1))->order('ordid ASC')->select();
        $this->assign('media_cate',$media_cate);
        
        //城市问题
        $city_list = M("city")->field('id,name')->where(array("pid"=>0))->select();
        foreach ($city_list as $key => $val){
        	$city_list[$key]['ture']=0;
        	$city_list[$key]['two'] = M("city")->field('id,name')->where(array("pid"=>$val['id']))->select();
            foreach ($city_list[$key]['two'] as $keys =>$vo){
            	$city_list[$key]['two'][$keys]['have']=0;
            	if(in_array($vo[id],explode(',',$meida_detail['city_id']))){
            		$city_list[$key]['ture']='1';
            		$city_list[$key]['two'][$keys]['have']='1';
            	}
            }
        }

        $this->assign('city_list',$city_list);
        //城市问题
        $this->assign('media_cate',$media_cate);
        $this->assign('meida_detail',$meida_detail);
        $this->assign('media_type',$media_type);
    }
	
	//查找子分类
	public function ajax_children_cate(){
		$id = $this->_post('id','intval');
		$list = $this->_cate_mod->field('id,name')->where(array('pid'=>$id,'status'=>1))->order('ordid ASC')->select();
		if($list){
			$this->ajaxReturn(1, '' ,$list);
		}else{
			$this->ajaxReturn(0, '操作失败');
		}
	}

    //上传图片
    public function ajax_upload_img() {
        $type = $this->_get('type', 'trim', 'img');
        if (!empty($_FILES[$type]['name'])) {
		
			$fdfs_obj = new FastFile();
			$result = $fdfs_obj->fdfs_upload('img');
			if($result){
				$savename = $result['group_name'].'/'.$result['filename'];
				$this->ajaxReturn(1, L('operation_success'), $savename);
			}else{
				 $this->ajaxReturn(0, '上传图片出错');
			}
            /*$dir = date('ym/d/');
            $result = $this->_upload($_FILES[$type], 'media/'. $dir );
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
		$result = $fdfs_obj->fast_del_img($img_path);
		if($result){
			$this->ajaxReturn(1, '删除成功');	
		}else{
			$this->ajaxReturn(0, '删除失败');
		}
    }
	
	
	
	
	
	
	
	
	
	
    

}