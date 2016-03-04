<?php
// +----------------------------------------------------------------------
// | ThinkPHP
// +----------------------------------------------------------------------
// | Licensed ( http://www.fangpinhui.com)
// +----------------------------------------------------------------------
// | Author: CH - L <chenlix@126.com>
// +----------------------------------------------------------------------
// $Id: CacheRedis.class.php 2728 2015-07-20

/**
+-------------------------------------
 * CacheRedis缓存驱动类
 * 要求安装phpredis扩展：https://github.com/owlient/phpredis
+-------------------------------------
 */
class CacheRedis {

    /**
    +----------------------------------------------------------
     * 架构函数
    +----------------------------------------------------------
     * @access public
    +----------------------------------------------------------
     */
    public function __construct($dataBase=0, $options='') {
        if ( !extension_loaded('redis') ) {
            throw_exception(L('_NOT_SUPPERT_').':redis');
        }
        if(empty($options)) {
            $options = array (
                'host'  => C('REDIS_HOST') ? C('REDIS_HOST') : '127.0.0.1',
                'port'  => C('REDIS_PORT') ? C('REDIS_PORT') : 6379,
                'timeout' => C('DATA_CACHE_TIMEOUT') ? C('DATA_CACHE_TIMEOUT') : false,
                'persistent' => false,
                'expire'   => C('DATA_CACHE_TIME'),
                'length'   => 0,
            );
        }
        $this->options =  $options;
        $func = $options['persistent'] ? 'pconnect' : 'connect';
        $this->handler  = new Redis;
        $this->connected = $options['timeout'] === false ?
        $this->handler->$func($options['host'], $options['port']) :
        $this->handler->$func($options['host'], $options['port'], $options['timeout']);
        $this->handler->select($dataBase);
    }

    /**
    +----------------------------------------------------------
     * 是否连接
    +----------------------------------------------------------
     * @access private
    +----------------------------------------------------------
     * @return boolen
    +----------------------------------------------------------
     */
    private function isConnected() {
        return $this->connected;
    }

    /**
    +----------------------------------------------------------
     * 读取缓存
    +----------------------------------------------------------
     * @access public
    +----------------------------------------------------------
     * @param string $name 缓存变量名
    +----------------------------------------------------------
     * @return mixed
    +----------------------------------------------------------
     */
    public function get($name) {
        return $this->handler->get($name);
    }

    /**
    +----------------------------------------------------------
     * 写入缓存
    +----------------------------------------------------------
     * @access public
    +----------------------------------------------------------
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
    +----------------------------------------------------------
     * @return boolen
    +----------------------------------------------------------
     */
    public function set($name, $value, $expire = null) {
        if(is_int($expire)) {
            $result = $this->handler->setex($name, $expire, $value);
        }else{
            $result = $this->handler->set($name, $value);
        }
        if($result && $this->options['length']>0) {
            // 记录缓存队列
            $this->queue($name);
        }
        return $result;
    }

    /**
    +----------------------------------------------------------
     * 由列表头部添加字符串值。如果不存在该键则创建该列表。如果该键存在，而且不是一个列表，返回FALSE。
    +----------------------------------------------------------
     * @access public
    +----------------------------------------------------------
     * @param string key,value
    +----------------------------------------------------------
     * @return 成功返回数组长度，失败false
    +----------------------------------------------------------
     */
    public function lpush($name, $value, $expire = null){
        if(is_int($expire)) {
            $result = $this->handler->lpush($name, $value, $expire);
        }else{
            $result = $this->handler->lpush($name, $value);
        }
        return $result;
    }

    /**
    +----------------------------------------------------------
     * 由列表头部添加字符串值。如果不存在该键则创建该列表。如果该键存在，而且不是一个列表，返回FALSE。
    +----------------------------------------------------------
     * @access public
    +----------------------------------------------------------
     * @param string key, value, $count
    +----------------------------------------------------------
     * @return 成功返回数组长度，失败false
    +----------------------------------------------------------
     */
    public function lRem($name, $value, $count = 0){
        $result = $this->handler->lRem($name, $value, $count);
        return $result;
    }

    /**
    +----------------------------------------------------------
     * 存取多个元素到hash表
    +----------------------------------------------------------
     * @access public
    +----------------------------------------------------------
     * @param $value = array();
    +----------------------------------------------------------
     * @return 成功返回数组长度，失败false
    +----------------------------------------------------------
     */
    public function hmset($name, $value){
        $result = $this->handler->hmset($name, $value);
        return $result;
    }




    /**
    +----------------------------------------------------------
     * 删除缓存
    +----------------------------------------------------------
     * @access public
    +----------------------------------------------------------
     * @param string $name 缓存变量名
    +----------------------------------------------------------
     * @return boolen
    +----------------------------------------------------------
     */
    public function rm($name) {
        return $this->handler->delete($name);
    }

    /**
    +----------------------------------------------------------
     * 清除缓存
    +----------------------------------------------------------
     * @access public
    +----------------------------------------------------------
     * @return boolen
    +----------------------------------------------------------
     */
    public function clear() {
        return $this->handler->flushDB();
    }
}