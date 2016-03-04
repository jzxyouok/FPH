<?php
class receiveAction extends backendAction
{

    public function index() {
        $mobile     = $this->_get('mobile','trim');
        $username   = $this->_get('username','trim');
        $title      = $this->_get('title','trim');
        $pid        = $this->_get('pid','trim');
        $city_id    = $this->_get('city_id','intval');
        $time_start = $this->_get('time_start','trim');
        $time_end   = $this->_get('time_end','trim');
        $status     = $this->_get('status','trim');

        /*$uid = $_COOKIE['admin']['id'];
        $case_field = M('case_field')->where(array('admin_id'=>$uid))->getfield('property');
        if($case_field){
           $property_list = M('property')->field('id,title')->where("id in(".$case_field.")")->select();
        }
        $this->assign('property_list',$property_list);*/

        if(!empty($city_id)){
            $select_city_spid = M('city')->where('id ='.$city_id)->getField('spid');
            if($select_city_spid != 0){
                $select_city_spid = $select_city_spid.$city_id;
            }else{
                $select_city_spid = $city_id;
            }
        }

        $list = D('myclient')->receive_index($mobile, $username, $pid, $city_id, $time_start, $time_end, $status, 20);
        
        //取签到领路费信息
        $receive_signs					= array();
        $receive_ids					= array();
        foreach( $list[1] AS $item )
        {
        	$receive_id					= $item['id'];
        	if($item['type'] == '3')	$receive_ids[$receive_id]	= $receive_id;
        }
        $tmp_receive_signs			= M('receive_sign')->field('receive_id,photo,photo_time,latitude')->where("receive_id IN('".implode("','",$receive_ids)."')")->select();
        foreach( $tmp_receive_signs AS $tmp_receive_sign )
        {
        	$receive_id					= $tmp_receive_sign['receive_id'];
        	$receive_signs[$receive_id]	= $tmp_receive_sign;
        }
        
        //获取广告机编码
        $expensess				= array();
        $rule_ids				= array();
        foreach( $list[1] AS $item )
        {
        	$rule_id			= $item['rule_id'];
        	$rule_ids[$rule_id]	= $rule_id;
        }
        $tmp_expensess	= M('expenses','fph_',C('DB_property'))->field('id,machine_code')->where("id IN('".implode("','", $rule_ids)."')")->select();
        foreach( $tmp_expensess AS $tmp_expenses )
        {
        	$rule_id				= $tmp_expenses['id'];
        	$expensess[$rule_id]	= $tmp_expenses;
        }
        
        foreach( $list[1] AS $key => $item )
        {
        	$receive_id						= $item['id'];
        	$rule_id						= $item['rule_id'];

        	$list[1][$key]['photo']			= isset( $receive_signs[$receive_id]['photo'] ) ? $receive_signs[$receive_id]['photo'] : '';
        	$list[1][$key]['machine_code']	= isset( $expensess[$rule_id]['machine_code'] ) ? $expensess[$rule_id]['machine_code'] : '-';
        }
        
        $this->assign('list', $list[1]);
        $this->assign('page',$list[0]);

        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);

