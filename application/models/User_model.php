<?php

class User_model extends CI_Model {
	
	public function __construct() {
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

		return $this->evaluateResult($result);
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

		$return_val = $this->evaluateResult($result);
		if ($return_val) {
			$this->fillInUser($result);
		}
		return $return_val;
	}
	
	public function updateTempCode() {
		$this->db->where('email', $this->user['email']);
		$data = array(
			'temp_code' => $this->user['temp_code'],
			'temp_code_valid_until' => $this->user['temp_code_valid_until']
		);
		return $this->evaluateResult($this->db->update($this->user_table, $data));
	}
	
	public function updateConfirmed() {
		$this->db->where('id', $this->user['id']);
		$data = array(
			'confirmed' => $this->user['confirmed']
		);
		return $this->evaluateResult($this->db->update($this->user_table, $data));
	}
	
	public function updatePassword() {
		$this->db->where('id', $this->user['id']);
		$data = array(
			'password' => $this->user['hashed_password']
		);
		return $this->evaluateResult($this->db->update($this->user_table, $data));
	}

	public function insertToken() {
		$data = array(
			'refresh_token' => $this->user['refresh_token'],
			'uuid' => $this->user['uuid'],
			'user_id' => $this->user['id']
		);
		
		$result = $this->db->insert($this->token_table, $data);

		return $this->evaluateResult($result);
	}
	
	/*
	If the update is called from login with refresh token, we need
	to check the old refresh token again in the database update, in order
	to prevent racing conditions: If refresh is called twice with the same
	token within short period of time, the token of the second request
	might get verified, before the first request has updated it in the db.
	Without checking again directly during update, the second request would also
	succeed in updating the token, resulting in the response to the first request
	holding	a token, that has already been updated again (and thus invalidated).
	*/
	public function updateToken($old_refresh_token = null) {
		$data = array(
			'refresh_token' => $this->user['refresh_token']
		);
		$this->db->where('uuid', $this->user['uuid']);
		$this->db->where('user_id', $this->user['id']);
		if (! is_null($old_refresh_token)) {
			$this->db->where('refresh_token', $old_refresh_token);
		}
		
		$result = $this->db->update($this->token_table, $data);

		return $this->evaluateResult($result);
	}

	// all tokens for given user
	public function deleteRefreshTokens() {
		$data = array(
			'user_id' => $this->user['id']
		);
		
		$result = $this->db->delete($this->token_table, $data);
		
		return $result == false ? false : true;
	}
	
	// token for given uuid and user
	public function deleteRefreshToken() {
		$data = array(
			'user_id' => $this->user['id'],
			'uuid' => $this->user['uuid']
		);
		
		$result = $this->db->delete($this->token_table, $data);
		
		return $result == false ? false : true;
	}
	
	public function deleteUser() {
		$data = array(
			'id' => $this->user['id']
		);
		
		$result = $this->db->delete($this->user_table, $data);
		
		return $result == false ? false : true;
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
	
	private function evaluateResult($result) {
		if ($result == false) {
			$this->error = $this->db->error();
			return false;
		}

		if (method_exists($result, 'num_rows') && $result->num_rows() == 0) {
			return false;
		}
		
		if (is_int($result) && $result == 0) {
			return false;
		}
		
		if ($this->db->affected_rows() == 0) {
			return false;
		}
		
		return true;
	}
}