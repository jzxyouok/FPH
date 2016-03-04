<?php
define('MODE_NAME', 'cli');
/* 当前程序版本 */
define('PIN_VERSION', '3.0');
/* 当前程序Release */
define('PIN_RELEASE', '20140615');
/* 应用名称*/
define('APP_NAME', 'app');
/* 应用目录*/
define('APP_PATH', './app/');
/* 数据目录*/
define('PIN_DATA_PATH', './data/');
/* 扩展目录*/
define('EXTEND_PATH', APP_PATH . 'Extend/');
/* 配置文件目录*/
define('CONF_PATH', PIN_DATA_PATH . 'config/');
/* 数据目录*/
define('RUNTIME_PATH', PIN_DATA_PATH . 'runtime/');
/* HTML静态文件目录*/
define('HTML_PATH', PIN_DATA_PATH . 'html/');
/* DEBUG开关*/
define('APP_DEBUG', true);
/*获取项目根目录*/
define('BASE_PATH',str_replace('\\','/',realpath(dirname(__FILE__).'/'))."/");

$depr = '/';
$path   = isset($_SERVER['argv'][1])?$_SERVER['argv'][1]:'';
if(!empty($path)) {
    $params = explode($depr,trim($path,$depr));
}

!empty($params)?$_GET['g']=array_shift($params):"";
!empty($params)?$_GET['m']=array_shift($params):"";
!empty($params)?$_GET['a']=array_shift($params):"";

if(count($params)>1) {
// 解析剩余参数 并采用GET方式获取
    preg_replace('@(\w+),([^,\/]+)@e', '$_GET[\'\\1\']="\\2";', implode(',',$params));
}

require(BASE_PATH."_core/setup.php");