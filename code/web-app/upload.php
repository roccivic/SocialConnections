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

if (empty($_FILES["image"]) || ! is_array($_FILES["image"])) {
    header("HTTP/1.0 400 Bad Request");
    echo '<h1>Error 400: Bad Request</h1>no images found';
    die();
}

@mkdir("face_cache/$session");
@chmod("face_cache/$session", 0777);

$data = array(
    'session' => $session,
    'access_token' => Config::FACE_REC_SECRET
);

foreach ($_FILES["image"]["name"] as $key => $value) {
    $tmp_name = $_FILES["image"]["tmp_name"][$key];
    $name = $_FILES["image"]["name"][$key];
    move_uploaded_file($tmp_name, "face_cache/$session/$name");
    chmod("face_cache/$session/$name", 0777);
    $data['image[' . $key . ']'] = "@face_cache/$session/$name";
}


$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, Config::FACE_REC_URL . 'upload.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_exec($ch);
curl_close($ch);

?>