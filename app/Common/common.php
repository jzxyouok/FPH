<?php
//获取第一张图片-缩略图
function ex_pic($picarr,$suffix){
	$pic = explode(',',$picarr);
	$a = explode(".",$pic[0]);
	return __ROOT__.'/data/upload/property/'.$a[0].$suffix.'.'.$a[1];
}

function city_name($city_id){
	$pic = explode(',',$city_id);
	foreach ($pic as $key=>$val){
		$city_name .= '&nbsp;&nbsp;'.M('city')->where('id='.$val)->getField('name')  ;
	}
	return $city_name;
}

//根据地方id 获取其上级区域
function getareanamearr($id){
        $return = M('city')->where(array('id'=>$id))->getfield('name');
        $pid = M('city')->where(array('id'=>$id))->field('pid,name')->find();
        if($pid['pid'] != 0){
            $fid =  M('city')->where(array('id'=>$pid['pid']))->field('pid,name')->find();           
        }
        if($fid['pid'] != 0){
            $fid1 =  M('city')->where(array('id'=>$fid['pid']))->field('pid,name')->find();           
        }
        if(!empty($fid)){
                $return = $fid1['name'].'-'.$return;
        }  
         if(!empty($pid)){
                 $return = $fid['name'].'-'.$return;
        }
        
             
        return $return;
   }    


//发送短信
function Post($curlPost,$url){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
		$return_str = curl_exec($curl);
		curl_close($curl);
		return $return_str;
}
//发送短信
function xml_to_array($xml){
	$reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
	if(preg_match_all($reg, $xml, $matches)){
		$count = count($matches[0]);
		for($i = 0; $i < $count; $i++){
		$subxml= $matches[2][$i];
		$key = $matches[1][$i];
			if(preg_match( $reg, $subxml )){
				$arr[$key] = xml_to_array( $subxml );
			}else{
				$arr[$key] = $subxml;
			}
		}
	}
	return $arr;
}
//获取随机码
function random($length = 6 , $numeric = 0) {
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	if($numeric) {
		$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
	} else {
		$hash = '';
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
	}
	return $hash;
}

//验证手机
function checkMobile($mobilephone){ 
	$exp = "/^13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[0123456789]{1}[0-9]{8}$|14[0-9]{1}[0-9]{8}$|17[0123456789]{1}[0-9]{8}$/"; 
	if(preg_match($exp,$mobilephone)){ 
		return true; 
	}else{ 
		return false; 
	} 
}
//验证用户名//^([\u4E00-\uFA29]|[\uE7C7-\uE7F3]|[a-zA-Z])*$
function checkusername($username){
	//$exp = '/^(?!\_+)[a-z0-9\x80-\xff\_]{3,16}$/'; 
	$exp = '/^(?!\_+)[a-zA-Z\x80-\xff\_]{2,16}$/';
	if(preg_match($exp,$username)){ 
		return true; 
	}else{ 
		return false; 
	} 
}

//php转换标签
function htmltag($val){
    return htmlspecialchars($val);
}

//获取客户端IP
function get_ip(){
	$unknown = 'unknown';
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
   $ip = preg_match("/[\d\.]{7,15}/", $ip, $matches) ? $matches[0] : $unknown; 
	if (false !== strpos($ip, ',')){ $ip = reset(explode(',', $ip)); }
	return $ip;
}

/**
关于中奖概率算法
**/
function getRand($proArr) { 
    $result = ''; 
    //概率数组的总概率精度 
    $proSum = array_sum($proArr); 
    //概率数组循环 
    foreach ($proArr as $key => $proCur) { 
        $randNum = mt_rand(1, $proSum);             //抽取随机数
        if ($randNum <= $proCur) { 
            $result = $key;                         //得出结果
            break; 
        } else { 
            $proSum -= $proCur;                     
        } 
    } 
    unset ($proArr); 
    return $result; 
}

//手机号码归属地
function get_city($mobile){
    $url = "http://virtual.paipai.com/extinfo/GetMobileProductInfo?mobile=".$mobile."&amount=10000&callname=getPhoneNumInfoExtCallback";
    $ch = curl_init($url) ;  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回  
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
    $output = curl_exec($ch);
    $arr = explode("'", $output);
    $city_name = iconv("gb2312","utf-8",$arr[15]);
    return $city_name.'市';
}

//微信获取信息
function httppost($url){
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_HEADER,0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    $res = curl_exec($ch);
    curl_close($ch);
    $json_obj = json_decode($res,true); 
    return $json_obj;
}

