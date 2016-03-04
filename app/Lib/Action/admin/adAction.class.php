<?php
class adAction extends backendAction {

    private $_ad_type = array('image'=>'图片', 'code'=>'代码', 'flash'=>'Flash', 'text'=>'文字');
    public $list_relation = true;
    public function _initialize() {
        parent::_initialize();
    }

    public function index() {
        M('ad','fph_',C('DB_ad'));

        $start_time_min = $this->_get('start_time_min', 'trim');
        $start_time_max = $this->_get('start_time_max', 'trim');
        $end_time_min   = $this->_get('end_time_min', 'trim');
        $end_time_max   = $this->_get('end_time_max', 'trim');
        $board_id       = $this->_get('board_id', 'trim');
        $keyword        = $this->_get('keyword', 'trim');

        $where = 'status != 2';
        if($start_time_min){
            $where .= ' AND start_time >= '.strtotime($start_time_min);
        }
        if($start_time_max){
            $where .= ' AND start_time <= '.strtotime($start_time_max)+86400;
        }
        if($end_time_min){
            $where .= ' AND end_time >= '.strtotime($end_time_min);
        }
        if($end_time_max){
            $where .= ' AND end_time <= '.intval(strtotime($end_time_max))+86400;
        }
        if($board_id != ''){
            $where .= ' AND board_id = '.$board_id;
        }
        if($keyword){
            $where .= " AND name like '%".$keyword."%'";
        }
        $field = 'id,board_id,name,url,start_time,end_time,ordid,add_time,status,is_login,display_time';
        $list = D('ad')->lists($where, $field);
        $this->assign('list', $list[0]);
        $this->assign('page', $list[1]);


        $whereRes = 'status != 2';
        $fieldRes = 'id,name';
        $res = D('adboard')->readlists($whereRes, $fieldRes);
        $this->assign('board_list', $res);

        $this->assign('search', array(
            'start_time_min' => $start_time_min,
            'start_time_max' => $start_time_max,
            'end_time_min' => $end_time_min,
            'end_time_max' => $end_time_max,
            'board_id' => $board_id,
            'keyword' => $keyword,
        ));

        $this->display();
    }

    public function add() {
        M(NUll,'fph_',C('DB_ad'));
        if (IS_POST) {
            $name          = $this->_post('name', 'trim');
            $url           = $this->_post('url', 'trim');
            $board_id      = $this->_post('board_id', 'trim');
            $img           = $this->_post('img', 'trim');
            $desc          = $this->_post('desc', 'trim');
            $start_time    = $this->_post('start_time', 'trim');
            $end_time      = $this->_post('end_time', 'trim');
            $status        = $this->_post('status', 'intval');
            $is_login      = $this->_post('is_login', 'intval');
            $display       = $this->_post('display_time', 'intval');
            $city          = $this->_post('city','trim');
            $whole_country = $this->_post('whole_country','intval',0);

            !$name && $this->error('请输入广告名称');
            !$board_id && $this->error('请选择广告位');
            !$img && $this->error('请上传图片');
            !$start_time && $this->error('请选择开始时间');
            !$end_time && $this->error('请选择结束时间');

            if(strtotime($end_time) < time()){
                $this->error('广告结束时间不能小于当前时间');
            }
            if(strtotime($start_time) > strtotime($end_time)){
                $this->error('广告开始时间不能大于结束时间');
            }

            $data['name']          = $name;
            $data['url']           = $url;
            $data['board_id']      = $board_id;
            $data['img']           = $img;
            $data['desc']          = $desc;
            $data['start_time']    = strtotime($start_time);
            $data['end_time']      = strtotime($end_time);
            $data['status']        = $status;
            $data['is_login']      = $is_login;
            $data['add_time']      = time();
            $data['display_time']  = $display;
            if($whole_country == 0){
                $data['city_id']   = implode(',',$city);
            }else{
                $data['city_id']   = NULL;
            }
            if(false !== D('ad')->insterData($data)){
                $this->success('提交成功');
            }else{
                $this->error('提交失败');
            }
        } else {
            $whereRes = 'status != 2';
            $fieldRes = 'id,name,width,height';
            $res = D('adboard')->readlists($whereRes, $fieldRes);
            $this->assign('board_list', $res);

            //城市
            M('city','fph_',C('DB_fangpinhui'));
            $city_list = M("city")->field('id,name')->where("pid = 0 AND id != 3388")->select();
            foreach ($city_list as $key => $val){
                $city_list[$key]['two'] = M("city")->field('id,name')->where(array("pid"=>$val['id']))->select();
            }
            $this->assign('city_list',$city_list);
            $this->display();
        }
    }


