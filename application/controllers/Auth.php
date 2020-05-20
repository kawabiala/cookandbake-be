<?php

class Auth extends CI_Controller {
    
    public function __construct() {
        
        parent::__construct();
        $this->load->model('user_model');
        
        $this->load->library('email');
        $this->load->helper('form');
        $this->load->library('form_validation');
        
    }
    
    private const TOKEN_LENGTH = 64;
    private const TEMP_CODE_LENGTH = 16;
    
    // registration with sending an email with confirmation code
    public function register() {
        
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Passwort', 'required');
        $this->form_validation->set_rules('dataprotection', 'Datenschutz', 'required');
        
        if ($this->form_validation->run() === FALSE) {
        	$this->error(400, 'Validation error');
        }
        
        if (strtolower($this->input->post('dataprotection')) == 'true') {
        	// TODO: save acceptance or acceptance date to db, i.e. hand over to model
        } else {
        	$this->error(404, 'Dataprotection error');
        }         
        
        $this->user_model->setValue('email', $this->input->post('email'));
        $this->user_model->setValue(
        	'hashed_password', 
        	password_hash($this->input->post('password'), PASSWORD_DEFAULT));

        if (! $this->user_model->newUser())  {
			$this->error(409, 'Duplicate');
        }

		$this->sendConfirmationMail($this->input->post('email'));
		$data['msg'] = $this->input->post('email') . ' registered.';
		$this->responseWithCode(201, $data);
    }
    
    // Confirms registration by handing in the temporary code
    public function confirmRegistration() {
        $this->form_validation->set_rules('email', 'Email', 'required');
        $this->form_validation->set_rules('temp_code', 'Temporärer Code', 'required');

        if ($this->form_validation->run() === FALSE) {
        	$this->error(400, 'Validation error');
        }
        
        $this->loadUser();
        
        if ($this->user_model->getValue('temp_code') != $this->input->post('temp_code')) {
        	log_message('error', 'db: ' . $this->user_model->getValue('temp_code') . ' provided: ' . $this->input->post('temp_code'));
        	$this->error(404, 'Verification error');
        }
        
        if ($this->user_model->getValue('temp_code_valid_until') < date('Y-m-d H:i:s')) {
        	$this->error(408, 'Code timed out');
        }
        
        // if the email is confirmed, we don't care for correct or valid code
        if ($this->user_model->getValue('confirmed') == 1) {
			$data['confirmed'] = 'confirmed';
			$this->response($data);
        }
        
        $this->user_model->setValue('confirmed', 1);
        if (! $this->user_model->updateConfirmed()) {
        	$this->error(400, 'Confirmation error');
        }
        
        $data['confirmed'] = 'confirmed';
        $this->response($data);
    }
    
    // regular login with password
    public function loginWithPassword() {
        
        // validate input
        $this->form_validation->set_rules('email', 'Email', 'required');
        $this->form_validation->set_rules('password', 'Passwort', 'required');
        $this->form_validation->set_rules('uuid', 'UUID', 'required');
        
        if ($this->form_validation->run() === FALSE) {
        	$this->error(400, 'Validation error');
        }
        
        // load User
        $this->loadUser();
			
        // verify password
		if (! $this->verify_password()) {
			$this->error(404, 'Verification error');
		}
		
    	if ($this->user_model->getValue('confirmed') == 0) {
    		$this->sendConfirmationMail($this->input->post('email'));
    		$this->error(206, 'Please confirm the account');
    	}		
		
		// refresh token
		$refresh_token = $this->generateToken();
		$this->user_model->setValue('refresh_token', $refresh_token);
		
		/*
		If logging in from a new device for the first time, loadUser will set uuid to null,
		as there is no entry for the uuid in the db.
		*/
		if ($this->user_model->getValue('uuid') == null) {
			$this->user_model->setValue('uuid', $this->input->post('uuid'));
			if (! $this->user_model->insertToken()) {
				$this->error(400, 'Error while creating token');
			}
		} else {
			if (! $this->user_model->updateToken()) {
				$this->error(400, 'Error while creating token');
			}
		}

		// save userid into session and send token
		$this->session->userid = $this->user_model->getValue('id');
		$data['refresh_token'] = $refresh_token;
		$this->response($data);
    }
    
