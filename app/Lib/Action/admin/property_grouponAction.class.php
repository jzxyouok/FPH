<?php
class property_grouponAction extends backendAction
{

    public function _initialize() {
        parent::_initialize();
        $this->RedisDataBase= C('DB_REDIS_ACTIVITY_KANJIA');
        $this->RedisDataBaseMQ= C('DB_REDIS_PROPERTYMQ');
    }

    /*
    *@Descriptions：团购/团购
    *@Date:2014-07-21
    *@Author: chl
    */
    public function index() {

        //$redis = new CacheRedis($this->RedisDataBase);
        //$redis->handler->hmset('key2',array('field1'=>'v1','field2'=>'v2'));


        //$redis->rm('key2');
        //$redis->set('123111222221', 'ewrewrew');
        //$redis->handler->hset('key1','field1','v1');
        //$redis->handler->hset('key1','field2','v2');

        //$redis->handler->hmset('key2',array('field1'=>'v1','field2'=>'v2'));
        //$aa = $redis->handler->hmget('activity.groupon.5',array('now_number'));//单个字段读取
        //$aa = $redis->handler->hgetall('activity.groupon.5');//全部读取
        //print_r($aa);

        M(NULL,NULL,C('DB_activity'));

    	$p = $this->_get('p','intval',1);
        $pid = $this->_get('id','intval',1);

        $where ='pid = '.$pid;
        $count = M('groupon')->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();
        $list = M('groupon')->field('id,time_start,time_end,add_time,demand,preferential,status,schedule')->where($where)->order('id DESC')->limit($p->firstRow.','.$p->listRows)->select();
        foreach($list as $key=>$val){
            $list[$key]['count'] = M('join_groupon')->where(array('gid'=>$val['id'],'status'=>1))->count('id');
        }
        $this->assign('list',$list);
        $this->assign('page',$page);

    	$this->assign('p',$p);
        $this->assign('id',$pid);
    	$this->display();
    }

    /*
   *@Descriptions：编辑楼盘团购
   *@Date:2014-07-03
   *@Author: chl
   */
    public function edit() {
        $id   = $this->_request('id','intval',1);
        $pid = $this->_request('pid','intval',1);
        $user_list = D('property')->case_field_list($id);
        $this->assign('user_list',$user_list);

        M(NULL,NULL,C('DB_activity'));
        if(IS_POST){
            foreach ($_POST as $key=>$val) {
                $_POST[$key] = htmltag($val);
            }
            $id                  = $this->_post('id','intval');
            $pid                = $this->_post('pid','intval');
            $time_start    = $this->_post('time_start','trim');
            $time_end     = $this->_post('time_end','trim');
            $demand       = $this->_post('demand','intval');
            $preferential  = $this->_post('preferential','trim');
            $rule              = $this->_post('rule','trim');
            $aid                = $this->_post('aid','intval');
            $schedule       = $this->_post('schedule','intval');
            $status           = $this->_post('status','intval');

            if(!$id){
                $this->error('服务器异常');
            }
            if(!$time_start){
                $this->error('请选择开始时间');
            }
            if(!$time_end){
                $this->error('请选择结束时间');
            }
            if(strtotime($time_start) >= strtotime($time_end)){
                $this->error('开始时间不能大于结束时间');
            }
            /*if(!$aid){
                $this->error('请选择服务专员');
            }*/
            if($status){
                $activity_count = M('groupon')->where('pid = '.$id.' AND status = 1 AND id !='.$pid.'')->count('id');
                if($activity_count){
                    $this->error('系统中已经存在该楼盘的有效活动');
                }
            }
            $data['time_start']    = strtotime($time_start);
            $data['time_end']     = strtotime($time_end);
            $data['demand']       = $demand;
            $data['preferential'] = $preferential;
            $data['rule']             = $rule;
            $data['aid']              = $aid;
            $data['schedule']    = $schedule;
            $data['status']        = $status;
            $data['pid']              = $id;
            $data['id']              = $pid;
            $data['add_time']   = time();
            if(false !== M('groupon')->where('id = '.$pid)->save($data)){
                $now_number = M('join_groupon')->where('gid = '. $pid)->count('id');//单个字段读取
                $data['now_number'] = $now_number;
                $redis = new CacheRedis($this->RedisDataBase);
                $redis->handler->hmset('activity.groupon.'.$pid.'',$data);

                $RedisMQ = new CacheRedis($this->RedisDataBaseMQ);
                $RedisMQ->lRem('propertyMQ', $id , 0);
                $RedisMQ->lpush('propertyMQ', $id);

                $this->success('修改成功');
                exit;
            }else{
                $this->success('修改失败');
            }
        }

        $info = M('groupon')->where('id='.$pid)->find();
        $this->assign('info',$info);


        $this->assign('id',$id);
        $this->display();
    }

