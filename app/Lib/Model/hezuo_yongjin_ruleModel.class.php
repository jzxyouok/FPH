<?php
class hezuo_yongjin_ruleModel extends RelationModel
{
    //自动完成
    protected $_auto = array(
        //array('add_time', 'time', 1, 'function'),
    );
    //自动验证
    protected $_validate = array(
        //array('title', 'require', '{%article_title_empty}'),
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
       // return date("Y-m-d H:i:s",time());
    }
	
	//检测楼盘是否存在
	public function name_exists($pid, $id=0) {
        $where = "pid=" . $pid . " AND id<>'" . $id . "'";
        $result = $this->where($where)->count('id');
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
}