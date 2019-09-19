<?php

class Auth extends CI_Controller {
    
    public function __construct() {
        
        parent::__construct();
        $this->load->model('user_model');
        
        $this->load->library('email');
        $this->load->helper('form');
        $this->load->library('form_validation');
        
    }
    
    public function view($page = 'home') {
        
        if (! file_exists(APPPATH.'views/auth/'.$page.'.php')) {
            show_404();
        }
        
        // Data-Array handed to views
        $data = null;
        
        //$this->load->view($page, $data);
        $this->load->view('auth/'.$page, $data);
    }
    
    public function register($page = 'register') {
        
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Passwort', 'required');
        
        if ($this->form_validation->run() === FALSE) {
        	$this->error(400, 'Provided credentials could not be validated (e.g. email address not valid)');
        } else {
            $isInserted = $this->user_model->register(
                $this->input->post('email'), 
                $this->input->post('password'));

            if ($isInserted) {
            	$this->sendEmail($this->input->post('email'), 'Welcome', 'Welcome to Cook and Bake');
            	$data['msg'] = $this->input->post('email') . ' registered.';
                $this->response($data);
            } else {
	        	$this->error(400, 'Email already exists');
            }
        }
    }
    
    public function login($page = 'login') {
        
        $this->form_validation->set_rules('email', 'Email', 'required');
        $this->form_validation->set_rules('password', 'Passwort', 'required');
        $this->form_validation->set_rules('uuid', 'UUID', 'required');
        
        if ($this->form_validation->run() === FALSE) {
        	$this->error(400, 'Provided credentials could not be validated');
        } else {
            $id = $this->user_model->verify(
                $this->input->post('email'), 
                $this->input->post('password'));
            
            if ($id != null) {

                $data['refresh_token'] = $this->generateToken();
                
                if ($this->user_model->save_refresh_token(
                	$id, 
                	$data['refresh_token'],
                	$this->input->post('uuid'))) 
                {
	                $this->session->userid = $id;
	                $this->response($data);
                } else {
	            	$this->error(400, 'Error while creating refresh_token: '.$this->user_model->error['message']);
                }
            } else {
            	$this->error(401, 'Provided credentials could not be verified');
            }
        }
    }
    
    public function loginWithRefreshToken() {
        $this->form_validation->set_rules('email', 'Email', 'required');
        $this->form_validation->set_rules('refresh_token', 'Token', 'required');
        $this->form_validation->set_rules('uuid', 'UUID', 'required');
        
        if ($this->form_validation->run() === FALSE) {
        	$this->error(400, 'Provided token could not be validated');
        } else {
            $id = $this->user_model->verify_refresh_token(
                $this->input->post('email'), 
                $this->input->post('refresh_token'),
                $this->input->post('uuid'));
            
            if ($id != null) {

                $data['refresh_token'] = $this->generateToken();
                
                if ($this->user_model->save_refresh_token(
                	$id, 
                	$data['refresh_token'], 
                	$this->input->post('uuid')))
                {
	                $this->session->userid = $id;
	                $this->response($data);
                } else {
	            	$this->error(400, 'Error while creating refresh_token');
                }
            } else {
            	$this->error(401, 'Provided token could not be verified '.$this->user_model->error['message']);
            }
        }
    }
    
    public function changePassword() {
//    	$msg = '<a href="http://www.cookandbake.de">Click here</a>';
    	$msg = '<a href="https://www.pingwinek.de/cookandbake">Click here</a>';
    	$this->sendEmail('jens.reufsteck@gmail.com', 'Change Password', $msg);
    }
    
    private function sendEmail($to, $subject, $msg) {
		$this->email->from('cookandbake@pingwinek.de', 'Cook and Bake');
		$this->email->to($to);
		$this->email->subject($subject);
		$this->email->message($msg);
		$this->email->set_mailtype('html');
		$this->email->send();
    }
    
    private function generateToken() {
    	return bin2hex(random_bytes(64));
    }
    
    private function response($msg) {
        if (is_array($msg)) {
            $this->send_response(200, $msg);
        } else {
            $this->error(400, 'Malformed response body: ' + $msg);
        }
    }
    
    private function error($code, $msg) {
        $msg = array("error" => $msg);
        $this->send_response($code, $msg);
    }
    
    private function send_response($code, $msg) {
        $this->output
            ->set_status_header($code)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($msg))
            ->_display();
        exit();
    }
    
}