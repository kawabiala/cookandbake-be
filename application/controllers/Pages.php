<?php

class Pages extends CI_Controller {

    public function view($page = 'home') {
        
        if (! file_exists(APPPATH.'views/pages/'.$page.'.php')) {
            show_404();
        }
        
        // Data-Array handed to views
        $data = null;
        
        $this->output->set_header('Set-Cookie: cookandbake=null; max-age=0');
        
        //$this->load->view($page, $data);
        $this->load->view('pages/'.$page, $data);
    }
}