//获取access_token
function access_token($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    $jsoninfo = json_decode($output, true);
    //print_r($access_token);exit;
    return $jsoninfo;
}

function addslashes_deep($value) {
    $value = is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
    return $value;
}

function stripslashes_deep($value) {
    if (is_array($value)) {
        $value = array_map('stripslashes_deep', $value);
    } elseif (is_object($value)) {
        $vars = get_object_vars($value);
        foreach ($vars as $key => $data) {
            $value->{$key} = stripslashes_deep($data);
        }
    } else {
        $value = stripslashes($value);
    }

    return $value;
}

function todaytime() {
    return mktime(0, 0, 0, date('m'), date('d'), date('Y'));
}

/**
 * 友好时间
 */
function fdate($time) {
    if (!$time)
        return false;
    $fdate = '';
    $d = time() - intval($time);
    $ld = $time - mktime(0, 0, 0, 0, 0, date('Y')); //年
    $md = $time - mktime(0, 0, 0, date('m'), 0, date('Y')); //月
    $byd = $time - mktime(0, 0, 0, date('m'), date('d') - 2, date('Y')); //前天
    $yd = $time - mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')); //昨天
    $dd = $time - mktime(0, 0, 0, date('m'), date('d'), date('Y')); //今天
    $td = $time - mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')); //明天
    $atd = $time - mktime(0, 0, 0, date('m'), date('d') + 2, date('Y')); //后天
    if ($d == 0) {
        $fdate = '刚刚';
    } else {
        switch ($d) {
            case $d < $atd:
                $fdate = date('Y年m月d日', $time);
                break;
            case $d < $td:
                $fdate = '后天' . date('H:i', $time);
                break;
            case $d < 0:
                $fdate = '明天' . date('H:i', $time);
                break;
            case $d < 60:
                $fdate = $d . '秒前';
                break;
            case $d < 3600:
                $fdate = floor($d / 60) . '分钟前';
                break;
            case $d < $dd:
                $fdate = floor($d / 3600) . '小时前';
                break;
            case $d < $yd:
                $fdate = '昨天' . date('H:i', $time);
                break;
            case $d < $byd:
                $fdate = '前天' . date('H:i', $time);
                break;
            case $d < $md:
                $fdate = date('m月d H:i', $time);
                break;
            case $d < $ld:
                $fdate = date('m月d', $time);
                break;
            default:
                $fdate = date('Y年m月d日', $time);
                break;
        }
    }
    return $fdate;
}

/**
 * 获取用户头像
 */
function avatar($uid, $size) {
    $avatar_size = explode(',', C('pin_avatar_size'));
    $size = in_array($size, $avatar_size) ? $size : '100';
    $avatar_dir = avatar_dir($uid);
    $avatar_file = $avatar_dir . md5($uid) . "_{$size}.jpg";
    if (!is_file(C('pin_attach_path') . 'avatar/' . $avatar_file)) {
        $avatar_file = "default_{$size}.jpg";
    }
    return __ROOT__ . '/' . C('pin_attach_path') . 'avatar/' . $avatar_file;
}

/**
 * 获取用户头像-修改
 */
function avatar_edit($uid, $size) {
    $avatar_size = explode(',', C('pin_avatar_size'));
    $size = in_array($size, $avatar_size) ? $size : '100';
    $avatar_dir = avatar_dir($uid);
    $avatar_file = $avatar_dir . md5($uid) . "_{$size}.jpg";
    if (!is_file(C('pin_attach_path') . 'avatar/' . $avatar_file)) {
        $avatar_file = "photo_{$size}.jpg";
    }
    return __ROOT__ . '/' . C('pin_attach_path') . 'avatar/' . $avatar_file;
}
/**
 * app获取用户头像
 */
function avatar_app($uid, $size) {
	$avatar_size = explode(',', C('pin_avatar_size'));
	$size = in_array($size, $avatar_size) ? $size : '100';
	$avatar_dir = avatar_dir($uid);
	$avatar_file = $avatar_dir . md5($uid) . "_{$size}.jpg";
	if (!is_file(C('pin_attach_path') . 'avatar/' . $avatar_file)) {
		$avatar_file = "app_{$size}.png";
	}
	return __ROOT__ . '/' . C('pin_attach_path') . 'avatar/' . $avatar_file;
}


function avatar_dir($uid) {
    $uid = abs(intval($uid));
    $suid = sprintf("%09d", $uid);
    $dir1 = substr($suid, 0, 3);
    $dir2 = substr($suid, 3, 2);
    $dir3 = substr($suid, 5, 2);
    return $dir1 . '/' . $dir2 . '/' . $dir3 . '/';
}

