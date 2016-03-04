<?php
/**
 * 后台控制器基类
 *
 * @author andery
 */
class backendAction extends baseAction
{
    protected $_name = '';
    protected $menuid = 0;
    public function _initialize() {

        parent::_initialize();
        $this->_name = $this->getActionName();	
/*
*ajax方式的操作权限判断 start
**/ 
    if($_COOKIE['admin']['role_id']!=1){
        if(IS_AJAX){
            
            $ajax_action_name = ACTION_NAME;
            $ajax_module_name = MODULE_NAME;
            //IS_AJAX && $this->ajaxReturn(0, MODULE_NAME);
            //遍历该module下的所有菜单项
            $menuid_array = D('menu')->where(array('module_name'=>$ajax_module_name))->field('id,name,action_name')->select();
            $role_id = $_COOKIE['admin']['role_id'];
            //遍历rolexid下的所有菜单项id
            $authid_array = M('admin_auth')->where(array('role_id'=>$role_id))->field('menu_id')->select();
            //匹配两组id 增加到新的数组中 $result_array
            $result_array = array();
            foreach ($authid_array as $key => $value) {
                foreach ($menuid_array as $k => $v) {
                     if ($value['menu_id'] == $v['id']){
                         $result_array[$key]['id'] = $value['menu_id'];   
                         $result_array[$key]['name'] = $v['name'];
                         $result_array[$key]['action_name'] = $v['action_name'];   
                     }                    
                }
            }
            //大权限 add del edit匹配完毕
             $ajax_caozuo = substr($ajax_action_name,-4);
             $ajax_caozuo6 = substr($ajax_action_name,-6);
            if($ajax_caozuo == '_add' || $ajax_action_name == 'add'){
                    //表示该ajax将要执行增加操作  遍历所有权限

                foreach ($result_array as $key => $value) {
                    # code...
                    if($value['action_name'] == 'add'){
                            //表示允许增加操作
                        $add = 1;
                    }
                }
                if(!$add){
                    $this->ajaxReturn(0, '没有权限');
                }
            }

            if($ajax_caozuo == '_del'){
                    //表示该ajax将要执行删除操作  遍历所有权限
                foreach ($result_array as $key => $value) {
                    # code...
                    if($value['action_name'] == 'delete'){
                            //表示允许删除操作
                        $delete = 1;
                    }
                }
                if(!$delete){
                    $this->ajaxReturn(0, '没有权限');
                }
            }
            if($ajax_caozuo == 'edit'){
                    //表示该ajax将要执行修改操作  遍历所有权限
                foreach ($result_array as $key => $value) {
                    # code...
                    if($value['action_name'] == 'edit'){
                            //表示允许修改操作
                        $edit = 1;
                    }
                }
                if(!$edit){
                    $this->ajaxReturn(0, '没有权限');
                }
            }
            if($ajax_caozuo6 == 'delete'){
                    //表示该ajax将要执行修改操作  遍历所有权限
                foreach ($result_array as $key => $value) {
                    # code...
                    if($value['action_name'] == 'delete'){
                            //表示允许修改操作
                        $delete6 = 1;
                    }
                }
                if(!$delete6){
                    $this->ajaxReturn(0, '没有权限');
                }
            }
        }
    }
        //ajax操作权限控制end

       // $this->roleid = $this->_request('roleid', 'intval');        
       // if(!$this->roleid){
        else{

        $this->check_priv();
    }
      //  }
        $this->menuid = $this->_request('menuid', 'trim', 0);

        if ($this->menuid) {
            $sub_menu = D('menu')->sub_menu($this->menuid, $this->big_menu);
            $selected = '';
            foreach ($sub_menu as $key=>$val) {
                $sub_menu[$key]['class'] = '';
                if (MODULE_NAME == $val['module_name'] && ACTION_NAME == $val['action_name'] && strpos(__SELF__, $val['data'])) {
                    $sub_menu[$key]['class'] = $selected = 'on';
                }
            }

            if (empty($selected)) {
                foreach ($sub_menu as $key=>$val) {
                    if (MODULE_NAME == $val['module_name'] && ACTION_NAME == $val['action_name']) {
                        $sub_menu[$key]['class'] = 'on';
                        break;
                    }
                }
            }
            $this->assign('sub_menu', $sub_menu);
        }
        $this->assign('menuid', $this->menuid);
    }

