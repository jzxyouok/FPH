<?php
class adboardAction extends backendAction {
    public function _initialize() {
        parent::_initialize();
        M('adboard','fph_',C('DB_ad'));
        $this->_mod = D('adboard');
    }

    public function index() {
        M('adboard','fph_',C('DB_ad'));
        $where = 'status != 2';
        $list = D('adboard')->lists($where);
        $this->assign('list', $list[0]);
        $this->assign('page', $list[1]);

        $big_menu = array(
            'title' => L('adboard_add'),
            'iframe' => U('adboard/add'),
            'id' => 'add',
            'width' => '600',
            'height' => ''
        );
        $this->assign('big_menu', $big_menu);

        $this->display();
    }

    public function add() {
        M('adboard','fph_',C('DB_ad'));
        if (IS_POST) {
            $name        = $this->_post('name', 'trim');
            $width       = $this->_post('width', 'trim');
            $height      = $this->_post('height', 'trim');
            $description = $this->_post('description', 'trim');
            $status      = $this->_post('status', 'trim');
            !$name && $this->ajaxReturn(0, '请输入广告位名称');

            $data['name']        = $name;
            $data['width']       = $width;
            $data['height']      = $height;
            $data['description'] = $description;
            $data['status']      = $status;

            if(false !== D('adboard')->insterData($data)){
                $this->ajaxReturn(1, L('operation_success'), '', 'add');
            }else{
                $this->ajaxReturn(0, L('operation_failure'));
            }
        } else {
            $this->assign('open_validator', true);
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(1, '', $response);
            } else {
                $this->display();
            }
        }
    }

    public function edit() {
        M('adboard','fph_',C('DB_ad'));
        if (IS_POST) {
            $id          = $this->_post('id', 'trim');
            $name        = $this->_post('name', 'trim');
            $width       = $this->_post('width', 'trim');
            $height      = $this->_post('height', 'trim');
            $description = $this->_post('description', 'trim');
            $status      = $this->_post('status', 'trim');
            !$id && $this->ajaxReturn(0, '系统参数出错');
            !$name && $this->ajaxReturn(0, '请输入广告位名称');

            $data['name']        = $name;
            $data['width']       = $width;
            $data['height']      = $height;
            $data['description'] = $description;
            $data['status']      = $status;

            $where = 'id = '.$id;
            if(false !== D('adboard')->updateInfo($where, $data)){
                $this->ajaxReturn(1, L('operation_success'), '', 'edit');
            }else{
                $this->ajaxReturn(0, L('operation_failure'));
            }
        } else {
            $this->assign('open_validator', true);
            if (IS_AJAX) {
                $id = $this->_get('id', 'intval');
                $where = 'id = '.$id;
                $info = D('adboard')->readInfo($where);
                $this->assign('info', $info);

                $response = $this->fetch();
                $this->ajaxReturn(1, '', $response);
            } else {
                $this->display();
            }
        }
    }

    public function ajax_check_name() {
        M('adboard','fph_',C('DB_ad'));
        $name = $this->_get('name', 'trim');
        $id = $this->_get('id', 'intval');
        if (D('adboard')->name_exists($name, $id)) {
            $this->ajaxReturn(0, L('adboard_already_exists'));
        } else {
            $this->ajaxReturn();
        }
    }

    //删除
    public function delete(){
        M('adboard','fph_',C('DB_ad'));
        $id = $this->_get('id','intval');
        !$id && $this->ajaxReturn(0, L('operation_failure'));
        $where = 'id = '.$id;
        $data['status'] = 2;
        if(false !== D('adboard')->updateInfo($where, $data)){
            $this->ajaxReturn(1, L('operation_success'));
        }else{
            $this->ajaxReturn(0, L('operation_failure'));
        }
    }
}