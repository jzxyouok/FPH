<?php
class channelAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('admin');
    }

    public function index() {
        $time_start = $this->_get('time_start','trim');
        $time_end   = $this->_get('time_end','trim');
        $status     = $this->_get('status','trim');
        $keyword    = $this->_get('keyword','trim');
        $where = '1=1';
        if($time_start){
            $time_start = strtotime($time_start);
            $where .= ' AND last_time >= '.$time_start;
        }
        if($time_end){
            $time_end = strtotime($time_end);
            $where .= ' AND last_time <= '.$time_end;
        }
        if($status!=''){
            $where .= ' AND status = '.$status;
        }
        if($keyword!=''){
            $where .= " AND username like '%".$keyword."%' ";
        }
       
        $count = $this->_mod->where($where)->count('id');
        import("ORG.Util.Page");
        $p    = new Page($count, 20);
        $page = $p->show();
        $str  = 'id,username,mobile,status,add_time,code_id';
        $list = $this->_mod->field($str)->where($where)->limit($p->firstRow.','.$p->listRows)->order('id DESC') ->select();
        foreach($list as $key=>$val){
           $list[$key]['people'] = M('user')->where(array('admin_id'=>$val['id']))->count('id');
        }
        $this->assign('page',$page);
        $this->assign('list',$list);
        $this->assign('count',$count);

        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);

        //共有成员
        $count_people = M('user')->where("admin_id != 0")->count('id');
        $this->assign('count_people',$count_people);

        $this->assign('search', array(
            'time_start' => $time_start,
            'time_end' => $time_end,
            'status'  => $status,
            'keyword' => $keyword,
        ));
        $this->display();
    }

    public function add(){  
    	
    	$this->display();
    }

    

    public function edit(){
    	$id = $this->_get('id','intval');
        $fph = C('DB_PREFIX');

        //共有经纪人
        $count_people = M('user')->where(array('admin_id'=>$id))->count('id');
        $this->assign('count_people',$count_people);

        $where = 'admin_id='.$id;
        $count = M('user')->where($where)->count('id');
        import("ORG.Util.Page");
        $p    = new Page($count, 20);
        $page = $p->show();
        $str  = 'id,username,mobile,origin,reg_time,stat_property,stores_id';
        $list = M('user')->field($str)->where($where)->limit($p->firstRow.','.$p->listRows)->order('id DESC') ->select();

        foreach($list as $k=>$v){
            $list[$k]['daikan']    = M('myclient_property')->where('with_look = 1 AND uid='.$v['id'])->count('id');
            $list[$k]['chengjiao'] = M('myclient_property')->where('status = 7 AND uid='.$v['id'])->count('id');
            $my = M('myclient_status')->field('A.id,C.expenditure')
                                      ->table("{$fph}myclient_status AS A
                                               INNER JOIN {$fph}myclient_property AS B ON A.mpid = B.id
                                               INNER JOIN {$fph}commission as C ON C.pid=A.id")
                                      ->where('A.status = 7 AND B.uid = '.$v['id'])->select();
            $list[$k]['yongjin'] = 0;
            foreach($my as $k1=>$v1){
                $list[$k]['yongjin'] = $list[$k]['yongjin'] + M('expenditure')->where('pid='.$v1['id'])->sum('price');
            }
            //驻守
            if($v['stat_property']){
                $stat_property = explode(',',$v['stat_property']);
                foreach($stat_property as $v2){
                    $list[$k]['property_title'] .= M('property')->where(array('id'=>$v2))->getfield('title').',';
                }               
            }elseif(!$v['stat_property'] && $v['stores_id']){
                $list[$k]['property_title'] .= M('stores')->where(array('id'=>$v['stores_id']))->getfield('name');
            }

        }
        $this->assign('page',$page);
        $this->assign('list',$list);

        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);

        $this->assign('id',$id);

        $this->display();
        
    }

    /*
     * 批量移动*修改用户
     * $ids 当前被操作用户
     */
    public function move() {
        if (IS_POST) {
            $ids        = $this->_post('ids','trim');
            $move_id    = $this->_post('move_id','intval');
            $ids_arr    = explode(',', $ids);
            
            !$ids && $this->ajaxReturn(0, '请选择要移动的经纪人');
            !$move_id && $this->ajaxReturn(0, '请选择所属人');

            foreach ($ids_arr as $val) {
                M('user')->where(array('id'=>$val))->save(array('admin_id'=>$move_id));
            }
            $this->ajaxReturn(1, L('operation_success'), '', 'move');
              
        } else {
            $uid = $this->_get('uid','intval');
            $ids = trim($this->_request('id'), ',');
            $this->assign('ids', $ids);

            $admin_username = M('admin')->where(array('id'=>$uid))->getfield('username');
            $this->assign('admin_username',$admin_username);

            $admin_list = M('admin')->field('id,username')->where(array('status'=>1))->select();
            $this->assign('admin_list',$admin_list);
            
            $resp = $this->fetch();
            $this->ajaxReturn(1, '', $resp);
        }
    }

    
}