<?php
class app_fkb_customerAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('app_fkb_customer');
        $broker = F('broker');
        $citylist = F('citylist');
        if(empty($broker) || empty($citylist)){
           $this->getbrokercache();   //加载缓存数据
        }

    }

    public function index() {
        $broker = F('broker');
        //$customer   = $this->_get('customer','trim');
        $mobile     = $this->_get('tel','trim');
        $broker_tel = $this->_get('broker_tel','trim');
        $sort = $this->_get('sort','trim');
        $order = $this->_get('order','trim');
        $level = $this->_get('level','trim');
        $select_city_id = $this->_get('city_id','intval');
        if(!empty($sort) && !empty($order)){
            $orderby = $sort.' '.$order;
        }else{
            $orderby = 'id desc';
        }
        $where = 'isdel = 0';
/*        if($customer!=''){
            $where .= " AND customer like '%".$customer."%'";
        }*/
        if($level!=0){
            $where .=' and level ='.$level;
        }
        if($mobile!=''){
            $where .= " AND tel = '".$mobile."'";
        }
        
        if($broker_tel!=''){
            $brokerid = M('app_visitor_staff')->where(array('tel'=>$broker_tel))->getfield('id');
            if($brokerid){
                $where .= " AND brokerid = ".$brokerid;
            }
        }
        if($select_city_id != 0){
            $where .=' and city in(select id from fph_city where id = '.$select_city_id.' or spid RLIKE "[[:<:]]'.$select_city_id.'[[:>:]]")';
            //定位选择的城市
            $spid_city = M('city')->where(array('id'=>$select_city_id))->getField('spid');
            if($spid_city==0){
                $spid_city = $select_city_id;
            }else{
                $spid_city .= $select_city_id;
            }
            $this->assign('selected_ids_city',$spid_city);
        }
        $count = $this->_mod->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();
        $list = $this->_mod->field('fph_app_fkb_customer.id,customer,tel,a.level,addtime,brokerid,fph_app_fkb_customer.city,client,isbak,updatetime')->join('fph_app_fkb_wanttobuy as a on a.customerid = fph_app_fkb_customer.id')->where($where)->order($orderby)->limit($p->firstRow.','.$p->listRows)->select();
        // echo $this->_mod->getlastsql();
         //客户等级
         foreach($list as $key=>$val){
                if($val['level'] ==0){
                        $list[$key]['level'] = '';
                }
                if($val['level'] ==1){
                        $list[$key]['level'] = '<strong style="color:#ff0000">A</strong> 级客户';
                }
                 if($val['level'] ==2){
                        $list[$key]['level'] = '<strong style="color:#ff0000">B</strong> 级客户';
                }
                 if($val['level'] ==3){
                        $list[$key]['level'] = '<strong style="color:#ff0000">C</strong> 级客户';
                }
                 if($val['level'] ==4){
                        $list[$key]['level'] = '<strong style="color:#ff0000">D</strong> 级客户';
                }

         }  
         //获取经济人信息
         foreach ($list as $key => $value) {
                 foreach($broker as $k=>$v){
                    if($value['brokerid'] == $v['id']){
                        $list[$key]['brokername'] = $v['username'].'  '.$v['tel'];
                    }
                 }
                 $list[$key]['city'] = D('city')->getcityfromcache($value['city']);
             } 
         //今日新增客户人数
        $timestart = strtotime(date('Y-m-d'));
        $timeend = $timestart+24*60*60;  
        $todaywhere = 'isdel = 0 and (addtime between '.$timestart.' and '.$timeend.')';      
        $todaycount = $this->_mod->where($todaywhere)->count('id');
        //报备总数
        $bakcount = $this->returnbaknums();
        $this->assign('list', $list);
        $this->assign('page',$page);
        $this->assign('count',$count);
        $this->assign('todaycount',$todaycount);
        $this->assign('customer',$customer);
        $this->assign('bakcount',$bakcount);
        $this->assign('tel',$mobile);
        $this->assign('level',$level);
        $this->assign('isbak',$isbak);
        $this->assign('broker_tel',$broker_tel);
        $this->display();
    }

    public function _before_add() {
      
    }

    public function _before_insert($data='') {		
        $data['addtime'] = time();
        $data['tel'] = $this->_post('mobile','trim');
        return $data;
    }

    public function _before_edit() {
        $id = $this->_get('id','intval'); 
        $this->assign('id',$id);    
        $admin_detail = $this->_mod->where(array('id'=>$id))->find();
        
    }

    public function _after_edit($info){
         $info['broker'] = D('app_fkb_customer')->getbroker($info['brokerid']);
         $spid_city = M('city')->where(array('id'=>$info['city']))->getField('spid');
        if($spid_city==0){
            $spid_city = $info['city'];
        }else{
            $spid_city .= $info['city'];
        }
        $this->assign('selected_ids_city',$spid_city);
        return $info;
    }

    public function _before_update($data=''){
        $data['city'] = $this->_post('city','trim');
        $data['updatetime'] = time();
        return $data;
    }
   

    //数据假删除 回收站铺垫
    public function ajax_delete(){
        $id =  trim($this->_request('id'), ',');
        if (false !== M('app_fkb_customer')->where(array('id'=>array('in',$id)))->save(array('isdel'=>1))) {
           // exit;
            $this->ajaxReturn(1, '删除成功');
        }else{
            $this->ajaxReturn(0, '操作失败');
        }
    }

    //F缓存经纪人信息
    public function getbrokercache(){
        $brokerlist = M('admin')->field('id,username,mobile as tel')->select();
        $citylist = M('city')->field('id,name,pid')->select();
        F('broker',$brokerlist);
        F('citylist',$citylist);
    }

    //根据id获取经纪人信息
    public function getbrokerinfo(){
        $name = trim($this->_post('name'));
        $list = F('broker');
        foreach ($list as $key => $value) {
            if(!strstr($value['username'],$name)){
                    unset($list[$key]);
            }
        }
        if(!empty($list)){
            $str ='';
            foreach ($list as $key => $value) {
                 $str .= '<li data-id="'.$value['id'].'">'.$value['username'].'&nbsp;&nbsp;&nbsp;&nbsp;'.$value['tel'].' </li>';
            }           
        }else{
            $str ='<li data-error="1">没有符合经济人！</li>';
        }
            $this->ajaxReturn(1,'',$str);    
        
    }

    public function ajax_city() {
        $id = $this->_get('id', 'intval');
        $return = M('city')->field('id,name')->where(array('pid'=>$id))->select();
        if ($return) {
            $this->ajaxReturn(1, L('operation_success'), $return);
        } else {
            $this->ajaxReturn(0, L('operation_failure'));
        }
    }

    //购买意向修改
    public function wanttobuy(){
        $customerid = $this->_get('id','intval'); 
        $this->assign('id',$customerid); 
        if(IS_POST){
               $data  = array(
                        'level' => $this->_post('level','intval'),
                        'buyingmotive' => implode(",", $this->_post('buyingmotive','trim')),
                        'customerid' => $this->_post('id','intval'),
                );              
                //查询是否存在信息
               $ishave = M('app_fkb_wanttobuy')->where('customerid='.$data['customerid'])->getfield('id');
               if(!$ishave){
                    $res = M('app_fkb_wanttobuy')->data($data)->add(); 
               }else{
                    $res = M('app_fkb_wanttobuy')->where(array('customerid'=>$data['customerid']))->save($data);
               }
               $url = u('app_fkb_customer/wanttobuy',array('id'=>$data['customerid']));
               $this->success(L('operation_success'));
                
        }
        $info = M('app_fkb_wanttobuy')->where('customerid='.$customerid)->field('id,level,buyingmotive')->find();        
        $buyingmotivelist = array(
                        '0' =>array('id'=>1,'name'=>'自住'),
                        '1' =>array('id'=>2,'name'=>'投资'),
                        '2' =>array('id'=>3,'name'=>'刚需'),
                        '3' =>array('id'=>4,'name'=>'其他'),
            );
        $this->assign('buyingmotivelist',$buyingmotivelist);
        $this->assign('info',$info);
        $cate = array('property_type'=>1,'property_feature'=>12,'building_type'=>15,'decoration'=>23);
        $catelist = D('property')->property_cate($cate);
        $this->assign('catelist',$catelist);
        $this->display();
    }

    //根据所选城市查询户型
    public function gethousetypebycity(){
            $select_city_id = $this->_post('city_id','intval');
            $housetype = $this->_post('housetype','trim');
            if(!$select_city_id){
                $this->ajaxReturn('0','没有数据');
            }
            $list = M('property_housetype')->where('pid in (select id from fph_property where city_id in(select id from fph_city where id = '.$select_city_id.' or spid RLIKE "[[:<:]]'.$select_city_id.'[[:>:]]"))')->field('house_room,house_hall,house_wc')->select();
           foreach ($list as $key => $value) {
                   $list[$key] = $value['house_room'].','.$value['house_hall'].','.$value['house_wc'];
               }
            $list = array_flip(array_flip($list));     //去除数组中重复的元素
            asort($list);
            $str = '';
            foreach($list as $k=>$v){
                $list[$k] = explode(',', $v);    //室 厅 卫 
                $tmp = $list[$k][0].'-'.$list[$k][1].'-'.$list[$k][2];
                $tmp1 = str_replace('-', '', $tmp);
                $housetype = str_replace('-', '', $housetype);
                if($tmp1 == $housetype){                               
                                $checked = 'checked = "checked"';
                               // $select = $housetype;
                   }
                $str .= '<label><input type="checkbox" name="housetype[]" id="housetype_all" value="'.$tmp.'" '.$checked.' size="30">'.$list[$k][0].'室 '.$list[$k][1].'厅 '.$list[$k][2].'卫</label>';
              /*  $str .='<option value="'.$tmp.'" '.$select.'>'.$list[$k][0].'室 '.$list[$k][1].'厅 '.$list[$k][2].'卫</option>';  */
                $checked ='';
            }
           if(!empty($str)){
                $this->ajaxReturn('1','操作成功！',$str); 
           }else{
                $this->ajaxReturn('0','没有数据');
           }
           
    }

    



    public function getpricesum(){
                $customerid = $this->_post('customerid','intval');
                $sumprice = M('app_fkb_wanttobuy')->where('customerid ='.$customerid)->getfield('sumprice');
                $list = D('app_fkb_customer')->price_sum_bycity();                
                foreach($list as $k=>$v){ 
                         if($v['price'] == $sumprice){
                                $select = 'selected = selected';
                         }                  
                         $str .='<option value="'.$v.'" '.$select.'>'.$v.'</option>';
                         $select = '';     
            }
                $this->ajaxReturn('1','操作成功！',$str); 

    }

    public function average_price(){
                $customerid = $this->_post('customerid','intval');
                $average_price = M('app_fkb_wanttobuy')->where('customerid ='.$customerid)->getfield('average_price');
                $list = D('app_fkb_customer')->average_price_bycity();
                foreach($list as $k=>$v){                
                        if($v['price'] == $average_price){
                                $select = 'selected = selected';
                         }                  
                         $str .='<option value="'.$v.'" '.$select.'>'.$v.'</option>';
                         $select = '';
            }
                $this->ajaxReturn('1','操作成功！',$str); 

    }

    // 陪同者
    public function partner(){
        $customerid = $this->_get('id','intval');
        $this->assign('id',$customerid);
        $list = M('app_fkb_partner')->join('fph_app_fkb_customer as b on fph_app_fkb_partner.customerid =b.id ')->where('fph_app_fkb_partner.isdel = 0 and fph_app_fkb_partner.customerid ='.$customerid)->field('fph_app_fkb_partner.id,fph_app_fkb_partner.name,fph_app_fkb_partner.sexy,fph_app_fkb_partner.relationship,fph_app_fkb_partner.tel,b.customer,b.tel as mobile')->order('fph_app_fkb_partner.id desc')->select();
        $this->assign('list',$list);
       //print_r($list);
        $this->display();
    }

    //编辑陪同者
    public function partner_edit(){
            $id = $this->_get('id', 'intval');
            $info = M('app_fkb_partner')->find($id);
            $this->assign('id',$id);
            if(IS_POST){
                   $data = array(
                            'id' => $this->_post('id','intval'),
                            'sexy' => $this->_post('sexy','intval'),
                            'tel' => $this->_post('tel','trim'),
                            'name' => $this->_post('name','trim'),
                            'relationship' => $this->_post('relationship','trim'),
                    );
                  // print_r($data);exit;
                   if(empty($data['id'])){
                        $this->ajaxReturn(0, '参数错误！');
                        exit;
                   }
                    $updateres = M('app_fkb_partner')->where(array('id'=>$data['id']))->data($data)->save(); 
                    IS_AJAX && $this->ajaxReturn(1, L('operation_success'), '', 'partner_edit');
                    $this->success(L('operation_success'));

            }else{
                if (method_exists($this, '_after_edit')) {
                 $info = $this->_after_edit($info);
                }
                $this->assign('info', $info);
                $this->assign('open_validator', true);
                if (IS_AJAX) {
                    $response = $this->fetch();
                    $this->ajaxReturn(1, '', $response);
                } else {
                    $this->display();
                }
            }
            
    }


    //添加陪同者
    public function partner_add(){
            $customerid = $this->_get('customerid','intval');
            $this->assign('customerid',$customerid);
             if(IS_POST){
                   $data = array(
                            'customerid' => $this->_post('customerid','intval'),
                            'sexy' => $this->_post('sexy','intval'),
                            'tel' => $this->_post('tel','trim'),
                            'name' => $this->_post('name','trim'),
                            'relationship' => $this->_post('relationship','trim'),
                    );
                  // print_r($data);exit;
                   if(empty($data['customerid'])){
                        $this->ajaxReturn(0, '参数错误！');
                        exit;
                   }
                    $updateres = M('app_fkb_partner')->data($data)->add(); 
                    IS_AJAX && $this->ajaxReturn(1, L('operation_success'), '', 'partner_add');
                    $this->success(L('operation_success'));

            }else{
                $this->assign('open_validator', true);
                if (IS_AJAX) {
                    $response = $this->fetch();
                    $this->ajaxReturn(1, '', $response);
                } else {
                    $this->display();
                }
            }
            
    }

     //删除陪同者信息
     public function ajax_partner_delete(){
        $id = $this->_get('id','intval');
        $res = M('app_fkb_partner')->where('id ='.$id)->data(array('isdel'=>1))->save();
        IS_AJAX && $this->ajaxReturn(1, L('operation_success'), '', 'partner_add');
        $this->success(L('operation_success'));
     } 

    //顾问推荐
    public function brokersuggest(){
            $mod = M('app_fkb_brokersuggest');
            $customerid = $this->_get('id','intval');
            $this->assign('id',$customerid);
            $isbak = $this->_get('isbak','intval');
              
            $where = 'fph_app_fkb_brokersuggest.customerid = '.$customerid;
            if($isbak ==1){
                $where .=' and fph_app_fkb_brokersuggest.brokerid !=""';
            }else{
                $where .=' and fph_app_fkb_brokersuggest.brokerid is null or fph_app_fkb_brokersuggest.brokerid =""';
            }
            $count = $mod->where($where)->count('id');
            import("ORG.Util.Page");
            $p = new Page($count, 20);
            $page = $p->show();
            $list = $mod->field('user.mobile,user.username,c.customer,c.tel,fph_app_fkb_brokersuggest.id,fph_app_fkb_brokersuggest.customerid,fph_app_fkb_brokersuggest.property_id,b.title,fph_app_fkb_brokersuggest.brokerid,fph_app_fkb_brokersuggest.addtime')->join('fph_property as b on b.id = fph_app_fkb_brokersuggest.property_id')->join('fph_app_fkb_customer as c on c.id =fph_app_fkb_brokersuggest.customerid')->join('fph_user as user on user.id = fph_app_fkb_brokersuggest.brokerid')->where($where)->order($orderby)->limit($p->firstRow.','.$p->listRows)->select();
          //echo $mod->getlastsql();
            foreach ($list as $key => $value) {
                if(!empty($value['brokerid'])){
                    $list[$key]['isbak'] = 1;
                }else{
                    $list[$key]['isbak'] = 0;
                }
            }
            //print_r($list);
            $this->assign('isbak',$isbak);         
            $this->assign('page',$page);
            $this->assign('list',$list);
            $this->display();
    }   

    //添加推荐楼盘
    public function brokersuggest_add(){
            $customerid = $this->_get('customerid','intval');
            $this->assign('customerid',$customerid);
             if(IS_POST){
                   $data = array(
                            'customerid' => $this->_post('customerid','intval'),
                            'property_id' => $this->_post('property_id','intval'),
                            'type'  => 2,
                    );
                  // print_r($data);exit;
                   if(empty($data['customerid']) || empty($data['property_id'])){
                        $this->ajaxReturn(0, '参数错误！');
                        exit;
                   }
                   //判断楼盘是否推荐过
                   $ishave = M('app_fkb_brokersuggest')->where($data)->getfield('id');
                   if(!$ishave){
                        $res = M('app_fkb_brokersuggest')->data($data)->add(); 
                        IS_AJAX && $this->ajaxReturn(1, L('operation_success'), '', 'brokersuggest_add');
                   }else{
                        IS_AJAX && $this->ajaxReturn(0, '该楼盘已经推荐给此客户！', '', 'brokersuggest_add');
                   }
                    $this->success(L('operation_success'));

            }else{
                $this->assign('open_validator', true);
                if (IS_AJAX) {
                    $response = $this->fetch();
                    $this->ajaxReturn(1, '', $response);
                } else {
                    $this->display();
                }
            }
            
    }


    //模糊搜索楼盘
    public function search_property(){
        $property = $this->_post('property','trim');
        if(empty($property)){
             $this->ajaxReturn(0,'请输入要推荐的楼盘！');
        }
        $list = M('property')->where('title like "%'.$property.'%"')->field('id,title')->select();
       // $sql = M('property')->getlastsql();
        if(!empty($list)){
            $str ='';
            foreach ($list as $key => $value) {
                 $str .= '<li data-id="'.$value['id'].'" data-property="'.$value['title'].'">'.$value['title'].' </li>';
            }           
        }else{
            $str ='<li data-error="1">没有符合楼盘！</li>';
        }
            $this->ajaxReturn(1,'',$str);    
        
    }

    //删除陪同者信息
     public function ajax_brokersuggest_delete(){
        $id = $this->_get('id','intval');
        $res = M('app_fkb_brokersuggest')->where('id ='.$id)->delete();
        IS_AJAX && $this->ajaxReturn(1, L('operation_success'), '', 'partner_add');
        $this->success(L('operation_success'));
     } 


     //点击报备
    public function ajax_brokersuggest_bak(){
            $id = $this->_get('id','intval');
            $this->assign('id',$id);
             if(IS_POST){
                    $data = array(
                               'customerid' =>$this->_post('customerid','intval'),
                               'property_id' =>$this->_post('property_id','intval'),
                               'brokerid' =>$this->_post('brokerid','intval'),
                               'addtime' =>time(),
                        );
                    foreach ($data as $key => $value) {
                        # code...
                        if(empty($value)){
                            $this->ajaxReturn(0, '参数错误，操作失败');
                            exit;
                        }
                    }
                    $res = M('app_fkb_brokersuggest')->where(array('customerid'=>$data['customerid'],'property_id'=>$data['property_id']))->data(array('addtime'=>$data['addtime'],'brokerid'=>$data['brokerid']))->save();
/*                          $sql = M('app_fkb_brokersuggest')->getlastsql();
                         $this->ajaxReturn(1,'',$sql);exit;   */ 
                    IS_AJAX && $this->ajaxReturn(1, L('operation_success'), '', 'ajax_brokersuggest_bak');
                    $this->success(L('operation_success'));

            }else{
                $info = M('app_fkb_brokersuggest')->join('fph_app_fkb_customer as a on a.id = fph_app_fkb_brokersuggest.customerid')->join('fph_property as b on fph_app_fkb_brokersuggest.property_id = b.id')->where('fph_app_fkb_brokersuggest.id ='.$id)->field('fph_app_fkb_brokersuggest.id,fph_app_fkb_brokersuggest.customerid,fph_app_fkb_brokersuggest.property_id,a.customer,b.title,a.tel')->find();
                $this->assign('info',$info);
                $this->assign('open_validator', true);
                if (IS_AJAX) {
                    $response = $this->fetch();
                    $this->ajaxReturn(1, '', $response);
                } else {
                    $this->display();
                }
            }
            
    }


    //根据关键字获取报备经济人的姓名 电话  id
    public function getbakbrokerinfo(){
        $name = trim($this->_post('name'));  
        $where = ' status =1';
        if(empty($name)){
            $this->ajaxReturn(0,'请输入关键字');exit;    
        }
        $where .=' and  (username like "%'.$name.'%" or mobile ="'.$name.'")';     
        $list = M('user')->field('id,username,mobile')->where($where)->select();
/*      $sql = M('user')->getlastsql();
        $this->ajaxReturn(1,'',$sql);exit;   */ 
        if(!empty($list)){
            $str ='';
            foreach ($list as $key => $value) {
                 $str .= '<li data-id="'.$value['id'].'">'.$value['username'].'&nbsp;&nbsp;&nbsp;&nbsp;'.$value['mobile'].' </li>';
            }           
        }else{
            $str ='<li data-error="1">没有符合经济人！</li>';
        }
            $this->ajaxReturn(1,'',$str);    
        
    }


    //跟进信息
    public function process(){
        $customerid = $this->_get('id','intval');
        $list = M('app_fkb_brokerfollow')->where(array('customerid'=>$customerid))->order('addtime asc')->select();
        $operatorlist = M('admin')->field('id,username,mobile')->select();
        foreach ($list as $key => $value) {
            foreach ($operatorlist as $k => $v) {
                if($value['operator'] == $v['id']){
                    $list[$key]['operatorname'] = $v['username'] .' '.$v['mobile'];
                }
            }
        }
        //print_r($operatorlist);
        $this->assign('list',$list);
        $this->assign('id',$customerid);
        $this->display();
    }

    //添加跟进弹窗
     public function ajax_addprocess(){
            $customerid = $this->_get('customerid','intval');
            $this->assign('customerid',$customerid);                     
             if(IS_POST){
                    $data = array(
                            'customerid' => $this->_post('customerid','intval'),
                            'content'  =>$this->_post('content','trim'),
                            'addtime' =>time(),
                            'operator' =>$_COOKIE['admin']['id'],

                        );
                    if(empty($data['customerid'])){
                      IS_AJAX && $this->ajaxReturn(0,'主访者信息缺失');    
                     }
                    if(empty($data['content'])){
                      IS_AJAX && $this->ajaxReturn(0,'跟进信息不能为空');    
                     } 
                     $res = M('app_fkb_brokerfollow')->data($data)->add();                     
                    IS_AJAX && $this->ajaxReturn(1, L('operation_success'), '', 'ajax_addprocess');                
                    $this->success(L('operation_success'));

            }else{
                if(empty($customerid)){
                      IS_AJAX && $this->ajaxReturn(0,'主访者信息缺失');    
                  }
                $info = M('app_fkb_customer')->where('id ='.$customerid)->field('id,customer,tel')->find();
                $this->assign('info',$info);    
                $this->assign('open_validator', true);
                if (IS_AJAX) {
                    $response = $this->fetch();
                    $this->ajaxReturn(1, '', $response);
                } else {
                    $this->display();
                }
            }
    }

    //顾客浏览过的楼盘
    public function viewed(){
        $customerid = $this->_get('id','intval');
        $this->assign('id',$customerid);
        $where = 'isdel = 0';
        $id = M('app_fkb_customer')->where('id ='.$customerid)->getfield('id');
        if(!$id){
            $this->error('主访客户不存在');exit;
        }
        $where .=' and customerid ='.$customerid;        
        $count = M('app_fkb_viewed')->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();
        $list = M('app_fkb_viewed')->field('fph_app_fkb_viewed.id,fph_app_fkb_viewed.property_id,fph_app_fkb_viewed.addtime,a.title')->join('fph_property as a on a.id = fph_app_fkb_viewed.property_id')->where($where)->order('fph_app_fkb_viewed.addtime desc')->limit($p->firstRow.','.$p->listRows)->select();
       // echo M('app_fkb_collect')->getlastsql();
        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->display();
    }


    //浏览记录
    public function history(){
        $customerid = $this->_get('id','intval');
        $this->assign('id',$customerid);        
        $id = M('app_fkb_customer')->where('id ='.$customerid)->getfield('id');
        if(!$id){
            $this->error('主访客户不存在');exit;
        }
        $average_price = $this->_get('average_price','trim');
        $sumprice = $this->_get('sumprice','trim');
        $housetype = $this->_get('housetype','trim');
        $type = $this->_get('type','trim');
        $feature = $this->_get('feature','trim');
        $area = $this->_get('area','trim');
        $this->assign('average_price',$average_price);
        $this->assign('sumprice',$sumprice);
        $this->assign('housetype',$housetype);
        $this->assign('type',$type);
        $this->assign('feature',$feature);
        $this->assign('area',$area);
        
        $data_temp = S($analysis_customerid);
       /* if(!empty($data_temp)){
                $this->assign('list',$data_temp);
                print_r($data_temp);
                $this->display();
                exit;
        }*/
        $mod = M(NULL,NULL,C('DB_FKB'));
        if($this->_get('average_price','trim') == 'average_price'){
            if(!empty($data_temp['result_average_price'])){
                   $data['result_average_price'] = $data_temp['result_average_price'];
            }else{
                   $data['result_average_price'] = $this->fieldanalysis($mod,'average_price',$customerid,$limit);           
            }
            
        } 
        if($this->_get('sumprice','trim') == 'sumprice'){
            if(!empty($data_temp['result_sumprice'])){
                   $data['result_sumprice'] = $data_temp['result_sumprice'];
            }else{
                  $data['result_sumprice'] = $this->fieldanalysis($mod,'sumprice',$customerid,$limit); 
            }
           
        } 
        if($this->_get('housetype','trim') == 'housetype'){
            if(!empty($data_temp['result_housetype'])){
                   $data['result_housetype'] = $data_temp['result_housetype'];
            }else{
                 $data['result_housetype'] = $this->fieldanalysis($mod,'housetype',$customerid,$limit);
                foreach ($data['result_housetype'] as $key => $value) {
                        $data['result_housetype'][$key]['housetype'] = explode('-',$data['result_housetype'][$key]['housetype']);
                        $data['result_housetype'][$key]['housetype'] = $data['result_housetype'][$key]['housetype'][0].'室'.$data['result_housetype'][$key]['housetype'][1].'厅'.$data['result_housetype'][$key]['housetype'][2].'卫';
                }
            }
        
         }
        $mod = M('property','fph_',C('DB_FPH'));
        $cate = array('property_type'=>1,'property_feature'=>12,'building_type'=>15,'decoration'=>23);
        $catelist = D('property')->property_cate($cate);
        if($this->_get('type','trim') == 'type'){
            if(!empty($data_temp['result_property_type'])){
                   $data['result_property_type'] = $data_temp['result_property_type'];
            }else{
                $data['result_property_type'] = $this->fieldanalysis($mod,'property_type',$customerid,$limit); 
                foreach ($data['result_property_type'] as $key => $value) {
                        foreach ($catelist['property_type'] as $k => $v) {
                              if($v['id'] ==$value['property_type']){
                                    $data['result_property_type'][$key]['property_type'] = $v['name'];
                              }
                        }
                }
            }            
          
        }
        if($this->_get('feature','trim') == 'feature'){
            if(!empty($data_temp['result_property_feature'])){
                   $data['result_property_feature'] = $data_temp['result_property_feature']; 
            }else{
                $data['result_property_feature'] = $this->fieldanalysis($mod,'property_feature',$customerid,$limit); 
                foreach ($data['result_property_feature'] as $key => $value) {
                        foreach ($catelist['property_feature'] as $k => $v) {
                             if($v['id'] == $value['property_feature']){
                                $data['result_property_feature'][$key]['property_feature'] = $v['name'];
                             }
                        }
                }
            }
         }
        if($this->_get('area','trim') == 'area'){
             if(!empty($data_temp['result_city_id'])){
                   $data['result_city_id'] = $data_temp['result_city_id']; 
            }else{
                 $data['result_city_id'] = $this->fieldanalysis($mod,'city_id',$customerid,$limit); 
                foreach ($data['result_city_id'] as $key => $value) {
                            foreach (F('citylist') as $k => $v) {
                                if($value['city_id'] == $v['id']){
                                    $data['result_city_id'][$key]['city_name'] = $v['name'];
                                }
                            }
                }
            }           
        }
        
        $this->assign('list',$data);       
        S($analysis_customerid,$data,180);  //180秒内缓存有效
        $this->display();
    }


   //返回报备总数 和今日报备数
    public function returnbaknums(){
        //报备总数
        $mod = M('app_fkb_brokersuggest');
        $data = $mod->where('brokerid <> "" and addtime <>""' )->field('id,addtime')->select();
        $time = time();
        $today = strtotime(date('Y-m-d'));
        $tomorow = $today + 24*3600;
        $i = 0; 
        foreach ($data as $key => $value) {
            if($value['addtime'] > $today && $tomorow){
                  $i++;  
            }
        }
        $return = array(
                'allcount' => count($data),
                'todaycount' =>$i,
            );
        return $return;
    }  



    //客户数据分析
    public function analysis(){
        $customerid = $this->_post('customerid','intval');
        $analysis_customerid = 'customer_'.$customerid;
        !$customerid && $this->ajaxReturn(0,'参数错误！');
        $data_temp = S($analysis_customerid);
        if(!empty($data_temp)){
                 $data = $data_temp;
                 $this->ajaxReturn(1,'缓存结果',$data);
                 exit;
        }
        $limit = ' limit 3';
        $mod = M('customer_behavior','fph_',C('DB_FKB'));
        $_mod = D('customer_behavior');        
        $data['result_average_price'] = $this->fieldanalysis($mod,'average_price',$customerid,$limit);   
        $data['result_housetype'] = $this->fieldanalysis($mod,'housetype',$customerid,$limit);
        $data['result_housetype'] = explode(',',$data['result_housetype']);
        foreach ($data['result_housetype'] as $key => $value) {
                $data['result_housetype'][$key] = explode('-',$data['result_housetype'][$key]);
                $data['result_housetype'][$key] = $data['result_housetype'][$key][0].'室'.$data['result_housetype'][$key][1].'厅'.$data['result_housetype'][$key][2].'卫';
        }
        $data['result_housetype'] = implode(',', $data['result_housetype']);
        $data['result_sumprice'] = $this->fieldanalysis($mod,'sumprice',$customerid,$limit);
        $data['result_property_type'] = explode(',',$this->fieldanalysis($mod,'property_type',$customerid,$limit));       
        $data['result_property_feature'] = explode(',',$this->fieldanalysis($mod,'property_feature',$customerid,$limit)); 
        $data['result_city_id'] = $this->fieldanalysis($mod,'city_id',$customerid,$limit); 
        $data['result_city_id'] = explode(',',$data['result_city_id']);
        foreach ($data['result_city_id'] as $key => $value) {
                    foreach (F('citylist') as $k => $v) {
                        if($value == $v['id']){
                            $data['result_city_id'][$key] = $v['name'];
                        }
                    }
        }
        $data['result_city_id'] = implode(',',$data['result_city_id']);

        $data['result_area_id'] = $this->fieldanalysis($mod,'area_id',$customerid,$limit); 
        $data['result_area_id'] = explode(',',$data['result_area_id']);
        foreach ($data['result_area_id'] as $key => $value) {
                    foreach (F('citylist') as $k => $v) {
                        if($value == $v['id']){
                            $data['result_area_id'][$key] = $v['name'];
                        }
                    }
        }
        $data['result_area_id'] = implode(',',$data['result_area_id']);
        $mod = M('property','fph_',C('DB_FPH'));
        $cate = array('property_type'=>1,'property_feature'=>12,'building_type'=>15,'decoration'=>23);

        $catelist = D('property')->property_cate($cate);
        foreach ($data['result_property_type'] as $key => $value) {
                foreach ($catelist['property_type'] as $k => $v) {
                      if($v['id'] ==$value){
                            $data['result_property_type'][$key] = $v['name'];
                      }
                }
        }
        foreach ($data['result_property_feature'] as $key => $value) {
                foreach ($catelist['property_feature'] as $k => $v) {
                      if($v['id'] ==$value){
                            $data['result_property_feature'][$key] = $v['name'];
                      }
                }
        }
        $data['result_property_feature'] = implode(' 、  ', $data['result_property_feature']);
        $data['result_property_type'] = implode(' 、  ', $data['result_property_type']);
        
        S($analysis_customerid,$data,60);  //60秒内缓存有效
       // cookie($cookiename,array('result_average_price'=>$data['result_average_price'],'result_housetype'=>$data['result_housetype'],'result_sumprice'=>$data['result_sumprice'],'result_property_type'=>$data['result_property_type'],'result_property_feature'=>$data['result_property_feature'],'result_city_id'=>$data['result_city_id']));
       // cookie($cookiename,$data,3600);
        //$d = $_COOKIE[$cookiename];
        $this->ajaxReturn(1,'分析结果',$data);
    }

  /**
  客户筛选数据分析
  */
  public function fieldanalysis($mod,$fieldname,$customer_id,$limit){
          $sql_average_price = 'SELECT '.$fieldname.',COUNT(id) AS tp_count,addtime FROM `fph_customer_behavior` WHERE customer_id ='.$customer_id.' GROUP BY '.$fieldname.' order by tp_count desc,addtime desc'.$limit;
          $result_average_price =  $mod->query($sql_average_price);

          if(!empty($limit)){
               foreach ($result_average_price as $key => $value) {
                  unset($result_average_price[$key]['tp_count']);
                  unset($result_average_price[$key]['addtime']);
                  $result_average_price[$key] = $value[$fieldname];
               }
               return $result_average_price = implode(',',$result_average_price);        
          }else{
               return $result_average_price;        
          }    
         
      }
    

}