<?php

class Pages extends CI_Controller {
    
    public function view($page = 'home') {
        
        if (! file_exists(APPPATH.'views/pages/'.$page.'.php')) {
            show_404();
        }
        
        // Data-Array handed to views
        $data = null;
        
        //$this->load->view($page, $data);
        $this->load->view('pages/'.$page, $data);
    }
}