<?php
/**
 * Created by PhpStorm.
 * User: geyouwen
 * Date: 15-8-25
 * Time: 下午3:13
 */
class join_reservationAction extends backendAction
{
    public function _initialize()
    {
        parent::_initialize();
        $this->config = array('08:00 - 10:00', '10:00 - 12:00', '12:00 - 14:00', '14:00 - 16:00', '16:00 - 18:00', '18:00 - 20:00',);
    }

    public function index()
    {
        $id = $this->_get('id', 'intval');
        $where = 'pid = ' . $id;
        echo $date = $this->_get('time_start','trim');
        $getTime = $this->_get('getTime','trim');
        if($date)
        {
            $this->assign('date', $date);
            $where .= ' and order_time_start <= '.strtotime($date). ' and order_time_end >='.strtotime($date);
        }
        if($date && $getTime)
        {
            $time = explode(' - ',$getTime);
            $where .= ' and order_time_start <= '.strtotime($date.' '.$time[0]). ' and order_time_end >='.strtotime($date.' '.$time[0]);
        }
        $isArrived = $this->_get('status','trim');
        if($isArrived == 1) $where .= ' and arrived_time > 0';
        if($isArrived == 3) $where .= ' and arrived_time = 0';
        M('join_reservation','fph_',C('DB_member'));
        $mobile  = $this->_get('mobile','trim');
        if($mobile) {
            $uid = D('member')->getField('mobile = '.$mobile,  'id');
            if($uid) $where .= ' and uid ='.$uid;
            $this->assign('mobile', $mobile);
        }
        $select_time = $this->config;
        $fields = '*';
        $data = D('join_reservation')->getList($where,$fields);
        if(!$data)
        {
            $data = array('','');
        } else {
            $reservation_ids = implode(',', i_array_column($data[1],'eservation_id'));
            $uids = implode(',', i_array_column($data[1],'uid'));
            $option = 'id in ('. $reservation_ids .')';
            M('reservation','fph_',C('DB_activity'));
            $tmp = D('reservation')->getList($option, 'id,money', false);
            M('member','fph_',C('DB_member'));
            $where = 'id in ('. $uids .')';
            $user = D('member')->getList($where,  'id,mobile');
            $data[1] = $this->buildData($data[1], $tmp, $user);
         }
        $this->assign('list', $data[1]);
        $this->assign('page', $data[0]);
        $this->assign('id', $id);
        $this->assign('status', $isArrived);
        $this->assign('getTime', $getTime);
        $this->assign('select_time', $select_time);
        $this->display();
    }
    private function buildData($data1,$data2, $user)
    {
        foreach ($data1 as $key =>$val) {
            foreach($data2 as $k => $v)
            {
                if($val['eservation_id'] == $v['id']) $data1[$key]['money'] = $v['money'];
            }
            foreach($user as $k1 => $v1)
            {
                if($val['uid'] == $v1['id']) $data1[$key]['mobile'] = $v1['mobile'];
            }
            if(!($val['arrived_time'])) $data1[$key]['arriveStatus'] = '<font color="green">等待客户到访</font>';
            if($val['arrived_time']) $data1[$key]['arriveStatus'] = '<font color="red">已到访</font>';
            if(!$val['arrived_time'] && time() > $val['order_time_end']) $data1[$key]['arriveStatus'] = '<font color="orange">已过期</font>';
        }
        return $data1;
    }

    public function analysis()
    {
        $id = $this->_get('id', 'intval');
        $getYear = $this->_get('year', 'intval');
        $getMonth = $this->_get('month', 'intval');
        $where ='pid = '.$id;
        $months = $getYear .'-'.$getMonth;  // search month  ex: 2015-8
        if(!$getYear && !$getMonth)
        {
            $months = date('Y-m',time());
            $getMonth = (int)date('m',time());
        }
        $end = date('t',strtotime($months)); // 获取指定月份天数
        $monthBefore = strtotime($months . '-01');
        $monthEnd = strtotime($months . '-'. $end) + 86400;
        $where .= ' and order_time_start >='.$monthBefore .' and order_time_end <= '. $monthEnd;
        M('join_reservation','fph_',C('DB_member'));
        $list = D('join_reservation')->getList($where, '*', false);
        $analysis = $this->buildAnalysisData($list, $end, $months);
        $categories = i_array_column($analysis, 'date');
        foreach($categories as $k => $v)
        {
           $categories[$k] = "'$v'";
        }
        $categories = implode(',', $categories);
        $count = implode(',', i_array_column($analysis, 'count'));
        $arrived = implode(',', i_array_column($analysis, 'arrived'));
        $arrivedBefore = implode(',', i_array_column($analysis, 'arrivedBefore'));
        $arrivedLate = implode(',', i_array_column($analysis, 'arrivedLate'));

        //
        $yearStart = '2015';
        $nowYear = date('Y');
        $year = array();
        $j = 0;
        for($i = $yearStart; $i <= $nowYear; $i++)
        {
            $year[$j] = $i;
            $j++;
        }
        $month = array();
        for($i=0; $i < 12; $i++)
        {
            $month[$i] = $i+1;
        }

        $this->assign('categories', $categories);
        $this->assign('count', $count);
        $this->assign('arrived', $arrived);
        $this->assign('arrivedBefore', $arrivedBefore);
        $this->assign('arrivedLate', $arrivedLate);
        $this->assign('id', $id);
        $this->assign('year', $year);
        $this->assign('month', $month);
        $this->assign('getYear', $getYear);
        $this->assign('getMonth', $getMonth);
        $this->assign('memberNum',array_sum(i_array_column($analysis, 'count')));
        $this->display();
    }

