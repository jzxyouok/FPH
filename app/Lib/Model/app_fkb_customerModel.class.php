<?php

class app_fkb_customerModel extends Model
{
	//访客宝从缓存中根据id获取经纪人信息
   public function getbroker($id){    
       $data = F('broker');
       foreach ($data as $key => $value) {
           if($value['id'] == $id){
             return $name = $value['username'].'&nbsp;&nbsp;'.$value['tel'];
           }        
       }
    }


    //城市关联预算总价
	public function price_sum_bycity(){
          $price_sum[802] = array(
                   'city_id' => 802,  //上海 
                   'price'   => array(
                                  '0' =>'全部' , 
                                  '1' =>'80万以下' ,
                                  '2' =>'80-150万' ,
                                  '3' =>'150-200万' ,
                                  '4' =>'200-300万' ,
                                  '5' =>'300-500万' ,
                                  '6' =>'500万以上' ,
                                 )
            );
          return $price_sum;
  }
    //城市关联意向均价
  public function average_price_bycity(){
          $average_price[802] = array(
                   'city_id' => 802,  //上海 
                   'price'   => array(
                                  '0' =>'全部' ,
                                  '1' =>'5千以下' ,
                                  '2' =>'5-8千' ,
                                  '3' =>'8千-1万' ,
                                  '4' =>'1-1.5万' ,
                                  '5' =>'1.5-2万' ,
                                  '6' =>'2万以上' ,
                                 )
            );
          return $average_price;
  }

  /**
  客户信息统计
  */
  public function get_customer_info_rate($customer_id){
        $basic = M('app_fkb_customer')->field('customer,sexy,tel,address,basicinfo,analysis')->where('id ='.$customer_id)->find();
        $other =  M('app_fkb_wanttobuy')->where('customerid ='.$customer_id)->field('buyingmotive,level')->find();
        $limit = ' limit 3';
        $customerid = $customer_id;
        $mod =M(NULL,NULL,C('DB_FKB'));
        //$mod = M('fkb_customer_behavior_analysis','fph_','mysql://root:123456@192.168.1.115:3306/fph_fkb_behavior_analysis');
        $data['result_average_price'] = $this->fieldanalysis($mod,'average_price',$customerid,$limit);   
        $data['result_housetype'] = $this->fieldanalysis($mod,'housetype',$customerid,$limit);        
        $data['result_sumprice'] = $this->fieldanalysis($mod,'sumprice',$customerid,$limit);
        $data['result_property_type'] = $this->fieldanalysis($mod,'property_type',$customerid,$limit);
        $data['result_property_feature'] = $this->fieldanalysis($mod,'property_feature',$customerid,$limit);
        $data['result_city_id'] = $this->fieldanalysis($mod,'city_id',$customerid,$limit); 
        $data['result_area_id'] = $this->fieldanalysis($mod,'area_id',$customerid,$limit); 
        
        $count_rate = 0;
 
       if(!empty($basic['customer'])){
            $count_rate = $count_rate+0.1;        //姓名 0.1
       }
       if(!empty($basic['sexy'])){
            $count_rate = $count_rate+0.1;        //性别  0.1
       }
       if(!empty($basic['tel'])){
            $count_rate = $count_rate+0.1;        //电话 0.1
       }
       if(!empty($basic['analysis'])){
            $count_rate = $count_rate+0.1;        //客户分析 0.1
       }
       if(!empty($basic['address'])){
            $count_rate = $count_rate+0.05;       //联系地址 0.05
       }
       if(!empty($basic['basicinfo'])){
            $count_rate = $count_rate+0.05;       //基本信息  0.05
       }


        if(!empty($other['level'])){
            $count_rate = $count_rate+0.1;    //购买时间   0.1
        }

        if(!empty($other['buyingmotive'])){
            $count_rate = $count_rate+0.05;  
        }

        foreach ($data as $key => $value) {
                if(!empty($value)){
                    $count_rate = $count_rate+0.05;   //客户筛选条件  7*0.05
                }
          }
       return $count_rate;  
        

  }

    /**
    客户筛选数据分析
     */
    public function fieldanalysis($mod,$fieldname,$customer_id,$limit){
        $sql_average_price = 'SELECT '.$fieldname.',COUNT(id) AS tp_count,addtime FROM `fph_customer_behavior` WHERE customer_id ='.$customer_id.' GROUP BY '.$fieldname.' order by tp_count desc,addtime desc'.$limit;
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