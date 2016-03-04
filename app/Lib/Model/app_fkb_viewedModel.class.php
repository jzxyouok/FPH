<?php

class app_fkb_viewedModel extends Model
{
	//判断是否已经存在浏览记录
  public function viewed_isexsist($customerid,$pid){
      $id = M('app_fkb_viewed')->where(array('customerid'=>$customerid,'property_id'=>$pid))->getfield('id');
      //$sql = M('app_fkb_partner')->getlastsql();
      return $id;
     
  }
	
}