    // login based on refresh token
    public function loginWithRefreshToken() {
        $this->form_validation->set_rules('email', 'Email', 'required');
        $this->form_validation->set_rules('refresh_token', 'Token', 'required');
        $this->form_validation->set_rules('uuid', 'UUID', 'required');
        
        if ($this->form_validation->run() === FALSE) {
        	$this->error(400, 'Validation error');
        }
        
        $this->loadUser();
        
        // verify token
        if ($this->user_model->getValue('refresh_token') != $this->input->post('refresh_token')) {
        	log_message('debug', 'token provided: ' . $this->input->post('refresh_token') . ' local: ' . $this->user_model->getValue('refresh_token'));
        	$this->error(404, 'Verification error');
        }
        
    	if ($this->user_model->getValue('confirmed') == 0) {
    		$this->error(206, 'Please confirm the account');
    	}
        
		// refresh token
		$refresh_token = $this->generateToken();
		$this->user_model->setValue('refresh_token', $refresh_token);
		
		// update refresh token
		if (! $this->user_model->updateToken($this->input->post('refresh_token'))) {
			$this->error(400, 'Error while creating token');
		}

		// save userid into session and send token
		$this->session->userid = $this->user_model->getValue('id');
		$data['refresh_token'] = $refresh_token;
		$this->response($data);
    }
    
    // Indicates, that a new password is needed
    // Triggers sending a temporary code
    public function lostPassword() {
        $this->form_validation->set_rules('email', 'Email', 'required');

        if ($this->form_validation->run() === FALSE) {
        	$this->error(400, 'Validation error');
        }
        
        $this->loadUser();
        
        $this->user_model->setValue(
        	'temp_code', $this->generateTempCode());
        $this->user_model->setValue(
        	'temp_code_valid_until', date('Y-m-d H:i:s', strtotime('+ 2 days')));
        	
        if (! $this->user_model->updateTempCode()) {
        	$this->error(400, 'Something went wrong');
        }

		$this->sendEmail(
			$this->input->post('email'), 
			'Set New Password', 
			$this->getLostPasswordMailBody($this->user_model->getValue('temp_code')));
		$data['msg'] = 'New password mail for ' . $this->input->post('email') . ' sent.';
		$this->response($data);
    }
    
    // Authenticates with a temporary code and sets the new password
    public function newPassword() {
        $this->form_validation->set_rules('email', 'Email', 'required');
        $this->form_validation->set_rules('temp_code', 'Temporärer Code', 'required');
        $this->form_validation->set_rules('password', 'Passwort', 'required');
        
        if ($this->form_validation->run() === FALSE) {
        	$this->error(400, 'Validation error');
        }
        
        $this->loadUser();
        
        if ($this->user_model->getValue('temp_code') != $this->input->post('temp_code')) {
        	$this->error(404, 'Verification error');
        }
        
        if ($this->user_model->getValue('temp_code_valid_until') < date('Y-m-d H:i:s')) {
        	$this->error(408, 'Provided code not valid anymore');
        }
        
        $this->user_model->setValue(
        	'hashed_password',
        	password_hash($this->input->post('password'), PASSWORD_DEFAULT));
        	
        if (! $this->user_model->updatePassword()) {
        	$this->error(400, 'Error wail updating password');
        }
        
        $data['new password'] = 'set';
        $this->response($data);
    }
    
    // authenticate with email and password and set new password
    public function changePassword() {
        $this->form_validation->set_rules('email', 'Email', 'required');
        $this->form_validation->set_rules('password', 'Altes Passwort', 'required');
        $this->form_validation->set_rules('new_password', 'Neues Passwort', 'required');

        if ($this->form_validation->run() === FALSE) {
        	$this->error(400, 'Validation error');
        }
        
        $this->loadUser();
        
		if (!password_verify(
			$this->input->post('password'),
			$this->user_model->getValue('hashed_password'))) 
		{
			$this->error(404, 'Verification error');
		}
		
		/*
    	if ($this->user_model->getValue('confirmed') == 0) {
    		$this->error(402, 'Account not confirmed');
    	}
    	*/
    	
    	$this->user_model->setValue(
    		'hashed_password',
    		password_hash($this->input->post('new_password'), PASSWORD_DEFAULT));
    		
    	if (! $this->user_model->updatePassword()) {
    		$this->error(400, 'Password could not be updated');
    	}
    	
        $data['new password'] = 'set';
        $this->response($data);
    }
    
    public function logout() {
        
        // validate input
        $this->form_validation->set_rules('email', 'Email', 'required');
        
        if ($this->form_validation->run() === FALSE) {
        	$this->error(400, 'Validation error');
        }
        
        $this->loadUser();
        
        $this->user_model->deleteRefreshToken();
        $this->doLogout();
        
        $data['Logout'] = 'OK';
        $this->response($data);
}
    
