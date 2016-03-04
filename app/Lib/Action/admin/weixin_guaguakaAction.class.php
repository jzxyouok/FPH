<?php
class weixin_guaguakaAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('weixin_guaguaka');
    }

    public function _before_index() {
    	
    	//中奖区间所剩限额
    	$guaguaka_id=$this->_mod->field('id,geiling')->select();
    	foreach ($guaguaka_id as $val) {
    		$weixin_sum = M('weixin_lottery')->where('`interval`='.$val['id'])->sum('amount');
    		$weixin_poor[$val['id']] = ($val['geiling']-($weixin_sum*100)) >= 0 ? $val['geiling']-($weixin_sum*100) : 0;
    	}
    	$this->assign('weixin_poor_list', $weixin_poor);

        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);

        //默认排序
        $this->sort = 'id';
        $this->order = 'ASC';	
    }

    public function _before_add() {
    	
        $author = $_COOKIE['admin']['username'];
        $this->assign('author',$author);
    }

    protected function _before_insert($data) {    	
    	if (IS_POST){
	    	$grade      = $this->_post('grade','intval');
	    	$min        = $this->_post('min','intval');
	    	$max        = $this->_post('max','intval');
	    	$geiling    = $this->_post('geiling','intval');
	    	$proportion = $this->_post('proportion','intval');
	    	$cond       = $this->_post('cond','intval');

	    	//echo count($min);exit;
	    		
	    	for($i=0;$i<count($min);$i++){
				$data["grade"]       = $grade[$i];
				$data["min"]         = $min[$i];
				$data["max"]         = $max[$i];
				$data["geiling"]     = $geiling[$i];
				$data["proportion"]  = $proportion[$i];
				$data["cond"]        = $cond[$i];				
			    if(!empty($min[$i])){
				  M('weixin_guaguaka')->add($data);
			    }
			}
			    $this->success('添加成功');
			    exit;
    	}
        return $data;
    }

    public function _before_edit(){
        $id = $this->_get('id','intval');
    }

    protected function _before_update($data) {
    	$proportion = $this->_post('proportion','intval');
    	$min = $this->_post('min','intval');
    	$max = $this->_post('max','intval');
    	if($proportion>100){
    		$this->error('中奖率不能大于100');
    		exit;
    	}
    	// if($min>=$max){
    	// 	$this->error('最小值不能大于等于最大值');
    	// 	exit;
    	// }
    	
        return $data;
    }
	
}