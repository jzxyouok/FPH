<?php
class crondAction extends frontendAction {
    
    public function index() {
	    ini_set('date.timezone','Asia/Shanghai');
        $gettime = time();
        $str = 'id,with_look,status,add_time,status_cid,pid,property';
        $data['status_cid'] =array('neq',0);
        $data['status']     = array('in','1,2,3,4');
        $mproperty          = M('myclient_property')->field($str)->where($data)->select();
        foreach($mproperty as $key=>$value){
            if($value['with_look'] == 1){
                $systems_time = C('pin_protection_time');
            }else{
                $systems_time = C('pin_delegate_time');
            }
            $m_addtime = M('myclient_status')->where(array('mpid'=>$value['id'],'status'=>$value['status']))->getField('add_time');
            //系统有效保护期
            $protection_time = $m_addtime+$systems_time*24*3600;
            if($protection_time<=$gettime){
            	//有效期失效
                $datas['mpid'] 			= $value['id'];
                $datas['pid'] 			= $value['property'];
                $datas['status'] 		= 9;
                $datas['status_cid'] 	= 0;
                $datas['with_look'] 	= $value['with_look'];
                $datas['add_time'] 		= $gettime;
                $m_s = M('myclient_status')->add($datas);
                $m_p = M('myclient_property')->where(array('id'=>$value['id']))->save(array('status_cid'=>0,'status'=>9));
            }
        }
        return true;
    }
    

}