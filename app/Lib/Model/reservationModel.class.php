<?php
class reservationModel extends Model
{
    //自动完成
    protected $_auto = array(
        array('add_time', 'time', 1, 'function'),
    );
    //自动验证
    protected $_validate = array(
        array('title', 'require', '{%article_title_empty}'),
    );
    //关联关系
    protected $_link = array(
        'cate' => array(
            'mapping_type' => BELONGS_TO,
            'class_name' => 'article_cate',
            'foreign_key' => 'cate_id',
        )
    );
    public function addtime()
    {
        return date("Y-m-d H:i:s",time());
    }

    /**
     * @param $option
     * @param $fields 'id,uid,info,praise,status,add_time'
     * @return array
     */
    public function getList($option, $fields, $isPage = false)
    {
        M(NULL,NULL,C('DB_activity'));
        $where = $option;
        if($isPage){
            $count = M('reservation')->where($where)->count('id');
            import("ORG.Util.Page");
            $p = new Page($count, 20);
            $page = $p->show();
            $list = M('reservation')->field($fields)->where($where)->order('id DESC')->limit($p->firstRow.','.$p->listRows)->select();
            return array($page,$list);
        } else {
            $list = M('reservation')->field($fields)->where($where)->order('id DESC')->select();
            return $list;
        }

    }

    public function insertData($data)
    {
        M(NULL,NULL,C('DB_activity'));
        $id = M('reservation')->add($data);
        return $id;
    }

    public function onlyOneTrue($id, $pid)
    {
        M(NULL,NULL,C('DB_activity'));
        return M('reservation')->where('status = 1 and pid = '. $pid . ' and id !=' .$id )->save(array('status' => 0));
    }

    public function getOne($id)
    {
        return $info = M('reservation')->where('id = '. $id)->find();
    }

    public function updateById($id, $data)
    {
        return M('reservation')->where('id = '. $id)->save($data);
    }

    public function del($ids)
    {
        if(is_array($ids))
        {
            $where = 'id in ('.implode(',',$ids).')';

        } else{
           $where = 'id = '.$ids;
        }
        M('reservation')->where($where)->save(array('status' => 2));
    }

    /**
     * @param $option
     * @param $fields 'id,uid,info,praise,status,add_time'
     * @return array
     */
    public function Lists($where, $fields, $order, $isPage = false)
    {
        if($isPage){
            $count = M('reservation')->where($where)->count('id');
            import("ORG.Util.Page");
            $p = new Page($count, $isPage);
            $page = $p->show();
            $list = M('reservation')->field($fields)->where($where)->order($order)->limit($p->firstRow.','.$p->listRows)->select();
        } else {
            $list = M('reservation')->field($fields)->where($where)->order($order)->select();
        }

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

        foreach($list as $key=>$val){
            $list[$key]['title'] = $tmp[$val['pid']];
            $list[$key]['city_name'] = $cityArr[$val['pid']];
        }

        return array($list, $page);

    }

    public function getOneByPid($pid, $fields)
    {
        return $info = M('reservation')->where('pid = '. $pid)->field($fields)->select();
    }

}