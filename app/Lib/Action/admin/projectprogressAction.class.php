<?php
class projectprogressAction extends backendAction
{
    public function _initialize() {
        parent::_initialize();
    }

    public function index() {
		$fph = C('DB_PREFIX');
		$data['B.term_end'] = array('EGT',time());
		$data['B.term_start'] = array('ELT',time());
		$hezuo_list = M('property')->field('A.id,A.title,A.add_time')->table("{$fph}property AS A LEFT JOIN {$fph}property_cooperation AS B ON A.id=B.pid")
		->where($data)
		->order('A.add_time DESC')->select();

		foreach ($hezuo_list as $key => $value) {
				$time1=time();#当前时间
				$time2= $value['add_time'];//过年时间
				$sub2=($time1-$time2)/60/60/24;
				$sub = explode('.', $sub2);
				$hezuo_list[$key]['shangxiantianshu'] = $sub[0];
				$hezuo_list[$key]['baobeikehushu'] = count(M('myclient_property')->where('property ='.$value['id'])->group('pid')->select());
				$hezuo_list[$key]['baobeijinjirenshu'] = count(M('myclient_property')->where('property ='.$value['id'])->group('uid')->select());
				$hezuo_list[$key]['daikanrenshu'] =count(M('myclient_property')->where('status = 4 AND property ='.$value['id'])->select());
				$hezuo_list[$key]['chengjiaoshu'] =count(M('myclient_property')->where('status = 7 AND property ='.$value['id'])->select());
		}
		$this->assign('hezuo_list', $hezuo_list);
        $this->display();
    }
	
}