<?php
// +----------------------------------------------------------------------
// | Descriptions:本页面作为后台的数据测试，数据库运行，前端人员的页面修改添加
// +----------------------------------------------------------------------
// | State : 不可以删除此页面，如要删除，前联系作者。
// +----------------------------------------------------------------------
// | Date: 2014-11-18 
// +----------------------------------------------------------------------
// | Author: chenli
// +----------------------------------------------------------------------


class testAction extends backendAction{

	public function _initialize() {
        parent::_initialize();
        //$this->_mod = D('article');
        //$this->_cate_mod = D('article_cate');
    }
	
	/*
	*@Descriptions：所有页面的入口
	*@Date:2014-11-18
	*@Author: chenli
	*/
	public function index(){
		
		
		$this->display();
	}

	public function img()
	{
		$this->display();
	}
	
	/*
	*@Descriptions：地图数据弹出页面
	*@Date:2014-11-18
	*@Author: chenli
	*/
	public function metro(){
		if (IS_AJAX) {
			
			//地铁数据
			$metro_list = M('metro')->field('id,name')->where(array('pid'=>0))->select();
			foreach($metro_list as $key=>$val){
				$metro_list[$key]['subway'] = M('metro')->field('id,name')->where(array('pid'=>$val['id']))->select();
			}
			$this->assign('metro_list', $metro_list);
			
			$response = $this->fetch();
			$this->ajaxReturn(1, '', $response);
		} else {
			$this->display();
		}
	}

}