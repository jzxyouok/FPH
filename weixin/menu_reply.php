<?php
/*
<xml>
<ToUserName><![CDATA[toUser]]></ToUserName>
<FromUserName><![CDATA[FromUser]]></FromUserName>
<CreateTime>123456789</CreateTime>
<MsgType><![CDATA[event]]></MsgType>
<Event><![CDATA[CLICK]]></Event>
<EventKey><![CDATA[EVENTKEY]]></EventKey>
</xml>

*/
function receiveMenu($postObj){
			$key = trim($postObj->EventKey);
            switch ($key)
            {
                case "笑话":
               		$result = joker($postObj);      
                    break;
                case "快递查询":
                	$result = express($postObj);       
                    break;
                case "公交路线":
                	$result = bus($postObj,$content);   
                    break;
                case "周公解梦":
                	$result = dream($postObj,$content);     
                    break;
                case "历史今天":
                	$result = history($postObj); 
                    break;
                case "股票分析":
                	$result = stock($postObj); 
                    break;
                case "城市团购":
                	$result = group($postObj); 
                    break;
                case "自驾路线":
                	$result = myselfdrive($postObj); 
                    break;
                
            }
            return $result;
}
function joker($postObj,$content){
            $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[text]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                        </xml>";
    		$url     = "http://apix.sinaapp.com/joke/?appkey=".$postObj->ToUserName;   //随便传一个参数   //发送的是笑话
            $output  = file_get_contents($url);
            $content = json_decode($output, true);
            $result  = sprintf($textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), $content);
            return $result;
}

function express($postObj){
            $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[news]]></MsgType>
                            <ArticleCount>1</ArticleCount>
                            <Articles>
                            <item>
                            <Title><![CDATA[%s]]></Title>
                            <Description><![CDATA[%s]]></Description>
                            </item>
                            </Articles>
                        </xml>";
    		$title="快递单号查询";
    		$content="请发送： 快递单号 #键结束\n例如： 3143490877#";
            $result = sprintf($textTpl, $postObj->FromUserName, $postObj->ToUserName, time(),$title, $content);
            return $result;
}

function history($postObj){
   			$textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[news]]></MsgType>
                            <ArticleCount>1</ArticleCount>
                            <Articles>
                            <item>
                            <Title><![CDATA[%s]]></Title>
                            <Description><![CDATA[%s]]></Description>
                            </item>
                            </Articles>
                        </xml>";
            $url="http://api100.duapp.com/history/?appkey=jglfdjgl";
    		$output  = file_get_contents($url);
            $content = json_decode($output, true);
    		$title="历史上的今天";
            $result = sprintf($textTpl, $postObj->FromUserName, $postObj->ToUserName, time(),$title, $content);
            return $result;
			
}


function bus($postObj,$content){
   			$textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[news]]></MsgType>
                            <ArticleCount>1</ArticleCount>
                            <Articles>
                            <item>
                            <Title><![CDATA[%s]]></Title>
                            <Description><![CDATA[%s]]></Description>
                            </item>
                            </Articles>
                        </xml>";
            if(empty($content)){
                    $content="请发送： 城市名称&公交线路\n例如： 蚌埠&302";
                	$title="公交路线查询";
            }else{
                	$keyword=$postObj->Content;
                	$k      = explode("&",$keyword); 
            	    $title  = $k[0]." " .$k[1]."路 公交车路线";
            }
            $result = sprintf($textTpl, $postObj->FromUserName, $postObj->ToUserName, time(),$title, $content);
            return $result;
			
}



function dream($postObj,$content){
			$textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[news]]></MsgType>
                            <ArticleCount>1</ArticleCount>
                            <Articles>
                            <item>
                            <Title><![CDATA[%s]]></Title>
                            <Description><![CDATA[%s]]></Description>
                            </item>
                            </Articles>
                        </xml>";
			if(empty($content)){
				$title   ="周公解梦";
    		    $content ="请发送： 我梦见+内容\n例如： 我梦见写作业";
			}else{
				$title   =$postObj->Content." -> "."解梦结果";
			}
            $result  = sprintf($textTpl, $postObj->FromUserName, $postObj->ToUserName, time(),$title, $content);
            return $result;
}