    /*
   *@Descriptions：添加楼盘团购
   *@Date:2014-07-03
   *@Author: chl
   */
    public function add() {
        $id   = $this->_request('id','intval',1);
        $pid = $this->_request('pid','intval',1);

        if(IS_POST){
            foreach ($_POST as $key=>$val) {
                $_POST[$key] = htmltag($val);
            }
            $id                 = $this->_post('id','intval');
            $time_start    = $this->_post('time_start','trim');
            $time_end     = $this->_post('time_end','trim');
            $demand       = $this->_post('demand','intval');
            $preferential  = $this->_post('preferential','trim');
            $rule              = $this->_post('rule','trim');
            $aid                = $this->_post('aid','intval');
            $schedule       = $this->_post('schedule','intval');
            $status           = $this->_post('status','intval');

            if(!$id){
                $this->error('服务器异常');
            }
            if(!$time_start){
                $this->error('请选择开始时间');
            }
            if(!$time_end){
                $this->error('请选择结束时间');
            }
            if(strtotime($time_start) >= strtotime($time_end)){
                $this->error('开始时间不能大于结束时间');
            }
//             if(!$aid){
//                 $this->error('请选择服务专员');
//             }
            M(NULL,NULL,C('DB_activity'));

            if($status){
                $activity_count = M('groupon')->where('pid = '.$id.' AND status = 1')->count('id');
                if($activity_count){
                    $this->error('系统中已经存在该楼盘的有效活动');
                }
            }
            $data['pid']               = $id;
            $data['time_start']    = strtotime($time_start);
            $data['time_end']     = strtotime($time_end);
            $data['demand']       = $demand;
            $data['preferential'] = $preferential;
            $data['rule']             = $rule;
            $data['aid']              = $aid;
            $data['schedule']    = $schedule;
            $data['status']        = $status;
            $data['add_time']   = time();
            if($return_id= D('groupon')->add($data)){
                $data['now_number'] = 0;
                $data['id'] = $return_id;
                $redis = new CacheRedis($this->RedisDataBase);
                $redis->handler->hmset('activity.groupon.'.$return_id.'',$data);

                $RedisMQ = new CacheRedis($this->RedisDataBaseMQ);
                $RedisMQ->lRem('propertyMQ', $id , 0);
                $RedisMQ->lpush('propertyMQ', $id);
                $this->success('提交成功');
                exit;
            }else{
                $this->success('提交失败');
            }
        }

        $user_list = D('property')->case_field_list($id);
        $this->assign('user_list',$user_list);

        $this->assign('p',$p);
        $this->assign('id',$id);
        $this->assign('time',date('Y-m-d H:i:s'));
        $this->display();
    }


    /*
 *@Descriptions：查看活动报名
 *@Date:2014-07-03
 *@Author: chl
 */
    public function lists(){
        M(NULL,NULL,C('DB_activity'));
        $id   = $this->_request('id','intval',1);
        $pid = $this->_request('pid','intval',1);
        $p    = $this->_get('p','intval',1);

        $where = 'gid = ' . $pid;
        $count = M('join_groupon')->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();
        $list = M('join_groupon')->field('id,gid,uid,mobile,sex,name,add_time,status')->where($where)->order('id DESC')->limit($p->firstRow.','.$p->listRows)->select();
        foreach($list as $key=>$val){
            M(NULL,'fph_',C('DB_member'));
            $list[$key]['username'] = M('member_extend')->where(array('uid'=>$val['uid']))->getfield('username');
            $list[$key]['user_mobile'] = M('member')->where(array('id'=>$val['uid']))->getfield('mobile');
        }
        $this->assign('list',$list);
        $this->assign('page',$page);


        $this->assign('id',$id);
        $this->assign('pid',$pid);
        $this->assign('p',$p);
        $this->display();
    }

    //报表中心*楼盘团购
    public function groupon_index(){

        $this->display();
    }

    //导出楼盘团购
    public function groupOnIndexImport(){
        M('groupon','fph_',C('DB_activity'));
        $field = 'id,pid,time_start,time_end,add_time,demand,preferential,status,schedule';
        $order = 'id DESC';
        $page  = 200;
        $list = D('groupon')->lists($field, $order, $page);

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
        $objPhpExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);

