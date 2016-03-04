<?php
class testAction extends frontendAction {
    
    public function index() {
	    $date = date('Y-m-d H:i:s');
		M('test')->add(array('test'=>$date));
		echo $date;exit;
    }

    //修改手机号码对应的city_id
    //1430352000
    //1430443920
    public function get_city_id(){
    	//echo 'www';exit;
    	header("Content-Type: text/html; charset=UTF-8");
		$list = M('user')->field('id,mobile,city_id,reg_time')->where("city_id=0 AND reg_time>=1434124800 AND reg_time<=1434384000")->limit(0,1500)->select();
		//print_r($list);exit;
		echo M('user')->getlastsql();
    	foreach($list as $key=>$value){
    		//手机号码归属地
            $city_name = get_city($value['mobile']);         
            $city_id = M('city')->where("name='".$city_name."'")->getfield('id');             
            if(!$city_id) $city_id=0;           
            if(false !== M('user')->where(array('id'=>$value['id']))->save(array('city_id'=>$city_id))){          	
            	echo $key.'--'.$value['id'].'--'.$value['mobile'].'--'.$city_name.'--'.date('Y-m-d H:i',$value['reg_time']).'<br>';
            }else{
            	echo '失败--'.$city_name;
            }    
    	}
    }

    public function up_time(){

    	$add_time = M('myclient_status')->where('add_time !=0 ')->order('add_time asc')->getField('add_time');
 		M('myclient_status')->where('add_time=0')->save(array('add_time'=>$add_time));
    	echo 'ok';

    }
	public function metro(){
		 $str = '西流湖,西三环,秦岭路,桐柏路,碧沙岗,绿城广场,医学院,郑州火车站,二七广场,人民路,紫荆山路,燕庄,民航路,会展中心,黄河南路,农业南路,东风南路,郑州东,博学路,市体育中心';

		$arr = explode(',', $str);
		foreach($arr as $k=>$v){
			echo $v.'<BR>';
			$data['city_id'] = 0;
			$data['pid'] = 496;
			$data['name'] = $v;
			//M('metro')->add($data);
		}
		// $data['city_id'] = 22;
		// $data['pid'] = 0;
		// $data['name'] = '9号线';
		// M('metro')->add($data);

		//$this->display();
	}
	//头像程序
	public function avatar(){
		$uid = M('user')->field('id')->select();
		
		foreach($uid as $key=>$val){
			$uid = $val['id'];
            $uid = abs(intval($uid));
            $suid = sprintf("%09d", $uid);
            $dir1 = substr($suid, 0, 3);
            $dir2 = substr($suid, 3, 2);
            $dir3 = substr($suid, 5, 2);
            $avatar_dir = $dir1.'/'.$dir2.'/'.$dir3.'/';
			$avatar = $avatar_dir.md5($uid).'.jpg';
			
			 M('user')->where(array('id'=>$uid))->save(array('avatar'=>$avatar));
			 
			echo $avatar_dir.md5($uid).'.jpg<br>';
		}
	}
	
	
	//导出excel
	public function out_excel(){
		
    	$stores_list = M('stores')->field('id,uid,code_id,name,address')->where("contact is null")->select();

		$total=count($stores_list);//总数
    	
    	Vendor("Classes.PHPExcel");
    	Vendor("Classes.PHPExcel.php");

    	//创建处理对象实例
    	$objPhpExcel=new PHPExcel();
    	$objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
    	//设置表格的宽度  手动
		$objPhpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
		$objPhpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
    	$objPhpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
    	$objPhpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
    	$objPhpExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
		$objPhpExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
		$objPhpExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
		$objPhpExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
		$objPhpExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
		
    	//设置标题
    	$rowVal = array(0=>'编号',1=>'ID', 2=>'门店代码', 3=>'门店名称', 4=>'门店地址',5=>'创建人', 6=>'创建人电话', 7=>'联系人',8=>'电话');
    	foreach ($rowVal as $k=>$r){
    		$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)
    		->getFont()->setBold(true);//字体加粗
    		$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
    		getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
    		$objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
    	}
		