    public function edit() {
        M(NUll,'fph_',C('DB_ad'));
        if (IS_POST) {
            $id            = $this->_post('id', 'trim');
            $name          = $this->_post('name', 'trim');
            $url           = $this->_post('url', 'trim');
            $board_id      = $this->_post('board_id', 'trim');
            $img           = $this->_post('img', 'trim');
            $desc          = $this->_post('desc', 'trim');
            $start_time    = $this->_post('start_time', 'trim');
            $end_time      = $this->_post('end_time', 'trim');
            $status        = $this->_post('status', 'intval');
            $is_login      = $this->_post('is_login', 'intval');
            $display       = $this->_post('display_time', 'intval');
            $city          = $this->_post('city','trim');
            $whole_country = $this->_post('whole_country','intval',0);

            !$name && $this->error( '请输入广告名称');
            !$board_id && $this->error('请选择广告位');
            !$img && $this->error('请上传图片');
            !$start_time && $this->error('请选择开始时间');
            !$end_time && $this->error('请选择结束时间');

            if(strtotime($start_time) > strtotime($end_time)){
                $this->error('广告开始时间不能大于结束时间');
            }

            $data['name']          = $name;
            $data['url']           = $url;
            $data['board_id']      = $board_id;
            $data['img']           = $img;
            $data['desc']          = $desc;
            $data['start_time']    = strtotime($start_time);
            $data['end_time']      = strtotime($end_time);
            $data['status']        = $status;
            $data['is_login']      = $is_login;
            $data['display_time']  = $display;
            if($whole_country == 0){
                $data['city_id']   = implode(',',$city);
            }else{
                $data['city_id']   = NULL;
            }
            $where = 'id = '.$id;
            if(false !== D('ad')->updateInfo($where, $data)){
                $this->success('提交成功');
            }else{
                $this->error('提交失败');
            }
        } else {
            $whereRes = 'status != 2';
            $fieldRes = 'id,name,width,height';
            $res = D('adboard')->readlists($whereRes, $fieldRes);
            $this->assign('board_list', $res);

            $id = $this->_get('id', 'intval');
            $where = 'id = '.$id;
            $info = D('ad')->readInfo($where);
            $this->assign('info', $info);

            //城市
            M('city','fph_',C('DB_fangpinhui'));
            $city_list = M("city")->field('id,name')->where("pid = 0 AND id != 3388")->select();
            foreach ($city_list as $key => $val){
                $city_list[$key]['ture']=0;
                $city_list[$key]['two'] = M("city")->field('id,name')->where(array("pid"=>$val['id']))->select();
                foreach ($city_list[$key]['two'] as $keys =>$vo){
                    $city_list[$key]['two'][$keys]['have']=0;
                    if(in_array($vo['id'],explode(',',$info['city_id'])) || !$info['city_id']){
                        $city_list[$key]['ture']='1';
                        $city_list[$key]['two'][$keys]['have']='1';
                    }
                }
            }
            $this->assign('city_list',$city_list);
            $this->display();
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
        } else {
            $this->ajaxReturn(0, L('illegal_parameters'));
        }
    }

    //删除
    public function delete(){
        M('ad','fph_',C('DB_ad'));
        $id = $this->_get('id','intval');
        !$id && $this->ajaxReturn(0, L('operation_failure'));
        $where = 'id = '.$id;
        $data['status'] = 2;
        if(false !== D('ad')->updateInfo($where, $data)){
            $this->ajaxReturn(1, L('operation_success'));
        }else{
            $this->ajaxReturn(0, L('operation_failure'));
        }
    }
}