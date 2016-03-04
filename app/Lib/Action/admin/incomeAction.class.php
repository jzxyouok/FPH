<?php
class incomeAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('income');
        $this->_id = $this->_get('id','intval');
    }
    //收入管理
    public function _before_index(){
        $id = $this->_get('id','intval');
        if(empty($id)){
            $this->error('参数有误');exit;
        }
        //应收入
        $income_price = M('commission')->where("pid=".$id)->getField('income');
        //实收入(总)
        $tot_price = M('income')->where("pid=".$id)->sum('price');
        $this->assign('id', $id);
        $this->assign('surplus', ($income_price-$tot_price));
        $this->assign('type', 'income');
        $this->assign('list_table', true);
    }
    protected function _search() {
         $map = array();
         $map['pid'] = $this->_id;
         return $map;
    }

    //收入管理 添加
    public function add(){
        if(IS_POST){
            $surplus =  $this->_post('surplus');
            $data['name'] =  $this->_post('name');
            $data['price'] =  $this->_post('price');
            $data['pid'] =  $this->_post('pid');
            $data['author'] =  $_COOKIE['admin']['username'];
            $data['income_time'] =  strtotime($this->_post('income_time'));
            $data['is_invoice'] =  $this->_post('is_invoice');
            $data['invoice_up'] =  '';
            $data['income_info'] =  $this->_post('income_info');
            $data['add_time'] =  time();
            // if($data['price'] >$surplus){
            //     $this->error('已超出剩余收入');exit;
            // }
            $id= M('income')->add($data);
            if($id){
                $this->success('修改成功',U('income/index',array('id'=>$data['pid'])));exit;
            }else{
                $this->error('修改失败',U('income/index',array('id'=>$data['pid'])));exit;
            }
        }
        $id = $this->_get('id','intval');
        $income_price = M('commission')->where("pid=".$id)->getField('income');
        $tot_price = M('income')->where("pid=".$id)->sum('price');
        $surplus = $income_price-$tot_price;
        if($surplus<0){
            $surplus = 0;
        }
        $this->assign('surplus', $surplus);
        $this->assign('id', $id);
        $this->assign('type', 'income');
        $this->display();
    }
    /**
     * ajax检测价格是否超出
     */
    public function ajax_check_price() {
        $price = $this->_get('price', 'trim');
        $id = $this->_get('id', 'intval');
        $iid = $this->_get('iid', 'intval');
        $p = $this->_mod->price_exists($price,$id,$iid);
        if($p<0){
            $p = 0;
        }
        $this->ajaxReturn(1, $p);

    }
    //收入管理 修改
    public function edit(){
        if(IS_POST){
            $iid =  $this->_post('iid');
            $surplus =  $this->_post('surplus');
            $data['name'] =  $this->_post('name');
            $data['price'] =  $this->_post('price');
            $data['pid'] =  $this->_post('pid');
            $tot_price = M('income')->where("id !=$iid and  pid=".$data['pid'])->sum('price');
            $income_price = M('commission')->where("pid=".$data['pid'])->getField('income');
            // if( ($tot_price+$data['price']) >$income_price){
            //     $this->error('已超出剩余收入');exit;
            // }
            $data['author'] =  $_COOKIE['admin']['username'];
            $data['income_time'] =  strtotime($this->_post('income_time'));
            $data['is_invoice'] =  $this->_post('is_invoice');
            $data['invoice_up'] =  '';
            if($data['is_invoice'] == 0){
                $data['invoice_up']= '';
            }
            $data['income_info'] =  $this->_post('income_info');
            $id= M('income')->where('id='.$iid)->save($data);
            if($id){
                $this->success('修改成功',U('income/index',array('id'=>$data['pid'])));exit;
            }else{
                $this->error('修改失败',U('income/index',array('id'=>$data['pid'])));exit;
            }
        }
        $iid = $this->_get('iid','intval');
        $id = $this->_get('id','intval');
        $income_price = M('commission')->where("pid=".$id)->getField('income');
        $tot_price = M('income')->where("pid=".$id)->sum('price');
        $info = M('income')->where("id=".$iid)->find();
        $surplus = $income_price-$tot_price;
        if($surplus<0){
            $surplus = 0;
        }
        $this->assign('surplus',$surplus);
        $this->assign('info', $info);
        $this->assign('id', $id);
        $this->assign('iid', $iid);
        $this->assign('type', 'income');
        $this->display();
    }
}