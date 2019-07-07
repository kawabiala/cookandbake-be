<?php

class Recipe extends CI_Controller {
    
    public function __construct() {
        
        parent::__construct();
        $this->load->model('recipe_model');
    }
    
    public function get_all() {
        $this->authorize();
        $this->response($this->recipe_model->get());
    }
    
    public function get($id = -1) {
        $this->authorize();
        $this->recipe_model->set_value('id', $id);
        
        $this->response($this->recipe_model->get());
    }
    
    public function insert() {
        $this->authorize();
        
        log_message('debug', 'Recipe.insert: '.json_encode($this->input->input_stream));
            
        if ($this->validate(FALSE)) {
            $this->recipe_model->set_value('title', $this->input->input_stream('title'));
            $this->recipe_model->set_value('description', $this->input->input_stream('description'));
            $this->recipe_model->set_value('instruction', $this->input->input_stream('instruction'));
        } else {
            $this->error(400, 'Data could not be inserted due to validation error');
        }
        
        if ($this->recipe_model->insert()) {
            $this->get($this->recipe_model->get_value('id'));
        } else {
            if (is_array($this->recipe_model->error)) {
                $this->error(400, $this->recipe->error['code'] + ': ' + $this->recipe->error['message']);
            } else {
                $this->error(400, 'undefined database error');
            }
        }
    }
    
    public function update() {
        $this->authorize();
        
        log_message('debug', 'Recipe.update: '.json_encode($_POST));
            
        if ($this->validate(TRUE)) {
            $this->recipe_model->set_value('id', $this->input->post('id'));
            $this->recipe_model->set_value('title', $this->input->post('title'));
            $this->recipe_model->set_value('description', $this->input->post('description'));
            $this->recipe_model->set_value('instruction', $this->input->post('instruction'));
        } else {
            $this->error(400, 'Data could not be updated due to validation error');
        }
        
        if ($this->recipe_model->update()) {
            $this->get($this->recipe_model->get_value('id'));
        } else {
            if (is_array($this->recipe_model->error)) {
                $this->error(400, $this->recipe_model->error['code'] + ': ' + $this->recipe_model->error['message']);
            } else {
                $this->error(400, 'undefined database error');
            }
        }
    }
    
    public function delete($id = -1) {
        $this->authorize();
        $this->error(400, 'not implemented');

    }
    
    private function authorize() {
        
        $user_id = $this->session->userid;
        if ($user_id == null) {
            $this->error(401, 'no valid session');
        } else {
            $this->recipe_model->set_value('user_id', $user_id);
        }
    }
    
    private function validate($isUpdate = true) {
        
        $this->load->helper('form');
        $this->load->library('form_validation');
        
        if ($this->input->method() == 'put') {
            $this->form_validation->set_data($this->input->input_stream());    
        }
        
        $this->form_validation->set_rules('title', 'Rezeptname', 'required');
        if ($isUpdate) {
            $this->form_validation->set_rules('id', 'ID', 'required');
        }
        
        return ($this->form_validation->run() === TRUE); 
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