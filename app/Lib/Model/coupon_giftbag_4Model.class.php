<?php
class coupon_giftbag_4Model extends Model {
    /**
     * 礼包副表模型
     * mihailong
     * 2015-10-01
     */


    public function getList($id){
        return $this->where(array('gid'=>$id))->find();
    }



}