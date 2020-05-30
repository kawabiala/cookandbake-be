<?php

class Pages extends CI_Controller {

    public function view($page = 'home', $language = '', $region = '') {
        
        $path = '';
        $default_path = "pages/$page";
        $language_path = '';
        $region_path = '';
        
        if ($language != '') {
        	$language_path = "pages/".strtolower($language)."/$page";
        }
        
        if ($language != '' && $region != '') {
        	$region_path = "pages/".strtolower($language)."_".strtoUpper($region)."/$page";
        }

//        $path = "pages/$path.php";
        log_message('debug', "Default path: $default_path");
        log_message('debug', "Language path: $language_path");
        log_message('debug', "Region path: $region_path");
        
        if (file_exists(APPPATH."views/$region_path.php")) {
        	$path = $region_path;
        } else if (file_exists(APPPATH."views/$language_path.php")) {
        	$path = $language_path;
        } else if (file_exists(APPPATH."views/$default_path.php")) {
        	$path = $default_path;
        } else {
            show_404();
        }
        
        //if (! file_exists(APPPATH.'views/pages/'.$page.'.php')) {
        /*
        if (! file_exists(APPPATH."views/$path")) {
            show_404();
        }
        */
        
        log_message('debug', "Chosen path: $path");

        // Data-Array handed to views
        $data = null;
        
        $this->output->set_header('Set-Cookie: cookandbake=null; max-age=0');
        
        //$this->load->view('pages/'.$page, $data);
        $this->load->view($path, $data);
    }
}