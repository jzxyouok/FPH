<?php
class giftbagAction extends backendAction
{

	public function _initialize() {
		parent::_initialize();
		M(NULL,NULL,C('DB_giftbag'));
		$this->RedisDataBaseEXPENSES= C('DB_REDIS_PROPERTY_EXPENSES');
	}

    public function index(){
		$couponGiftBag = D('coupon_giftbag');
		$giftBagType = D('giftbag_type');
		$count = $couponGiftBag->getCount();
		import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();
		$couponGiftBagList = $couponGiftBag->getList($p->firstRow,$p->listRows);
		$giftBagTypeList = $giftBagType->getListByKey();
		if(!empty($couponGiftBagList) && is_array($couponGiftBagList)){
			foreach($couponGiftBagList as $k => $val){
				$id = $val['id'];
				$tid = $val['tid'];
				$couponGiftBagList[$k]['name'] = $giftBagTypeList[$tid];
				$trueIssue = $this->getTrueIssue($id);
				$giftBagViceList = $this->getgiftbagVice($id,$tid);
				$couponGiftBagList[$k]['trip'] = $giftBagViceList['trip']?$giftBagViceList['trip']:0;
				$couponGiftBagList[$k]['fund'] = $giftBagViceList['fund']?$giftBagViceList['fund']:0;
				$couponGiftBagList[$k]['trueIssue'] = $trueIssue?$trueIssue:0;
				$couponGiftBagList[$k]['sumtrip'] = $couponGiftBagList[$k]['trip']*$couponGiftBagList[$k]['trueIssue'];
				$couponGiftBagList[$k]['sumfund'] = $couponGiftBagList[$k]['fund']*$couponGiftBagList[$k]['trueIssue'];
			}
		}
		$this->assign('list',$couponGiftBagList);
		$this->assign('page',$page);
		$this->display();
	}

	/**
	 *
	 * 新增
	 */
	public function add(){
		$giftBagType = D('giftbag_type');
		$couponGiftBag = D('coupon_giftbag');
		if(IS_POST){
			$data['tid']         = $this->_post('type','intval');
			$viceData['tid']     = $this->_post('type','intval');
			$viceData['trip']    = $this->_post('trip','trim');
			$viceData['fund']    = $this->_post('fund','trim');
			$viceData['pid']     = $this->_post('pid','trim');
			$data['planissue']   = $this->_post('planissue','intval');
			$data['time_start']  = strtotime($this->_post('time_start','trim'));
			$data['time_end']    = strtotime($this->_post('time_end','trim'));
			$data['status']      = $this->_post('status','intval');
			$data['add_time']    = time();
			if($data['time_start'] > $data['time_end']){
				$this->error('活动开始时间不能大于结束时间');
			}
			if($data['tid'] ==2){
				$viceData['invitation']  = $this->_post('invitation','intval');
			}
			if($data['tid'] ==3){
				$viceData['remark']  = $this->_post('remark','trim');
			}
			if($data['status']==1){
				$isSuccess = $couponGiftBag->checkGiftBag($data);
				if($isSuccess){
					$this->error('已存在同类有效礼包');
				}
			}
			$viceData['gid'] = $couponGiftBag->add($data);
			$viceData['add_time'] = time();
			$result = $this->addgiftbagVice($viceData);
			if($result){
				//修改的数据写入缓存
				if($data['status']==1 && $data['time_start']<time() && $data['time_end']>time()){
					$redis = new CacheRedis($this->RedisDataBaseEXPENSES);
					$redisData = $viceData;
					$redisData['planissue'] = $data['planissue'];
					$redisKey = 'giftbag'  . '_'. $data['tid'];
					$redis->handler->hmset($redisKey, $redisData);
					$redis->handler->expire($redisKey, C('giftBagRedisTime'));
				}
				$this->success('提交成功');
				exit;
			}else{
				$this->error('提交失败');
			}
		}else{
			$giftBagTypeList = $giftBagType->getListByKey();
			$this->assign('giftBagTypeList',$giftBagTypeList);
			$this->display();
		}
	}

