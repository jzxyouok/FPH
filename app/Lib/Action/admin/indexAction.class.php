<?php
class indexAction extends backendAction {

    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('menu');
    }
    public function index() {
	
        $top_menus = $this->_mod->admin_menu(0);
    	if(!empty($top_menus)){
    	    global $global_mid;
    	    $global_mid = reset($top_menus);
    	}
            $this->assign('top_menus', $top_menus);
            //$my_admin = array('username'=>$_SESSION['admin']['username'],'admin_role_id'=>$_SESSION['admin']['role_id']);
			$my_admin = array('username'=>$_COOKIE['admin']['username'],'role_id'=>$_COOKIE['admin']['role_id']);
            //$my_admin['rolename'] = M('admin_role')->where(array('id'=>$_SESSION['admin']['role_id']))->getfield('name');
			$my_admin['rolename'] = M('admin_role')->where(array('id'=>$_COOKIE['admin']['role_id']))->getfield('name');
            $this->assign('my_admin', $my_admin);
            $this->display();
        }
	
    public function left()
    {
        $menuid = $this->_request('menuid', 'intval');
        if ($menuid) {
            $left_menu = $this->_mod->admin_menu($menuid);
            foreach ($left_menu as $key=>$val) {
                $left_menu[$key]['sub'] = $this->_mod->admin_menu($val['id']);
				foreach($left_menu[$key]['sub'] as $k=>$v){
				  if($v['id']==288){
						$left_menu[$key]['sub'][$k]['id']=0;
				  }
				}
			//	print_r($left_menu[$key]['sub'] );
            }
        } else {
		//获取全局变量 左边权限模块
		$this->index();
		global $global_mid;
		$menuid = $global_mid['id'];    			
            $left_menu = $this->_mod->admin_menu($menuid);
            foreach ($left_menu as $key=>$val) {
                $left_menu[$key]['sub'] = $this->_mod->admin_menu($val['id']);
				foreach($left_menu[$key]['sub'] as $k=>$v){
				  if($v['id']==288){
						$left_menu[$key]['sub'][$k]['id']=0;
				  }
				}
			//	print_r($left_menu[$key]['sub'] );
            }
		}
        $this->assign('left_menu', $left_menu);
		//echo  $menuid;
		
		//exit;
        $this->display();
    }

    public function panel() {
        $message = array();
        if (is_dir('./install')) {
            $message[] = array(
                'type' => 'error',
                'content' => "您还没有删除 install 文件夹，出于安全的考虑，我们建议您删除 install 文件夹。",
            );
        }
        if (APP_DEBUG == true) {
            $message[] = array(
                'type' => 'error',
                'content' => "您网站的 DEBUG 没有关闭，出于安全考虑，我们建议您关闭程序 DEBUG。",
            );
        }
        if (!function_exists("curl_getinfo")) {
            $message[] = array(
                'type' => 'error',
                'content' => "系统不支持 CURL ,将无法采集商品数据。",
            );
        }
        $this->assign('message', $message);
        $system_info = array(
            'pinphp_version' => PIN_VERSION . ' ThinkPHP '. PIN_RELEASE .' ',
            'server_domain' => $_SERVER['SERVER_NAME'] . ' [ ' . gethostbyname($_SERVER['SERVER_NAME']) . ' ]',
            'server_os' => PHP_OS,
            'web_server' => $_SERVER["SERVER_SOFTWARE"],
            'php_version' => PHP_VERSION,
            'mysql_version' => mysql_get_server_info(),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'max_execution_time' => ini_get('max_execution_time') . '秒',
            'safe_mode' => (boolean) ini_get('safe_mode') ?  L('yes') : L('no'),
            'zlib' => function_exists('gzclose') ?  L('yes') : L('no'),
            'curl' => function_exists("curl_getinfo") ? L('yes') : L('no'),
            'timezone' => function_exists("date_default_timezone_get") ? date_default_timezone_get() : L('no')
        );
        $this->assign('system_info', $system_info);
        $this->display();
    }

    public function login() {
		
        if (IS_POST) {
            $username = $this->_post('username', 'trim');
            $password = $this->_post('password', 'trim');
            $verify_code = $this->_post('verify_code', 'trim');
            if(session('verify') != md5($verify_code)){
                $this->error(L('verify_code_error'));
            }
            $admin = M('admin')->where("(username='".$username."' OR mobile='".$username."') AND status=1")->find();
            if (!$admin) {
                $this->error(L('admin_not_exist'));
            }
            if ($admin['password'] != md5($password)) {
                $this->error(L('password_error'));
            }
            session('admin', array(
                'id' => $admin['id'],
                'role_id' => $admin['role_id'],
                'username' => $admin['username'],
				'expire' => 3600,
            ));
			
			//login 记录下cookie值
			setcookie("admin[id]",$admin['id'],time()+3600*24);
			setcookie("admin[role_id]",$admin['role_id'],time()+3600*24);
			setcookie("admin[username]",$admin['username'],time()+3600*24);
			
			
            M('admin')->where(array('id'=>$admin['id']))->save(array('last_time'=>time(), 'last_ip'=>get_client_ip()));
            $this->admin_log(M('admin'),$admin['id']);
            $this->success(L('login_success'), U('index/index'));
        } else {
            $this->display();
        }
    }
	

    public function logout() {
        session('admin', null);
		//清除登陆时存储cookie信息
		setcookie("admin[id]",$admin['id'],time()-3600);
		setcookie("admin[role_id]",$admin['role_id'],time()-3600);
		setcookie("admin[username]",$admin['username'],time()-3600);
		
        $this->success(L('logout_success'), U('index/login'));
        exit;
    }

    public function verify_code() {
        Image::buildImageVerify(4,1,'gif','50','24');
    }

    public function often() {
        if (isset($_POST['do'])) {
            $id_arr = isset($_POST['id']) && is_array($_POST['id']) ? $_POST['id'] : '';
            $this->_mod->where(array('ofen'=>1))->save(array('often'=>0));
            $id_str = implode(',', $id_arr);
            $this->_mod->where('id IN('.$id_str.')')->save(array('often'=>1));
            $this->admin_log($this->_mod,$id_str);
            $this->success(L('operation_success'));
        } else {
            $r = $this->_mod->admin_menu(0);
            $list = array();
            foreach ($r as $v) {
                $v['sub'] = $this->_mod->admin_menu($v['id']);
                foreach ($v['sub'] as $key=>$sv) {
                    $v['sub'][$key]['sub'] = $this->_mod->admin_menu($sv['id']);
                }
                $list[] = $v;
            }
            $this->assign('list', $list);
            $this->display();
        }
    }

    public function map() {
        $r = $this->_mod->admin_menu(0);
        $list = array();
        foreach ($r as $v) {
            $v['sub'] = $this->_mod->admin_menu($v['id']);
            foreach ($v['sub'] as $key=>$sv) {
                $v['sub'][$key]['sub'] = $this->_mod->admin_menu($sv['id']);
            }
            $list[] = $v;
        }
        $this->assign('list', $list);
        $this->display();
    }
}