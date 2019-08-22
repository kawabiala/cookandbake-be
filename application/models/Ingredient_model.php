<?php

class Ingredient_model extends CI_Model {

    private $ingredient = array(
        'id' => -1,
        'recipe_id' => -1,
        'name' => '',
        'quantity' => 0,
        'unity' => null
    );
    
    private $table_name = 'ingredient';
    
    public function __construct() {
        $this->load->database();
        
        $this->error = null;
    }
    
    public function set_value($field, $value) {
        if (array_key_exists($field, $this->ingredient)) {
            $this->ingredient[$field] = $value;
        }
    }
    
    public function get_value($field) {
        if (array_key_exists($field, $this->ingredient)) {
            return $this->ingredient[$field];
        }
    }
    
    public function get() {
        $this->db->select('id, recipe_id, name, quantity, unity');
        if ($this->ingredient['recipe_id'] >= 0) {
        	$this->db->where('recipe_id', $this->ingredient['recipe_id']);
        } 
        if ($this->ingredient['id'] >= 0) {
            $this->db->where('id', $this->ingredient['id']);
        }
        
        $query = $this->db->get($this->table_name);
        
        return $query->result();
    }
    
    public function insert() {
        $data = array(
            'recipe_id' => $this->ingredient['recipe_id'],
            'name' => $this->ingredient['name'],
            'quantity' => $this->ingredient['quantity'],
            'unity' => $this->ingredient['unity']
        );
        
        if ($this->db->insert($this->table_name, $data)) {
            $this->ingredient['id'] = $this->db->insert_id();
            return true;
        } else {
            $this->error = $this->db->error();
            return false;
        }
    }
    
    public function update() {
        $data = array(
            'name' => $this->ingredient['name'],
            'quantity' => $this->ingredient['quantity'],
            'unity' => $this->ingredient['unity']
        );
        
        $this->db->where('id', $this->ingredient['id']);
        //$this->db->where('recipe_id', $this->ingredient['recipe_id']);
        
        if ($this->db->update($this->table_name, $data)) {
            return true;
        } else {
            $this->error = $this->db->error();
            return false;
        }
    }
    
    public function delete() {
        $this->db->where('id', $this->ingredient['id']);
        if ($this->db->delete($this->table_name)) {
        	return true;
        } else {
            $this->error = $this->db->error();
        	return false;
        }
    }
        
}