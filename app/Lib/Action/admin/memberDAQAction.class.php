<?php
class memberDAQAction extends backendAction
{
	/**
	 * 数据采集列表
	 * {@inheritDoc}
	 * @see backendAction::index()
	 */
    public function index()
    {
    	/*
    	 * 查询方式
    	 */
    	$cid	= $this->_get('cid','trim');
    	$fields						= array('*');
    	$where						= '';
    	$order						= "time desc, id desc";
    	if(!empty($cid))	$where	= "cid='{$cid}'";
    	 
    	/*
    	 * citys
    	 */
        $citys		= array();
        $tmp_citys	= D('city')->province();
        foreach( $tmp_citys AS $tmp_city )
        {
        	$cid			= $tmp_city['id'];
        	$citys[$cid]	= $tmp_city;
        }
    	
        /*
         * 列表
         */
        M(NULL,NULL,C('DB_member'));
        $list	= D('member_data_acquisition')->getPageList( $fields, $where, $order);    
        
        /*
         * 手机号
         */
        $mobiles	= array();
        $uids		= array_map(function($v){ return $v['uid'];}, $list[0]);
        $members	= D('member')->getList("id IN('".implode("','",$uids)."')", array('id','mobile'));
        foreach( $members AS $member )
        {
			$uid			= $member['id'];
			$mobiles[$uid]	= $member['mobile'];
        }
       
        
        /*
         * user name
         */
        $usernames	= array();
        $uids		= array_map(function($v){ return $v['uid'];}, $list[0]);
        $members	= D('member')->getListByExtend("uid IN('".implode("','",$uids)."')", array('uid','username'));
        foreach( $members AS $member )
        {
        	$uid				= $member['uid'];
        	$usernames[$uid]	= $member['username'];
        }
        
        /*
         * 列表中写入扩展信息
         */
        foreach( $list[0] AS $key => $item )
        {
        	$cid							= $item['cid'];
        	$uid							= $item['uid'];
        	$list[0][$key]['mobile']		= isset( $mobiles[$uid] ) ? $mobiles[$uid] : '-';
        	$list[0][$key]['username']		= isset( $usernames[$uid] ) ? $usernames[$uid] : '-';
        	$list[0][$key]['city_name']		= $citys[$cid]['name'];
        	$list[0][$key]['data']			= json_decode( $item['data'], TRUE );
        }

        /*
         * 输出结果
         */
        $this->assign('list',$list[0]);
        $this->assign('page',$list[1]);
        $this->assign('citys',$citys);
        $p = $this->_get('p','intval',1);
        $this->assign('p',$p);
        $this->assign('list_table',true);
        $this->display();
    }
}