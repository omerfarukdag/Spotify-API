<?php
require_once '../db.php';
$client_id = client('client_id');
$response_type = "code";
$redirect_uri = CALLBACK_URI;
$scope = "user-read-currently-playing";
$redirect = "https://accounts.spotify.com/authorize?client_id=$client_id&response_type=$response_type&redirect_uri=$redirect_uri&scope=$scope";
header("Location: $redirect");
?>