    public function unsubscribe() {
        
        // validate input
        $this->form_validation->set_rules('email', 'Email', 'required');
        $this->form_validation->set_rules('password', 'Passwort', 'required');
        
        if ($this->form_validation->run() === FALSE) {
        	$this->error(400, 'Validation error');
        }
        
        $this->loadUser();
        
        // verify
        if (! $this->verify_password()) {
        	$this->error(404, 'Verification error');
        }
        
        // delete
        $this->user_model->deleteRefreshTokens();
        $this->user_model->deleteUser();
        $this->doLogout();
        
        $data['Unsubscribe'] = 'OK';
        $this->response($data);
    }
    
    private function loadUser() {
		$this->user_model->setValue('email', $this->input->post('email'));
		$this->user_model->setValue('uuid', $this->input->post('uuid'));
		
		if (!$this->user_model->getUser()) {
			log_message('error', $this->user_model->error['message']);
			$this->error(404, 'Verification error');
		}
    }
    
    private function verify_password() {
    	return password_verify(
			$this->input->post('password'),
			$this->user_model->getValue('hashed_password'));
    }
     
    private function login() {
    	
		// refresh token
		$refresh_token = $this->generateToken();
		$this->user_model->setValue('refresh_token', $refresh_token);
		
		/*
		If logging in from a new device for the first time, loadUser will set uuid to null,
		as there is no entry for the uuid in the db.
		*/
		if ($this->user_model->getValue('uuid') == null) {
			$this->user_model->setValue('uuid', $this->input->post('uuid'));
			if (! $this->user_model->insertToken()) {
				$this->error(400, 'Error while creating token');
			}
		} else {
			if (! $this->user_model->updateToken($this->input->post('refresh_token'))) {
				$this->error(400, 'Error while creating token');
			}
		}

		// save userid into session and send token
		$this->session->userid = $this->user_model->getValue('id');
		$data['refresh_token'] = $refresh_token;
		$this->response($data);
    }
    
    private function doLogout() {
    	$this->session->unset_userdata['userid'];
    }
    
    private function sendConfirmationMail($to) {
    	$this->updateTempCode();
    	$this->sendEmail(
    		$to, 
    		'Bestätigungsmail', 
    		$this->getConfirmMailBody($this->user_model->getValue('temp_code')));
    }
    
    private function sendEmail($to, $subject, $msg) {
		$this->email->from('cookandbake@pingwinek.de', 'Cook and Bake');
		$this->email->to($to);
		$this->email->subject($subject);
		$this->email->message($msg);
		$this->email->set_mailtype('html');
		$this->email->send();
    }
    
    private function updateTempCode() {
    	$this->user_model->setValue('temp_code', $this->generateTempCode());
    	$this->user_model->setValue('temp_code_valid_until', date('Y-m-d H:i:s', strtotime('+ 2 days')));
    	
        if (! $this->user_model->updateTempCode()) {
        	$this->error(400, 'Something went wrong');
        }
    }
    
    private function generateToken() {
    	return bin2hex(random_bytes(self::TOKEN_LENGTH));
    }
    
    private function generateTempCode() {
    	return bin2hex(random_bytes(self::TEMP_CODE_LENGTH));
    }
    
    private function getConfirmMailBody($tempCode) {
    	$href = "https://pingwinek.de/cookandbake/confirm_registration/temp_code/$tempCode";
    	
    	$mailBody = '<h1>Willkommen zu Cook and Bake</h1>';
    	$mailBody .= '<div>Das ist eine Bestätigungsmail, um die Richtigkeit des Email-Accounts zu sichern.</div>';
    	$mailBody .= "<div>Einfach <a href='$href'>hier bestätigen!</a></div>";
    	$mailBody .= '<div>Wenn Sie keinen Account bei Cook and Bake angelegt haben, wenden Sie sich an XY.</div>';
    	return $mailBody;
    }
    
    private function getLostPasswordMailBody($tempCode) {
    	$href = "https://pingwinek.de/cookandbake/lost_password/temp_code/$tempCode";
    	
    	$mailBody = '<h1>Neues Passwort für Cook and Bake</h1>';
    	$mailBody .= '<div>Sie haben ein neues Passwort für Cook and Bake angefordert.</div>';
    	$mailBody .= "<div>Einfach <a href='$href'>hier klicken</a>, um ein neues Passwort zu setzen!</div>";
    	$mailBody .= '<div>Wenn Sie kein neues Passwort bei Cook and Bake angefordert haben, wenden Sie sich an XY.</div>';
    	return $mailBody;
    }
    
    private function responseWithCode($code, $msg) {
        if (is_array($msg)) {
            $this->send_response($code, $msg);
        } else {
            $this->error(400, 'Malformed response body: ' + $msg);
        }
    }
    
    private function response($msg) {
        $this->responseWithCode(200, $msg);
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