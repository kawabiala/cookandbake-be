<?php

class User_model extends CI_Model {
        
    public function __construct() {
        $this->load->database();
        $this->table_name = 'user';
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
    
    public function verify_refresh_token($email, $token) {
        $this->db->select('id');
        $this->db->where('email', $email);
        $this->db->where('refresh_token', $token);
        $query = $this->db->get($this->table_name);
        
        $row = $query->row();
        return isset($row->id) ? $row->id : null;
        
    }
    
    public function save_refresh_token($id, $token) {
    	$this->db->where('id', $id);
    	$data = array(
	    	'refresh_token' => $token
    	);
    	return $this->db->update($this->table_name, $data);
    }
}