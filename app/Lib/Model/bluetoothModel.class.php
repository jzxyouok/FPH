<?php
class bluetoothModel extends Model
{
    public function lists($uuid, $major, $minor, $title, $status, $time_start, $time_end){
        $time  = time();
        $where = "status != 2";

        if($uuid){
            $where .=" AND uuid = '".$uuid."'";
        }
        if($major){
            $fieldMajor = 'id';
            $whereMajor = 'major = '.$major;
            $majorInfo = D('bluetooth_cate')->majorInfo($fieldMajor, $whereMajor);
            if($majorInfo){
                $where .=" AND major = ".$majorInfo['id']."";
            }else{
                $where .=" AND major = 0";
            }
        }
        if($minor){
            $where .=" AND minor = ".$minor;
        }
        if($status != ''){
            $where .=" AND status = ".$status;
        }
        if($time_start){
            $where .=" AND time_start <= ".$time;
        }
        if($time_end){
            $where .=" AND time_end >= ".$time + 86400;
        }
        if($title){
            M('property','fph_',C('DB_fangpinhui'));
            $whereProperty = "title = '".$title."'";
            $fieldProperty = "id";
            $propertyInfo = D('property')->findPropertyInfo($whereProperty, $fieldProperty);
            if($propertyInfo){
                $where .=" AND pid = ".$propertyInfo['id'];
            }else{
                $where .=" AND pid = 0";
            }
        }

        M(NULL,'fph_',C('DB_property'));
        $count = M('bluetooth')->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();
        $str = 'id,uuid,major,minor,pid,time_start,time_end,status,add_time';
        $list = M('bluetooth')->field($str)->where($where)->order('add_time DESC')->limit($p->firstRow.','.$p->listRows)->select();
        //M('city','fph_',C('DB_fangpinhui'));
        foreach($list as $key=>$val){
            if($val['major']){
                $fieldMajor = 'major';
                $whereMajor = 'id='.$val['major'];
                $majorInfo  = D('bluetooth_cate')->majorInfo($fieldMajor, $whereMajor);
                $list[$key]['major'] = $majorInfo['major'];
            }
            M('property','fph_',C('DB_fangpinhui'));
            $whereProperty = 'id = '.$val['pid'];
            $fieldProperty = 'title';
            $propertyInfo  = D('property')->findPropertyInfo($whereProperty, $fieldProperty);
            $list[$key]['title'] = $propertyInfo['title'];
            $list[$key]['deviceID'] = $val['uuid'].'-'.sprintf('%04s', dechex($majorInfo['major'])).'-'.sprintf('%04s', dechex($val['minor']));
            M('receive','fph_',C('DB_member'));
            $whereLinks = 'bid = '.$val['id'];
            $list[$key]['links'] = D('receive')->links($whereLinks);
        }
        return array($list, $page);
    }



    public function insertData($data){
        $returnData = M('bluetooth')->add($data);
        return $returnData;
    }

    public function updateData($where, $data){
        $returnData = M('bluetooth')->where($where)->save($data);
        return $returnData;
    }

    //导出
    public function export(){
        $where = 'status=1';
        $str = 'id,city_id,major,add_time,status';
        $list = M('bluetooth_cate')->field($str)->where($where)->order('add_time DESC')->select();
        M('city','fph_',C('DB_fangpinhui'));
        foreach($list as $key=>$val){
            //所在城市
            if($val['city_id']){
                $city_name = M('city')->where('id='.$val['city_id'])->getField('name');
                $list[$key]['city_name'] = $city_name;
            }
        }
        return $list;
    }

    //查询最大的magor
    public function maxMajor($field, $where, $order){
        $data = M('bluetooth')->field($field)->where($where)->order($order)->find();
        return $data;
    }

    //查看
    public function show($field, $where){
        $data = M('bluetooth')->field($field)->where($where)->find();
        return $data;
    }

    //查看设备绑定楼盘
    public function listsData($field, $where, $pageSize = false){
        if($pageSize){
            $count = M('bluetooth')->where($where)->count('id');
            import("ORG.Util.Page");
            $p = new Page($count, $pageSize);
            $page = $p->show();
            $list = M('bluetooth')->field($field)->where($where)->order('add_time DESC')->limit($p->firstRow.','.$p->listRows)->select();
        }else{
            $list = M('bluetooth')->field($field)->where($where)->order('add_time DESC')->select();
        }
        foreach($list as $key=>$val){
            if($val['major']){
                $fieldMajor = 'major';
                $whereMajor = 'id='.$val['major'];
                $majorInfo  = D('bluetooth_cate')->majorInfo($fieldMajor, $whereMajor);
                $list[$key]['major'] = $majorInfo['major'];
            }
            M('property','fph_',C('DB_fangpinhui'));
            $whereProperty = 'id = '.$val['pid'];
            $fieldProperty = 'title';
            $propertyInfo  = D('property')->findPropertyInfo($whereProperty, $fieldProperty);
            $list[$key]['title'] = $propertyInfo['title'];
            $list[$key]['deviceID'] = bin2hex($val['uuid']).'-'.sprintf('%04s', dechex($majorInfo['major'])).'-'.sprintf('%04s', dechex($val['minor']));
            M('receive','fph_',C('DB_member'));
            $whereLinks = 'bid = '.$val['id'];
            $list[$key]['links'] = D('receive')->links($whereLinks);
        }
        return array($list, $page);
    }

}