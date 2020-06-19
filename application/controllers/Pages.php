<?php

class Pages extends CI_Controller {

	public function index() {		
		$language = $this->getAcceptedLanguage();
		$this->redirect($language, 'home');
	}

    public function view($page = 'home', $language = '', $region = '') {
        
        if ($language == '') {
			$language = $this->getAcceptedLanguage();
			$this->redirect($language, $page);
			exit();
        }
        
        $path = $this->getLocalizedPagePath($page, $language, $region);
/*        
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
*/        
        //if (! file_exists(APPPATH.'views/pages/'.$page.'.php')) {
        /*
        if (! file_exists(APPPATH."views/$path")) {
            show_404();
        }
        */
        
        log_message('debug', "Chosen path: $path");

        // Data-Array handed to views
        $data['resource_path'] = $this->getResourcePath($page);
        
        $this->output->set_header('Set-Cookie: cookandbake=null; max-age=0');
        
        //$this->load->view('pages/'.$page, $data);
        $this->load->view($path, $data);
    }
    
    private function getResourcePath($page = '') {
    	$BASE_SIZE = 3;
    	$resource_path = "resources";
    	
    	$request_uri = $_SERVER['REQUEST_URI'];
    	$request_uri = rtrim(ltrim($request_uri, "/"), "/");
        $segments = explode("/", $request_uri);
        $size = count($segments);
        
    	$add_folder_up = $size - $BASE_SIZE;
    	
        $is_index = ($page == $segments[$size - 1]);
        if ($page == $segments[$size - 1]) {
        	$add_folder_up--;
        }
        
        for ($i = 0; $i < $add_folder_up; $i++) {
        	$resource_path = "../$resource_path";
        }
        
        return $resource_path;
    }
    
    private function getAcceptedLanguage() {
		$language = 'de';
		if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
			$language_string = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
			$language_segments = preg_split("/[;|,]/", $language_string);
			if (count($language_segments) > 0) {
				$language = rtrim(ltrim($language_segments[0]));
			}
		}
		
		return $language;
    }
    
    private function getLocalizedPagePath($page, $language, $region) {
    	$default_locale = 'de';
    	
        $path = '';
        $default_path = "pages/$default_locale/$page";
        $language_path = '';
        $region_path = '';
        
        if ($language != '') {
        	$language_path = "pages/".strtolower($language)."/$page";
        }
        
        if ($language != '' && $region != '') {
        	$region_path = "pages/".strtolower($language)."_".strtoUpper($region)."/$page";
        }

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
        
        return $path;
    }
    
    private function redirect($language, $page) {
        $this->output->set_header('Set-Cookie: cookandbake=null; max-age=0');
        $this->output->set_header("location: $language/$page");
        $this->output->set_status_header(301);
        $this->output->_display();
    }
    
    private function sendPage() {
    }
}