<?php
class ideal_priceAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('ideal_price');
    }

    public function _before_index() {

        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);
		
		$big_menu = array(
            'title' => '添加价格',
            'iframe' => U('ideal_price/add'),
            'id' => 'add',
            'width' => '500',
            'height' => '100'
        );
        $this->assign('big_menu', $big_menu);

        //默认排序
        $this->sort = 'id';
        $this->order = 'ASC';
    }

    protected function _search() {
        $map = array();
        
        return $map;
    }

    public function _before_add(){
       

    }

    protected function _before_insert($data) {
	
        return $data;
    }

    public function _before_edit(){
       
    }

    protected function _before_update($data) {

        return $data;
    }
}