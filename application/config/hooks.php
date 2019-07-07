<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/

$hook['pre_controller'] = function() {
    log_message('debug', 'Host: '.$_SERVER['HTTP_HOST']
                .' URI: '.$_SERVER['REQUEST_URI']
                .' Method: '.$_SERVER['REQUEST_METHOD']
//                .' Contenttype: '.$_SERVER['CONTENT_TYPE']
                .' Protocol: '.$_SERVER['SERVER_PROTOCOL']
                .' Query-String: '.$_SERVER['QUERY_STRING']
                .' Post: '.json_encode($_POST));
    
    $body = file_get_contents('php://input');
    log_message('debug', 'Body: '.$body);
/*    
    if ($_SERVER['CONTENT_TYPE'] == 'application/x-www-form-urlencoded') {
        //parse_str does not seem to work
    } elseif ($_SERVER['CONTENT_TYPE'] == 'application/json') {
        log_message('debug', json_decode($body, true));
    }
*/
};
