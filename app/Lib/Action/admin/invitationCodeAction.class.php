<?php
/**
 * 用户信息管理
 */
class invitationCodeAction extends backendAction
{

    public function _initialize() {
        parent::_initialize();
    }

    /**
     * 推广统计
     */
    public function index()
    {
        $username = $this->_get('username', 'trim');
        $mobile = $this->_get('mobile', 'trim');
        if(isset($_GET['username']) && $username)
        {
            if(!strlen($username) > 20) $this->error('字符超过20个字符');
        }
        if(isset($_GET['mobile']) && $mobile && !checkMobile($mobile)){
           $this->error('手机号码输入错误');
        }
        //没有查询条件 查询当日数据
        $time_start  = strtotime(date('Y-m-d'));
        if(isset($_GET['inviteTime']) && $_GET['inviteTime'])
        {
            $time_start = $inviteTime = strtotime($this->_get('inviteTime', 'trim'));
        }
        $time_end = $time_start + 86400; //全天数据

        if($time_start != strtotime(date('Y-m-d')) && isset($inviteTime)) {
            $tableColumn = date('m/d',$inviteTime).'日人数';
        } else {
            $tableColumn = '单日人数';
        }

        //没有指定推广人员
        $inviteWhere = 1;
        if(!$username && !$mobile)
        {
            $inviteWhere .= ' and mobile > 0 and code > 0';
        }

        //指定人员
        if($username)
        {
            // 根据用户姓名查询
            $memberWhere = 'username = "'.$username.'"';
            $mobiles =  $this->getBroker($memberWhere, 'mobile');
            foreach($mobiles as $k => $v)
            {
                if(!$v) unset($mobiles[$k]);
            }
            if($mobiles) $inviteWhere .= ' and mobile in('.implode(',', $mobiles).')';
        }
        if($mobile) $inviteWhere .= ' and mobile = "'.$mobile.'"';
        M('invitation_code','fph_',C('DB_member'));
        $tmp = D('invitation_code')->getInviteMember($inviteWhere, 'mobile,code,type', 'id asc', 20);
        $list = isset($tmp[0]) && $tmp[0] ? $tmp[0] : array();
        $page = isset($tmp[1]) && $tmp[1] ? $tmp[1] : array();
        if($list)
        {
            foreach($list as $key => $val)
            {
                //根据手机号查询姓名
                $query = 'mobile = '.$val['mobile'];
                $member = $this->getMemberInfo($query, $val['type']);
                $list[$key]['username'] = $member['username'];
                $list[$key]['mobile'] = $member['mobile'];
                M('member','fph_',C('DB_member'));
                //所有数据
                $allSql = 'broker_mobile = "'.$member['mobile'].'"';
                $list[$key]['allCount'] = D('member')->countNum($allSql);
                //单日数据
                $sql = 'broker_mobile = "'.$member['mobile'].'" and invite_time >= '.$time_start.' and invite_time < '.$time_end;
                $list[$key]['singleCount'] = D('member')->countNum($sql);
            }
        }
        $this->assign('list', $list);
        $this->assign('tableColumn', $tableColumn);
        $this->assign('page', $page);
        $this->assign('username', $username);
        $this->assign('mobile', $mobile);
        $this->display('invitationCount');
    }

    /**
     * 根据mobile 从user admin tmp_member  查询所需要的
     */
    public function getBroker($where, $fields)
    {
        // user
        M('user','fph_',C('DB_fangpinhui'));
        $info = D('user')->getInfo($where, $fields);
        $userId = isset($info[$fields]) && $info[$fields] ? $info[$fields] : '';
        //if($userId) return $userId;

        // admin
        M('admin','fph_',C('DB_fangpinhui'));
        $info = D('admin')->getInfo($where, $fields);
        $adminId = isset($info[$fields]) && $info[$fields] ? $info[$fields] : '';
       // if($adminId) return $adminId;

        // tmp_member
        M('tmp_member','fph_',C('DB_member'));
        $info = D('tmp_member')->getInfo($where, $fields);
        $TmpId = isset($info[$fields]) && $info[$fields] ? $info[$fields] : '';
       // if($TmpId) return $TmpId;
        return array($userId, $adminId, $TmpId);

    }

