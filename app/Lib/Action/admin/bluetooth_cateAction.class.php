<?php
class bluetooth_cateAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();

    }

    public function index() {
        $provinceList = D('city')->province();
        $this->assign('provinceList',$provinceList);

        M('bluetooth_cate','fph_',C('DB_property'));

        $city_id   = $this->_get('city_id','intval');
        $major     = $this->_get('major','trim');
        $list = D('bluetooth_cate')->lists($city_id, $major);
        $this->assign('list',$list[0]);
        $this->assign('page',$list[1]);
        $this->assign('selected_ids_city',$list[2]);

        $this->assign('search', array(
            'city_id' => $city_id,
            'major' => $major,
        ));

        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);
        $this->display();
    }


    public function add()
    {
        if(IS_POST){
            $city   = $this->_post('city','intval');
            $major  = $this->_post('major','intval');
            !$city && $this->error('请选择城市');
            !$major && $this->error('请填写major');

            M('bluetooth_cate','fph_',C('DB_property'));

            $where = "major = ".$major." AND status = 1";
            $majorCount = D('bluetooth_cate')->majorCount($where);

            if($majorCount){
                $this->error('该major号已经存在,请替换');
            }

            $data['city_id']  = $city;
            $data['major']    = $major;
            $data['add_time'] = time();
            if(false !== D('bluetooth_cate')->insertData($data)){
                $this->success( '添加成功');
            }else{
                $this->error('添加失败');
            }
            exit;
        }
        $provinceList = D('city')->province();
        $this->assign('provinceList',$provinceList);

        $this->display();
    }

    //删除
    public function delete(){
        if(IS_AJAX){
            $id  = $this->_get('id','intval');
            !$id && $this->ajaxReturn(0,'参赛出错');
            M('bluetooth_cate','fph_',C('DB_property'));

            $where = "id = ".$id."";
            $data['status'] = 2;
            if(false !== D('bluetooth_cate')->delete($where, $data)){
                $this->ajaxReturn(1, '删除成功');
            }else{
                $this->ajaxReturn(0, '删除失败');
            }

        }
    }

    //根据城市选择major
    public function ajax_major(){
        if(IS_AJAX){
            M('bluetooth_cate','fph_',C('DB_property'));
            $city_id = $this->_post('city_id','intval');
            $list = D('bluetooth_cate')->ajax_major($city_id);
            if($list){
                $this->ajaxReturn(1, '非法提交', $list);
            }else{
                $this->ajaxReturn(0, '没有找到相关major');
            }
        }
    }

    //导出excel
    public function export(){
        M('bluetooth_cate','fph_',C('DB_property'));

        $stores_list = D('bluetooth_cate')->export();

        $total=count($stores_list);//总数

        Vendor("Classes.PHPExcel");
        Vendor("Classes.PHPExcel.php");

        //创建处理对象实例
        $objPhpExcel=new PHPExcel();
        $objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
        //设置表格的宽度  手动
        $objPhpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objPhpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objPhpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPhpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        //设置标题
        $rowVal = array(0=>'编号',1=>'ID', 2=>'城市', 3=>'对应major');
        foreach ($rowVal as $k=>$r){
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)
                ->getFont()->setBold(true);//字体加粗
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
            getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
            $objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
        }

        $objPhpExcel->getActiveSheet()->setCellValue('A1', '编号');
        $objPhpExcel->getActiveSheet()->setCellValue('B1', 'ID');
        $objPhpExcel->getActiveSheet()->setCellValue('C1', '城市');
        $objPhpExcel->getActiveSheet()->setCellValue('D1', '对应major');
        //设置当前的sheet索引 用于后续内容操作
        $objPhpExcel->setActiveSheetIndex(0);
        $objActSheet=$objPhpExcel->getActiveSheet();
        //设置当前活动的sheet的名称
        $title="major管理";
        $objActSheet->setTitle($title);
        //设置单元格内容
        $j=1;
        foreach($stores_list as $k => $v)
        {
            $num=$k+2;
            $objPhpExcel->setActiveSheetIndex(0)
                //Excel的第A列，uid是你查出数组的键值，下面以此类推
                ->setCellValue('A'.$num, $j)
                ->setCellValue('B'.$num, $v['id'])
                ->setCellValue('C'.$num, $v['city_name'])
                ->setCellValue('D'.$num, $v['major']);
            $j++;
        }
        //$objPhpExcel->setActiveSheetIndex(0)->setCellValue('I2', $total);//单行信息
        $title="major管理";
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