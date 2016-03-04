<?php
class indexAction extends frontendAction {
    
    public function index() {
		$str = $_SERVER['HTTP_HOST'];
		$www_d = substr($str , 0 , 4);
		if($www_d=='mail'){
			header("Location: http://exmail.qq.com/login");exit;
		}
		
        $list = M('home_flo')->order('ordid ASC,add_time DESC')->limit(0,12)->where(array('status'=>1))->select();
        $this->assign('list',$list);
        $this->_config_seo();
        $city_id = $this->_get('city','intval');
        if(!$city_id){
            $city_id = $_COOKIE['head_city'];
        }
        $this->assign('setTitle', '房品汇');
        switch($city_id)
        {
            case 22:
                $this->tianjin();
                break;
            default :
                $this->display();
                break;
        }
    }

    //天津首页
    public function tianjin(){
        $this->assign('setTitle', '房品汇');
        $this->display('index:tianjin');
    }
}