    /**
     * 列表页面
     */
    public function index() {

        $map = $this->_search();
        $mod = D($this->_name);

        !empty($mod) && $this->_list($mod, $map);
        $this->display();
    }

    /**
     * 添加
     */
    public function add() {
        $mod = D($this->_name);
          if (IS_POST) {
               foreach($list = $mod->create() as $k=>$v){
                  $list[$k] = trim($v);
               } 
            if (false === $data = $list) {
                IS_AJAX && $this->ajaxReturn(0, $mod->getError());
                $this->error($mod->getError());
            }
            if (method_exists($this, '_before_insert')) {
                $data = $this->_before_insert($data);
            }
            
            if( $mod->add($data) ){
               $dataid = $mod->getLastInsID();
                if( method_exists($this, '_after_insert')){
                    $id = $mod->getLastInsID();
                    $this->_after_insert($id);
                }
		$this->admin_log($mod,$dataid);
                IS_AJAX && $this->ajaxReturn(1, L('operation_success'), '', 'add');
                $this->success(L('operation_success'));
            } else {
                IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
                $this->error(L('operation_failure'));
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

    /**
     * 修改
     */
   
    public function edit(){
        $mod = D($this->_name);
        $pk = $mod->getPk();
        if (IS_POST) {
            foreach($list = $mod->create() as $k=>$v){
                  $list[$k] = trim($v);
            } 
            if (false === $data = $list) {
                IS_AJAX && $this->ajaxReturn(0, $mod->getError());
                $this->error($mod->getError());
            }
            if (method_exists($this, '_before_update')) {
                $data = $this->_before_update($data);
            }
            if (false !== $mod->save($data)) {
                if( method_exists($this, '_after_update')){
                    $id = $data['id'];
                    $this->_after_update($id);
                }
		$this->admin_log($mod,$data['id']);
                IS_AJAX && $this->ajaxReturn(1, L('operation_success'), '', 'edit');
                $this->success(L('operation_success'));
            } else {
                IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
                $this->error(L('operation_failure'));
            }
        } else {
            $id = $this->_get($pk, 'intval');
            $info = $mod->find($id);
            if (method_exists($this, '_after_edit')) {
                 $info = $this->_after_edit($info);
            }
            $this->assign('info', $info);
            $this->assign('open_validator', true);
            if (IS_AJAX) {
                $response = $this->fetch();
                $this->ajaxReturn(1, '', $response);
            } else {
                $this->display();
            }
        }
    }

    /**
     * ajax修改单个字段值
     */
    public function ajax_edit()
    {
        //AJAX修改数据
        $mod = D($this->_name);
        $pk = $mod->getPk();
        $id = $this->_get($pk, 'intval');
        $field = $this->_get('field', 'trim');
        $val = $this->_get('val', 'trim');
        //允许异步修改的字段列表  放模型里面去 TODO
        $mod->where(array($pk=>$id))->setField($field, $val);
        $dataid = $mod->getLastInsID();
        $this->admin_log($mod,$dataid);
        $this->ajaxReturn(1);
    }

    /**
     * 删除
     */
    public function delete()
    {
        
        $mod = D($this->_name);

        $pk = $mod->getPk();
        
        $ids = trim($this->_request($pk), ',');

        if ($ids) {
            if (false !== $mod->delete($ids)) {
                $this->admin_log($mod,$ids);
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

    /**
     * 获取请求参数生成条件数组
     */
    protected function _search() {
        //生成查询条件
        $mod = D($this->_name);
        $map = array();
        foreach ($mod->getDbFields() as $key => $val) {
            if (substr($key, 0, 1) == '_') {
                continue;
            }
            if ($this->_request($val)) {
                $map[$val] = $this->_request($val);
            }
        }
        return $map;
    }

    /**
     * 列表处理
     *
     * @param obj $model  实例化后的模型
     * @param array $map  条件数据
     * @param string $sort_by  排序字段
     * @param string $order_by  排序方法
     * @param string $field_list 显示字段
     * @param intval $pagesize 每页数据行数
     */
    protected function _list($model, $map = array(), $sort_by='', $order_by='', $field_list='*', $pagesize=20)
    {
        //排序 
        $mod_pk = $model->getPk();
        if ($this->_request("sort", 'trim')) {
            $sort = $this->_request("sort", 'trim');
        } else if (!empty($sort_by)) {
            $sort = $sort_by;
        } else if ($this->sort) {
            $sort = $this->sort;
        } else {
            $sort = $mod_pk;
        }
        if ($this->_request("order", 'trim')) {
            $order = $this->_request("order", 'trim');
        } else if (!empty($order_by)) {
            $order = $order_by;
        } else if ($this->order) {
            $order = $this->order;
        } else {
            $order = 'DESC';
        }

        //如果需要分页
        if ($pagesize) {
            $count = $model->where($map)->count($mod_pk);
            $pager = new Page($count, $pagesize);
        }
        $select = $model->field($field_list)->where($map)->order($sort . ' ' . $order);
        $this->list_relation && $select->relation(true);
        if ($pagesize) {
            $select->limit($pager->firstRow.','.$pager->listRows);
            $page = $pager->show();
             $this->assign("page", $page);
        }
        $list = $select->select();

        $this->assign('list', $list);
        $this->assign('list_table', true);
    }

     public function check_priv() {        
        if (MODULE_NAME == 'attachment') {
            return true;
        }

        if ( (!isset($_COOKIE['admin']) || !$_COOKIE['admin']) && !in_array(ACTION_NAME, array('login','verify_code')) ) {
            $this->redirect('index/login');
        }
        if($_COOKIE['admin']['role_id'] == 1) {
            return true;
        }
        if (in_array(MODULE_NAME, explode(',', 'index'))) {
            return true;
        }
        $menu_mod = M('menu');
        $menu_id = $menu_mod->where(array('module_name'=>MODULE_NAME, 'action_name'=>ACTION_NAME))->getField('id');
        //$priv_mod = D('admin_role_priv');      
        //echo $menu_mod->getlastsql();
        $priv_mod = D('admin_auth');
        $r = $priv_mod->where(array('menu_id'=>$menu_id, 'role_id'=>$_COOKIE['admin']['role_id']))->count();
        //echo $priv_mod->getlastsql();
        if (!$r) {     
            //$this->error(L('_VALID_ACCESS_'),U('index/panel'));
			exit;
        }
    }
    
    protected function update_config($new_config, $config_file = '') {
        !is_file($config_file) && $config_file = CONF_PATH . 'home/config.php';
        if (is_writable($config_file)) {
            $config = require $config_file;
            $config = array_merge($config, $new_config);
            file_put_contents($config_file, "<?php \nreturn " . stripslashes(var_export($config, true)) . ";", LOCK_EX);
            @unlink(RUNTIME_FILE);
            return true;
        } else {
            return false;
        }
    }
    
	//管理员日志记录
     public function admin_log($mod,$id){
        
        $admin_log = M('admin_log');
        $action = '';
        switch(ACTION_NAME){
            case 'add':
                $action='添加操作';
            break;
            case 'edit':
                    $action='修改操作';
                break;
            case 'delete':
                    $action='删除操作';
                break;
             case 'login':
                    $action='登录操作';
                break;
            case 'url':
                    $action='URL模式操作';
                break;
            default:
            $action='功能操作';
        }
        
        $info = $action.',操作组名为'.GROUP_NAME.',控制器名为:'.$this->_name.',操作方法为:'.ACTION_NAME;
        if($id){
            $info .=',对应标识ID为:'.$id;
        }
        //$ip = get_client_ip();
        $ip = get_ip();
        $url = "index.php?g=".GROUP_NAME."&m=".$this->_name."&a=".ACTION_NAME;
        $log['url'] = $url;
        $log['new_ip'] = ip2long($ip);
		if($_COOKIE['admin']['username']){
        	$log['username'] = $_COOKIE['admin']['username'];
		}else{
			$log['username'] = $_SESSION['admin']['username'];
		}
        $log['info'] = $info;
        $log['add_time'] = time();
        $admin_log->data($log)->add();
    }
}
