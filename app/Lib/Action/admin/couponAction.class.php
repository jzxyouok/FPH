<?php
class couponAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('coupon');
    }

    public function _before_index() {
        
        $res = M('property')->field('id,title')->select();
        $cate_list = array();
        foreach ($res as $val) {
            $cate_list[$val['id']] = $val['title'];
        }
        $this->assign('cate_list', $cate_list);

        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);
         
        //默认排序
        $this->sort = 'add_time';
        $this->order = 'DESC';
    }

    protected function _search() {
        $map = array();
        ($time_start = $this->_request('time_start', 'trim')) && $map['add_time'][] = array('egt', strtotime($time_start));
        ($time_end = $this->_request('time_end', 'trim')) && $map['add_time'][] = array('elt', strtotime($time_end)+(24*60*60-1));
        $status = $this->_request('status', 'trim');
        ($keyword = $this->_request('keyword', 'trim')) && $map['mobile'] = array('like', '%'.$keyword.'%');
		if($status!=''){
			$map['status'] = $status;
		}

        $this->assign('search', array(
            'time_start' => $time_start,
            'time_end' => $time_end,
            'status'  => $status,
            'keyword' => $keyword,
        ));
        return $map;
    }

    public function _before_add(){  
    	
        $author = $_COOKIE['admin']['username'];
        $this->assign('author',$author);

    }

    protected function _before_insert($data) {
        $mobile = $this->_post('mobile','trim');
        $money  = $this->_post('money','trim');
        $item     = $this->_post('item','trim');
        $mobile_count = $this->_mod->where("mobile='".$mobile."'")->count('id');
        if($mobile_count){
            $this->error('该经纪人已经添加过优惠券');
        }
        //生成优惠券
        $img = './data/upload/coupon/default.jpg';
        $font = './data/upload/coupon/msyh.ttf';
        $number = substr($mobile, -4);
        $this->generateImg ($img, $item, $money, $number, $font, $mobile);
        

        //上传图片
        if (!empty($_FILES['img']['name'])) {
            if($_FILES['img']['size']/1024 > C('pin_attr_allow_size'))
            {
                $this->error('图片超过尺寸限制');
            }
            $art_add_time = date('ym/d/');
            $result = $this->_upload($_FILES['img'], 'article/' . $art_add_time, array('width'=>'720', 'height'=>'540', 'remove_origin'=>true));
            if ($result['error']) {
                $this->error($result['info']);
            } else {
                $ext = array_pop(explode('.', $result['info'][0]['savename']));
                $data['img'] = $art_add_time .'/'. str_replace('.' . $ext, '_thumb.' . $ext, $result['info'][0]['savename']);
            }
        }
        return $data;
    }

    public function _before_edit(){  	   	
        $id = $this->_get('id','intval');
        $pid = $this->_mod->where(array('id'=>$id))->getfield('pid');
        $property_title = M('property')->where(array('id'=>$pid))->getfield('title');
         $this->assign('property_title',$property_title);
    }

    protected function _before_update($data) {
        $id     = $this->_post('id','inval');
        $mobile = $this->_post('mobile','trim');
        $money  = $this->_post('money','trim');
        $item   = $this->_post('item','trim');
        $mobile_count = $this->_mod->where("mobile='".$mobile."' AND id != ".$id."")->count('id');
        if($mobile_count){
            $this->error('该经纪人已经添加过优惠券');
        }
        //生成优惠券
        $img = './data/upload/coupon/default.jpg';
        $font = './data/upload/coupon/msyh.ttf';
        $number = substr($mobile, -4);
        $this->generateImg ($img, $item, $money, $number, $font, $mobile);
				
        if (!empty($_FILES['img']['name'])) {
            if($_FILES['img']['size']/1024 > C('pin_attr_allow_size'))
            {
                $this->error('图片超过尺寸限制');
            }
            $art_add_time = date('ym/d/');
            //删除原图
            $old_img = $this->_mod->where(array('id'=>$data['id']))->getField('img');
			$old_img = './data/upload/article/'. $old_img;//修改后
            //$old_img = $this->_get_imgdir() . $old_img;
            is_file($old_img) && @unlink($old_img);
            //上传新图
            $result = $this->_upload($_FILES['img'], 'article/' . $art_add_time, array('width'=>'720', 'height'=>'540', 'remove_origin'=>true));
            if ($result['error']) {
                $this->error($result['info']);
            } else {
                $ext = array_pop(explode('.', $result['info'][0]['savename']));
                $data['img'] = $art_add_time .'/'. str_replace('.' . $ext, '_thumb.' . $ext, $result['info'][0]['savename']);
            }
        } else {
            unset($data['img']);
        }

        return $data;
    }

    public function _before_delete(){
        $id = $this->_get('id','intval');
        $mobile = $this->_mod->where(array('id'=>$id))->getfield('mobile');
        unlink('./data/upload/coupon/'.$mobile.'.jpg');     
    }

    //验证手机号码
    public function ajax_check_mobile() {
        $mobile = $this->_get('mobile', 'trim');
        $id = $this->_get('id', 'intval');
        $user_info = M('user')->field('username')->where("mobile='".$mobile."'")->find();
        if($user_info){
             $this->ajaxReturn(1,'',$user_info);
        }else{
            $this->ajaxReturn(0, '该经纪人不存在,请先添加经纪人');        
        }
    }

     /**
     * ajax检测手机是否存在
     */
    public function ajax_check_number() {
        $mobile = $this->_get('mobile', 'trim');
        $id = $this->_get('id', 'intval');
        if ($this->_mod->mobile_exists($mobile,  $id)) {
            $this->ajaxReturn(0, '该编号已经存在');
        } else {
            $this->ajaxReturn();
        }
    }

    //搜索带看楼盘
    public function input_search(){
        $fph=C('DB_PREFIX');
        $title = $this->_post('title','trim');
        !$title && $this->ajaxReturn(0, '请输入要搜索的内容');
        if($title){
            $time=time();
            $list = M('property')->field("A.id,A.title")->table("{$fph}property AS A LEFT JOIN {$fph}property_cooperation AS B ON A.id=B.pid")->where('A.title like "%'.$title.'%" AND A.status=1 AND B.term_start<='.$time.' AND B.term_end >='.$time)->order('add_time DESC')->limit(50)->select();
            $str = "";
            $str .= "<ul class='popup'>";
                foreach($list as $val) {
                    $title = $val['title'];
                    $str .= "<li rel=".$val['id'].">".msubstr($title,0,35,'utf-8',true)."</li>";
                }
            $str .= '</ul>';
        }else{
         $str .= "<div>无数据,请检查输入的关键字</div>";
        }
        $this->ajaxReturn(1, '未知错误！', $str);
    }
	
    //生成图片
    public function generateImg($source, $text1, $text2, $text3, $font = 'msyh.ttf', $mobile) {  
        //$date = '' . date ( 'Ymd' ) . '/';  
        $img ='./data/upload/coupon/'.$mobile.'.jpg';  
        //if (file_exists ( './' . $img )) {  
        //    return $img;  
        //}  
          
        $main = imagecreatefromjpeg ( $source );  
          
        $width = imagesx ( $main );  
        $height = imagesy ( $main );  
          
        $target = imagecreatetruecolor ( $width, $height );  
          
        $white = imagecolorallocate ( $target, 255, 255, 255 );  
        imagefill ( $target, 0, 0, $white );  
          
        imagecopyresampled ( $target, $main, 0, 0, 0, 0, $width, $height, $width, $height );  
          
        $fontSize = 26;//18号字体
        $fontSize2 = 30;//34号字体
        $fontColor = imagecolorallocate ( $target, 255, 255, 255 );//字体的RGB颜色 
        $fontColor2 = imagecolorallocate ( $target, 179, 15, 47 );//字体的RGB颜色  
        $fontColor3 = imagecolorallocate ( $target, 229, 39, 77 );//字体的RGB颜色  
          
        $fontWidth = imagefontwidth ( $fontSize );  
        $fontHeight = imagefontheight ( $fontSize );

        $fontWidth2 = imagefontwidth ( $fontSize2 );  
        $fontHeight2 = imagefontheight ( $fontSize2 ); 
          
        $textWidth = $fontWidth * mb_strlen ( $text1 );  
        $x = ceil ( ($width - $textWidth) / 2.5 );//计算文字的水平位置   
        // $textHeight = $fontHeight;  
        // $y = ceil(($height - $textHeight) / 2);//计算文字的垂直位置            
        imagettftext ( $target, $fontSize, 0, $x, 160, $fontColor, $font, $text1 );  
        
        //第二个 
        $textWidth = $fontWidth2 * mb_strlen ( $text2 );  
        $x = ceil ( ($width - $textWidth) / 3 );
        imagettftext ( $target, $fontSize2, 0, $x, 240, $fontColor2, $font, $text2 );  
          
        $textWidth = $fontWidth * mb_strlen ( $text3 );  
        $x = ceil ( ($width - $textWidth) / 2 );           
        imagettftext ( $target, 18, 0, 238, 315, $fontColor3, $font, $text3 );//写文字，且水平居中  
 
        imagegif ( $target,$img, 0 );  
          
        imagedestroy ( $main );  
        imagedestroy ( $target );  
        //imagedestroy ( $child1 );  
        return $img;  
    } 
	
}