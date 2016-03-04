<?php
/**
 * Created by PhpStorm.
 * User: geyouwen
 * Date: 15-8-25
 * Time: 下午3:13
 */
class reservationAction extends backendAction
{
    public function _initialize()
    {
        parent::_initialize();
        M('reservation','fph_',C('DB_activity'));
        $this->redis= C('DB_REDIS_PROPERTY_RESERVATION');
        $this->RedisDataBase= C('DB_REDIS_PROPERTYMQ');
        $this->redisKey = 'property.reservation_';
    }

    public function index()
    {
        $id = $this->_get('id','intval');
        $option = 'pid = '.$id;
        $fields = '*';
        $data = D('reservation')->getList($option, $fields, true);
        M('join_reservation','fph_',C('DB_member'));
        $counts = D('join_reservation')->countNUm();
        if($data[1] && $counts){
            foreach($data[1] as $key => $val)
            {
                $data[1][$key]['num'] = 0;
                $data[1][$key]['account'] = 0;
                foreach($counts as $k => $v)
                {
                    if($val['id'] ==  $v['eservation_id']) $data[1][$key]['num'] = $v['num'];
                    $data[1][$key]['account'] = number_format($data[1][$key]['num'] * $val['money'], 2);
                }
                if($val['status'] == 0) $data[1][$key]['statustTips'] = '<span style="color: #ff0000; font-weight: bold">无效</span>';
                if($val['time_end']+86400 < time() ) $data[1][$key]['statustTips'] = '<span style="color: orange; font-weight: bold">已过期</span>';
                if($val['status'] == 1 && $val['time_end']+86400 >= time() ) $data[1][$key]['statustTips'] = '<span style="color: #03b301; font-weight: bold">使用中</span>';
                if($val['status'] == 2) $data[1][$key]['statustTips'] = '<span style="color:#ff0000; font-weight: bold">已删除</span>';
            }
        }
        //print_r($data[1]);
        $this->assign('nowTime', time());
        $this->assign('list', $data[1]);
        $this->assign('page', $data[0]);
        $this->assign('id', $id);
        $this->display();
    }

    public function add()
    {
        if(IS_POST)
        {
            $time_start = $this->_post('time_start', 'trim');
            $time_end = $this->_post('time_end', 'trim');
            $money = $this->_post('money','intval');
            $pid = $this->_post('id','intval');
            $data = array(
                'time_start' => strtotime($time_start),
                'time_end' => strtotime($time_end),
                'money' => $money,
                'add_time' => time(),
                'pid' => $pid,
                'status' => 1,
            );
            if($data['time_end']+86400 < time())  $this->error('结束时间错误，增加失败');
            $backId = D('reservation')->insertData($data);
            if(!$backId) $this->error('增加失败');
            D('reservation')->onlyOneTrue($backId, $pid);
            $redis = new CacheRedis($this->redis);
            //删除原有数据
            $redis->rm($this->redisKey . $pid);
            $data['id']      = $backId;
            $expires = ($data['time_end'] - time())+86400;
            $redis->handler->hmset($this->redisKey.$pid, $data);
            $redis->handler->expire($this->redisKey.$pid, $expires);

            //修改的数据写入mongo
            $redisMQ = new CacheRedis($this->RedisDataBase);
            $redisMQ->lRem('propertyMQ', $pid , 0);
            $redisMQ->lpush('propertyMQ', $pid);

            $this->assign('id', $pid);
            $this->success('添加成功', u('reservation/index',array('id' => $pid)));
        }
        $id = $this->_get('id','intval');

        $this->assign('id', $id);
        $this->display();
    }

    /**
     * 删除
     */
    public function delete()
    {

        $id = trim($this->_request('id'));
        $pid = trim($this->_request('pid'));
        if ($id) {
            if (false !== D('reservation')->del($id)) {
                //删除原有数据
                $redis = new CacheRedis($this->redis);
                $reservation = $redis->handler->hGetAll($this->redisKey .$pid);
                if($reservation && $reservation['id']==$id){
                    $redis->rm($this->redisKey . $pid);
                }

                //修改的数据写入mongo
                $redisMQ = new CacheRedis($this->RedisDataBase);
                $redisMQ->lRem('propertyMQ', $pid , 0);
                $redisMQ->lpush('propertyMQ', $pid);
                IS_AJAX && $this->ajaxReturn(1, L('operation_success'));
                $this->success(L('operation_success'));
            } else {
                IS_AJAX && $this->ajaxReturn(0, L('operation_failure'));
                $this->error(L('operation_failure'));
            }
        } else {
            IS_AJAX && $this->ajaxReturn(0, L('illegal_parameters'));
            $this->error(L('illegal_parameters'));
        }
    }

