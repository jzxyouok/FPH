<?php
 return array (  
   'DB_HOST' => '192.168.1.149',
   //'DB_HOST' => '10.168.11.22',
   'DB_NAME' => 'fangpinhui',
   'DB_USER' => 'develop',
   'DB_PWD' => 'fangpinhui100',
   //'DB_PWD' => 'fangpinhui_db',
   'DB_PORT' => '3306',
   'DB_PREFIX' => 'fph_',
   'DB_HOST_t' => '192.168.1.149',
   'DB_NAME_t' => 'property',
   'DB_USER_t' => 'root',
   'DB_PWD_t' => 'fangpinhui100',
   'DB_PREFIX_t' => 'fph_',
   'DATA_CACHE_TYPE' => 'Redis',
   'REDIS_HOST'            => '192.168.1.149',
   'REDIS_PORT'            => 6379,
   'DATA_CACHE_TIME' => 3600,
   'DB_REDIS_ACTIVITY_KANJIA'=>4,
   'DB_REDIS_ACTIVITY_COUPON'=>4,
   'DB_REDIS_PROPERTYMQ'=>5,
   'DB_REDIS_PROPERTY_EXPENSES'=>4,
   'DB_REDIS_PROPERTY_RESERVATION'=>4,
   'DB_REDIS_PROPERTY_SUGGEST'=>4,
   'DB_property' => 'mysql://develop:fangpinhui100@192.168.1.149:3306/property',        //房源库
   'DB_customer' => 'mysql://develop:fangpinhui100@192.168.1.149:3306/customer',        //客户库
   'DB_bbs' => 'mysql://develop:fangpinhui100@192.168.1.149:3306/bbs',                  //社区库
   'DB_fangpinhui' => 'mysql://develop:fangpinhui100@192.168.1.149:3306/fangpinhui',    //默认库
   'DB_member' => 'mysql://develop:fangpinhui100@192.168.1.149:3306/member',            //注册用户库
   'DB_log' => 'mysql://develop:fangpinhui100@192.168.1.149:3306/log',                  //注册用户库
   'DB_activity' => 'mysql://develop:fangpinhui100@192.168.1.149:3306/activity',        //活动库
   'DB_OPERATION_ACTIVITY' => 'mysql://develop:fangpinhui100@192.168.1.149:3306/operation_activity',
   'DB_FKB' => 'mysql://develop:fangpinhui100@192.168.1.149:3306/customer_behavior',
   'DB_FPH' => 'mysql://develop:fangpinhui100@192.168.1.149:3306/fangpinhui',
   'DB_ad' => 'mysql://develop:fangpinhui100@192.168.1.149:3306/ad',
     'DB_giftbag' => 'mysql://develop:fangpinhui100@192.168.1.149:3306/giftbag',       //优惠礼包库
 );
