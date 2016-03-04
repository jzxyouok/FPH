<?php
class indexAction extends frontendAction {
    
    public function index() {

        $this->_config_seo();
        $this->display();
    }
}