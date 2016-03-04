<?php
return array(
    'URL_MODEL' => '0',
    'URL_ROUTER_ON' => false,
	//'APP_SUB_DOMAIN_DEPLOY' => false,
	//楼盘图片缩略图
    'loupan_thumb_Width' => array('150', '300'),
    'loupan_thumb_Suffix' => array('_100x75', '_480x360'),
	//楼盘户型图片
    'house_thumb_Width' => array('800','640','280','100'),
    'house_thumb_Height' => array('600','480','210','75'),
    'house_thumb_Suffix' => array('_800x600','_640x480','_280x210','_100x75'),
    //楼盘图片管理
    'propertyimg' => array(
        '1'=>array('name'=>'效果图','file'=>'property/xiaoguo','width'=>'800,720,640,480,360,100','height'=>'600,540,480,360,240,75','suffix'=>'_800x600,_720x540,_640x480,_480x360,_360x240,_100x75'),
        '2'=>array('name'=>'规划图','file'=>'property/guihua','width'=>'800,720,640,480,360,100','height'=>'600,540,480,360,240,75','suffix'=>'_800x600,_720x540,_640x480,_480x360,_360x240,_100x75'),
        '3'=>array('name'=>'配套图','file'=>'property/peitao','width'=>'800,720,640,480,360,100','height'=>'600,540,480,360,240,75','suffix'=>'_800x600,_720x540,_640x480,_480x360,_360x240,_100x75'),
        '4'=>array('name'=>'实景图','file'=>'property/shijing','width'=>'800,720,640,480,360,100','height'=>'600,540,480,360,240,75','suffix'=>'_800x600,_720x540,_640x480,_480x360,_360x240,_100x75'),
        '5'=>array('name'=>'交通图','file'=>'property/jiaotong','width'=>'800,720,640,480,360,100','height'=>'600,540,480,360,240,75','suffix'=>'_800x600,_720x540,_640x480,_480x360,_360x240,_100x75'),
        '6'=>array('name'=>'样板房','file'=>'property/yangban','width'=>'800,720,640,480,360,100','height'=>'600,540,480,360,240,75','suffix'=>'_800x600,_720x540,_640x480,_480x360,_360x240,_100x75'),
    	),
    //楼盘焦点缩略图
    'setfocus'=>array('name'=>'焦点图','file'=>'property/thumbnail','width'=>'720,320,280,160','height'=>'540,240,210,120','suffix'=>'_app_list_thumb,_pc_thumb,_app_thumb,_weixin_thumb'),
    'BLUETOOTH_UUID' => '66688868-abcd-1234-8888-201403182015',
    'giftBagRedisTime' => 86400, //礼包的redis有效期只有 1天
);