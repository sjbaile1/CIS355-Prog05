<?php // Simply destroys the session and logs the user out.
session_start();
session_destroy();
header('Content-Type: application/json');
echo json_encode(['location'=>"login.html"]);
exit();