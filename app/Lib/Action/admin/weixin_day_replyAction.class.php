<?php
class weixin_day_replyAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('weixin_day_reply');
    }

    public function _before_index() {

        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);
        //所属地区
        $title_arr = M('weixin_reply')->field('id,title,pid')->select();
        $title_list = array();
        foreach ($title_arr as $val) {
            $title_list[$val['id']] = $val['title'];
        }
        $this->assign('title_list', $title_list);
        //默认排序
        $this->sort = 'id';
        $this->order = 'DESC';
    }

    protected function _search() {
        $fph = C('DB_PREFIX');
        $map = array();
        ($time_start = $this->_request('time_start', 'trim')) && $map['add_time'][] = array('egt', strtotime($time_start));
        ($time_end = $this->_request('time_end', 'trim')) && $map['add_time'][] = array('elt', strtotime($time_end)+(24*60*60-1));
        $status = $this->_request('status', 'trim');
		$tid = $this->_request('tid', 'trim');
        $keyword = $this->_request('keyword', 'trim');
        if($keyword){
            $map['pid'] = " IN ( SELECT id FROM {$fph}weixin_reply WHERE title like '%".$keyword."%' )";
        }
		if($status!=''){
			$map['status'] = $status;
		}
		if($tid!=''){
			$map['tid'] = $tid;
		}
        $this->assign('search', array(
            'time_start' => $time_start,
            'time_end' => $time_end,
            'selected_ids' => $selected_ids,
            'status'  => $status,
			'tid'  => $tid,
            'keyword' => $keyword,
        ));
        return $map;
    }

    public function _before_add()
    {
        //默认回复信息
        $wenzi_list = D('weixin_keyword_reply')->read_reply(1);
        $this->assign('wenzi_list',$wenzi_list);

        //当前id
        $wenzi_pid = D('weixin_keyword_reply')->read_reply_pid(1);
        $this->assign('wenzi_pid',$wenzi_pid);
    }

    protected function _before_insert($data) {
        return $data;
    }

    public function _before_edit(){
        $id = $this->_get('id','intval');
        $info_data = $this->_mod->field('tid,pid')->where(array('id'=>$id))->find();
        if($info_data['tid'] == 4){
             $list = D('weixin_keyword_reply')->read_reply($info_data['tid']);
            foreach ($list as $key => $val){
                $list[$key]['title'] = explode('//',$val['title']);            
            }
            $this->assign('list',$list);
        }else{
            $reply_list = M('weixin_reply')->field('id,title')->where(array('pid'=>$info_data['tid']))->limit(50)->select();
            $this->assign('reply_list',$reply_list);
        }
    }

    protected function _before_update($data) {
        return $data;
    }

	//上传图片
    public function ajax_upload_img() {
        $type = $this->_get('type', 'trim', 'img');
        if (!empty($_FILES[$type]['name'])) {
            $dir = date('ym/d/');
            $result = $this->_upload($_FILES[$type], 'advert/'. $dir );
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

    //点击单选-类型搜索
    public function type_search(){
        $tid = $this->_post('tid','intval');
        !$tid && $this->ajaxReturn(0, '参数出差');
        if($tid){
            $list = D('weixin_keyword_reply')->read_reply($tid);
            $str = "";
            if($tid == 4){
                $str .= '<table width="100%" cellspacing="0" class="treeTable">';
                foreach ($list as $key => $val){
                    $list[$key]['title'] = explode('//',$val['title']);
                    foreach ($list[$key]['title'] as $k => $v){
                        if(!$k){
                            $str .= '<tr id="'.$key.'" class="collapsed">';
                            $str .= '<td class="select_td_txt" id="'.$val['id'].'"><span class="expander" style="padding-left:20px"></span>'.$v.'</td>';
                            $str .= '</tr>';
                        }else{
                            $str .= '<tr class="code-'.$key.' dispnone" style="display:none;">';
                            $str .= '<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└─ '.$v.'</td>';
                            $str .= '</tr>';
                        }
                    }
                }
                $str .= '</table>';
            }else{
                $str .= "<ul>";
                    foreach($list as $val) {
                            $title = $val['title'];
                        $str .= "<li rel=".$val['id'].">".msubstr($title,0,35,'utf-8',true)."</li>";
                    }
                $str .= '</ul>';
            }
        }else{
         $str .= "<ul><li>无数据</li></<ul>";
        }
        $this->ajaxReturn(1, '未知错误！', $str);
    }

    //输入模糊搜索
    public function input_search(){
        $info_search = $this->_post('info_search','trim');
        $tid         = $this->_post('tid','intval');
        !$info_search && $this->ajaxReturn(0, '请输入要搜索的内容');
        !$tid && $this->ajaxReturn(0, '请选择要发送信息的类型');
        if($info_search && $tid){
            $list = D('weixin_keyword_reply')->input_array_search($info_search,$tid);
            $str = "";
            if($tid == 4){
                $str .= '<table width="100%" cellspacing="0" class="treeTable">';
                foreach ($list as $key => $val){
                    $list[$key]['title'] = explode('//',$val['title']);
                    //$list[$key]['title'][0];
                    foreach ($list[$key]['title'] as $k => $v){
                        if(!$k){
                            $str .= '<tr id="'.$key.'" class="collapsed">';
                            $str .= '<td class="select_td_txt"><span class="expander" style="padding-left:20px"></span>'.$v.'</td>';
                            $str .= '</tr>';
                        }else{
                            $str .= '<tr class="code-'.$key.' dispnone" style="display:none;">';
                            $str .= '<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└─ '.$v.'</td>';
                            $str .= '</tr>';
                        }
                    }
                }
                $str .= '</table>';
            }else{
                $str .= "<ul>";
                    foreach($list as $val) {
                        $title = $val['title'];
                        $str .= "<li rel=".$val['id'].">".msubstr($title,0,35,'utf-8',true)."</li>";
                    }
                $str .= '</ul>';
            }
        }else{
         $str .= "<ul><li>无数据</li></<ul>";
        }
        $this->ajaxReturn(1, '未知错误！', $str);

    }

}