    private function buildAnalysisData($list, $end, $months)
    {
        $analysis = array();
        for($begin = 1; $begin <= $end; $begin++)
        {
            $analysis[$begin]['date'] =date('m/d', strtotime($months . '-'. $begin));
            $analysis[$begin]['count'] = 0;
            $analysis[$begin]['arrived'] = 0;
            $analysis[$begin]['arrivedBefore'] = 0;
            $analysis[$begin]['arrivedLate'] = 0;
            foreach($list as $key => $val)
            {
                $todayStart = strtotime($months . '-'. $begin);
                $todayEnd = $todayStart  + 86400;
                if(($val['arrived_time'] >= $todayStart) && ($val['arrived_time'] < $todayEnd))
                {
                    $analysis[$begin]['count']++;
                }
                if(($val['arrived_time'] >= $todayStart) && ($val['arrived_time'] < $todayEnd) && $val['arrived_time'] <= $val['order_time_end'] && $val['arrived_time'] >= $val['order_time_start'])
                {
                    $analysis[$begin]['arrived']++;
                }
                if(($val['arrived_time'] >= $todayStart) && ($val['arrived_time'] < $todayEnd) && $val['arrived_time'] < $val['order_time_start'])
                {
                    $analysis[$begin]['arrivedBefore']++;
                }
                if(($val['arrived_time'] >= $todayStart) && ($val['arrived_time'] < $todayEnd) && $val['arrived_time'] > $val['order_time_end'])
                {
                    $analysis[$begin]['arrivedLate']++;
                }
            }
        }
        return $analysis;
    }

    public function export()
    {
        $id = $this->_get('pid', 'intval');
        $getYear = $this->_get('exYear', 'intval');
        $getMonth = $this->_get('exMonth', 'intval');
        $where = 1;
        if(trim($id))
        {
            $where .=' and pid = '.$id;
        }

        $property = $this->_get('property', 'trim');
        if(trim($property))
        {
            $this->assign('property', $property);
            $pid = D('property')->findField('title = "'.$property.'"','id');
            if(!$pid) $this->error('没有该楼盘的预约数据！');
            M('join_reservation','fph_',C('DB_member'));
            $rid = D('join_reservation')->getEnableByPid($pid,'id');
            if(!$rid) echo '楼盘暂无预约数据！';
            $where .=' and pid = '.$pid;
        }

        $months = $getYear .'-'.$getMonth;  // search month  ex: 2015-8
        if(!$getYear || !$getMonth)
        {
            $this->error('请选择要导出的数据月份');
            exit;
        }
        $end = date('t',strtotime($months)); // 获取指定月份天数
        $monthBefore = strtotime($months . '-01');
        $monthEnd = strtotime($months . '-'. $end) + 86400;
        $where .= ' and order_time_start >='.$monthBefore .' and order_time_end <= '. $monthEnd;
        M('join_reservation','fph_',C('DB_member'));
        $list = D('join_reservation')->getList($where, '*', false);
        $analysis = $this->buildAnalysisData($list, $end, $months);
        $this->analysisExcel($analysis, $getYear, $getMonth);
    }

