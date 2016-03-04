<?php
class cityAction extends backendAction { 
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('city');
    }

    public function index() {
        $sort = $this->_get("sort", 'trim', 'ordid');
        $order = $this->_get("order", 'trim', 'ASC');
		$idorder = 'id ASC,';
        $tree = new Tree();
        $tree->icon = array('│ ','├─ ','└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        //$result = $this->_mod->order($idorder . $sort . ' ' . $order)->select();
		$result = $this->_mod->where('pid=0')->order($idorder . $sort . ' ' . $order)->select();
        $array = array();
        foreach($result as $r) {
            $r['str_recommend'] = '<img data-tdtype="toggle" data-id="'.$r['id'].'" data-field="recommend" data-value="'.$r['recommend'].'" src="__STATIC__/images/admin/toggle_' . ($r['recommend'] == 0 ? 'disabled' : 'enabled') . '.gif" />';
            $r['str_img'] = $r['img'] ? '<span class="img_border"><img src="'.attach($r['img'], 'item_cate').'" style="width:26px; height:26px;" class="J_preview" data-bimg="'.attach($r['img'], 'item_cate').'" /></span>' : '';
            $r['str_manage'] = '<a href="javascript:;" class="J_showdialog" data-uri="'.U('city/add',array('pid'=>$r['id'])).'" data-title="'.L('add_item_cate').'" data-id="add" data-width="520">'.L('add_item_subcate').'</a> |
                                <a href="javascript:;" class="J_confirmurl" data-acttype="ajax" data-uri="'.U('city/delete',array('id'=>$r['id'])).'" data-msg="'.sprintf(L('confirm_delete_one'),$r['name']).'">'.L('delete').'</a>';
            $r['parentid_node'] = ($r['id'])? ' class=""' : '';
            $array[] = $r;
        }
	
        $str  = "<tr \$parentid_node><td align='center'><input type='checkbox' value='\$id' class='J_checkitem'></td><td align='center' rel='1'>\$id</td>
                 <td><span class='collapsed' style='padding-left:20px'></span>
		 <span data-tdtype='edit' data-field='name' data-id='\$id' class='tdedit'  style='color:\$fcolor'>\$name</span></td><td><span data-tdtype='edit' data-field='latitude' data-id='\$id' class='tdedit'>\$latitude</span></td><td align='center'>\$str_recommend</td><td align='center'>\$str_manage</td></tr>";
        $tree->init($array);
        $list = $tree->get_tree(0, $str);
        $this->assign('list', $list);
        //bigmenu (标题，地址，弹窗ID，宽，高)
        $big_menu = array(
            'title' => '添加城市',
            'iframe' => U('city/add'),
            'id' => 'add',
            'width' => '520'
            //'height' => '100'
        );
        $this->assign('big_menu', $big_menu);
        $this->assign('list_table', true);
        $this->display();
    }

    /**
     * 添加子菜单上级默认选中本栏目
     */
    public function _before_add() {
        $pid = $this->_get('pid', 'intval', 0);
        if ($pid) {
            $spid = $this->_mod->where(array('id'=>$pid))->getField('spid');
            $spid = $spid ? $spid.$pid : $pid;
            $this->assign('spid', $spid);
        }
    }

    /**
     * 入库数据整理
     */
    protected function _before_insert($data = '') {
        //检测分类是否存在
        if($this->_mod->name_exists($data['name'], $data['pid'])){
            $this->ajaxReturn(0, L('item_cate_already_exists'));
        }
        //生成spid
        $data['spid'] = $this->_mod->get_spid($data['pid']);
        return $data;
    }

    /**
     * 修改提交数据
     */
    protected function _before_update($data = '') {
        if ($this->_mod->name_exists($data['name'], $data['pid'], $data['id'])) {
            $this->ajaxReturn(0, L('item_cate_already_exists'));
        }
        $item_cate = $this->_mod->field('img,pid')->where(array('id'=>$data['id']))->find();
        if ($data['pid'] != $item_cate['pid']) {
            //不能把自己放到自己或者自己的子目录们下面
            $wp_spid_arr = $this->_mod->get_child_ids($data['id'], true);
            if (in_array($data['pid'], $wp_spid_arr)) {
                $this->ajaxReturn(0, L('cannot_move_to_child'));
            }
            //重新生成spid
            $data['spid'] = $this->_mod->get_spid($data['pid']);
        }
        return $data;
    }

    /**
     * 批量移动分类
     */
    public function move() {
        if (IS_POST) {
            $data['pid'] = $this->_post('pid', 'intval');
            $ids = $this->_post('ids');
            //检查移动分类是否合法
            //获取目标分类信息
            $target_spid = $this->_mod->where(array('id'=>$data['pid']))->getField('spid');
            $ids_arr = explode(',', $ids);
            foreach ($ids_arr as $id) {
                if (false !== strpos($target_spid . $data['pid'].'|', $id.'|')) {
                    $this->ajaxReturn(0, L('cannot_move_to_child'));
                }
            }
            //修改PID和SPID
            $data['spid'] = $this->_mod->get_spid($data['pid']);
            $this->_mod->where(array('id' => array('in', $ids)))->save($data);
            $this->ajaxReturn(1, L('operation_success'), '', 'move');
        } else {
            $ids = trim($this->_request('id'), ',');
            $this->assign('ids', $ids);
            $resp = $this->fetch();
            $this->ajaxReturn(1, '', $resp);
        }
    }

    /**
     * 获取紧接着的下一级分类ID
     */
    public function ajax_getchilds() {
        $id = $this->_get('id', 'intval');
        $type = $this->_get('type', 'intval', null);
        $map = array('pid'=>$id);
        if (!is_null($type)) {
            $map['type'] = $type;
        }
        $return = $this->_mod->field('id,name')->where($map)->select();
        if ($return) {
            $this->ajaxReturn(1, L('operation_success'), $return);
        } else {
            $this->ajaxReturn(0, L('operation_failure'));
        }
    }
	
	public function city_list(){
		$id = $this->_post('id', 'intval');
		$rel_mun = $this->_post('rel_mun', 'intval');
		if($id){
			$obj = M('city');
			$city = $obj->field('id,name,latitude,recommend')->where("pid=$id")->order('id ASC')->select();
			$str = "";
			$url = __ROOT__;
				foreach($city as $val) {
				$city_count = $obj->where("pid=".$val['id'])->count('id');
					$str .= '<tr class="no node-'.$id.'">';
					$str .= '<td align="center">';
					$str .= '<input class="J_checkitem" type="checkbox" value="'.$val['id'].'">';
					$str .= '</td>';
					$str .= '<td align="center" rel="'.$rel_mun.'">'.$val['id'].'</td>';
					$str .= '<td style="padding-left:'.$rel_mun.'px;">';
					if($city_count){
					$str .= '<span class="collapsed" style="padding-left:20px;"></span>';
					}
					$str .= '├─';
					$str .= '<span class="tdedit" data-id="'.$val['id'].'" data-field="name" data-tdtype="edit">'.$val['name'].'</span>';
					$str .= '</td>';
                    $str .= '<td>';
                    $str .= '<span class="tdedit" data-id="'.$val['id'].'" data-field="latitude" data-tdtype="edit">'.$val['latitude'].'</span>';
                    $str .= '</td>';
                    if($val['recommend']) {
                        $str .= '<td align="center"><img data-tdtype="toggle" data-id="' . $val['id'] . '" data-field="recommend" data-value="' . $val['recommend'] . '" src="static/images/admin/toggle_enabled.gif" /></td>';
                    }else{
                        $str .= '<td align="center"><img data-tdtype="toggle" data-id="' . $val['id'] . '" data-field="recommend" data-value="' . $val['recommend'] . '" src="static/images/admin/toggle_disabled.gif" /></td>';
                    }
					$str .= '<td align="center">';
					$str .= '<a class="J_showdialog" data-width="520" data-id="add" data-title="添加分类" data-uri="'.$url.'/index.php?g=admin&m=city&a=add&pid='.$val['id'].'" href="javascript:;">添加子分类</a>';
					$str .= '|&nbsp;';
					$str .= '<a class="J_confirmurl" data-msg="确定要删除“'.$val['name'].'”吗？" data-uri="'.$url.'/index.php?g=admin&m=city&a=delete&id='.$val['id'].'" data-acttype="ajax" href="javascript:;">删除</a>';
					$str .= '</td>';
					$str .= '</tr>';
				}
			$this->ajaxReturn(1, L('operation_success'), $str);
		}
	}

    //ajax获取城市
    public function ajax_city(){
        if(IS_AJAX){
            $province = $this->_post('province','intval');
            $list = D('city')->city($province);
            if($list){
                $this->ajaxReturn(1, '非法提交', $list);
            }else{
                $this->ajaxReturn(0, '没有找到相关城市');
            }
        }
    }
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	
}