    	$objPhpExcel->getActiveSheet()->setCellValue('A1', '编号');
    	$objPhpExcel->getActiveSheet()->setCellValue('B1', 'ID');
    	$objPhpExcel->getActiveSheet()->setCellValue('C1', '门店代码');
    	$objPhpExcel->getActiveSheet()->setCellValue('D1', '门店名称');
    	$objPhpExcel->getActiveSheet()->setCellValue('E1', '门店地址');
		$objPhpExcel->getActiveSheet()->setCellValue('F1', '创建人');
		$objPhpExcel->getActiveSheet()->setCellValue('G1', '创建人电话');
		$objPhpExcel->getActiveSheet()->setCellValue('H1', '联系人');
		$objPhpExcel->getActiveSheet()->setCellValue('I1', '电话');
    	//设置当前的sheet索引 用于后续内容操作
    	$objPhpExcel->setActiveSheetIndex(0);
    	$objActSheet=$objPhpExcel->getActiveSheet();
    	//设置当前活动的sheet的名称
    	$title="门店名单";
    	$objActSheet->setTitle($title);
    	//设置单元格内容
		$j=1;
    	foreach($stores_list as $k => $v)
    	{   
			$user_info = M('user')->field('username,mobile')->where(array('id'=>$v['uid']))->find();
    		$num=$k+2;
    		$objPhpExcel->setActiveSheetIndex(0)
    		//Excel的第A列，uid是你查出数组的键值，下面以此类推
    		->setCellValue('A'.$num, $j)
    		->setCellValue('B'.$num, $v['id'])
    		->setCellValue('C'.$num, $v['code_id'])
    		->setCellValue('D'.$num, $v['name'])
    		->setCellValue('E'.$num, $v['address'])
    		->setCellValue('F'.$num, $user_info['username'])
			->setCellValue('G'.$num, $user_info['mobile'])
			->setCellValue('H'.$num, '')
			->setCellValue('I'.$num, '');
		$j++;
    	}
    	//$objPhpExcel->setActiveSheetIndex(0)->setCellValue('I2', $total);//单行信息
    	$title="门店名单";
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
	
	
	//导出excel*楼盘
	public function out_excel_property(){
		
    	$list = M('property')->field('id,title')->where("status = 0")->select();

		$total=count($list);//总数
    	
    	Vendor("Classes.PHPExcel");
    	Vendor("Classes.PHPExcel.php");

    	//创建处理对象实例
    	$objPhpExcel=new PHPExcel();
    	$objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
    	//设置表格的宽度  手动
		$objPhpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
		$objPhpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
    	$objPhpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(35);

		
    	//设置标题
    	$rowVal = array(0=>'编号',1=>'ID', 2=>'楼盘名称');
    	foreach ($rowVal as $k=>$r){
    		$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)
    		->getFont()->setBold(true);//字体加粗
    		$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
    		getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
    		$objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
    	}
		
    	$objPhpExcel->getActiveSheet()->setCellValue('A1', '编号');
    	$objPhpExcel->getActiveSheet()->setCellValue('B1', 'ID');
    	$objPhpExcel->getActiveSheet()->setCellValue('C1', '楼盘名称');

    	//设置当前的sheet索引 用于后续内容操作
    	$objPhpExcel->setActiveSheetIndex(0);
    	$objActSheet=$objPhpExcel->getActiveSheet();
    	//设置当前活动的sheet的名称
    	$title="未发布楼盘";
    	$objActSheet->setTitle($title);
    	//设置单元格内容
		$j=1;
    	foreach($list as $k => $v)
    	{   
			$user_info = M('user')->field('username,mobile')->where(array('id'=>$v['uid']))->find();
    		$num=$k+2;
    		$objPhpExcel->setActiveSheetIndex(0)
    		//Excel的第A列，uid是你查出数组的键值，下面以此类推
    		->setCellValue('A'.$num, $j)
    		->setCellValue('B'.$num, $v['id'])
    		->setCellValue('C'.$num, $v['title']);
		$j++;
    	}
    	//$objPhpExcel->setActiveSheetIndex(0)->setCellValue('I2', $total);//单行信息
    	$title="未发布楼盘";
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
	
	/*
	*门店加sid=admin 表 id
	*/
	public function stores_sid(){
		header("Content-Type: text/html; charset=UTF-8");
		$fph  = C('DB_PREFIX');
		$list = M('stores')->field('A.id,B.mobile')->table("{$fph}stores AS A INNER JOIN {$fph}user AS B ON A.service = B.id")->select();
		foreach($list as $v){
			$admin = M('admin')->field('id')->where('mobile ='.$v['mobile'])->find();
				if($admin){
					$sid = $admin['id'];
					if(false !== M('stores')->where('id ='.$v['id'])->save(array('sid'=>$sid))){
						echo '转换'.$v['id'].'门店成功11！<br>';
					}else{
						echo '转换'.$v['id'].'门店失败！<br>';
					}
				}
		}
		exit;
	}
	