function stock($postObj){
			$textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[text]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                        </xml>";
    		
            $content = "请发送股票代码，例如：股票代码600575";
            $result  = sprintf($textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), $content);
            return $result;
}

function stock_result($postObj,$content){
   			$textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[news]]></MsgType>
                            <ArticleCount>1</ArticleCount>
                            <Articles>
                            <item>
                            <Title><![CDATA[%s]]></Title>
                            <Description><![CDATA[%s]]></Description>
                            </item>
                            </Articles>
                        </xml>";
            	    $title  = $postObj->Content;
            
            $result = sprintf($textTpl, $postObj->FromUserName, $postObj->ToUserName, time(),$title, $content);
            return $result;
			
}



function group($postObj,$content){
		$textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[news]]></MsgType>
                            <ArticleCount>1</ArticleCount>
                            <Articles>
                            <item>
                            <Title><![CDATA[%s]]></Title>
                            <Description><![CDATA[%s]]></Description>
                            </item>
                            </Articles>
                        </xml>";
             if(empty($content)){
                 $content="请发送： 城市名称团购商户名/商品名/地址等\n例如： 杭州团购西湖区\n           蚌埠团购美食\n           合肥团购庐阳区";
                 $title="城市团购查询";
            }else{
                	$keyword=$postObj->Content;
                	$k      = explode("团购",$keyword); 
            	    $title = "团购-".$k[0]."-" .$k[1];
                 	
            }
            $result = sprintf($textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), $title,$content);
            return $result;
}




function group_result($postObj,$content){
		if(!is_array($content))
            return;

        $itemTpl = "    <item>
                            <Title><![CDATA[%s]]></Title>
                            <Description><![CDATA[%s]]></Description>
                            <PicUrl><![CDATA[%s]]></PicUrl>
                            <Url><![CDATA[%s]]></Url>
                        </item>
                    ";
        $item_str = "";
        foreach ($content as $item){
            if(empty($item['PicUrl'])){
                $item['Title']= $item['Title']."                                         广告联系QQ:494595280";
            	$item['PicUrl']="http://www.carccj.com/chenya/weixin/pic/101.jpg";
            }
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
            }
    	

        $newsTpl = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[news]]></MsgType>
                        <Content><![CDATA[]]></Content>
                        <ArticleCount>%s</ArticleCount>
                        <Articles>
                        $item_str
                        </Articles>
                    </xml>";

        $resultStr = sprintf($newsTpl, $postObj->FromUserName, $postObj->ToUserName, time(), count($content));
        return $resultStr;
}





    
function myselfdrive($postObj,$content){
		$textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[news]]></MsgType>
                            <ArticleCount>1</ArticleCount>
                            <Articles>
                            <item>
                            <Title><![CDATA[%s]]></Title>
                            <Description><![CDATA[%s]]></Description>
                            </item>
                            </Articles>
                        </xml>";
             if(empty($content)){
                 $content="请回复： 起点城市*起点地名*终点城市*终点地名\n例如： 北京*清华大学*北京*天安门\n           六安*皖西学院*六安*六安职业技术学院\n           蚌埠*安徽财经大学*蚌埠*蚌埠医学院";
                 $title="城市自驾路线查询";
            }else{
                	$keyword=$postObj->Content;
                	$k      = explode("*",$keyword); 
            	    $k[0]= trim($k[0]);  //起点城市
                    $k[1]= trim($k[1]);  //起点名称
                    $k[2]= trim($k[2]);  //终点城市
            		$k[3]= trim($k[3]);  //终点名称
                 	$title=$k[0].$k[1]." -> ".$k[2].$k[3];
                 	
            }
            $result = sprintf($textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), $title,$content);
            return $result;
}

?>














