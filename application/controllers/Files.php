<?php

class Files extends CI_Controller {

	const FILENAMES = array(
		'recipe'
	);
	
	const TYPES = array(
		'application/pdf' => 'pdf'
	);
	
	// FCPATH: '/'
	// BASEPATH: '/system/'
	// APPPATH: '/application/'
	private $upload_dir = FCPATH.'uploads/';

	public function __construct() {
		parent::__construct();
	}
	
	public function save() {
		$this->authorize();
		
		if (!isset(array_keys($_FILES)[0])) {
			$this->error(400, "No file provided");
		}
		
		$field_name = array_keys($_FILES)[0];
		$file = $_FILES[$field_name];
		$file_name = $file['name'];
		$file_type = $file['type'];
		$file_name_tmp = $file['tmp_name'];
		
		// Only white listed names are allowed
		if ($this->check_file_name($file_name) == false) {
//			$this->error(400, 'filename not accepted');
			$this->update();
		}
		
		// Create a new empty file with unique name
		$file_name_new = tempnam($this->upload_dir, $file_name.'_');
		if ($file_name_new == false) {
			$this->error(400, 'could not save file due to filesystem error');
		}

		// Check file type and get appropriate suffix
		$file_name_with_suffix = $file_name_new.'.'.$this->get_file_suffix($file_type);
		if ($file_name_with_suffix == false) {
			$this->error(400, 'content type not accepted');
		}
		
		// Append the suffix to the created file with unique file name
		if (rename($file_name_new, $file_name_with_suffix) == false) {
			$this->error(400, 'could not save file with type suffix');
		}
		
		// Move the uploaded file from the temporary directory to the created file
		if (move_uploaded_file($file_name_tmp, $file_name_with_suffix) == false) {
			$this->error(400, 'could not save file due to unknown reason');
		}
		
		$this->send_response(200, array('file_id' => basename($file_name_with_suffix)));
	}
	
	public function change() {
		$this->authorize();
		
		if (!isset(array_keys($_FILES)[0])) {
			$this->error(400, "No file provided");
		}
		
		$field_name = array_keys($_FILES)[0];
		$file = $_FILES[$field_name];
		$file_name = $file['name'];
		$file_name_tmp = $file['tmp_name'];
		
		$file_name_with_path = $this->upload_dir.'/'.$file_name;
		if (! file_exists($file_name_with_path)) {
			$this->error(400, 'could not update file with this name');
		}
		
		// Move the uploaded file from the temporary directory to the created file
		if (move_uploaded_file($file_name_tmp, $file_name_with_path) == false) {
			$this->error(400, 'could not save file due to unknown reason');
		}
		
		$this->send_response(200, array('file_id' => basename($file_name_with_path)));
	}
	
	private function check_file_name($file_name) {
		if (in_array($file_name, self::FILENAMES)) {
			return true;
		} else {
			return false;
		}
	}
		
	private function get_file_suffix($file_type) {
		if (array_key_exists($file_type, self::TYPES)) {
			$suffix = self::TYPES[$file_type];
			return $suffix;
		} else {
			return false;
		}
	}
		
    private function authorize() {
        $user_id = $this->session->userid;
        if ($user_id == null) {
            $this->error(401, 'no valid session');
        }
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
            ->set_output(json_encode($msg, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK))
            ->_display();
        exit();
    }
    
}