<?php
class bluetoothAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
    }

    public function index() {

        M('bluetooth','fph_',C('DB_property'));

        $uuid       = $this->_get('uuid','trim');
        $major      = $this->_get('major','trim');
        $minor      = $this->_get('minor','trim');
        $title      = $this->_get('title','trim');
        $status     = $this->_get('status','trim');
        $time_start = $this->_get('time_start','trim');
        $time_end   = $this->_get('time_end','trim');

        $list = D('bluetooth')->lists($uuid, $major, $minor, $title, $status, $time_start, $time_end);
        $this->assign('list',$list[0]);
        $this->assign('page',$list[1]);

        $this->assign('search', array(
            'uuid' => $uuid,
            'major' => $major,
            'minor' => $minor,
            'title' => $title,
            'status'  => $status,
            'time_start' => $time_start,
            'time_end' => $time_end,
        ));

        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);
        $this->display();
    }


    public function add()
    {
        if(IS_POST){
            M('bluetooth','fph_',C('DB_property'));

            $uuid        = C('BLUETOOTH_UUID');
            $city_id     = $this->_post('city','intval');
            $major       = $this->_post('major','intval');
            $pid         = $this->_post('pid','intval');
            $time_start  = $this->_post('time_start','trim');
            $time_end    = $this->_post('time_end','trim');
            $time        = time();
            !$city_id && $this->error('请选择城市');
            !$major && $this->error('请选择major');

            $field = 'minor';
            $where = "uuid = '".$uuid."' AND major = ".$major;
            $order = 'minor DESC';
            $maxMajor = D('bluetooth')->maxMajor($field, $where, $order);
            if($maxMajor['minor'] == 65532){
                $this->error('该major号下的minor号已经处于最大值,请添加新的major');
            }

            if($pid){
                if(!$time_start || !$time_end){
                    $this->error('请选择设备绑定时间');
                }
                if(strtotime($time_end) < $time){
                    $this->error('设备绑定结束时间不能小于当前时间');
                }
                $data['time_start'] = strtotime($time_start);
                $data['time_end']   = strtotime($time_end);
                $data['status']     = 1;
            }else{
                $data['time_start'] = 0;
                $data['time_end']   = 0;
                $data['status']     = 0;
            }
            if($maxMajor){
                $data['minor'] = $maxMajor['minor'] + 1;
            }else{
                $data['minor'] = 1;
            }
            $data['uuid']     = $uuid;
            $data['major']    = $major;
            $data['pid']      = $pid;
            $data['add_time'] = $time;
            if(false !== D('bluetooth')->insertData($data)){
                $this->success( '添加成功');
            }else{
                $this->error('添加失败');
            }
            exit;

        }

        $provinceList = D('city')->province();
        $this->assign('provinceList',$provinceList);

        $uuid = C('BLUETOOTH_UUID');
        $this->assign('uuid',$uuid);

        $this->display();
    }

    public function show(){
        $id     = $this->_get('id','intval');
        $menuid = $this->_get('menuid','menuid');
        M('bluetooth','fph_',C('DB_property'));

        $field = 'id,uuid,major,minor,pid,time_start,time_end,status,add_time';
        $where = 'id = '.$id;
        $info = D('bluetooth')->show($field, $where);

        $fieldMajor = 'major';
        $whereMajor = 'id='.$info['major'];
        $majorInfo  = D('bluetooth_cate')->majorInfo($fieldMajor, $whereMajor);
        $info['major'] = $majorInfo['major'];

        M('property','fph_',C('DB_fangpinhui'));
        $whereProperty = 'id = '.$info['pid'];
        $fieldProperty = 'title';
        $propertyInfo  = D('property')->findPropertyInfo($whereProperty, $fieldProperty);

        $info['title'] = $propertyInfo['title'];
        $info['deviceID'] = $info['uuid'].'-'.sprintf('%04s', dechex($info['major'])).'-'.sprintf('%04s', dechex($info['minor']));
        $info['menuid'] = $menuid;

        $this->assign('info',$info);
        $this->display();
    }

    public function edit(){
        $id     = $this->_request('id','intval');
        $menuid = $this->_request('menuid','menuid');

        if(IS_POST){
            M('bluetooth','fph_',C('DB_property'));

            $uuid        = C('BLUETOOTH_UUID');
            $pid         = $this->_post('pid','intval');
            $time_start  = $this->_post('time_start','trim');
            $time_end    = $this->_post('time_end','trim');
            $status      = $this->_post('status','intval');
            $editPid     = $this->_post('editPid','intval');
            $major       = $this->_post('major','intval');
            $minor       = $this->_post('minor','intval');
            $time        = time();

            !$pid && $this->error('请选择绑定楼盘');
            if(!$time_start || !$time_end){
                $this->error('请选择设备绑定时间');
            }
            if(strtotime($time_end) < $time){
                $this->error('设备绑定结束时间不能小于当前时间');
            }
            if(!$major || !$minor){
                $this->error('系统参数出错！');
            }

            //查询设备是否已经绑定其他的楼盘
            if($status){
                $fieldInfo = 'id';
                $whereInfo = "id != ".$id." AND uuid = '".$uuid."' AND major = ".$major." AND minor = ".$minor." AND status = 1";
                $bluetoothInfo = D('bluetooth')->show($fieldInfo, $whereInfo);
                if($bluetoothInfo){
                    $this->error('该设备已经绑定其他楼盘！');
                    exit;
                }
            }

            $data['time_start'] = strtotime($time_start);
            $data['time_end']   = strtotime($time_end);
            $data['status']     = $status;
            $data['pid']        = $pid;

            $where = 'id = '.$id;
            $statusData['status'] = 0;


            if($pid != $editPid && $editPid){
                $data['uuid']     = $uuid;
                $data['major']    = $major;
                $data['minor']    = $minor;
                $data['add_time'] = $time;
                if($returnID = D('bluetooth')->insertData($data)){
                    D('bluetooth')->updateData($where, $statusData);
                    $this->success('修改成功',U('bluetooth/edit',array('id'=>$returnID,'menuid'=>$menuid)));
                }else{
                    $this->error('修改失败');
                }
            }elseif(!$editPid || $pid == $editPid){
                if(false !== D('bluetooth')->updateData($where, $data)){
                    $this->success('修改成功');
                }else{
                    $this->error('修改失败');
                }
            }
            exit;
        }
        M('bluetooth','fph_',C('DB_property'));

        $field = 'id,uuid,major,minor,pid,time_start,time_end,status,add_time';
        $where = 'id = '.$id;
        $info = D('bluetooth')->show($field, $where);
        $info['major_id'] = $info['major'];

        $fieldMajor = 'major';
        $whereMajor = 'id='.$info['major'];
        $majorInfo  = D('bluetooth_cate')->majorInfo($fieldMajor, $whereMajor);
        $info['major'] = $majorInfo['major'];

        M('property','fph_',C('DB_fangpinhui'));
        $whereProperty = 'id = '.$info['pid'];
        $fieldProperty = 'title';
        $propertyInfo  = D('property')->findPropertyInfo($whereProperty, $fieldProperty);

        $info['title'] = $propertyInfo['title'];
        //$info['deviceID'] = bin2hex($info['uuid']).'-'.sprintf('%04s', dechex($info['major'])).'-'.sprintf('%04s', dechex($info['minor']));
        $info['deviceID'] = $info['uuid'].'-'.sprintf('%04s', $info['major']).'-'.sprintf('%04s', $info['minor']);
        $info['menuid'] = $menuid;

        $this->assign('info',$info);
        //print_r($info);

        $this->display();
    }

    public function lists(){
        $id     = $this->_get('id','intval');
        $menuid = $this->_get('menuid','menuid');

        M('bluetooth','fph_',C('DB_property'));

        $field = 'id,uuid,major,minor,pid,time_start,time_end,status,add_time';
        $where = 'id = '.$id;
        $info = D('bluetooth')->show($field, $where);

        $whereList = "uuid = '".$info['uuid']."' AND major = ".$info['major']." AND minor = ".$info['minor']." AND status != 2";
        $list = D('bluetooth')->listsData($field, $whereList, 20);
        $this->assign('list',$list[0]);
        $this->assign('page',$list[1]);

        $this->assign('id',$id);
        $this->assign('menuid',$menuid);

        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);

        $this->display();
    }

    //导出excel
    public function export(){
        M('bluetooth','fph_',C('DB_property'));
        ini_set('memory_limit', '1024M');
        $whereList = "status != 2";
        $field = 'id,uuid,major,minor,pid,time_start,time_end,status,add_time';
        $list = D('bluetooth')->listsData($field, $whereList);
        $stores_list = $list[0];

        $total=count($stores_list);//总数

        Vendor("Classes.PHPExcel");
        Vendor("Classes.PHPExcel.php");

        //创建处理对象实例
        $objPhpExcel=new PHPExcel();
        $objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
        //设置表格的宽度  手动
        $objPhpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPhpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
        $objPhpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPhpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPhpExcel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
        $objPhpExcel->getActiveSheet()->getColumnDimension('F')->setWidth(35);
        $objPhpExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
        $objPhpExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
        $objPhpExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
        //设置标题
        $rowVal = array(0=>'编号', 1=>'UUID', 2=>'major', 3=>'minor', 4=>'设备ID', 5=>'当前安置楼盘', 6=>'链接人次', 7=>'使用状态', 8=>'添加时间');
        foreach ($rowVal as $k=>$r){
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)
                ->getFont()->setBold(true);//字体加粗
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
            getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
            $objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
        }

        $objPhpExcel->getActiveSheet()->setCellValue('A1', '编号');
        $objPhpExcel->getActiveSheet()->setCellValue('B1', 'UUID');
        $objPhpExcel->getActiveSheet()->setCellValue('C1', 'major');
        $objPhpExcel->getActiveSheet()->setCellValue('D1', 'minor');
        $objPhpExcel->getActiveSheet()->setCellValue('E1', '设备ID');
        $objPhpExcel->getActiveSheet()->setCellValue('F1', '当前安置楼盘');
        $objPhpExcel->getActiveSheet()->setCellValue('G1', '链接人次');
        $objPhpExcel->getActiveSheet()->setCellValue('H1', '使用状态');
        $objPhpExcel->getActiveSheet()->setCellValue('I1', '添加时间');

        //设置当前的sheet索引 用于后续内容操作
        $objPhpExcel->setActiveSheetIndex(0);
        $objActSheet=$objPhpExcel->getActiveSheet();
        //设置当前活动的sheet的名称
        $title="设备管理";
        $objActSheet->setTitle($title);
        //设置单元格内容
        $j=1;
        foreach($stores_list as $k => $v)
        {
            if($v['status']){
                $v['status'] = '已启用';
            }else{
                $v['status'] = '已停用';
            }
            $num=$k+2;
            $objPhpExcel->setActiveSheetIndex(0)
                //Excel的第A列，uid是你查出数组的键值，下面以此类推
                ->setCellValue('A'.$num, $j)
                ->setCellValue('B'.$num, $v['uuid'])
                ->setCellValue('C'.$num, $v['major'])
                ->setCellValue('D'.$num, $v['minor'])
                ->setCellValue('E'.$num, $v['deviceID'])
                ->setCellValue('F'.$num, $v['title'])
                ->setCellValue('G'.$num, $v['links'])
                ->setCellValue('H'.$num, $v['status'])
                ->setCellValue('I'.$num, date('Y-m-d', $v['add_time']));
            $j++;
        }
        //$objPhpExcel->setActiveSheetIndex(0)->setCellValue('I2', $total);//单行信息
        $title="设备管理";
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