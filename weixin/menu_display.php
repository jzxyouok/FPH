<?php
require "./conn.php";
//请注意菜单有24小时缓存
//取消关注  然后再关注  方便看到修改后的效果
$appid = "wx3ce1eceec205c6c4";
$appsecret = "6a377f78c74e13ff5e4e425af7b11ecc";

function httpGet($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);

    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
  }
function new_getAccessToken() {
    // access_token 应该全局存储与更新，我写入了文件中  这里与多客服的json是同一文件
    $data = json_decode(file_get_contents("../data/runtime/Data/access_token.json"));
    if ($data->expire_time < time()) {
      $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx3ce1eceec205c6c4&secret=6a377f78c74e13ff5e4e425af7b11ecc";
      $res = json_decode(httpGet($url));
      $access_token = $res->access_token;
      if ($access_token) {
        $data->expire_time = time() + 7000;
        $data->access_token = $access_token;
        $fp = fopen("../data/runtime/Data/access_token.json", "w");
        fwrite($fp, json_encode($data));
        fclose($fp);
      }
    } else {
      $access_token = $data->access_token;
    }
    return $access_token;
  }

$access_token=new_getAccessToken($appid,$appsecret);
define("ACCESS_TOKEN", $access_token);

//创建菜单
/*
function createMenu($data){

 $ch = curl_init();
 curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".ACCESS_TOKEN);
 curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
 curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
 curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
 curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
 curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 $tmpInfo = curl_exec($ch);
 if (curl_errno($ch)) {
  return curl_error($ch);
 }
 curl_close($ch);
 return $tmpInfo;
 
}
*/
function unicode2utf8($str) { 
// unicode编码转化，用于显示emoji表情 
	$str = '{"result_str":"'.$str.'"}'; 
// 组合成json格式      
	$strarray = json_decode ($str,true ); 
// json转换为数组，利用 JSON 对 \uXXXX 的支持来把转义符恢复为 Unicode 字符 
	return $strarray ['result_str']; 
}

function dataPost($post_string, $url) {//POST方式提交数据

 $context = array ('http' => array ('method' => "POST", 'header' => "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) \r\n Accept: */*", 'content' => $post_string ) );

 $stream_context = stream_context_create ( $context );

 $data = file_get_contents ( $url, FALSE, $stream_context );

 return $data;

}

$data = ' {';
     $data .= '"button":[';
		 $sql="SELECT id,menu_type,name,link FROM fph_weixin_cate WHERE pid=0 AND status=1 ORDER BY ordid ASC LIMIT 0,3";
		 $res=mysql_query($sql);
		 while($row=mysql_fetch_array($res,MYSQL_BOTH)){
			
			//统计是否有二级分类
			$sql_con="SELECT id FROM fph_weixin_cate WHERE pid=".$row['id']." AND status=1";
			$res_con=mysql_query($sql_con);
			$count_con = mysql_num_rows($res_con);

			if($row['menu_type']==1){
				if($count_con){
					$data .= '{';
						//$data .= '"name":"'.unicode2utf8('\ue11d').$row['name'].'",';
						$data .= '"name":"'.$row['name'].'",';
						$data .= '"sub_button":[';

							$sql2="SELECT menu_type,name,link FROM fph_weixin_cate WHERE pid=".$row['id']." AND status=1 ORDER BY ordid ASC LIMIT 0,5";
							$res2=mysql_query($sql2);
							$count = mysql_num_rows($res2);
							$i=1;
							while($row2=mysql_fetch_array($res2,MYSQL_BOTH)){
								$data .= '{';
								if($row2['menu_type']==1){
									$data .= '"type":"view",';
									$data .= '"name":"'.$row2['name'].'",';
									$data .= '"url":"'.$row2['link'].'"';
								}else{
									$data .= '"type":"click",';
									$data .= '"name":"'.$row2['name'].'",';
									$data .= '"key":"'.$row2['name'].'"';
								}
								if($count==$i){
									$data .= '}';
								}else{
									$data .= '},';
								}
							$i++;
							}
							
						$data .= ']';
					$data .= '},';
				}else{
					$data .= '{';
					  $data .= '"type":"view",';
					  $data .= '"name":"'.$row['name'].'",';
					  $data .= '"url":"'.$row['link'].'"';
				    $data .= '},';
				}
			}else{
				$data .= '{';
				$data .= '"type":"click",';
				$data .= '"name":"'.$row['name'].'",';
				$data .= '"key":"'.$row['name'].'"';
				$data .= '},';
			}
		 }
		 
	

	 /*
	 $sql="SELECT name FROM fph_weixin_cate WHERE pid=0";
	 $res=mysql_query($sql);
	 while($row=mysql_fetch_array($res,MYSQL_BOTH)){
		  $data .= '{';
			  $data .= '"type":"view",';
			  $data .= '"name":"'.$row['name'].'",';
			  $data .= '"url":"http://www.carccj.com/chenya/thinksns3.1/"';
		   $data .= '},';
	 }*/
	   
	   /*
	   $data .= '{';
          $data .= '"type":"view",';
          $data .= '"name":"abcdef",';
          $data .= '"url":"http://www.carccj.com/chenya/thinksns3.1/"';
       $data .= '},';
	   */
	   
	   
	   
	   $data .= ']';
 $data .= '}';
//echo createMenu($data);//创建菜单
print_r($data);
$menuPostUrl = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".ACCESS_TOKEN;
echo dataPost($data,$menuPostUrl);
?>