<?php
/**
 * 用户控制器基类
 *
 * @author andery
 */
class weixin_userbaseAction extends frontendAction {

    public function _initialize(){
        parent::_initialize();
		//$user_info = $this->visitor->is_login;
		$url = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		$jump_url = __ROOT__.'/index.php?g=weixin&m=user&a=login&url='.$url;
        //访问者控制

        if(MODULE_NAME != 'user' || ACTION_NAME != 'index'){
            if (!$this->visitor->is_login && !in_array(ACTION_NAME, array('login', 'register', 'ajax_stores', 'ajax_check', 'share', 'myshare', 'send_sms'))) {
                IS_AJAX && $this->ajaxReturn(0, L('login_please'));
    			header("Location: $jump_url");
            }
        }
    }

}