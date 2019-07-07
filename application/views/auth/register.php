<?php

$this->load->database();

$field_name_email = "email";
$field_name_pw = "password";

$email = isset($_POST[$field_name_email]) ? $_POST[$field_name_email] : null;
$password = isset($_POST[$field_name_pw]) ? password_hash($_POST[$field_name_pw], PASSWORD_DEFAULT) : null;

echo "register: ".$email." + ".$password;