        //设置标题
        $rowVal = array(0=>'编号',1=>'楼盘名称', 2=>'城市', 3=>'有效期', 4=>'优惠幅度', 5=>'要求人数', 6=>'参与人数', 7=>'团购状态');
        foreach ($rowVal as $k=>$r){
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->getFont()->setBold(true);//字体加粗
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
            getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
            $objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
        }

        $objPhpExcel->getActiveSheet()->setCellValue('A1', '编号');
        $objPhpExcel->getActiveSheet()->setCellValue('B1', '楼盘名称');
        $objPhpExcel->getActiveSheet()->setCellValue('C1', '城市');
        $objPhpExcel->getActiveSheet()->setCellValue('D1', '有效期');
        $objPhpExcel->getActiveSheet()->setCellValue('E1', '优惠幅度');
        $objPhpExcel->getActiveSheet()->setCellValue('F1', '要求人数');
        $objPhpExcel->getActiveSheet()->setCellValue('G1', '参与人数');
        $objPhpExcel->getActiveSheet()->setCellValue('H1', '团购状态');

        //设置当前的sheet索引 用于后续内容操作
        $objPhpExcel->setActiveSheetIndex(0);
        $objActSheet=$objPhpExcel->getActiveSheet();
        //设置当前活动的sheet的名称
        $title="楼盘团购";
        $objActSheet->setTitle($title);
        //设置单元格内容
        $j=1;
        foreach($list[0] as $k => $v){
            if($v['schedule'] == 1){
                $schedule = '组团中';
            }elseif($v['schedule'] == 2){
                $schedule = '团购中';
            }if($v['schedule'] == 3){
                $schedule = '团购成功';
            }if($v['schedule'] == 4){
                $schedule = '团购失败';
            }
            $num=$k+2;
            $objPhpExcel->setActiveSheetIndex(0)
                //Excel的第A列，uid是你查出数组的键值，下面以此类推
                ->setCellValue('A'.$num, $j)
                ->setCellValue('B'.$num, $v['title'])
                ->setCellValue('C'.$num, $v['city_name'])
                ->setCellValue('D'.$num, date('Y/m/d', $v['time_start']).'-'.date('Y/m/d', $v['time_end']))
                ->setCellValue('E'.$num, $v['preferential'])
                ->setCellValue('F'.$num, $v['demand'])
                ->setCellValue('G'.$num, $v['count'])
                ->setCellValue('H'.$num, $schedule);
            $j++;
        }
        //$objPhpExcel->setActiveSheetIndex(0)->setCellValue('G2', '2321321321');//单行信息
        $title="楼盘团购";
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

    //报表中心*团购客户
    public function groupon_list(){

        $this->display();
    }

    //报表中心*导出团购客户
    public function groupOnListImport(){
        M('join_groupon','fph_',C('DB_activity'));
        $field = 'id,gid,uid,status,add_time';
        $order = 'add_time DESC';
        $page  = 200;
        $list = D('join_groupon')->lists($field, $order, $page);

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

        //设置标题
        $rowVal = array(0=>'编号',1=>'楼盘名称', 2=>'客户姓名', 3=>'联系方式', 4=>'参与时间');
        foreach ($rowVal as $k=>$r){
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->getFont()->setBold(true);//字体加粗
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
            getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
            $objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
        }

        $objPhpExcel->getActiveSheet()->setCellValue('A1', '编号');
        $objPhpExcel->getActiveSheet()->setCellValue('B1', '楼盘名称');
        $objPhpExcel->getActiveSheet()->setCellValue('C1', '客户姓名');
        $objPhpExcel->getActiveSheet()->setCellValue('D1', '联系方式');
        $objPhpExcel->getActiveSheet()->setCellValue('E1', '参与时间');

        //设置当前的sheet索引 用于后续内容操作
        $objPhpExcel->setActiveSheetIndex(0);
        $objActSheet=$objPhpExcel->getActiveSheet();
        //设置当前活动的sheet的名称
        $title="团购客户";
        $objActSheet->setTitle($title);
        //设置单元格内容
        $j=1;
        foreach($list[0] as $k => $v){
            $num=$k+2;
            $objPhpExcel->setActiveSheetIndex(0)
                //Excel的第A列，uid是你查出数组的键值，下面以此类推
                ->setCellValue('A'.$num, $j)
                ->setCellValue('B'.$num, $v['title'])
                ->setCellValue('C'.$num, $v['username'])
                ->setCellValue('D'.$num, $v['mobile'])
                ->setCellValue('E'.$num, date('Y/m/d', $v['add_time']));
            $j++;
        }
        //$objPhpExcel->setActiveSheetIndex(0)->setCellValue('G2', '2321321321');//单行信息
        $title="团购客户";
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