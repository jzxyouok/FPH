<?php
/**
 * 前台控制器基类
 *
 * @author andery
 */
class frontendAction extends baseAction {

    protected $visitor = null;
    
    public function _initialize() {
        parent::_initialize();
        //网站状态
        if (!C('pin_site_status')) {
            header('Content-Type:text/html; charset=utf-8');
            exit(C('pin_closed_reason'));
        }
        //初始化访问者
        $this->_init_visitor();
        //第三方登陆模块
        $this->_assign_oauth();
        //网站导航选中
        $this->assign('nav_curr', '');
		if(GROUP_NAME=="home"){
			$city   = $this->_get('city', 'intval');
			if($city){
				cookie('head_city',null);
				cookie('head_select',null);
				cookie('head_city',$city);
			}else{
				if(empty($_COOKIE['head_city'])){
					cookie('head_city',803);
					cookie('head_select',1);
				}else{
					cookie('head_select',null);
				}
				$city = $_COOKIE['head_city'];
			}
			//城市
			$this->assign('city_list', $this->_city_list($city));
		}
    }
    
    /**
    * 初始化访问者
    */
    private function _init_visitor() {
        $this->visitor = new user_visitor();
        $this->assign('visitor', $this->visitor->info);
    }

    /**
     * 第三方登陆模块
     */
    private function _assign_oauth() {
        if (false === $oauth_list = F('oauth_list')) {
            $oauth_list = D('oauth')->oauth_cache();
        }
        $this->assign('oauth_list', $oauth_list);
    }
	
    /**
     * SEO设置
     */
    protected function _config_seo($seo_info = array(), $data = array()) {
        $page_seo = array(
            'title' => C('pin_site_title'),
            'keywords' => C('pin_site_keyword'),
            'description' => C('pin_site_description')
        );
        $page_seo = array_merge($page_seo, $seo_info);
        //开始替换
        $searchs = array('{site_name}', '{site_title}', '{site_keywords}', '{site_description}');
        $replaces = array(C('pin_site_name'), C('pin_site_title'), C('pin_site_keyword'), C('pin_site_description'));
        preg_match_all("/\{([a-z0-9_-]+?)\}/", implode(' ', array_values($page_seo)), $pageparams);
        if ($pageparams) {
            foreach ($pageparams[1] as $var) {
                $searchs[] = '{' . $var . '}';
                $replaces[] = $data[$var] ? strip_tags($data[$var]) : '';
            }
            //符号
            $searchspace = array('((\s*\-\s*)+)', '((\s*\,\s*)+)', '((\s*\|\s*)+)', '((\s*\t\s*)+)', '((\s*_\s*)+)');
            $replacespace = array('-', ',', '|', ' ', '_');
            foreach ($page_seo as $key => $val) {
                $page_seo[$key] = trim(preg_replace($searchspace, $replacespace, str_replace($searchs, $replaces, $val)), ' ,-|_');
            }
        }
        $this->assign('page_seo', $page_seo);
    }

    /**
    * 连接用户中心
    */
    protected function _user_server() {
        $passport = new passport(C('pin_integrate_code'));
        return $passport;
    }

    /**
     * 前台分页统一
     */
    protected function _pager($count, $pagesize) {
        $pager = new Page($count, $pagesize);
        $pager->rollPage = 5;
        $pager->setConfig('prev', '<');
        $pager->setConfig('theme', '%upPage% %first% %linkPage% %end% %downPage%');
        return $pager;
    }
    
    
    
    /**
     * 前台城市
     * 
     */
    protected function _city_list($select_city)
    {
		if(!isset($select_city) OR empty($select_city))
			$select_city =803;
			
		//区域显示
		$city = M('property')->field('city_id')->where('status = 1')->group('city_id')->select();
		if(empty($city))
			return '';
		$city_id = '';
		foreach($city as $k=>$v){
			if(!empty($v['city_id']))
			$city_id .= $this->city_one($v['city_id']).',';
		}
		$city_id = substr($city_id,0,-1);
		$city_id = array_unique(explode(',', $city_id));
        if($city_id){
            foreach($city_id as $key => $val){
                if(!$val) unset($city_id[$key]);
            }
        }
		$citylist = M('city')->where('id in('.implode(',',$city_id).')')->order('FIELD(id ,'.$select_city.') DESC ')->select();
		return $citylist;
    }
    
     //获取区域 市级别
    public function city_one($id)
    {
	$city = M('city')->where('id ='.$id)->find();
	$count = explode('|',$city['spid']);
	if(count($count) == 2)
	{
	    return $id;
	}
	if($city['pid'] == 0)
	{
	    return M('city')->where('id ='.$id)->getField('pid');
	}
	return $this->city_one($city['pid']);
    }
    

    

    
}