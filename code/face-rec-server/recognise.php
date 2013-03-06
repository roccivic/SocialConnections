<?php

require_once 'config.php';

if (empty($_REQUEST['access_token'])
    || $_REQUEST['access_token'] !== Config::FACE_REC_SECRET
) {
    header("HTTP/1.0 401 Unauthorized");
    echo '<h1>Error 401: Unauthorized</h1>';
    die();
}

if (strtolower($_SERVER['REQUEST_METHOD']) !== 'post') {
    header("HTTP/1.0 400 Bad Request");
    echo '<h1>Error 400: Bad Request</h1>invalid request method';
    die();
}

$session = ! empty($_REQUEST["session"]) ? trim($_REQUEST["session"]) : '';
if (empty($session) || ! is_readable('face_cache/' . $session)) {
    header("HTTP/1.0 400 Bad Request");
    echo '<h1>Error 400: Bad Request</h1>invalid session token';
    die();
}

$gid = ! empty($_REQUEST["gid"]) ? intval($_REQUEST["gid"]) : 0;
if (! $gid) {
    header("HTTP/1.0 400 Bad Request");
    echo '<h1>Error 400: Bad Request</h1>invalid group selected';
    die();
}

$image = ! empty($_REQUEST["image"]) ? $_REQUEST["image"] : '';
$image_path = 'face_cache/' . $session . '/' . $image;
if (! is_readable($image_path)) {
    header("HTTP/1.0 400 Bad Request");
    echo '<h1>Error 400: Bad Request</h1>invalid image selected';
    die();
}

echo intval(
    trim(
        shell_exec(
            escapeshellcmd(
                './face-rec ' . $gid . ' ' . $image_path
            )
        )
    )
);

?>