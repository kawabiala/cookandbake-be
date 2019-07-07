<?php

class Api extends CI_Controller {
    
    public function view($page = 'home') {
        
        if (! file_exists(APPPATH.'views/api/'.$page.'.php')) {
            show_404();
        }
        
        // Data-Array handed to views
        $data = [];
        $data['uid'] = $this->session->userid;
        
        //$this->load->view($page, $data);
        $this->load->view('api/'.$page, $data);
    }
}