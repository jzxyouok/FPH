<?php

class cityModel extends Model
{
	
	/**
     * 生成spid 
     * 
     * @param int $pid 父级ID
     */
    public function get_spid($pid) {
        if (!$pid) {
            return 0; 
        }
        $pspid = $this->where(array('id'=>$pid))->getField('spid');
        if ($pspid) {
            $spid = $pspid . $pid . '|';
        } else {
            $spid = $pid . '|';
        }
        return $spid;
    }
    
    /**
     * 获取分类下面的所有子分类的ID集合
     * 
     * @param int $id
     * @param bool $with_self
     * @return array $array 
     */
    public function get_child_ids($id, $with_self=false) {
        $spid = $this->where(array('id'=>$id))->getField('spid');
        $spid = $spid ? $spid .= $id .'|' : $id .'|';
        $id_arr = $this->field('id')->where(array('spid'=>array('like', $spid.'%')))->select();
        $array = array();
        foreach ($id_arr as $val) {
            $array[] = $val['id'];
        }
        $with_self && $array[] = $id;
        return $array;
    }

    /**
     * 获取和分类关联的标签ID集合
     */
    public function get_tag_ids($cate_id) {
        $res = M('item_cate_tag')->field('tag_id')->where(array('cate_id'=>$cate_id))->select();
        $ids = array();
        foreach($res as $tag) {
            $ids[] = $tag['tag_id'];
        }
        return $ids;
    }

    /**
     * 根据ID获取分类名称
     */
    public function get_name($id) {
        //分类数据
        if (false === $cate_data = F('cate_data')) {
            $cate_data = $this->cate_data_cache();
        }
        return $cate_data[$id]['name'];
    }

    /**
     * 获取标签分类紧接上级实体分类
     */
    public function get_pentity_id($id) {
        $pentity_id = 0;
        if (false === $cate_data = F('cate_data')) {
            $cate_data = $this->cate_data_cache();
        }
        $spid = array_reverse(explode('|', trim($cate_data[$id]['spid'], '|')));
        foreach ($spid as $val) {
            if ($cate_data[$val]['type'] == 0) {
                $pentity_id = $val;
                break;
            }
        }
        return $pentity_id;
    }

    /**
     * 读取写入缓存(有层级的分类数据)
     */
    public function cate_cache() {
        $cate_list = array();
        $cate_data = $this->field('id,pid,name,fcolor,type')->where('status=1')->order('ordid')->select();
        foreach ($cate_data as $val) {
            if ($val['pid'] == '0') {
                $cate_list['p'][$val['id']] = $val;
            } else {
                $cate_list['s'][$val['pid']][$val['id']] = $val;
            }
        }
        F('cate_list', $cate_list);
        return $cate_list;
    }

    /**
     * 读取写入缓存(无层级分类列表)
     */
    public function cate_data_cache() {
        $cate_data = array();
        $result = $this->field('id,pid,spid,name,fcolor,type,seo_title,seo_keys,seo_desc')->where('status=1')->order('ordid')->select();
        foreach ($result as $val) {
            $cate_data[$val['id']] = $val;
        }
        F('cate_data', $cate_data);
        return $cate_data;
    }

    /**
     * 分类关系读取写入缓存
     */
    public function relate_cache() {
        $cate_relate = array();
        $cate_data = $this->field('id,pid,spid')->where('status=1')->order('ordid')->select();
        foreach ($cate_data as $val) {
            $cate_relate[$val['id']]['sids'] = $this->get_child_ids($val['id']); //子孙
            if ($val['pid'] == '0') {
                $cate_relate[$val['id']]['tid'] = $val['id']; //祖先
            } else {
                $cate_relate[$val['id']]['tid'] = array_shift(explode('|', $val['spid'])); //祖先
            }
        }
        F('cate_relate', $cate_relate);
        return $cate_relate;
    }

    /**
     * 检测分类是否存在
     * 
     * @param string $name
     * @param int $pid
     * @param int $id
     * @return bool 
     */
    public function name_exists($name, $pid, $id=0) {
        $where = "name='" . $name . "' AND pid='" . $pid . "' AND id<>'" . $id . "'";
        $result = $this->where($where)->count('id');
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 更新则删除缓存
     */
    protected function _before_write(&$data) {
        F('cate_data', NULL);
        F('cate_list', NULL);
        F('cate_relate', NULL);
        F('index_cate_list', NUll);
    }

    /**
     * 删除也删除缓存
     */
    protected function _after_delete($data, $options) {
        F('cate_data', NULL);
        F('cate_list', NULL);
        F('cate_relate', NULL);
        F('index_cate_list', NUll);
    }
	
	/**
     * 删除前置关联
     */
    public function admin_del_img($ids) {
		
    }

	public function city_name($id){
		$data = M('city')->where(array('id'=>$id))->getfield('name');
		return $data;
	}
	//省
	public function province(){
		$list = M('city')->field('id,name')->where(array('pid'=>0))->order('id ASC')->select();
		return $list;
	}
	
	//市
	public function city($id){
		$list = M('city')->field('id,name')->where(array('pid'=>$id))->order('id ASC')->select();
		return $list;
	}
	
	//市#推荐
	public function recommend_city($id){
		$list = M('city')->field('id,name')->where(array('recommend'=>1))->order('id ASC')->select();
		return $list;
	}
	
	
	/**
	 * description ：获取所有楼盘的城市  (注：二级城市)
	 * @param $type=0 数组  $type=1 字符串
	 * author      ：H.J.H
	 * dade        :2014-12-24
	 */	
	
	public function get_proprerty_city($type=0){
		
		$fph = C('DB_PREFIX');
		
		//区域所有选择
		$time  = time();
		$where = "and ".$time.">B.term_start and ".$time." < B.term_end ";
		
		$list_area = M('property')->field('A.city_id')
		                          ->table("{$fph}property AS A INNER JOIN {$fph}property_cooperation AS B ON A.id = B.pid")
		                          ->where('A.status=1 '.$where)
		                          ->order('A.add_time DESC')
		                          ->select();
		$room = array();
		foreach($list_area as $k => $v){
			$city_area   = M('city')->field('pid,spid')->where(array('id'=>$v['city_id']))->find();
			if(substr_count($city_area['spid'],'|') == 3){
				$city_areas   = M('city')->field('pid,spid')->where(array('id'=>$city_area['pid']))->find();
				$area_list.= $city_areas['pid'].',';
			}
			if(substr_count($city_area['spid'],'|') == 2){
			  $area_list.= $city_area['pid'].',';
			}
			if(substr_count($city_area['spid'],'|') == 0){
				$area_list.= $v['city_id'].',';
			}
		}
	
		$citylist =  M('city')->field('id,name')->where("id in (".substr($area_list,0,-1).")")->select();
		
		//$citylist = array(array('id'=>'803','name'=>'上海市'),array('id'=>'867','name'=>'苏州市'));
		
		return  $type ?  substr($area_list,0,-1)  :   $citylist;
	}
	

    //从缓存中根据id获取城市信息
    //gyw
    public function getcityfromcache($id){
        $data = F('citylist');
        foreach ($data as $key => $value) {
           if($value['id'] == $id){
             return $name = $value['name'];
           }        
       }
    }

    /**
     * @param $field
     * @param $where
     * @return mixed
     */
    public function getCity($field, $where){
        $data = M('city')->field($field)->where($where)->find();
        return $data;
    }
	
	
	
	
	
	
	
	
	
	
}