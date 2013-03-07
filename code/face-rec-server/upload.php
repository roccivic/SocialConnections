<?php

require_once 'config.php';

if (empty($_REQUEST['access_token'])
    || $_REQUEST['access_token'] !== Config::FACE_REC_SECRET
) {
    header("HTTP/1.0 401 Unauthorized");
    echo '<h1>Error 401: Unauthorized</h1>';
    die();
}

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
    echo '<h1>Error 400: Bad Request</h1>no image found';
    die();
}

@mkdir("face_cache/$session");
@chmod("face_cache/$session", 0777);

foreach ($_FILES["image"]["name"] as $key => $value) {
    $tmp_name = $_FILES["image"]["tmp_name"][$key];
    $name = $_FILES["image"]["name"][$key];
    shell_exec("convert $tmp_name -resize 92x112\! -colorspace Gray face_cache/$session/$name");
    chmod("face_cache/$session/$name", 0777);
}

?>