	/**
	 *
	 * 编辑
	 *
	 */
	public function edit(){
		$couponGiftBag = D('coupon_giftbag');
		$giftBagType = D('giftbag_type');
		$giftBagVice = D("coupon_giftbag_2");
		$id = $this->_request('id','intval');
		$giftBagTypeList = $giftBagType->getListByKey();
		$info = $couponGiftBag->where(array('id'=>$id))->find();
		$giftBagViceList = $this->getgiftbagVice($info['id'],$info['tid']);
		$info['trip'] 		= $giftBagViceList['trip'];
		$info['fund'] 		= $giftBagViceList['fund'];
		$info['pid'] 		= $giftBagViceList['pid'];
		$info['invitation'] = $giftBagViceList['invitation'];
		$info['name'] 		= $giftBagTypeList[$info['tid']];
		$this->assign('info',$info);
		if(IS_POST) {
			$data['planissue'] = $this->_post('planissue', 'intval');
			$data['time_start'] = strtotime($this->_post('time_start', 'trim'));
			$data['time_end'] = strtotime($this->_post('time_end', 'trim'));
			$data['status'] = $this->_post('status', 'intval');
			$tid = $this->_post('tid', 'intval');
			$data['id']          = $id;
			$data['tid']         = $tid;
			$viceData['tid']     = $tid;
			if($data['time_start'] > $data['time_end']){
				$this->error('活动开始时间不能大于结束时间');
			}
			if ($data['status'] == 1) {
				$isSuccess = $couponGiftBag->checkGiftBag($data);
				if ($isSuccess) {
					$this->error('已存在同类有效礼包');
				}
			}
			$result = $couponGiftBag->where(array('id' => $id))->save($data);
			if ($tid == 2) {
				$viceData['invitation'] = $this->_post('invitation', 'intval');
				$resultVice = $giftBagVice->where(array('gid' => $id))->save($viceData);
			}
			if ($result or $resultVice) {
				//修改的数据写入缓存
				$redis = new CacheRedis($this->RedisDataBaseEXPENSES);
				$redisKey = 'giftbag'  . '_'. $data['tid'];
				if($data['status']==1 && $data['time_start']<time() && $data['time_end']>time()){
					//删除原有数据
					$redis->rm('giftbag.coupon_giftbag.'.$data['tid'].'_'.date('Y-m-d',time()));
					$couponData = D("coupon_giftbag_".$tid)->where(array('gid' => $id))->find();
					unset($couponData['pid']);
					$redisData = $couponData;
					$redisData['planissue'] = $data['planissue'];
					$redis->handler->hmset($redisKey, $redisData);
					$redis->handler->expire($redisKey, C('giftBagRedisTime'));
				}else{
					$redis->rm($redisKey);
				}
				$this->success('提交成功');
				exit;
			} else {
				$this->error('提交失败');
			}
		}
		$this->display();
	}

	/**
	 *
	 * 领取记录
	 *
	 */
	public function lists(){
		$id = $this->_get('id','intval');
		$giftBagRecord = D('giftbag_record');
		if($id){
			$count = $giftBagRecord->getCount($id);
			import("ORG.Util.Page");
			$p = new Page($count, 20);
			$page = $p->show();
			$giftBagRecordList = $giftBagRecord->getList($id,$p->firstRow,$p->listRows);
			if(!empty($giftBagRecordList)&&is_array($giftBagRecordList)){
				foreach($giftBagRecordList as $k => $val){
					M('member','fph_',C('DB_member'));
					$user_info = M('member')->field('mobile')->where(array('id'=>$val['uid']))->find();
					$user_extend = M('member_extend')->field('username')->where(array('uid'=>$val['uid']))->find();
					$giftBagRecordList[$k]['username'] = empty($user_extend['username'])?'':$user_extend['username'];
					$giftBagRecordList[$k]['mobile']   = $user_info['mobile'];
				}
			}
			$this->assign('list',$giftBagRecordList);
			$this->assign('page',$page);
			$this->display();
		}else{
			$this->error('提交参数错误');
		}
	}

	/**
	 * 获取礼包副表信息
	 */
	private function getgiftbagVice($id,$tid){
		$giftBagViceList = array();
		$giftBagVice = D("coupon_giftbag_".$tid);
		$giftBagViceList = $giftBagVice->getList($id);
		return $giftBagViceList;
	}

	/**
	 * 获取实际发放份数
	 */

	private function getTrueIssue($gid){
		return M("giftbag_record")->where("gid = $gid and record_time > 0 ")->count();
	}

	/**
	 * 新增礼包副表信息
	 */
	private function addgiftbagVice($data){
		$tid 			= $data["tid"];
		$giftBagVice 	= D("coupon_giftbag_".$tid);
		$result 		= $giftBagVice->add($data);
		return $result;
	}
    
}