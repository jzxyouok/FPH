<?php
class adboardModel extends Model {

    public function lists($where){

        $count = M('adboard')->where($where)->count('id');
        import("ORG.Util.Page");
        $p = new Page($count, 20);
        $page = $p->show();

        $str = 'id,name,width,height,description,status';
        $list = M('adboard')->field($str)->where($where)->order('id DESC')->limit($p->firstRow.','.$p->listRows)->select();
        return array($list,$page);
    }
    /**
     * 检测分类是否存在
     */
    public function name_exists($name, $id=0) {
        $where = "name='" . $name . "' AND id<>'" . $id . "'";
        $result = M('adboard')->where($where)->count('id');
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    
    //获取广告位模板
    public function get_tpl_list() {
        $cfg_files = glob(LIB_PATH.'Widget/advert/*.config.php');
        $tpl_list = array();
        foreach ($cfg_files as $file) {
            $basefile = basename($file);
            $key = str_replace('.config.php', '', $basefile);
            $tpl_list[$key] = include_once($file);
            $tpl_list[$key]['alias'] = $key;
        }
        return $tpl_list;
    }

    //添加
    public function insterData($data){
        $returnData = M('adboard')->add($data);
        return $returnData;
    }

    //查询
    public function readInfo($where, $field = '*'){
        $returnData = M('adboard')->field($field)->where($where)->find();
        return $returnData;
    }

    //查询
    public function readlists($where, $field = '*'){
        $returnData = M('adboard')->field($field)->where($where)->select();
        return $returnData;
    }

    //修改
    public function updateInfo($where, $data){
        $returnData = M('adboard')->where($where)->save($data);
        return $returnData;
    }
}