	/*
	*门店加sid=admin 表 id
	*/
	public function stores_mid(){
		header("Content-Type: text/html; charset=UTF-8");
		$fph  = C('DB_PREFIX');
		$list = M('stores')->field('A.id,B.mobile')->table("{$fph}stores AS A INNER JOIN {$fph}user AS B ON A.uid = B.id")->where("type_table=1")->select();
		foreach($list as $v){
			$admin = M('admin')->field('id')->where('mobile ='.$v['mobile'])->find();
				if($admin){
					$sid = $admin['id'];
					if(false !== M('stores')->where('id ='.$v['id'])->save(array('mid'=>$sid))){
						echo '转换'.$v['id'].'门店成功22！<br>';
					}else{
						echo '转换'.$v['id'].'门店失败！<br>';
					}
				}
		}
		exit;
	}
	
	public function stores_log_mid(){
		header("Content-Type: text/html; charset=UTF-8");
		$fph  = C('DB_PREFIX');
		$list = M('stores_log')->field('A.id,B.mobile')->table("{$fph}stores_log AS A INNER JOIN {$fph}user AS B ON A.uid = B.id")->select();
		foreach($list as $v){
			$admin = M('admin')->field('id')->where('mobile ='.$v['mobile'])->find();
			//echo M('admin')->getlastsql().'<br>';
				if($admin){
					if(false !== M('stores_log')->where('id ='.$v['id'])->save(array('mid'=>$admin['id']))){
						echo '转换'.$v['id'].'门店成功33！<br>';
					}else{
						echo '转换'.$v['id'].'门店失败！<br>';
					}
				}
		}
		exit;
	}
	
	public function company_mid(){
		header("Content-Type: text/html; charset=UTF-8");
		$fph  = C('DB_PREFIX');
		$list = M('company')->field('A.id,A.uid,B.mobile')->table("{$fph}company AS A INNER JOIN {$fph}user AS B ON A.uid = B.id")->select();
		foreach($list as $v){
			$admin = M('admin')->where('mobile ='.$v['mobile'])->find();
			if($admin){
				if(false !== M('company')->where('id ='.$v['id'])->save(array('mid'=>$admin['id']))){
					echo '公司'.$v['id'].'门店成功44！<br>';
				}else{
					echo '公司'.$v['id'].'门店失败！<br>';
				}
			}
		}
		exit;
	}

	//导入Excel
	public function inprot_excel(){
		header("Content-Type: text/html; charset=UTF-8");
		if(IS_POST){
			echo "<a href=''.U('test/inprot_excel').''>继续导入</a><br/>";
			ini_set('memory_limit', '1024M');
			header("Content-Type: text/html; charset=UTF-8");
    		$tmp_name = $_FILES['inputExcel']['tmp_name'];
			$userid   = $this->_post('userid','intval');
			$pid      = $this->_post('pid','intval');
			$format   = explode('.',$_FILES['inputExcel']['name']);
			if($format[1]!='xls'){
				$this->error('请上传格式为.xls的文件');exit;
			}
    		if(!$tmp_name){
				$this->error('请先上传Excel文件');exit;
			}
			if(!$userid){
				$this->error('请填写经纪人id');exit;
			}
			if(!$pid){
				$this->error('请填写楼盘id');exit;
			}
    		Vendor("Classes.PHPExcel1");
    		Vendor("Classes.PHPExcel.php");
    		Vendor("Classes.PHPExcel.IOFactory");
    		Vendor("Classes.PHPExcel.Reader.Excel5");
    		$objReader = PHPExcel_IOFactory::createReader('Excel5');
    		$objPHPExcel = $objReader->load($tmp_name);
    		$sheet = $objPHPExcel->getSheet(0);
    		$highestRow = $sheet->getHighestRow(); // 取得总行数
    		$highestColumn = $sheet->getHighestColumn(); // 取得总列数
    		

    		$k = 0;
    		for($j=2;$j<=$highestRow;$j++){
    
    			$date   = $objPHPExcel->getActiveSheet()->getCell("B".$j)->getValue();
    			$name   = $objPHPExcel->getActiveSheet()->getCell("C".$j)->getValue();
				$mobile = $objPHPExcel->getActiveSheet()->getCell("D".$j)->getValue();
				$gender = $objPHPExcel->getActiveSheet()->getCell("E".$j)->getValue();
				 
    			$last_id = M('myclient')->add(array('name'=>(string)$name,'mobile'=>(string)$mobile,'gender'=>(string)$gender));//生成id
				
    			$time = strtotime(str_replace('.','-',$date));
    			//写入报备数据
		        $data['uid']             = $userid;
		        $data['pid']             = $last_id;
		        $data['property']        = $pid;
		        $data['status']          = 1;
		        $data['with_look']       = 1;
		        $data['protection_time'] = 15;
		        $data['add_time']        = $time;
				$data['visit_time']      = $time;
		        $mpid = M('myclient_property')->add($data);

		        //写入记录*流程
				$datas['mpid']       = $mpid;
				$datas['pid']        = $pid;
				$datas['status']     = 1;
				$datas['status_cid'] = 1;
				$datas['with_look']  = 1;
				$datas['add_time']   = $time;
				$datas['name']       = '杨慧';
				$datas['visit_time'] = $time;
				M('myclient_status')->add($datas);
					

    			if($last_id){
    				echo "第".$j."行导入成功 <br/>";
    			}else {
    				echo "<span style='color:#ff0000'>第".$j."行导入失败</span><br/>";
    			}
    
    		}


		}
		$this->display();
	}
	