    /**
     * 获取推广人员信息
     */
    public function getMemberInfo($where, $type)
    {
        switch ($type){
            case 1 :
                // user
                M('user','fph_',C('DB_fangpinhui'));
                return $info = D('user')->getInfo($where, 'username, mobile');
                break;
            case 2 :
                // admin
                M('admin','fph_',C('DB_fangpinhui'));
                $info = D('admin')->getInfo($where, 'username, mobile');
                return $info;
                break;
            case 3 :
                // tmp_member
                M('tmp_member','fph_',C('DB_member'));
                return $info = D('tmp_member')->getInfo($where, 'username, mobile');
                break;
        }
    }
    /**
     * 添加推广人员
     */
    public function addMember()
    {
        if(IS_POST)
        {
            //添加临时推广人员
            if(isset($_POST['mobile']) && isset($_POST['name']))
            {
                $tmpUsername = $this->_post('name','trim');
                $tmpMobile = $this->_post('mobile','trim');
                if(!$tmpUsername) $this->error('用户名不能为空');
                if(!checkMobile($tmpMobile)) $this->error('手机号码输入错误');
                $flag = $this->isMemberInSystem($tmpMobile);
                if(!$flag) $this->error('该手机号码已经在系统内部被使用，请使用快速添加方式！');
                $this->addTmpMember($tmpUsername, $tmpMobile);
                $data = $this->brokerCodeAdd($tmpMobile, 3);
                if($data)$this->success('数据添加成功');
            }
        }
        $this->display();
    }

    public function addMemberAjax()
    {
        $systemType = $this->_post('type','trim');
        $systemMobile = $this->_post('mobile','trim');
        if(isset($_POST['type']) && trim($_POST['mobile']))
        {
            //快速添加
            if(!$systemMobile) $this->ajaxReturn(0,'手机号不能为空');
            if(!$systemType) $this->ajaxReturn(0,'用户信息有误');

            $data = $this->brokerCodeAdd($systemMobile, $systemType);
            if($data) $this->ajaxReturn(1,'数据添加成功！');
            $this->ajaxReturn(0,'数据添加失败！');
        }
    }

    /**
     * 导出报表
     */
    public function exportExcel()
    {
        if(isset($_GET['time_start']) || isset($_GET['time_end']) || isset($_GET['mobile']))
        {
            $time_start = $this->_get('time_start', 'trim');
            $time_end = $this->_get('time_end', 'trim');
            $mobile = $this->_get('mobile', 'trim');
            if (!$time_start && !$time_end && !$mobile) $this->error('至少选择输入一个筛选条件');
            if (!$time_start && $time_end) $this->error('请选择开始时间');
            $where = 'broker_mobile > 0';
            if ($time_start) {
                $time_start = strtotime($time_start);
                $date_end = $time_start + 86400;
                $title = date('Y-m-d', $time_start);
            }
            if ($time_end) {
                $time_end = strtotime($time_end);
                $date_end = $time_end + 86400;
                $title = date('Y-m-d', $time_start).'至'.date('Y-m-d', $date_end);
            }
            if(isset($date_end))
            {
                $where .= ' and invite_time >=' . $time_start . ' and invite_time <' . $date_end;
            }
            if ($mobile)
            {
                $where .= ' and broker_mobile ="'.$mobile.'"';
                $title = '推广员'.$mobile;
            }
            $title = $title.'推广数据报表';
            $fields = 'id, uid, invite_time, broker_mobile';
            M('member', 'fph_', C('DB_member'));
            $list = D('member')->getListByExtend($where, $fields, $order='invite_time DESC' );
            foreach($list as $k => $val){
                $list[$k]['mobile'] = D('member') ->getField('id ='.$val['uid'],'mobile');
            }
            if(!$list) $this->error('没有符合条件的数据');
            $this->analysisExcel($list, $title);

        }
        $this->display();
    }