    private function analysisExcel($analysis, $getYear, $getMonth)
    {
        Vendor("Classes.PHPExcel");
        Vendor("Classes.PHPExcel.php");
        //创建处理对象实例
        $objPhpExcel=new PHPExcel();
        $objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
        //设置表格的宽度  手动
        $objPhpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPhpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
        $objPhpExcel->getActiveSheet()->getColumnDimension('N')->setWidth(45);
        //设置标题
        $rowVal = array(0=>'日期',1=>'到访总数', 2=>'到访（准点）', 3=>'到访（提前）', 4=>'到访（推迟）');
        foreach ($rowVal as $k=>$r){
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)
                ->getFont()->setBold(true);//字体加粗
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
            getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
            $objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
        }
        $objPhpExcel->getActiveSheet()->setCellValue('A1', '日期')->getColumnDimension('A')->setWidth(12);
        $objPhpExcel->getActiveSheet()->setCellValue('B1', '到访总数')->getColumnDimension('B')->setWidth(12);
        $objPhpExcel->getActiveSheet()->setCellValue('C1', '到访（准点）')->getColumnDimension('C')->setWidth(12);
        $objPhpExcel->getActiveSheet()->setCellValue('D1', '到访（提前）')->getColumnDimension('D')->setWidth(12);
        $objPhpExcel->getActiveSheet()->setCellValue('E1', '到访（推迟）')->getColumnDimension('E')->setWidth(12);
        //设置当前的sheet索引 用于后续内容操作
        $objPhpExcel->setActiveSheetIndex(0);
        $objActSheet=$objPhpExcel->getActiveSheet();
        //设置当前活动的sheet的名称
        $title=$getYear.'年'. $getMonth .'月份预约到访数据';
        $objActSheet->setTitle($title);
        //insert data start
        foreach($analysis as $k=>$v){
            $num=$k+1;
            $objPhpExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$num, $v['date'])
                ->setCellValue('B'.$num, $v['count'])
                ->setCellValue('C'.$num, $v['arrived'])
                ->setCellValue('D'.$num, $v['arrivedBefore'])
                ->setCellValue('E'.$num, $v['arrivedLate']);
        }
        // insert data end
        $name = date('Y-m-d');//设置文件名
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

    //报表中心*预约客户
    public function lists(){

        $this->display();
    }

    //导出预约客户
    public function importLists(){
        M('join_reservation','fph_',C('DB_member'));
        $where = '1=1 and status = 1';
        $field = 'id,uid,pid,eservation_id,add_time,arrived_time,order_time_start,order_time_end,status';
        $order = 'add_time DESC';
        $list = D('join_reservation')->Lists($where, $field, $order);

        Vendor("Classes.PHPExcel");
        Vendor("Classes.PHPExcel.php");

        //创建处理对象实例
        $objPhpExcel=new PHPExcel();
        $objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
        //设置表格的宽度  手动
        $objPhpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPhpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
        $objPhpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPhpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPhpExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPhpExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $objPhpExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);

        //设置标题
        $rowVal = array(0=>'编号',1=>'楼盘名称', 2=>'预约奖励', 3=>'预约人姓名', 4=>'联系方式', 5=>'预约时间', 6=>'到访时间');
        foreach ($rowVal as $k=>$r){
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->getFont()->setBold(true);//字体加粗
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
            getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
            $objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
        }

        $objPhpExcel->getActiveSheet()->setCellValue('A1', '编号');
        $objPhpExcel->getActiveSheet()->setCellValue('B1', '楼盘名称');
        $objPhpExcel->getActiveSheet()->setCellValue('C1', '预约奖励');
        $objPhpExcel->getActiveSheet()->setCellValue('D1', '预约人姓名');
        $objPhpExcel->getActiveSheet()->setCellValue('E1', '联系方式');
        $objPhpExcel->getActiveSheet()->setCellValue('F1', '预约时间');
        $objPhpExcel->getActiveSheet()->setCellValue('G1', '到访时间');

        //设置当前的sheet索引 用于后续内容操作
        $objPhpExcel->setActiveSheetIndex(0);
        $objActSheet=$objPhpExcel->getActiveSheet();
        //设置当前活动的sheet的名称
        $title="预约客户";
        $objActSheet->setTitle($title);
        //设置单元格内容
        $j=1;
        foreach($list[0] as $k => $v){
            $arrived_time = $v['arrived_time'] ? date('Y/m/d H:i', $v['arrived_time']) : '-';
            $num=$k+2;
            $objPhpExcel->setActiveSheetIndex(0)
                //Excel的第A列，uid是你查出数组的键值，下面以此类推
                ->setCellValue('A'.$num, $j)
                ->setCellValue('B'.$num, $v['title'])
                ->setCellValue('C'.$num, $v['money'].'元')
                ->setCellValue('D'.$num, $v['username'])
                ->setCellValue('E'.$num, $v['mobile'])
                ->setCellValue('F'.$num, date('Y/m/d H:i', $v['order_time_start']).'-'.date('H:i', $v['order_time_end']))
                ->setCellValue('G'.$num, $arrived_time);
            $j++;
        }
        //$objPhpExcel->setActiveSheetIndex(0)->setCellValue('G2', '2321321321');//单行信息
        $title="预约客户";
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