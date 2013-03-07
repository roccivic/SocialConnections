<?php

// Prevents unplanned execution paths
define("SOCIALCONNECTIONS", 1);

require_once 'config.php';

$data = array();
if (strtolower($_SERVER['REQUEST_METHOD']) !== 'post') {
    header("HTTP/1.0 400 Bad Request");
    echo '<h1>Error 400: Bad Request</h1>invalid request method';
    die();
}

$session = ! empty($_REQUEST["session"]) ? $_REQUEST["session"] : '';

if (! strlen($session)) {
    header("HTTP/1.0 400 Bad Request");
    echo '<h1>Error 400: Bad Request</h1>no session token';
    die();
}

if ($_FILES["image"]["error"] !== 0) {
    header("HTTP/1.0 400 Bad Request");
    echo '<h1>Error 400: Bad Request</h1>no image found';
    die();
}

@mkdir("face_cache/$session");
@chmod("face_cache/$session", 0777);
$tmp_name = $_FILES["image"]["tmp_name"];
$name = $_FILES["image"]["name"];
move_uploaded_file($tmp_name, "face_cache/$session/$name");
chmod("face_cache/$session/$name", 0777);

$ch = curl_init();
$data = array(
    'session' => $session,
    'image' => "@face_cache/$session/$name",
    'access_token' => Config::FACE_REC_SECRET
);
curl_setopt($ch, CURLOPT_URL, Config::FACE_REC_URL . 'upload.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_exec($ch);
curl_close($ch);

?>