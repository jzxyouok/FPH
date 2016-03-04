<?php

class app_fkb_brokersuggestModel extends Model
{
	//判断收藏的楼盘是否存在重复
  public function collect_isexsist($customerid,$pid){
      $type = M('app_fkb_brokersuggest')->where(array('customerid'=>$customerid,'property_id'=>$pid,'isdel'=>0))->getfield('type');
      //返回类型  1 收藏 2 推荐
      return $type;     
  }

  public function propertycollect_add($customerid,$pid,$type =1){
  	$data = array(
  			'customerid' =>$customerid,
  			'property_id' =>$pid,
  			'collecttime' =>time(),
  			'type' =>$type,   //1 收藏  2推荐
  		);
  	$id = M('app_fkb_brokersuggest')->data($data)->add();
  	return $id;
  }

  /**
  判断是否被删除
  */
   public function collect_isdel($customerid,$pid){
      $id = M('app_fkb_brokersuggest')->where(array('customerid'=>$customerid,'property_id'=>$pid,'isdel'=>1))->getfield('id');
      //返回类型  1 收藏 2 推荐
      return $id;     
  }

  /**
  改变收藏状态
  */
  public function change_collect_status($customerid,$pid){
      return M('app_fkb_brokersuggest')->where(array('customerid'=>$customerid,'property_id'=>$pid,'isdel'=>1))->save(array('isdel'=>0,'collecttime'=>time()));
  }

  //取消楼盘收藏
  public function collect_cancle($customerid,$pid){
      $data = array('isdel'=>1);
  		M('app_fkb_brokersuggest')->where(array('customerid'=>$customerid,'property_id'=>$pid))->save($data);

  }
	
}