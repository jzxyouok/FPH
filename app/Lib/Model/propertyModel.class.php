<?php
// +----------------------------------------------------------------------
// | Descriptions:本页面作为 楼盘数据处理
// +----------------------------------------------------------------------
// | State : 不可以删除此页面，如要删除，前联系作者。
// +----------------------------------------------------------------------
// | Date: 2014-11-18 
// +----------------------------------------------------------------------
// | Author: wsj
// +----------------------------------------------------------------------

class propertyModel extends RelationModel
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

    /*
    *@Descriptions：楼盘获取 楼盘分类
    *@param array $cate  楼盘分类  key 字段 value 楼盘分类id
    *@return array
    *@Date:2014-11-18
    *@Author: wsj
    */
    public function property_cate($cate)
    {
        if(!is_array($cate))
            return false;

        $array = array();
        foreach ($cate as $key => $value) {
             $array[$key] = M('property_cate')->field('id,name,pid')->where('pid ='.$value)->select();
        }
        return $array;
    }

    /*
    *@Descriptions：地铁关联，字符串转换数组
    *@param string $str
    *@return array
    *@Date:2014-11-19
    *@Author: wsj
    */
    public function metro($str)
    {
        if(empty($str))
            return false;
        
        $array = array();
        $strarray = explode('|', $str);
        foreach ($strarray as $k1 => $v) {
            $arr = explode('&', $v);
            $metro = M('metro')->field('id,pid,name')->where('id ='.reset($arr))->find();
            $array[$k1]['metro_id'] = $metro['id'];
            $array[$k1]['metro_pid'] = $metro['pid'];
            $array[$k1]['metro_name'] = $metro['name'];
            $endarr = explode(',', end($arr));
            foreach ($endarr as $k2 => $n) {
                $array[$k1]['metro_end'][$k2] = M('metro')->field('id,pid,name')->where('id ='.$n)->find();
            }
        }
        return $array;
    }
    //查询路费
    public function expenses_list($id){
        $list = M('expenses')->where(array('pid'=>$id))->order('add_time DESC')->select();
        return $list;
    }

    //添加路费
    public function expenses_insert($data){
        $result_data = M('expenses')->add($data);
        return $result_data;
    }

    //判断是否存在有效路费
    public function expenses_count_status($pid, $time_end=false, $id=false){
        if(!$time_end){
            $time = strtotime(date('Y-m-d'));
            $data = M('expenses')->where("pid=".$pid." AND time_start <= ".$time." AND time_end >= ".$time." AND status != 2")->count('id');
        }else{
            $data = M('expenses')->where("id != ".$id." AND pid=".$pid." AND time_start < ".time()." AND time_end > ".time()." AND time_start <= ".$time_end." AND status != 2")->count('id');
            //echo M('expenses')->getLastSql();exit;
        }
        return $data;
    }

    //删除路费
    public function expenses_delete($id){
        $data = M('expenses')->where(array('id'=>$id))->save(array('status'=>2));
        return $data;
    }

    //编辑路费*获取详细信息
    public function expenses_info($id){
        $data = M('expenses')->where(array('id'=>$id))->find();
        $data['forecast_money'] = $data['copies'] * $data['rule'];
        return $data;
    }

    //修改路费
    public function expenses_update($id,$data){
        $data = M('expenses')->where(array('id'=>$id))->save($data);
        return $data;
    }

    //统计支出路费的人数和金额
    public function expenses_count_people($list){
        M("receive",C('DB_PREFIX'),C('DB_member'));
        foreach($list as $key=>$val){
            $list[$key]['people'] = M('receive')->where(array('rule_id'=>$val['id']))->count('id');
            $list[$key]['defray'] = $list[$key]['people'] * $val['rule'];
            $list[$key]['forecast_money'] = $list[$key]['copies'] * $val['rule'];
            $list[$key]['surplus_copies'] = $list[$key]['copies'] - $list[$key]['people'];
        }
        return $list;
    }

    //统计路费领取份数
    public function expenses_count_copies($pid){
        M("receive",C('DB_PREFIX'),C('DB_member'));
        $data = M('receive')->where(array('rule_id'=>$pid))->count('id');
        return $data;
    }

    //读取楼盘评论列表
    public function property_bbs_index($pid){
        M(NULL,NULL,C('DB_bbs'));
        $where ='pid = '.$pid;
        $count = M('comment')->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();
        $list = M('comment')->field('id,uid,info,praise,status,add_time')->where($where)->order('id DESC')->limit($p->firstRow.','.$p->listRows)->select();
        foreach($list as $key=>$val){
            $list[$key]['reply_count'] = M('comment_reply')->where(array('cid'=>$val['id']))->count('id');
            M(NULL,'fph_',C('DB_member'));
            $user_info = M('member')->field('mobile')->where(array('id'=>$val['uid']))->find();
            $user_extend = M('member_extend')->field('username')->where(array('uid'=>$val['uid']))->find();
            $list[$key]['username'] = $user_extend['username'];
            $list[$key]['mobile']      = $user_info['mobile'];
        }
        return array($page,$list);
    }

    //读取楼盘评论详细信息
    public function property_bbs_info($pid){
        M(NULL,NULL,C('DB_bbs'));
        $data = M('comment')->field('id,uid,info,praise,status,add_time')->where(array('id'=>$pid))->find();
        $data['reply_count'] = M('comment_reply')->where(array('cid'=>$data['id']))->count('id');
        $data['img'] = explode(',',M('comment_img')->where(array('cid'=>$data['id']))->getfield('img'));
        M(NULL,'fph_',C('DB_member'));
        $user_info = M('member')->field('mobile')->where(array('id'=>$data['uid']))->find();
        $user_extend = M('member_extend')->field('username,origin')->where(array('uid'=>$data['uid']))->find();
        $data['username'] = $user_extend['username'];
        $data['mobile']      = $user_info['mobile'];
        $data['origin']        = $user_extend['origin'];
        return $data;
    }

    //修改楼盘评论
    public function property_bbs_undate($img,$info,$status,$pid,$origin){
        M(NULL,NULL,C('DB_bbs'));
        if($origin==9){
            $data['info']     = $info;
        }
        $data['status'] = $status;
        if(false !== M('comment')->where(array('id'=>$pid))->save($data)){
            $data = M('comment_img')->where(array('cid'=>$pid))->save(array('img'=>$img));
        }
        return $data;
    }

    //回复信息列表
    public function reply_list($pid){
        M(NULL,NULL,C('DB_bbs'));
        //评论信息
        $comment_info = M('comment')->where(array('id'=>$pid))->getfield('info');

        //回复数据
        $where ='cid = '.$pid;
        $count = M('comment_reply')->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();
        $list = M('comment_reply')->where($where)->order('id DESC')->limit($p->firstRow.','.$p->listRows)->select();
        foreach($list as $key=>$val){
            M(NULL,'fph_',C('DB_member'));
            $user_info = M('member')->field('mobile')->where(array('id'=>$val['uid']))->find();
            $user_extend = M('member_extend')->field('username')->where(array('uid'=>$val['uid']))->find();
            $list[$key]['username'] = $user_extend['username'];
            $list[$key]['mobile']      = $user_info['mobile'];
        }
        return array($comment_info,$list,$page);
    }

    //编辑评论回复*/读取数据
    public function reply_list_info($pid){
        M(NULL,NULL,C('DB_bbs'));
        $data = M('comment_reply')->where(array('id'=>$pid))->find();

        M(NULL,'fph_',C('DB_member'));
        $user_info = M('member')->field('mobile')->where(array('id'=>$data['uid']))->find();
        $user_extend = M('member_extend')->field('username')->where(array('uid'=>$data['uid']))->find();
        $data['username'] = $user_extend['username'];
        $data['mobile']      = $user_info['mobile'];
        return $data;
    }

    //修改编辑评论回复
    public function property_reply_undate($updateWhere,$updateData){
        M(NULL,NULL,C('DB_bbs'));
        $data= M('comment_reply')->where($updateWhere)->save($updateData);
        return $data;
    }

    //读取C端甲鱼帐号
    public function user_list(){
        M(NULL,'fph_',C('DB_member'));
        $idsArr= M('member_extend')->field('uid')->where("origin=9 AND username!=''")->select();
        foreach($idsArr as $val){
            $ids .= $val['uid'].',';
        }

       $ids = substr($ids,0,-1);
        if($ids){
            $idsArr = M('member')->field('id')->where("status=1 AND id in(".$ids.")")->select();
        }

        foreach($idsArr as $val){
            $idss .= $val['id'].',';
        }

        $idss = substr($idss,0,-1);
        if($idss){
            $list = M('member_extend')->field('uid,username,origin')->where("uid in(".$idss.")")->select();
        }

        return $list;
    }

    //添加评论
    public function property_bbs_add($uid,$add_time,$info,$pid,$img){
        M(NULL,NULL,C('DB_bbs'));
        $data['uid']           = $uid;
        $data['add_time'] = $add_time;
        $data['info']          = $info;
        $data['pid']           = $pid;
        //$result_data = M('comment')->add($data);
        if($cid = M('comment')->add($data)){
            $data = M('comment_img')->add(array('cid'=>$cid,'img'=>$img));
        }
        return $data;
    }

    //获取楼盘负责人
    public function case_field_list($id){
        $ids = D('case_field')->case_field_list($id);
        if($ids){
            $data = D('admin')->admin_username($ids);
        }else{
            $data = array();
        }
        return $data;
    }

    //读取楼盘标题
    public function propertyTitle($id){
       return M('property')->where(array('id'=>$id))->getfield('title');
    }

    //判断是否有路费
    public function expenses($time){
        M('expenses','fph_',C('DB_property'));
        $time_end = $time-86400;
        $idsArr= M('expenses')->field('pid')->where('time_start < '.$time.' AND time_end > '.$time_end.' AND status = 1')->select();
        foreach($idsArr as $val){
            $ids .= $val['pid'].',';
        }
        return $ids = substr($ids,0,-1);
    }

    //判断是否有团购
    public function groupBy($time){
        M('group_buy','fph_',C('DB_activity'));
        $idsArr= M('group_buy')->field('pid')->where('time_start < '.$time.' AND time_end > '.$time.'')->select();
        foreach($idsArr as $val){
            $ids .= $val['pid'].',';
        }
        return $ids = substr($ids,0,-1);
    }

    //查找有路费到楼盘
    public function expenses_index($time, $title){
        if($title){
            M('property','fph_',C('DB_fangpinhui'));
            $property_list = M('property')->field('id')->where("title like '%".$title."%'")->select();
            $ids = '';
            foreach($property_list as $val){
                $ids .= $val['id'].',';
            }
            $ids = substr($ids,0,strlen($ids)-1);
        }

        M('expenses','fph_',C('DB_property'));
        $str = 'id,pid';
        $where = 'time_start < '.$time.' AND time_end > '.($time-86400);
        if($ids){
            $where .= ' AND pid in ('.$ids.')';
        }elseif(!$ids && $title){
            $where .= ' AND pid in (0)';
        }
        $listArr = M('expenses')->where($where)->field($str)->group('pid')->select();

        import("ORG.Util.Page");
        $p = new Page(count($listArr), 20);
        $page = $p->show();

        $list = M('expenses')->where($where)->field($str)->group('pid')->limit($p->firstRow.','.$p->listRows)->select();
        $redis = new CacheRedis(0);
        foreach($list as $key=>$val){
            M('property','fph_',C('DB_fangpinhui'));
            $list[$key]['title'] = $this->propertyTitle($val['pid']);
            $code = $redis->get('laravel:propertyExpensesCode.'.$val['pid']);
            $list[$key]['code'] = $code;
        }
        return array($list,$page);
    }

    //添加人员分配
    public function propertyStaffAdd($data){
        M('staff','fph_',C('DB_property'));
        $result= M('staff')->add($data);
        return $result;
    }

    //修改人员陪在
    public function propertyStaffEdit($id, $data){
        M('staff','fph_',C('DB_property'));
        $result= M('staff')->where("id = ".$id."")->save($data);
        return $result;
    }

    //添加人员分配
    public function propertyStaffIndex($pid, $query){
        M('staff','fph_',C('DB_property'));
        $list = M('staff')->field('id,uid,status')->where("pid = ".$pid." AND ".$query)->order('id DESC')->select();
        foreach($list as $key => $val){
            M('admin','fph_',C('DB_fangpinhui'));
            $list[$key]['username'] = M('admin')->where('id = '.$val['uid'].'')->getfield('username');
            $list[$key]['mobile']   = M('admin')->where('id = '.$val['uid'].'')->getfield('mobile');
            $list[$key]['adminid']  = M('admin')->where('id = '.$val['uid'].'')->getfield('id');
        }

        return $list;
    }

    //人员分配详细信息
    public function propertyStaffInfo($id){
        M('staff','fph_',C('DB_property'));
        $data = M('staff')->field('uid,garrison,bargain,principal,status')->where("id = ".$id."")->find();
        return $data;
    }

    //删除人员配置
    public function propertyStaffDelete($id, $type){
        M('staff','fph_',C('DB_property'));
        if($type==1){
            $data = M('staff')->where("id = ".$id."")->save(array('garrison'=>0));
        }elseif($type==2){
            $data = M('staff')->where("id = ".$id."")->save(array('bargain'=>0));
        }elseif($type==3){
            $data = M('staff')->where("id = ".$id."")->save(array('principal'=>0));
        }
        return $data;
    }

    //ajax
    public function ajaxStaffInfo($uid, $pid){
        M('staff','fph_',C('DB_property'));
        $data = M('staff')->field('id,uid,garrison,bargain,principal,status')->where("uid = ".$uid." AND pid = ".$pid."")->find();
        return $data;
    }

    public function findField($where, $field)
    {
        M('property', 'fph_', C('DB_fangpinhui'));
        $id = M('property')->where($where)->getField($field);
        return $id;
    }

    //统计路费被领取份数
    public function expensesReceive($pid){
        M('receive','fph_',C('DB_member'));
        return M('receive')->where('pid = '.$pid)->count('id');
    }

    public function findPropertyInfo($where, $field)
    {
        $data = M('property')->field($field)->where($where)->find();
        return $data;
    }


    public function getList($where, $field)
    {
        $data = M('property')->field($field)->where($where)->select();
        return $data;
    }

    public function propertyList($where, $field, $order, $limit){
        return M('property')->field($field)->where($where)->order($order)->limit($limit)->select();
    }

    public function findPropertyField($where, $field)
    {
        $data = M('property')->where($where)->getField($field);
        return $data;
    }


}