function attach($attach, $type) {
    if (false === strpos($attach, 'http://')) {
        //本地附件
        return __ROOT__ . '/' . C('pin_attach_path') . $type . '/' . $attach;
        //远程附件
        //todo...
    } else {
        //URL链接
        return $attach;
    }
}

/*
 * 获取缩略图
 */
function get_thumb($img, $suffix = '_thumb') {
    if (false === strpos($img, 'http://')) {
        $ext = array_pop(explode('.', $img));
        $thumb = str_replace('.' . $ext, $suffix . '.' . $ext, $img);
    } else {
        if (false !== strpos($img, 'taobaocdn.com') || false !== strpos($img, 'taobao.com')) {
            switch ($suffix) {
                case '_s':
                    $thumb = $img . '_100x100.jpg';
                    break;
                case '_m':
                    $thumb = $img . '_210x1000.jpg';
                    break;
                case '_b':
                    $thumb = $img . '_480x480.jpg';
                    break;
            }
        }
    }
    return $thumb;
}

/*
 * 获取缩略图
 */
function get_fdfs_image($img, $suffix = '_thumb') {
	//$ext = array_pop(explode('.', $img));
	$ext_arr = explode('.', $img);
	$ext     = array_pop($ext_arr);
	$thumb   = str_replace('.' . $ext, $suffix . '.' . $ext, $img);
    return 'http://image01.fangpinhui.com/'.$thumb;
}

/**
 * 对象转换成数组
 */
function object_to_array($obj) {
    $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
    foreach ($_arr as $key => $val) {
        $val = (is_array($val) || is_object($val)) ? object_to_array($val) : $val;
        $arr[$key] = $val;
    }
    return $arr;
}

/**
 * 高德坐标转换成 百度坐标
 * @param $bd_lon
 * @param $bd_lat
 * @return mixed
 */
function bd_encrypt($gg_lon, $gg_lat)
{
	$x_pi = 3.14159265358979324 * 3000.0 / 180.0;
	$x = $gg_lon;
	$y = $gg_lat;
	$z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
	$theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
	$data[0] = $z * cos($theta) + 0.0065;
	$data[1] = $z * sin($theta) + 0.006;
	return $data;
}

/**
 * 百度坐标转换成 高德坐标
 * @param $bd_lon
 * @param $bd_lat
 * @return mixed
 */
function bd_decrypt($bd_lon, $bd_lat)
{
	$x_pi = 3.14159265358979324 * 3000.0 / 180.0;
	$x = $bd_lon - 0.0065;
	$y = $bd_lat - 0.006;
	$z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
	$theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
	$data[0] = $z * cos($theta);
	$data[1] = $z * sin($theta);
	return $data;
}

   /** 
* @desc 根据两点间的经纬度计算距离 
* @param float $lat 纬度值 
* @param float $lng 经度值 
*/
function getDistance($lat1, $lng1, $lat2, $lng2){
//     $earthRadius = 6367000; //approximate radius of earth in meters 
	$earthRadius = 6371000; // 修改于16.01.21.经确认以前只再微信设置驻守楼盘的列表中使用.
	
    /* 
    Convert these degrees to radians 
    to work with the formula 
    */
    
    $lat1 = ($lat1 * pi() ) / 180; 
    $lng1 = ($lng1 * pi() ) / 180; 
    $lat2 = ($lat2 * pi() ) / 180; 
    $lng2 = ($lng2 * pi() ) / 180; 
    /* 
    Using the 
    Haversine formula 
     
    http://en.wikipedia.org/wiki/Haversine_formula 
     
    calculate the distance 
    */
    $calcLongitude = $lng2 - $lng1; 
    $calcLatitude = $lat2 - $lat1; 
    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2); 
    $stepTwo = 2 * asin(min(1, sqrt($stepOne))); 
    $calculatedDistance = $earthRadius * $stepTwo; 
     
    return round($calculatedDistance); 
}

