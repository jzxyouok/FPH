<?php
class tAction extends frontendAction {
    
    public function t() {
		$d = $this->_get('d',intval);
	    $urlToEncode = C('website')."/index.php?g=weixin&m=myshare&a=register&share_id=".md5($d)."";
		header("Location: $urlToEncode");
    }
    

}