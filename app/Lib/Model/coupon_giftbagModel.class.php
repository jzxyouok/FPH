<?php
class coupon_giftbagModel extends Model {
    /**
     * 优惠礼包模型
     * mihailong
     * 2015-10-01
     */


    public function getList($firstPage,$pageSize){
        $firstPage = $firstPage?$firstPage:0;
        $pageSize = $pageSize?$pageSize:20;
        $field = 'id,tid,planissue,time_start,time_end,status';
        $data = $this->field($field)->order('id DESC')->limit($firstPage,$pageSize)->select();
        return $data;
    }

    public function getCount(){
        return $this->count();
    }

    public function checkGiftBag($data){
        if($data['id']){
            $where['id'] = array('neq',$data['id']);
        }
        $where['tid'] = $data['tid'];
        $where['status'] = 1;
        return  $this->where($where)->find();
    }




}