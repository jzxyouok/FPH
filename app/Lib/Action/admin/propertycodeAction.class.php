<?php
class propertycodeAction extends backendAction
{


    public function index() {
        $title = $this->_get('title','trim');
        $time = time();
        $list = D('property')->expenses_index($time, $title);
        $this->assign('list',$list[0]);
        $this->assign('page',$list[1]);


        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);
        $this->assign('title',$title);

        $this->display();
    }

    //更新验证码*存入redis
    public function updatecode(){
        if(IS_POST){
            $pid   = $this->_post('pid','intval');
            if(!$pid){
                $this->ajaxReturn(0, '系统参数出错');
            }
            $code  = random(6,1);
            $redis = new CacheRedis(0);
            $redisCode = $redis->set('laravel:propertyExpensesCode.'.$pid, $code);
            if($redisCode){
                $this->ajaxReturn(1, '生成成功', $code);
            }else{
                $this->ajaxReturn(1, '生成失败');
            }
        }
    }


}