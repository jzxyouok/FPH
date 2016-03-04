<?php
class adModel extends RelationModel {

    public function lists($where, $field){

        $count = M('ad')->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();

        $list = M('ad')->field($field)->where($where)->order('add_time DESC')->limit($p->firstRow.','.$p->listRows)->select();
        foreach($list as $key=>$val){
            $adboardWhere = 'id = '.$val['board_id'];
            $adboardInfo = D('adboard')->readInfo($adboardWhere);
            $list[$key]['adboardname'] = $adboardInfo['name'];
        }
        return array($list,$page);
    }

    //添加
    public function insterData($data){
        $returnData = M('ad')->add($data);
        return $returnData;
    }

    //查询
    public function readInfo($where, $field = '*'){
        $returnData = M('ad')->field($field)->where($where)->find();
        return $returnData;
    }

    //查询
    public function readlists($where, $field = '*'){
        $returnData = M('ad')->field($field)->where($where)->select();
        return $returnData;
    }

    //修改
    public function updateInfo($where, $data){
        $returnData = M('ad')->where($where)->save($data);
        return $returnData;
    }

    //关联关系
    protected $_link = array(
        'adbord' => array(
            'mapping_type' => BELONGS_TO,
            'class_name' => 'adboard',
            'foreign_key' => 'board_id',
        ),
    );
	
	/**
     * 删除图片
     */
    public function admin_del_img($ids) {
		$ids = explode(",",$ids);
		if($ids){
            foreach($ids as $key=>$val){
				$img = M('ad')->where(array('id'=>$val))->getfield('content');
				unlink('./data/upload/advert/'.$img);
			}
        } 
    }
}