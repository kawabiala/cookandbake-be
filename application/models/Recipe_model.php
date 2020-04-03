<?php

class Recipe_model extends CI_Model {

    private $recipe = array(
        'id' => -1,
        'user_id' => -1,
        'title' => '',
        'description' => null,
        'instruction' => null,
        'last_modified' => 0
    );
    
    private $table_name = 'recipe';
    
    public function __construct() {
        $this->load->database();
        
        $this->error = null;
    }
    
    public function set_value($field, $value) {
        if (array_key_exists($field, $this->recipe)) {
            $this->recipe[$field] = $value;
        }
    }
    
    public function get_value($field) {
        if (array_key_exists($field, $this->recipe)) {
            return $this->recipe[$field];
        }
    }
    
    public function get() {
        $this->db->select('id, title, description, instruction, last_modified');
        $this->db->where('user_id', $this->recipe['user_id']);
        if ($this->recipe['id'] >= 0) {
            $this->db->where('id', $this->recipe['id']);
        }
        
        $query = $this->db->get($this->table_name);
        
        return $query->result();
    }
    
    public function insert() {
        $data = array(
            'user_id' => $this->recipe['user_id'],
            'title' => $this->recipe['title'],
            'description' => $this->recipe['description'],
            'instruction' => $this->recipe['instruction'],
            'last_modified' => $this->recipe['last_modified']
        );
        
        if ($this->db->insert($this->table_name, $data)) {
            $this->recipe['id'] = $this->db->insert_id();
            return true;
        } else {
            $this->error = $this->db->error();
            return false;
        }
    }
    
    public function update() {
        $data = array(
            'title' => $this->recipe['title'],
            'description' => $this->recipe['description'],
            'instruction' => $this->recipe['instruction'],
            'last_modified' => $this->recipe['last_modified']
        );
        
        $this->db->where('id', $this->recipe['id']);
        $this->db->where('user_id', $this->recipe['user_id']);
        
        if ($this->db->update($this->table_name, $data)) {
            return true;
        } else {
            $this->error = $this->db->error();
            return false;
        }
    }
    
    public function delete() {
        $this->db->where('id', $this->recipe['id']);
        if ($this->db->delete($this->table_name)) {
        	return true;
        } else {
            $this->error = $this->db->error();
        	return false;
        }
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
    
}