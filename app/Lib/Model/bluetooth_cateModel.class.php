<?php
class bluetooth_cateModel extends Model
{
    public function lists($city_id, $major){

        $where = 'status=1';
        if($city_id){
            M('city','fph_',C('DB_fangpinhui'));
            $idsArr = M('city')->field('id')->where('id = '.$city_id.' or spid RLIKE "[[:<:]]'.$city_id.'[[:>:]]"')->select();
            foreach($idsArr as $val){
                $ids .= $val['id'].',';
            }
            $ids = substr($ids,0,-1);
            if($ids){
                $where .=' AND city_id in('.$ids.')';
            }
            //查询所属城市
            $spid_city = M('city')->where(array('id'=>$city_id))->getField('spid');
            if($spid_city==0 ){
                $spid_city = $city_id;
            }else{
                $spid_city .= $city_id;
            }
        }
        if($major){
            $where .=' AND major = '.$major.'';
        }

        M(NULL,'fph_',C('DB_property'));
        $count = M('bluetooth_cate')->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();
        $str = 'id,city_id,major,add_time,status';
        $list = M('bluetooth_cate')->field($str)->where($where)->order('add_time DESC')->limit($p->firstRow.','.$p->listRows)->select();
        M('city','fph_',C('DB_fangpinhui'));
        foreach($list as $key=>$val){
            //所在城市
            if($val['city_id']){
                $city_name = M('city')->where('id='.$val['city_id'])->getField('name');
                $list[$key]['city_name'] = $city_name;
            }
        }
        return array($list, $page, $spid_city);
    }



    public function insertData($data){
        $returnData = M('bluetooth_cate')->add($data);
        return $returnData;
    }

    //判断统一个城市major号是否存在
    public function majorCount($where){
        $returnData = M('bluetooth_cate')->where($where)->count('id');
        return $returnData;
    }

    //删除
    public function delete($where, $data){
        $returnData = M('bluetooth_cate')->where($where)->save($data);
        return $returnData;
    }

    //导出
    public function export(){
        $where = 'status=1';
        $str = 'id,city_id,major,add_time,status';
        $list = M('bluetooth_cate')->field($str)->where($where)->order('add_time DESC')->select();
        M('city','fph_',C('DB_fangpinhui'));
        foreach($list as $key=>$val){
            //所在城市
            if($val['city_id']){
                $city_name = M('city')->where('id='.$val['city_id'])->getField('name');
                $list[$key]['city_name'] = $city_name;
            }
        }
        return $list;
    }

    //magor
    public function ajax_major($city_id){
        $list = M('bluetooth_cate')->field('id,major')->where(array('city_id'=>$city_id,'status'=>1))->order('id ASC')->select();
        return $list;
    }

    //查找单个magor
    public function majorInfo($field, $where){
        $data = M('bluetooth_cate')->field($field)->where($where)->find();
        return $data;
    }

}