	//删除用户头像图片
	public function del_user_avatar(){
		$suffix = array('_64x64','_100x100');
		$fdfs_obj = new FastFile();
		$list = M('user')->field('avatar')->where("avatar LIKE '%group1%'")->select();
		
		foreach($list as $key=>$val){
			$fdfs_obj->fast_del_img($val['avatar']);
			$img_exp = explode('.',$val['avatar']);
			foreach($suffix as $k=>$v){
				$img_thumb = $img_exp[0].$v.'.'.$img_exp[1];
				$fdfs_obj->fast_del_img($img_thumb);
			}
			echo $val['avatar'].'<br>';
		}
		//print_r($list);
	}
	
	//删除门店图片
	public function del_stores_img(){
		$suffix = array('_100x70');
		$fdfs_obj = new FastFile();
		$list = M('stores')->field('img')->where("img LIKE '%group1%'")->select();
		//print_r($list);exit;
		foreach($list as $key=>$val){
			$fdfs_obj->fast_del_img($val['img']);
			$img_exp = explode('.',$val['img']);
			foreach($suffix as $k=>$v){
				$img_thumb = $img_exp[0].$v.'.'.$img_exp[1];
				$fdfs_obj->fast_del_img($img_thumb);
			}
			echo $val['img'].'<br>';
		}
		//print_r($list);
	}
	
	//删除媒体图片
	public function del_media_img(){
		$suffix = array('_100x70');
		$fdfs_obj = new FastFile();
		$list = M('media')->field('img')->where("img LIKE '%group1%'")->select();
		//print_r($list);exit;
		foreach($list as $key=>$val){
			$fdfs_obj->fast_del_img($val['img']);
			/*$img_exp = explode('.',$val['img']);
			foreach($suffix as $k=>$v){
				$img_thumb = $img_exp[0].$v.'.'.$img_exp[1];
				$fdfs_obj->fast_del_img($img_thumb);
			}*/
			echo $val['img'].'<br>';
		}
		//print_r($list);
	}

	//初始admin表，增加邀请码
	public function admin_code_id(){
		$list = M('admin')->field('id')->select();
		$i = 800000;
		foreach($list as $key=>$val){
			M('admin')->where(array('id'=>$val['id']))->save(array('code_id'=>$i));
			$i++;
		}
		//print_r($list);
	}

	//初始user表，增加admin_id
	public function user_admin_id(){
		header("Content-Type: text/html; charset=UTF-8");
		/*$list = M('admin')->field('id')->select();
		foreach($list as $key=>$val){
			$list[$key]['stores'] = M('stores')->field('id,name')->where(array('type'=>1,'mid'=>$val['id']))->select();
			foreach($list[$key]['stores'] as $k=>$v){
				echo $val['id'].'--'.$v['id'].'<br>';
			}
		}*/
		$list = M('stores')->where(array('type'=>1))->field('id,mid,name')->select();
		foreach($list as $key=>$val){
			echo $val['mid'].'--'.$val['id'].'<br>';
			M('user')->where(array('stores_id'=>$val['id']))->save(array('admin_id'=>$val['mid']));
		}		
	}

