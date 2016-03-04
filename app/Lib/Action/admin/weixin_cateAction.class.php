<?php
class weixin_cateAction extends backendAction {
    public function _initialize() {
        parent::_initialize();
        $this->_mod = D('weixin_cate');
    }

    public function index() {
        $sort = $this->_get("sort", 'trim', 'ordid');
        $order = $this->_get("order", 'trim', 'ASC');
        $tree = new Tree();
        $tree->icon = array('│ ','├─ ','└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        $result = $this->_mod->order($sort . ' ' . $order)->select();
        $array = array();
        foreach($result as $r) {
            $r['str_status'] = '<img data-tdtype="toggle" data-id="'.$r['id'].'" data-field="status" data-value="'.$r['status'].'" src="__STATIC__/images/admin/toggle_' . ($r['status'] == 0 ? 'disabled' : 'enabled') . '.gif" />';
			if(!$r['pid']){
            $r['str_manage'] = '<a href="javascript:;" class="J_showdialog" data-uri="'.U('weixin_cate/add',array('pid'=>$r['id'])).'" data-title="'.L('add_item_cate').'" data-id="add" data-width="520" data-height="">'.L('add_item_subcate').'</a> |
                                <a href="javascript:;" class="J_showdialog" data-uri="'.U('weixin_cate/edit',array('id'=>$r['id'])).'" data-title="'.L('edit').' - '. $r['name'] .'" data-id="edit" data-width="520" data-height="">'.L('edit').'</a> |
                                <a href="javascript:;" class="J_confirmurl" data-acttype="ajax" data-uri="'.U('weixin_cate/delete',array('id'=>$r['id'])).'" data-msg="'.sprintf(L('confirm_delete_one'),$r['name']).'">'.L('delete').'</a>';
			}else{
				$r['str_manage'] = '<a href="javascript:;" class="J_showdialog" data-uri="'.U('weixin_cate/edit',array('id'=>$r['id'])).'" data-title="'.L('edit').' - '. $r['name'] .'" data-id="edit" data-width="520" data-height="">'.L('edit').'</a> |
                                <a href="javascript:;" class="J_confirmurl" data-acttype="ajax" data-uri="'.U('weixin_cate/delete',array('id'=>$r['id'])).'" data-msg="'.sprintf(L('confirm_delete_one'),$r['name']).'">'.L('delete').'</a>';
			}

            $r['parentid_node'] = ($r['pid'])? ' class="child-of-node-'.$r['pid'].'"' : '';
            $array[] = $r;
        }
        $str  = "<tr id='node-\$id' \$parentid_node>
                <td align='center'><input type='checkbox' value='\$id' class='J_checkitem'></td>
                <td align='center'>\$id</td>
                <td>\$spacer<span data-tdtype='edit' data-field='name' data-id='\$id' class='tdedit'  style='color:\$fcolor'>\$name</span></td>
				<td>\$link</span></td>
                <td align='center'><span data-tdtype='edit' data-field='ordid' data-id='\$id' class='tdedit'>\$ordid</span></td>
                <td align='center'>\$str_status</td>
                <td align='center'>\$str_manage</td>
                </tr>";
        $tree->init($array);
        $list = $tree->get_tree(0, $str);
        $this->assign('list', $list);
        //bigmenu (标题，地址，弹窗ID，宽，高)
        $big_menu = array(
            'title' => '添加菜单',
            'iframe' => U('weixin_cate/add'),
            'id' => 'add',
            'width' => '520',
            'height' => ''
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
		$weixin_cate = $this->_mod->field('id,name')->where('pid=0')->select();
		$this->assign('weixin_cate', $weixin_cate);
		$this->assign('pid', $pid);
        
    }
	
	/**
     * 添加子菜单上级默认选中本栏目
     */
    public function _before_edit() {
		$id = $this->_get('id', 'intval', 0);
		$weixin_info = $this->_mod->field('id,pid')->where(array('id'=>$id))->find();
		if($weixin_info['pid']){
			$pid = $weixin_info['pid'];
		}
        $this->assign('pid', $pid);
		$weixin_cate = $this->_mod->field('id,pid,name')->where('pid=0')->select();
		$this->assign('weixin_cate', $weixin_cate);
    }

    /**
     * 入库数据整理
     */
    protected function _before_insert($data = '') {
		$pid = $this->_post('pid', 'intval', 0);
		if(!$pid){
			$top_count = $this->_mod->where(array('pid'=>0,'status'=>1))->count('id');
			if($top_count==3){
				$this->ajaxReturn(0, '只能有3个顶级分类');
			}
		}else{
			$two_count = $this->_mod->where(array('pid'=>$pid,'status'=>1))->count('id');
			if($two_count==5){
				$this->ajaxReturn(0, '每个顶级分类的子分类只能有5个');
			}
		}
		
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
        $item_cate = $this->_mod->field('pid')->where(array('id'=>$data['id']))->find();
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
            $this->admin_log($this->_mod,$ids);
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

   
}