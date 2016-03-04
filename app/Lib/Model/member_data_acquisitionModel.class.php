<?php
class member_data_acquisitionModel extends Model
{
    public function getPageList( $fields = array('*'), $where=NULL, $order='id DESC')
    {
        M(NULL,NULL,C('DB_member'));
        $count = M('member_data_acquisition')->where($where)->count('id');
        import("ORG.Util.Page");
        $page	= new Page($count, 20);
        $list	= M('member_data_acquisition')->field( $fields )->where( $where )->order($order)->limit( $page->firstRow. ','. $page->listRows )->select();
        return array( $list, $page );
    }
}