	//导出excel*无服务专员门店
	public function out_excel_stores(){
		
    	$list = M('stores')->field('id,code_id,name,city_id,add_time')->where("type = 2 AND sid=0")->select();

		$total=count($list);//总数
    	
    	Vendor("Classes.PHPExcel");
    	Vendor("Classes.PHPExcel.php");

    	//创建处理对象实例
    	$objPhpExcel=new PHPExcel();
    	$objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
    	//设置表格的宽度  手动
		$objPhpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
		$objPhpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
    	$objPhpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
    	$objPhpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    	$objPhpExcel->getActiveSheet()->getColumnDimension('E')->setWidth(35);
    	$objPhpExcel->getActiveSheet()->getColumnDimension('F')->setWidth(35);

		
    	//设置标题
    	$rowVal = array(0=>'编号',1=>'邀请码', 2=>'门店名称', 3=>'共有人数', 4=>'城市', 5=>'添加时间');
    	foreach ($rowVal as $k=>$r){
    		$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)
    		->getFont()->setBold(true);//字体加粗
    		$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
    		getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
    		$objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
    	}
		
    	$objPhpExcel->getActiveSheet()->setCellValue('A1', '编号');
    	$objPhpExcel->getActiveSheet()->setCellValue('B1', '邀请码');
    	$objPhpExcel->getActiveSheet()->setCellValue('C1', '门店名称');
    	$objPhpExcel->getActiveSheet()->setCellValue('D1', '共有人数');
    	$objPhpExcel->getActiveSheet()->setCellValue('E1', '添加时间');
    	$objPhpExcel->getActiveSheet()->setCellValue('F1', '城市');

    	//设置当前的sheet索引 用于后续内容操作
    	$objPhpExcel->setActiveSheetIndex(0);
    	$objActSheet=$objPhpExcel->getActiveSheet();
    	//设置当前活动的sheet的名称
    	$title="无服务专员门店";
    	$objActSheet->setTitle($title);
    	//设置单元格内容
		$j=1;
    	foreach($list as $k => $v)
    	{   
			$stores_user = M('user')->where(array('stores_id'=>$v['id']))->count('id');

			//城市
			$city_spid = M('city')->where('id ='.$v['city_id'])->getField('spid');
            $spid_arr = explode('|', $city_spid.$v['city_id']);
            $get_cityname = '';
            foreach ($spid_arr as $key => $value) {
                $get_cityname .= M('city')->where('id ='.$value)->getField('name').' ';
            }

    		$num=$k+2;
    		$objPhpExcel->setActiveSheetIndex(0)
    		//Excel的第A列，uid是你查出数组的键值，下面以此类推
    		->setCellValue('A'.$num, $j)
    		->setCellValue('B'.$num, $v['code_id'])
    		->setCellValue('C'.$num, $v['name'])
    		->setCellValue('D'.$num, $stores_user)
    		->setCellValue('E'.$num, $get_cityname)
    		->setCellValue('F'.$num, date('Y-m-d H:i:s',$v['add_time']));
		$j++;
    	}
    	//$objPhpExcel->setActiveSheetIndex(0)->setCellValue('I2', $total);//单行信息
    	$title="无服务专员门店";
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

    //redis测试
    public function redis(){
        $redis = new CacheRedis(6);
        //echo $redis->lRem('123', 'asdfg123456' ,0);
        //$redis->lpush('123', 'asdfg123456');

        //$redis->rm('propertyMQ');
        echo 'cs';
        /*
        //修改的数据写入缓存
        $redis = new CacheRedis();
        $redis->lRem('propertyMQ', $data['id'] , 0);
        $redis->lpush('propertyMQ', $data['id']);
        */
        //$redis->handler->set('www','331111133345w64',10);
        //echo $redis->handler->get('www');

    }

	//导出excel*无服务专员门店 type = 2 AND
	public function out_excel_stores_baoshan(){

		$list = M('stores')->field('id,name,city_id,sid,add_time,address,mid,contact,contact_tel')->where('city_id in(select id from fph_city where id = 814 or spid RLIKE "[[:<:]]814[[:>:]]")')->select();


		$total=count($list);//总数

		Vendor("Classes.PHPExcel");
		Vendor("Classes.PHPExcel.php");

		//创建处理对象实例
		$objPhpExcel=new PHPExcel();
		$objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
		//设置表格的宽度  手动
		$objPhpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
		$objPhpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPhpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
		$objPhpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
		$objPhpExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
		$objPhpExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
		$objPhpExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
		$objPhpExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
		$objPhpExcel->getActiveSheet()->getColumnDimension('I')->setWidth(55);



		//设置标题
		$rowVal = array(0=>'编号',1=>'门店名称', 2=>'服务专员', 3=>'服务专员电话', 4=>'所属者', 5=>'所属者电话', 6=>'联系人', 7=>'联系人电话', 8=>'地址');
		foreach ($rowVal as $k=>$r){
			$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)
				->getFont()->setBold(true);//字体加粗
			$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
			getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
			$objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
		}

