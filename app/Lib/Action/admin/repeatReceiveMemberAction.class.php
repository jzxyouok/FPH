<?php
class repeatReceiveMemberAction extends backendAction
{
    public function _initialize() {
	    parent::_initialize();
		M(NULL, 'fph_', C('DB_log'));

    }

    /**
     * 重复领取用户
     * mihailong
     */
    public function index() {
		$mobile = $this->_get('mobile','trim');
		$searchWhere = '1';
		if($mobile)
		{
			M('member', 'fph_', C('DB_member'));
			$where = 'mobile = '.$mobile;
			$uid = D('member')->getField($where, 'id');
			if($uid) $searchWhere .= ' and uid = '.$uid;
		}
		M('receive_member', 'fph_', C('DB_log'));
		$count = M('receive_member')->where($searchWhere)->count('id');
		$p = new Page($count, 20);
		$page = $p->show();
		$list = M('receive_member')->where($searchWhere)->limit($p->firstRow.','.$p->listRows)->select();
		if($list)
		{
			$member = array_column($list,'uid');
			$member = implode(',', $member);
			$where = "id in ($member)";
			M('member', 'fph_', C('DB_member'));
			$data = D('member')->getList($where, 'id,mobile');
			$list = $this->buildData($list, $data);
		}
		$this->assign('list',$list);
		$this->assign('page',$page);
		$this->assign('search',$mobile);
		$this->assign('p',$p);
		$this->display();
    }
	private function buildData($list, $member)
	{
		$tmp = [];
		foreach($member as $key => $val)
		{
			$tmp[$val['id']] = $val['mobile'];
		}
		foreach($list as $key => $val)
		{
			$list[$key]['mobile'] = $tmp[$val['uid']];
		}
		return $list;
	}

	/**
	 * 单个用户的重复领取记录
	 */
	public function repeatList()
	{
		$uid = $this->_get('uid','trim');
		M('member', 'fph_', C('DB_member'));
		$member = M('member_wallet')->where(array('uid' => $uid))->find();
		$where = 'uid = '. $uid;
		$order = 'isdel asc';
		M('journal_repeat', 'fph_', C('DB_log'));
		$count = M('journal_repeat')->where($where)->count('id');
		$p = new Page($count, 20);
		$page = $p->show();
		$list = M('journal_repeat')->where($where)->order($order)->limit($p->firstRow.','.$p->listRows)->select();
		if($list)
		{
			$pids = array_unique(i_array_column($list, 'pid'));
			$pids = implode(',', $pids);
			M('property', 'fph_', C('DB_fangpinhui'));
			$titles = D('property')->where("id in ($pids)")->field('id,title')->select();
			$list = $this->propertyBuild($list, $titles);
		}
		$this->assign('list',$list);
		$this->assign('page',$page);
		$this->assign('member',$member);
		$this->assign('p',$p);
		$this->display();
	}

	private function propertyBuild($list, $titles)
	{
		$tmp = array();
		foreach($titles as $key => $val)
		{
			$tmp[$val['id']] = $val['title'];
		}
		foreach($list as $key => $val)
		{
			$list[$key]['title'] = isset($tmp[$val['pid']]) ?  $tmp[$val['pid']] : '';
		}
		return $list;
	}

	/**
	 * 删除记录
	 */
	public function delete()
	{
		$uid = $this->_get('uid','intval');
		$pid = $this->_get('pid','intval');
		$id = $this->_get('id','intval');
		if(!$uid || !$pid || !$id) $this->error('参数错误');
		$where = 'isdel = 0 and uid = '. $uid . ' and pid = '. $pid;
		$list = M('journal_repeat')->where($where)->select();
		$count = count($list);
		if($count < 2) $this->error('必须保留一条有效记录');
		//删除的金额是否大于钱包余额
		M('member', 'fph_', C('DB_member'));
		$member = M('member_wallet')->where(array('uid' => $uid))->find();
		$repeat = M('journal_repeat')->where(array('id' => $id))->field('journal_account,sid')->find();
		//journal_repeat isdel =1
		if($member['money'] < $repeat['journal_account'])  $this->error('余额不足');
		M('journal_account', 'fph_', C('DB_log'));
		M('journal_repeat')->where(array('id' => $id))->save(array('isdel' => 1));
		//journal_account 删除对应记录
		M('journal_account')->where(array('id' => $id))->delete();
		//钱包对应减少相应金额
		M('receive', 'fph_', C('DB_member'));
		M('receive')->where(array('id' => $repeat['sid']))->save(array('status' => 0));
		M('member_wallet')->where('uid = '.$uid. ' and money >= ' . $repeat['journal_account'])->setDec('money',$repeat['journal_account']);
		$this->success('操作成功');
	}

	public function recover()
	{
		$uid = $this->_get('uid','intval');
		$sid = $this->_get('sid','intval');
		$id = $this->_get('id','intval');
		if(!$sid || !$id) $this->error('参数错误');
		$delete_record = M('journal_repeat')->where(array('id' => $id))->find();
		M('journal_repeat')->where(array('id' => $id))->save(array('isdel' => 0));
		unset($delete_record['pid'],$delete_record['isdel']);
		M('journal_account')->where(array('id' => $id))->add($delete_record);
		M('member', 'fph_', C('DB_member'));
		M('receive')->where(array('id' => $sid))->save(array('status' => 1));
		M('member_wallet')->where('uid = '.$uid)->setInc('money',$delete_record['journal_account']);
		$this->success('操作成功');
	}

}