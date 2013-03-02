<?php

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
$tmp_name = $_FILES["image"]["tmp_name"];
$name = $_FILES["image"]["name"];
move_uploaded_file($tmp_name, "face_cache/$session/$name");

?>
