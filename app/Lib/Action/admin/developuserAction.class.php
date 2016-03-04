<?php
class developuserAction extends backendAction {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('user');
    }
    //发展会员显示
    public function index() {
        $username = $this->_get("username",'trim');
        $mobile   = $this->_get("mobile",'trim');
        $type       =  $this->_get("type",'trim');
        $this->assign('username', $username);
        $this->assign('mobile', $mobile);
        $where = '1=1';
        if($username){
            $where .= ' AND username like "%'.$username.'%"';
        }
        if($mobile)
        {
            $where .=' AND mobile = '.$mobile;
        }
        if(empty($username) && empty($mobile))
        {
            if($type !=2){
                $where .= ' AND id in(3009,3060,3012,3010,3017,3011,3016,1129,2998,3015,21,38,49,51,54,40,61,22,23,26,52,41,30,100,48,47,46,63,53)';
            }
        }
        //统计会员
        $user_count = $this->_mod->where($where)->count('id');
        $pagesize=22;
        $pager = new Page($user_count, $pagesize);
        //查询第一级用户 根据第一级用户展开下级用户
        $result = $this->_mod->field('id,username,share_id,last_time')->where($where)->limit($pager->firstRow.','.$pager->listRows)->select();
        $page = $pager->show();
        $array = array();
        $str = '';
        foreach($result as $v)
        {
            global $i;
            $i = 0;
            $count = $this->_mod->where('share_id ='.$v['id'])->count('id');
            $count_user = $this->count_user($v['id']);
            $str .= "<tr id='truserid_".$v['id']."'>
                        <td align='center'> ".$v['id']."
                        <input type='hidden' value='0' id='padding".$v['id']."'></td>
                        <td style='padding-left:0px'><span id='collapsed".$v['id']."'";
            if($count == 0)
            {
                $str .= "class='expanded'"; 
            }
            else
            {
                $str .= "class='collapsed' onclick='ajax_user(".$v['id'].")'";
            }
            $str .= "style='padding-left:20px'></span><span>".$v['username']."&nbsp;(".$count.")—".$count_user."</span></td><td>".date('Y-m-d H:i',$v['last_time'])."</td></tr>";
        }
        $this->assign('list', $str);
       
        //获取起始时间
        $start = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $end = mktime(23,59,59,date("m"),date("d"),date("Y"));
        $time_count = $this->_mod->where('reg_time BETWEEN '.$start.' AND '.$end)->count('id');
        $this->assign('type', $type);
        $this->assign("page", $page);
        $this->assign('time_count', $time_count);
        $user_count_zongshu = $this->_mod->count('id');
        $this->assign('user_count', $user_count_zongshu);
        $this->display();
    }
    
    //根据ajax展开会员信息
    public function ajax_user()
    {
        if(IS_AJAX)
        {
            $sort = $this->_request("sort", 'trim', 'reg_time');
            $order = $this->_request("order", 'trim', 'ASC');
            $id = $this->_post('id', 'intval');
            //根据第一级用户展开下级用户
            $result = $this->_mod->field('id,username,share_id,last_time')->where('share_id = '.$id)->order($sort . ' ' . $order)->select();
            $array = array();
            $str = '';
            $padding = $this->_post('padding', 'intval')+40;
            foreach($result as $v)
            {
                $count = $this->_mod->where('share_id ='.$v['id'])->count('id');
                $str .= "<tr id='truserid_".$v['id']."' class='trrmo_".$v['share_id']." no'>
                            <td align='center'>
                            <input type='hidden' value='".$padding."' id='padding".$v['id']."'></td>
                            <td  style='padding-left:".$padding."px'>├─<span id='collapsed".$v['id']."'";
                if($count == 0)
                {
                    $str .= "class='expanded'"; 
                }
                else
                {
                    $str .= "class='collapsed' onclick='ajax_user(".$v['id'].")'";
                }
                $str .= "style='padding-left:20px'></span><span>".$v['username'].'&nbsp;('.$count.")</span></td><td>".date('Y-m-d H:i',$v['last_time'])."</td></tr>";
            }
            $this->ajaxReturn(1, '', $str);
        }
    }
    
    
    //递归循环会员总数
    public function count_user($id)
    {
        global $i;
        $i++;
        $d = $this->_mod->field('id,share_id')->where('share_id='.$id)->select();
        foreach($d as $v)
        {
            $this->count_user($v['id']);
        }
        return $i-1;
    }
}