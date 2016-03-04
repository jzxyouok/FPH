<?php
class grouponModel extends RelationModel
{
    public function lists($field, $order, $page=20) {
        $where ='1 = 1';
        $count = M('groupon')->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, $page);
        $page = $p->show();

        $list = M('groupon')->field($field)->where($where)->order($order)->limit($p->firstRow.','.$p->listRows)->select();
        $pidArr = array_unique(i_array_column($list, 'pid'));
        $propertyWhere = 'id in ('. implode(',', $pidArr).')';
        $propertyField = 'id,title,city_id';
        M('property', 'fph_', C('DB_fangpinhui'));
        $propertyList = M('property')->field($propertyField)->where($propertyWhere)->select();

        $tmp = array();
        $cityArr = array();
        foreach($propertyList as $key => $val){
            $tmp[$val['id']] = $val['title'];

            $citySpid = M('city')->where('id ='.$val['city_id'])->getField('spid');
            $spidArr = explode('|', $citySpid.$val['city_id']);
            $name = '';
            foreach ($spidArr as $k => $v) {
                $name .= M('city')->where('id ='.$v)->getField('name').' ';
            }
            $cityArr[$val['id']] = $name;
        }

        M('join_groupon', 'fph_', C('DB_activity'));
        foreach($list as $key=>$val){
            $joinWhere = 'gid = '.$val['id'].' AND status = 1';
            $list[$key]['count'] = D('join_groupon')->joinGroupOnCount($joinWhere);
            $list[$key]['title'] = $tmp[$val['pid']];
            $list[$key]['city_name'] = $cityArr[$val['pid']];
        }
        return array($list, $page);
    }

    //楼盘团购信息
    public function getFind($field, $where) {
        $data = M('groupon')->field($field)->where($where)->find();
        return $data;
    }
}