/**
 +----------------------------------------------------------
 * 字符串截取，支持中文和其他编码
 +----------------------------------------------------------
 * @static
 * @access public
 +----------------------------------------------------------
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
	 $len = strlen($str);
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
	if(($len/3) > $length){
		return $suffix ? $slice.'...' : $slice;
	}else{
		return $slice;
	}


}

//array_column() 返回输入数组中某个单一列的值
function i_array_column($input, $columnKey, $indexKey=null){
    if(!function_exists('array_column')){
        $columnKeyIsNumber  = (is_numeric($columnKey))?true:false;
        $indexKeyIsNull            = (is_null($indexKey))?true :false;
        $indexKeyIsNumber     = (is_numeric($indexKey))?true:false;
        $result                         = array();
        foreach((array)$input as $key=>$row){
            if($columnKeyIsNumber){
                $tmp= array_slice($row, $columnKey, 1);
                $tmp= (is_array($tmp) && !empty($tmp))?current($tmp):null;
            }else{
                $tmp= isset($row[$columnKey])?$row[$columnKey]:null;
            }
            if(!$indexKeyIsNull){
                if($indexKeyIsNumber){
                    $key = array_slice($row, $indexKey, 1);
                    $key = (is_array($key) && !empty($key))?current($key):null;
                    $key = is_null($key)?0:$key;
                }else{
                    $key = isset($row[$indexKey])?$row[$indexKey]:0;
                }
            }
            $result[$key] = $tmp;
        }
        return $result;
    }else{
        return array_column($input, $columnKey, $indexKey);
    }
}

/**
 * 图片上传oss
 * @param $module	参数只能为property或user或comment
 * @param $type	当module参数为user或comment时type可传参数1:yun,2:b端,3:c端,当module参数为property时type可传参数1:效果图,2:效果图,3:配套图,4:实景图,5:交通图,6:样板图,7:户型图,8:领路费
 * @param $image
 */
function oss_image_upload( $module, $type, $image )
{	
	$result				= oss_api_request('OSS_API_UPLOAD_IMAGE_URI',array(
			'module'	=> $module,
			'type'		=> $type,
			'files'		=> "@{$image['tmp_name']};filename={$image['name']};type={$image['type']}",
	));
	if($result['status'] == TRUE && !isset( $result['decoded_response']['data']['imgUrl'] ))
	{
		$result['status']	= FALSE;
		$result['message']	= '上传失败.接口未返回上传后图片路径.';
	}	
	return $result;
}

/**
 * 获取图片url
 */
function oss_get_image_url( $path, $module, $imgMM )
{
	$result				= oss_api_request('OSS_API_GET_IMAGE_URI',array(
			'module'	=> $module,
			'imgMM'		=> $imgMM,
			'url'		=> $path,
	));
	if($result['status'] == TRUE && !isset( $result['decoded_response']['data']['imgUrl'] ))
	{
		$result['status']	= FALSE;
		$result['message']	= '读取失败.接口未返回图片url.';
	}
	return $result;
}

/**
　* 删除图片，支持多个一起删除，用逗号分割
 */
function oss_del_image_url( $module, $image )
{
	return oss_api_request('OSS_API_GET_IMAGE_URI',array(
			'module'	=> $module,
			'urls'		=> $image,
	));
}

/**
 * 请求oss api 接口
 * @param $type OSS_API_UPLOAD_IMAGE_URI OSS_API_GET_IMAGE_URI OSS_API_DELETE_IMAGE_URI
 */
function oss_api_request( $type, $data )
{
	$result	= array(
			'status'			=> FALSE,			//　上传失败
			'message'			=> '未知错误.',		// 描述信息
			'request'			=> $data,			//　请求参数
			'curl_info'			=> array(),			// curl 信息
			'decoded_response'	=> array(),			// 解析后的返回信息
			'response'			=> '',				//　api接口端返回信息
	);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, C($type) );
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	$response			= curl_exec($ch);
	$curl_errorno		= curl_errno($ch);
	$curl_info			= curl_getinfo($ch);
	$curl_http_code		= curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$curl_errormsg		= curl_error($ch);
	curl_close($ch);
	
	$result['curl_info']		= $curl_info;
	$result['response']			= $response;
	$decoded_response			= json_decode( $response, TRUE );
	$result['decoded_response']	= $decoded_response;
	
	if(!isset( $decoded_response['code'] ) || !isset( $decoded_response['msg'] ))
	{
		$result['status']		= FALSE;
		$result['message']		= in_array($curl_http_code, array(200, 201, 204, 206)) ? 'API接口返回数据格式异常.' : "CURL ERROR:[http code:{$curl_http_code}][error no:{$curl_errorno}][error msg:{$curl_errormsg}]";
	}
	
	if( $decoded_response['code'] != '1000' )
	{
		$result['status']		= FALSE;
		$result['message']		= empty( $decoded_response['msg'] ) ? "请求失败" : $decoded_response['msg'] ;
	}
	
	if( $decoded_response['code'] == '1000')
	{
		$result['status']		= TRUE;
		$result['message']		= empty( $decoded_response['msg'] ) ? "请求成功" : $decoded_response['msg'] ;
	}
	
	return $result;
}