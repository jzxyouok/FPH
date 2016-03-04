<?php

class customer_behaviorModel extends Model
{
	
  /**
  客户筛选数据分析
  */
  public function fieldanalysis($mod,$fieldname,$customer_id,$limit){
          $sql_average_price = 'SELECT '.$fieldname.',COUNT(id) AS tp_count,addtime FROM `customer_behavior` WHERE customer_id ='.$customer_id.' GROUP BY '.$fieldname.' order by tp_count desc,addtime desc'.$limit;
          $result_average_price =  $mod->query($sql_average_price);

          if(!empty($limit)){
               foreach ($result_average_price as $key => $value) {
                  unset($result_average_price[$key]['tp_count']);
                  unset($result_average_price[$key]['addtime']);
                  $result_average_price[$key] = $value[$fieldname];
               }
               return $result_average_price = implode(',',$result_average_price);        
          }else{
               return $result_average_price;        
          }    
         
      }

}