<?php
class expenses_extendModel extends RelationModel {


    //添加
    public function insertData($data){
        $returnData = M('expenses_extend')->add($data);
        return $returnData;
    }

    //查询
    public function readInfo($where, $field = '*'){
        $returnData = M('expenses_extend')->field($field)->where($where)->find();
        return $returnData;
    }


}