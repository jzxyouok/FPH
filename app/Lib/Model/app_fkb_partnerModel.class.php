<?php

class app_fkb_partnerModel extends Model
{
	//判断陪同者是否存在重复
  public function partner_isexsist($customerid,$tel){
      $id = M('app_fkb_partner')->where(array('customerid'=>$customerid,'tel'=>$tel))->getfield('id');
      //$sql = M('app_fkb_partner')->getlastsql();
      return $id;
     
  }
	
}