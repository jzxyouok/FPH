<?php
class giftbag_recordModel extends Model {
    /**
     * 领取记录模型
     * mihailong
     * 2015-10-01
     */


    public function getList($gid,$firstPage,$pageSize){
        $firstPage = $firstPage?$firstPage:0;
        $pageSize = $pageSize?$pageSize:20;
        $field = 'id,uid,gid,record_time,trip,fund,status,origin';
        $data = $this->field($field)->where("gid = $gid and record_time > 0 ")->order('id DESC')->limit($firstPage,$pageSize)->select();
        return $data;
    }

    public function getCount($gid){
        return $this->where(array('gid'=>$gid))->count();
    }




}