    public function edit()
    {
        if (IS_POST)
        {
            $time_start = $this->_post('time_start', 'trim');
            $time_end = $this->_post('time_end', 'trim');
            $id = $this->_post('id','intval');
            $data = array(
                'time_start' => strtotime($time_start),
                'time_end' => strtotime($time_end),
            );
            if($data['time_end']+86400 < time())  $this->error('结束时间错误，编辑失败');
            $back = D('reservation')->updateById($id, $data);
            $redis = new CacheRedis($this->redis);
            $info = D('reservation')->getOne($id);
            //删除redis原有数据
            $redis->rm($this->redisKey . $info['pid']);
            $expires = ($data['time_end'] - time())+86400;
            $redis->handler->hmset($this->redisKey.$info['pid'], $info);
            $redis->handler->expire($this->redisKey.$info['pid'], $expires);

            //修改的数据写入mongo
            $redisMQ = new CacheRedis($this->RedisDataBase);
            $redisMQ->lRem('propertyMQ', $info['pid'] , 0);
            $redisMQ->lpush('propertyMQ', $info['pid']);
            if($back) return $this->success(L('operation_success'));
            return $this->success('没有变更数据');
        }
        $id = $this->_get('id','intval');
        $pid = $this->_get('pid','intval');
        $data = D('reservation')->getOne($id);
        $this->assign('info', $data);
        $this->assign('id', $pid);
        $this->display();
    }

    //报表中心*楼盘预约
    public function lists(){

        $this->display();
    }

    //导出楼盘预约
    public function importLists(){
        M('reservation','fph_',C('DB_activity'));
        $where = '1=1';
        $field = 'id,pid,money,time_start,time_end,status,add_time';
        $order = 'add_time DESC';
        $list = D('reservation')->Lists($where, $field, $order);
        M('join_reservation','fph_',C('DB_member'));
        $counts  = D('join_reservation')->countNUm();
        $joinWhere = "arrived_time != 0";
        $joinNum = D('join_reservation')->countNUm($joinWhere);
        if($list[0] && $counts){
            foreach($list[0] as $key => $val)
            {
                $list[0][$key]['num'] = 0;
                $list[0][$key]['joinNum'] = 0;
                $list[0][$key]['account'] = 0;
                foreach($counts as $k => $v){
                    if($val['id'] == $v['eservation_id']) $list[0][$key]['num'] = $v['num'];
                    //$list[0][$key]['account'] = number_format($list[0][$key]['num'] * $val['money'], 2);
                }
                foreach($joinNum as $k => $v){
                    if($val['id'] ==  $v['eservation_id']) $list[0][$key]['joinNum'] = $v['joinNum'];
                    $list[0][$key]['account'] = number_format($list[0][$key]['joinNum'] * $val['money'], 2);
                }
                if($val['status'] == 0) $list[0][$key]['statustTips'] = '无效';
                if($val['time_end']+86400 < time() ) $list[0][$key]['statustTips'] = '已过期';
                if($val['status'] == 1 && $val['time_end']+86400 >= time() ) $list[0][$key]['statustTips'] = '使用中';
                if($val['status'] == 2) $list[0][$key]['statustTips'] = '已删除';
            }
        }
        //print_r($list[0]);exit;
        Vendor("Classes.PHPExcel");
        Vendor("Classes.PHPExcel.php");

        //创建处理对象实例
        $objPhpExcel=new PHPExcel();
        $objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
        //设置表格的宽度  手动
        $objPhpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPhpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
        $objPhpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
        $objPhpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
        $objPhpExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPhpExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $objPhpExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);

        //设置标题
        $rowVal = array(0=>'编号',1=>'楼盘名称', 2=>'城市', 3=>'预约奖励', 4=>'预约人数', 5=>'预约已到访人数', 6=>'支出费用');
        foreach ($rowVal as $k=>$r){
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->getFont()->setBold(true);//字体加粗
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
            getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
            $objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
        }

        $objPhpExcel->getActiveSheet()->setCellValue('A1', '编号');
        $objPhpExcel->getActiveSheet()->setCellValue('B1', '楼盘名称');
        $objPhpExcel->getActiveSheet()->setCellValue('C1', '城市');
        $objPhpExcel->getActiveSheet()->setCellValue('D1', '预约奖励');
        $objPhpExcel->getActiveSheet()->setCellValue('E1', '预约人数');
        $objPhpExcel->getActiveSheet()->setCellValue('F1', '预约已到访人数');
        $objPhpExcel->getActiveSheet()->setCellValue('G1', '支出费用');

        //设置当前的sheet索引 用于后续内容操作
        $objPhpExcel->setActiveSheetIndex(0);
        $objActSheet=$objPhpExcel->getActiveSheet();
        //设置当前活动的sheet的名称
        $title="楼盘团购";
        $objActSheet->setTitle($title);
        //设置单元格内容
        $j=1;
        foreach($list[0] as $k => $v){
            $num=$k+2;
            $objPhpExcel->setActiveSheetIndex(0)
                //Excel的第A列，uid是你查出数组的键值，下面以此类推
                ->setCellValue('A'.$num, $j)
                ->setCellValue('B'.$num, $v['title'])
                ->setCellValue('C'.$num, $v['city_name'])
                ->setCellValue('D'.$num, $v['money'].'元/人')
                ->setCellValue('E'.$num, $v['num'].'人')
                ->setCellValue('F'.$num, $v['joinNum'].'人')
                ->setCellValue('G'.$num, $v['account'].'元');
            $j++;
        }
        //$objPhpExcel->setActiveSheetIndex(0)->setCellValue('G2', '2321321321');//单行信息
        $title="楼盘预约";
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