<?php

class operationactivityModel extends Model
{

	public function insertSignRecord($params)
	{

		return M('operationactivity')->data($params)->add();
	}

	public function isSign($time,$openid)
	{
		$isSign = M('operationactivity')->where(array('openid' => $openid, 'signdate' => $time))->getfield('id');
		if(!empty($isSign))
		{
			return true;
		}
		return false;

	}

	public function allSignDate($openid)
	{
		$all = M('operationactivity')->where(array('openid' => $openid))->field('signdate')->order('signtime asc')->select();
		return $all;
	}
	
}