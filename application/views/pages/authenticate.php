<?php
$id_token = $_POST['id_token'];

require __DIR__.'../../../../google-api-php-client-2.2.2/vendor/autoload.php';

//The CLIENT_ID of the app, that accesses the backend
$CLIENT_ID = '851675929977-jnl65e25478i01c24ohfuntmujqg3fiq.apps.googleusercontent.com';

$client = new Google_Client(['client_id' => $CLIENT_ID]);

$payload = $client->verifyIdToken($id_token);

if ($payload) {
    $userid = $payload['sub'];
    $this->session->userid = $userid;
    echo $userid;
    // If request specified a G Suite domain:
    //$domain = $payload['hd'];
} else {
    // Invalid ID token
    echo "invalid token";
}