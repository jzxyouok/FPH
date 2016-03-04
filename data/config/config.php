<?php
return array(
    'APP_GROUP_LIST' => 'home,admin,weixin,download,m,topic,wxy,clientapp', //分组
    'DEFAULT_GROUP' => 'home', //默认分组
    'DEFAULT_MODULE' => 'index', //默认控制器
    'TAGLIB_PRE_LOAD' => 'pin', //自动加载标签
    'APP_AUTOLOAD_PATH' => '@.Pintag,@.Pinlib,@.ORG', //自动加载项目类库
    'TMPL_ACTION_SUCCESS' => 'public:success',
    'TMPL_ACTION_ERROR' => 'public:error',
    'DATA_CACHE_SUBDIR'=>true, //缓存文件夹
    'DATA_PATH_LEVEL'=>3, //缓存文件夹层级
    'LOAD_EXT_CONFIG' => 'url,db', //扩展配置
    'SHOW_PAGE_TRACE' => false,
	'OUTPUT_ENCODE' =>  false,
	'website' => 'http://yun.chenli.dev.com/',
    'fph' => 'fangpinhui.com',
    'd' => 'http://d.fangpinhui.com',
    'c' => 'http://yun.chenli.dev.com/index.php?g=clientapp&m=index&a=index',
    'C_IOS_URL' => 'https://appsto.re/cn/oWeG9.i',
    'C_ANDROID_URL' => 'http://www.fangpinhui.com/data/upload/app_download/fangpinhui_1.0.4_150818.apk',
	'img_url' => 'http://img.corp.com/',
	'AppID' => 'wx3ce1eceec205c6c4',
	'AppSecret' => '6a377f78c74e13ff5e4e425af7b11ecc',
	//'SHOW_RUN_TIME'		=> true, // 运行时间显示
  	
	'APP_SUB_DOMAIN_DEPLOY' => false,
  	'APP_SUB_DOMAIN_RULES'  =>array(
  		'yuna' => array("admin/"),
		'yun' => array("admin/"),
		'd' => array("download/"),
        'c' => array("clientapp/"),
		'myun' => array("m/"),
		'topic' => array("topic/"),
		'v' => array("wxy/"),
        //'hongbao' => array("hongbao/"),
  	),
		
	//OSS API
	'OSS_API_UPLOAD_IMAGE_URI'	=> 'http://192.168.1.24:8099/img/upload',
	'OSS_API_GET_IMAGE_URI'		=> 'http://192.168.1.24:8099/img/getImgUrl',
	'OSS_API_DELETE_IMAGE_URI'	=> 'http://192.168.1.24:8099/img/delImg',		
);