        $this->assign('search', array(
            'mobile' => $mobile,
            'username' => $username,
            'title' => $title,
            'pid' => $pid,
            'city_id' => $city_id,
            'time_start' => $time_start,
            'time_end' => $time_end,
            'city_spid' => $select_city_spid,
            'status' =>$status,
        ));
        $this->display();
    }

    //客户资料
    public function edit(){
        $id = $this->_get('id','intval');

        if(IS_POST){
            $id                   = $this->_post('id','intval');
            $result_status = $this->_post('result_status','trim');
            $report_time   = $this->_post('report_time','trim');
            $remark         = $this->_post('remark','trim');
            if(!$report_time){
                $report_time = 0;
            }else{
                $report_time = strtotime($report_time);
            }
            if($result_status!=1 && $result_status!=2){
                $this->ajaxReturn(0, '请选择是否有效客户');
            }
            if($result_status==2 && !$report_time){
                $this->ajaxReturn(0, '请选择报备时间');
            }
            if($result_status==2 && !$remark){
                $this->ajaxReturn(0, '请填写备注');
            }
            if($result_status==1){
                $report_time = 0;
                $remark = '';
            }
            if(false !== D('myclient')->receive_edit_update($id,$result_status,$report_time,$remark)){
                $this->ajaxReturn(1, '提交成功');
            }else{
                $this->ajaxReturn(0, '提交失败');
            }

       }

        $info = D('myclient')->receive_edit($id);
        $this->assign('info',$info);

        $this->assign('id',$id);
        $this->display();
    }

    //领取记录
    public function receive_list(){
        $id = $this->_get('id','intval');

        $list = D('myclient')->receive_list($id);
        $this->assign('list', $list[1]);
        $this->assign('page',$list[0]);

        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);

        $this->assign('id',$id);
        $this->display();
    }

    //浏览记录
    public function skim_through(){
        $id = $this->_get('id','intval');

        $list = D('myclient')->skim_through_list($id);
        $this->assign('list', $list[1]);
        $this->assign('page',$list[0]);

        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);

        $this->assign('id',$id);
        $this->display();
    }

    //导出数据
    public function import() {
        $mobile     = $this->_get('mobile','trim');
        $username   = $this->_get('username','trim');
        $pid        = $this->_get('pid','trim');
        $city_id    = $this->_get('city_id','intval');
        $time_start = $this->_get('time_start','trim');
        $time_end   = $this->_get('time_end','trim');
        $status     = $this->_get('status','trim');

        $list = D('myclient')->receive_index($mobile, $username, $pid, $city_id, $time_start, $time_end, $status);
        //print_r($list[1]);exit;

        Vendor("Classes.PHPExcel");
        Vendor("Classes.PHPExcel.php");

        //创建处理对象实例
        $objPhpExcel=new PHPExcel();
        $objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
        //设置表格的宽度  手动
        $objPhpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPhpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPhpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPhpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(35);
        $objPhpExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPhpExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $objPhpExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPhpExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);

        //设置标题
        $rowVal = array(0=>'编号',1=>'客户姓名', 2=>'手机号码', 3=>'楼盘', 4=>'申领时间', 5=>'领取时间', 6=>'领取状态', 7=>'客户有效性');
        foreach ($rowVal as $k=>$r){
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->getFont()->setBold(true);//字体加粗
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
            $objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
        }

        $objPhpExcel->getActiveSheet()->setCellValue('A1', '编号');
        $objPhpExcel->getActiveSheet()->setCellValue('B1', '客户姓名');
        $objPhpExcel->getActiveSheet()->setCellValue('C1', '手机号码');
        $objPhpExcel->getActiveSheet()->setCellValue('D1', '楼盘');
        $objPhpExcel->getActiveSheet()->setCellValue('E1', '申领时间');
        $objPhpExcel->getActiveSheet()->setCellValue('F1', '领取时间');
        $objPhpExcel->getActiveSheet()->setCellValue('G1', '领取状态');
        $objPhpExcel->getActiveSheet()->setCellValue('H1', '客户有效性');

        //设置当前的sheet索引 用于后续内容操作
        $objPhpExcel->setActiveSheetIndex(0);
        $objActSheet=$objPhpExcel->getActiveSheet();
        //设置当前活动的sheet的名称
        $title="到访客户";
        $objActSheet->setTitle($title);
        //设置单元格内容
        $j=1;
        foreach($list[1] as $k => $v){
            $receive_time = $v['receive_time'] ? date('Y-m-d H:i:s', $v['receive_time']) : '-';
            $status = $v['status'] ? '已领取' : '未领取';
            if($v['result_status'] == 1){
                $result_status = '有效客户';
            }elseif($v['result_status'] == 0){
                $result_status = '-';
            }elseif($v['result_status'] == 2){
                $result_status = '无效客户';
            }

            $num=$k+2;
            $objPhpExcel->setActiveSheetIndex(0)
                //Excel的第A列，uid是你查出数组的键值，下面以此类推
                ->setCellValue('A'.$num, $j)
                ->setCellValue('B'.$num, $v['username'])
                ->setCellValue('C'.$num, $v['mobile'])
                ->setCellValue('D'.$num, $v['title'])
                ->setCellValue('E'.$num, date('Y-m-d H:i:s', $v['add_time']))
                ->setCellValue('F'.$num, $receive_time)
                ->setCellValue('G'.$num, $status)
                ->setCellValue('H'.$num, $result_status);
            $j++;
        }
        //$objPhpExcel->setActiveSheetIndex(0)->setCellValue('G2', '2321321321');//单行信息
        $title="到访客户";
        $name=date('Y-m-d');//设置文件名
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Transfer-Encoding:utf-8");
        header("Pragma: no-cache");
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$title.'_'.urlencode($name).'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPhpExcel, 'Excel5');
        $objWriter->save('php://output');

    }


}