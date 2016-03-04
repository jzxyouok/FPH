<?php
class receiveModel extends Model
{
    public function links($where)
    {
        $data = M('receive')->where($where)->count('id');
        return $data;
    }

    public function getPid($sid)
    {
        $data = M('receive')->where(array('id' => $sid))->getField('pid');
        return $data;
    }

    //检测用户是否存在异常的领取路费记录
    public function checkRecord($uid)
    {
        $data = $this->where('status = 1 and uid = '. $uid)->group('pid')->having("count('id') > 1")->select();
        return $data;
    }

}