<?php

class User_model_old extends CI_Model {
        
    public function __construct() {
        $this->load->database();
        $this->table_name = 'user';
        $this->error = null;
    }
    
    public function register($email, $password) {
        $data = array(
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        );
        
        return $this->db->insert($this->table_name, $data);
    }
    
    public function verify($email, $password) {
        
        $this->db->select('id, password');
        $this->db->where('email', $email);
        $query = $this->db->get($this->table_name);
        
        $row = $query->row();
        $hashed_password = isset($row->password) ? $row->password : null;
        $id = isset($row->id) ? $row->id : null;
        
        return password_verify($password, $hashed_password) ? $id : null;
    }
    
    public function verify_refresh_token($email, $token, $uuid) {
        $this->db->select('user.id');
        $this->db->from('user');
        $this->db->join('refresh_token', 'refresh_token.user_id = user.id');
        $this->db->where('email', $email);
        $this->db->where('refresh_token.refresh_token', $token);
        $this->db->where('refresh_token.uuid', $uuid);
        $query = $this->db->get();
        
        if ($query == false) {
        	$this->error = $this->db->error();
        	$row = null;
        } else {
	        $row = $query->row();
	    }
        return isset($row->id) ? $row->id : null;
        
    }
    
    public function save_refresh_token($id, $token, $uuid) {
    	if ($this->insert_refresh_token($id, $token, $uuid)) {
    		return true;
    	} else {
    		return $this->update_refresh_token($id, $token, $uuid);
    	}
    }
    
    public function save_temp_code($email, $code) {
    	$data = array(
    		'temp_code' => $code,
    		'temp_code_valid_until' => date('Y-m-d H:i:s', strtotime('+ 2 days'))
    	);
    	$this->db->where('email', $email);
    	
    	$result = $this->db->update($this->table_name, $data);
    	$this->error = $this->db->error();
    	return $result;
    }
    
    public function verify_temp_code($email, $code) {
    	$this->db->select('id', 'temp_code_valid_until');
    	$this->db->where('email', $email);
    	$this->db->where('temp_code', $code);
    	$query = $this->db->get($this->table_name);
    	
    	if ($query == false) {
    		$this->error = $this->db->error();
    		return false;
    	} else {
    		$row = $query->row();
    		if (isset($row->id) && $row->temp_code_valid_until > date('Y-m-d H:i:s')) {
    			$this->db->where('id', $row->id);
    			$data = array('confirmed' => true);
    			return $this->db->update($this->table_name);
    		} else {
    			return false;
    		}
    	}
    }
    
    private function update_refresh_token($id, $token, $uuid) {
    	$this->db->where('user_id', $id);
    	$this->db->where('uuid', $uuid);
    	$data = array(
	    	'refresh_token' => $token
    	);
    	return $this->db->update('refresh_token', $data);
    }
    
    private function insert_refresh_token($id, $token, $uuid) {
        $data = array(
            'user_id' => $id,
            'refresh_token' => $token,
            'uuid' => $uuid
        );
        
        $result = $this->db->insert('refresh_token', $data);
        $this->error = $this->db->error();
        return $result;
    }
}