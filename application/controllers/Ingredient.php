<?php

class Ingredient extends CI_Controller {
    
    public function __construct() {
        
        parent::__construct();
        $this->load->model('ingredient_model');
    }
    
    public function get_all($recipeId = -1) {
        $this->authorize();
        $this->ingredient_model->set_value('recipe_id', $recipeId);

        $this->response($this->ingredient_model->get());
    }
    
    public function get($id = -1) {
        $this->authorize();
        $this->ingredient_model->set_value('id', $id);
        
        $this->response($this->ingredient_model->get());
    }
    
    public function insert() {
        $this->authorize();
        
        log_message('debug', 'Ingredient.insert: '.json_encode($this->input->input_stream));
            
        if ($this->validate(FALSE)) {
            $this->ingredient_model->set_value('recipe_id', $this->input->input_stream('recipe_id'));
            $this->ingredient_model->set_value('name', $this->input->input_stream('name'));
            if ($this->input->input_stream('quantity') != null) {
	            $this->ingredient_model->set_value('quantity', $this->input->input_stream('quantity'));
	        }
            $this->ingredient_model->set_value('unity', $this->input->input_stream('unity'));
        } else {
            $this->error(400, 'Data could not be inserted due to validation error'.validation_errors());
        }
        
        if ($this->ingredient_model->insert()) {
            $this->get($this->ingredient_model->get_value('id'));
        } else {
            if (is_array($this->ingredient_model->error)) {
                $this->error(400, $this->ingredient_model->error['code'] . ': ' . $this->ingredient_model->error['message']);
            } else {
                $this->error(400, 'undefined database error');
            }
        }
    }
    
    public function update() {
        $this->authorize();
        
        log_message('debug', 'Ingredient.update: '.json_encode($_POST));
            
        if ($this->validate(TRUE)) {
            $this->ingredient_model->set_value('id', $this->input->post('id'));
            $this->ingredient_model->set_value('recipe_id', $this->input->post('recipe_id'));
            $this->ingredient_model->set_value('name', $this->input->post('name'));
            $this->ingredient_model->set_value('quantity', $this->input->post('quantity'));
            $this->ingredient_model->set_value('unity', $this->input->post('unity'));
        } else {
            $this->error(400, 'Data could not be updated due to validation error'.validation_errors());
        }
        
        if ($this->ingredient_model->update()) {
            $this->get($this->ingredient_model->get_value('id'));
        } else {
            if (is_array($this->ingredient_model->error)) {
                $this->error(400, $this->ingredient_model->error['code'] . ': ' . $this->ingredient_model->error['message']);
            } else {
                $this->error(400, 'undefined database error');
            }
        }
    }
    
    public function delete($id = -1) {
        $this->authorize();

        log_message('debug', 'Ingredient.delete: '.$id);
            
        $this->ingredient_model->set_value('id', $id);
        
        if ($this->ingredient_model->delete()) {
        	$this->send_response(200, 'Resource deleted');
        } else {
            if (is_array($this->ingredient_model->error)) {
                $this->error(400, $this->ingredient_model->error['code'] . ': ' . $this->ingredient_model->error['message']);
            } else {
                $this->error(400, 'undefined database error');
            }
		}
    }
    
    private function authorize() {
        
        $user_id = $this->session->userid;
        if ($user_id == null) {
            $this->error(401, 'no valid session');
        }
    }
    
    private function validate($isUpdate = true) {
        
        $this->load->helper('form');
        $this->load->library('form_validation');
        
        if ($this->input->method() == 'put') {
            $this->form_validation->set_data($this->input->input_stream());    
        }
        
        $this->form_validation->set_rules('recipe_id', 'RezeptID', 'required');
        $this->form_validation->set_rules('name', 'Zutat', 'required');
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