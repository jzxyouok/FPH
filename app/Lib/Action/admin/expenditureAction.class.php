<?php
class expenditureAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('expenditure');
        $this->_id = $this->_get('id','intval');
    }
    //支出管理
    public function _before_index(){
        $id = $this->_get('id','intval');
        if(empty($id)){
            $this->error('参数有误');exit;
        }
        //应支出
        $expenditure_price = M('commission')->where("pid=".$id)->getField('expenditure');
        //实支出(总)
        $tot_price = M('expenditure')->where("pid=".$id)->sum('price');
        $this->assign('id', $id);
        $this->assign('surplus', ($expenditure_price-$tot_price));
        $this->assign('type', 'expenditure');
        $this->assign('list_table', true);
    }
    protected function _search() {
         $map = array();
         $map['pid'] = $this->_id;
         return $map;
    }
    //支出管理 添加
    public function add(){
        if(IS_POST){
            $surplus =  $this->_post('surplus');
            $data['name'] = '4';
            $data['price'] =  $this->_post('price');
            $data['pid'] =  $this->_post('pid');
            $data['author'] =  $_COOKIE['admin']['username'];
            $data['expenditure_time'] =  strtotime($this->_post('expenditure_time'));
            $data['is_invoice'] =  $this->_post('is_invoice');
            $data['invoice_up'] =  $this->_post('invoice_up');
            $data['expenditure_info'] =  $this->_post('expenditure_info');
            $data['invoice_count'] = $this->_post('invoice_count');
            $data['add_time'] =  time();
            $id= M('expenditure')->add($data);
            if($id){
                $this->success('修改成功',U('expenditure/index',array('id'=>$data['pid'])));exit;
            }else{
                $this->error('修改失败',U('expenditure/index',array('id'=>$data['pid'])));exit;
            }
        }
        $id = $this->_get('id','intval');
        $expenditure_price = M('commission')->where("pid=".$id)->getField('expenditure');
        $tot_price = M('expenditure')->where("pid=".$id)->sum('price');
        $surplus = $expenditure_price-$tot_price;
        if($surplus<0){
            $surplus = 0;
        }
        $this->assign('surplus', $surplus);
        $this->assign('id', $id);
        $this->assign('type', 'expenditure');
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
    //支出管理 修改
    public function edit(){
        if(IS_POST){
            $iid =  $this->_post('iid');
            $surplus =  $this->_post('surplus');
            $data['name'] = '4';
            $data['price'] =  $this->_post('price');
            $data['pid'] =  $this->_post('pid');

            $tot_price = M('expenditure')->where("id !=$iid and  pid=".$data['pid'])->sum('price');
            $expenditure_price = M('commission')->where("pid=".$data['pid'])->getField('expenditure');
            $data['author'] =  $_COOKIE['admin']['username'];
            $data['expenditure_time'] =  strtotime($this->_post('expenditure_time'));
            $data['is_invoice'] =  $this->_post('is_invoice');
            $data['invoice_up'] =  $this->_post('invoice_up');
            $data['invoice_count'] = $this->_post('invoice_count');
            if($data['is_invoice'] == 0){
                $data['invoice_up']= '';
            }
            $data['expenditure_info'] =  $this->_post('expenditure_info');
            $id= M('expenditure')->where('id='.$iid)->save($data);

            if($id){
                $this->success('修改成功',U('expenditure/index',array('id'=>$data['pid'])));exit;
            }else{
                $this->error('修改失败',U('expenditure/index',array('id'=>$data['pid'])));exit;
            }
        }
        $iid = $this->_get('iid','intval');
        $id = $this->_get('id','intval');
        $expenditure_price = M('commission')->where("pid=".$id)->getField('expenditure');
        $tot_price = M('expenditure')->where("pid=".$id)->sum('price');
        $info = M('expenditure')->where("id=".$iid)->find();
        $surplus = $expenditure_price-$tot_price;
        if($surplus<0){
            $surplus = 0;
        }
        $this->assign('surplus', $surplus);
        $this->assign('info', $info);
        $this->assign('id', $id);
        $this->assign('iid', $iid);
        $this->assign('type', 'expenditure');
        $this->display();
    }

}