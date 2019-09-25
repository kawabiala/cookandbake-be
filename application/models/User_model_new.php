<?php

class User_model_new extends CI_Model {
	
	public function _construct() {
		$this->load->database();
	}
	
	public $error = null;
	
	private $user = array(
		'id' => null,
		'email' => null,
		'hashed_password' => null,
		'temp_code' => null,
		'temp_code_valid_until' => null,
		'confirmed' => 0,
		'uuid' => null,
		'refresh_token' => null
	);
	
	private $user_table = 'user';
	private $token_table = 'refresh_token';
	
	public function setValue($key, $value) {
		$this->user[$key] = $value;
	}
	
	public function getValue($key) {
		return $this->user[$key];
	}
	
	public function newUser() {
		$data = array(
			'email' => $this->user['email'],
			'password' => $this->user['hashed_password'],
			'temp_code' => $this->user['temp_code'],
			'temp_code_valid_until' => $this->user['temp_code_valid_until']
		);
		$result = $this->db->insert($this->user_table, $data);
		if ($result == false) {
			$this->error = $this->db->error();
		}
		return $result;
	}
	
	public function getUser() {
		$this->db->select('user.id AS id, email, password, temp_code, temp_code_valid_until, confirmed, uuid, refresh_token');
		$this->db->where('email', $this->user['email']);
		$this->db->from($this->user_table);
		$this->db->join(
			$this->token_table, 
			'user.id = refresh_token.user_id AND uuid = \''.$this->user['uuid'].'\'', 
			'left outer');
		
		$result = $this->db->get();
		
		if ($result == false) {
			$this->error = $this->db->error();
			return false;
		} else {
			$this->fillInUser($result);
			return true;
		}
	}
	
	public function updateTempCode() {
		$this->db->where('id', $this->user['id']);
		$data = array(
			'temp_code' => $this->user['temp_code'],
			'temp_code_valid_until' => $this->user['temp_code_valid_until']
		);
		return $this->db->update($this->user_table, $data);
	}
	
	public function updateConfirmed() {
		$this->db->where('id', $this->user['id']);
		$data = array(
			'confirmed' => $this->user['confirmed']
		);
		return $this->db->update($this->user_table, $data);
	}
	
	public function updatePassword() {
		$this->db->where('id', $this->user['id']);
		$data = array(
			'password' => $this->user['hashed_password']
		);
		return $this->db->update($this->user_table, $data);
	}
	
	public function insertOrUpdateToken() {
		$token = $this->user['refresh_token'];
		$uuid = $this->user['uuid'];
		$user_id = $this->user['id'];
		
		if ($this->insertToken($user_id, $uuid, $token)) {
			return true;
		} else {
			return $this->updateToken($user_id, $uuid, $token);
		}
	}
	
	private function insertToken($user_id, $uuid, $token) {
		$data = array(
			'refresh_token' => $token,
			'uuid' => $uuid,
			'user_id' => $user_id
		);
		return $this->db->insert($this->token_table, $data);
	}
	
	private function updateToken($user_id, $uuid, $token) {
		$data = array('refresh_token' => $token);
		$this->db->where('uuid', $uuid);
		$this->db->where('user_id', $user_id);
		$result = $this->db->update($this->token_table, $data);
		return ($result == false || $result = 0) ? false : true;
	}
	
	private function fillInUser($result) {
		$row = $result->row();
		$this->user['id'] = isset($row->id) ? $row->id : null;
		$this->user['email'] = isset($row->email) ? $row->email : null;
		$this->user['hashed_password'] = isset($row->password) ? $row->password : null;
		$this->user['temp_code'] = isset($row->temp_code) ? $row->temp_code : null;
		$this->user['temp_code_valid_until'] = isset($row->temp_code_valid_until) ? $row->temp_code_valid_until : null;
		$this->user['confirmed'] = isset($row->confirmed) ? $row->confirmed : 0;
		$this->user['uuid'] = isset($row->uuid) ? $row->uuid : null;
		$this->user['refresh_token'] = isset($row->refresh_token) ? $row->refresh_token : null;
	}
}