<?php
class hongbaoAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('weixin_lottery');
    }

    public function index() {
        
        $fph=C('DB_PREFIX');
        $time_start = strtotime($this->_get('time_start','trim'));
        $time_end   = strtotime($this->_get('time_end','trim'));
        $mobile     = $this->_get('mobile','trim');
        $amount     = $this->_get('amount','trim');
        $status     = $this->_get('status','trim');

        $where = "A.type=3";
        if($time_start) {
            $where .= " AND A.add_time >= ".$time_start;
        }
        if($time_end) {
            $where .= " AND A.add_time <= ".$time_end;
        }
        if($mobile) {
            $uid = M('user')->where("mobile='".$mobile."'")->getfield('id');
            if (!$uid) $uid=0;
            $where .= " AND A.uid = ".$uid;
        }
        if($amount) {
            $where .= " AND A.amount >= ".$amount;
        }
        if($status!=''){
            $where .= " AND A.status = ".$status;
        }
        $count = $this->_mod->where($where)->table("{$fph}weixin_lottery AS A")->count('A.id');
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();
        $str = 'A.id,A.uid,A.amount,A.add_time,A.status,A.intro,B.mobile';     
        $list = $this->_mod->field($str)
                            ->where($where)
                            ->table("{$fph}weixin_lottery AS A LEFT JOIN {$fph}user AS B ON A.uid=B.id")
                            ->order('A.add_time DESC')
                            ->limit($p->firstRow.','.$p->listRows)
                            ->select();
        $this->assign('page',$page);
        $this->assign('list',$list);

        //统计红包总额
        $amount_total = $this->_mod->where(array('type'=>3))->sum('amount');
        $this->assign('amount_total',$amount_total);
        
        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);

        $this->assign('time_start',$time_start);
        $this->assign('time_end',$time_end);
        $this->assign('mobile',$mobile);
        $this->assign('amount',$amount);
        $this->assign('status',$status);
        $this->display();
    }

    

    
}