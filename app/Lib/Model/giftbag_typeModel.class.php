<?php
class giftbag_typeModel extends Model {
    /**
     * 礼包类型模型
     * mihailong
     * 2015-10-01
     */


    public function getListByKey(){
        $data = array();
        $field = 'id,name';
        $result = $this->field($field)->where(array('status'=>1))->order('id DESC')->select();
        foreach($result as $val){
            $data[$val['id']] = $val['name'];
        }
        return $data;
    }



}