		$objPhpExcel->getActiveSheet()->setCellValue('A1', '编号');
		$objPhpExcel->getActiveSheet()->setCellValue('B1', '门店名称');
		$objPhpExcel->getActiveSheet()->setCellValue('C1', '服务专员');
		$objPhpExcel->getActiveSheet()->setCellValue('D1', '服务专员电话');
		$objPhpExcel->getActiveSheet()->setCellValue('E1', '所属者');
		$objPhpExcel->getActiveSheet()->setCellValue('F1', '所属者电话');
		$objPhpExcel->getActiveSheet()->setCellValue('G1', '联系人');
		$objPhpExcel->getActiveSheet()->setCellValue('H1', '联系人电话');
		$objPhpExcel->getActiveSheet()->setCellValue('I1', '地址');

		//设置当前的sheet索引 用于后续内容操作
		$objPhpExcel->setActiveSheetIndex(0);
		$objActSheet=$objPhpExcel->getActiveSheet();
		//设置当前活动的sheet的名称
		$title="上海宝山门店";
		$objActSheet->setTitle($title);
		//设置单元格内容
		$j=1;
		foreach($list as $k => $v)
		{

			$s_admin_name = M('admin')->field('username,mobile')->where(array('id'=>$v['sid']))->find();
			$m_admin_name = M('admin')->field('username,mobile')->where(array('id'=>$v['mid']))->find();

			//城市
			$city_spid = M('city')->where('id ='.$v['city_id'])->getField('spid');
			$spid_arr = explode('|', $city_spid.$v['city_id']);
			$get_cityname = '';
			foreach ($spid_arr as $key => $value) {
				$get_cityname .= M('city')->where('id ='.$value)->getField('name').' ';
			}

			$num=$k+2;
			$objPhpExcel->setActiveSheetIndex(0)
				//Excel的第A列，uid是你查出数组的键值，下面以此类推
				->setCellValue('A'.$num, $j)
				->setCellValue('B'.$num, $v['name'])
				->setCellValue('C'.$num, $s_admin_name['username'])
				->setCellValue('D'.$num, $s_admin_name['mobile'])
				->setCellValue('E'.$num, $m_admin_name['username'])
				->setCellValue('F'.$num, $m_admin_name['mobile'])
				->setCellValue('G'.$num, $v['contact'])
				->setCellValue('H'.$num, $v['contact_tel'])
				->setCellValue('I'.$num, $get_cityname.$v['address']);
			$j++;
		}
		//$objPhpExcel->setActiveSheetIndex(0)->setCellValue('G2', '2321321321');//单行信息
		$title="上海宝山门店";
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


	//导出excel*无服务专员门店 type = 2 AND
	public function out_excel_stores_jiading(){

		$list = M('stores')->field('id,name,city_id,sid,add_time,address,mid,contact,contact_tel')->where('city_id in(select id from fph_city where id = 815 or spid RLIKE "[[:<:]]815[[:>:]]")')->select();


		$total=count($list);//总数

		Vendor("Classes.PHPExcel");
		Vendor("Classes.PHPExcel.php");

		//创建处理对象实例
		$objPhpExcel=new PHPExcel();
		$objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
		//设置表格的宽度  手动
		$objPhpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
		$objPhpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPhpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
		$objPhpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
		$objPhpExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
		$objPhpExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
		$objPhpExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
		$objPhpExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
		$objPhpExcel->getActiveSheet()->getColumnDimension('I')->setWidth(55);



		//设置标题
		$rowVal = array(0=>'编号',1=>'门店名称', 2=>'服务专员', 3=>'服务专员电话', 4=>'所属者', 5=>'所属者电话', 6=>'联系人', 7=>'联系人电话', 8=>'地址');
		foreach ($rowVal as $k=>$r){
			$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)
				->getFont()->setBold(true);//字体加粗
			$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
			getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
			$objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
		}

		$objPhpExcel->getActiveSheet()->setCellValue('A1', '编号');
		$objPhpExcel->getActiveSheet()->setCellValue('B1', '门店名称');
		$objPhpExcel->getActiveSheet()->setCellValue('C1', '服务专员');
		$objPhpExcel->getActiveSheet()->setCellValue('D1', '服务专员电话');
		$objPhpExcel->getActiveSheet()->setCellValue('E1', '所属者');
		$objPhpExcel->getActiveSheet()->setCellValue('F1', '所属者电话');
		$objPhpExcel->getActiveSheet()->setCellValue('G1', '联系人');
		$objPhpExcel->getActiveSheet()->setCellValue('H1', '联系人电话');
		$objPhpExcel->getActiveSheet()->setCellValue('I1', '地址');

