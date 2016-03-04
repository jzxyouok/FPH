<?php
class weixin_keyword_replyModel extends RelationModel
{
    //自动完成
    protected $_auto = array(
        array('add_time', 'time', 1, 'function'),
    );
    //自动验证
    protected $_validate = array(
        array('keyword', 'require', '关键字不能为空'),
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

    //读取消息
    public function read_reply($pid){
        $list = M('weixin_reply')->field('id,title,img')->where(array('pid'=>$pid,'status'=>1))->order('add_time DESC')->limit(50)->select();
        return $list;
    }
    //读取默认id
    public function read_reply_pid($pid){
       $data = M('weixin_reply')->field('id')->where(array('pid'=>$pid,'status'=>1))->order('add_time DESC')->find();
       return $data;
    }

    //输入模糊搜索
    public function input_array_search($info_search,$tid){
        $str = 'id,title,img';
            $list = M('weixin_reply')->field($str)->where('pid='.$tid.' AND title like "%'.$info_search.'%" AND status=1')->order('add_time DESC')->limit(50)->select();
        return $list;
    }

}