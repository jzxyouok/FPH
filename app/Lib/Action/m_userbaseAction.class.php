<?php
/**
 * 用户控制器基类
 *
 * @author chl
 */
class m_userbaseAction extends m_frontendAction {

    public function _initialize(){
        parent::_initialize();
		$url = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		$jump_url = __ROOT__.'/index.php?g=m&m=user&a=login&url='.$url;
        //访问者控制
        if (!$this->m_user_cookie['id'] && !in_array(ACTION_NAME, array('login', 'send_sms'))) {
            IS_AJAX && $this->ajaxReturn(0, L('login_please'));
			header("Location: $jump_url");
        }
    }

}