		//设置当前的sheet索引 用于后续内容操作
		$objPhpExcel->setActiveSheetIndex(0);
		$objActSheet=$objPhpExcel->getActiveSheet();
		//设置当前活动的sheet的名称
		$title="上海嘉定门店";
		$objActSheet->setTitle($title);
		//设置单元格内容
		$j=1;
		foreach($list as $k => $v)
		{

			$s_admin_name = M('admin')->field('username,mobile')->where(array('id'=>$v['sid']))->find();
			$m_admin_name = M('admin')->field('username,mobile')->where(array('id'=>$v['mid']))->find();

			//城市
			$city_spid = M('city')->where('id ='.$v['city_id'])->getField('spid');
			$spid_arr = explode('|', $city_spid.$v['city_id']);
			$get_cityname = '';
			foreach ($spid_arr as $key => $value) {
				$get_cityname .= M('city')->where('id ='.$value)->getField('name').' ';
			}

			$num=$k+2;
			$objPhpExcel->setActiveSheetIndex(0)
				//Excel的第A列，uid是你查出数组的键值，下面以此类推
				->setCellValue('A'.$num, $j)
				->setCellValue('B'.$num, $v['name'])
				->setCellValue('C'.$num, $s_admin_name['username'])
				->setCellValue('D'.$num, $s_admin_name['mobile'])
				->setCellValue('E'.$num, $m_admin_name['username'])
				->setCellValue('F'.$num, $m_admin_name['mobile'])
				->setCellValue('G'.$num, $v['contact'])
				->setCellValue('H'.$num, $v['contact_tel'])
				->setCellValue('I'.$num, $get_cityname.$v['address']);
			$j++;
		}
		//$objPhpExcel->setActiveSheetIndex(0)->setCellValue('G2', '2321321321');//单行信息
		$title="上海嘉定门店";
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


	//导出excel*无服务专员门店 type = 2 AND
	public function out_excel_stores_baoshan_contact(){

		$stores_list = M('stores')->field('id')->where('city_id in(select id from fph_city where id = 814 or spid RLIKE "[[:<:]]814[[:>:]]")')->select();
		$ids = '';
		foreach($stores_list as $val){
			$ids .= $val['id'].',';
		}
		$ids = substr($ids,0,strlen($ids)-1);

		$list = M('user')->field('username,mobile,stores_id')->where("stores_id in (".$ids.")")->select();

		$total=count($list);//总数

		Vendor("Classes.PHPExcel");
		Vendor("Classes.PHPExcel.php");

		//创建处理对象实例
		$objPhpExcel=new PHPExcel();
		$objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
		//设置表格的宽度  手动
		$objPhpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
		$objPhpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPhpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
		$objPhpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);



