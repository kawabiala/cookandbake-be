<?php

class Auth extends CI_Controller {
    
    public function __construct() {
        
        parent::__construct();
        $this->load->model('user_model');
        
        $this->load->library('email');
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
        
        $this->load->helper('form');
        $this->load->library('form_validation');
        
        $this->form_validation->set_rules('email', 'Email', 'required');
        $this->form_validation->set_rules('password', 'Passwort', 'required');
        
        if ($this->form_validation->run() === FALSE) {
            $msg = array('error' => 'Provided credentials could not be validated');
            
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($msg));
        } else {
            $isInserted = $this->user_model->register(
                $this->input->post('email'), 
                $this->input->post('password'));

            if ($isInserted) {
                $this->load->view('auth/register');
            } else {
            $msg = array('error' => 'Email already exists');
            
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($msg));
            }
        }
    }
    
    public function login($page = 'login') {
        
        $this->load->helper('form');
        $this->load->library('form_validation');
        
        $this->form_validation->set_rules('email', 'Email', 'required');
        $this->form_validation->set_rules('password', 'Passwort', 'required');
        
        if ($this->form_validation->run() === FALSE) {
            $msg = array('error' => 'Provided credentials could not be validated');
            
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($msg));
        } else {
            $id = $this->user_model->verify(
                $this->input->post('email'), 
                $this->input->post('password'));
            log_message('debug', $this->input->as_json);
            
            if ($id != null) {
                $this->session->userid = $id;
                
                $this->email->from('cookandbake@pingwinek.de', 'Cook and Bake');
                $this->email->to('jens.reufsteck@gmail.com');
                $this->email->subject('cookandbake');
                $this->email->message('user with email ' . $this->input->post('email') . ' logged in.');
                $this->email->send();
                
                $data['authenticated'] = 'isAuthenticated';
                $this->load->view('auth/login', $data);
            } else {
            $msg = array('error' => 'Provided credentials could not be verified');
            
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($msg));
            }
        }
    }
}