<?php
// show_error places surrounds message with <p>error message</p>
$message = preg_replace('(</?p>)', '', $message);

echo json_encode(array('error' => $message));
?>