    private function analysisExcel($analysis, $title)
    {
        Vendor("Classes.PHPExcel");
        Vendor("Classes.PHPExcel.php");
        //创建处理对象实例
        $objPhpExcel=new PHPExcel();
        $objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
        //设置表格的宽度  手动
        $objPhpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPhpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
        $objPhpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(45);
        //设置标题
        $rowVal = array(0=>'日期',1=>'注册人手机号码', 2=>'推广人手机号码');
        foreach ($rowVal as $k=>$r){
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)
                ->getFont()->setBold(true);//字体加粗
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
            getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
            $objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
        }
        $objPhpExcel->getActiveSheet()->setCellValue('A1', '日期')->getColumnDimension('A')->setWidth(20);
        $objPhpExcel->getActiveSheet()->setCellValue('B1', '注册人手机号码')->getColumnDimension('B')->setWidth(20);
        $objPhpExcel->getActiveSheet()->setCellValue('C1', '推广人手机号码')->getColumnDimension('C')->setWidth(20);
        //设置当前的sheet索引 用于后续内容操作
        $objPhpExcel->setActiveSheetIndex(0);
        $objActSheet=$objPhpExcel->getActiveSheet();
        //设置当前活动的sheet的名称
        //$title=$getYear.'年'. $getMonth .'月份预约到访数据';
        $objActSheet->setTitle($title);
        //insert data start
        foreach($analysis as $k=>$v){
            $num=$k+2;
            $objPhpExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$num, date('Y/m/d', $v['invite_time']))
                ->setCellValue('B'.$num, $v['mobile'])
                ->setCellValue('C'.$num, $v['broker_mobile']);
        }
        // insert data end
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Transfer-Encoding:utf-8");
        header("Pragma: no-cache");
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$title.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPhpExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     * 获取系统的人员
     */
    public function systemMember()
    {
        $option = $this->_post('option','trim');
        if(!$option) $this->ajaxReturn(0, '', '');
        //$where = 'status = 1 and username != "" and (username like "%'.$option.'%" or mobile like "%'.$option.'%")';
        $where = 'status = 1 and (username like "%'.$option.'%" or mobile like "%'.$option.'%")';
        $fields = 'id,username,mobile';
        $userList = D('user')->getList($where, $fields);
        $adminList = D('admin')->getList($where, $fields);
        $data = $this->buildMemberData($userList, $adminList);
        $this->ajaxReturn(1, '', $data);
    }

    private function  buildMemberData($userList, $adminList)
    {
        // type  1:user 2:admin
        $tmp = '';
        if($userList)
        {
            foreach($userList as $key => $val)
            {
                $val['type'] = 1;
                $str = '<li data-type="'.$val['type'].'" data-uid="'.$val['id'].'"><span class="search_content_name">'. $val['username'] .'</span><span class="search_content_phone">'. $val['mobile'] .'</span></li>';
                $tmp .= $str;
            }
        }

        if($adminList)
        {
            foreach($adminList as $key => $val)
            {
                $val['type'] = 2;
                $str = '<li data-type="'.$val['type'].'" data-uid="'.$val['id'].'"><span class="search_content_name">'. $val['username'] .'</span><span class="search_content_phone">'. $val['mobile'] .'</span></li>';
                $tmp .= $str;
            }
        }

        return $tmp;

    }

    /**
     * 经纪人邀请码生成
     */
    public function brokerCodeAdd($mobile, $type, $tmpMobile)
    {
        if(!$tmpMobile){
            $data = $this->buildInvitationCode($mobile, $type);
            return $data;
        } else {
            $data = $this->buildInvitationCode($tmpMobile, $type);
            return $data;
        }

    }
    
    private function buildInvitationCode($mobile, $type)
    {
        //查询经纪人是否有邀请码
        $where = 'mobile = '.$mobile;
        $field = 'id';
        M('invitation_code','fph_',C('DB_member'));
        $isExsist = D('invitation_code')->codeExsist($where, $field);
        $code = rand(100000, 999999);
        while(D('invitation_code')->codeExsist('code = '.$code, $field))
        {
            $code = rand(100000, 999999);
        }
        M('invitation_code','fph_',C('DB_member'));
        if($isExsist)
        {
            $this->ajaxReturn(0, '该推广人员已经存在', '');
            //update
            //$data = array('code' => $code);
            //$result = D('invitation_code')->updateCode($where, $data);
        } else
        {
            //insert
            $addData = array('mobile' => $mobile, 'code' => $code, 'type' =>$type);
            $result = D('invitation_code')->insertCode($addData);
        }
        return $result;
    }


    /**
     * 添加临时人员
     */
    public function addTmpMember($username, $mobile)
    {
        //判断是否已经是临时人员
        M('tmp_member','fph_',C('DB_member'));
        $isTmp = D('tmp_member')->isExsist('status = 1 and mobile = "'.$mobile.'"', 'mobile');
        if($isTmp) $this->error('该手机号码已经存在！');
        //执行添加操作
        $data = array(
            'username' => $username,
            'mobile' => $mobile,
            'type' => 1
        );
        $result = D('tmp_member')->insertMember($data);
        if(!$result) $this->error('新增人员失败！');
        return $result;
    }

    /**
     * 判断临时人员是否已经存储在系统中
     */

    public function isMemberInSystem($mobile)
    {
        $flag = true;
        $where = 'mobile = '.$mobile;
        M('user','fph_',C('DB_fangpinhui'));
        $isInUser = D('user')->getInfo($where, 'username, mobile');
        if($isInUser) $flag = false;
        M('admin','fph_',C('DB_fangpinhui'));
        $isInAdmin = D('admin')->getInfo($where, 'username, mobile');
        if($isInAdmin) $flag = false;
        return $flag;
    }
    
    
    
    
    
    
    
    

}