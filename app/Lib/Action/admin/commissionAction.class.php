<?php
class commissionAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('commission');
    }
    public function index() {
        $fph = C('DB_PREFIX');
        $where['A.status']  = array('eq',7);
        $uid = $this->_get('uid','trim');
        $kehu = $this->_get('kehu','trim');
        $loupan = $this->_get('loupan','trim');
        $status = $this->_get('status','intval');
        if(!empty($uid))
        {
            $uid = M('user')->field('id')->where('username like "%'.$uid.'%"')->select();
            foreach($uid as $k=>$v){
                $arr_id[] = $v['id'];
            }
            if(!$arr_id){
                $arr_id = '暂无信息';
            }
            $where['B.uid']  = array('in',$arr_id);
        }
        if(!empty($kehu))
        {
            $where['A.username']  = array('like','%'.$kehu.'%');
        }
        $c_id = M('commission')->field('pid')->where('status =1')->select();
        $arr_cid = array();
        foreach($c_id as $k=>$v){
            $arr_cid[] =$v['pid'];
        }
        if(($status === 1) or ($status === 0))
        {
            if($status === 0 && !empty($arr_cid)){
               $where['A.id']  = array('not in',$arr_cid);
            }elseif($status===1){
                $where['C.status']  = array('eq',$status);
            }
        }
        if(!empty($loupan))
        {
            $loupan = M('property')->field('id')->where('title like "%'.$loupan.'%"')->select();
            foreach($loupan as $k=>$v){
                $arr_loupan[] = $v['id'];
            }
            if(!$arr_loupan){
                $arr_loupan = '暂无信息';
            }
            $where['A.pid']  = array('in',$arr_loupan);
        }
        $str = 'A.id,A.add_time,A.pid,A.total_price,A.username,B.uid,C.status';
        $count = M('myclient_status')
                ->field($str)
                ->table("{$fph}myclient_status AS A
                        LEFT JOIN {$fph}myclient_property AS B ON A.mpid = B.id left join {$fph}commission as C on C.pid=A.id")
                ->where($where)->count('A.id');
        $p = new Page($count,20);
        $page = $p->show();
        $list = M('myclient_status')
                ->field($str)
                ->limit($p->firstRow.','.$p->listRows)
                ->table("{$fph}myclient_status AS A
                        LEFT JOIN {$fph}myclient_property AS B ON A.mpid = B.id left join {$fph}commission as C on C.pid=A.id")
                ->where($where)->order('A.add_time DESC')->select();

        $list_pid     = array_unique(i_array_column($list, 'pid'));
        $list_pid     = implode(',', $list_pid);
        $property_arr = M('property')->where("id in ($list_pid)")->field('id,title')->select();
        foreach($property_arr as $k => $v){
            $property[$v['id']] = $v['title'];
        }

        $list_uid = array_unique(i_array_column($list, 'uid'));
        $list_uid = implode(',', $list_uid);
        $user_arr = M('user')->where("id in ($list_uid)")->field('id,username')->select();
        foreach($user_arr as $k => $v){
            $user[$v['id']] = $v['username'];
        }

        foreach($list as $k=>$v){
            $list[$k]['title']             = $property[$v['pid']];
            $list[$k]['uid']               = $user[$v['uid']];
            $list[$k]['commission']        = M('commission')->field('income,expenditure')->where('pid='.$v['id'])->find();
            $list[$k]['income']            = M('income')->where('pid='.$v['id'])->sum('price');
            $list[$k]['income_ratio']      = ($list[$k]['income']/$list[$k]['commission']['income'])*100;
            $list[$k]['expenditure']       = M('expenditure')->where('pid='.$v['id'])->sum('price');
            $list[$k]['expenditure_ratio'] = ($list[$k]['expenditure']/$list[$k]['commission']['expenditure'])*100;
        }
        //数据统计
        $list_t = M('myclient_status')
                ->field($str)
                ->table("{$fph}myclient_status AS A
                        LEFT JOIN {$fph}myclient_property AS B ON A.mpid = B.id left join {$fph}commission as C on C.pid=A.id")
                ->where($where)->select();
       /*foreach($list_t as $k=>$v){
            $list_t[$k]['income_c'] = M('commission')->where('pid='.$v['id'])->sum('income');//应收
            $list_t[$k]['expenditure_c'] = M('commission')->where('pid='.$v['id'])->sum('expenditure');//应付
            $list_t[$k]['income'] = M('income')->where('pid='.$v['id'])->sum('price');//实收
            $list_t[$k]['expenditure'] = M('expenditure')->where('pid='.$v['id'])->sum('price');//实付
            $list_t[$k]['income_ratio'] = ($list_t[$k]['income']/$list_t[$k]['income_c'])*100;//收款比率
            $list_t[$k]['expenditure_ratio'] = ($list_t[$k]['expenditure']/$list_t[$k]['expenditure_c'])*100;//付款比率
            $list_t['tot_income'] +=  $list_t[$k]['income_c'];
            $list_t['totincome'] +=  $list_t[$k]['income'];
            $list_t['tot_expenditure']  +=  $list_t[$k]['expenditure_c'];
            $list_t['totexpenditure'] +=  $list_t[$k]['expenditure'];
        }*/

        $list_t_id                 = array_unique(i_array_column($list_t, 'id'));
        $list_t_id                 = implode(',', $list_t_id);
        $list_t['tot_income']      = M('commission')->where("pid in ($list_t_id)")->sum('income');//应收
        $list_t['tot_expenditure'] = M('commission')->where("pid in ($list_t_id)")->sum('expenditure');//应付
        $list_t['totincome']       = M('income')->where("pid in ($list_t_id)")->sum('price');//实收
        $list_t['totexpenditure']  = M('expenditure')->where("pid in ($list_t_id)")->sum('price');//实付

        $list_t['jieyu1'] = $list_t['tot_income'] - $list_t['totincome'];
        $list_t['jieyu2'] = $list_t['tot_expenditure'] - $list_t['totexpenditure'];
        $uid = $this->_get('uid','trim');
        $kehu = $this->_get('kehu','trim');
        $loupan = $this->_get('loupan','trim');
        $this->assign('search', array('uid' => $uid,'kehu' => $kehu,'loupan' => $loupan,'status'=>$status));
        $this->assign('list_table', true);
        $this->assign('page',$page);
        $this->assign('list_t',$list_t);
        $this->assign('list',$list);
        $this->display();
    }

     public function edit(){
        $fph = C('DB_PREFIX');
        if(IS_POST){
			$stores_id    = $this->_post('stores_id','intval'); 
			$bank         = $this->_post('bank','trim'); 
			$bank_account = $this->_post('bank_account','trim'); 
			$bank_name    = $this->_post('bank_name','trim'); 
			if(!$stores_id){
				$this->error('该经纪人没有绑定门店,不能结算佣金!');exit;
			}
			
            $data['income'] =  $this->_post('income');
            $data['income_info'] =  $this->_post('income_info');
            $data['expenditure'] =  $this->_post('expenditure');
            $data['expenditure_info'] =  $this->_post('expenditure_info');
            $data['status'] =  $this->_post('status');
            $data['pid'] =  $this->_post('id');
            if(M('commission')->field('id')->where('pid='.$data['pid'])->find()){
                if (false !== M('commission')->where('pid='.$data['pid'])->save($data)){
					//更新门店信息
					M('stores')->where('id='.$stores_id)->save(array('bank'=>$bank,'bank_account'=>$bank_account,'bank_name'=>$bank_name));
					$this->success('修改成功');exit;
				}else{
					 $this->error('修改失败');exit;
				}
            }else{
                if (false !== M('commission')->add($data)){
					$this->success('修改成功');exit;
				}else{
					 $this->error('修改失败');exit;
				}
            }
        }
        $id = $this->_get('id','intval');
        $str = 'A.id,A.affirm_one,A.counterfoil,A.add_time,A.pid,A.total_price,A.with_look,A.username,A.mpid,A.measure,A.cate_id,A.number,B.uid';
        $info = M('myclient_status')
                ->field($str)
                ->table("{$fph}myclient_status AS A
                        LEFT JOIN {$fph}myclient_property AS B ON A.mpid = B.id")
                ->where('A.id ='.$id)->find();
        $info['mess'] = M('property')->field('id,title,prefer,commission_info')->where('id='.$info['pid'])->find();
        $info['uid'] = M('user')->field('stores_id,username,mobile')->where('id='.$info['uid'])->find();
        $info['cate_name'] = M('property_cate')->where('id='.$info['cate_id'])->getField('name');

        $c_info = M('commission')->where('pid='.$id)->find();
        if(!$c_info){
            $c_info['status']=0;
        }
        //echo $info['add_time'];
        $p_info = M('property_commission')->where('property_type  RLIKE "[[:<:]]'.$info['cate_id'].'[[:>:]]"  AND pid = '.$info['pid'].' AND term_start < '.$info['add_time'].'  AND  term_end > '.$info['add_time'].' ')->find();
        
        $stores_info = M('stores')->field('id,name,bank,bank_account,bank_name')->where(array('id'=>$info['uid']['stores_id']))->find();
        
        $cme = M('cooperation_methods')->where('pid = '.$info['pid'].' AND property_type like "%'.$info['cate_id'].'%" AND term_start < '.time().' AND term_end  > '.time().'' )->find();

        $this->assign('cme',$cme);	
        $this->assign('stores_info',$stores_info);	
        $this->assign('p_info',$p_info);
        $this->assign('c_info',$c_info);
        $this->assign('info',$info);
        $this->assign('type', 'edit');
        $this->assign('id', $id);
        $this->display();
    }

    /**
     * phpEscel导出结佣表数据
     * @author lishun
     * date  2014.12.25
     */
    function export_excel(){
       /* if($_SESSION['admin']['role_id'] != 1) {
            $this->error('无权限操作');
        }*/
       $str = 'A.id,A.add_time,A.pid,A.total_price,A.username,B.uid';
        $fph = C('DB_PREFIX');
        $where = 'A.status = 7';
        $list = M('myclient_status')
                ->field($str)
                ->table("{$fph}myclient_status AS A
                        LEFT JOIN {$fph}myclient_property AS B ON A.mpid = B.id")
                ->where($where)->select();
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
        $rowVal = array(0=>'编号',1=>'成交时间', 2=>'成交楼盘', 3=>'客户', 4=>'经纪人',5=>'总价',6=>'应收',7=>'实收',8=>'收款比率',9=>'应付',10=>'实付',11=>'付款比率');
        foreach ($rowVal as $k=>$r){
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)
            ->getFont()->setBold(true);//字体加粗
            $objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
            getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
            $objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
        }
        $objPhpExcel->getActiveSheet()->setCellValue('A1', '编号');
        $objPhpExcel->getActiveSheet()->setCellValue('B1', '成交时间');
        $objPhpExcel->getActiveSheet()->setCellValue('C1', '成交楼盘');
        $objPhpExcel->getActiveSheet()->setCellValue('D1', '客户');
        $objPhpExcel->getActiveSheet()->setCellValue('E1', '经纪人');
        $objPhpExcel->getActiveSheet()->setCellValue('F1', '总价');
        $objPhpExcel->getActiveSheet()->setCellValue('G1', '应收');
        $objPhpExcel->getActiveSheet()->setCellValue('H1', '实收');
        $objPhpExcel->getActiveSheet()->setCellValue('I1', '收款比率');
        $objPhpExcel->getActiveSheet()->setCellValue('J1', '应付');
        $objPhpExcel->getActiveSheet()->setCellValue('K1', '实付');
        $objPhpExcel->getActiveSheet()->setCellValue('L1', '付款比率');
        //设置当前的sheet索引 用于后续内容操作
        $objPhpExcel->setActiveSheetIndex(0);
        $objActSheet=$objPhpExcel->getActiveSheet();
        //设置当前活动的sheet的名称
        $title="结佣管理表";
        $objActSheet->setTitle($title);
        //设置单元格内容
        foreach($list as $k=>$v){
            $list[$k]['title'] = M('property')->where('id='.$v['pid'])->getField('title');
            $list[$k]['uid'] = M('user')->where('id='.$v['uid'])->getField('username');
            $list[$k]['commission'] = M('commission')->where('pid='.$v['id'])->find();
            $list[$k]['income'] = M('income')->where('pid='.$v['id'])->sum('price');
            $list[$k]['income_ratio'] = ($list[$k]['income']/$list[$k]['commission']['income'])*100;
            $list[$k]['expenditure'] = M('expenditure')->where('pid='.$v['id'])->sum('price');
            $list[$k]['expenditure_ratio'] = ($list[$k]['expenditure']/$list[$k]['commission']['expenditure'])*100;
            $list_t['tot_income'] +=  $list[$k]['commission']['income'];
            $list_t['totincome'] +=  $list[$k]['income'];
            $list_t['tot_expenditure']  +=  $list[$k]['commission']['expenditure'];
            $list_t['totexpenditure'] +=  $list[$k]['expenditure'];


            $num=$k+2;
            $objPhpExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$num, $k+1)
            ->setCellValue('B'.$num, date('Y-m-d H:i:s',$v['add_time']))
            ->setCellValue('C'.$num, $list[$k]['title'])
            ->setCellValue('D'.$num, $v['username'])
            ->setCellValue('E'.$num, $list[$k]['uid'])
            ->setCellValue('F'.$num, $v['total_price'])
            ->setCellValue('G'.$num, $list[$k]['commission']['income'])
            ->setCellValue('H'.$num, $list[$k]['income'])
            ->setCellValue('I'.$num, substr($list[$k]['income_ratio'],0,4).'%')
            ->setCellValue('J'.$num, $list[$k]['commission']['expenditure'])
            ->setCellValue('K'.$num, $list[$k]['expenditure'])
            ->setCellValue('L'.$num, substr($list[$k]['expenditure_ratio'],0,4).'%');
        }
        $objPhpExcel->setActiveSheetIndex(0)->setCellValue('N1', '应收:'.$list_t['tot_income']);
        $objPhpExcel->setActiveSheetIndex(0)->setCellValue('N2', '实收:'.$list_t['totincome']);
        $objPhpExcel->setActiveSheetIndex(0)->setCellValue('N3', '收款比率:'.substr(($list_t['totincome']/$list_t['tot_income'])*100,0,4).'%');
        $objPhpExcel->setActiveSheetIndex(0)->setCellValue('N4', '应付:'.$list_t['tot_expenditure']);
        $objPhpExcel->setActiveSheetIndex(0)->setCellValue('N5', '实付:'.$list_t['totexpenditure']);
        $objPhpExcel->setActiveSheetIndex(0)->setCellValue('N6', '付款比率:'.substr(($list_t['totexpenditure']/$list_t['tot_expenditure'])*100,0,4).'%');
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