		//设置标题
		$rowVal = array(0=>'编号',1=>'门店名称', 2=>'经纪人', 3=>'经纪人电话');
		foreach ($rowVal as $k=>$r){
			$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)
				->getFont()->setBold(true);//字体加粗
			$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
			getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
			$objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
		}

		$objPhpExcel->getActiveSheet()->setCellValue('A1', '编号');
		$objPhpExcel->getActiveSheet()->setCellValue('B1', '门店名称');
		$objPhpExcel->getActiveSheet()->setCellValue('C1', '经纪人');
		$objPhpExcel->getActiveSheet()->setCellValue('D1', '经纪人电话');

		//设置当前的sheet索引 用于后续内容操作
		$objPhpExcel->setActiveSheetIndex(0);
		$objActSheet=$objPhpExcel->getActiveSheet();
		//设置当前活动的sheet的名称
		$title="上海宝山门店经纪人";
		$objActSheet->setTitle($title);
		//设置单元格内容
		$j=1;
		foreach($list as $k => $v)
		{

			$stores_info = M('stores')->field('name')->where(array('id'=>$v['stores_id']))->find();


			$num=$k+2;
			$objPhpExcel->setActiveSheetIndex(0)
				//Excel的第A列，uid是你查出数组的键值，下面以此类推
				->setCellValue('A'.$num, $j)
				->setCellValue('B'.$num, $stores_info['name'])
				->setCellValue('C'.$num, $v['username'])
				->setCellValue('D'.$num, $v['mobile']);
			$j++;
		}
		//$objPhpExcel->setActiveSheetIndex(0)->setCellValue('G2', '2321321321');//单行信息
		$title="上海宝山门店经纪人";
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

	//导出excel*无服务专员门店 type = 2 AND
	public function out_excel_stores_jiading_contact(){

		$stores_list = M('stores')->field('id')->where('city_id in(select id from fph_city where id = 815 or spid RLIKE "[[:<:]]815[[:>:]]")')->select();
		$ids = '';
		foreach($stores_list as $val){
			$ids .= $val['id'].',';
		}
		$ids = substr($ids,0,strlen($ids)-1);

		$list = M('user')->field('username,mobile,stores_id')->where("stores_id in (".$ids.")")->select();

		$total=count($list);//总数

		Vendor("Classes.PHPExcel");
		Vendor("Classes.PHPExcel.php");

		//创建处理对象实例
		$objPhpExcel=new PHPExcel();
		$objPhpExcel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);//设置单元格宽度
		//设置表格的宽度  手动
		$objPhpExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
		$objPhpExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		$objPhpExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
		$objPhpExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);



		//设置标题
		$rowVal = array(0=>'编号',1=>'门店名称', 2=>'经纪人', 3=>'经纪人电话');
		foreach ($rowVal as $k=>$r){
			$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)
				->getFont()->setBold(true);//字体加粗
			$objPhpExcel->getActiveSheet()->getStyleByColumnAndRow($k,1)->
			getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
			$objPhpExcel->getActiveSheet()->setCellValueByColumnAndRow($k,1,$r);
		}

		$objPhpExcel->getActiveSheet()->setCellValue('A1', '编号');
		$objPhpExcel->getActiveSheet()->setCellValue('B1', '门店名称');
		$objPhpExcel->getActiveSheet()->setCellValue('C1', '经纪人');
		$objPhpExcel->getActiveSheet()->setCellValue('D1', '经纪人电话');

		//设置当前的sheet索引 用于后续内容操作
		$objPhpExcel->setActiveSheetIndex(0);
		$objActSheet=$objPhpExcel->getActiveSheet();
		//设置当前活动的sheet的名称
		$title="上海嘉定门店经纪人";
		$objActSheet->setTitle($title);
		//设置单元格内容
		$j=1;
		foreach($list as $k => $v)
		{

			$stores_info = M('stores')->field('name')->where(array('id'=>$v['stores_id']))->find();


			$num=$k+2;
			$objPhpExcel->setActiveSheetIndex(0)
				//Excel的第A列，uid是你查出数组的键值，下面以此类推
				->setCellValue('A'.$num, $j)
				->setCellValue('B'.$num, $stores_info['name'])
				->setCellValue('C'.$num, $v['username'])
				->setCellValue('D'.$num, $v['mobile']);
			$j++;
		}
		//$objPhpExcel->setActiveSheetIndex(0)->setCellValue('G2', '2321321321');//单行信息
		$title="上海嘉定门店经纪人";
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


	//导入2015橙动天津活动需求验证码
	public function importExcelUser(){
		M('verify_code','fph_',C('DB_activity'));
		if(IS_POST){
			header("Content-Type: text/html; charset=UTF-8");
			echo "<a href=''.U('test/importExcelUser').''>继续导入</a><br/>";
			ini_set('memory_limit', '1024M');
			$tmp_name = $_FILES['inputExcel']['tmp_name'];
			$format   = explode('.', $_FILES['inputExcel']['name']);
			$pid = $this->_post('pid', 'intval');
			if($format[1] != 'xls'){
				$this->error('请上传格式为.xls的文件');exit;
			}
			if(!$pid){
				$this->error('参数出错');exit;
			}
			Vendor("Classes.PHPExcel1");
			Vendor("Classes.PHPExcel.php");
			Vendor("Classes.PHPExcel.IOFactory");
			Vendor("Classes.PHPExcel.Reader.Excel5");
			$objReader     = PHPExcel_IOFactory::createReader('Excel5');
			$objPHPExcel   = $objReader->load($tmp_name);
			$sheet         = $objPHPExcel->getSheet(0);
			$highestRow    = $sheet->getHighestRow(); //取得总行数
			//$highestColumn = $sheet->getHighestColumn(); //取得总列数

			for($j=1; $j<=$highestRow; $j++){
				$code = $objPHPExcel->getActiveSheet()->getCell("A".$j)->getValue();
				if($code){
					$data['pid']         = $pid;
					$data['verify_code'] = (string)$code;
					$data['add_time']    = time();
					$data['time_start']  = 1448812800;
					$data['time_end']    = 1449936000;
					$data['status']      = 0;
					M('verify_code')->add($data);
					echo $code.'导入成功 <br/>';
				}else{
					echo "<span style='color:#ff0000'>".$code."已经存在</span><br/>";
				}
			}
		}
		$this->display();
	}


	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
    

}